<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        assign_task.php , web file to assign task to role for PeopleAggregator
 * Author:      tekritisoftware
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
*/
$login_required = TRUE;
//including necessary files
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "web/includes/network.inc.php";
require_once "api/Roles/Roles.php";
require_once "api/Permissions/PermissionsHandler.class.php";


 $res = PermissionsHandler::can_network_user(PA::$login_uid, 
                                             PA::$network_info->network_id,
                                             array('permissions'=>'post_to_community, edit_content'), 
                                             true);

 echo "Manage settings: $res";
 
$msg = '';
if (@$_GET['msg']){
  $msg = $_GET['msg']; 
}
//echo '<pre>';print_r($_POST);exit;
if (@$_POST['save']){
  $count_role  = (int) $_POST['totalcount'] ;
  for($i = 1 ; $i <= $count_role ; $i++) {
    $role_id  = $_POST['link_id'.$i] ; 
    $role = new Roles();
    $task_count  = (int) $_POST['taskcount'] ;
    for($j = 1 ; $j <=$task_count ; $j++) {
      $task_id =  $_POST['taskid'.$j] ; 
      $chk = $role_id .'~'. $task_id;
      if ( $_POST[$chk]) {
        try {
          $role->assign_tasks_to_role($task_id, $role_id) ;   
        }
        catch (PAException $e) {
          $msg = "$e->message";
          $error = TRUE;
        }
      } else {
        $task_exist = Roles::is_roletask_exist($role_id, $task_id);
        if ($task_exist) {
          Roles::delete_taskrole($role_id, $task_id);
        } 
      }
    }
  }
header("Location:assign_tasks.php?msg=9015");
exit;
}

$page = new PageRenderer("setup_module", PAGE_TASK_MANAGE, "Manage Task Relations", 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);

$page->html_body_attributes = 'class="no_second_tier network_config"';

function setup_module($column, $module, $obj) {
}

uihelper_error_msg($msg);
uihelper_get_network_style();
echo $page->render();

?>
