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

// POST handler for MessageModule

require_once "api/Message/Message.php";

//TODO: To put all the static texts to MessageHandler.

$folder_name = empty($_GET['folder']) ? INBOX : $_GET['folder'];
$message = "";

//code for creating new folder in messages for the user
if (!empty($_form['new_folder'])) {
  
  $error = false;
  // input validation for the folder name. Only alpha numerics are allowed.
  if (!Validation::validate_alpha_numeric($_form['new_folder'], true)) {
    $message = __('Folder creation failed. ').$_form['new_folder'].__(' is not a valid folder name.');
    $error = true;
  }    
  
  if (!$error) {
    if (Message::create_folder(PA::$login_uid, $_form['new_folder'])) {
      $message = $_form['new_folder'].' folder created successfully';
      $folder_name = $_form['new_folder'];
    } else {
      $message = __('Folder creation failed. You already have a folder named ').$_form['new_folder'];
    }
  }
  
}

if (!empty($_form['form_action'])) {
  if ($_form['form_action'] == 'delete') {
    //code for deleting the messages.
    if (!empty($_form['index_id'])) {
      Message::delete_message($_form['index_id']);
      $msg_count = count($_form['index_id']);
      if ($msg_count == 1) {
	$message = __('1 message has been deleted successfully');  
      } else {
	$message = $msg_count.__(' messages have been deleted successfully');
      }
      
    }
  }
  
  if ($_form['form_action'] == 'move') {
    //code for moving the messages to some other folder.
    if ($_form['sel_folder'] == -1) {// $_form['sel_folder'] is -1 when there no folder has been made by the user
      $message = 'Please create a folder to move message(s)';
    } else {
      if (!empty($_form['index_id'])) {
	if ($folder_name != $_form['sel_folder']) {//destination folder should not be the same
	  $folder_id = Message::get_folder_by_name(PA::$login_uid, $_form['sel_folder']);
	  Message::move_message_to_folder($_form['index_id'], $folder_id,$_form['msgid']);
	  
	  $msg_count = count($_form['index_id']);
	  if ($msg_count == 1) {
	    $message = __('1 message has been moved to folder ').$_form['sel_folder'].__(' successfully');
	  } else {
	    $message = $msg_count.__(' messages have been moved to folder ').$_form['sel_folder'].__(' successfully');
	  }
	  
	  //setting the folder selected to the one where messages have been moved.
	  $folder_name = $_form['sel_folder'];
	} else {
	  $message = __('Selected message(s) are already in ').$folder_name.__(' folder');
	}
      } else {
	$message = __('Please select message(s) to move');
      }
    }
    
  }
}

//HACK: throw stuff back in $_GET so mymessage.php will get it and pass it to MessageModule on rendering.
$_GET['folder'] = $folder_name;
$_GET['message'] = $message;

?>