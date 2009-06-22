<?php

global $network_info;

require_once "api/Network/Network.php";
require_once "api/User/User.php";
require_once "ext/ReportAbuse/ReportAbuse.php";
require_once "api/Message/Message.php";

// Now adding the report abuse for network owner message box

filter_all_post($_POST);
$extra = unserialize($network_info->extra);

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

/*  - Replaced with new PANotify code   

      // here we find the Id of network owner
      if($network_info->type == MOTHER_NETWORK_TYPE) {
        $user_id = SUPER_USER_ID;
      }
      else {
        $user_id = Network::get_network_owner($network_info->network_id);
      }
      // Sender name
      $visitor_name = $_SESSION['user']['name'];
      $user = new User();
      $user->load((int)$user_id);

      // Loading the network owner
      $to_network_owner = $user->email;
      $network_name = $network_info->name;
      $cid = $_GET['cid'];
      $mail_type = 'report_abuse_for_comment';
      $_content_url = PA::$url . PA_ROUTE_CONTENT . '/cid='.$cid;
      $content_url = "<a href=\"$_content_url\">$_content_url</a>";
      //    $delete_url = PA::$url .'/deletecomment.php?comment_id='.$_POST['id'];
      $_delete_url = PA::$url .'/deletecomment.php?comment_id='.$_POST['id'];
      $delete_url = "<a href=\"$_delete_url\">$_delete_url</a>";
      $mail_sub_msg_array = array('login_name'=>$user->login_name,
     'recipient_username' => $user->login_name, 
     'recipient_firstname' => $user->first_name, 
     'recipient_lastname' => $user->last_name, 
      'visitor_name' => $visitor_name,
      'network_url' => PA::$url,
      'network_name' => $network_name,
      'message' => $_POST['abuse'],
      'content_url' => $content_url,
      'delete_url' => $delete_url,
      'config_site_name' => PA::$site_name);
      $error_message = 9003;

      switch(@$extra['notify_owner']['report_abuse_on_content']['value']) {
        case 1:
          $check = pa_mail($to_network_owner, $mail_type, $mail_sub_msg_array);
          break;

        case 2:
          send_message_to_user($user->login_name, null, $mail_sub_msg_array);
          break;

        case 3:
          $check = pa_mail($to_network_owner, $mail_type, $mail_sub_msg_array);
          send_message_to_user($user->login_name, null, $mail_sub_msg_array);
          break;
      }
*/
      try {
        $content = Content::load_content((int)$_REQUEST['cid'], (int)PA::$login_uid);
        if($content->parent_collection_id!= -1) {
          $collection = ContentCollection::load_collection((int)$content->parent_collection_id, PA::$login_uid);
          if($collection->type == GROUP_COLLECTION_TYPE) {
              PANotify::send("report_abuse_on_comment_grp_owner", $collection, PA::$login_user, $report_abuse_obj);

/* - Replaced with new PANotify code    

            $group_owner_id = Group::get_owner_id((int)$content->parent_collection_id);
            $group_owner = new User();
            $group_owner->load((int)$group_owner_id['user_id']);
            $to_group_owner = $group_owner->email;
            $mail_sub_msg_array['login_name'] = $group_owner->login_name;
            $mail_sub_msg_array['recipient_firstname']    = $group_owner->first_name;
            $mail_sub_msg_array['recipient_lastname']     = $group_owner->last_name;
            $mail_sub_msg_array['recipient_username']     = $group_owner->login_name;
            $mail_sub_msg_array['group_name'] = $collection->title;
            $mail_type = 'report_abuse_on_comment_grp_owner';

            //            $mail_sub_msg_array['delete_url'] .= '&gid='.$content->parent_collection_id;
            $_delete_url .= '&gid='.$content->parent_collection_id;
            $delete_url = "<a href=\"$_delete_url\">$_delete_url</a>";
            $mail_sub_msg_array['delete_url'] = $delete_url;

            //              $mail_sub_msg_array['group_url'] = PA::$url .'/group.php?gid='.$content->parent_collection_id;
            $_group_url = PA::$url  . PA_ROUTE_GROUP . '/gid='.$content->parent_collection_id;
            $gr_url = "<a href=\"$_group_url\">$_group_url</a>";
            $mail_sub_msg_array['group_url'] = $gr_url;

            $mail_sub_msg_array['config_site_name'] = PA::$site_name;
            $check = pa_mail($to_group_owner, $mail_type, $mail_sub_msg_array);
*/            
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

 /* - Replaced with new PANotify code
 
    if ($_SESSION['user']['id']) {
      $visitor_name = $_SESSION['user']['name'];
    } else {
      if (!empty($_POST['visitor_name'])) {
        $visitor_name = trim($_POST['visitor_name']);
      } else {
        $visitor_name = 'some one';
      }
    }
    if ($network_info->type == MOTHER_NETWORK_TYPE) {
      $user_id = SUPER_USER_ID;
    }
    else {
      $user_id = Network::get_network_owner($network_info->network_id);
    }
    $user = new User();
    $user->load((int)$user_id);
    $to_network_owner = $user->email;
    $network_name = $network_info->name;
    $cid = $_GET['cid'];
    $mail_type = 'report_abuse';

    $_content_url = PA::$url . PA_ROUTE_CONTENT . '/cid='.$cid;
    $content_url = "<a href=\"$_content_url\">$_content_url</a>";

    //      $delete_url = PA::$url .'/deletecontentbynetadmin.php?cid='.$cid;
    $_delete_url = PA::$url .'/deletecontentbynetadmin.php?cid='.$cid;
    $delete_url = "<a href=\"$_delete_url\">$_delete_url</a>";

    $mail_sub_msg_array = array('login_name'=>$user->login_name,
   'recipient_username' => $user->login_name, 
   'recipient_firstname' => $user->first_name, 
   'recipient_lastname' => $user->last_name, 
    'visitor_name' => $visitor_name,
    'network_url' => PA::$url,
    'network_name' => $network_name,
    'message' => $_POST['abuse'],
    'content_url' => $content_url,
    'delete_url' => $delete_url,
    'config_site_name' => PA::$site_name);
    $error_message = 9003;

    switch(@$extra['notify_owner']['report_abuse_on_content']['value']) {
      case 1:
        $check = pa_mail($to_network_owner, $mail_type, $mail_sub_msg_array);
        break;

      case 2:
        send_message_to_user($user->login_name, null, $mail_sub_msg_array);
        break;

      case 3:
        $check = pa_mail($to_network_owner, $mail_type, $mail_sub_msg_array);
        send_message_to_user($user->login_name, null, $mail_sub_msg_array);
        break;
    }
*/
    try {
      $content = Content::load_content((int)$_REQUEST['cid'], (int)PA::$login_uid);
      if($content->parent_collection_id!= -1) {
        $collection = ContentCollection::load_collection((int)$content->parent_collection_id, PA::$login_uid);
        if($collection->type == GROUP_COLLECTION_TYPE) {
            PANotify::send("report_abuse_grp_owner", $collection, PA::$login_user, $report_abuse_obj);

/*  - Replaced with new PANotify code 
   
          $group_owner_id = Group::get_owner_id((int)$content->parent_collection_id);
          $group_owner = new User();
          $group_owner->load((int)$group_owner_id['user_id']);
          $to_group_owner = $group_owner->email;
          $mail_type = 'report_abuse_grp_owner';
          $mail_sub_msg_array['login_name'] = $group_owner->login_name;
          $mail_sub_msg_array['group_name'] = $collection->title;
          $mail_sub_msg_array['recipient_firstname']    = $group_owner->first_name;
          $mail_sub_msg_array['recipient_lastname']     = $group_owner->last_name;
          $mail_sub_msg_array['recipient_username']     = $group_owner->login_name;

          //            $mail_sub_msg_array['delete_url'] .= '&gid='.$content->parent_collection_id;
          $_delete_url .= '&gid='.$content->parent_collection_id;
          $delete_url = "<a href=\"$_delete_url\">$_delete_url</a>";
          $mail_sub_msg_array['delete_url'] = $delete_url;


          //            $mail_sub_msg_array['group_url'] = PA::$url .'/group.php?gid='.$content->parent_collection_id;
          $_group_url = PA::$url  . PA_ROUTE_GROUP . '/gid='.$content->parent_collection_id;
          $gr_url = "<a href=\"$_group_url\">$_group_url</a>";
          $mail_sub_msg_array['group_url'] = $gr_url;

          $mail_sub_msg_array['config_site_name'] = PA::$site_name;
          $check = pa_mail($to_group_owner, $mail_type, $mail_sub_msg_array);
*/          
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

/**
   function is added for sending the mail of Abuse report 
   parameter required - sender name, subject of mail, message ..
*/

/* - Replaced with new PANotify code

function send_message_to_user($user_name, $suject=null, $mail_sub_msg_array) {
  // Adding the message for newtork owner
  global $network_info, $login_uid;

  $network_name = $network_info->name;
  $report_name = $_SESSION['user']['name'];
  $site_name = PA::$site_name;
  $message = $mail_sub_msg_array['message'];
  $content_url = $mail_sub_msg_array['content_url'];
  $delete_url  = $mail_sub_msg_array['delete_url'];

  if(empty($suject))
  $subject = "$report_name has reported an abuse about some content in your network $network_name";

  $msg ="<br>
      $report_name has reported an abuse about some comment in your network $network_name.<br>
      <br>
      $report_name reported:<br> $message
      <br>
      Click Here $content_url to view that comment as well as content.
      <br>
      <br>
      Click here $delete_url to delete that comment.
      <br>
      <br>
      Thanks<br>
      <br />
      The $site_name Team.
      <br />
      Everyone at $site_name respects your privacy. Your information will never be shared with third parties unless specifically requested by you.
      ";
  Message::add_message((int)$login_uid, null, $user_name, $subject, $msg);
  return;
}
*/
?>