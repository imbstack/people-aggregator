<?php
$login_required = TRUE;
include_once("web/includes/page.php"); 
require_once "api/Content/Content.php";

if (!isset($_GET['del_rep'])) { // When user delete the forum than all the repllies will be deleted
  $mid = $_GET['mid'];
  $params['action'] = 'edit_forum';
  
  $cond_array = array('boardmessage_id'=>$mid);
  $forum_detail = MessageBoard::get_forums($cond_array);
  
  
  $owner = Group::get_owner_id($forum_detail[0]->parent_id);
  $params['forum_owner'] = $forum_detail[0]->user_id;
  $params['group_owner'] = $owner['user_id'];
  
  if (user_can($params)) {
    try { 
      MessageBoard::delete_all_in_parent($mid, PARENT_TYPE_MESSAGE);
    } catch (Exception $e) {
      // catch if delete is fail 
    }  
    
    $location = $_SERVER['HTTP_REFERER'];
    header("Location: $location");
    exit;
  } 
} else {
// When User wants to delete repllies of forum
  $mid = $_GET['mid'];
  $params['action'] = 'delete_rep';
  
  // fiding the parent for the replly
  $request_info = load_info();
  $msg = new MessageBoard();
  $rep_details = $msg->get_by_id($_REQUEST['mid']);
  
  $cond_array = array('boardmessage_id'=>$request_info['parent_id']);
  $forum_detail = MessageBoard::get_forums($cond_array);
  
  $owner = Group::get_owner_id($_REQUEST['ccid']);
  $params['forum_owner'] = $forum_detail[0]->user_id;
  $params['rep_owner'] = $rep_details['user_id'];
  $params['group_owner'] = $owner['user_id'];
  
  if (user_can($params)) {
    try { 
      MessageBoard::delete($mid);
    } catch (Exception $e) {
      // catch if delete is fail 
    }  
    
    $location = $_SERVER['HTTP_REFERER'];
    header("Location: $location");
    exit;
  }
}
?>