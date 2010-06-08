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

if(isset($_REQUEST['login_required']) && ($_REQUEST['login_required'] == 'true')) {
  $login_required = TRUE;
}

$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
//require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Messaging/MessageDispatcher.class.php";

$user = get_user(); // User object for uid specified on query string, or logged in user if no user specified
$header_tpl = 'header.tpl';
if (!empty($_GET['uid'])) {
  //user header will be applied
  require_once "web/includes/functions/user_page_functions.php";
  $header_tpl = 'header_user.tpl';
}
// !empty($user) is added to check whather any one has logged in the network, or the url provides the uid or not.
$view_type = NULL;

if (@$_GET['view_type'] == 'relations' && !empty($user)) {
  $page_name = $user->get_name()."'s friends";
  $view_type = $_GET['view_type'];
}
else if (@$_GET['view_type'] == 'in_relations' && !empty($user)) {
  $page_name = 'People who call '.$user->get_name().' a friend';
  $view_type = $_GET['view_type'];
} else {
  $page_name = __('View All Members');
  $view_type = 'all';
}

if (@$_GET['gid']) {
  $page_name = __('View All Group Members');
  $header_tpl = 'header_group.tpl';
}

if (@$_POST['btn_approve']) {
  $user_id = (int)$_POST['related_id'];
  $relation_id = (int)$_GET['uid'];
  $status = APPROVED;
  try {
    $result = Relation::update_relation_status($user_id, $relation_id, $status, PA::$network_info->network_id);
    if ($result) { // if relationship has been made then send mail to the requestor
      $recip_obj = new User();
      $recip_obj->load((int)$relation_id);
      $relation_obj = Relation::getRelationData($user_id, $relation_id, PA::$network_info->network_id);
      PANotify::send("friend_request_approved", $recip_obj, PA::$network_info, $relation_obj);
      if (PA::$extra['reciprocated_relationship'] == NET_YES) {
        Relation::add_relation($relation_id, $user_id, DEFAULT_RELATIONSHIP_TYPE, PA::$network_info->address, PA::$network_info->network_id, NULL, NULL, NULL, true, APPROVED);
        PANotify::send("reciprocated_relation_estab", PA::$network_info, PA::$login_user, $relation_obj); // recipient is network owner
      }
    }
  } catch (PAException $e) {
    throw $e;
  }
} else if (@$_POST['btn_deny']) {
  $user_id = (int)$_GET['uid'];
  $relation_id = (int)$_POST['related_id'];
  try {
      $relation_obj = Relation::getRelationData($relation_id, $user_id, PA::$network_info->network_id);
     if (Relation::delete_relation($relation_id, $user_id, PA::$network_info->network_id)) {
      // if relation deleted successfully, send a notification to the requestor
      $recip_obj = new User();
      $recip_obj->load((int)$relation_id);
      PANotify::send("friend_request_denial", $recip_obj, PA::$network_info, $relation_obj);
    }
  } catch (PAException $e) {
    throw $e;
  }
}


function setup_module($column, $moduleName, $obj) {
    global $uid, $paging, $user, $view_type;
    switch ($column) {
    case 'left':
      $obj->mode = PUB;
      if ($moduleName=='RecentCommentsModule') {
        $obj->cid = @$_REQUEST['cid'];
        $obj->block_type = HOMEPAGE;
        $obj->mode = PRI;
      }
        break;

    case 'middle':
        $obj->mode = $view_type;
        $obj->network_info = PA::$network_info;
        $obj->content_id = @$_REQUEST['cid'];
        $obj->gid = @$_REQUEST['gid'];
        $obj->uid = $uid;
        $obj->block_type = 'media_management';
        $obj->view_type = $view_type;
        $obj->Paging["page"] = $paging["page"];
        $obj->Paging["show"] = $paging["show"];
        $obj->page_user = NULL;
        if( !empty($user)) {
        $obj->page_user = $user->get_name();
        }

    break;

    case 'right':
        $obj->mode = PRI;
        if ($moduleName != 'AdsByGoogleModule') {
          $obj->block_type = HOMEPAGE;
        }
        if (!empty($_GET['gid'])) {
          if ($moduleName == 'RecentPostModule') {
            $obj->type = 'group';
            $obj->gid = $_GET['gid'];
          }
        }
    break;
    }
}

$page = new PageRenderer("setup_module", PAGE_VIEW_ALL_MEMBERS, $page_name, "container_three_column.tpl", $header_tpl, PUB, HOMEPAGE, PA::$network_info);

$page->add_header_html(js_includes('common.js'));

if (@$_GET['gid']) {
  uihelper_get_group_style($_GET['gid']);
}
else if (!empty($_GET['uid'])) {
  //applying the user theme
  uihelper_set_user_heading($page);
}
else {
  uihelper_get_network_style();
}

echo $page->render();
?>