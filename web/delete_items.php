<?php 
/**
 * This file is used to delete the items and item related information based on the get variable type. If type is empty then the item will be deleted else the item_type will be deleted. For example if the type is message then the entry to be deleted is item_message
 **/
$login_required = TRUE;
include_once("web/includes/page.php"); 
require_once "ext/Celebrity/Celebrity.php";

if (!empty($_REQUEST['back_page'])) {
  $location = $_REQUEST['back_page'];
} else {
  //required param missing.
  $location = PA::$url . PA_ROUTE_HOME_PAGE;
  header("Location: $location");
  exit;
}
$action = 'delete_item';
if (!empty($_REQUEST['type'])) {
  $action .= '_'.$_REQUEST['type'];
}
if (!empty($_REQUEST['id'])) {
  $params = array('action'=>$action, 'uid'=>PA::$login_uid, 'id'=>$_REQUEST['id']);
  if (user_can($params)) {
    switch ($_REQUEST['type']) {
      case 'message':
        $celebrity_msg = new Celebrity();
        $celebrity_msg->id = $_REQUEST['id'];
        $celebrity_msg->delete_message();
        $msg_id = 16012;
        $result = TRUE;
      break;
      default:
        $result = FALSE;
    }
    if ($result) {
      header("Location: $location?msg=$msg_id");
      exit; 
    } else {
      header("Location: $location?msg=16013");
      exit;
    }
  } else {
    //not authorised.
     header("Location: $location?msg=16015");
     exit;
  }
} else {
  //required parameter missing
  header("Location: $location?msg=16013");
  exit;
}
?>