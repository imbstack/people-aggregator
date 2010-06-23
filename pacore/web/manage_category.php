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
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        manage_cotegory.php, web file to  Manage the category
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file manages information about category displayed within the application. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
 //anonymous user can not view this page;
  $login_required = TRUE;
  //including necessary files
  $use_theme = 'Beta';
  include_once("web/includes/page.php");
  require_once "web/includes/network.inc.php";
  include_once "api/Category/Category.php";
  $error = FALSE;
  $message = NULL;

  if (isset($_POST['cat_title']) && trim($_POST['cat_title']) == "") {
    $msg []= "Enter category title";
  }

if (empty($error)) {
  if ($_POST && empty($msg) && $_GET['a']!="edit") {
    $new_cat = new Category();
    $new_cat->name = trim($_POST['cat_title']);
    $new_cat->description = trim($_POST['cat_description']);
    $new_cat->parent_id = $_POST['parent_id'];
    $new_cat->type = $_POST['type'];
    $new_cat->save();
    header("Location: manage_category.php?msg_id=14001&type=".$_POST['type']);
  }
  //edit category.
  if (!empty($_GET['a']) && $_GET['a'] == "edit" && $_POST && empty($msg) ) {
    $new_cat = new Category();
    $new_cat->category_id = $_POST['category_id'];
    $new_cat->name = $_POST['cat_title'];
    $new_cat->description = $_POST['cat_description'];
    $new_cat->type = $_POST['type'];  
    $new_cat->save();
    header("Location: manage_category.php?msg_id=14002");
  }
  
  if(!empty($_GET['a']) && $_GET['a'] == "delete" && !empty($_GET['cat_id'])){
    Category::delete($_GET['cat_id']);
    header("Location: manage_category.php?msg_id=14003&type=".$_GET['type']);
  }
}

/*  ---------- FUNCTION DEFINITION ------------------*/
//call back function
function setup_module($column, $module, $obj) {
  global $form_data, $edit, $paging;
  switch ($module) {
   }  
}
$page = new PageRenderer("setup_module", PAGE_MANAGE_CATEGORY, "Manage Category", 'container_two_column.tpl','header.tpl',PRI,HOMEPAGE,PA::$network_info);

if (!empty($msg)) {
  for ($counter = 0; $counter < count($msg); $counter++) {
    $message .= $msg[$counter]."<br>";
  }
}
// display message
if (!empty($_GET['msg_id'])) {
  $message = $_GET['msg_id'];
}
uihelper_error_msg(@$message);

$page->html_body_attributes ='class="no_second_tier network_config"';
uihelper_get_network_style($page);

echo $page->render();
?>