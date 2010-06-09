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
 * File:        groupmedia_post.php, web file to display upload media contents
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file displays the all the upload media for the Groups. It uses
 *              page renderer to display the block modules, for handling this page user
 *              must be sign in as well as member of that group
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
// Only Registerd User can upload the files
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Image/Image.php";
require_once "api/Audio/Audio.php";
require_once "api/Video/Video.php";
require_once "api/Album/Album.php";
require_once "api/Activities/Activities.php";
require "api/api_constants.php";


$extra = unserialize(PA::$network_info->extra);
  filter_all_post($_POST);
  $content_type = 'media';
  $media_type = @$_POST['media_type'];
  
  $caption = NULL;
  switch ($media_type) {
    case 'image':
      $file_name_dynamic = "userfile_";
      $extention = NULL;
      $caption = $_POST['caption'];
      $msg_id = 2001;
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media&gid=" . $_GET['gid'];
    break;
    
    case 'audio':
      $file_name_dynamic = "userfile_audio_";
      $extention = '_audio';
      $caption = $_POST['caption_audio'];
      $msg_id = 2002;
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/view=groups_media&gid=" . $_GET['gid'];
    break;
    
    case 'video':
      $file_name_dynamic = "userfile_video_";
      $extention = '_video';
      $caption = $_POST['caption_video'];
      $msg_id = 2003;
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . "/view=groups_media&gid=" . $_GET['gid'];
    break;
  }
  // count number of parameter enter by the user 
  $cnt = count($caption);
  for ($i=0, $file_count=0; $i < $cnt; $i++) {
    $file = $_FILES[$file_name_dynamic.$i]['name'];
    if (empty($file)) 
      ++$file_count;
    
  }
  
  $error_msg = NULL;
  
  if ($file_count == $cnt) {
    $error_msg = "Select any $media_type file to upload";
  }
  
  if (empty($login_uid)) {
    $error_msg = "You have to login before uploading $media_type";
  }

if (!empty($_POST) && empty($error_msg)) {
  
  for ($k = 0; $k < $cnt; $k++) {
    $upload = uihelper_upload_gallery_for_group ($login_uid, $_POST, $_FILES, $extention, $k);
    if ($upload[3] == TRUE) {
      if ($extra['network_content_moderation'] == NET_YES && PA::$network_info->owner_id != $login_uid) {
        Network::moderate_network_content((int)$upload['collection_id'], $upload['content_id']);
        $msg_id = 1004;
      }
      $uploaded = TRUE;
      $success = true;
    }
    else {
        $error_msg = $upload[1]; //index 1 has the error message during uploading
    }
    
  }
} 

// deleting media
try {
  $id = @$_GET['id'];
  if(!empty($_GET['action']) && $_GET['action']=='delete') {

    if ($_GET['type'] == 'Images') {
        $new_image = new Image();
        $new_image->content_id = $id;
        $new_image->parent_collection_id = $_GET['gid'];
        $new_image->delete($id);
        $success_delete = TRUE;
        $msg_id = 2004;
    }

    if ($_GET['type'] == 'Audios') {
        $new_image = new Audio();
        $new_image->content_id = $id;
        $new_image->delete($id);
        $success_delete = TRUE;
        $msg_id = 2005;
    }

    if ($_GET['type'] == 'Videos') {
        $new_image = new Video();
        $new_image->content_id = $id;
        $new_image->delete($id);
        $success_delete = TRUE;
        $msg_id = 2006;
    }
  }
}
catch (PAException $e) {
   $error_msg = "$e->message";
   $error = TRUE;
}
//code for rivers of people
if (!empty($uploaded)) { 
  $type = $_GET['type'];
  switch ($type) {
    case 'Images':
      $activity = 'group_image_upload';//for rivers of people
      $activities_extra['info'] = ($login_name.' uploaded a image in group id ='.$_GET['gid']);
      break;
    case 'Videos':
      $activity = 'group_video_upload';//for rivers of people
      $activities_extra['info'] = ($login_name.' uploaded a video in group id ='.$_GET['gid']);
      break;
    case 'Audios':
      $activity = 'group_audio_upload';//for rivers of people
      $activities_extra['info'] = ($login_name.' uploaded a audio in group id ='.$_GET['gid']);
      break;
    default:
      break;
  }
  $activities_extra['collection_id'] =$upload['collection_id'];
  $activities_extra['content_id'] =$upload['content_id'];
  $extra = serialize($activities_extra);
  $object = $_GET['gid'];
  Activities::save($login_uid, $activity, $object, $extra);
}
// Code for Re-Direction  After Successfull deletion
if (!empty($success_delete) || !empty($success)) {
  $gid = $_GET['gid'];
  $type = $_GET['type'];  
  $location = $ret_url . "&msg_id=$msg_id";
  header("Location: $location");
  exit;
}

$setting_data = array('middle'=> array ( 'UploadMediaModule' ) );
    
/* This function is a Callback function which initialize the value for the BLOCK MODULES */
function setup_module($column, $moduleName, $obj) {
/* in this module we have to set user_id , group_id, as well as netwrok_id */  
  global $uid, $type, $album_id;
  $obj->uid = $uid;
  $obj->type = $_GET['type'];
  $obj->gid = $_GET['gid'];
}
                      
// fetching the data from the Constants.php and Rendering the data of the page 
// at present we are setting the value of setting data 
$page = new PageRenderer("setup_module", PAGE_GROUP_MEDIA_POST, "Group Media
Gallery", "container_one_column_media_gallery.tpl", "header.tpl", PUB, NULL,
PA::$network_info, NULL, $setting_data);

uihelper_error_msg($error_msg);
/* This function set the network theme in this page */
uihelper_get_network_style();
echo $page->render();
?>