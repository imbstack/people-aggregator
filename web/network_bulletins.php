<?php
//anonymous user can not view this page;
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
//including necessary files
include_once("web/includes/page.php");
include_once "api/ModuleSetting/ModuleSetting.php";
include_once "api/User/User.php";
include_once "api/Message/Message.php";
include_once "api/Network/Network.php";
require_once "web/includes/network.inc.php";
include_once "api/Theme/Template.php";
include_once "ext/BlogPost/BlogPost.php";
require_once "web/includes/functions/mailing.php";
//require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Messaging/MessageDispatcher.class.php";
require_once "web/includes/network.inc.php";

$error = FALSE;
$error_msg = '';
$authorization_required = TRUE;

filter_all_post($_POST);
if (!empty($_POST['bulletins']) && !$error) { // if no error and form is submitted
  $user = get_user(); // get logged in user
  $value_to_validate = array('title'=>'Title','bulletin_body'=>'Bulletin body');
  foreach ($value_to_validate as $key=>$value) {
    $_POST[$key] = trim($_POST[$key]);
    if (empty($_POST[$key])) {
    $error_msg .= $value.' can not be empty<br>';
    }
  }
  if (empty($_POST['inbox']) && empty($_POST['mail']) && empty($_POST['network_home'])){
    // if no destination is selected
    $error_msg .= 'Please specify atleast one destination';
  }
  if (!$error_msg) { // if no errors yet    
    $subject = $_POST['title'];
    $bull_message = $_POST['bulletin_body'];
    $from = (int)$_SESSION['user']['id'];
    
    if (PA::$network_info) { // getting network's users    
      $param = array('network_id'=>PA::$network_info->network_id,'neglect_owner' =>FALSE);
      $to_member = Network::get_members($param);
    } 
    if (!empty($to_member)) { // if member exists
      $no_reg_user = FALSE;

/*  - Replaced with new PANotify code     

      $owner_image = uihelper_resize_mk_user_img($user->picture, 80, 80,'alt="'.$user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
      $mail_subject = 'People Aggregator - '.PA::$network_info->name .' network\'s owner has sent you a bulletin';
      $mail_message = $subject .'<br /><br />'. $bull_message;
*/
      foreach($to_member['users_data'] as $recipient) { 
        PANotify::send("bulletin_sent", $recipient['user_obj'], PA::$network_info, array('bulletin.message' => $bull_message));
      }

/*  - Replaced with new PANotify code        

      $array_of_data = array('user_id'=>$user->user_id,'subject'=>$mail_subject,'message'=>$mail_message,'owner_image'=>$owner_image, 'user_name'=>$user->first_name, 'config_site_name'=>PA::$site_name);
      
      $net_owner = new User();
      $net_owner->load((int)PA::$network_info->owner_id);
      
      if (!empty($_POST['inbox'])) { // posting the bulletin to Inbox
          $count_user = $to_member['total_users'];
          $multiple_recipient = "";
          foreach($to_member['users_data'] as $to_user) {
          $to_uid = $to_user['user_id'];
          $to_name = $to_user['login_name'];
          $user_profile = User::load_user_profile($to_uid, $to_uid, 'notifications');
          if (!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']);
            $destination = $notify['bulletin_sent']['value'];
            if ($destination == NET_MSG || $destination == NET_BOTH) {
              try {
                Message::add_message($from, null, $to_name, $mail_subject, $mail_message);
              } catch (PAException $e) {
                // catch Nothing
                // This block of code is added, so that if folder isn't exist for 
                // a user, then it skipped that user
                // and continue to send bulletin to other users.
              }
              $my_messages_url = '<a href="' . PA::$url.'/'.FILE_MYMESSAGE . '">' . PA::$url.'/'.FILE_MYMESSAGE .'</a>';
              $_sender_url = url_for('user_blog', array('login'=>$user->login_name));
              $sender_url = "<a href=\"$_sender_url\">$_sender_url</a>";
              
              $params = array(
              'first_name_sender' => $user->login_name,
              'first_name_recipient' => $to_user['login_name'],
              'sender_id' => $user->user_id,
              'recipient_id' =>  $to_uid, 'recipient_email' =>   $to_user['email'],
              'my_messages_url' => $my_messages_url,
              'sender_url'=> $sender_url,
              'config_site_name'=> PA::$site_name,
              'network_owner_name' => $net_owner->first_name
                );
                auto_email_notification('msg_waiting_blink', $params);
              }
            }
          }
      }
      if (!empty($_POST['mail'])) { // posting the bulletin to registered email
        for ($i = 0; $i < count($to_member['users_data']); $i++ ) { 
          $to_uid = $to_member['users_data'][$i]['user_id'];
          $user_profile = User::load_user_profile($to_uid, $to_uid, 'notifications'); 
          if (!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']); 
            $destination = $notify['bulletin_sent']['value'];
            if ($destination == NET_EMAIL || $destination == NET_BOTH) {
              $inv_user_email = $to_member['users_data'][$i]['email'];
              $mail_type = 'bulletins';
              try {
                $check = pa_mail($inv_user_email, $mail_type, $array_of_data, $user->email);
              } catch ( PAException $e ) {
                $error_msg .= $e->message;
              }
            }
          }
        }  
      }
*/      
    }  
    else { // else no registered member
      if ($_POST['inbox'] == 1 || $_POST['mail'] == 1) {
        $no_reg_user = TRUE;
      }else {
        $no_reg_user = FALSE;
      }
    }
    $terms = array();
    if ($_POST['network_home'] == 1) { //posting the bulletin to the community blog
      if ($_POST['tags']) {
        $tags = explode(',', $_POST['tags']);
        foreach ($tags as $term) {
          $tr = trim($term);
          if ($tr) {
            $terms[] = $tr;
          }
        }
      } 
      try {
        
        $post_subject = "Network's owner bulletin - " . $_POST['title'];
        $post_message = $_POST['bulletin_body'];
        $res = BlogPost::save_blogpost(0, $from, $post_subject, $post_message, '', $terms, 0, $is_active = ACTIVE , $user->email);
      } catch (PAException $e) {
        $error_msg .= $e->message;
      }
      if(!empty($res['cid'])) {
        $content_obj = Content::load_content((int)$res['cid']);
        PANotify::send("content_posted_to_comm_blog", PA::$network_info, $user, $content_obj);
      }     
/*  - Replaced with new PANotify code      

      $permalink_cid = $res['cid'];
      $content_author_image = uihelper_resize_mk_user_img($user->picture, 80, 80,'alt="'.$user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
      $params['recipient_username'] = $net_owner->login_name; 
      $params['recipient_firstname'] = $net_owner->first_name; 
      $params['recipient_lastname'] = $net_owner->last_name; 
      $params['cid'] = $permalink_cid;  
      $params['first_name'] = $user->first_name;
      $params['user_id'] = $user->user_id;
      $params['user_image'] = $content_author_image;
      $params['content_title'] = @$_POST["blog_title"];
      $params['network_name'] = PA::$network_info->name;
      $params['network_owner_name'] = $net_owner->first_name;
      $params['config_site_name'] = PA::$site_name;
      $params['content_url'] = '<a href="' . PA::$url . PA_ROUTE_CONTENT . '/cid='.$permalink_cid .'&login_required=true">' . PA::$url . PA_ROUTE_CONTENT . '/cid='.$permalink_cid .'</a>';
      $params['content_moderation_url'] = '<a href="' . PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT . '">' . PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT .'</a>';
      // send notification to owner, as a content is being posted to comm. blog
      auto_email_notification('content_posted_to_comm_blog', $params);
*/      
    }
    if ($no_reg_user == TRUE) {
      $error_msg .= "No registered member in this network";
    }
    else {
      $error_msg .= " Bulletin has been sent ";
    }              
  }  
} else if (@$_POST['preview']) { // if preview is selected.
  filter_all_post($_POST);
  $subject = $_POST['title'];
  $bull_message = nl2br($_POST['bulletin_body']);
  // say $container_html is '/default_email_container.tpl'
  $container_html = 'default_email_container.tpl';
  $email_container = & new Template('web/Themes/Default/email_container/'.$container_html);
  $email_container->set('subject', $subject);
  $email_container->set('message', $bull_message);
  $preview_msg = $email_container->fetch();
} else if (@$_POST['send_to_me_only']) {
   $value_to_validate = array('title'=>'Title','bulletin_body'=>'Bulletin body');
   foreach ($value_to_validate as $key=>$value) {
     $_POST[$key] = trim($_POST[$key]);
     if (empty($_POST[$key])) {
      $error_msg .= $value.' can not be empty<br>';
     }
   }
   if (!$error_msg) { // if no errors
     filter_all_post($_POST);
     $subject = $_POST['title'];
     $bull_message = nl2br($_POST['bulletin_body']);
     $owner_image = uihelper_resize_mk_user_img($user->picture, 80, 80,'alt="'.$user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
     $send_to_me_array = array('user_id'=>$user->user_id, 'subject'=>$subject, 'message'=>$bull_message, 'owner_image'=>$owner_image, 'user_name'=>$user->first_name, 'config_site_name'=>PA::$site_name);
     $check = pa_mail($user->email, 'bulletins', $send_to_me_array, $user->email);
     $error_msg = "Bulletin has been sent to you.";
   }
}

function setup_module($column, $module, $obj) {
  global $preview_msg;
  switch ($module) {
    case 'NetworkBulletinsModule':
      $obj->preview_msg = $preview_msg;
    break;
  }
}
  $page = new PageRenderer("setup_module", PAGE_NETWORK_BULLETINS, "Network Bulletins", 'container_two_column.tpl','header.tpl',PRI,HOMEPAGE, PA::$network_info);

  if (!empty($error_msg)) {  
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $error_msg);
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}

$page->html_body_attributes ='class="no_second_tier network_config"';
$css_array = get_network_css();
if (is_array($css_array)) {
  foreach ($css_array as $key => $value) {
    $page->add_header_css($value);
  }
}

$css_data = inline_css_style();
if (!empty($css_data['newcss']['value'])) {
  $css_data = '<style type="text/css">'.$css_data['newcss']['value'].'</style>';
  $page->add_header_html($css_data);
}

echo $page->render();  
?>