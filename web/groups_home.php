<?php
$login_required = FALSE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");

require_once "api/Invitation/Invitation.php";
/*including Js files */
$parameter = js_includes('common.js');
// Code for Accepting and Denying the Join Invitation: Starts

if(!empty($_POST["collection_id"])) {
  $Invitation = new Invitation();
  $Invitation->inv_user_id = $_SESSION["user"]["id"];
  $Invitation->inv_id = $_POST["inv_id"];
  $Invitation->inv_group_name = $_POST["collection_name"];
  if(!empty($_POST["btn_accept"])) {
    $Invitation->accept();    
    header("Location: " . PA_ROUTE_GROUP ."/gid=".$_POST["collection_id"]."&action=join");
    exit;
  }
  if(!empty($_POST["btn_deny"])) {
    $Invitation->deny();
  }
}
// Counting for total Number of Groups
$total_groups = Group::get_total_groups();
// Code for Accepting and Denying the Join Invitation: Ends
//p($_GET);

function setup_module($column, $module, $obj) {
    global $login_uid, $paging, $page_uid, $page_user,$total_groups;

    switch ($module) {
    // TODO Remove the Search Module ... 
    case 'SearchGroupsModule':
      return "skip";
    break;
    case 'MyGroupsModule':
        return 'skip';//module is not backward compatible hence skiped.
        if (!$login_uid) return "skip";
        if ($page_uid && ($page_uid !=$login_uid)) {
          $obj->uid = $page_uid;
          $page_user = get_user();
          $obj->title = ucfirst($page_user->first_name).'\'s Groups';
          $obj->user_name = $page_user->login_name;
        }
        else {
          $obj->uid = $login_uid;          
        }
        // get pending group invitations for the logged-in user.
        $obj->mode = PUB;
        $pending_invitations = Invitation::get_pending_invitations_for_user_by_email($_SESSION["user"]["email"], $login_uid);
        $obj->pending_invitations = $pending_invitations;
        $obj->Paging["page"] = $paging["page"];
        $obj->Paging["show"] = $paging["show"];
      break;
      case 'GroupsCategoryModule':
        $obj->mode = PUB;
        $obj->total_groups= $total_groups;
        if (!empty($_GET['celebrity_id'])) {
          return 'skip';
        }
      break;
      case 'GroupsDirectoryModule':
          $obj->Paging["page"] = $paging["page"];
          $obj->Paging["show"] = $paging["show"];          
          $obj->total_groups = $total_groups;
          $obj->sort_by = @$_GET['sort_by'];
          if (@$_GET['keyword']) {
            $obj->name_string = $_GET['name_string'];
            $obj->keyword = $_GET['keyword'];
            $obj->uid =  @$_GET['uid'];
          }
          if ( @$_GET['uid'] ) {
              $obj->uid =  $_GET['uid'];
           }
          break; 
       case 'LargestGroupsModule':
          if (!empty($_GET['celebrity_id'])) {
            return 'skip';
          }
       break;
     }
      
      
}

$page = new PageRenderer("setup_module", PAGE_GROUPS_HOME, sprintf(__("Groups - %s"), $network_info->name), 'container_three_column.tpl','header.tpl',PRI,HOMEPAGE,$network_info);
$page->add_header_html($parameter);

$page->html_body_attributes = ' id="pg_groups_home"';
uihelper_get_network_style();
uihelper_error_msg(@$_GET['msg_id']);
echo $page->render();

?>