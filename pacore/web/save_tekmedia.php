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
$use_theme = 'Beta';//TODO : Remove this when new UI is completely implemented.
include "includes/page.php";
//require_once PA::$path.'';
require_once 'ext/TekVideo/TekVideo.php';
require_once 'api/Activities/Activities.php';
require_once 'web/includes/functions/auto_email_notify.php';
global $network_prefix, $domain_suffix, $activities_array;
if($_GET['status'] == 'TRUE') {
  // Code when video is successfully uploaded into Tekmedia
  // Now we have to save this video-id into our database
  $network_details = NULL;
  $redirect_url = PA::$url;
  $network_extra = unserialize(PA::$network_info->extra);
  $new_video = new TekVideo();
  $new_video->file_name = $_GET['video_id'];
  $new_video->author_id = PA::$login_uid;
  $new_video->title = $_GET['title'];
  $new_video->body = $_GET['description'];
  // Removing the album concept
  $new_video->email_id = PA::$user->email;
  if(empty($_GET['album_video']) || !empty($_GET['new_album_video'])) {
    $alb_type = VIDEO_ALBUM;
    $new_im_al = new Album($alb_type);
    $new_im_al->author_id = $uid;
    $new_im_al->type = 2;
    $album_title = !empty($_GET['new_album_video']) ? $_GET['new_album_video']: PA::$config->default_album_titles[$alb_type];
    $new_im_al->title = $album_title;
    $new_im_al->name = $album_title;
    $new_im_al->description = $album_title;
    $new_im_al->save();
    $new_video->parent_collection_id = $new_im_al->collection_id;
  } else {
    $new_video->parent_collection_id = $_GET['album_video'];
  }
  $new_video->allow_comments = 1;
  if (!empty($_GET['group_id'])) { 
  	$perm = 1;
  } else {
  	$perm = (!empty($_GET['video_perm'])) ? $_GET['video_perm'] : 1;
  }
  $new_video->video_perm = $new_video->file_perm =  $perm;
  $new_video->save();

  $tag_array = NULL;
  if(!empty($_GET['tag'])) {
    $tag_array = Tag::split_tags ($_GET['tag']);
    Tag::add_tags_to_content($new_video->content_id, $tag_array);
  }
  global $config_site_name;
  $uploaded = TRUE;
  $media_owner_image = uihelper_resize_mk_user_img(PA::$user->picture, 80, 80,'alt="'.PA::$user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
  $params['first_name'] = PA::$user->first_name;
  $params['user_id'] = PA::$login_uid;
  $params['user_image'] = $media_owner_image;
  $params['cid'] = $new_video->content_id;
  $params['media_title'] = $new_video->title;
  $params['content_url'] = $redirect_url.'/'.FILE_CONTENT.'?cid='.$new_video->content_id;
  $params['config_site_name'] = $config_site_name;
  $params['media_full_view_url'] = $redirect_url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$new_video->content_id;
  $params['content_moderation_url'] = $redirect_url.'/'.FILE_NETWORK_MANAGE_CONTENT;
  $params['network_name'] = PA::$network_info->name;
//   auto_email_notification('media_uploaded', $params, $network_details);

  $moderation = FALSE;
  if(!empty($_GET['group_id'])) {
    $activity = 'group_video_upload';//for rivers of people
    $location = $redirect_url.'/media/gallery/Videos/view=groups_media&gid='.$_GET['group_id'];
    $params['media_full_view_url'] = $redirect_url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$new_video->content_id;
    $group = new Group();
    $group->load((int)$_GET['group_id']);
    if($group->reg_type == REG_MODERATED && !in_array(PA::$login_uid, $group->moderators)) {
      $moderation = TRUE;
      $params['media_full_view_url'] = $redirect_url.'/group_moderation.php?view=content&gid='.$_GET['group_id'];
    }
  } else {
    $activity = 'user_video_upload';//for rivers of people
    $location = $redirect_url.'/media/gallery/Videos/uid='.PA::$login_uid;
  }
  $task = Roles::check_permission_by_value(PA::$login_uid, 'manage_content');
  if ((($network_extra['network_content_moderation'] == NET_YES && $network_details->owner_id != PA::$login_uid) || $moderation) && PA::$login_uid != SUPER_USER_ID) {
    Network::moderate_network_content($new_video->parent_collection_id, $new_video->content_id); 
    // is_active = 2 for unverified content
//     $location .= 1005;
  } else {
    //To send mail to all the members of the organization
    $params['content_type'] = 'Video';
    $params['content_title'] = $new_video->title;
    $params['first_name'] = PA::$user->first_name;
    $params['content_url'] = $redirect_url.'/'.FILE_MEDIA_FULL_VIEW.'?type=Videos&display=videos&cid='.$new_video->content_id.'&uid='.PA::$login_uid;
    $params['nid'] = PA::$network_info->network_id;
    //To show this video in recent in system log
    $activity = 'user_video_upload';//for rivers of people
    $activity_extra['info'] = (PA::$user->first_name.' uploaded a image in album id = 
		      '.$new_video->parent_collection_id);
    $activity_extra['content_id'] = $new_video->content_id;
    $extra = serialize($activity_extra);
    Activities::save(PA::$login_uid, $activity, $new_video->content_id, $extra,$activities_array);
//     $location .= 2003;
  }
  // Here we redirect with successful messages
  header("Location: $location");
  exit;
 } else {
  $redirector = PA::$url.'/media/gallery/Videos/uid='.PA::$login_uid;
  header("Location: $redirector");
  exit;
 // Code when video is not successfully uploaded
 }


?>