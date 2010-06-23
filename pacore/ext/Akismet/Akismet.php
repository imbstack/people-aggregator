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

/* Class for accessing Automattic's Akismet spam classifier web service.

   http://akismet.com/development/api/

*/

require_once "HTTP/Request.php";

class Akismet {
    private $api_key = "";
    
    public function __construct($key) {
	$this->api_key = $key;
    }
    
    public function verify_key($home_url) {
	return $this->call("verify-key", array("key" => $this->api_key, "blog" => $home_url), false);
    }
    
    public function check_spam($home_url, $params) {
	$params['blog'] = $home_url;
	foreach (array("user_ip", "user_agent", "referrer", "permalink", "comment_type", "comment_author", "comment_author_email", "comment_author_url", "comment_content") as $k) {
	    if (!isset($params[$k])) throw new PAException(REQUIRED_PARAMETERS_MISSING, "Akismet::check_spam call missing required or recommended parameter $k");
	}
	return $this->call("comment-check", $params);
    }
    
    private function call($method, $args, $include_api_key=true) {
	$host = "rest.akismet.com";
	if ($include_api_key) $host = "$this->api_key.$host";
	$url = "http://$host/1.1/$method";
	$req = new HTTP_Request($url, array("method" => "POST"));
	
	$req->addHeader("User-Agent", "PeopleAggregator/".PA_VERSION);
	foreach ($args as $k => $v) {
	    $req->addPostData($k, $v);
	}
	$req->sendRequest();
	return $req->getResponseBody();
    }

}

?>