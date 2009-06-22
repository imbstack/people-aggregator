<?php

/* class FortyThree
 *
 * An unfinished 43(things|places|people) client.  Supports all the
 * attributes of all the methods that we need to use inside PA, and
 * nothing else.  Feel free to add more methods/attributes as
 * required.
 *
 * Phillip Pearson
 * Copyright (C) 2006 Broadband Mechanics
 */

require_once "api/PAException/PAException.php";
require_once "HTTP/Client.php";

class FortyThree {
    public $login, $password, $pw_type;

    public function __construct() {
    }

    // call this to set the user credentials.
    // $ft->set_user("login name", "password");
    // or $ft->set_user("login name", "encoded api password", "wsse");
    public function set_user($login, $password, $pw_type='raw') {
        $this->login = $login;
        $this->password = $password;
        $this->pw_type = $pw_type;
        switch ($this->pw_type) {
        case 'raw':
        case 'wsse':
            break;
        default:
            throw new PAException(0, "Invalid pw_type '$pw_type'; must be 'raw' or 'wsse'");
        }
    }

    // wrapper for 43things.com's get_persons_entries function
    public function get_persons_entries($ft_uid, $offset=0, $maxresults=20) {
        $r = $this->_request(
            "43things",
            "get_persons_entries",
            "GET",
            array(
                "id" => $ft_uid,
                "offset" => $offset,
                "max" => $maxresults,
                ));

        $xp = $r['xpath'];

        $entries = array();
        foreach ($xp->query("/th:feed/th:entry") as $node) {
            $entries[] = $this->_convert_entry($xp, "th", $node);
        }
        
        return array(
            "pagination" => $this->_extract_pagination($xp, "th"),
            "entries" => $entries,
            );
    }

    // wrapper for 43places.com's search_places function
    public function search_places($name) {
        $r = $this->_request(
            "43places",
            "search_places",
            "GET",
            array(
                "q" => $name,
                ));

        $xp = $r['xpath'];

        $places = array();
        foreach ($xp->query("/pl:feed/pl:place") as $node) {
            $places[] = array(
                "place_id" => $xp->evaluate("string(@place_id)", $node),
                "name" => $xp->evaluate("string(pl:name)", $node),
                "url" => $xp->evaluate("string(pl:link/@href)", $node),
                "num_registered_people" => $xp->evaluate("string(pl:num_registered_people)", $node),
                "id" => $xp->evaluate("string(pl:id)", $node),
                );
        }

        return array(
            "pagination" => $this->_extract_pagination($xp, "pl"),
            "places" => $places,
            );
    }

    // experimenting with the atom api...
    public function entry() {
    }

    // internal functions to build and send HTTP requests to 43*.com

    public function _make_wsse($login, $password) {
        $nonce = md5(rand());
        echo "now it is ".date("Y-m-d H:i:s").", or is it ".date("c")."?<br>";
        $timestamp = gmdate("Y-m-d\TH:i:s\Z");
        echo "nonce: $nonce; time: $timestamp<br>";
        $munged = $nonce . $timestamp . $password;
        echo "all munged together: $munged<br>";
        $token = sha1($munged, TRUE);
        return 'UsernameToken Username="'.urlencode($login).'", PasswordDigest="'.base64_encode($token).'", Nonce="'.base64_encode($nonce).'", Created="'.($timestamp).'"';
    }

    public function _request($site, $func_name, $request_method, $data, $auth=FALSE) {

        $headers = Array(
            "User-Agent" => "PeopleAggregator/".PA_VERSION,
            );

	global $fortythree_api_key;
        $data['api_key'] = $fortythree_api_key;

        if ($auth) {
            switch ($this->pw_type) {
            case 'raw':
                $data['username'] = $this->login;
                $data['password'] = $this->password;
                break;
            case 'wsse':
                $headers['Authorization'] = 'WSSE profile="UsernameToken"';
                $headers['X-WSSE'] = $this->_make_wsse($this->login, $this->password);
                var_dump($headers);
                break;
            default:
                throw new PAException(0, "Invalid password type $this->pw_type");
            }
        }

        $url = "http://www.$site.com/service/$func_name";

        $c = new HTTP_Client(null, $headers);

        switch ($request_method) {
        case 'GET':
            $ret = $c->get($url, $data);
            break;
        case 'POST':
            $preEncoded = (gettype($data) == "string");
            $ret = $c->post($url, $data, $preEncoded);
            break;
        default:
            throw new PAException("invalid request method: $request_method");
        }
        $r = $c->currentResponse();

        echo "did http request to $url with data "; var_dump($data); echo ", returned $ret.<br>";

        $xml = $r["body"];

        echo "xml = <pre>".htmlspecialchars($xml)."</pre><hr>";

        $dom = DomDocument::loadXML($xml);

        $xp = new DOMXPath($dom);
        $xp->registerNamespace("dc", "http://purl.org/dc/elements/1.1/");
        $xp->registerNamespace("th", "http://43things.com/xml/2005/rc#");
        $xp->registerNamespace("pl", "http://43places.com/xml/2005/rc#");
        $xp->registerNamespace("pe", "http://43people.com/xml/2005/rc#");

        return array("xml" => $xml, "dom" => $dom, "xpath" => $xp);
    }

    // internal functions to parse out individual objects from the xml from 43*.com

    private function _extract_pagination($xp, $site, $path=NULL) {
        if (!$path) $path = "/$site:feed/$site:pagination";
        $pag = $xp->query($path)->item(0);

        return array(
            "offset" => $xp->evaluate("string($site:offset)", $pag),
            "max" => $xp->evaluate("string($site:max)", $pag),
            "total" => $xp->evaluate("string($site:total)", $pag),
            "next_offset" => $xp->evaluate("string($site:next_offset)", $pag),
            "previous_offset" => $xp->evaluate("string($site:previous_offset)", $pag),
            );
    }

    private function _convert_entry($xp, $site, $node) {
        return array(
            "title" => $xp->evaluate("string($site:title)", $node),
            "author_username" => $xp->evaluate("string($site:author/$site:username)", $node),
            "content" => $xp->evaluate("string($site:content[@mode='escaped'])", $node),
            "url" => $xp->evaluate("string($site:link[@rel='alternate']/@href)", $node),
            );
    }

}

?>