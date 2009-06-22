<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        role .php, web file to set role  for PeopleAggregator
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
global $network_info;
$authorization_required = TRUE;

$msg = "";
if (@$_GET['msg_id']){
  $msg = $_GET['msg_id'];
}
if (@$_POST['role_id']) {
  $role = new Roles();
  filter_all_post($_POST);
  $role->id = $_POST['role_id']; 
  $role->description = $_POST['desc'];
  $role->name = $_POST['role_name'];
  try {
    $role->update();
    $msg = 9009;
    header("Location:roles.php?msg_id=$msg");
    exit;
  }
  catch (PAException $e) {
    $msg = "$e->message";
    $error = TRUE;
  }
}
if (@$_POST['submit'] ) {
  $role = new Roles();
  filter_all_post($_POST);
  try {
    $role->description = $_POST['desc'];
    $role->name = $_POST['role_name'];
    $role->create();
    $msg = 9007;
    header("Location:roles.php?msg_id=$msg");
    exit;
  }
  catch (PAException $e) {
    $msg = "$e->message";
    $error = TRUE;
  }
}


$page = new PageRenderer("setup_module", PAGE_ROLE_MANAGE, "Manage Roles", 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, $network_info);
$page->html_body_attributes = 'class="no_second_tier network_config"';
function setup_module($column, $module, $obj) {
  global $error;
  switch ($module) {
      case 'RoleManageModule':
        if ( $error){
          $obj->display = true;
        }
      break;
    } 
}
$page->add_header_js("rolesedit.js");  
uihelper_error_msg($msg);
uihelper_get_network_style();
echo $page->render();
?>