<?php
$login_required = FALSE;
$login_never_required = TRUE; // because this page must be visible even if you are not logged in and on a private network!
$use_theme = 'Default';
include_once("web/includes/page.php");
require_once "api/Invitation/Invitation.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Relation/Relation.php";
require_once "api/Login/PA_Login.class.php";
require_once "api/Messaging/MessageDispatcher.class.php";
require_once "web/includes/classes/UrlHelper.class.php";

// Getting all variable through get/post
$invitation_id = ( @$_GET['InvID'] ) ? $_GET['InvID'] : $_POST['InvID'] ;
$group_invitation_id = ( @$_GET['GInvID'] ) ? $_GET['GInvID'] : $_POST['GInvID'] ;
$token = (@$_GET['token']) ? $_GET['token'] : @$_POST['token'];

if (!empty($_REQUEST['return'])) {
  $return_url = $_REQUEST['return'];
}

if (@$_POST['submit'] || @$_GET['action'] == 'login') { // if form is submitted
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  if (!$username || !$password) { //if username/password is empty
    $msg = "Error: Login name or Password cannot be empty";
    //header("Location:homepage.php?msg=$msg&return=$return_url");
//    $location = "login.php?msg=$msg";
    $location = UrlHelper::url_for(PA::$url . '/login.php?action=login', array(), 'https');
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
      $location = PA::$after_login_page;
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

	  if(!empty(PA::$network_info) && PA::$network_info->type!=MOTHER_NETWORK_TYPE) {
	    if (!(Network::member_exists(PA::$network_info->network_id, $u))) {
	      if (PA::$network_info->type == PRIVATE_NETWORK_TYPE && $inv_obj->user_id == PA::$network_info->owner_id) {
		$user_type = NETWORK_MEMBER;
	      }
	      Network::join(PA::$network_info->network_id, $u, $user_type);
          PANotify::send("network_join", PA::$network_info, $user_accepting_inv_obj, array());
	    }
	  }
	} catch (PAException $e) {
	  $msg = "$e->message";
	  $error_inv = TRUE;
	}
	if ($error_inv == TRUE) { // if error occured
      $location = UrlHelper::url_for(PA::$url . '/login.php', array('msg'=>$msg, 'return'=>$return_url), 'https');
      header("Location: $location");
//	  header("Location:login.php?msg=$msg&return=$return_url");
	  exit;
	}
	$location = PA::$after_login_page . '/msg=7016';
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
      $location = UrlHelper::url_for(PA::$url . '/login.php', array('msg'=>$msg, 'return'=>$return_url), 'https');
      header("Location: $location");
//	  header("Location:login.php?msg=$msg&return=$return_url");exit;
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
//    $r = PA::$url.'/login.php?msg='.$msg;
    $r = UrlHelper::url_for(PA::$url . '/login.php', array('msg'=>$msg), 'https');
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
