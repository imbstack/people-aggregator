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
require_once "api/Links/Links.php";
require_once "api/User/User.php";
require_once "api/User/Registration.php";
require_once "web/includes/constants.php";
require_once "web/includes/image_resize.php";
require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Validation/ProfanityFilter.php";
require_once "web/includes/functions/user_page_functions.php";
require_once "api/Permissions/PermissionsHandler.class.php";

define('DEFAULT_RELATIONSHIP_TYPE', 2);

// this is the central hook for any user generated output
// *anything* for display should go through here
function _out($html) {
  return (ProfanityFilter::filterHTML($html));
}

// lookuo the display_names for a list of login_names
function uihelper_lookupnames($names) {
	$return = array();
	$logins = preg_split("/,\s*/", $names);
	foreach ($logins as $i=>$login_name) {
		try {
			$u = new User();
			$u->load($login_name);
			$return[] = $u->display_name;
		} catch (Exception $e) {
			$return[] = $login_name;
		}
	}
	return implode(", ", $return);
}

function uihelper_plural($number, $singular, $zerotext=NULL, $plural=NULL) {
  if (!$plural) $plural = $singular . "s";
  // 0
  if (!$number) {
    if ($zerotext) return $zerotext;
    return "No $plural";
  }
  // 1
  if ($number == 1) return "1 $singular";
  // > 1
  return "$number $plural";
}

// get the url to a user's homepage
function uihelper_user_url($user_or_id) {
  if ($user_or_id instanceof User) {
    $user_or_id = $user_or_id->user_id;
  }
  return PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_or_id;
}

function uihelper_upload_gallery($uid, $_POST, $_FILES, $type, $k=0) {

  require_once "api/User/User.php";
  require_once "api/Tag/Tag.php";
  require_once "api/Album/Album.php";
  require_once "api/Image/Image.php";
  require_once "api/Audio/Audio.php";
  require_once "api/Video/Video.php";
  require_once "web/includes/classes/file_uploader.php";

  $logged_in_user = get_login_user();
  $error = false;
  $error_file = NULL;
  $uploaded = False;
  $title = NULL;
  $album_id = NULL;
  $user = new User();
  $media_count_no = $k;

  if ($type=='') {
    $file_type = "image";
    $alb_type = IMAGE_ALBUM;
    $new_img = new Image();
    $perm = 'image_perm';
    $new_im_al = new Album($alb_type);
  }
  elseif ($type=='_audio') {
    $file_type = "audio";
    $alb_type = AUDIO_ALBUM;
    $new_img = new Audio();
    $perm = 'audio_perm';
    $new_im_al = new Album($alb_type);
  }
  elseif ($type=='_video') {
    $file_type = "video";
    $alb_type = VIDEO_ALBUM;
    $new_img = new Video();
    $perm = 'video_perm';
    $new_im_al = new Album($alb_type);
  } else {
    throw new PAException(INVALID_ID, "Invalid album type '$type'");
  }

  $file_name_dynamic = "userfile$type"."_"."$k";
  if (!empty($_FILES[$file_name_dynamic]['name'])) {
    //file uploading start

    $file_name_dynamic_type = $file_name_dynamic; //"$file_name_dynamic"."$type";
    $newname = $_FILES[$file_name_dynamic_type]['name'];

    $uploadfile = PA::$upload_path.basename($_FILES[$file_name_dynamic_type]['name']);

    $myUploadobj = new FileUploader(); //creating instance of file.
    $image_type = "$file_type";
    $value= $file_name_dynamic_type;
    $file = $myUploadobj->upload_file(PA::$upload_path, $value, true, true, $image_type);
    $msg = NULL;
    if( $file == false) {
      $msg = $myUploadobj->error;
      $error = TRUE;
    }
    else {
      $new_img->file_name = "$file";
      $error_file = FALSE;
    }
  }
  else if (empty($_FILES[$file_name_dynamic]['name']) && !empty($_POST['userfile'.$type.'_url_'.$k])) {
    $remote_url = $_POST['userfile'.$type.'_url_'.$k];
    if (!strstr($remote_url, "http://")) {
      $remote_url = "http://".$remote_url;
    }
    $new_img->file_name = $remote_url;
  } else {
    $error = TRUE;
  }

  // file uploading end

  if ($error != TRUE) {
    try {
      $user->load((int)$uid);
      $action = (!empty($_GET['action'])) ? $_GET['action'] : 'upload';
      $colls = Album::load_all($uid, $alb_type);
      if (isset($_POST['submit'.$type]) && ($action != 'delete') && ($error_file == FALSE)) {

        $new_img->author_id = $uid;
        if ($type=='_audio') {
          $new_img->type = AUDIO;
        }
        elseif ($type=='_video') {
          $new_img->type = VIDEO;
        }
        else {
          $new_img->type = IMAGE;
        }
        if (!($_POST['caption'.$type][$k])) {
          $ext = explode(".",$newname);
          $_POST['caption'.$type][$k] = $ext[0];
        }
        $new_img->title = stripslashes(trim($_POST['caption'.$type][$k]));
        //$new_img->title = strip_tags($new_img->title);

        $new_img->excerpt = stripslashes(trim($_POST['caption'.$type][$k]));
        $new_img->excerpt = strip_tags($new_img->excerpt);
        if (empty($_POST['body'.$type][$k])) {
          $new_img->body = '';
          $new_img->body = strip_tags($new_img->body);
        }
        else {
          $new_img->body = stripslashes(trim($_POST['body'.$type][$k]));
          $new_img->body = strip_tags($new_img->body);
        }
        $new_img->file_perm = (!empty($_POST[$perm][$k])) ? $_POST[$perm][$k] : 1;
        $new_img->allow_comments = 1;
        if (!empty($_POST['new_album'.$type])) {
          global $new_album_id;
          if ($k==0) {
            $new_im_al->author_id = $uid;
            $new_im_al->type = 2;
            $new_im_al->title = $_POST['new_album'.$type];
            $new_im_al->name = $_POST['new_album'.$type];
            $new_im_al->description = $_POST['new_album'.$type];

            $new_im_al->save();
            $new_album_id = $new_im_al->collection_id;
            $new_img->parent_collection_id = $new_im_al->collection_id;
            $new_img->save();
            $album_id = $new_album_id;
          }
          else if ($k>0) {
            $new_img->parent_collection_id = $new_album_id;
            $new_img->save();
            $album_id = $new_album_id;
          }
        }
        else {
          if (empty($colls)) {
            $new_im_al->author_id = $uid;
            $new_im_al->type = 2;
            $default_album = str_replace("_", "", $type);
            $new_im_al->title = "My $default_album album";
            $new_im_al->name = "My $default_album album";
            $new_im_al->description = "My $default_album album";
            try {
              $new_im_al->save();
            }
            catch (PAException $e) {
              $msg = "$e->message";
              $error = TRUE;
            }
            $new_img->parent_collection_id = $new_im_al->collection_id;
            $album_id = $new_img->parent_collection_id;
          }
          else {
            foreach ($_POST as $k=>$v) {
              if ($k == 'album'.$type) {
                $albu_ids = $v;
              }
            }
            if(!isset($albu_ids) || empty($albu_ids)) {
              $albu_ids = $colls[0]['collection_id'];
            }
            $new_img->parent_collection_id = $albu_ids;
            $album_id = $albu_ids;
          }
          try {
            $new_img->save();
          }
          catch (PAException $e) {
            $msg = "$e->message";
            print $msg;
            $error = TRUE;
          }
        }

        if(!empty($_POST['tags'.$type][$media_count_no])) {
          $tag_array = Tag::split_tags ($_POST['tags'.$type][$media_count_no]);
          Tag::add_tags_to_content($new_img->content_id, $tag_array);
        }
      }
      else {
        throw new PAException(USER_NOT_FOUND , 'unable to upload file.');
      }
      if ($msg) {
        $uploaded = FALSE;
      }
      else {
        $uploaded = TRUE;
        if(isset($_REQUEST['gid'])) {
          $mail_type = "group_media_uploaded";
          $new_img->group_id = $_REQUEST['gid'];
        } else {
          $mail_type = "media_uploaded";
        }
        PANotify::send($mail_type, PA::$network_info, PA::$login_user, $new_img);

/*  - Replaced with new PANotify code

        $_content_url = PA::$url . PA_ROUTE_CONTENT . '/cid='.$new_img->content_id .'&login_required=true';
        $_media_full_view_url = PA::$url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$new_img->content_id .'&login_required=true';
        if(isset($_REQUEST['gid'])) {
          $_content_url .= '&gid='.$_REQUEST['gid'];
          $_media_full_view_url .= '&gid='.$_REQUEST['gid'];
        }
        $media_owner_image = uihelper_resize_mk_user_img($logged_in_user->picture, 80, 80,'alt="'.$logged_in_user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
        $params['first_name'] = $logged_in_user->first_name;
        $params['user_id'] = $logged_in_user->user_id;
        $params['user_image'] = $media_owner_image;
        $params['cid'] = $new_img->content_id;
        $params['media_title'] = $new_img->title;
        $params['content_url'] = '<a href="' . PA::$url . PA_ROUTE_CONTENT . '/cid='.$new_img->content_id . '">' . $new_img->title .'</a>';
        $params['config_site_name'] = PA::$site_name;
        $params['media_full_view_url'] = '<a href="' . PA::$url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$new_img->content_id .'&login_required=true">' . PA::$url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$new_img->content_id .'</a>';
        $params['content_moderation_url'] = '<a href="' . PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT . '">' . PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT .'</a>';
        auto_email_notification('media_uploaded', $params );
*/
      }
    }
    catch (PAException $e) {
      $msg = "$e->message";
      $error = TRUE;
    }
  }
  $array_of_error_message = array($error, $msg, $error_file, $uploaded, $new_img->content_id, $title,'album_id'=>$album_id);
  return $array_of_error_message;
}

function uihelper_generate_center_content($cid, $permalink=0, $show=0) {
  global $app;

  $content_tpl = array('Question');
  if ( $permalink == 1 ) {
    $permalink_content = uihelper_generate_center_content_permalink($cid,$show);
    return $permalink_content;
  }
  //if we are in network then cached file's id should have content as well as network id
  if(PA::$network_info) {
    $nid = '_network_'.PA::$network_info->network_id;
  } else {
    $nid='';
  }
  //unique name
  $cache_id = 'content_'.$cid.$nid.PA::$language;
  $middle_content = & new CachedTemplate($cache_id);
  //if this file is not cached then generate one for this
  if (!$middle_content->is_cached()) {
    $image_media_gallery = $audio_media_gallery = $video_media_gallery = FALSE;
    $back_page = PA::$url . $app->current_route;
    $content = Content::load_content((int)$cid, (int)@PA::$login_uid);
    // sanity rulez
    if (empty($content)) {
    	// echo "<hr>cid $cid doesn't exist<hr>"; 
    	return '';
    }

    // filter content filelds for output
    $content->title = _out($content->title);
    $content->body = _out($content->body);

    $content_url = PA::$url . PA_ROUTE_CONTENT . "/cid=$content->content_id";
    $content->title = '<a href="'.$content_url.'" >' . $content->title . '</a>';

    if (strstr($back_page, PA_ROUTE_CONTENT)) {
      if($content->parent_collection_id > 0) {
        // IF permalink content is a group content redirect to group homepage
        $back_page = PA::$url  . PA_ROUTE_GROUP . "/gid=" . $content->parent_collection_id;

      } else {
        //if coming from permalink page then redirect to user page
        $back_page = PA::$url . PA_ROUTE_USER_PUBLIC . "/" .$content->author_id;
      }
    }
    $back_page = urlencode($back_page);
    if(!$content->is_html) {
      $content->body = nl2br($content->body);
    }


    if( trim($content->type)=='Image' ){
      $image_media_gallery = TRUE;
    }
    if( trim($content->type)=='Audio' ){
      $audio_media_gallery = TRUE;
    }
    if( trim($content->type)=='Video' ){
      $video_media_gallery = TRUE;
    }
    if (isset(PA::$login_uid) && PA::$login_uid == $content->author_id) {
      $editable = TRUE;
    }
    $content->no_of_comments = Comment::count_comments_for_content($cid);
    $content->no_of_trackbacks = Content::count_trackbacks_for_content($cid);
    $content->trackback_url = PA::$url . "/pa_trackback.php?cid=".$cid;
    $content_user = new User();
    $content_user->load((int)$content->author_id);
    $content->author_name = '<a href= "' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $content_user->user_id .
                            '">' . chop_string($content_user->display_name, 20) . '</a>';
    $content->create_time = PA::date($content->changed, 'long'); // date("l, F d, Y", $content->changed);
    $tags = Tag::load_tags_for_content($cid);
    if($tags) {
      $t = array();
      for($i = 0;$i<count($tags);$i++) {
        $name = _out($tags[$i]['name']);
        $uid = PA::$login_uid;
        $url = PA::$url.'/'.FILE_TAG_SEARCH.'?name_string=content_tag&keyword='.$tags[$i]["name"];
        $t[] = "<a href=$url>".$name."</a>";
      }
      $tag_string = "<b>".__("Tags:")." </b>".implode(", ", $t);
    }
    else {
      $tag_string = "";
    }

    $content->tag_entry = $tag_string;

    if (property_exists(get_class($content), 'sbname')) {
      if (substr($content->sbname, 0, 5) == 'event') {
        $content->type = 'SBEvent'; // need to
      }
      if (substr($content->sbname, 0, 6) == 'review') {
        $content->type = 'Review';
      }
      if (substr($content->sbname, 0, 11) == 'media/audio') {
        $content->type = 'Audio';
      }
      if (substr($content->sbname, 0, 11) == 'media/video') {
        $content->type = 'Video';
      }
      if (substr($content->sbname, 0, 11) == 'media/image') {
        $content->type = 'Image';
      }
      if (substr($content->sbname, 0, 14) == 'showcase/group') {
        $content->type = 'GroupShowCase';
      }
      if (substr($content->sbname, 0, 15) == 'showcase/person') {
        $content->type = 'PersonShowCase';
      }
    }

    // replace magic strings
    $content->replace_percent_strings(PA::$url);

    /* Permalink and edit links for content */
    $perma_link = PA::$url . PA_ROUTE_PERMALINK . "/cid=" .$content->content_id;

    $middle_content->set_object('contents', $content);
    //TODO: gaurav: I am setting this to FALSE because for some reason edit links were appearing on other peoples posts also
    $middle_content->set('editable', FALSE);
    $middle_content->set('permalink', $perma_link);
    $middle_content->set('outer_block_id', 'outer_block_'.$content->content_id);
    $middle_content->set('inner_block_id', 'inner_block_'.$content->content_id);

    $middle_content->set('user_name', $content_user->login_name);
    $middle_content->set('current_theme_path', PA::$theme_url);
    $middle_content->set('back_page', $back_page);

    $middle_content->set('image_media_gallery', $image_media_gallery);
    $middle_content->set('audio_media_gallery', $audio_media_gallery);
    $middle_content->set('video_media_gallery', $video_media_gallery);
    if ($show == 1) {
      $middle_content->set('show', $show);
    }
    $return_content = '';
    if(!in_array($content->type, $content_tpl))
    $return_content = $middle_content->fetch_cache(CURRENT_THEME_FSPATH.'/'.$content->type.".tpl");
  } else {//this will load the file with cache id
    //it means there is already file which is cached
    $return_content = $middle_content->fetch_cache();
  }
  return $return_content;
}


function uihelper_generate_center_content_permalink($cid, $show=0) {
  global $app;
  $image_media_gallery = FALSE;
  $back_page = PA::$url . $app->current_route;
  $content = Content::load_content((int)$cid, (int)PA::$login_uid);
  // filter content fields for output
  $content->title = _out($content->title);
  $content->body = _out($content->body);

  if (strstr($back_page, PA_ROUTE_CONTENT) || strstr($back_page, PA_ROUTE_PERMALINK)) {
    if($content->parent_collection_id > 0) {
      $collection = ContentCollection::load_collection((int)$content->parent_collection_id, PA::$login_uid);
      if ($collection->type==GROUP_COLLECTION_TYPE) {
        $back_page = PA::$url  . PA_ROUTE_GROUP . "/gid=".$content->parent_collection_id;
      } else {
        $back_page = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=" . $content->author_id;
      }
      // IF permalink content is a group content redirect to group homepage
    } else {
      //if coming from permalink page then redirect to user page
      $back_page = PA::$url . PA_ROUTE_USER_PRIVATE;
    }
  }
  $moderateduser = Group::is_admin((int)$content->parent_collection_id, (int)PA::$login_uid) ? 1 : 0;
  $back_page = urlencode($back_page);
  if(!$content->is_html) {
    $content->body = nl2br($content->body);
  }

  $media_gallery_content = NULL;
  $media_gallery_content = in_array(trim($content->type), array('Image', 'Audio', 'Video'));
  $editable = (PA::$login_uid == $content->author_id || $moderateduser);
  $comments = Comment::get_comment_for_content($cid, '', 'ASC');

  $number_of_comments = count($comments);
  $content->no_of_comments = $number_of_comments;
  $trackback = Content::get_trackbacks_for_content($cid);
  $number_of_trackbacks = count($trackback);
  $content->no_of_trackbacks = $number_of_trackbacks;
  $content->trackback_url = PA::$url . "/pa_trackback.php?cid=".$cid;
  $content_user = new User();
  $content_user->load((int)$content->author_id);
  $content->create_time = PA::date($content->changed, 'long'); // date("l, F d, Y", $content->changed);
  $tags = Tag::load_tags_for_content($cid);
  if($tags) {
    $t = array();
    for($i = 0;$i<count($tags);$i++) {
      $name = _out($tags[$i]['name']);
      $uid = PA::$login_uid;
      $url = PA::$url.'/'.FILE_TAG_SEARCH.'?name_string=content_tag&keyword='.$tags[$i]["name"];
      $t[] = "<a href=$url>".$name."</a>";
    }
    $tag_string = "<b>Tags : </b>".implode(", ", $t);
  }
  else {
    $tag_string = "";
  }
  $content->tag_entry = $tag_string;
  if (property_exists($content, 'sbname')) {
    if (substr($content->sbname, 0, 5) == 'event') {
      $content->type = 'SBEvent';
    }
    elseif (substr($content->sbname, 0, 6) == 'review') {
      $content->type = 'Review';
    }
    elseif (substr($content->sbname, 0, 11) == 'media/audio') {
      $content->type = 'Audio';
    }
    elseif (substr($content->sbname, 0, 11) == 'media/video') {
      $content->type = 'Video';
    }
    elseif (substr($content->sbname, 0, 11) == 'media/image') {
      $content->type = 'Image';
    }
    elseif (substr($content->sbname, 0, 14) == 'showcase/group') {
      $content->type = 'GroupShowCase';
    }
    elseif (substr($content->sbname, 0, 15) == 'showcase/person') {
      $content->type = 'PersonShowCase';
    }
  }

  // replace magic strings
  $content->replace_percent_strings(PA::$url);

  $type =  $content->type;
  $type=$type.'Permalink';
  // comments
  $comments_list_tpl = & new Template(CURRENT_THEME_FSPATH."/center_comments.tpl");
  $comments_list_tpl->set('current_theme_path', PA::$theme_url);
  $comments_list_tpl->set('comments', $comments);
  $comments_list_tpl->set('author_id', $content->author_id);
  // Setting the variable for the abuse form ...

  $comments_list = $comments_list_tpl->fetch();

  //comment form
  $comment_form_tpl = & new Template(CURRENT_THEME_FSPATH."/comment_form.tpl");
  $comment_form_tpl->set('current_theme_path', PA::$theme_url);
  if (isset(PA::$login_uid)) {
    $user = new User();
    $user->load((int)PA::$login_uid);
    $login_name = $user->login_name;
    $comment_form_tpl->set('name', $login_name);
    $comment_form_tpl->set('login_name', $user->login_name);
  }
  $comment_form_tpl->set('cid', $cid);
  if($content->parent_collection_id > 0) {
    $comment_form_tpl->set('ccid', $content->parent_collection_id);
  }
  // abuse form
  $abuse_form_tpl = & new Template(CURRENT_THEME_FSPATH."/abuse_form.tpl");
  /* Permalink and edit links for content */
  if ( $content->parent_collection_id != -1 ) {
    $perma_link = PA::$url . PA_ROUTE_PERMALINK . "/cid=" .$content->content_id.'&ccid='.$content->parent_collection_id;
  }
  else {
    $perma_link = PA::$url . PA_ROUTE_PERMALINK . "/cid=" .$content->content_id;
  }

  $params = array( 'permissions'=>'edit_content', 'uid'=>PA::$login_uid, 'cid'=>$content->content_id );

  if(PermissionsHandler::can_user(PA::$login_uid, $params)) {
    if( $media_gallery_content ) {
      $edit_link = PA::$url .'/edit_media.php?cid='.$content->content_id;
    }
    else {
      $edit_link = PA::$url ."/post_content.php?cid=".$content->content_id;
    }
    $delete_link = PA::$url . PA_ROUTE_CONTENT . "?action=deleteContent&cid=" . $content->content_id . '&amp;back_page='.$back_page;

    // handle Event separately
    if ($type == "EventPermalink") {
      $edit_link = PA::$url .'/calendar.php?cid='.$content->content_id;
     $delete_link = $edit_link."&delete=1". '&amp;back_page='.$back_page;;
    }

  } else {
    $edit_link = $delete_link = NULL;
  }

  $user_link = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $content->author_id;

  /* Code for Approval and Denial links for a content */
  if( $moderateduser && $content->is_active == 2) {
    $approval_link = PA::$url . PA_ROUTE_PERMALINK . '/cid='.$content->content_id.'&ccid='.$content->parent_collection_id.'&apv=1';
    $denial_link = PA::$url . PA_ROUTE_PERMALINK . '/cid='.$content->content_id.'&ccid='.$content->parent_collection_id.'&dny=1';
  } else {
    $approval_link = $denial_link = NULL;
  }

  // Show comments form to logged in users, only if comments enabled.
  global $comments_disabled;

  // fix by Z.Hron; if group content - only members of group can comment it
  $can_user_comment = true;
  if((isset($_GET['gid'])) && (isset(PA::$login_uid))) {
    $can_user_comment = Group::member_exists((int)$_GET['gid'], PA::$login_uid);
  }

  if (!$comments_disabled && !empty(PA::$login_uid) && $can_user_comment) {
    $comment_form = $comment_form_tpl->fetch();
    $abuse_form = $abuse_form_tpl->fetch();
  } else {
    $comment_form = $abuse_form = NULL;
  }
  $middle_content = & new Template(CURRENT_THEME_FSPATH."/$type.tpl");
  $middle_content->set_object('contents', $content);
  $middle_content->set('editable', $editable);
  $middle_content->set('picture_name', $content_user->picture); //  to set picture name for diplaying in contets
  $middle_content->set('user_id', $content_user->user_id);
  $middle_content->set('user_name', $content_user->first_name.' '.$content_user->last_name);
  $middle_content->set('current_theme_path', PA::$theme_url);
  $middle_content->set('back_page', $back_page);
  $middle_content->set('comments', $comments_list);
  $middle_content->set('comment_form', $comment_form);
  $middle_content->set('abuse_form', $abuse_form);
  $middle_content->set('media_gallery_content', $media_gallery_content);

  if ($show == 1) {
    $middle_content->set('show', $show);
  }

  $middle_content->set('permalink', $perma_link);
  $middle_content->set('edit_link', $edit_link);
  $middle_content->set('approval_link', $approval_link);
  $middle_content->set('denial_link', $denial_link);
  $middle_content->set('delete_link', $delete_link);
  $middle_content->set('user_link', $user_link);

  $return_content = $middle_content->fetch();
  return $return_content;
}

/**
This function is used for loading the information from the get variables
Usage:on forum page for message board
**/

function load_info(){
  $request_info = array();
  if (!empty($_REQUEST['gid'])) {
    $request_info['parent_id'] = $_REQUEST['gid'];
    $request_info['parent_name_hidden'] = 'gid';
    $request_info['parent_type'] = PARENT_TYPE_COLLECTION;
    $obj = new Group();
    $obj->load($_REQUEST['gid']);
    $request_info['header_title'] = stripslashes($obj->title);
  }
  else if (!empty($_REQUEST['mid'])) {
    $request_info['parent_id'] = $_REQUEST['mid'];
    $request_info['parent_name_hidden'] = 'mid';
    $request_info['parent_type'] = PARENT_TYPE_MESSAGE;
    $obj = new MessageBoard();
    $data = $obj->get_by_id($_REQUEST['mid']);
    $request_info['header_title'] = stripslashes($data['title']);
  }
  else if(!empty($_REQUEST['cid'])) {
    $content = Content::load_content((int)$_REQUEST['cid'], (int)PA::$login_uid);
    $ccid = $content->parent_collection_id;
    if($ccid != 0 && $ccid != -1) {//here parent collection 0 is for deleted content and -1 is for home page routed thus checking that its not a group id
      $content_collection = ContentCollection::load_collection((int)$ccid, PA::$login_uid);
      if ($content_collection->type == GROUP_COLLECTION_TYPE) {
        $request_info['parent_id'] = $ccid;
        $request_info['parent_name_hidden'] = 'gid';
        $request_info['parent_type'] = PARENT_TYPE_COLLECTION;
      }
    }
  } else {
    return false;
  }
  return $request_info;
}


// for media gallery post in groups
function uihelper_upload_gallery_for_group($uid, $_POST, $_FILES, $type, $k=0) {

  require_once "api/User/User.php";
  require_once "api/Tag/Tag.php";
  require_once "api/Album/Album.php";
  require_once "api/Image/Image.php";
  require_once "api/Audio/Audio.php";
  require_once "api/Video/Video.php";
  require_once "web/includes/classes/file_uploader.php";
  $logged_in_user = get_login_user();
  $user = new User();
  $media_count_no = $k;
  $error_file = NULL;
  $uploaded = False;

  if ($type=='') {
    $file_type = "image";
    $alb_type = IMAGE_ALBUM;
    $new_img = new Image();
    $new_img->file_perm = @$_POST['image_perm'];
  }
  elseif ($type=='_audio') {
    $file_type = "audio";
    $alb_type = AUDIO_ALBUM;
    $new_img = new Audio();
    $new_img->file_perm = @$_POST['audio_perm'];
  }
  elseif ($type=='_video') {
    $file_type = "video";
    $alb_type = VIDEO_ALBUM;
    $new_img = new Video();
    $new_img->file_perm = @$_POST['video_perm'];
  }

  //file uploading start
  $file_name_dynamic = "userfile$type"."_"."$k";
  $file_name_dynamic_type = $file_name_dynamic; //"$file_name_dynamic"."$type";
  $newname = $_FILES[$file_name_dynamic_type]['name'];

  $uploadfile = PA::$upload_path.basename($_FILES[$file_name_dynamic_type]['name']);

  $myUploadobj = new FileUploader; //creating instance of file.
  $image_type = "$file_type";
  $value= $file_name_dynamic_type;

  $file = $myUploadobj->upload_file(PA::$upload_path,$value,true,true,$image_type);

  if( $file == false) {
    $msg = $myUploadobj->error;
    $error = TRUE;
  } else {
    $new_img->file_name = "$file";
    $error_file = FALSE;
  }
  // file uploading end

  if (empty($error)) {
    try {
      $user->load((int)$uid);
      $action = (!empty($_GET['action'])) ? $_GET['action'] : 'upload';
      $colls = Album::load_all($uid, $alb_type);

      if (isset($_POST['submit'.$type]) && ($action != 'delete') && ($error_file == FALSE)) {
        $new_img->author_id = $uid;
        if ($type=='_audio') {
          $new_img->type = AUDIO;
        }
        elseif ($type=='_video') {
          $new_img->type = VIDEO;
        }
        else {
          $new_img->type = IMAGE;
        }
        if (empty($_POST['caption'.$type][$k])) {
          $ext = explode(".",$newname);
          $_POST['caption'.$type][$k] = $ext[0];
        }
        $new_img->title = stripslashes(trim($_POST['caption'.$type][$k]));
        $new_img->title = strip_tags($new_img->title);
        $new_img->file_perm = ANYONE;
        $new_img->excerpt = stripslashes(trim($_POST['caption'.$type][$k]));
        $new_img->excerpt = strip_tags($new_img->excerpt);
        if (empty($_POST['body'.$type][$k])) {
          $new_img->body = '';
          $new_img->body = strip_tags($new_img->body);
        }
        else {
          $new_img->body = stripslashes(trim($_POST['body'.$type][$k]));
          $new_img->body = strip_tags($new_img->body);
        }

        $new_img->allow_comments = 1;
        $new_img->parent_collection_id = $_POST['group_id'];
        $new_img->save();

        if(!empty($_POST['tags'.$type][$media_count_no])) {
          $tag_array = Tag::split_tags ($_POST['tags'.$type][$media_count_no]);
          Tag::add_tags_to_content($new_img->content_id, $tag_array);
        }
      }
      else {
        throw new PAException(USER_NOT_FOUND , 'unable to upload file.');
      }
      if (!empty($msg)) {
        $uploaded = FALSE;
      }
      else {
        $uploaded = TRUE;
        if(isset($_REQUEST['gid'])) {
          $mail_type = "group_media_uploaded";
          $new_img->group_id = $_REQUEST['gid'];
        } else {
          $mail_type = "media_uploaded";
        }
        PANotify::send($mail_type, PA::$network_info, PA::$login_user, $new_img);

/*  - Replaced with new PANotify code

        $_media_full_view_url = PA::$url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$new_img->content_id .'&login_required=true';
        if(isset($_REQUEST['gid'])) {
          $_content_url .= '&gid='.$_REQUEST['gid'];
          $_media_full_view_url .= '&gid='.$_REQUEST['gid'];
        }
        $media_owner_image = uihelper_resize_mk_user_img($logged_in_user->picture, 80, 80,'alt="'.$logged_in_user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
        $params['first_name'] = $logged_in_user->first_name;
        $params['user_id'] = $logged_in_user->user_id;
        $params['user_image'] = $media_owner_image;
        $params['cid'] = $new_img->content_id;
        $params['media_title'] = $new_img->title;
        //Z.Hron  - fix for Group Media Upload notifications - missing links
        $params['media_full_view_url'] = '<a href="' . PA::$url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$new_img->content_id .'&login_required=true">' . PA::$url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$new_img->content_id .'</a>';
        $params['content_moderation_url'] = '<a href="' . PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT . '">' . PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT .'</a>';
        auto_email_notification('media_uploaded', $params );
*/
      }
    }
    catch (PAException $e) {
      $msg = "$e->message";
      $error = TRUE;
    }
  }
  $array_of_error_message = array(@$error, @$msg, @$error_file, @$uploaded, 'collection_id' => $new_img->parent_collection_id, 'content_id' => $new_img->content_id);
  return $array_of_error_message;
}

function uihelper_add_default_relation($user_id)
{
  return User_Registration::add_default_relation($user_id, PA::$network_info);
}

// for image type is blank, for audio media it is 'audio', for video media it is 'video'
function uihelper_add_default_media($user_id, $type='') {
  return User_Registration::add_default_media($user_id, $type, PA::$network_info);
}

function uihelper_add_default_links ( $user_id ) {
  return User_Registration::add_default_links($user_id);
}
function uihelper_add_default_blog($user_id) {
  return User_Registration::add_default_blog($user_id);
}

// This function is used for addtion the desktop image to  new user setting
function uihelper_add_default_desktopimage($user_id) {
  return User_Registration::get_default_desktopimage($user_id, PA::$network_info);
}
/* Function get the name of page and returns the class names applying in that page only */
function get_class_name ($page_type) {
  $template_class_name = NULL;
  switch ($page_type) {
    case PAGE_GROUP :
    case PAGE_GROUPS_HOME :
    case PAGE_ADDGROUP :
      // $template_class_name = 'class="group"';
      break;

    case PAGE_USER_PUBLIC :
      $template_class_name = '';
      break;
    case PAGE_EDIT_PROFILE :
      $template_class_name = 'class="editmainpage"';
      break;
    case PAGE_CHANGE_PASSWORD:
    case PAGE_MEDIA_GALLERY:
    case PAGE_GROUP_MEDIA_GALLERY:
    case PAGE_GROUP_MEDIA_POST:
    case PAGE_MEDIA_GALLERY_UPLOAD:
      $template_class_name = 'class="total_content"';
      break;
    case PAGE_MEDIA_FULL_VIEW:
      $template_class_name = 'class="wide_content media_full_view"';
      break;
    case PAGE_NETWORK_STATISTICS:
    case PAGE_CONFIGURE_SPLASH_PAGE:
      $template_class_name = 'class="edit_profile"';
      break;
    default :
      $template_class_name = '';
      break;
  }

  return $template_class_name;
}
/* Creating a Function which check that the Given URL of Image is exist or not  also check which type of image is that */
function verify_image_url($image_url) {

  $image_allowed = array('image/gif','image/jpg', 'image/jpeg', 'image/png', 'image/xpm', 'image/bmp');

  if (@fopen($image_url, "r")) {
    $description = getimagesize($image_url);
    if (in_array(strtolower($description['mime']), $image_allowed))
    return TRUE;
  }
  else {
    return FALSE;
  }

}
/**
    Creating a function which manage the User image
    @first argument for the name of image
    @second argument for the action perform on image
    @third is optional argument
  */

function manage_user_desktop_image( $image, $desktop_image_action, $options=NULL) {

  if (!defined("NEW_STORAGE") || !preg_match("|^pa://|", $image)) {
    if (!($image_attrib = @getimagesize(PA::$project_dir . "/web/files/$image"))) {
      if (!($image_attrib = @getimagesize(PA::$core_dir . "/web/files/$image"))) {
        // doesn't exist or not an image
        return NULL;
      }
    }
  }

  list($opts, $repeat) = uihelper_explain_desktop_image_action($desktop_image_action);

  // We use 1024x150 as the size so the header image doesn't shift when we click on the theme selector
  $img_desktop_info = uihelper_resize_img($image, 1024, 150, PA::$theme_rel . "/images/default_desktop_image.jpg", 'alt="Desktop image."', $opts);
  $img_desktop_info['repeat']= $repeat;
  return $img_desktop_info;
}

function uihelper_explain_desktop_image_action($desktop_image_action) {
  // image exists - resize as required
  switch ($desktop_image_action) {

    case DESKTOP_IMAGE_ACTION_TILE :
      $opts = RESIZE_FIT; // shrink or expand the image to fit inside 1016x191, then we'll repeat it from there
      $repeat = 'repeat';
      break;

    case DESKTOP_IMAGE_ACTION_CROP :
      $opts = RESIZE_CROP; // shrink or expand the image to > 1016x191, then crop the center out
      $repeat = 'no-repeat';
      break;

    case DESKTOP_IMAGE_ACTION_STRETCH :
      $opts = RESIZE_STRETCH; // ignore aspect ratio and stretch the image to 1016x191
      $repeat = 'no-repeat';
      break;

    case DESKTOP_IMAGE_ACTION_LEAVE :
      //$opts =  ( $image_attrib[0] >= DESKTOP_MAX_WIDTH && $image_attrib[1] >= DESKTOP_MAX_HEIGHT) ? RESIZE_CROP_NO_EXPAND : RESIZE_FIT_NO_EXPAND;
      $opts = RESIZE_CROP_NO_SCALE;
      $repeat = 'no-repeat';
      break;

    default:
      $opts = RESIZE_CROP;
      $repeat = 'no-repeat';
      break;
  }
  return array($opts, $repeat);
}

// This function return the networks css path
function get_network_css() {

  // TODO intregate with Mothership info
  $result = array();

  $result['network'] = PA::$theme_url . '/network.css';

  if (!empty(PA::$network_info)) {
    $extra = unserialize(PA::$network_info->extra);
    // checking that whether any theme is selected for network or not
    if (empty($extra['network_skin'])) {
      $extra['network_skin'] = 'defaults';//default network skins
    }
    $skin_info = skin_details($extra['network_skin']);
    $css = @$skin_info['networkCssFile'];
    $result[$extra['network_skin'].'_skin'] =
    PA::$theme_url . '/skins/'.$extra['network_skin'].'/'.$css;
  }

  return $result;
}

// This function return the network header image
function get_network_image() {
  $header_image = array();
  $extra = unserialize(PA::$network_info->extra);

  /* When user not selected any image for the Network then header image of the current
  theme will be displayed*/
  if(file_exists(PA::$project_dir ."/web/files/".$extra['basic']['header_image']['name'])) {
    $header_image_file = PA::$project_dir ."/web/files/".$extra['basic']['header_image']['name'];
  } else if(file_exists(PA::$core_dir ."/web/files/".$extra['basic']['header_image']['name'])) {
    $header_image_file = PA::$core_dir ."/web/files/".$extra['basic']['header_image']['name'];
  } else {
    $header_image_file = null;
  }

  if (empty($extra['basic']['header_image']['name']) || ($header_image_file == null)) {
    if (isset($extra['network_skin']) && !empty($extra['network_skin'])) {
      // Skin other than default has been seleted.
      $skin_info = skin_details($extra['network_skin']);
      $header_image = array();
      if (!empty($skin_info['headerImage'])) {
        $header_image['img'] = PA::$theme_url . '/skins/'.$extra['network_skin'].'/images/'.$skin_info['headerImage'];
        $header_image['repeat'] = 'no-repeat';
      }
    } else {
      // Default skin has been selected.
      $header_image['img'] = PA::$theme_url . '/images/header_image.jpg';
      $header_image['repeat'] = 'no-repeat';
    }

  } else {
    /* Handling Croping*/
    if ($image_attrib = getimagesize($header_image_file))
    {
      switch ($extra['basic']['header_image']['option']) {

        case DESKTOP_IMAGE_ACTION_TILE :
          $opts = RESIZE_FIT; // shrink or expand the image to fit inside 1000x191, then we'll repeat it from there
          $repeat = 'repeat';
          break;

        case DESKTOP_IMAGE_ACTION_CROP :
          $opts = RESIZE_CROP; // shrink or expand the image to > 1000x191, then crop the center out
          $repeat = 'no-repeat';
          break;

        case DESKTOP_IMAGE_ACTION_STRETCH :
          $opts = RESIZE_STRETCH; // ignore aspect ratio and stretch the image to 1000x191
          $repeat = 'no-repeat';
          break;

        case DESKTOP_IMAGE_ACTION_LEAVE :
          $opts =  ( $image_attrib[0] >= DESKTOP_MAX_WIDTH && $image_attrib[1] >= DESKTOP_MAX_HEIGHT) ? RESIZE_CROP_NO_EXPAND:RESIZE_FIT_NO_EXPAND;
          $repeat = 'no-repeat';
          break;

        default :
          $opts = RESIZE_CROP;
          $repeat = 'no-repeat';
          break;
      }
      $header_image_temp = uihelper_resize_img($extra['basic']['header_image']['name'], 1024, 191, PA::$theme_rel . "/images/header_image.jpg",'alt="Desktop image."',$opts);
      $header_image['img']= $header_image_temp['url'];
      $header_image['repeat'] = $repeat;
    }


  }
  return $header_image;

}
// This function return the path of skin  header image
function  get_skin_details() {
  $result = array();
  if (!empty(PA::$network_info)) {
    $extra = unserialize(PA::$network_info->extra);
    // checking that whether any theme is selected for network or not
    if (isset($extra['network_skin']) && !empty($extra['network_skin'])) {
      $result['path'] = PA::$theme_url .'/skins/'. $extra['network_skin'] ;
      $result['name'] = $extra['network_skin'];
    } else {
      $result['path'] = PA::$theme_url;
      $result['name'] = 'default';
    }
  } else {
    $result['path'] = PA::$theme_url;
    $result['name'] = '';
  }
  return $result;
}

/**
  * Function used to display value if it is set otherwise its default value will be set.
  * @param: $value: expected value. Will be displayed if it is set.
  * @param: $default: if $value is not set then $default value will be displayed
  */
function field_value($value, $default) {
  return $value = (isset($value)) ? $value : $default;
}

/**
  * Function to get age from birth date.
  * @param $birth_date
  * @param $is_time_stamp  true id $birth_date has time_stamp
  */
function convert_birthDate2Age($birth_date, $is_time_stamp=true) {
  $age = null;
  if ($is_time_stamp) {
    $age = floor((time() - $birth_date)/(86400*365));
  }
  return $age;
}
/**
  * Function to get inline style from network_info or given parameter.
  * @param $inline_data // if we provided outside from the function
  */
function inline_css_style($inline_data=null) {
  if (!empty($inline_data)) {
    $css_data=$inline_data['newcss'];
  } else {
    $extra = unserialize(PA::$network_info->extra);
    $css_data['newcss']['value'] = (!empty($extra['network_style'])) ? $extra['network_style']: '';
  }
  return $css_data;

}
function make_css ($data) {
  if ($data['profile_type'] == 'ui') {
    $ignore = Array('profile_type', 'submit', 'type');
    if (isset($data['submit'])) {
      foreach ($data as $k=>$v) {
        if (! in_array($k, $ignore)) {
          $profile[$k]['name'] = $k;
          $profile[$k]['value'] = $v;
        }
      }
    } else if (isset($data['restore_default'])) {
      foreach ($data as $k=>$v) {
        unset($profile[$k]); // we simply remove the setting if present
      }
    }
  }
  return $profile;
}


function get_user_theme ($uid=null) {

  $current_skin = array();
  $current_skin['css_files'] = array(PA::$theme_url . '/network.css');

  if (!empty($uid)) {
    $user = new User();
    $user->user_id = $uid;
    $skin_theme = $user->get_profile_field("skin", "theme");
    if (empty($skin_theme)) $skin_theme = "defaults";

    $current_skin['skin_path'] = PA::$theme_url . '/skins/'.$skin_theme;
    $skin_info = skin_details($skin_theme);

    if (!empty($skin_info['userheaderImage'])) {
      $current_skin['header_image'] = PA::$theme_url . '/skins/'.$skin_theme.'/images/'.$skin_info['userheaderImage'];
    }

    $current_skin['css_files'][] = PA::$theme_url . '/skins/'.$skin_theme.'/'.$skin_info['userCssFile'];
  }
  return $current_skin;
}

/* This function validate the Module setting data and Return Error in the time of saving */
function  validate_module_setting_data($data) {
  /* check for empty and Numeric values for the module data*/
  $error_msg = '';
  $i=0;
  $orienatation_msg='Orientation is not defined for';
  $stack_error_msg='Invalid stack order for';
  if(!empty($data['mod_left']))
  foreach ($data['mod_left'] as $key => $val) {
    if (empty($data['left_module'][$key]) ) {
      $error_msg_ori .=' '. $val.',<br />';
    }
    if(($data['textfield_for_left'][$key] == NULL) || !is_numeric($data['textfield_for_left'][$key])) {
      $error_msg_stack .='  '. $val.',<br />';
    }
    $i++;
  }

  $j=0;
  if(!empty($data['mod_right']))
  foreach ($data['mod_right'] as $key => $val) {
    if (empty($data['right_module'][$key])) {
      $error_msg_ori .=' '. $val.' ,<br />';
    }
    if(($data['textfield_for_right'][$key]==NULL) || !is_numeric($data['textfield_for_right'][$key])) {
      $error_msg_stack .='  '. $val.' ,<br />';
    }
    $j++;
  }
  $orienatation_msg = empty($error_msg_ori) ?'':$orienatation_msg.substr($error_msg_ori,0,-7).'<br />';
  $stack_error_msg =empty($error_msg_stack) ? '':$stack_error_msg.substr($error_msg_stack,0,-7);
  $error_msg=$orienatation_msg.$stack_error_msg;
  return($error_msg);
}

function get_skins($skin_type='network') {

  $skins_path = '/' . CURRENT_THEME_FSPATH.'/skins';
  $skins_directories = array();
  if(file_exists($core_skins_directory = PA::$core_dir . $skins_path)) {
    $skins_directories[] = $core_skins_directory;
  }
  if((PA::$core_dir != PA::$project_dir) && file_exists($proj_skins_directory = PA::$project_dir . $skins_path)) {
    $skins_directories[] = $proj_skins_directory;
  }

  $skins = array();
  foreach($skins_directories as $skins_dir) {
    $handle = opendir($skins_dir);
    while (false !== ($file = readdir($handle))) {
      if ($file == "." || $file == "..") continue;
      // make sure it's a valid new-style skin
      $config_file_path = $skins_dir.'/'.$file.'/config.xml';
      if (!file_exists($config_file_path)) continue;
      // ignore it if it's been disabled
      if (file_exists("$skins_dir/$file/disabled")) continue;

      $skin_info = skin_details($file);
      switch ($skin_type) {
        case 'network':
          if (!empty($skin_info['networkCssFile'])) {
            $skins[] = array('name'=>$file, 'css'=>$skin_info['networkCssFile'], 'preview'=>$skin_info['previewImage'], 'caption'=>$skin_info['name'],'headerImage'=>$skin_info['headerImageOk']);
          }
          break;
        case 'user':
          if (!empty($skin_info['userCssFile'])) {
            $skins[] = array('name'=>$file, 'css'=>$skin_info['userCssFile'], 'preview'=>$skin_info['previewImage'], 'caption'=>$skin_info['name'], 'headerImage'=>$skin_info['headerImageOk']);
          }
          break;
        case 'group':
          if (!empty($skin_info['groupCssFile'])) {
            $skins[] = array('name'=>$file, 'css'=>$skin_info['groupCssFile'], 'preview'=>$skin_info['previewImage'], 'caption'=>$skin_info['name'], 'headerImage'=>$skin_info['headerImageOk']);
          }
          break;
      }
    }
  }
  $skin_detail = add_default_skin($skins);
  return $skin_detail;
}

/**
* This function will return true or false on basis of whether custom user header is allowed in the theme or not.
* if 'headerImageOk' element in config.xml is 'yes' then custom header is allowed otherwise not
*/
function is_custom_header_allowed($uid) {
  $return = false;
  $current_skin = sanitize_user_data(User::load_user_profile($uid,$uid, 'skin'));

  if (empty($current_skin)) {
    $return = true;
  } else {
    $skin_info = skin_details($current_skin['theme']);
    $flag = $skin_info['headerImageOk'];
    $return = ($flag == 'yes' || $flag == 'yes') ? true : false;
  }
  return $return;
}

/**
* function to get the detail of the skin by reading its config file.
*/
function skin_details($skin_name) {
  $config_file = '/web/'.PA::$theme_rel.'/skins/'.$skin_name.'/config.xml';
  if(file_exists(PA::$project_dir . $config_file)) {
    $config_file = PA::$project_dir . $config_file;
  } else if(file_exists(PA::$core_dir . $config_file)) {
    $config_file = PA::$core_dir . $config_file;
  } else {
    $config_file = null;
  }
  $skin_info = array();
  if ($config_file) {
    $xml_doc = new DomDocument();
    $xml_doc->load($config_file);
    $config_params = array('name', 'previewImage', 'networkCssFile', 'groupCssFile', 'userCssFile', 'headerImage', 'userheaderImage', 'groupheaderImage', 'headerImageOk');
    foreach ($config_params as $param) {
      $node_obj = $xml_doc->getElementsByTagName($param);
      if (
      isset($node_obj->item(0)->nodeValue) &&
      ($node_obj->item(0)->nodeValue != 'none')) {
        $skin_info[$param] = $node_obj->item(0)->nodeValue;
      } else {
        $skin_info[$param] = null;
      }
    }
  }
  return $skin_info;
}

/**
* Funtion to get the group css. Current default css for the group will be returned as functionality for cutomizing group view is not there
*/
function get_group_theme($gid) {

  $group_var = new Group();
  $group_var->collection_id = $gid;
  $group_info = $group_var->get_group_theme_detail();
  $extra = NULL;
  if (isset($group_info['extra'])) {
    $extra = unserialize($group_info['extra']);
  }
  if (empty($extra['theme'])) {
    $skin_var['theme'] = 'defaults';//default group skin
  } else {
    $skin_var['theme'] = $extra['theme'];
  }
  $current_skin = array();
  $current_skin['css_files'] = array(PA::$theme_url . '/network.css');

  $current_skin['skin_path'] = PA::$theme_url . '/skins/'.$skin_var['theme'];
  $skin_info = skin_details($skin_var['theme']);

  $current_skin['header_image'] = PA::$theme_url . '/skins/'.$skin_var['theme'].'/images/'.$skin_info['groupheaderImage'];

  $current_skin['css_files'][] =
  PA::$theme_url . '/skins/'.$skin_var['theme'].'/'.$skin_info['groupCssFile'];

  $current_skin['header_image_allowed'] = ($skin_info['headerImageOk'] == 'yes')? TRUE: FALSE;
  return $current_skin;
}

// set user caption, shout out, header image, etc
function uihelper_set_user_heading($page, $do_theme=TRUE, $userid=NULL) {
  global $uid;

  $uid = (!empty($userid)) ? $userid: $uid;

  // Changing ... if we give User id than it will display the information of that user
  // .. if we not give the id than it displayed (Page users id or login user id )

  if (!empty($userid)) {
    $user = new User();
    $user->load((int)$userid);
  }
  else {
    $user = get_login_user();
    if (!PA::$login_uid) {
      $user = get_page_user();
    }
  }

  if (empty($user)) {
    return;
  }

  if ($do_theme) {
    // put links all theme CSS files in the header
    $theme_details = get_user_theme($uid);
    if (is_array($theme_details['css_files'])) {
      foreach ($theme_details['css_files'] as $key => $value) {
        $page->add_header_css($value);
      }
    }
    $page->header->set('theme_details', $theme_details);

    // see if we have user defined CSS
    // Here we have to load the user
    $usr = new User();
    $usr->user_id = $uid;
    $newcss = $usr->get_profile_field("ui", "newcss");
    if (!empty($newcss)) {
      $usercss = "<style>".$newcss."</style>";
      $page->add_header_html($usercss);
    }
  }

  // get selected general user data, so we can set captions, etc.
  $user_data_general = $user->get_profile_fields(GENERAL, array('user_caption', 'sub_caption', 'user_caption_image', 'desktop_image_action', 'desktop_image_display'));

  // set caption value
  if (!empty($user_data_general['user_caption'])) {
    $caption1 = chop_string($user_data_general['user_caption'], 20);
  } else {
    $caption1 = chop_string($user->first_name." ".$user->last_name, 20);
  }

  $page->header->set('caption1', $caption1);
  $page->header->set('caption2', chop_string(@$user_data_general['sub_caption'], 40));
  $page->header->set('caption_image', $user_data_general['user_caption_image']);
  $page->header->set('desktop_image_action', $user_data_general['desktop_image_action']);
  $page->header->set('display_image', @$user_data_general['desktop_image_display']);
}

/**
* This function is employed to display the array of tags in the string format.
* @param $tag_array: array containing tags eg. array(array('id'=>1, 'name'=>'tekriti'),array('id'=>2, 'name'=>'bbm'))
* @param $tag_link: hyperlink for the tagname
* @param $separator: separator between the tags.
*/

function show_tags($tag_array, $tag_link=FILE_SHOWCONTENT, $separator=", ") {
  // global var $_base_url has been removed - please, use PA::$url static variable

  $tag_string = null;
  if (count($tag_array) > 0) {
    foreach ($tag_array as $tag) {
      if (!is_null($tag_link)) {
        $tag_string .= '<a href="'.PA::$url .'/'.$tag_link.'?tag_id='.$tag['id'].'">'.$tag['name'].'</a>';
        $tag_string .= $separator;
      } else {
        $tag_string .= $tag['name'].$separator;
      }
    }
    $length = strlen($tag_string);
    $tag_string = substr($tag_string, 0, strlen($tag_string) - strlen($separator));
  }
  return $tag_string;
}

/* Call this function when we want to display network theme just before the page->render() */
function uihelper_get_network_style () {
  // for accessing the Page-Render variable
  global $page;
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
}

function uihelper_get_group_style($gid) {
  if (empty($gid)) return uihelper_get_network_style();

  global $page;
  $group_var = new Group();
  $group_var->collection_id = $gid;
  $group_info = $group_var->get_group_theme_detail();
  if(!empty($group_info['extra'])) {
    $extra = unserialize($group_info['extra']);
  }
  $css_array = get_group_theme($gid);
  $css_files = $css_array['css_files'];

  // setting the header image for the group header
  $group_info = ContentCollection::load_collection((int)$gid, PA::$login_uid);

  $page->header->set('caption_image', $group_info->header_image);
  $page->header->set('desktop_image_action', $group_info->header_image_action);
  $page->header->set('display_header_image', $group_info->display_header_image);

  $page->header->set('group_name', $group_info->title);
  $page->header->set('is_admin_member', 1);
  if($css_array['header_image_allowed']) {
    $page->header->set('header_image_allowed', 1);
  }
  // Adding the Css files in the page
  if (is_array($css_files)) {
    foreach ($css_files as $key => $value) {
      $page->add_header_css($value);
    }
  }

  // adding inline css in the page
  if (!empty($extra['style']['newcss'])) {
    $css_data = '<style type="text/css">'.$extra['style']['newcss'].'</style>';
    $page->add_header_html($css_data);
  }

}
/* Function for handling the error message */
function uihelper_error_msg($msg) {
  // for accessing the Page-Render variable
  global $page, $global_form_error;
  if (empty($msg) && empty($global_form_error)) return;

  // Here we check for error message from action.php
  $error = $global_form_error;
  unset($global_form_error);

  if(!empty($error)) {
    $msg = $error;
  }

  if (is_numeric($msg)) {
    $msg_obj = new MessagesHandler();
    $msg = $msg_obj->get_message($msg);
  }

  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg);
  $page->add_module("middle", "top", $msg_tpl->fetch());
}

/* function for adding the default skin at the TOP of skin selector */
function add_default_skin($var) {
  $cnt = count($var);
  if ($cnt > 0) {
    for ($i=0; $i<$cnt; $i++) {
      if ($var[$i]['name'] == 'defaults') {
        $default_theme_array = $var[$i];
        unset($var[$i]);
      }
    }
    array_unshift($var, $default_theme_array);
  }
  return $var;
}

// This function builds actual token by using encoding function such as sha1 using
// expiry time and email_id.
function build_invitation_token($expires, $email_id) {
  $sig = sha1($email_id);
  return "$expires:$email_id:$sig";
}

// This function accepts lifetime and email_id as parameter and returns a token
function get_invitation_token($lifetime, $email_id) {
  $expires = time() + $lifetime;
  return build_invitation_token($expires, $email_id);
}

// This function autheticate the validity of token
function authenticate_invitation_token($token) {
  if (!preg_match('/^([0-9]+):([a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}):([0-9a-f]+)$/', $token, $matches)) {
    throw new PAException(USER_TOKEN_INVALID, "This token is invalid - bad format");
  }
  if (empty($msg)) { // if token doesn't contain invalid formats
    list($_foo, $expires, $email_id, $token1) = $matches;
    $expiry = $expires - time();
    if ($expiry < 0) {
      $msg = 7017;
    } else { // if not expired yet
      $calculated_token = build_invitation_token($expires, $email_id);
      if ($calculated_token != $token) { // if not matched
        $msg = 7019;
      } else { // if matched
        $msg = $email_id;
        return array(TRUE, $msg);
      }
    }
  }
  if (!empty($msg)) { // if some msg is set
    return array(FALSE, $msg);
  }
}

// this function is used when we display the abuse report form ..
function uihelper_create_abuse_from($param) {
  //such as type = comment and id = comment id ;)
  $abuse_form_tpl = & new Template(CURRENT_THEME_FSPATH."/abuse_form.tpl");
  $abuse_form_tpl->set('div_id', $param['div_id']);
  $abuse_form_tpl->set('id', $param['id']);
  $abuse_form_tpl->set('type', $param['type']);
  $abuse_form = $abuse_form_tpl->fetch();
  return $abuse_form;
}

// this function is used when user click on comments
function uihelper_create_comment_form($param) {
  //such as type = comment and id = comment id ;)
  $comment_form_tpl = & new Template(CURRENT_THEME_FSPATH."/comment_form_all.tpl");
  $comment_form_tpl->set('div_id', $param['div_id']);
  $comment_form_tpl->set('id', $param['id']);
  $comment_form_tpl->set('type', $param['type']);
  if (!empty($param['show'])) {
    $comment_form_tpl->set('display', $param['show']);
  }
  if (!empty($param['action'])) {
    $comment_form_tpl->set('action', $param['action']);
  }
  if (!empty($param['module_name'])) {
    $comment_form_tpl->set('module_name', $param['module_name']);
  }
  $comment_form = $comment_form_tpl->fetch();
  return $comment_form;
}


function rating($rating_type, $type_id, $scale=5) {
  require_once 'api/Rating/Rating.php';
  require_once 'api/PA_Rating/PA_Rating.php';
  $return = array('overall'=>null, 'new'=>null);
  $Rating = new Rating();
  $Rating->set_rating_type($rating_type);
  $Rating->set_type_id((int)$type_id);
  $details = $Rating->get_rating();
  if (!empty($details)) {
    foreach ($details as $entity) {
      $stars_count = round(($entity->total_rating/$entity->total_max_rating)*$scale);
      $faded_stars_count = $scale - $stars_count;
      $existing_rating = null;
      for ($cnt = 0; $cnt < $stars_count; ++$cnt) {
        $return['overall'] .= '<img src="'.PA::$theme_url . '/images/star.gif" alt="star" />';
      }
      for ($cnt = 0; $cnt < $faded_stars_count; ++$cnt) {
        $return['overall'] .= '<img src="'.PA::$theme_url . '/images/starfaded.gif" alt="star" />';
      }
    }
  } else {
    for ($cnt = 0; $cnt < $scale; ++$cnt) {
      $return['overall'] .= '<img src="'.PA::$theme_url . '/images/starfaded.gif" alt="star" />';
    }
  }

  $user_rating = 0;
  if (!empty(PA::$login_uid)) {
    $params = array('rating_type'=>$rating_type, 'user_id'=>PA::$login_uid, 'type_id'=>$type_id);
    $user_rating_details = PA_Rating::get($params);
    // FIXME: this might not be set
    $user_rating = @$user_rating_details[0]->rating;
  }

  for ($counter = 1; $counter <= $user_rating; ++$counter) {
    $return['new'] .= '<img class="user_star';
    if ($counter == $user_rating) {
      $return['new'] .= ' current_rating';
    }
    $return['new'] .= '" src="'.PA::$theme_url . '/images/star.gif" alt="star" id="star_'.$type_id.'_'.$counter.'" onmouseover="javascript:toggle_stars.mouseover('.$counter.', '.$type_id.')" onmouseout="javascript:toggle_stars.mouseout('.$counter.', '.$type_id.')" onclick="javascript:toggle_stars.click('.$counter.', '.$type_id.', \''.$rating_type.'\', '.$scale.')" />';
  }

  for (; $counter <= $scale; ++$counter) {
    $return['new'] .= '<img class="user_star" src="'.PA::$theme_url . '/images/starfaded.gif" alt="star" id="star_'.$type_id.'_'.$counter.'" onmouseover="javascript:toggle_stars.mouseover('.$counter.', '.$type_id.')" onmouseout="javascript:toggle_stars.mouseout('.$counter.', '.$type_id.')" onclick="javascript:toggle_stars.click('.$counter.', '.$type_id.', \''.$rating_type.'\', '.$scale.')" />';
  }

  return $return;
}

function activities_message($subject, $object, $type, $extra) {
  // global var $_base_url has been removed - please, use PA::$url static variable
	$msg = '';
  if (!empty($object) && !empty($subject) && !empty($type)) {
    $extra = unserialize($extra);
    $user = new User();
    $user->load($subject, "user_id");
    if(!empty($_REQUEST['debug'])) echo "activity type: $type <br />";
    switch ($type) {
      case 'user_post_a_comment':
        $msg = '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>' . uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> has left a <a href='.$extra['content_url'].'>comment</a> </b>';
        break;
      case 'user_post_a_blog':
        $msg = '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>' . uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> posted a new blog <a href='.$extra['blog_url'].'>'.$extra['blog_name'].'</a> </b>';
        break;
      case 'content_modified':
        $msg = '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>' . uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> modified content <a href='.$extra['blog_url'].'>'.$extra['blog_name'].'</a> </b>';
        break;
      case 'user_image_upload':
        $msg =  '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>' . uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> uploaded a new <a href='.PA::$url .'/media_full_view.php?cid='.$extra['content_id'].'>image</a> </b>';
        break;
      case 'user_video_upload':
        $msg =  '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>' . uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> uploaded a new <a href='.PA::$url .'/media_full_view.php?cid='.$extra['content_id'].'>video</a> </b>';
        break;
      case 'user_audio_upload':
        $msg =  ' <span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>' . uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> uploaded a new <a href='.PA::$url .'/media_full_view.php?cid='.$extra['content_id'].'>audio</a> </b>';
        break;
      case 'user_friend_requested':
        break;
      case 'user_friend_added':
        $user = new User();
        $user->load($subject, "user_id");
        $friend = new User();
        $friend->load($object, 'user_id');
        $msg =  ' <span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <em> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> added  </em> <span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $object . '>'.uihelper_resize_mk_user_img($friend->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <em> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $object . '> '.$friend->display_name.'</a> as friend</em>';
        break;
      case 'user_friend_send_a_message':
        break;
      case 'group_created':
        $msg = '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> created a new group <a href='.$extra['group_url'].'>'.$extra['group_name'].'</a> </b>';
        break;
      case 'group_settings_updated':
        $msg = '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> updated settings for group <a href='.$extra['group_url'].'>'.$extra['group_name'].'</a> </b>';
        break;
      case 'group_joined':
        $msg = '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> joined the group <a href='.PA::$url  . PA_ROUTE_GROUP . '/gid='.$extra['group_id'].'>'.$extra['group_name'].'</a> </b>';
        break;
      case 'group_image_upload':
        $msg =  '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> uploaded a new <a href='.PA::$url .'/media_full_view.php?cid='.$extra['content_id'].'&gid='.$object.'>image</a> in <a href='.PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . '&gid='.$object.'>group gallery</a> </b>';
        break;
      case 'group_video_upload':
        $msg =  '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> uploaded a new <a href='.PA::$url .'/media_full_view.php?cid='.$extra['content_id'].'>video</a> in <a href='.PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS .'&gid='.$object.'>group gallery</a> </b>';
        break;
      case 'group_audio_upload':
        $msg =  '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> uploaded a new <a href='.PA::$url .'/media_full_view.php?cid='.$extra['content_id'].'>audio</a> in <a href='.PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS .'&gid='.$object.'>group gallery</a> </b>';
        break;
      case 'group_post_a_blog':
        $msg = '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>' . uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.'</a> posted a new blog <a href='.$extra['blog_url'].'>'.$extra['blog_name'].'</a> </b>';
        break;
      case 'network_joined':
        break;
      case 'network_created':
        break;
      default:
        if(defined('SHOW_EXTERNAL_ACTIVITY_FEEDS') and SHOW_EXTERNAL_ACTIVITY_FEEDS) {
          if(is_array($extra['info'])) {
             $msg =  '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.' </a></b>';
             foreach($extra['info'] as $key => $value) {
               $msg .= "<p>$value</p>";
             }
             $msg .= ' (external)';
          } else {
             $msg =  '<span> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '>'.uihelper_resize_mk_user_img($user->picture, 20, 20, 'alt="User Picture"') .'</a> </span> <b> <a href=' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $subject . '> '.$user->display_name.' </a></b>'.$extra['info'] . ' (external)';
          }
        }
    }
  }
  return $msg;
}

/**
* This function is employed to display the array of tags in the string format with the Tag URL.
* @param $tag_array: array containing tags eg. array(array('id'=>1, 'name'=>'tekriti'),array('id'=>2, 'name'=>'bbm'))
* @param $tag_link: hyperlink for the tagname
* @param $name_string: content tag name
* @param $separator: separator between the tags.
*/
function show_all_contents_for_tag($tag_array, $tag_link = FILE_TAG_SEARCH, $name_string = 'content_tag',  $separator=", ") {
  $tag_string = null;
  if (count($tag_array) > 0) {
    foreach ($tag_array as $tag) {
      if (!is_null($tag_link)) {
        $tag_string .= '<a href="'.PA::$url.'/'.$tag_link.'?name_string='.$name_string.'&keyword='.htmlspecialchars($tag["name"]).'">'.$tag['name'].'</a>';
        $tag_string .= $separator;
      } else {
        $tag_string .= $tag['name'].$separator;
      }
    }
    $length = strlen($tag_string);
    $tag_string = substr($tag_string, 0, strlen($tag_string) - strlen($separator));
  }
  return $tag_string;
}
function thumbs_rating($rating_type, $type_id, $scale=1) {

  // User can rate any entity . An entity can be User, content, comments etc
  require_once 'api/Rating/Rating.php';
  require_once 'api/PA_Rating/PA_Rating.php';
  $return = array('overall'=>null, 'new'=>null);
  $Rating = new Rating();
  $Rating->set_rating_type($rating_type);
  $Rating->set_type_id((int)$type_id);
  $condition = " rating_type = '$rating_type' AND rating = -1 AND type_id = $type_id ";
  $thumb_down = Rating::get($condition);
  $condition = " rating_type = '$rating_type' AND rating = 1 AND type_id = $type_id ";
  $thumb_up = Rating::get($condition);
  $total = count($thumb_up) + count($thumb_down);
  if ($total > 0) {
    $recommended = (count($thumb_up)/(count($thumb_up) + count($thumb_down)))*100;
    $not_recommended = (count($thumb_down)/(count($thumb_up) + count($thumb_down)))*100;
    //Over all rating is showing count of thumb_up rating . Done for Radio-one pages.
    $overall = 'Recommended:'.count($thumb_up).'<br />';
    $return['overall']= $overall;
  }else {
    $overall = 'Recommended: 0<br />';
    $return['overall']= $overall;
  }
  $user_rating = 0;
  if (!empty(PA::$login_uid)) {
    $params = array('rating_type'=>$rating_type, 'user_id'=>PA::$login_uid, 'type_id'=>$type_id);
    $user_rating_details = Rating::get(NULL, $params);
    $user_rating = @$user_rating_details[0]->rating;
    if(!empty($user_rating)) {
      //  user give the rating to that content
      if($user_rating == 1 )
      $return['new'] = 'Your Recommendation:<img src="'.PA::$theme_url . '/images/rec_yes1.png" alt="star" />';
      else
      $return['new'] = 'Your Recommendation:<img src="'.PA::$theme_url . '/images/rec_no1.png" alt="star" />';
      return $return;
    }
  }
  // image of thumbs up
  $counter = 1;
  $return['new'] = 'Make Recommendation:<b><img src="'.PA::$theme_url . '/images/rec_yes1.png" alt="star" id="star_'.$type_id.'_'.$counter.'" onclick="javascript:thumbs_rating.click('.$counter.', '.$type_id.', \''.$rating_type.'\', '.$scale.','.PA::$login_uid.')" style="cursor:pointer" /></b>';

  // image of thumbs down
  $counter = -1;
  $return['new'] .= '<b><img src="'.PA::$theme_url . '/images/rec_no1.png" alt="star" id="star_'.$type_id.'_'.$counter.'" onclick="javascript:thumbs_rating.click('.$counter.', '.$type_id.', \''.$rating_type.'\', '.$scale.','.PA::$login_uid.')" style="cursor:pointer" /></b>';

  return $return;
}
?>
