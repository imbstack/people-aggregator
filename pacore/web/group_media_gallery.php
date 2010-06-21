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
<?
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        group_media_gallery.php, web file to display Group Media contents
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file displays the media of the user. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = FALSE;
// Only log in user can Uploaded media
if(isset($_POST)) {
    $login_required = TRUE;
}
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Album/Album.php";
require_once "api/Image/Image.php";
require_once "api/Video/Video.php";
require_once "api/Audio/Audio.php";
require_once "web/includes/functions/auto_email_notify.php";
global $uid;
$msg = (isset($_GET['msg_id'])) ? $_GET['msg_id'] : '';
$show_view = (!empty($_GET['gallery'])) ? $_GET['gallery'] : 'thumb';
// Handling the type
$type_prefix = array(
    'Images',
    'Videos',
    'Audios',
);
if(empty($_GET['type']) || (!in_array($_GET['type'], $type_prefix))) {
    // When we doesn't found type
    $type = 'Images';
}
else {
    $type = $_GET['type'];
}
$module_name = $type.'MediaGalleryModule';
$setting_data = array(
    'middle' => array(
        $module_name,
    ),
);

/* This function is a Callback function which initialize the value for the BLOCK MODULES */
if(!empty($_GET['gid'])) {
    $gid        = (int) $_GET['gid'];
    $group_data = ContentCollection::load_collection($gid, $_SESSION['user']['id']);
    $group_name = $group_data->title;
    if($_SESSION['user']['id']) {
        $is_member = Group::get_user_type($_SESSION['user']['id'], (int) $gid);
    }
}

function setup_module($column, $module, $obj) {

    /* in this module we have to set user_id , group_id, as well as netwrok_id */
    global $type, $group_data, $is_member, $msg, $show_view;
    $obj->type = $type;
    $obj->show_view = $show_view;
    switch($module) {
        case 'ImagesMediaGalleryModule':
        case 'AudiosMediaGalleryModule':
        case 'VideosMediaGalleryModule':
            if($group_data->reg_type == REG_INVITE && $is_member == NOT_A_MEMBER) {
                $msg = MessagesHandler::get_message(9005);
                return "skip";
            }
            break;
    }
}
// fetching the data from the Constants.php and Rendering the data of the page
// at present we are setting the value of setting data
$page = new PageRenderer("setup_module", PAGE_GROUP_MEDIA_GALLERY, "Group Media Gallery", "container_one_column_media_gallery.tpl", "header_group.tpl", PUB, NULL, PA::$network_info, NULL, $setting_data);
if($type == 'Images') {
    $parameter = js_includes('jtip.js');
    $page->add_header_html($parameter);
    $css = PA::$theme_url.'/jtip.css';
    $page->add_header_css($css);
}
uihelper_error_msg($msg);
uihelper_get_group_style($gid);
echo $page->render();
?>