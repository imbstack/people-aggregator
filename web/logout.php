<?php

$login_required = TRUE;
include_once("web/includes/page.php");
require_once "api/Theme/Template.php";
require_once "api/Login/PA_Login.class.php";
require_once "web/includes/classes/UrlHelper.class.php";

//if return url is set in the request then after logout redirect to the location else redirect to homepage.Change done for Channel login widget.
$return = UrlHelper::url_for(PA::$url . '/' . FILE_LOGIN, array(), 'https');
//$return = PA::$url . '/' . FILE_LOGIN;
if (!empty($_REQUEST['return'])) {
  $return = $_REQUEST['return'];
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

?>
