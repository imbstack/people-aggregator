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
require_once "api/Content/Content.php";
require_once "api/Tag/Tag.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once '../api/Comment/Comment.php';
include_once "../api/ModuleSetting/ModuleSetting.php";
require_once '../ext/Group/Group.php';
include_once "../api/Theme/Template.php";
require_once '../api/Category/Category.php';
require_once '../ext/Image/Image.php';
require_once '../ext/Audio/Audio.php';
require_once '../ext/Video/Video.php';

 

$parameter = js_includes("all");
html_header("Content Management", $parameter);

// deleting images
try {
  if($_GET['action']=='delete') {
    //print_r($_POST); exit;

    if ($_POST['delete_type'] == 'image') {
      foreach ($_POST as $k=>$v) {
        $delete_pics_id[] = $k;
      }
      array_pop($delete_pics_id);
      array_pop($delete_pics_id);

      foreach ($delete_pics_id as $id) {
        $new_image = new Image();
        $new_image->content_id = $id;
        $new_image->delete($id);
      }
    }

    if ($_POST['delete_type'] == 'audio') {
      foreach ($_POST as $k=>$v) {
        $delete_audios_id[] = $k;
      }
      array_pop($delete_audios_id);
      array_pop($delete_audios_id);
      foreach ($delete_audios_id as $id) {
        $new_image = new Audio();
        $new_image->content_id = $id;
        $new_image->delete($id);
      }
    }

    if ($_POST['delete_type'] == 'video') {
      foreach ($_POST as $k=>$v) {
        $delete_videos_id[] = $k;
      }
      array_pop($delete_videos_id);
      array_pop($delete_videos_id);
      foreach ($delete_videos_id as $id) {
        $new_image = new Video();
        $new_image->content_id = $id;
        $new_image->delete($id);
      }
    }
  }
}
catch (PAException $e) {
   $msg = "$e->message";
   $error = TRUE;
}

function setup_module($column, $moduleName, $obj) {
    global $content_type, $users,$uid,$_REQUEST,$user;

    switch ($column) {
    case 'left':
        $obj->mode = PUB;
        if ($moduleName=='RecentCommentsModule') {
          $obj->cid = $_REQUEST['cid'];
          $obj->block_type = HOMEPAGE;
          $obj->mode = PRI;
        }
     break;

    case 'middle':
        $obj->mode = PUB;
        $obj->orientation = CENTER;
        $obj->content_id = $_REQUEST['cid'];
        $obj->mode = PUB;
        $obj->uid = $_SESSION['user']['id'];
        $obj->block_type = 'media_management';
    break;

    case 'right':
        $obj->mode = PRI;
        if ($moduleName != 'AdsByGoogleModule') {
          $obj->block_type = HOMEPAGE;
        }
     break;
    }
}
$page = new PageRenderer("setup_module", PAGE_MEDIA_MANAGEMENT, "Media Management", "media_gallery_pa.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info);

if (!empty($msg1)) {
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg1);
  $page->add_module("middle", "top", $msg_tpl->fetch());
}

echo $page->render();
?>