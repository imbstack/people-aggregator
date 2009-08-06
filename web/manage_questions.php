<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        manage_ad_center.php, web file to  Manage the ads
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file manages information about ads displayed within the application. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */

// logged in user can view this page
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.

//including necessary files
include_once("web/includes/page.php");
require_once "ext/Question/Question.php";


//setting variables 
global $login_uid;

$error = FALSE;
$authorization_required = TRUE;

if(!$error) { 
  // Code for saving the data
  if(!empty($_POST['save'])) {
    $question = new Question();
    if(!empty($_POST['question_id'])) {
      $question->update_question($_POST['question_id'], ACTIVE);
      $message = __('Question is set for all users');
    }
    else {
      $message = __('Please select a question to activate.');
    }
  }
  
  // Add empty condition;
  if (@$_POST['create_question']) {
    $question = new Question();
    $question->author_id = $login_uid;
    if(!empty($_POST['body'])) {
      $question->body = $_POST['body'];
      $question->save();
      $message = __('Question has been successfully saved'); 
    }
    else {
      $message = __('Fields marked with * must not be left empty');
    }
  }
  
  if (@$_GET['action'] == 'delete') {
    $question = new Question();
    if(!empty($_GET['content_id'])) {
      $question->delete($_GET['content_id']);
      $message = __('Question has been deleted successfully');
    }
  }
}
/*  ---------- FUNCTION DEFINITION ------------------*/
//call back function
function setup_module($column, $module, $obj) {
  global $paging;
  switch ($module) {
    case 'ManageQuestionsModule':
      // $obj->edit = $edit; // this seems to be set nowhere
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 10;    
    break;
  }  
}

$page = new PageRenderer("setup_module", PAGE_MANAGE_QUESTIONS, "Manage
Questions", 'container_two_column.tpl', 'header.tpl',
PRI, HOMEPAGE, PA::$network_info
);

uihelper_error_msg(@$message);

$page->html_body_attributes ='class="no_second_tier network_config"';
uihelper_get_network_style();

echo $page->render();
?>