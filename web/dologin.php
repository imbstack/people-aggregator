<?php
/*
 * Project:     PeopleAggregator: a social network developement platform
 * File:        dologin.php, file to authorize user
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file uses User api to login the user into the system.
                Login check is required when user wants to perform some 
                action.
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = FALSE;
$login_never_required = TRUE; // because this page must be visible even if you are not logged in and on a private network!
$use_theme = 'Beta';
include_once("web/includes/page.php");
require_once "api/Invitation/Invitation.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Relation/Relation.php";
//require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Login/PA_Login.class.php";
require_once "api/Messaging/MessageDispatcher.class.php";

// Getting all variable through get/post
$invitation_id = ( @$_GET['InvID'] ) ? $_GET['InvID'] : $_POST['InvID'] ;
$group_invitation_id = ( @$_GET['GInvID'] ) ? $_GET['GInvID'] : $_POST['GInvID'] ;
$token = (@$_GET['token']) ? $_GET['token'] : @$_POST['token'];

if ($_REQUEST['return']) {
  $return_url = $_REQUEST['return'];
}

if (@$_POST['submit'] || @$_GET['action'] == 'login') { // if form is submitted
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  if (!$username || !$password) { //if username/password is empty
    $msg = "Error: Login name or Password cannot be empty";
    //header("Location:homepage.php?msg=$msg&return=$return_url");
    $location = "login.php?msg=$msg";
    if ($invitation_id) {
      $location .= "&amp;InvID=$invitation_id";
    }
    else if ($group_invitation_id) {
      $location .= "&amp;GInvID=$group_invitation_id";
    }
    if ($token) {
      $location .= "&amp;token=$token";
    }
    if ($return_url) {
      $location .= "&return=$return_url";
    }  
    header("Location:$location");
    exit;
  }

  // username and password supplied - attempt to authenticate
  try {
    $u = User::authenticate_user($username, $password);
  } catch (PAException $e) {
    $msg = "Error: $e->message";
    $error = TRUE;
    $u = FALSE;
  }  
  if ($u > 0) { // if authetication succeeded
    $pal = new PA_Login();
    $remember_me = (isset($_POST['remember']) && $_POST['remember'] == 1);
    $pal->log_in($u, $remember_me, "password");
    // verify token
    if (!empty($token)) { // if token isn't empty
       try {
         $token_arr = authenticate_invitation_token($token);
       } catch(PAException $e) {
         $token_arr[1] = "$e->message";
       }
    }
    // if token is empty
    if (empty($token)) {
      // $location = PA_ROUTE_USER_PRIVATE;
      // RODO: add handling for user defined setting of start page
      $location = PA_ROUTE_HOME_PAGE;
    }
    else if ($token_arr[0] == TRUE && $token_arr[1] == $_SESSION['user']['email']) {
      if($invitation_id) { // if token is valid
	$user_accepting_inv_obj = new User();
	$user_accepting_inv_obj->load((int)$u);        
	try { // check token before accepting invitation
	  $new_invite = new Invitation();
	  $new_invite->inv_id = $invitation_id;
	  $new_invite->inv_user_id = $u;
	  $new_invite->accept();
	  $inv_obj = Invitation::load($invitation_id);
	  $user_obj = new User();
	  $user_obj->load((int)$inv_obj->user_id);
	  $relation_type_id = Relation::get_relation((int)$inv_obj->user_id, (int)$user_accepting_inv_obj->user_id, PA::$network_info->network_id);
	  $relation_type = Relation::lookup_relation_type($relation_type_id);	  
      $new_invite->inv_relation_type = $relation_type;
      PANotify::send("invitation_accept", $user_obj, $user_accepting_inv_obj, $new_invite);

/*  - Replaced with new PANotify code  

      $invited_user_url = url_for('user_blog', array('login'=>$user_accepting_inv_obj->login_name));
	  // data for passing in common mail method
	  $array_of_data = array('first_name' => $user_accepting_inv_obj->first_name,
				 'last_name' => $user_accepting_inv_obj->last_name, 
				 'user_name' => $user_accepting_inv_obj->login_name, 
				 'user_id' => $user_accepting_inv_obj->user_id,
				 'invited_user_id' => $inv_obj->user_id,
				 'invited_user_name' => $user_obj->login_name,
                 'recipient_username' => $user_obj->login_name, 
                 'recipient_firstname' => $user_obj->first_name, 
                 'recipient_lastname' => $user_obj->last_name, 
				 'mail_type' => 'invite_accept_pa',
				 'to' => $user_obj->email,
				 'network_name' => PA::$network_info->name,
				 'relation_type' => $relation_type,
         'config_site_name' => PA::$site_name,
         'invited_user_url' => "<a href=\"$invited_user_url\">$invited_user_url</a>");
	  auto_email_notification_members('invitation_accept', $array_of_data);
*/      
	  if(!empty(PA::$network_info) && PA::$network_info->type!=MOTHER_NETWORK_TYPE) {
	    if (!(Network::member_exists(PA::$network_info->network_id, $u))) { 
	      if (PA::$network_info->type == PRIVATE_NETWORK_TYPE && $inv_obj->user_id == PA::$network_info->owner_id) {
		$user_type = NETWORK_MEMBER;
	      }
	      Network::join(PA::$network_info->network_id, $u, $user_type);
          PANotify::send("network_join", PA::$network_info, $user_accepting_inv_obj, array());
/*  - Replaced with new PANotify code  
	      $params['uid'] = $u;
	      auto_email_notification('some_joins_a_network', $params );
*/          
	    }
	  }           
	} catch (PAException $e) {
	  $msg = "$e->message";
	  $error_inv = TRUE;
	}
	if ($error_inv == TRUE) { // if error occured
	  header("Location:login.php?msg=$msg&return=$return_url");
	  exit;
	}
	$location = PA_ROUTE_HOME_PAGE . '/msg=7016';
	// if no exception yet, set a success msg
      } // code for invitation accept ends here        
      
      if($group_invitation_id) { // accept group invitation
	$is_valid_ginv =  Invitation::validate_group_invitation_id($group_invitation_id);
	if (!$is_valid_ginv) {
	  throw new PAException(INVALID_INV, "Sorry you cant join this group. May be group no longer exists or you are using old invitation.");
	}
	$gid_invite = Invitation::load($group_invitation_id);
	if( Group::is_admin( $gid_invite->inv_collection_id, $logged_user->user_id)) {
	  $msg = "You are the moderator, you can not accept invitation of same group";
	  header("Location:login.php?msg=$msg&return=$return_url");exit;
	}            
	try {
	  $new_invite = new Invitation();
	  $new_invite->inv_id = $group_invitation_id;
	  $new_invite->inv_user_id = $u;
	  $new_invite->accept();
	  //get collection_id
	  $Ginv = Invitation::load($group_invitation_id);
	  $gid = $Ginv->inv_collection_id;                
	} catch (PAException $e) {
	  $msg = "$e->message";
	  $error = TRUE;
	  print $msg;
	}
	if (!empty($gid))
          header("Location: " . PA_ROUTE_GROUP . "/gid=$gid&action=join&GInvID=$group_invitation_id");exit;
      } // code for group invitation ends here
    } else { // if invalid token
      $msg = ($token_arr[0] == FALSE) ? $token_arr[1] : 7018;
      header("Location: ".PA_ROUTE_HOME_PAGE."/msg=$msg");exit;
    } 
    
    // redirect user
    if ($return_url) {
      // header("Location: ". PA::$url ."/$return_url");exit;
      // $return_url should already have the correct path
      header("Location: $return_url");exit;
    }   
    else {
      header("Location: ". PA::$url ."$location"); exit;
    }  
  } else { // if user is not authenticated
    $msg = (!empty($msg)) ? $msg : 'Error: Invalid login name or password';      
    $r = PA::$url.'/login.php?msg='.$msg;
    if ($invitation_id) {
      $r .= "&InvID=$invitation_id";
    }
    else if ($group_invitation_id) {
      $r .= "&GInvID=$group_invitation_id";
    }
    if ($token) {
      $r .= "&token=$token";
    }
    if ($return_url) {
      $r .= "&return=$return_url";
    }
    header("Location:$r");
    exit;
  }
} // end if form is submitted
?>
