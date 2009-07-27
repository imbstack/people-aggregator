<?php

$login_required = FALSE;

if(isset($_REQUEST['login_required']) && ($_REQUEST['login_required'] == 'true') || !empty($_POST['addcomment'])) {
  $login_required = TRUE;
}

$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
if (!empty($_POST['addcomment'])) {
  if (!empty(PA::$config->spam_log)) {
    $f = fopen(PA::$config->spam_log, "at");
    fwrite($f, date("c")." ".PA::$remote_ip." COMMENT\n"); // . var_export($_SERVER, TRUE) . var_export($_POST, TRUE));
    fclose($f);
  }
  if ($comments_disabled) {
    echo "comments disabled.";
    exit;
  }
}
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/Content/Content.php";
require_once "api/Tag/Tag.php";
require_once "api/Comment/Comment.php";
require_once "ext/Group/Group.php";
require_once "api/Category/Category.php";
require_once "api/Network/Network.php";
require_once "web/includes/functions/user_page_functions.php";

$header = 'header_user.tpl';// by default we are setting header as user's header
$media_gallery = 'homepage';
/**
  when collection type is not a Group
*/
$setting_data = ModuleSetting::load_setting(PAGE_PERMALINK, $uid);
$content = Content::load_content((int)$_REQUEST['cid'], (int)PA::$login_uid);
// apply output filtering
$content->title = _out($content->title);


$author = new User();
$author->load((int)$content->author_id);
$is_group_content = FALSE;

/**
   If Collection Type is a Group than left and right module will be the same as Group page
*/
$gid = @$_REQUEST['ccid'];
$content_id = @$_REQUEST['cid'];
$error_message  = '';
$authorized_users = array($content->author_id, PA::$network_info->owner_id);
$extra = unserialize(PA::$network_info->extra);
if (@$extra['network_content_moderation'] == NET_YES && Network::item_exists_in_moderation($content_id, $content->parent_collection_id, 'content') && !in_array(PA::$login_uid, $authorized_users)) {
  $error_message = 1001;
}
if ($content->parent_collection_id!= -1 ) {
//load here content collection
  $cid = $content->parent_collection_id;
  $collection = ContentCollection::load_collection((int)$cid, PA::$login_uid);
    if ($collection->type == GROUP_COLLECTION_TYPE) {
      $is_member = Group::member_exists((int)$cid, PA::$login_uid);
      $is_admin = Group::is_admin((int)$cid, PA::$login_uid);
      $is_group_content = TRUE;
      $header = 'header_group.tpl';// group header will be user in this case.
    //its group so lets load group details and group modules
      $media_gallery = 'grouppage';
      $group_details = pageLoadGroup($collection);
      $gid = $content->parent_collection_id;
      $setting_group_data = ModuleSetting::load_setting(PAGE_GROUP, $uid);
      $setting_data['left'] = $setting_group_data['left'];
      $setting_data['right'] = $setting_group_data['right'];
      if ($collection->reg_type == REG_INVITE && !$is_member && !$is_admin) {
       $error_message = 9005;
      }
    }

} else {
  $group_details = $collection = NULL;
}


// Code for Approving and Denying the Pending Content Moderations: Starts
if(!empty($_GET["cid"]) && !empty($_GET["ccid"])) {


    $type = 'content';
   if (Group::is_admin((int)$_GET["ccid"], (int)PA::$login_uid))  {
    $Group = new Group();
    $Group->collection_id = $_GET["ccid"];
    $contentIdArray = $_GET["cid"];

    if(($_GET["apv"]) == 1) {

          $Group->approve ($contentIdArray, $type);
          Content::update_content_status($contentIdArray, 1);
          header("location: " . PA_ROUTE_GROUP_MODERATION . "/gid=" . $_GET['ccid'] . "&view=content&msg=succ");

}

    if(($_GET['dny'])==1) {

          $Group->disapprove ($contentIdArray, $type);
          Content::update_content_status($contentIdArray);
          header("location: " . PA_ROUTE_GROUP_MODERATION . "/gid=" . $_GET['ccid'] . "&view=content&msg=dny");

        }
    }
}

// code for adding a comment:

require_once "web/submit_comment.php";
if (isset($_GET['err'])) {
   $error_message = strip_tags(urldecode ($_GET['err']));
}
// code for reporting abuse
require_once "web/includes/blocks/submit_abuse.php";
if (isset($_GET['err'])) {
   $error_message = strip_tags(urldecode ($_GET['err']));
}
if (!empty($group_details['skip_group_modules'])) {
  $error_message = __('You need to be member of this group to view the contents');
}

if (!empty($_GET['msg_id'])) {
  $error_message = $_GET['msg_id'];
}

function setup_module($column, $moduleName, $obj) {
   global $is_group_content, $group_details, $users, $gid, $content, $collection, $error_message, $media_gallery;

   if (!empty($group_details['collection_id'])) {
	 	// we are in a group
	 	$gid = (int)$group_details['collection_id'];
	 	$login_uid = @PA::$login_user->user_id;
	 	$group = ContentCollection::load_collection($gid, $login_uid);
	 	$member_type = Group::get_user_type($login_uid, $gid);
	 	if ($group->reg_type == REG_INVITE && $member_type == NOT_A_MEMBER) {
      $group_top_mesg = 9005;
      return "skip";
    }
   }

    if ($group_details['access_type'] == 'Private Moderated' && $member_type == NOT_A_MEMBER) {
      $group_top_mesg = 9005;
      return "skip";
    }


  switch ($moduleName) {
    case 'RecentCommentsModule':
      $obj->cid = $_REQUEST['cid'];
      $obj->block_type = HOMEPAGE;
      $obj->mode = PRI;
    break;
    case 'GroupAccessModule':
      $obj->group_details = $group_details;
    break;
    case 'MembersFacewallModule':
      $obj->group_details = $group_details;
      $obj->links = $group_details['users'];
      $obj->gid = $gid;
      $obj->mode = PRI;
      $obj->block_type = 'Homepage';
    break;

     case 'EventCalendarSidebarModule':
      if ($is_group_content) {
        $obj->assoc_type = "group";
        $obj->assoc_id = $gid;
        if (!isset($_GET['gid']) || $_GET['gid'] != $gid) {
          $_GET['gid'] = $gid; // make sure it get's passed so we can construct the right URLs
        }
        $obj->title = 'Group Events';
        $is_member = Group::get_user_type((int)PA::$login_uid, $gid);
        if ($is_member == NOT_A_MEMBER) {
          $obj->may_edit = false;
        } else {
          $obj->may_edit = true;
        }
      } else {
        $obj->assoc_id = $content->author_id; // displaying the calendar for the AUTHOR of this content
        $obj->assoc_type = "user"; // this is the personal calendar
        if ((int)PA::$login_uid != $user->user_id) {
          $obj->may_edit = false;
        } else {
          $obj->may_edit = true;
        }

      }

    case 'PermalinkModule':
     if ($error_message == 9005 || $error_message == 1001) {
       return "skip";
     }
     if ($group_details['skip_group_modules']) return "skip";
      $obj->content_id = $_REQUEST['cid'];
      $obj->content = $content;
    break;
    case 'GroupStatsModule':
      $obj->group_details = $group_details;
      $obj->gid = $gid;
      $obj->block_type = 'GroupSideBlocks';
    break;
    case 'RecentPostModule':
    if (@$collection->type == GROUP_COLLECTION_TYPE) {
      $obj->type = 'group';
      $obj->gid = $collection->collection_id;
    } else {
      $obj->mode = PUB;
      $obj->type = 'permalink';
    }
    break;
    case 'ImagesModule':
      $obj->page = $media_gallery;
      $obj->group_details = $group_details;
    break;
  }
}
$page = new PageRenderer("setup_module", PAGE_PERMALINK, sprintf("%s - %s - %s", strip_tags($content->title), $author->get_name(), PA::$network_info->name), "container_three_column.tpl", $header, PUB, HOMEPAGE, PA::$network_info,'',$setting_data);

uihelper_error_msg($error_message);

$page->html_body_attributes ='class="no_second_tier"';

if (!$is_group_content) {
  uihelper_set_user_heading($page,TRUE,$content->author_id);
}
else {
  uihelper_get_group_style((int)$gid);
}

$page->add_header_html(js_includes('common.js'));
// To Do need to remove inline styling
$inline_style = '.no_second_tier #bg_blog_post { margin-top: 3px; }';
$append_css = "<style>".$inline_style."</style>";
$page->add_header_html($append_css);
$page->add_header_html(js_includes('rating.js'));
// for calendar sidebar Module
$css_path = PA::$theme_url . '/calendar.css';
$page->add_header_css($css_path);
$page->add_header_html(js_includes('calendar.js'));


echo $page->render();

// this function loads group details for this page
//arguement - $group collection object
function pageLoadGroup($group) {
  $access = $group->access_type;
  $skip_group_modules = FALSE;
  $is_admin = FALSE;
  if ( $group->access_type == $group->ACCESS_PRIVATE ) {
    if (PA::$login_uid) {//if private group
      if (GROUP::member_exists($group->collection_id,PA::$login_uid)) {
        $skip_group_modules = FALSE;
      } else {// haha no way for non member of group
        $skip_group_modules = TRUE;
      }
    } else {//haha no way for anonymous user
      $skip_group_modules = TRUE;
    }
    $access_type = 'Private';
  } else {
    $access_type = 'Public';
  }
  if( $group->reg_type == $group->REG_OPEN ) {
    $access_type.= ' Open';
  } else {
    $access_type.= ' Moderated';
  }
  if (Group::is_admin((int)$group->collection_id, (int)PA::$login_uid)){
    $is_admin = TRUE;
  }
  $members = $group->get_members($cnt=FALSE, 5, 1, 'created', 'DESC',FALSE);
  $group_details = array();
  $group_details['collection_id'] = $group->collection_id;
  $group_details['type'] = $group->type;
  $group_details['author_id'] = $group->author_id;
  $user = new User();
  $user->load((int)$group->author_id);
  $login_name = $user->login_name;
  $first_name = $user->first_name;
  $last_name = $user->last_name;
  $group_details['author_name'] = $login_name;
  $group_details['author_picture'] = $user->picture;
  $group_details['title'] = $group->title;
  $group_details['description'] = $group->description;
  $group_details['is_active'] = $group->is_active;
  $group_details['picture'] = $group->picture;
  $group_details['desktop_picture'] = @$group->desktop_picture;
  $group_details['created'] = PA::datetime($group->created, 'long', 'short'); // date("F d, Y h:i A", $group->created);
  $group_details['changed'] = $group->changed;
  $group_details['category_id'] = $group->category_id;
  $cat_obj = new Category();
  $cat_obj->set_category_id($group->category_id);
  $cat_obj->load();
  $cat_name = stripslashes($cat_obj->name);
  $cat_description = stripslashes($cat_obj->description);
  $group_details['category_name'] = $cat_name;
  $group_details['category_description'] = $cat_description;
  $group_details['members'] = Group::get_member_count($group->collection_id);
  $group_details['access_type'] = $access_type;
  $group_details['is_admin'] = $is_admin;
  //////////////////get details of group EOF
  if(is_array($members)){
    $count = count($members);
    foreach ($members as $member) {
      $count_relations = Relation::get_relations($member['user_id'], APPROVED, PA::$network_info->network_id);
      $user = new User();
      $user->load((int)$member['user_id']);
      $login_name = $user->login_name;
      $user_picture = $user->picture;
      $users_data[] = array('user_id'=>$member['user_id'],'picture'=>$user_picture,'login_name'=>$login_name,'no_of_relations'=>count($count_relations));
    }
    $final_array = array('users_data'=>$users_data, 'total_users'=>$count);
  }
  $users = $final_array;
  $is_member = (Group::member_exists((int)$group->collection_id, (int)PA::$login_uid)) ? TRUE : FALSE;
  $group_details['is_member'] = $is_member;
  $group_details['skip_group_modules'] = $skip_group_modules;
  $group_details['users'] = $users;
  return $group_details;
}
?>
