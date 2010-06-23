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
include_once("web/includes/page.php");

require_once 'api/TekVideo/TekVideo.php';

if($_GET['status'] == 'TRUE') {
  $network_extra = unserialize(PA::$network_info->extra);
  $new_video = new TekVideo();
  $new_video->file_name = $_GET['video_id'];
  $new_video->author_id = PA::$login_uid;
  $new_video->title = (!empty($_GET['title'])) ? $_GET['title'] : '';
  $new_video->body = (!empty($_GET['description'])) ? $_GET['description'] : '';
  $new_video->email_id = PA::$user->email;
  $new_video->allow_comments = 1;
  if (!empty($_GET['group_id'])) {
  	$perm = 1;
  	$album = $_GET['group_id'];
  } else {
  	$perm = (!empty($_GET['video_perm'])) ? $_GET['video_perm'] : 1;
  	// get the actual album for this user!!!
    $video_albums = Album::load_all(PA::$login_uid, VIDEO_ALBUM);
    if (!empty($video_albums[0])) {
    	$album = $video_albums[0]['collection_id'];
    } else {
    	// we need to create one
	    $new_al = new Album(VIDEO_ALBUM);
  	  $new_al->author_id = PA::$login_uid;
  	  $new_al->type = 2;
  	   
  	  $new_al->description = $new_im_al->name = $new_al->title =
    	PA::$config->default_album_titles[VIDEO_ALBUM];
    	$new_al->save();
  	  $album = $new_al->collection_id;
		}
  }
  $new_video->video_perm = $new_video->file_perm =  $perm;
  $new_video->parent_collection_id = $album;
  $new_video->save();
  $default_icon = uihelper_resize_mk_img(null, 86, 92, 'images/default_video.png', "", RESIZE_CROP);
  $content_url = PA::$url . "/" . FILE_MEDIA_FULL_VIEW . "?cid=$new_video->content_id";
// echo "<pre>".print_r($new_video,1)."</pre>";
?>
<p><b>Video was uploaded successfully.</p>
<script>
/*
    NOTE: this code added by Z.Hron
    BOF - forward media data to the parent form (if exists)
*/

function create_hidden_tag(tag_name, tag_id, tag_value) {
  var elem = document.createElement('input');
  elem.setAttribute("type", 'hidden' );
  elem.setAttribute("name", tag_name );
  elem.setAttribute("id", tag_id );
  elem.setAttribute("value", tag_value );
  return elem;
}
var parent_form = parent.document.forms[0];

if(parent_form != 'undefined') {
  var cid_tag = parent.document.getElementById('media_cid');
  if(cid_tag) {
    cid_tag.value = '<?=$new_video->content_id?>';
  } else {
    cid_tag = create_hidden_tag('media[cid]', 'media_cid', '<?=$new_video->content_id?>');
    parent_form.appendChild(cid_tag);
  }
  var type_tag = parent.document.getElementById('media_type');
  if(type_tag) {
    type_tag.value = 'video';
  } else {
    type_tag = create_hidden_tag('media[type]', 'media_type', 'video');
    parent_form.appendChild(type_tag);
  }
  var value_tag = parent.document.getElementById('media_file');
  if(value_tag) {
    value_tag.value = '<?=$new_video->video_id?>';
  } else {
    value_tag = create_hidden_tag('media[file]', 'media_file', '<?=$new_video->video_id?>');
    parent_form.appendChild(value_tag);
  }
  var url_tag = parent.document.getElementById('media_url');
  if(url_tag) {
    url_tag.value = '<?=$content_url?>';
  } else {
    url_tag = create_hidden_tag('media[url]', 'media_url', '<?=$content_url?>');
    parent_form.appendChild(url_tag);
  }
  var icon_div = parent.document.getElementById('media_icon');
  if(icon_div) {
    icon_div.innerHTML = '<a href="<?=$content_url?>" alt="<?=$new_video->title?>"><?=$default_icon?></a>';
  }
}

/* EOF - forward media data to the parent form (if exists) */

if(typeof parent.video_success == 'function') {
  parent.video_success('<?=$_GET['video_id']?>');
}
</script>
<?php } else {
?>
<p><b>There was an error uploading your video, please try again later.</b></p>
<script>
if(typeof parent.video_failure == 'function') {
  parent.video_failure('Video upload failed, please try again.');
}
</script>
<?php
 }
?>
