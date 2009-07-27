<?php
require_once "api/Invitation/Invitation.php";
require_once "web/includes/urls.php";
require_once "api/Messaging/MessageDispatcher.class.php";

class GroupInvitationModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  
  public $outer_template = 'outer_public_center_module.tpl';
  public $user_groups = array();
  public $entered_people = array();
  public $subject;
  public $group;
  public $message;
  public $message_type;
  public $group_title;
  
  public function __construct() {
    parent::__construct();
    $this->title = __('Invite Into Groups');
  }
  
  public function initializeModule($request_method, $request_data) {
    if (empty(PA::$login_uid)) {
      return 'skip';
    }
  }

  public function handleGroupInvitationSubmit($request_method, $request_data) {
    switch($request_method) {
      case 'POST':
        if(method_exists($this, 'handlePOSTPageSubmit')) {  // function handlePOSTPageSubmit implemented?
           $this->handlePOSTPageSubmit($request_data);      // yes, use this function to handle POST data!
        }
    }
  }
  
  public function handlePOSTPageSubmit($request_data) {
    if (isset($request_data['submit'])) { 
      filter_all_post($request_data);
      $gid = $request_data['groups'];
      $self_invite = FALSE;
      $error = FALSE;
      // check if groups are there
      if (empty($gid)) {
        $error = TRUE;
        $msg[] = __("Please select a group");
      }  
      if (empty($error) && !empty($request_data['email_user_name'])) { // if login name are supplied 
        $friend_user_name = trim($request_data['email_user_name']);
        $friend_user_name_array = explode(',', $friend_user_name);
        $cnt_usr_name = count($friend_user_name_array);
        for ($counter = 0; $counter < $cnt_usr_name; $counter++) {
          try {
            $user_obj = new User();
            $user_obj->load(trim($friend_user_name_array[$counter]));
            if ($user_obj->email == PA::$login_user->email) { 
              $self_invite = TRUE; //you can not invite your self    
            } else {
              $valid_user_login_names[] = $user_obj->login_name;
              $valid_usr_name_email[] = $user_obj->email;
            } 
          } catch (PAException $e)   {
            if (!empty($friend_user_name_array[$counter])) {
              $invalid_login_msg .=  $friend_user_name_array[$counter] . ', ';
            } 
          }
        }  // end for
        if (!empty($invalid_login_msg)) {
          $invalid_login_msg = substr($invalid_login_msg, 0, -2);
          $msg[] = sprintf(__('Invitation could not be sent to following login names- %s'), $invalid_login_msg);
        }
      }  // end if : if user names are supplied.

      $invalid = null;
      if (empty($error) && !empty($request_data['email_id'])) {  // if email ids are supplied
        $friend_email = trim($request_data['email_id']);
        $friend_email_array = explode(',', $friend_email);
        $cnt_email = count($friend_email_array);
        // Check for valid-invalid email addresses start
        for ($counter = 0; $counter < $cnt_email; $counter++) {
          $email_validation = validate_email(trim($friend_email_array[$counter]));
          if ($email_validation == '0') {
            $invalid[] = trim($friend_email_array[$counter]);
          }
          else if ($friend_email_array[$counter] ==  PA::$login_user->email) {
            $self_invite = TRUE;
          } else {
            $valid_user_first_emails[] = $friend_email_array[$counter];
            $valid_email[] = trim($friend_email_array[$counter]);
          }
        }
      } 
      // Check for valid-invalid email addresses end
      // Action for valid-invalid email addresses start
      if (empty($friend_email) && empty($friend_user_name)) { // if email field is left empty
        $msg[] = MessagesHandler::get_message(6001);
        $error = TRUE;
      } else if (!empty($friend_email) && !empty($friend_user_name)) {
        $msg = array();
        $msg[] = MessagesHandler::get_message(7026);
        $error = TRUE;
      } else if (!empty($self_invite) || sizeof($invalid) > 0) { // if self invitation is made
        if (!empty($self_invite)) {
          $msg[] = MessagesHandler::get_message(6002);
        }
        if (!empty($invalid)) {
          // if invalid email addresses are supplied
          $invalid_cnt = count($invalid);
          $invalid_msg = '';
          for ($counter = 0; $counter < $invalid_cnt; $counter++) {
            if (!empty($invalid[$counter])) {
              $invalid_msg .= $invalid[$counter].', ';
            }  
          }
          if (!empty($invalid_msg)) {
            $invalid_msg = substr($invalid_msg, 0, -2);
            $msg[] = sprintf(__('Invitation could not be sent to following email addresses- %s'), $invalid_msg);
          }
        }
      }
      
      if (empty($error)) { // At this point invitation could be made
      
          if (!empty($valid_email) && !empty($valid_usr_name_email)) {
            $valid_email = array_merge($valid_email, $valid_usr_name_email);
            $valid_user_first_emails = array_merge($valid_user_first_emails, $valid_user_login_names);
          } else if(!empty($valid_usr_name_email)) {
            $valid_email = $valid_usr_name_email;
            $valid_user_first_emails = $valid_user_login_names;
          }
          
          if (!empty($valid_email)) {
            $valid_cnt = count($valid_email);
            $invitation_message = nl2br($request_data['message']);
            for ($counter = 0; $counter < $valid_cnt; $counter++) {
              $group = new Group();
              $group->load((int)$gid);
              $inv = new Invitation();
              $inv->user_id =  PA::$login_uid;
              $inv->username = PA::$login_user->login_name;
              // for invitation not for any group invitation collection id is -1
              $inv->inv_collection_id = $gid;
              $inv->inv_group_name = $group->title;
              $inv->inv_status = INVITATION_PENDING;
              $auth_token = get_invitation_token(LONG_EXPIRES, $valid_email[$counter]);
              $token = '&amp;token='.$auth_token;
              
              $link_desc = wordwrap(PA::$url . '/'.FILE_REGISTER."?GInvID=$inv->inv_id", 120, "<br>", 1);
              $inv->register_url = "<a href=\"". PA::$url . '/'.FILE_REGISTER."?GInvID=$inv->inv_id\">$link_desc</a>";
              
              $acc_link_desc = wordwrap(PA::$url . "/".FILE_LOGIN."?action=accept&GInvID=$inv->inv_id$token", 120, "<br>", 1);
              $inv->accept_url = "<a href=\"". PA::$url . "/".FILE_LOGIN."?action=accept&GInvID=$inv->inv_id$token\">$acc_link_desc</a>";
              $inv->inv_user_id = null;
              $inv->inv_user_first_name = $valid_user_first_emails[$counter];
              $inv->inv_email = $valid_email[$counter];
              $inv->inv_summary = sprintf(__("Invitation from %s %s to join %s"), PA::$login_user->first_name, PA::$login_user->last_name, $inv->inv_group_name);
              $inv->inv_message = !empty($invitation_message) ? $invitation_message : null;
              $save_error = false;
              try {
                $inv->send();
              }
              catch (PAException $e) {
                $save_msg = "$e->message";
                $save_error = true;
              }
              if ($save_error == true) {
                $msg[] = sprintf(__('Sorry: you are unable to invite a friend.  Reason: %s'), $sav_msg);
              } else {
              // invitation has been sent, now send email
              $user_type = Group::get_user_type(PA::$login_uid, $gid);
              if ($user_type == OWNER) {
                $mail_type = 'invite_group';
                $requester = $group;
              } else if ($user_type == MEMBER) {
                $mail_type = 'invite_group_by_member';
                $requester = PA::$login_user;
              }
              PAMail::send($mail_type, $inv->inv_email, $requester, $inv);

/*  - Replaced with new PANotify code  

              $invitee_image = uihelper_resize_mk_user_img(PA::$login_user->picture, 80, 80,'alt="'.PA::$login_user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
              
              $group_icon_image = uihelper_resize_mk_img($group->picture, 219, 35, DEFAULT_NETWORK_ICON,  'alt="'.$group->title.'"');
              
              $user_type = Group::get_user_type(PA::$login_uid, $gid);
              if ($user_type == OWNER) {
                $mail_type = 'invite_group';
              } else if ($user_type == MEMBER) {
                $mail_type = 'invite_group_by_member';
              }
              $group_url = PA::$url . PA_ROUTE_GROUP . '/gid='.$inv->inv_collection_id;
              $invitee_url = url_for('user_blog', array('login'=>PA::$login_user->login_name));
              $array_of_data = array(
              'first_name'=>PA::$login_user->first_name , 
              'last_name'=>PA::$login_user->last_name, 
              'user_name'=> PA::$login_user->login_name,
              'user_id'=>PA::$login_user->user_id, 
              'message'=>$inv->inv_message,
              'group_name'=> $inv->inv_group_name ,
              'accept_url'=>$inv->accept_url, 
              'register_url'=>$inv->register_url, 
              'invited_user_name'=> $inv->inv_user_first_name,
              'invitee_image'=>$invitee_image, 
              'group_id'=>$inv->inv_collection_id,
              'group_description' => $group->description,
              'group_icon_image' => $group_icon_image,
              'group_url' => "<a href=\"$group_url\">$group_url</a>",
              'network_name' => PA::$network_info->name,
              'invitee_url'=> "<a href=\"$invitee_url\">$invitee_url</a>",
              'config_site_name'=>PA::$site_name);
              $check = pa_mail($inv->inv_email, $mail_type, $array_of_data, PA::$login_user->email);
*/
              $succ_msg .= $valid_user_first_emails[$counter] . ', ';
              if ($counter == ($valid_cnt - 1)) {
                $succ_msg = substr($succ_msg, 0, -2);
                //$msg_1[] = "Invitation has been sent successfully to -" . $succ_msg;
              }
            }
          } // end for : invitation to multiple email
        }
      } 
    }//..do invite
    if (!empty($msg)) {
      $msg = array_reverse($msg);
      $message = NULL;
      for ($counter = 0; $counter < count($msg); $counter++) {
        $message .= $msg[$counter]."<br />";
      }
    }

    $msg_array = array();
    $msg_array['failure_msg'] = $message;
    $msg_array['success_msg'] = 6004;
    $redirect_url = PA::$url . PA_ROUTE_GROUP;
    if (!empty($request_data['groups'])) {
      $query_str = "gid=".$request_data['groups'];
    }
    set_web_variables($msg_array, $redirect_url, $query_str);
  }

  public function render() {
    $groups = Group::get_user_groups (PA::$login_uid, FALSE, 'ALL');
    $groups_count = count($groups);
    for ( $i=0; $i < $groups_count; $i++ ) {
        $this->user_groups[] = array('gid'=>$groups[$i]['gid'],'name'=>stripslashes($groups[$i]['name']));
    }
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  public function generate_inner_html () {
    switch ($this->mode) {
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    $register = & new Template($tmp_file);
    $register->set('message_type', $this->message_type);
    $register->set('group', $this->group);
    $register->set('group_title', $this->group_title);
    $register->set('user_groups', $this->user_groups);
    $inner_html = $register->fetch();
    return $inner_html;
  }
}
?>