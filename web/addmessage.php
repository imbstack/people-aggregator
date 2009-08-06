<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        addmessage.php, web file to send private messages
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file send private message among the users of 
 *              the network. It uses 
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
require_once "api/Message/Message.php";
require_once "web/includes/functions/auto_email_notify.php";

$mid = null;
$possible_actions = array('reply', 'forward', 'new');
$action = 'new'; //its a new message by default.
$error = false;
if ($_POST) {
  
  if (!empty($_POST['do_action'])) {
    if (in_array($_POST['do_action'], $possible_actions)) {
      $action = $_POST['do_action'];
    }
    
    if (!empty($_POST['mid'])) {
     // message id of the message for which some action has to be taken.
      $mid = $_POST['mid'];
    } else {
      // If mid is not set then its a save of a new message only.
      $action = 'new';
    }
  }
  
  //TODO: Move the static texts to messageHandler
  if (isset($_POST['send'])) {
    filter_all_post($_POST, TRUE);// applying input filter to the post data
    
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $in_reply_to = $_POST['in_reply_to'];
    
    if (empty($_POST['to'])) {      
      $message = 8003;
      $error = true;
    }
    
    if(strlen($body) > MAX_MESSAGE_LENGTH) {      
      $message = 8002;
      $error = true;
    }
    
    if (!$error) {
      if (empty($subject)) {
        $subject = '[none]';
      }
      $login_names = preg_split("/,\s*/", $_POST['to']);
      $found = array();//user id of all the valid login names
      $valid_recipients = array(); //login name of all the valid login names.
      $invalid_recipients = array();//names of all the invalid recipients.
      foreach ($login_names as $login_name) {
        try {
          $User = new User();
          $User->load($login_name);
          $valid_recipients['id'][] = $User->user_id;
          $valid_recipients['name'][] = $User->login_name;
          $valid_recipients['fname'][] = $User->first_name;
          $valid_recipients['email'][] = $User->email;
        } catch (PAException $e) {
          $invalid_recipients[] = $login_name;
        }
      }

      $message = null;
      if (count($valid_recipients)) {
        $is_draft = FALSE;
        Message::add_message($login_uid, $valid_recipients['id'], $valid_recipients['name'], $subject, $body, $is_draft, $in_reply_to);      
        for ($counter = 0; $counter < count($valid_recipients['id']); $counter++) {
          $_sender_url = url_for('user_blog',  array('login'=>$_SESSION['user']['name']));
          $sender_url = "<a href=\"$_sender_url\">$_sender_url</a>";
          $params = array(
              'first_name_sender' => $_SESSION['user']['first_name'],
              'first_name_recipient' =>$valid_recipients['fname'][$counter],
              'sender_id' => $login_uid,
              'recipient_id' =>  $valid_recipients['id'][$counter], 
              'recipient_email' => $valid_recipients['email'][$counter],
              'sender_url' => $sender_url,
              'my_messages_url' => '<a href="' . PA::$url . PA_ROUTE_MYMESSAGE . '">' . PA::$url . PA_ROUTE_MYMESSAGE .'</a>'
          );
          auto_email_notification('msg_waiting_blink', $params);
        }
        $message = __('Message sent successfully to ').implode(",", $valid_recipients['name']).'<br />';
      }
 
      if (count($invalid_recipients)) {
        //some of the recipients are invalid. So displaying the error message for them.
        $message .= 'Message sending failed for '.implode(",", $invalid_recipients).' as user(s) doesn\'t exist';
        $error = true;
      } else {
        // message sent successfully to all the recipients. Redirecting user to inbox.        
        header("Location: ". PA::$url . PA_ROUTE_MYMESSAGE ."/msg=message_sent");
        exit;
      }
    }
    
  }
  
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
function setup_module($column, $moduleName, $obj) {
  global $action, $login_uid, $page_uid;
  switch ($column) {    
    case 'middle':
      if (!empty($_POST['mid'])) {
        $obj->mid = $_POST['mid'];
      }
      $obj->action = $action;
      $obj->uid = $login_uid;
      if (!empty($page_uid)) {
        $obj->page_uid = $page_uid;
      }
    break;
  }
  $obj->mode = PUB;  
}
//renders the modules on the page
$page = new PageRenderer("setup_module", PAGE_ADDMESSAGE, "Compose", "container_one_column.tpl", 'header.tpl', PRI, HOMEPAGE, PA::$network_info);

uihelper_set_user_heading($page);
if (!empty($message)){
  uihelper_error_msg($message);
}  

echo $page->render();
?>