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
/**
 * This class is use to fetch the photo of an user from www.flickr.com
 * and then return the array contains the title and url of that photo.
 *
 * @package Flickrclient
 * @author Tekriti Software (http://www.tekritisoftware.com)
 */

//api
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
//ext
require_once "Flickr/API.php";

class Flickrclient {

  public function __construct() {
    Logger::log("Enter: Flickrclient::__construct");

    // individual users can override the global API key and secret in their local_config.php files if they wish
    global $flickr_api_key, $flickr_api_secret, $flickr_auth_type;
    
    $this->api_key = trim($flickr_api_key);
    $this->api_secret = trim($flickr_api_secret);
    $this->auth_type = trim($flickr_auth_type);

    // set up api wrapper
    $this->api =& new Flickr_API(array(
				       'api_key' => $this->api_key,
				       'api_secret' => $this->api_secret,
				       ));

    Logger::log("Exit: Flickrclient::__construct");
  }

  // wrapper for Flickr_API::callMethod that converts errors into PAExceptions
  private function _api_callmethod($methodName, $args) {
    $r = $this->api->callMethod($methodName, $args);
    if (!$r) {
      throw new PAException(REMOTE_ERROR, "Flickr error ".$this->api->_err_code.": ".$this->api->_err_msg);
    }
    return $r;
  }

  // generate an authentication frob (see flickr_in.php for usage)
  public function auth_getFrob() {
    list($dom, $xp, $xml) = $this->_api_callmethod("flickr.auth.getFrob", array());
    $frob = $xp->query("/rsp/frob")->item(0)->textContent;
    return $frob;
  }

  // build an authentication url, given a frob and a permission string (see flickr_in.php for usage)
  public function make_auth_url($frob, $perms) {
    $munge = $this->api_secret."api_key".$this->api_key."frob$frob"."perms$perms";
    return "http://flickr.com/services/auth/?api_key=$this->api_key&perms=$perms&frob=$frob&api_sig=".md5($munge);
  }

  // turn a validated frob into an authentication token (see flickr_in.php for usage)
  public function auth_getToken($frob) {
    list($dom, $xp, $xml) = $this->_api_callmethod("flickr.auth.getToken", array("frob" => $frob));
    $this->user_token = $xp->query("/rsp/auth/token")->item(0)->textContent;
    return array(
      "token" => $this->user_token,
      "perms" => $xp->query("/rsp/auth/perms")->item(0)->textContent,
      "nsid" => $xp->query("/rsp/auth/user/@nsid")->item(0)->value,
      "username" => $xp->query("/rsp/auth/user/@username")->item(0)->value,
      "fullname" => $xp->query("/rsp/auth/user/@fullname")->item(0)->value,
      );
  }

  // given an user's nsid get their (public) infp
  public function people_getInfo($nsid) {
    $r = 
      $this->_api_callmethod("flickr.people.getInfo", 
        array("user_id" => $nsid));
    return $r;
  }

  // get a user's contact list (requires read authentication)
  function contacts_getList() {
    if (!$this->user_token) throw new PAException(OPERATION_NOT_PERMITTED, "You must authenticate against flickr before calling contacts_getList()");

    list($dom, $xp, $xml) = $this->_api_callmethod("flickr.contacts.getList", array('auth_token' => $this->user_token));

    $contacts = array();

    foreach ($xp->query("/rsp/contacts/contact") as $node) {
      $contacts[] = array(
	  "nsid" => $xp->query("@nsid", $node)->item(0)->value,
	  "username" => $xp->query("@username", $node)->item(0)->value,
	  "iconserver" => $xp->query("@iconserver", $node)->item(0)->value,
	  "realname" => $xp->query("@realname", $node)->item(0)->value,
	  "friend" => $xp->query("@friend", $node)->item(0)->value,
	  "family" => $xp->query("@family", $node)->item(0)->value,
	  "ignored" => $xp->query("@ignored", $node)->item(0)->value,
	  );
    }

    return $contacts;
  }

  // given an e-mail address, get the user's nsid
  public function people_findByEmail($email) {
    list($dom, $xp, $xml) = $this->_api_callmethod("flickr.people.findByEmail", array("find_email" => $email));
    $nsid = $xp->query("/rsp/user/@nsid")->item(0)->value;
    Logger::log("Resolved Flickr e-mail '$email' to NSID '$nsid'");
    return $nsid;
  }

  // given a username, get the user's nsid
  public function people_findByUsername($username) {
    list($dom, $xp, $xml) = $this->_api_callmethod("flickr.people.findByUsername", array("username" => $username));
    $nsid = $xp->query("/rsp/user/@nsid")->item(0)->value;
    Logger::log("Resolved Flickr username '$username' to NSID '$nsid'");
    return $nsid;
  }

  // gets info on a user's public photos
  // returns array(
  //   array("server" => "...", "id" => "...", "secret" => "...", "thumbnail_url" => "..."),
  //   ...
  //   );

  public function people_getPublicPhotos($nsid, $per_page, $page) {
    list($dom, $xp, $response) = $this->_api_callmethod('flickr.people.getPublicPhotos', array('user_id'=> $nsid, 'per_page' => $per_page, 'page' => $page));
    
    $photos = array();

    foreach ($xp->query("/rsp/photos/photo") as $node) {

      $server = $xp->query("@server", $node)->item(0)->value;
      $id = $xp->query("@id", $node)->item(0)->value;
      $secret = $xp->query("@secret", $node)->item(0)->value;

      $base = "http://static.flickr.com/$server/${id}_${secret}";

      $photos[] = array(
	"server" => $server,
	"id" => $id,
	"secret" => $secret,
	"url" => "http://flickr.com/photos/$nsid/$id/",
	"med_url" => "$base.jpg",
	"75x75_url" => "${base}_s.jpg",
	);
    }

    Logger::log("Retrieved ".count($photos)." photos from Flickr for NSID $nsid");
    return $photos;
  }

  // get a url to a user's photos, given the user's nsid
  function urls_getUserPhotos($user_id) {
    list($dom, $xp, $xml) = $this->_api_callmethod("flickr.urls.getUserPhotos", array("user_id" => $user_id));
    return $xp->query("/rsp/user/@url")->item(0)->value;
  }

}

?>