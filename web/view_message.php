<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        view_message.php, web file to view private messages
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file view private message. It uses 
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 * TODO: check for mid variation from url
 */
 
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Message/Message.php";

if (empty($_GET['mid'])) {
  //redirecting to inbox if message id is not set
  header("Location: " . PA::$url . PA_ROUTE_MYMESSAGE);
  exit;
}
$mid = $_GET['mid'];

function setup_module($column, $moduleName, $obj) {
  global $login_uid, $mid;
  switch ($column) {
    case 'middle':
      $obj->uid = $login_uid;
      $obj->mid = $mid;
      $obj->mode = 'view_mesage';
      if(isset($_GET['q'])) {
        $obj->search_string = $_GET['q'];
      }
  }
}

//check if user is checking out only his content
if ( $uid != $login_uid ) {
  throw new PAException(OPERATION_NOT_PERMITTED, "You cant access other user's messages.");
}

$page = new PageRenderer("setup_module", PAGE_VIEW_MESSAGE, "Private Messages - PA::$network_info->name","container_one_column.tpl",'header.tpl', PRI, HOMEPAGE, PA::$network_info);

uihelper_set_user_heading($page);
if (@$message) uihelper_error_msg($message);

echo $page->render();

?>