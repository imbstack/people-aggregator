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


require_once "api/Network/Network.php";
require_once "api/User/User.php";
require_once "api/ReportAbuse/ReportAbuse.php";
require_once "api/Message/Message.php";

// Now adding the report abuse for network owner message box

filter_all_post($_POST);
$extra = unserialize(PA::$network_info->extra);

if(!empty($_POST['type']) && $_POST['type'] == 'comment') {

  // User must be loged in for sending the abuse report
  if (!empty($_POST['rptabuse']) && !empty(PA::$login_uid)) {
    $error_message="";
    try {
      // Saving the abuse report
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = TYPE_COMMENT;
      $report_abuse_obj->parent_id = $_POST['id'];
      $report_abuse_obj->reporter_id = PA::$login_uid;
      $report_abuse_obj->body = $_POST['abuse'];
      $id = $report_abuse_obj->save();
    }
    catch(PAException $e) {
      $error_message = $e->message;
    }

    $ccid_string = "";

    $abuse= trim($_POST['abuse']);
    if(!empty($abuse)) {
       PANotify::send("report_abuse_on_comment", PA::$network_info, PA::$login_user, $report_abuse_obj);
      try {
        $content = Content::load_content((int)$_REQUEST['cid'], (int)PA::$login_uid);
        if($content->parent_collection_id!= -1) {
          $collection = ContentCollection::load_collection((int)$content->parent_collection_id, PA::$login_uid);
          if($collection->type == GROUP_COLLECTION_TYPE) {
              PANotify::send("report_abuse_on_comment_grp_owner", $collection, PA::$login_user, $report_abuse_obj);

            $error_message = 9002;
          }
        }
      } catch (PAException $e) {
        //catch none
      }
    }
    else {
      $error_message = 9004;
    }
  }
}
// Code for sending Email to Network owner for abuse content..
$ccid_string = "";
if (!empty($_POST['rptabuse']) && !empty(PA::$login_uid) && !isset($_POST['type'])) {

  $error_message="";
  try {
    // Saving the abuse report
    $report_abuse_obj = new ReportAbuse();
    $report_abuse_obj->parent_type = TYPE_CONTENT;
    $report_abuse_obj->parent_id = $_GET["cid"];
    $report_abuse_obj->reporter_id = PA::$login_uid;
    $report_abuse_obj->body = $_POST['abuse'];
    $id = $report_abuse_obj->save();
  }
  catch (PAException $e) {
    $error_message = $e->message;
  }

  $ccid_string = "";
  if(!empty($_POST['ccid'])) {
    $ccid_string = "&ccid=".$_POST['ccid'];
  }
  $abuse= trim($_POST['abuse']);
  if (!empty($abuse)) {
    PANotify::send("report_abuse_on_content", PA::$network_info, PA::$login_user, $report_abuse_obj);
    try {
      $content = Content::load_content((int)$_REQUEST['cid'], (int)PA::$login_uid);
      if($content->parent_collection_id!= -1) {
        $collection = ContentCollection::load_collection((int)$content->parent_collection_id, PA::$login_uid);
        if($collection->type == GROUP_COLLECTION_TYPE) {
          PANotify::send("report_abuse_grp_owner", $collection, PA::$login_user, $report_abuse_obj);
          $error_message = 9002;
        }
      }
    } catch (PAException $e) {
      //catch none
    }
    $_POST = array();
  } else {
    $error_message = 9004;
  }
}
if(!empty($error_message)) {
  $location
  = PA::$url . PA_ROUTE_CONTENT . "/cid=".$_GET["cid"]."&err=".urlencode($error_message)
  .$ccid_string;
}
?>