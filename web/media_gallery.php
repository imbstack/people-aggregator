<?
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        Media Gallery.php, web file to display mediagallery contents
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
if(!empty($_POST)) {
  $login_required = TRUE;
}
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "ext/Album/Album.php";
require_once "ext/Image/Image.php";
require_once "ext/Video/Video.php";
require_once "ext/Audio/Audio.php";
require_once "web/includes/functions/auto_email_notify.php";
require_once "web/includes/functions/user_page_functions.php";

  // Here we find out the user id of the user 
  $uid = $login_uid;
  if (!empty($page_uid) && $page_uid != $login_uid) {
    $uid = $page_uid;
  }  

  //Handling The Views 
  $show_view = (!empty($_GET['gallery'])) ? $_GET['gallery']: 'thumb';
  // Handling the type   
  $type_prefix = array('Images','Videos','Audios');
  if (empty($_GET['type']) || (!in_array($_GET['type'],$type_prefix))) { 
  // When we doesn't found type 
    $type = 'Images';
  } else {
    $type = $_GET['type'];
  }
  $module_name = $type.'MediaGalleryModule';
  $setting_data = array('middle' => array($module_name));

// deleting images 
try {
  if(isset($_GET['action']) && ($_GET['action']=='delete') && ($login_uid)) {
    $id = $_GET['id'];
    $type = $_POST['media_type'];
    $album_id = $_POST['album_id'];
    if ($_GET['type'] == 'image') {
        $new_image = new Image();
        $new_image->content_id = $id;
        $new_image->delete($id);
        $msg = 2004;
        header("Location: ". PA::$url ."/media_gallery.php?type=$type&msg_id=$msg&album_id=$album_id");
        exit;
    }

    if ($_GET['type'] == 'audio') {
        $new_image = new Audio();
        $new_image->content_id = $id;
        $new_image->delete($id);
        $msg = 2005;
        header("Location: ". PA::$url ."/media_gallery.php?type=$type&msg_id=$msg&album_id=$album_id");
        exit;
    }

    if ($_GET['type'] == 'video') {
        $new_image = new Video();
        $new_image->content_id = $id;
        $new_image->delete($id);
        $msg = 2006;
        header("Location: ". PA::$url ."/media_gallery.php?type=$type&msg_id=$msg&album_id=$album_id");
        exit;
    }
  }
}
catch (PAException $e) {
   $msg = "$e->message";
   $error = TRUE;
}
/* End of Uploading and deleting */
     
/* This function is a Callback function which initialize the value for the BLOCK MODULES */
function setup_module($column, $moduleName, $obj) {
/* in this module we have to set user_id , group_id, as well as netwrok_id */  
  global $uid, $type, $album_id, $show_view;
  $obj->uid = $uid;
  $obj->show_view = $show_view;
  if (!empty($_GET['uid'])) {
    $obj->uid = $_GET['uid'];
  }
  $obj->type = $type;
  $obj->album_id = $album_id;
  if (isset($_GET['album_id']) && (!empty($_GET['album_id']))) { 
    $obj->album_id = $_GET['album_id'];  
  }
}

if (!empty($login_user)) {
  $title = User::get_login_name_from_id($uid)." - ";
} else {
  $title = "";
}
$title .= "Media Gallery - $network_info->name";

// fetching the data from the Constants.php and Rendering the data of the page 
// at present we are setting the value of setting data 
$page = new PageRenderer("setup_module", PAGE_MEDIA_GALLERY, $title, "container_one_column_media_gallery.tpl", "header_user.tpl", PUB, NULL, $network_info, null, $setting_data);
/* To avoding javascript select value function alert */
if(!isset($_GET['msg_rep'])) {
  if (!empty($msg) || !empty($_GET['msg_id'])) {
    $msg_obj = new MessagesHandler();
    $msg_id = ($_GET['msg_id']) ? $_GET['msg_id']: $msg;
    $dynamic_msg = ($msg_id == 2007)?substr ($type,0,5):NULL;
    $msg = $msg_obj->get_message($msg_id,$dynamic_msg);
    if ($msg) {
      $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
      $msg_tpl->set('message', $msg);
      $page->add_module("middle", "top", $msg_tpl->fetch());
    }
  }
}
// Jtip is shown when user watching the image media
if ($type == 'Images') { 
  $parameter = js_includes('jtip.js');
  $page->add_header_html($parameter);
  $css = $current_theme_path.'/jtip.css';
  $page->add_header_css($css);
}
// if user is not log in as well as not viewing any user page 
if (!empty($uid)) {
  uihelper_set_user_heading($page,$do_theme=TRUE, $uid);
}
else {
  uihelper_get_network_style();
}

echo $page->render();
?>