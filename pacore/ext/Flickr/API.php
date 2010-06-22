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
#
# PEAR::Flickr_API
# hacked up by Phillip Pearson @ Broadband Mechanics to use DOMDocument instead of XML/Tree
#
# Author: Cal Henderson
# Version: $Revision: 1.6 $
# CVS: $Id: API.php,v 1.6 2005/07/25 18:22:13 cal Exp $
#
//	require_once 'XML/Tree.php';
require_once 'HTTP/Request.php';

class Flickr_API {
    var $_cfg = array(
        'api_key'       => '',
        'api_secret'    => '',
        'endpoint'      => 'http://www.flickr.com/services/rest/',
        'auth_endpoint' => 'http://www.flickr.com/services/auth/?',
        'conn_timeout'  => 5,
        'io_timeout'    => 5,
    );
    var $_err_code = 0;
    var $_err_msg = '';

    function Flickr_API($params = array()) {
        foreach($params as $k => $v) {
            $this->_cfg[$k] = $v;
        }
    }

    function callMethod($method, $params = array()) {
        $this->_err_code = 0;
        $this->_err_msg = '';

        #
        # create the POST body
        #
        $p            = $params;
        $p['method']  = $method;
        $p['api_key'] = $this->_cfg['api_key'];
        if($this->_cfg['api_secret']) {
            $p['api_sig'] = $this->signArgs($p);
        }
        $p2 = array();
        foreach($p as $k => $v) {
            $p2[] = urlencode($k).'='.urlencode($v);
        }
        $body = implode('&', $p2);

        #
        # create the http request
        #
        $req = &new HTTP_Request($this->_cfg['endpoint'], array('timeout' => $this->_cfg['conn_timeout']));
        $req->_readTimeout = array(
            $this->_cfg['io_timeout'],
            0,
        );
        $req->setMethod(HTTP_REQUEST_METHOD_POST);
        $req->addRawPostData($body);
        $req->sendRequest();
        $this->_http_code = $req->getResponseCode();
        $this->_http_head = $req->getResponseHeader();
        $this->_http_body = $req->getResponseBody();
        if($this->_http_code != 200) {
            $this->_err_code = 0;
            if($this->_http_code) {
                $this->_err_msg = "Bad response from remote server: HTTP status code $this->_http_code";
            }
            else {
                $this->_err_msg = "Couldn't connect to remote server";
            }
            return 0;
        }

        #
        # create xml tree
        #
        $dom = new DOMDocument();
        $dom->loadXML($this->_http_body);
        $xp = new DOMXPath($dom);

        #
        # check we got an <rsp> element at the root
        #
        if(!$xp->query("/rsp")->length) {
            $this->_err_code = 0;
            $this->_err_msg = "Bad XML response";
            return 0;
        }

        #
        # stat="fail" ?
        #
        $stat = $xp->query("/rsp/@stat")->item(0)->value;
        if($stat == 'fail') {
            $n = null;
            foreach($xp->query("/rsp/err") as $err) {
                $this->_err_code = $xp->query("@code", $err)->item(0)->value;
                $this->_err_msg = $xp->query("@msg", $err)->item(0)->value;
            }
            return 0;
        }

        #
        # weird status
        #
        if($stat != 'ok') {
            $this->_err_code = 0;
            $this->_err_msg = "Unrecognised REST response status";
            return 0;
        }

        #
        # return the tree
        #
        return array($dom, $xp, $this->_http_body);
    }

    function getErrorCode() {
        return $this->_err_code;
    }

    function getErrorMessage() {
        return $this->_err_msg;
    }

    function getAuthUrl($perms, $frob = '') {
        $args = array(
            'api_key' => $this->_cfg['api_key'],
            'perms' => $perms,
        );
        if(strlen($frob)) {
            $args['frob'] = $frob;
        }
        $args['api_sig'] = $this->signArgs($args);

        #
        # build the url params
        #
        $pairs = array();
        foreach($args as $k => $v) {
            $pairs[] = urlencode($k).'='.urlencode($v);
        }
        return $this->_cfg['auth_endpoint'].implode('&', $pairs);
    }

    function signArgs($args) {
        ksort($args);
        $a = '';
        foreach($args as $k => $v) {
            $a .= $k.$v;
        }
        return md5($this->_cfg['api_secret'].$a);
    }
}
?>
