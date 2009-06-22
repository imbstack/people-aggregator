<?php
/*
 * Project:     PeopleAggregator: a social network developement platform
 * File:        addgroup.php, web file to create / edit a group
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file displays the group creation page site. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");

require_once "api/ContentCollection/ContentCollection.php";
require_once "ext/Group/Group.php";
require_once "api/Category/Category.php";
require_once "web/includes/classes/file_uploader.php";
require_once "api/Message/Message.php";
require_once "web/includes/functions/auto_email_notify.php";

default_exception();

$parameter = js_includes("all");

$header = 'header.tpl';//default network header while creating groups. While group editing header_group.tpl will be used.
$title = __("Request to join");
html_header($title, $parameter);
$onload = (isset($onload)) ? $onload : '';
$optional_parameters = "onload=\"$onload\"";
html_body($optional_parameters);

if (!empty($_POST['request'])) {
  //code for requesting
  $error = FALSE;
  $success = FALSE;
  try {
    $request_sent = Network::join($network_info->network_id, $_SESSION['user']['id']);
    if ($request_sent) {
      $success = TRUE;
    }
  } catch (PAException $e) { 
    $join_error = $e->message;
    $error = TRUE;
  
  }
  if ($error) {
    $error_msg = "Your request to join this network could not be sent due to following reason: ".$join_error.". You can go back to the home network by clicking 'Return to home network'";
  } else {
    $success_msg = "Your request to join this network has been successfully sent to the moderator of this network. The Moderator will check this request and approve or deny the request. You can go back to mother network by clicking the button 'Return to home network'";
  }
} 
if (!empty($_POST['back'])) {
  //redirect to mother network
  header("Location:".$mothership_info['url']);
  exit;
}







/**
 *  Function : setup_module()
 *  Purpose  : call back function to set up variables 
 *             used in PageRenderer class
 *             To see how it is used see api/PageRenderer/PageRenderer.php 
 *  @param    $column - string - contains left, middle, right
 *            position of the block module 
 *  @param    $moduleName - string - contains name of the block module
 *  @param    $obj - object - object reference of the block module
 *  @return   type string - returns skip means skip the block module
 *            returns rendered html code of block module
 */

function setup_module($column, $module, $obj) {
    global $login_uid, $paging, $page_uid, $permission_denied_msg, $error,$error_msg, $success, $success_msg;
    
    switch ($module) {
      case 'RequestModule':
        if ($error) {
          $obj->mode = 'msg_display';
          $obj->error = TRUE;
          $obj->error_msg = $error_msg;
        }
        if ($success) {
          $obj->mode = 'msg_display';
          $obj->success = TRUE;
          $obj->success_msg = $success_msg;
        }
      break;
    }
}

$page = new PageRenderer("setup_module", PAGE_REQUEST, "Groups - PeopleAggregator", 'container_three_column.tpl', $header, PRI, HOMEPAGE, $network_info);

if (isset($show_options) && $show_options) {
  $page->header->show_options = TRUE;
}

$gid = (isset($_REQUEST['gid'])) ? (int)$_REQUEST['gid'] : null;



$css_array = get_network_css();
if (is_array($css_array)) {
  foreach ($css_array as $key => $value) {
    $page->add_header_css($value);
  }
}





$page->header->set('onload', $onload);


echo $page->render();
?>