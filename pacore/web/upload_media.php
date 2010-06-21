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
 * File:        upload_media.php, web file to display upload media contents
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file displays the all the upload media for the user. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
// Only Registerd User can upload the files
$login_required = TRUE;
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Album/Album.php";
require_once "api/Image/Image.php";
require_once "api/Video/Video.php";
require_once "api/Audio/Audio.php";
//require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Activities/Activities.php";
require_once "api/api_constants.php";
require_once "api/Messaging/MessageDispatcher.class.php";
$extra = unserialize(PA::$network_info->extra);
$user = get_user();
//
$uid = PA::$login_uid;

/* ..... Handling the Post Data ....*/
if(isset($_POST)) {

    /* Function for Filtering the POST data Array */
    filter_all_post($_POST, TRUE);
}
$error_msg = NULL;

/* Error handling for the media gallery */
if(!empty($_POST)) {
    // first we have chech that which type of media user wants to upload
    $media_type = $_POST['media_type'];
    switch($media_type) {
        case 'image':
            $url_file_field    = 'userfile_url_';
            $file_name_dynamic = "userfile_";
            $extention         = NULL;
            $caption           = $_POST['caption'];
            break;
        case 'audio':
            $url_file_field    = 'userfile_audio_url_';
            $file_name_dynamic = "userfile_audio_";
            $extention         = '_audio';
            $caption           = $_POST['caption_audio'];
            break;
        case 'video':
            $url_file_field    = 'userfile_video_url_';
            $file_name_dynamic = "userfile_video_";
            $extention         = '_video';
            $caption           = $_POST['caption_video'];
            break;
    }
    // count number of parameter enter by the user
    $cnt = count($caption);
    for($i = 0, $file_count = $cnt; $i < $cnt; $i++) {
        $url = $_POST[$url_file_field.$i];
        $file = $_FILES[$file_name_dynamic.$i]['name'];
        if(empty($url) && empty($file)) {
            $file_count--;
        }
    }
    $error = NULL;
    if($file_count != $cnt) {
        $error_msg = __("Select any $media_type file to upload");
    }
    if(empty(PA::$login_uid)) {
        $error_msg = __("You have to login before uploading $media_type");
    }
}
if(!empty($_POST) && empty($error_msg)) {
    for($k = 0; $k < $cnt; $k++) {
        $upload = uihelper_upload_gallery(PA::$login_uid, $_POST, $_FILES, $extention, $k);
        if($upload[3] == TRUE) {
            if($extra['network_content_moderation'] == NET_YES && PA::$network_info->owner_id != PA::$login_uid) {
                Network::moderate_network_content((int) $upload['album_id'], $upload[4]);
                // is_active = 2 for unverified content
                $moderation_msg = TRUE;
            }
            $uploaded = TRUE;
            $success = true;
        }
        else {
            $error_msg = $upload[1];
            //index 1 has the error message during uploading
        }
    }
}
// Code for Re-Direction
if(isset($success) && ($success == true)) {
    switch($_GET['type']) {
        case 'Images':
            $album_id = (!empty($upload['album_id'])) ? $upload['album_id'] : $_POST['album'];
            $album    = "&album_id=".$album_id;
            $msg_id   = (!empty($moderation_msg) && ($moderation_msg == true)) ? 1005 : 2001;
            //for rivers of people
            $activity = 'user_image_upload';
            //for rivers of people
            $activity_extra['info']       = ($login_name.' uploaded a image in album id = 
                        '.$upload['album_id']);
            $activity_extra['content_id'] = $upload[4];
            $extra                        = serialize($activity_extra);
            $object                       = $upload['album_id'];
            Activities::save(PA::$login_uid, $activity, $object, $extra);
            $ret_url = PA::$url.PA_ROUTE_MEDIA_GALLEY_IMAGES."/uid=".PA::$login_uid."&msg_id=$msg_id".$album;
            header("Location: ".$ret_url);
            exit;
            break;
        case 'Audios':
            $album_id = (!empty($upload['album_id'])) ? $upload['album_id'] : $_POST['album_audio'];
            $album    = "&album_id=".$album_id;
            $msg_id   = (!empty($moderation_msg) && ($moderation_msg == true)) ? 1005 : 2002;
            //for rivers of people
            $activity = 'user_audio_upload';
            //for rivers of people
            $activity_extra['info']       = ($login_name.' uploaded a audio in album id = 
                        '.$upload['album_id']);
            $activity_extra['content_id'] = $upload[4];
            $extra                        = serialize($activity_extra);
            $object                       = $upload['album_id'];
            Activities::save(PA::$login_uid, $activity, $object, $extra);
            $ret_url = PA::$url.PA_ROUTE_MEDIA_GALLEY_AUDIOS."/uid=".PA::$login_uid."&msg_id=$msg_id".$album;
            header("Location: ".$ret_url);
            exit;
            break;
        case 'Videos':
            $album_id = (!empty($upload['album_id'])) ? $upload['album_id'] : $_POST['album_video'];
            $album    = "&album_id=".$album_id;
            $msg_id   = (!empty($moderation_msg) && ($moderation_msg == true)) ? 1005 : 2003;
            //for rivers of people
            $activity = 'user_video_upload';
            //for rivers of people
            $activity_extra['info']       = ($login_name.' uploaded a video in album id =                                     '.$upload['album_id']);
            $activity_extra['content_id'] = $upload[4];
            $extra                        = serialize($activity_extra);
            $object                       = $upload['album_id'];
            Activities::save(PA::$login_uid, $activity, $object, $extra);
            $ret_url = PA::$url.PA_ROUTE_MEDIA_GALLEY_VIDEOS."/uid=".PA::$login_uid."&msg_id=$msg_id".$album;
            header("Location: ".$ret_url);
            exit;
            break;
    }
}
$setting_data = array(
    'middle' => array(
        'UploadMediaModule',
    ),
);
if(!empty($_GET['gid']) && $_GET['gid']) {
    // here we calculating the groups of that User
    $group_ids = Group::get_user_groups(PA::$login_uid);
    $group_ids_array = array();
    if(!empty($group_ids)) {
        for($i = 0; $i < count($group_ids); $i++) {
            $group_ids_array[] = $group_ids[$i]['gid'];
        }
    }
    if(!in_array($_GET['gid'], $group_ids_array)) {
        header("Location: ".PA::$url.PA_ROUTE_GROUP."/gid=".$_GET['gid'].'&msg_id=13003');
        exit;
    }
}

/* This function is a Callback function which initialize the value for the BLOCK MODULES */
function setup_module($column, $moduleName, $obj) {

    /* in this module we have to set user_id , group_id, as well as netwrok_id */
    global $uid, $type, $album_id;
    $obj->uid = $uid;
    $obj->type = $_GET['type'];
}
// fetching the data from the Constants.php and Rendering the data of the page
// at present we are setting the value of setting data
$page = new PageRenderer("setup_module", PAGE_MEDIA_GALLERY_UPLOAD, sprintf(__("%s - Media Gallery - %s"), $login_user->get_name(), PA::$network_info->name), "container_one_column_media_gallery.tpl", "header.tpl", PUB, NULL, PA::$network_info, null, $setting_data);

/* This function shows the Error message */
uihelper_error_msg($error_msg);

/* This function set the user theme in this page */
uihelper_set_user_heading($page);
echo $page->render();
?>