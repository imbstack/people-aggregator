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
//TO DO: while saving content save function should be called once
//variable for Blog save should be according to criteria specified

require_once "ext/BlogPost/BlogPost.php";
require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Activities/Activities.php";
require_once "api/api_constants.php";

// echo "_POST <pre>".print_r($_POST,1)."</pre>"; exit;

$user = get_user();
if (isset($_POST['publish']) && $content_type == 'BlogPost') {
  if (!empty($_POST['attach_media_html'])) {
  	$_POST["description"] .= "\n<br clear=\"all\"/><br /><br />\n".$_POST['attach_media_html'];
  }


  /* data_array is used to populate the form with the values in case of error */
  $data_array["blog_title"] = trim($_POST["blog_title"]);

  filter_all_post($_POST);
  $data_array["description"] = trim($_POST["description"]);
  $data_array["tags"] = trim($_POST["tags"]);
  $error = FALSE;
  $post_err = "";

  if(preg_match_all('#<([^>]+)>#i', $data_array["blog_title"], $matches)) {
    $error = TRUE;
    $post_err.= "Title contains illegal HTML code: <br />";
    $found_tags = array();
    foreach($matches[1] as $html_tag) {
      if( 0 !== strpos(trim($html_tag), "/")) {
        $post_err.= htmlspecialchars("<$html_tag>") . "<br />";
      }
    }
  }

  if (empty($data_array["blog_title"])) {
    $error = TRUE;
    $post_err = "Post Title cannot be empty.<br />";
  }

  if (empty($data_array["description"]) ) {
    $error = TRUE;
    $post_err.= "Description cannot be empty.<br />";
  }
  // if no error then do the rest of work
  if ( !$error ) {
    ////////////get tags
    $terms = array();
  	$tags = preg_split('/\s*,\s*/' , strtolower($_POST['tags']));
    $tags = array_unique($tags);
    foreach ($tags as $term) {
      $tr = trim($term);
      if ($tr) {
        $terms[] = $tr;
      }
    }
    /////////////////
    // check to see if user wants to edit the post
    // now just edit and redirect to permalink

    $track = null;          // NOTE: by Zoran Hron; trackback never has been used !?
    if( !empty($cid) ) {
      $condition = array('content_id' => $cid);
      $is_active = ACTIVE;
      if (PA::is_moderated_content())
        $content = Content::load_all_content_for_moderation(NULL, $condition);
      if (!empty($content)) {
        $is_active = $content[0]['is_active'];
      }

      $r = BlogPost::save_blogpost($cid, PA::$login_uid, $_POST["blog_title"], $_POST["description"], $track, $terms, -1, $is_active);
      if($r['cid'] == $cid) {
        $login_required_str = null;
        $content_author_image = uihelper_resize_mk_user_img($user->picture, 80, 80,'alt="'.$user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
        if(PA::is_moderated_content()) {
          $login_required_str = '&login_required=true';
        }
/*
        $network_owner = new User();
        $network_owner->load((int)PA::$network_info->owner_id);
        $network_owner_name = User::map_ids_to_logins(PA::$network_info->owner_id);
        $params['recipient_username'] = $network_owner->login_name;
        $params['recipient_firstname'] = $network_owner->first_name;
        $params['recipient_lastname'] = $network_owner->last_name;
        $params['cid'] = $r['cid'];
        $params['first_name'] = $user->first_name;
        $params['user_id'] = $user->user_id;
        $params['user_image'] = $content_author_image;
        $params['content_title'] = $_POST["blog_title"];
        $params['network_name'] = PA::$network_info->name;
        $_content_url = PA::$url . PA_ROUTE_CONTENT . '/cid='.$r['cid'].$login_required_str;
        $params['content_url'] = "<a href=\"$_content_url\">$_content_url</a>";
        $_content_moderation_url = PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT;
        $params['content_moderation_url'] = "<a href=\"$_content_moderation_url\">$_content_moderation_url</a>";
        $params['config_site_name'] = PA::$site_name;
        $params['network_owner_name'] = $network_owner_name[PA::$network_info->owner_id];
        auto_email_notification('content_modified', $params);
*/
        $content_obj = Content::load_content((int)$r['cid']);
        PANotify::send("content_modified", PA::$network_info, $user, $content_obj);

        //for rivers of people
        $activity = 'content_modified';
        $activity_extra['info'] = ($user->first_name.' modified blog post');
        $activity_extra['blog_name'] =  $_POST["blog_title"];
        $activity_extra['blog_id'] = $r['cid'];
        $activity_extra['blog_url'] = PA::$url . PA_ROUTE_CONTENT . '/cid='.$r['cid'].$login_required_str;
        $extra = serialize($activity_extra);
        $object = $r['cid'];
        if(!PA::is_moderated_content()) {//Write to activity log only when moderation is off
          Activities::save($user->user_id, $activity, $object,$extra);
        }
      }

      //invalidate cache
      if( PA::$network_info ) {
        $nid = '_network_'.PA::$network_info->network_id;
      } else {
        $nid='';
      }
      //unique name
      $cache_id = 'content_'.$cid.$nid;
      CachedTemplate::invalidate_cache($cache_id);
      if (PA::is_moderated_content()) {
        $error_msg = '&msg_id=1004';
      } else {
        $error_msg = '&msg_id=7027';
      }

      $location = PA::$url . PA_ROUTE_CONTENT . "/cid=$cid".$error_msg;
      header("location:$location");exit;
    }//.. end of edit

    // If we have come this far it means it is not edit and we have to create post
    //save post normally
    if (isset($_POST['route_to_pa_home']) && $_POST['route_to_pa_home'] == 1) {
      $display_on_homepage = DISPLAY_ON_HOMEPAGE;//its zero
    } else {
      $display_on_homepage = NO_DISPLAY_ON_HOMEPAGE;//This will not show up on homepage - flag has opposite values
    }
		 
		if (!empty(PA::$config->simple['omit_routing'])) {
			$ccid = (!empty($_REQUEST['ccid'])) ? $_REQUEST['ccid'] : -1;
			$post_saved = BlogPost::save_blogpost(0, PA::$login_uid, $_POST["blog_title"], $_POST["description"], NULL, $terms, $ccid, 1, $display_on_homepage);
		} else {
			$post_saved = BlogPost::save_blogpost(0, PA::$login_uid, $_POST["blog_title"], $_POST["description"], NULL, $terms, -1, 1, $display_on_homepage);
		}
    $permalink_cid = $post_saved['cid'];
    if (PA::is_moderated_content() && PA::$network_info->owner_id != $user->user_id) {
      Network::moderate_network_content(-1, $permalink_cid);// -1 for contents; not a part of any collection
      $error_msg = "&err=".urlencode(MessagesHandler::get_message(1004));
    }

    $login_required_str = null;
    if(PA::is_moderated_content()) {
      $login_required_str = '&login_required=true';
    }
/* - Replaced with new PANotify code

    $content_author_image = uihelper_resize_mk_user_img($user->picture, 80, 80,'alt="'.$user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
    $network_owner = new User();
    $network_owner->load((int)PA::$network_info->owner_id);
    $network_owner_name = User::map_ids_to_logins(PA::$network_info->owner_id);
    $params['recipient_username'] = $network_owner->login_name;
    $params['recipient_firstname'] = $network_owner->first_name;
    $params['recipient_lastname'] = $network_owner->last_name;
    $params['cid'] = $permalink_cid;
    $params['first_name'] = $user->first_name;
    $params['user_id'] = $user->user_id;
    $params['user_image'] = $content_author_image;
    $params['content_title'] = $_POST["blog_title"];
    $params['network_name'] = PA::$network_info->name;
    $_content_url = PA::$url . PA_ROUTE_CONTENT . '/cid='.$permalink_cid.$login_required_str;
    $params['content_url'] = "<a href=\"$_content_url\">$_content_url</a>";
    $_content_moderation_url = PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT;
    $params['content_moderation_url'] = "<a href=\"$_content_moderation_url\">$_content_moderation_url</a>";
    $params['config_site_name'] = PA::$site_name;
    $params['network_owner_name'] = $network_owner_name[PA::$network_info->owner_id];
    auto_email_notification('content_posted', $params);
    if ($display_on_homepage == DISPLAY_ON_HOMEPAGE) {
      auto_email_notification('content_posted_to_comm_blog', $params);
    }
*/
    $content_obj = Content::load_content((int)$permalink_cid);
    PANotify::send("content_posted", PA::$network_info, $user, $content_obj);
    if ($display_on_homepage == DISPLAY_ON_HOMEPAGE) {
      PANotify::send("content_posted_to_comm_blog", PA::$network_info, $user, $content_obj);
    }
    //for rivers of people
    $activity = 'user_post_a_blog';
    $activity_extra['info'] = $user->first_name.'posted a new blog';
    $activity_extra['blog_name'] =  $_POST["blog_title"];
    $activity_extra['blog_id'] = $permalink_cid;
    $activity_extra['blog_url'] = PA::$url . PA_ROUTE_CONTENT . '/cid=' . $permalink_cid . $login_required_str;
    $extra = serialize($activity_extra);
    $object = $permalink_cid;
    if (!PA::is_moderated_content()) {//Write to activity log only when moderation is off
      Activities::save($user->user_id, $activity, $object,$extra);
    }

    if (empty(PA::$config->simple['omit_routing'])) {
			//save post in groups
    	$routed_to_groups = route2groups();
    }

    // save post to outputthis
    route_to_outputthis($_POST["blog_title"], $_POST["description"]);

    //we have saved it in all the locations lets redirect it to various locations
    if (!empty($_GET['ccid'])) {
      $gid = $_GET['ccid'];
      $group = ContentCollection::load_collection((int)$gid, PA::$login_uid);
      $is_member = Group::get_user_type((int)PA::$login_uid, (int)$gid);
      if ( $is_member == NOT_A_MEMBER) {
        $msg = "&msg_id=7028";
      } else {
      if(($group->reg_type == REG_MODERATED) || (PA::$extra['network_content_moderation'] == NET_YES)) {
          $msg = "&msg_id=1004";
        } else {
          $msg = "&msg_id=7027";
        }
      }
      // it means user is coming from group's page then redirect it to group
      //load group to see if group is if it is moderated
      $location = PA::$url . PA_ROUTE_GROUP . "/gid=".$_REQUEST['ccid'].$msg;
      header("location:$location");exit;
    } else {
      //just redirect it to permalink page
      if (PA::is_moderated_content()) {
        $error_msg = "&msg_id=1004";
      } else {
        $error_msg = "&msg_id=7027";
      }
      // header("location:".PA::$url . PA_ROUTE_CONTENT . "/cid=".$permalink_cid.$error_msg);exit;
      header("location:".PA::$url .PA_ROUTE_USER_PRIVATE."?cid=".$permalink_cid.$error_msg);exit;
    }
  }
   else {//..end of !$error
    $post_err = 'Post could not be saved due to following errors:<br>'.$post_err;
  }
}//$_POST

?>
