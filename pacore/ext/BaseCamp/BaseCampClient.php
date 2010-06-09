<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php

/* class BaseCampClient
 *
 * A client for BaseCamp (http://basecamphq.com/).
 *
 * Usage:
 *
 *  $bc = new BaseCampClient("http://myelin.grouphub.com/", "myelin", "myelin's password");
 *  $projects = $bc->list_projects();
 *
 * Phillip Pearson
 * Copyright (C) 2006 Broadband Mechanics
 */

require_once "api/PAException/PAException.php";
require_once "HTTP/Client.php";

class BaseCampClient {

    // constructor

    public function __construct($site_url, $login, $password) {
        $this->site_url = $site_url;
        $this->login = $login;
        $this->password = $password;
    }

    // api methods

    public function list_projects() {
        return $this->_request("$this->site_url/project/list", "<request/>");
    }

    public function people($company_id, $project_id=0) {
        $url = $this->site_url;
        if ($project_id) $url .= "/projects/$project_id";
        $url .= "/contacts/people/$company_id";
        return $this->_request($url, "<request/>");
    }

    public function project_contacts($project_id) {
	return $this->_request("$this->site_url/projects/$project_id/contacts", "<request/>");
    }

    public function companies() {
	// the API doesn't do this, so we have to scrape basecamp :(
	$r = $this->_scrape("$this->site_url/contacts/all_clients");

	$companies = array();

//	echo "looking for companies<br>";
	if (preg_match_all('/<table class="Contacts">(.*?)<\/table>/s', $r['body'], $rows)) {
	    foreach ($rows[1] as $row) {
//		echo "row: looking for company info<br>";
		if (preg_match('/<h1>(.*?)<\/h1>.*?\/contacts\/company\/(\d+)/s', $row, $m)) {
//		    echo "now looking for people<br>";
		    $people = array();
		    if (preg_match_all('/<td class="person">.*?<h2>(.*?)<\/h2>.*?mailto:(.*?)"/s', $row, $cells, PREG_SET_ORDER)) {
			foreach ($cells as $cell) {
			    list($html, $pname, $pemail) = $cell;
//			    echo "person: $pname, $pemail<br>";
			    $people[] = array(
				'name' => $pname,
				'email' => $pemail,
				);
			}
		    }
		    $companies[] = array(
			'name' => $m[1],
			'id' => $m[2],
			'people' => $people,
			);
		}
	    }
	}

	return $companies;
    }

    // plumbing

    // HTTP_Client instance used for scraping company ids
    private $scraper;

    public function _scrape($url) {
	$this->_scrape_login();
	$this->scraper->get($url); //FIXME: check response code
	return $this->scraper->currentResponse();
    }

    public function _scrape_login() {
	if ($this->scraper) return;

	$url = "$this->site_url/login/authenticate";
	$this->scraper = new HTTP_Client();
	$this->scraper->post($url, array(
		     "user_name" => $this->login,
		     "password" => $this->password,
		     "remember_me" => "on",
		     ));
	$r = $this->scraper->currentResponse();
        if (strpos($r['body'], '<div class="bad"') !== FALSE)
            throw new PAException(USER_INVALID_PASSWORD, "Login incorrect");
//	echo "<p>response to login call:</p><pre>".htmlspecialchars($r['body']).'</pre>';
    }

    private function _request($url, $xml) {
        
        $headers = array(
            "Accept" => "application/xml",
            "Content-Type" => "application/xml",
            );

        $c = new HTTP_Client(null, $headers);

        $c->setRequestParameter(array(
                                    "user" => $this->login,
                                    "pass" => $this->password,
                                    ));

        $ret = $c->post($url, $xml, TRUE);
        switch ($ret) {
        case 200:
            // ok
            break;
            
        case 401:
            throw new PAException(USER_INVALID_PASSWORD, "Login incorrect");
            
        case 403:
            throw new PAException(USER_ACCESS_DENIED, "Access denied");
            
        default:
            throw new PAException(GENERAL_SOME_ERROR, "BaseCamp API returned HTTP error code $ret");
        }
        
        $r = $c->currentResponse();

//        echo "did http request to $url with data "; var_dump($xml); echo ", returned $ret.<br>";

        $xmlret = $r["body"];

//        echo "xml response: <pre>".htmlspecialchars($xmlret)."</pre>";

        return simplexml_load_string($xmlret);
    }
    
}

?>