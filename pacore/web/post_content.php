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
define("ROUTE_TO_ALL", -1);
define("ROUTE_TO_NONE", -2);
$use_theme = 'Beta';
include_once("web/includes/page.php");

require_once "api/Cache/Cache.php";
require_once "api/Album/Album.php";
// require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Messaging/MessageDispatcher.class.php";
require_once "api/Permissions/PermissionsHandler.class.php";

$authorization_required = TRUE;
//there is specific task assigned to each page
//you can see array of tasks in authorize.inc.php
/*
$page_task = 'post_to_community';//meta networks  post_to_community
require_once "web/includes/authorize.inc.php";
$permission_to_post = $task_perm;//set from authorize.inc.php
*/
$user = get_user();

$_GET = url_decode_all($_GET);
$_POST = url_decode_all($_POST);
$_REQUEST = url_decode_all($_REQUEST);

//filter_all_post($_POST);

// check to see if we are here for edit and user has permissions to do it
if( $cid = @$_REQUEST['cid'] ) {

  $params = array( 'permissions'=>'edit_content', 'uid'=>PA::$login_uid, 'cid'=> $cid );
  if(!PermissionsHandler::can_user(PA::$login_uid, $params)) {
    header("Location: ". PA::$url . PA_ROUTE_HOME_PAGE . "/msg=".urlencode('Error: You are not authorized to access this page.'));
    exit;
  }

  $obj_content_type = Content::load_content((int)$cid, PA::$login_uid);// this content will be used for edit mode
  if($obj_content_type->type == 'BlogPost') {
     unset($_REQUEST["sb_mc_type"]);
  }
  //tells edit mode
  $is_edit = 1;
  $parent_collection_id = $obj_content_type->parent_collection_id;
} else {
  //tells create first time mode
  $cid = 0;
  $is_edit = 0;
}

//take care of content collection in case of ccid
//right now a user can come from groups.php page to create post in that group
// Then it will have ccid associated with it.
// TODO : if user comes from any other collection source to create the post then
// that case will have to be taken in account
// right now it only handles ccid for the group
$ccid = (empty($_REQUEST['ccid']))? -1: $_REQUEST['ccid'];



////functions used in this page block
/* Purpose : This function will route the post to multiple groups which are selected
return type : true
*/

function route2groups() {
  global $user, $is_edit;
  $extra = unserialize(PA::$network_info->extra);
  $tags = preg_split('/\s*,\s*/' , strtolower($_POST['tags']));
  $tags = array_unique($tags);
  $net_owner = new User();
  $net_owner->load((int)PA::$network_info->owner_id);

  //find tag entry
  $terms = array();
  foreach ($tags as $term) {
    $tr = trim($term);
    if ($tr) {
      $terms[] = $tr;
    }
  }
  if (!empty($_POST['route_to_pa_home']) && ($_POST['route_to_pa_home'] == 1)) {
    $display_on_homepage = DISPLAY_ON_HOMEPAGE;//its zero
  } else {
    $display_on_homepage = NO_DISPLAY_ON_HOMEPAGE;//This will not show up on homepage - flag has opposite values
  }
  if (is_array($_POST['route_targets_group'])) {
    if (in_array(-2, $_POST['route_targets_group'])) {//-2 means Select none of group
      // no need to post in any group
    } elseif (in_array(-1, $_POST['route_targets_group'])) {//-1 means select all the groups
      // post in all the groups
      $group_array =  explode(',', $_POST['Allgroups']);
      foreach ($group_array as $gid) {// post to all the groups
        $_group = Group::load_group_by_id((int)$gid);
        $login_required_str = null;
        if($_group->access_type == ACCESS_PRIVATE) {
          $login_required_str = '&login_required=true';
        }
        $res = BlogPost::save_blogpost(0, PA::$login_uid, $_POST['blog_title'], $_POST['description'], NULL, $terms, $gid, $is_active = 1, $display_on_homepage);
        $permalink_cid = $res['cid'];


// NOTE: would this notification message be sent for each group ???
        $content_obj = Content::load_content((int)$permalink_cid);
        PANotify::send("content_posted", PA::$network_info, $user, $content_obj); // notify network owner (maybe group owner would be better?)
        if($display_on_homepage == DISPLAY_ON_HOMEPAGE) {
           PANotify::send("content_posted_to_comm_blog", PA::$network_info, $user, $content_obj);
        }
//-------
        //for rivers of people
        $activity = 'group_post_a_blog';
        $activity_extra['info'] = ($user->first_name.'posted a new blog');
        $activity_extra['blog_name'] =  $_POST["blog_title"];
        $activity_extra['blog_id'] = $permalink_cid;
        $activity_extra['blog_url'] = PA::$url.PA_ROUTE_CONTENT.'/cid='.$permalink_cid . $login_required_str;
        $extra = serialize($activity_extra);
        $object = $gid;

        // update status to unverified
        $group = ContentCollection::load_collection((int)$gid, PA::$login_uid);
        if($group->reg_type == REG_MODERATED) {
          Network::moderate_network_content((int)$gid, $permalink_cid);
        } else if ($extra['network_content_moderation'] == NET_YES && $is_edit == 0 && PA::$network_info->owner_id != $user->user_id) {
          Network::moderate_network_content($gid, $permalink_cid);
        }
        if(!PA::is_moderated_content() && ($group->reg_type != REG_MODERATED)) { //Write to activity log only when moderation is off
          Activities::save($user->user_id, $activity, $object, $extra);
        }
      }
    } else {
      // post in selected groups
      foreach($_POST['route_targets_group'] as $gid) {//only send to selected groups
        $_group = Group::load_group_by_id((int)$gid);
        $login_required_str = null;
        if($_group->access_type == ACCESS_PRIVATE) {
          $login_required_str = '&login_required=true';
        }
        $res = BlogPost::save_blogpost(0, PA::$login_uid, $_POST['blog_title'], $_POST['description'], NULL, $terms, $gid, $is_active = 1, $display_on_homepage);
        $permalink_cid = $res['cid'];

        $content_obj = Content::load_content((int)$permalink_cid);
        PANotify::send("content_posted", PA::$network_info, $user, $content_obj); // notify network owner (maybe group owner would be better?)
        if($display_on_homepage == DISPLAY_ON_HOMEPAGE) {
           PANotify::send("content_posted_to_comm_blog", PA::$network_info, $user, $content_obj);
        }

        //for rivers of people
        $activity = 'group_post_a_blog';
        $activity_extra['info'] = ($user->first_name.'posted a new blog');
        $activity_extra['blog_name'] =  $_POST["blog_title"];
        $activity_extra['blog_id'] = $permalink_cid;
        $activity_extra['blog_url'] = PA::$url.PA_ROUTE_CONTENT.'/cid='.$permalink_cid . $login_required_str;
        $extra = serialize($activity_extra);
        $object = $gid;

        // update status to unverified
        $group = ContentCollection::load_collection((int)$gid, PA::$login_uid);
        if($group->reg_type == REG_MODERATED) {
          Network::moderate_network_content((int)$gid, $permalink_cid);
        } else if ($extra['network_content_moderation'] == NET_YES && $is_edit == 0 && PA::$network_info->owner_id != $user->user_id) {
          Network::moderate_network_content($gid, $permalink_cid);
        }
        if(!PA::is_moderated_content() && ($group->reg_type != REG_MODERATED)) { //Write to activity log only when moderation is off
          Activities::save($user->user_id, $activity, $object, $extra);
        }

      }
    }
  }
  return TRUE;
}



/* Purpose : This function will route the post to multiple groups which are selected
$params is list of parameters in following manner
$params['cid'] = 0;
$params['title'] = 'title';
$params['albums'] = array(0=>1,1=>2);
return type : It will return the array of albums in which this post has been posted
*/

function route2albums($params) {

}

/* Purpose : This function will create new album
$params is list of parameters in following manner
$params['cid'] = 0;
$params['title'] = 'title';
$params['albums'] = array(0=>1,1=>2);
return type : It will create an album or post return the array
*/

function createalbum() {
  $album_type = $_POST['sb_mc_type'];
  $album_type = trim(substr($album_type,6));

  if($album_type == "audio") {
    $alb_type = AUDIO_ALBUM;
    $new_al = new Album($alb_type);

  }
  else if($album_type == "video") {
    $alb_type = VIDEO_ALBUM;
    $new_al = new Album($alb_type);

  }
  else if($album_type == "image") {
    $alb_type = IMAGE_ALBUM;
    $new_al = new Album($alb_type);

  }
  $new_al->author_id = PA::$login_uid;
  $new_al->type = 2;//it means this collection is album 1 is for groups
  $new_al->title = $_POST['new_album'];
  $new_al->name = $_POST['new_album'];
  $new_al->description = $_POST['new_album'];
  $album_save_error="";
  try{
    $new_al->save();
  }
  catch (PAException $e) {
    $album_save_error = "$e->message";
    $alb_error = true;
  }
  if ( $alb_error ) {
    $err = array('error'=>TRUE, 'msg'=>$album_save_error);
    return $err;
  } else {
    $ccid = $new_al->collection_id;
    return $ccid;
  }

}

////end of functions used in this page block



//Whether to route the post to external blogs or not

if(@$_POST['route_targets_external']) {
  $All_external_blog = $_POST['Allexternal_blog'];
  $external_targets = array();
  $external_targets = $_POST['route_targets_external'];
  $count_targets_external = count($external_targets);
} else {
  $route_post = 0;
}

// routing to groups
$internal_targets = array();
$count_targets_internal = 0;
$Allgroup = '';

if (@$_POST['route_targets_group']) {
  $Allgroup = @$_POST['Allgroups'];
  $internal_targets = array();
  $internal_targets = $_POST['route_targets_group'];
  $count_targets_internal = count($internal_targets);
} else {
    $route_post_internal = 0;
}

// Routing to Albums
$Allalbum = '';

if (@$_POST['route_targets_album'] ) {
  $route_to_album = 1;
  $Allalbum = $_POST['all_album'];
  $target_album = $_POST['route_targets_album'];
} else {
  $route_albums = 0;
}

// Routing to PeopleAggregator MyPage
$display_on = 1;
$route_to_pa = FALSE;
if (@$_POST['route_to_pa_home'] == 1) {
  $display_on = 0;
  $route_to_pa = TRUE; // The content will be displayed on the homepage also.
}

// routing to new album
if (@$_POST['new_album']) {
  $new_album = $_POST['new_album'];
}

if (!$is_edit) {
  $user_data_general = array();
  $user_generaldata = User::load_user_profile((int)$_SESSION['user']['id'], (int)$_SESSION['user']['id'], GENERAL);
  for ($i=0; $i<count($user_generaldata); $i++) {
    $name = $user_generaldata[$i]['name'];
    $value = $user_generaldata[$i]['value'];
    $perm_name = $name."_perm";
    $perm_value = $user_generaldata[$i]['perm'];
    $user_data_general["$name"] = $value;
    $user_data_general["$perm_name"] = $perm_value;
  }
  $outputthis_error_mesg = "";
  $show_external_blogs = TRUE;
  $outputthis_username = trim(@$user_data_general['outputthis_username']);
  $outputthis_password = trim(@$user_data_general['outputthis_password']);
  if (empty($outputthis_username)||empty($outputthis_password)) {
    $show_external_blogs = FALSE;
    $outputthis_error_mesg = OUTPUTTHIS_ERROR_MESSAGE;
  } else {
    // fetch outputthis targets (if we don't have them in the ext_cache already)
    $cache_key = "outputthis_targets";
    $targets = Cache::getExtCache(PA::$login_uid, $cache_key);
    if ($targets === NULL) {
      // not cached
      $targets = OutputThis::get_targets($outputthis_username,$outputthis_password);
      if ($targets[0]=='error') {
	$show_external_blogs = FALSE;
	$outputthis_error_mesg = ucfirst($targets[1]);
      } else if (!empty($targets)) {
	// remember them for next time - so we don't have to wait for outputthis on every page view
	Cache::setExtCache(PA::$login_uid, $cache_key, $targets);
      }
    }
  }
  $is_edit = 0;
} else {
   $is_edit = 1;
  // Temporary solution to hide the external routing in edit mode
}

function get_target_name ($target_id) {
  global $targets;

  for($counter = 0; $counter < count($targets); $counter++) {
    if($target_id == $targets[$counter]['ID']) {
      return $targets[$counter]['title'];
    }
  }
}

function route_to_outputthis ($title, $body) {
  global $outputthis_username, $outputthis_password, $error_message;

  $external_targets = $_POST['route_targets_external']; /* Selected external targets array */
  //p($external_targets);
  if (count($external_targets)) { /* User has selected something from external targets */
    if( in_array(ROUTE_TO_NONE, $external_targets) ) {
      $error_message[] = 'Content has not been routed to any of your external blog';
    }
    else if( in_array(ROUTE_TO_ALL, $external_targets )) {
      $external_targets = $_POST['Allexternal_blog']; /* All external targets array */
    }

    for($i = 0; $i < count($external_targets); $i++) {
      $blog_post = array('title'=>$title,'body'=>$body);
      if (!empty($outputthis_username) && !empty($outputthis_password)) {
        $return = OutputThis::send($blog_post, array($external_targets[$i]), $outputthis_username, $outputthis_password);

        if( $return == 1) { /* routing successfull to external blog */
          $error_message[] = 'Post routed successfully to '.get_target_name ($external_targets[$i]);
        }
        else {
          $error_message[] = 'Post routed failed for '.get_target_name ($external_targets[$i]).'. '.$return[1];
        }
      }
    }
  }
}

function setup_module($column, $moduleName, $obj) {
    global $sb_mc_type, $cid,$ccid,$error_array,$err_album_name_exist,$content_type,$sb_types;
    global $display, $is_edit, $album_save_error, $data_array,$error, $sb_mc_type,$targets;
    global $show_external_blogs, $outputthis_error_mesg;
    global $post_err, $permission_to_post, $obj_content_type;
    switch ($column) {

    case 'middle':
            $obj->get_user_albums();
            $obj->author_id = $_SESSION['user']['id'];
						$content_type = 'BlogPost';
						require_once "web/includes/blocks/createcontent.php";
						if(!empty($cid)) {
							$obj->set_id($cid);
						}
						if(strlen($error) > 0) {
							$obj->set_error_msg($error, $data_array);
						}

						$obj->display = 'blog';
            $obj->targets = $targets;
            $obj->show_external_blogs = $show_external_blogs;
            $obj->is_edit = $is_edit;
            $obj->permission_to_post = $permission_to_post;
            $obj->parent_collection_id = @$obj_content_type->parent_collection_id;
            $obj->ccid = ($obj->parent_collection_id) ? $obj->parent_collection_id : $ccid;
      break;
    }
}


$page = new PageRenderer("setup_module", PAGE_POSTCONTENT, "Create content", "container_one_column_postcontent.tpl", "header.tpl", PRI, HOMEPAGE, PA::$network_info);

// load JQzery forms Plugin
$page->add_header_html(js_includes('forms.js'));
// and the modal overlay functions
$page->add_header_html(js_includes('attach_media_modal.js'));
$css_path = PA::$theme_url . '/modal.css';
$page->add_header_css($css_path);

uihelper_error_msg($post_err);
uihelper_get_network_style();
echo $page->render();

?>
