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
 * Project:     PeopleAggregator: a social network developement platform
 * File:        save_blog_settings.php, web file is handel the appointmentie status
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */

/** This page us used for change the Appointmentie status .
Super  user can  view this page;
*/
$login_required = TRUE;
$use_theme = 'Beta';
include_once("web/includes/page.php");
global $number_user;
require_once "api/User/User.php";
require_once "web/includes/functions/user_page_functions.php";
$user = new User();
$params_profile = Array(
    ',',
);
$field_type    = GENERAL;
$field_name    = 'BlogSetting';
$user->user_id = $_SESSION['user']['id'];
if($_POST['personal_blog'] && $_POST['external_blog']) {
    $status = BLOG_SETTING_STATUS_ALLDISPLAY;
}
elseif($_POST['personal_blog'] && !$_POST['external_blog']) {
    $status = PERSONAL_BLOG_SETTING_STATUS;
}
elseif(!$_POST['personal_blog'] && $_POST['external_blog']) {
    $status = EXTERNAL_BLOG_SETTING_STATUS;
}
else {
    $status = BLOG_SETTING_STATUS_NODISPLAY;
}
$params_profile[0] = Array(
    0 => $user->user_id,
    1 => $field_name,
    2 => $status,
    3 => $field_type,
    4 => 1,
    5 => null,
);
$user->save_user_profile_fields($params_profile, $field_type, $field_name);
if(!empty($_GET['mode']) && htmlspecialchars($_GET['mode']) == 'blog_rss') {
    header("Location:".PA::$url.PA_ROUTE_EDIT_PROFILE."?type=blogs_rss&msg_id=9025");
    exit;
}
header("Location: ".PA::$url.PA_ROUTE_USER_PRIVATE.'/'."msg_id=9025");
exit;
?>
