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
// moved this out of register.php

require_once "api/User/Registration.php";
require_once "api/Content/Content.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Relation/Relation.php";
require_once "api/Comment/Comment.php";
require_once "api/Message/Message.php";
require_once 'web/includes/classes/file_uploader.php';
//require_once "web/includes/functions/mailing.php";
//require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Messaging/MessageDispatcher.class.php";

class RegistrationPage {
  public $silent;
  
  public function __construct($silent = false ) {
    $this->silent = $silent; // silent - do not send any notif. messages and skip email verification!
  }
  
  function handle_uploaded_avatar_pic() {
    if(isset($_POST['user_filename']))
       $_POST['user_filename'] = Storage::validateFileId($_POST['user_filename']);
    if (!empty($_FILES['userfile']['name'])) {
      // process uploaded image file
      $myUploadobj = new FileUploader;
      $file = $myUploadobj->upload_file(PA::$upload_path,'userfile',true,true,'image');
      if (!$file) throw new PAException(FILE_NOT_UPLOADED, $myUploadobj->error);
    } else {
      // download given image url
      $avatar_url = trim(@$_REQUEST['avatar_url']);
      if (!empty($avatar_url) && preg_match("|http://(.*?)/(.*)|", $avatar_url, $m)) {
	list(, $uf_server, $uf_path) = $m;
	$file = Storage::save($avatar_url, basename($uf_path), "critical", "image");
	if (!$file) throw new PAException(FILE_NOT_UPLOADED, sprintf(__("Could not retrieve file from URL: %s"), $avatar_url));
      }
    }
    if (@$file) {
      $_POST['user_filename'] = $file;
      $_POST['avatar_url'] = '';
    }
  }

  function handle_join() {
    $error_inv = false;
    $invitation_id = (isset($_REQUEST['InvID'])) ? $_REQUEST['InvID'] : null;
    $group_invitation_id = (isset($_REQUEST['GInvID'])) ? $_REQUEST['GInvID'] : null;

    $mother_network_info = Network::get_mothership_info();
    $extra = unserialize($mother_network_info->extra);
    if (!$this->reg_user->register($_POST, PA::$network_info)) {
      // registration failed
      return;
    }

    // If the user is joining a network other than the 
    if ($mother_network_info->network_id != PA::$network_info->network_id) {
	    Network::join(1, $this->reg_user->newuser->user_id, NETWORK_MEMBER);
    }

    if (($extra['email_validation'] == NET_NO) || $this->silent) {  // silent registration - no email validation!
      // Success!
      if(!$this->silent) {
        register_session($this->reg_user->newuser->login_name, $this->reg_user->newuser->user_id,
                         $this->reg_user->newuser->role,
                         $this->reg_user->newuser->first_name, $this->reg_user->newuser->last_name,
                         $this->reg_user->newuser->email,
                         $this->reg_user->newuser->picture);
        $_SESSION['login_source'] = 'password'; // password recently entered, so enable access to edit profile
        PANotify::send("new_user_registered", PA::$network_info, $this->reg_user->newuser, array());
      }
  if($invitation_id) { // if an invitation to join a network
	    $this->inv_error = "";
	    $is_valid = Invitation::validate_invitation_id($invitation_id);
	    if (!$is_valid) {
	      $msg = 7017; // invalid network invitation
	    }
	if (empty($msg)) {
	  try { // try to except invitation
	    $new_invite = new Invitation();
	    $new_invite->inv_id = $invitation_id;
	    $new_invite->inv_user_id = $this->reg_user->newuser->user_id;
	    $new_invite->accept();
	    $inv_obj = Invitation::load($invitation_id);
	    $user_obj = new User();
	    $user_obj->load((int)$inv_obj->user_id);
	    //if invitation is for private network
	    if (PA::$network_info->type == PRIVATE_NETWORK_TYPE) {
	      $user_type = NULL;
	      if (PA::$network_info->owner_id == $inv_obj->user_id) {
		    $user_type = NETWORK_MEMBER;
	      }
	      Network::join(PA::$network_info->network_id, $this->reg_user->newuser->user_id, $user_type);
	    }
	    $msg = 7016;
        $relation_type = null;
        $relationship_level = 2;    //default relation level id is 2 for friend
        try {
          $relation_type_id = Relation::get_relation((int)$inv_obj->user_id, (int)$this->reg_user->newuser->user_id, PA::$network_info->network_id);
        } catch (PAException $e) {
          Relation::add_relation((int)$inv_obj->user_id, (int)$this->reg_user->newuser->user_id, $relationship_level, PA::$network_info->address, PA::$network_info->network_id, NULL, NULL, NULL, true, APPROVED);
          $relation_type = Relation::lookup_relation_type($relation_type_id);
        }

        $new_invite->inv_relation_type = $relation_type;
        if(!$this->silent) {
          PANotify::send("invitation_accept", $user_obj, $this->reg_user->newuser, $new_invite);
        }  
	  } catch (PAException $e) {
	    $this->inv_error = $e->message;
	    $this->reg_user->msg = "$e->message";
	    $error_inv = TRUE;
	  }
	  if ($error_inv == TRUE) { // if invitation fails, then do login again
	    header("Location: ".PA::$url."/login.php?msg=".$this->reg_user->msg."&return=$return_url");
	    exit;
	  }
	}
	$redirect_url = PA_ROUTE_HOME_PAGE . '/msg='.$msg;
      }
      else if ($group_invitation_id) { // if an invitation to join a group
	// User registration is in response to a group invitation, so
	// now that the user is registered, handle the group invitation.
	try {
	  $is_valid_ginv =  Invitation::validate_group_invitation_id($group_invitation_id);
	  if (!$is_valid_ginv) {
	    $msg = 3001;
	  }
	} catch (PAException $e) {
	  $this->inv_error = "$e->message";
	}
	if (empty($msg)) { //if group invitation is valid, and no error yet
	  try {
	    $new_invite = new Invitation();
	    $new_invite->inv_id = $group_invitation_id;
	    $new_invite->inv_user_id = $this->reg_user->newuser->user_id;
	    $new_invite->accept();
	    //get collection_id
	    $Ginv = Invitation::load($group_invitation_id);
	    $gid = $Ginv->inv_collection_id;
        $relationship_level = 2;    //default relation level id is 2 for friend
        try {
          $relation_type_id = Relation::get_relation((int)$Ginv->user_id, (int)$this->reg_user->newuser->user_id, PA::$network_info->network_id);
        } catch (PAException $e) {
            Relation::add_relation((int)$Ginv->user_id, (int)$this->reg_user->newuser->user_id, $relationship_level, PA::$network_info->address, PA::$network_info->network_id, NULL, NULL, NULL, true, APPROVED);
            Relation::add_relation((int)$this->reg_user->newuser->user_id, (int)$Ginv->user_id, $relationship_level, PA::$network_info->address, PA::$network_info->network_id, NULL, NULL, NULL, true, APPROVED);
        }
	  } catch (PAException $e) {
	    $this->reg_user->msg = "$e->message";
	    $this->reg_user->error = TRUE;
	    print $this->reg_user->msg;
	  }
	  $redirect_url = PA_ROUTE_GROUP . "/gid=$gid&action=join&GInvID=$group_invitation_id";
	} else { //else redirect registered user to its page.
	  $redirect_url = PA_ROUTE_USER_PRIVATE . '/' . "msg_id=$msg";
	} // end of if group invitation is valid
      }
      if (empty($redirect_url)) { // if no url is set yet
	// not a group invitation, so redirect to private user page when done
        $msg = 5003;
        $redirect_url = PA_ROUTE_USER_PRIVATE . '/' . "msg_id=$msg";
      }
      header("Location: ".PA::$url . $redirect_url);
      exit;
    } // end if email_validation is set
    else {
      $expires = LONG_EXPIRES; // for 15 days
      $user = new User();
      $user->login_name = $this->reg_user->newuser->login_name;
      $user->password = $this->reg_user->newuser->password;
      $token = $user->get_auth_token($expires);
      if (!empty($invitation_id)) {
	$invitation = '&InvID='.$invitation_id;
      } else if (!empty($group_invitation_id)) {
	$invitation = '&GInvID='.$group_invitation_id;
      } else {
	$invitation = NULL;
      }
      $user_type = NETWORK_WAITING_MEMBER;
      Network::join(PA::$network_info->network_id, $this->reg_user->newuser->user_id, $user_type);
      if(!$this->silent) {
        $activation_url = PA::$url.'/mail_action.php?action=activate&token='.$token.$invitation;
        PAMail::send("activate_account", $this->reg_user->newuser, PA::$network_info, array('account.activation_url' => $activation_url));
      }
      global $app;
      $er_msg = urlencode("Check your email for activation code.");
      $app->redirect(PA::$url . PA_ROUTE_SYSTEM_MESSAGE . "?show_msg=7013&msg_type=info&redirect_url=" . urlencode(PA::$url . '/' . FILE_LOGIN));

    } //end if email validation is set
  }

  function main() {
    // instantiate now so we can store errors about file upload
    $this->reg_user = new User_Registration();

    // store or download avatar pic, if a file (on POST) or avatar_url (GET or POST) is given
    $this->handle_uploaded_avatar_pic();
    // --- the rest of the function is only executed for POST requests ---
    if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
    // only for registration, too
    if (@$_REQUEST['op'] != 'register') return;

    if (preg_match("/preview/i", @$_POST['submit'])) {
      // just previewing - skip actual registration
    }
    else if (@$_REQUEST['op'] == 'register') {
      // if registering, do it!
      $this->handle_join();
    }
  }

}
?>
