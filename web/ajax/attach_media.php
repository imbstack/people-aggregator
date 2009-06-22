<?php
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "ext/Album/Album.php";
require_once "ext/Image/Image.php";
require_once "ext/Video/Video.php";
require_once "ext/Audio/Audio.php";
require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Activities/Activities.php";
require_once "api/api_constants.php";

$extra = unserialize(PA::$network_info->extra);

$user = PA::$login_user;
$uid = PA::$login_uid;

if (isset($_POST)) {
  filter_all_post($_POST, TRUE);
}

$error_msg = NULL;

if (!empty($_POST)) {
  $media_type = $_POST['media_type'];
  switch ($media_type) {
    case 'image':
      $url_file_field = 'userfile_url_';
      $file_name_dynamic = "userfile_";
      $extention = NULL;
      $caption = $_POST['caption'];
      $_POST['submit'] = "Upload Image";
      break;

    case 'audio':
      $url_file_field = 'userfile_audio_url_';
      $file_name_dynamic = "userfile_audio_";
      $extention = '_audio';
      $caption = $_POST['caption_audio'];
      $_POST['submit_audio'] = "Upload Audio";
      break;

    case 'video':
      $url_file_field = 'userfile_video_url_';
      $file_name_dynamic = "userfile_video_";
      $extention = '_video';
      $caption = $_POST['caption_video'];
      $_POST['submit_video'] = "Upload Video";
      break;
  }
  // count number of parameter enter by the user
  $cnt = count($caption);
  for ($i=0, $file_count=$cnt; $i < $cnt; $i++) {
    $url = @$_POST[$url_file_field.$i];
    $file = @$_FILES[$file_name_dynamic.$i]['name'];
    if ( empty($url) && empty($file) )
      $file_count--;

  }
  $error = NULL;

  if ($file_count != $cnt) {
    $error_msg =sprintf(__("Select any %s file to upload"), __($media_type));
  }

  if (empty(PA::$login_uid)) {
    $error_msg = sprintf(__("You have to login before uploading a %s"), __($media_type));
  }

}

if (!empty($_POST) && empty($error_msg)) {
  $moderation_msg = FALSE;

  for ($k = 0; $k < $cnt; $k++) {
    if (!empty($_REQUEST['group_id'])) {
        $upload = uihelper_upload_gallery_for_group($login_uid, $_POST, $_FILES, $extention, $k);
        $upload['album_id'] = @$upload['collection_id'];
    } else {
        $upload = uihelper_upload_gallery(PA::$login_uid, $_POST, $_FILES, $extention, $k);
        $upload['content_id'] = @$upload[4];
    }

    if ($upload[3] == TRUE) {
      if ($extra['network_content_moderation'] == NET_YES && PA::$network_info->owner_id != PA::$login_uid) {
            $album_id =
                Network::moderate_network_content((int)$upload['album_id'], $upload['content_id']);
            $moderation_msg = TRUE;
      }
      $uploaded = TRUE;
      $success = true;
    } else {
        $error_msg = $upload[1]; //index 1 has the error message during uploading
    }
  }
//  echo "<pre>" . print_r(array($upload, PA::$login_uid, $_POST, $_FILES, $extention, $k),1) . "</pre>"; die();
}

if (isset($success) && ($success == true)) { //
    if (!empty($_REQUEST['group_id'])) {
        $type = $_GET['type'];
        switch ($type) {
            case 'Images':
                $activity = 'group_image_upload';
                $activities_extra['info'] = ($login_name.' uploaded a image in group id ='.$_REQUEST['group_id']);
                $gallery_link = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media&gid=".$_REQUEST['group_id'];
            break;
            case 'Videos':
                $activity = 'group_video_upload';//for rivers of people
                $activities_extra['info'] = ($login_name.' uploaded a video in group id ='.$_REQUEST['group_id']);
                $gallery_link = PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEO . "/view=groups_media&gid=".$_REQUEST['group_id'];
            break;
            case 'Audios':
                $activity = 'group_audio_upload';//for rivers of people
                $activities_extra['info'] = ($login_name.' uploaded a audio in group id ='.$_REQUEST['group_id']);
                $gallery_link = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIO . "/view=groups_media&gid=".$_REQUEST['group_id'];
            break;
            default:
                break;
        }
        $activities_extra['collection_id'] =$upload['collection_id'];
        $activities_extra['content_id'] =$upload['content_id'];
        $extra = serialize($activities_extra);
        $object = $_REQUEST['group_id'];
        Activities::save($login_uid, $activity, $object, $extra);
    } else {
        switch ($_GET['type']) {
            case 'Images':
                $album_id = (!empty($upload['album_id'])) ? $upload['album_id']:$_POST['album'];
                $album = "&album_id=".$album_id;
                $msg_id = ($moderation_msg) ? 1005 : 2001;
                $activity = 'user_image_upload';//for rivers of people
                $activity_extra['info'] = ($login_name.' uploaded a image in album id =
                                                    '.$upload['album_id']);
                $activity_extra['content_id'] = $upload[4];
                $extra = serialize($activity_extra);
                $object = $upload['album_id'];
                Activities::save(PA::$login_uid, $activity, $object, $extra);
                $gallery_link = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=" . PA::$login_uid  . $album;
            break;
            case 'Audios':
                $album_id = (!empty($upload['album_id'])) ? $upload['album_id']: $_POST['album_audio'];
                $album = "&album_id=".$album_id;
                $msg_id = ($moderation_msg) ? 1005 : 2002;
                //for rivers of people
                $activity = 'user_audio_upload';//for rivers of people
                $activity_extra['info'] = ($login_name.' uploaded a audio in album id =
                                                    '.$upload['album_id']);
                $activity_extra['content_id'] = $upload[4];
                $extra = serialize($activity_extra);
                $object = $upload['album_id'];
                Activities::save(PA::$login_uid, $activity, $object, $extra);
                $gallery_link = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/uid=" . PA::$login_uid  . $album;
            break;
            case 'Videos':
                $album_id = (!empty($upload['album_id'])) ? $upload['album_id']: $_POST['album_video'];
                $album = "&album_id=".$album_id;
                $msg_id = ($moderation_msg) ? 1005 : 2003;
                //for rivers of people
                $activity = 'user_video_upload';//for rivers of people
                $activity_extra['info'] = ($login_name.' uploaded a video in album id =                                     '.$upload['album_id']);
                $activity_extra['content_id'] = $upload[4];
                $extra = serialize($activity_extra);
                $object = $upload['album_id'];
                Activities::save(PA::$login_uid, $activity, $object, $extra);
                $gallery_link = PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . "/uid=" . PA::$login_uid  . $album;
            break;
        }
    }
}


if (!empty($error_msg)) {
    _error_msg($error_msg);
} else {

  $cid = (int)$upload['content_id'];
  $content_info = Content::load_content($cid, PA::$login_uid);

  switch ($content_info->type) {
    case 'Image':
        $show_media = new Image();
    break;
    case 'Audio':
        $show_media = new Audio();
    break;
    case 'TekVideo':
        $show_media = new TekVideo();
    break;
    default:
        die("Content ID $cid is non-media (not image, audio, or video)");
    break;
  }
  $show_media->load($cid);
  switch ($content_info->type) {
    case 'Audio':
      if (strstr($show_media->file_name, "http://")) {
        $src = $show_media->file_name;
        $file = $show_media->file_name;
      } else {
        $file = $show_media->file_name;
        $src = Storage::getURL($file);
      }
      $media_value = $show_media->file_name;
      $default_icon = uihelper_resize_mk_img(null, 86, 92, 'images/default_audio.png', "", RESIZE_CROP);
    break;
    case 'TekVideo':
      $image_show = '<script src="'.PA::$tekmedia_site_url.'/Integration/remotePlayer.php?video_id='.$show_media->video_id.'&preroll=true"></script>';
      $default_icon = uihelper_resize_mk_img(null, 86, 92, 'images/default_video.png', "", RESIZE_CROP);
      $media_value = $show_media->video_id;
    break;
    case 'Image':
      if (strstr($show_media->image_file, "http://")) {
        $tt = $show_media->image_file;
          $image_show = getimagehtml($tt, 86, 92, "", $tt);
          $media_value = $show_media->file_name;
      } else {
          $image_show = uihelper_resize_mk_img($show_media->image_file, 86, 92, 'images/default_image.png', "", RESIZE_CROP);
          $default_icon = $image_show;
          $media_value = $show_media->file_name;
      }
    break;
    default:
        die("Content ID $cid is non-media (not image, audio, or video)");
    break;
  }
  $content_url = PA::$url . "/" . FILE_MEDIA_FULL_VIEW . "?cid=$show_media->content_id";
  $content_link = "<a href=\"$content_url\" alt=\"$show_media->title\">$default_icon</a>";
?>
  <input type="hidden" name="media[cid]" id="media_cid" value="<?=$show_media->content_id?>" />
  <input type="hidden" name="media[type]" id="media_type" value="<?=$media_type?>" />
  <input type="hidden" name="media[file]" id="media_file" value="<?=$media_value?>" />
  <input type="hidden" name="media[url]" id="media_url" value="<?=$content_url?>" />
  <script>
    var icon_div = parent.document.getElementById('media_icon');
    if(icon_div) {
      icon_div.innerHTML = '<a href="<?=$content_url?>" alt="<?=$show_media->title?>"><?=$default_icon?></a>';
    }
  </script>
<?php
}

function _error_msg($msg) {
  // for accessing the Page-Render variable
  global $page, $global_form_error;
  if (empty($msg) && empty($global_form_error)) return;
  $error = $global_form_error;
  unset($global_form_error);
  if (!empty($error)) {
    $msg = $error;
  }

  if (is_numeric($msg)) {
    $msg_obj = new MessagesHandler();
    $msg = $msg_obj->get_message($msg);
  }

?>
<div class="error">
    <?=$msg?>
</div>
<? } ?>