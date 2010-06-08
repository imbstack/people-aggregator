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
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");

require_once 'api/Category/Category.php';
require_once 'ext/Image/Image.php';
require_once 'ext/Audio/Audio.php';
require_once 'ext/Video/Video.php';
require_once 'ext/Album/Album.php';
require_once "api/Permissions/PermissionsHandler.class.php";

// for query count
global $query_count_on_page;
$query_count_on_page = 0;
$error = null;

$parameter = js_includes("all");
html_header("Media Gallery - " . PA::$network_info->name, $parameter);

$uid = PA::$login_uid;
/*  Check for the content author id */
if(!empty($_REQUEST["cid"])) {
  $cid = $_REQUEST["cid"];
  $params = array( 'permissions'=>'edit_content', 'uid'=> $uid, 'cid'=> $cid );
  if(!PermissionsHandler::can_user(PA::$login_uid, $params)) {
    header("Location: ". PA::$url . PA_ROUTE_HOME_PAGE . "/msg=".urlencode('Error: You are not authorized to access this page.'));
    exit;
  }
  // It will give the content type whether its an SB Content or a Blogpost
  $obj_content_type = Content::load_content($cid, $uid);

}
if(!empty($_GET['cid'])) {
  $cid = $_GET['cid'];
  if ( $obj_content_type->type ) {
    switch( $obj_content_type->type ) {
      case 'Image':
        $show_media = new Image();
      break;
      case 'Audio':
        $show_media = new Audio();
      break;
      case 'TekVideo':
        $show_media = new TekVideo();
      break;
    }
    $show_media->load($cid);
 //   echo "<pre>" . print_r($show_media, 1). "</pre>";
    // loading tags for media
    $tags_array = Tag::load_tags_for_content($show_media->content_id);
    $tags_string= "";
    if(count($tags_array) > 0) {
        for($counter = 0; $counter < count($tags_array); $counter++) {
            $tags_string .= $tags_array[$counter]['name'].", ";
        }
        $tags_string = substr($tags_string, 0, strlen($tags_string) - 2);
    }
    $show_media->tags = $tags_string;
    // loading content's contentcollection information
    $cc_info = ContentCollection::load_collection($show_media->parent_collection_id, PA::$login_uid);
  }
}
if ($uid == PA::$login_uid) {
  $extra = unserialize(PA::$network_info->extra);
  // for edit - group media
  if (isset($_POST['submit_group']) && ($_POST['group_id'])) {
    if ($_POST['media_type'] == 'image') {
      $module = 'Images';
      $new_save = new Image();
      if (isset($_POST['image_perm'])) {
        $new_save->file_perm = $_POST['image_perm'];
      }
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media&gid=" . $_POST['group_id'];
    }
    else if ($_POST['media_type'] == 'audio') {
      $module = 'Audios';
      $new_save = new Audio();
      if (isset($_POST['audio_perm'])) {
        $new_save->file_perm = $_POST['audio_perm'];
      }
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/view=groups_media&gid=" . $_POST['group_id'];
    }
    else if ($_POST['media_type'] == 'video') {
      $module = 'Videos';
      $new_save = new TekVideo();
      $new_save->status = 1;
      if (isset($_POST['video_perm'])) {
        $new_save->video_perm = $_POST['video_perm'];
        $new_save->file_perm = $_POST['video_perm'];
      }
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . "/view=groups_media&gid=" . $_POST['group_id'];
    }
    $new_save->content_id = $_POST['file_id'];

    $new_save->title = stripslashes(trim($_POST['caption']));
    $new_save->title = strip_tags($new_save->title);

    $new_save->excerpt = stripslashes(trim($_POST['caption']));
    $new_save->excerpt = strip_tags($new_save->excerpt);

    $new_save->body = stripslashes(trim($_POST['body']));
    $new_save->body = stripslashes($new_save->body);

    $new_save->parent_collection_id = $_POST['group_id'];

    $condition = array('content_id' => $new_save->content_id);
    $is_active = ACTIVE;
    if ($extra['network_content_moderation'] == NET_YES) {
      $content = Content::load_all_content_for_moderation(NULL, $condition);
      if (!empty($content)) {
        $is_active = $content[0]['is_active'];
      }
    }
    $new_save->is_active = $is_active;
    $new_save->save();
    $_tags = explode(',' , strtolower(str_replace(' ', '', $_POST['tags'])));
    $_tags = implode(',', array_unique($_tags));
    $tag_array = Tag::split_tags($_tags);
    Tag::add_tags_to_content($new_save->content_id, $tag_array);
    $msg = __(substr ($module, 0, 5) . " updated successfully");
    header("Location: ". $ret_url . "&msg=$msg");
  }

  if (isset($_POST['submit'])) {
    /* Function for Filtering the POST data Array */
    filter_all_post($_POST, TRUE);
    if ($_POST['media_type'] == 'image') {
      $module = 'Images';
      $new_save = new Image();
      if (isset($_POST['image_perm'])) {
        $new_save->file_perm = $_POST['image_perm'];
      }
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=" . $cc_info->author_id;
    }
    else if ($_POST['media_type'] == 'audio') {
      $module = 'Audios';
      $new_save = new Audio();
      if (isset($_POST['audio_perm'])) {
        $new_save->file_perm = $_POST['audio_perm'];
      }
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/uid=" . $cc_info->author_id;
    }
    else if ($_POST['media_type'] == 'video') {
      $module = 'Videos';
      $new_save = new Video();
      if (isset($_POST['video_perm'])) {
        $new_save->file_perm = $_POST['video_perm'];
      }
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . "/uid=" . $cc_info->author_id;
    }
    $new_save->content_id = $_POST['file_id'];

    $new_save->title = stripslashes(trim($_POST['caption']));
    $new_save->title = strip_tags($new_save->title);

    $new_save->excerpt = stripslashes(trim($_POST['caption']));
    $new_save->excerpt = strip_tags($new_save->excerpt);

    $new_save->body = stripslashes(trim($_POST['body']));
    $new_save->body = stripslashes($new_save->body);

    if (isset($_POST['file_name'])) {
      $new_save->file_name = $_POST['file_name'];
    }
    $new_save->allow_comments = 1;
    if (!empty($_POST['new_album'])) {
      if ($_POST['media_type'] == 'image') {
        $alb_type = IMAGE_ALBUM;
        $new_im_al = new Album($alb_type);
      }
      else if ($_POST['media_type'] == 'audio') {
        $alb_type = AUDIO_ALBUM;
        $new_im_al = new Album($alb_type);
      }
      else if ($_POST['media_type'] == 'video') {
        $alb_type = VIDEO_ALBUM;
        $new_im_al = new Album($alb_type);
      }
      $new_im_al->type = 2;
      $new_im_al->title = $_POST['new_album'];
      $new_im_al->name = $_POST['new_album'];
      $new_im_al->description = $_POST['new_album'];
      try {
        $new_im_al->save();
        $new_save->parent_collection_id = $new_im_al->collection_id;
      }
      catch(PAException $e) {
        $error = $e->message;
      }
    }
    else {
      $new_save->parent_collection_id = $_POST['album'];
    }

    if(!$error) {
      $condition = array('content_id' => $new_save->content_id);
      $is_active = ACTIVE;
      if ($extra['network_content_moderation'] == NET_YES) {
        $content = Content::load_all_content_for_moderation(NULL, $condition);
        if (!empty($content)) {
          $is_active = $content[0]['is_active'];
        }
      }
      $new_save->is_active = $is_active;
      $new_save->save();

      if(!empty($_POST['tags'])) {
        $_tags = explode(',' , strtolower(str_replace(' ', '', $_POST['tags'])));
        $_tags = implode(',', array_unique($_tags));
        $tag_array = Tag::split_tags($_tags);
      } else {
        $tag_array = array();
      }
      Tag::add_tags_to_content($new_save->content_id, $tag_array);
      $album_id = $_POST['album'];
      $album = "&album_id=".$album_id;
      $msg = __(substr ($module, 0, 5) . " updated successfully");
      header("Location: " . $ret_url . $album . "&msg=$msg");
    }

  }
}

$user = new User();
$user->load((int)$uid);

function setup_module($column, $moduleName, $obj) {
    global $show_media, $users, $uid, $cc_info, $user, $author_id;
    $uid = PA::$login_uid;
    switch ($column) {
    case 'left':
       $obj->mode = PUB;
       if ($moduleName != 'LogoModule') {
          $obj->block_type = HOMEPAGE;
        }
        if ($moduleName == 'RelationsModule') {
          $obj->mode = PUB;
        }
        $obj->uid = $uid;
    break;

    case 'middle':
        $obj->content_id = $_REQUEST['cid'];
        $obj->mode = PUB;
        $obj->uid = $uid;
        $obj->media_data = $show_media;
        $obj->contentcollection_type = $cc_info->type;
        $obj->author_id = $cc_info->author_id;
   break;

    case 'right':
        $obj->uid = $uid;
        if ($moduleName=='UserPhotoModule') {
            $obj->block_type = 'UserPhotoBlock';
         }
         if ($moduleName != 'AdsByGoogleModule') {
            $obj->block_type = HOMEPAGE;
         }
      break;
    }
}

$page = new PageRenderer("setup_module", PAGE_EDIT_MEDIA, "Edit Media", "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info);

if (!empty($error)) {
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $error);
  $page->add_module("middle", "top", $msg_tpl->fetch());
}

$css_array = get_network_css();
if (is_array($css_array)) {
  foreach ($css_array as $key => $value) {
    $page->add_header_css($value);
  }
}

$css_data = inline_css_style();
if (!empty($css_data['newcss']['value'])) {
  $css_data = '<style type="text/css">'.$css_data['newcss']['value'].'</style>';
  $page->add_header_html($css_data);
}
echo $page->render();
?>
