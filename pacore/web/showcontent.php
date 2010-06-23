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
$login_required = FALSE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Content/Content.php";
require_once "api/Comment/Comment.php";
require_once "api/Tag/Tag.php";
require_once "api/BlogPost/BlogPost.php";
include_once "api/Theme/Template.php";
include_once "api/ModuleSetting/ModuleSetting.php";

$error_message = NULL;

// This function splits an associative array based on it's keys
function &array_split(&$in) {
  // get arguments, passed to funtion
  $keys = func_get_args();
  // get the key, to be removed from array
  array_shift($keys);
  foreach($keys as $key) {
      unset($in[$key]); // desired array
  }
  return;
}

// Search with Date Range
if(@$_GET["mFrom"] != 0) {
    $from = mktime(0, 0, 0, $_GET["mFrom"], $_GET["dFrom"], $_GET["yFrom"]);
    $to = mktime(0, 0, 0, $_GET["mTo"], $_GET["dTo"], $_GET["yTo"]);
    if($to > $from) {
        $search_string_array["date"]["from"] = $from;
        $search_string_array["date"]["to"] = $to;
    }
    else if( $to == $from ){
      // Get the content for a single day. Adding 86400 = no. of seconds in a day.
      $search_string_array["date"]["from"] = $from;
      $search_string_array["date"]["to"] = $from + 86400;
    }
    else {
      $error_message =  __('Date range selected is invalid, so it has been ignored in searching');
    }
}

if(!empty($_GET["allwords"])) {
    $allwords_array = explode(" ", trim($_GET["allwords"]));
    if(count($allwords_array) > 0) {
        for($counter = 0; $counter < count($allwords_array); $counter++) {
            $search_string_array["allwords"][$counter] = trim($allwords_array[$counter]);
        }
    }
}

// For exact phrase
if(!empty($_GET["phrase"])) {
    $search_string_array["phrase"][0] = trim($_GET["phrase"]);
}

// For Any words
if(!empty($_GET["anywords"])) {
    $anywords_array = explode(" ", trim($_GET["anywords"]));
    if(count($anywords_array) > 0) {
        for($counter = 0; $counter < count($anywords_array); $counter++) {
            $search_string_array["anywords"][$counter] = trim($anywords_array[$counter]);
        }
    }
}

// For None of the words
if(!empty($_GET["notwords"])) {
    $notwords_array = explode(" ", trim($_GET["notwords"]));
    if(count($notwords_array) > 0) {
        for($counter = 0; $counter < count($notwords_array); $counter++) {
            $search_string_array["notwords"][$counter] = trim($notwords_array[$counter]);
        }
    }
}
//$content_array = Content::content_search($search_string_array);
//die;
if (!isset($search_string_array) && isset($_GET["mFrom"])) {
  $error_message = __("Please enter either data or date to search");
}
$setting_data = ModuleSetting::load_setting(PAGE_SHOWCONTENT, $uid);
$user = new User();
if ($login_uid) {
  $user_details = $user->load((int)$uid);
}

$_REQUEST['ccid'] = @$_REQUEST['gid'];

//get details of group
if ($_REQUEST['ccid']) {
  $is_member = FALSE;
  $is_admin = FALSE;
  $content_access = TRUE;
  $is_invite = FALSE;
  //$gid = (int)$_REQUEST['gid'];  gid changed to ccid
  $gid = (int)$_REQUEST['gid'];
  $group = ContentCollection::load_collection((int)$gid, $login_uid);
  $access = $group->access_type;

  if( $group->access_type == $group->ACCESS_PRIVATE ) {
    $access_type = 'Private';
  } else {
    $access_type = 'Public';
  }

  if( $group->reg_type == $group->REG_OPEN ) {
    $access_type.= ' Open';
  } else {
    $access_type.= ' Moderated';
  }
  if (Group::is_admin((int)$_REQUEST['gid'], (int)$login_uid)){
    $is_admin = TRUE;
  }
  $members = $group->get_members();
  $group_details = array();
  $group_details['collection_id'] = $group->collection_id;
  $group_details['type'] = $group->type;
  $group_details['author_id'] = $group->author_id;
  $user = new User();
  $user->load((int)$group->author_id);
  $first_name = $user->first_name;
  $last_name = $user->last_name;
  $login_name = $user->login_name;
  $group_details['author_name'] = $login_name;
  $group_details['author_picture'] = $user->picture;
  $group_details['title'] = $group->title;
  $group_details['description'] = $group->description;
  $group_details['is_active'] = $group->is_active;
  $group_details['picture'] = $group->picture;
  $group_details['desktop_picture'] = @$group->desktop_picture;
  $group_details['created'] = PA::datetime($group->created, 'long', 'short'); //date("F d, Y h:i A", $group->created);
  $group_details['changed'] = $group->changed;
  $group_details['category_id'] = $group->category_id;
  $cat_obj = new Category();
  $cat_obj->set_category_id($group->category_id);
  $cat_obj->load();
  $cat_name = stripslashes($cat_obj->name);
  $cat_description = stripslashes($cat_obj->description);
  $group_details['category_name'] = $cat_name;
  $group_details['category_description'] = $cat_description;
  $group_details['members'] = Group::get_member_count($gid);
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

  if ((isset($_SESSION['user']['id'])) && (Group::member_exists((int)$group->collection_id, (int)$_SESSION['user']['id']))) {
      $is_member = TRUE;
  }

  $group_details['is_member'] = $is_member;
//..get details of group ends
}//..ccid


if(!empty($_GET['btn_searchContent'])) {
  array_unshift($setting_data['middle'], 'SearchContentModule');
}
if ($_REQUEST['ccid']) {
  array_unshift($setting_data['left'],'GroupAccessModule','MembersFacewallModule');
  array_unshift($setting_data['right'],'GroupStatsModule','RecentPostModule');
}
function setup_module($column, $moduleName, $obj) {
    global $request_info, $title,$body,$name,$email,$paging,$msg;
    global $group_details,$users,$search_string_array, $uid;
  switch ($moduleName) {
    case 'GroupAccessModule':
    case 'GroupStatsModule':
//      $obj->group_details = $group_details;
    break;
    case 'MembersFacewallModule':
      $obj->group_details = $group_details;
      $obj->mode = PRI;
      $obj->block_type = HOMEPAGE;
      $obj->links = $users;
      $obj->gid = $_REQUEST['ccid'];
    break;
    case 'ShowContentModule':
      $obj->uid = $uid;
     if (@$_GET['tier_one']) {
      $splited = & array_split($_GET, 'tier_one');
     }
     if (
       ((@$_GET['btn_searchContent'])
        && (@$_GET['allwords']||
            @$_GET['phrase']||
            @$_GET['anywords']||
            @$_GET['notwords']||
            @$_GET['mFrom']||
            @$_GET['dFrom']||
            @$_GET['yFrom']||
            @$_GET['mTo']||
            @$_GET['dTo']||
            @$_GET['yTo']
           )
         ) || !sizeof($_GET)
           || !empty($_GET['tag_id'])
           || !empty($_REQUEST['gid'])
           || !empty($_GET['page'])

       ) {
       if (!empty($_GET["show"])) {
          $obj->type = "show";
      } else if (!empty($_GET["tag_id"])) {
          $obj->type = "tag";
          $obj->tag_id = trim($_GET["tag_id"]);
      } else if (!empty($_GET["btn_searchContent"])){
          $obj->type = "search";
          $obj->search_string_array = $search_string_array;
      } else if (!empty($_REQUEST["gid"])){
          $obj->type = "group";
          $obj->html_block_id_flag = 1;
          $obj->gid = trim($_REQUEST["gid"]);
      } else {
        $obj->show_all = 1;
      }
      $obj->show_filters = TRUE;
      $obj->content_type = @$content_type;
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 5;
    } else {
      return 'skip';
    }
    break;
    case 'RecentPostModule':
        $obj->block_type = HOMEPAGE;
        $obj->type = 'group';
        $obj->mode = PRI;
        $obj->gid = $_REQUEST['ccid'];
        $obj->group_details = $group_details;
    break;
    case 'GroupForumModule':
      $obj->parent_id = $request_info['parent_id'];
      $obj->parent_name_hidden = $request_info['parent_name_hidden'];
      $obj->parent_type = $request_info['parent_type'];
      $obj->header_title = $request_info['header_title'];
      $obj->title_form = $title;
      $obj->body = $body;
      $obj->name = $name;
      $obj->email = $email;
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = $paging["show"];
      if ( $error )
      {
        $obj->msg = $msg;
      }
   break;
  }
}

$page = new PageRenderer("setup_module", PAGE_SHOWCONTENT, __("Content"), "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info,'',$setting_data);

$page->html_body_attributes .= ' class="no_second_tier" id="pg_showcontent"';
uihelper_error_msg($error_message);
uihelper_get_network_style ();
echo $page->render();

?>
