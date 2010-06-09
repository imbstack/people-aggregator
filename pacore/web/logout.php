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

$login_required = TRUE;
include_once("web/includes/page.php");
require_once "api/Theme/Template.php";
require_once "api/Login/PA_Login.class.php";
require_once "web/includes/classes/UrlHelper.class.php";



// if return url is set in the request then after logout redirect to the location else redirect to homepage.
if (!empty($_REQUEST['return'])) {
  $return = $_REQUEST['return'];
} else {
	// build rthe url via UrlHelper so we can respect the SSL directives
	$return = UrlHelper::url_for(PA::$url . '/' . FILE_LOGIN, array(), 'https');
}

// destroy the login cookie
PA_Login::log_out();

// invalidate the cache for user profile
$file = PA::$theme_url . "/user_profile.tpl?uid=".PA::$login_uid;
CachedTemplate::invalidate_cache($file);

// kill the session
$_SESSION = array();
session_destroy();
session_start();

// and go home :)
header("Location: $return");
exit;
?>
