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
/*
 * Project:     PeopleAggregator: a social network developement platform
 * File:        change_password.php, allows change password
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file is used for change password form. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = FALSE;
$use_theme = 'Beta';
include_once("web/includes/page.php");


// for query count
global $query_count_on_page;
$query_count_on_page = 0;
$error = $save_error = FALSE;
$empty_error = $error_password_match = $error_password_length_g = $error_password_length_l = $error_login_name = FALSE;
if (isset($_POST['submit'])) {
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);
  $forgot_password_id = $_POST['forgot_password_id'];

  if (empty($confirm_password) || empty($password)) {
    $error = TRUE;
    $empty_error = TRUE;
  } 
  else if ($password != $confirm_password) {
    $error_password_match = TRUE;
    $error = TRUE;
  }
  else if (strlen($password) > 15) {
    $error_password_length_g = TRUE;
    $error = TRUE;
  }
  else if (strlen($password) <5) {
    $error_password_length_l = TRUE;
    $error = TRUE;
  }


  if ($error != TRUE) {
    try {
      if( User::change_password($password, $forgot_password_id) ){
        $msg_id = 7004;
        header("Location: ". PA::$url ."/login.php?msg_id=$msg_id" );
        exit;
      }
    }
    catch (PAException $e)  {
      $msg = "$e->message";
      $save_error = TRUE;
    }
  }
}

if ($error == TRUE || $save_error == TRUE) {
  $error = TRUE;
}


function setup_module ($column, $moduleName, $obj) {

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
$page = new PageRenderer("setup_module", PAGE_CHANGE_PASSWORD, "Change Password page", "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE,  PA::$network_info);
$msg = $msg1 = NULL;
if (!empty($error)) {
  
   if ($error_login_name == TRUE) {
      $msg = "Username $login_name is already taken";
    }
    if ($error_password_match == TRUE) {
      $msg = "Error: Passwords do not match.";
    }
    if ($error_password_length_g == TRUE) {
      $msg = "The password must be less than 15 characters.";
    }
    if ($error_password_length_l == TRUE) {
      $msg = "The password must be greater than 5 characters.";
    }
    if($empty_error) {
      $msg = 'field(s) can\'t be left blank';
    }
    if ($error == TRUE) {
      $msg1 = "Sorry: your password has not been changed. <br> Reason: ".$msg;
    }
    else {
      $msg1 = "Congratulations!!! <br> your password has been changed successfully.";
     }
}
uihelper_error_msg($msg1);
uihelper_get_network_style();
echo $page->render();
?>