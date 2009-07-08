<?php
$login_required = FALSE;

//including necessary files
include_once("web/includes/page.php");
global $network_info;
require_once "api/Message/Message.php";
require_once "api/Invitation/Invitation.php";
require_once "api/Messaging/MessageDispatcher.class.php";

$newuser = User::from_auth_token($_GET['token']);
// $newuser is a User object returned by from_auth_token function
if($newuser) {//if token is valid
  // if user activates his account
  if (isset($_GET['action']) && ($_GET['action'] == 'activate')) {
    if ($newuser->is_active == ACTIVE) { // if got verified
      header("Location: ". PA::$url . PA_ROUTE_HOME_PAGE . "/msg=7015");
      exit;
    }
    $activate_user = new User();      
    $activate_user->update_status($newuser->user_id, ACTIVE);    
    // providing defaults to new user
    // creating message basic folders
    Message::create_basic_folders($newuser->user_id);
    
    // adding default relation
    if ( $newuser->user_id != SUPER_USER_ID ) {
      User_Registration::add_default_relation($newuser->user_id, $network_info);
    }
    
    // adding default media as well as album
    User_Registration::add_default_media($newuser->user_id, '', $network_info);
    User_Registration::add_default_media($newuser->user_id, '_audio', $network_info);
    User_Registration::add_default_media($newuser->user_id, '_video', $network_info);
    User_Registration::add_default_blog($newuser->user_id);
    
    //adding default link categories & links
    User_Registration::add_default_links ($newuser->user_id);

    // Making user member of a network if he is registering to PA from a network
    if (!empty($network_info)) {

      if(!Network::member_exists($network_info->network_id, $newuser->user_id)) {  // check is waiting member
        Network::join($network_info->network_id, $newuser->user_id);              // no - join to network
      } else {
        Network::approve($network_info->network_id, $newuser->user_id);           // yes - approve membership  
      }  

    }
    // register session
    register_session($newuser->login_name,$newuser->user_id,$newuser->role,$newuser->first_name,$newuser->last_name,$newuser->email,$newuser->picture);
    PA::$login_user = $newuser;
    PA::$login_uid  = $newuser->user_id;
    // send welcome message
    PAMail::send("welcome_message", $newuser, PA::$network_info, array());

    if (!empty($_GET['InvID'])) { // if network invitation
      $invitation_id = $_GET['InvID'];
      $inv_error = "";
      $error_inv =false;
        try {
          $is_valid = Invitation::validate_invitation_id ($invitation_id);
          if (!$is_valid) {
            throw new PAException(INVALID_INV, "Sorry but the invitation ID you are using is no longer valid.");
          }
          $new_invite = new Invitation();
          $new_invite->inv_id = $invitation_id;
          $new_invite->inv_user_id = $newuser->user_id;
          $new_invite->accept();
        } catch (PAException $e) {
          $inv_error = $e->message;
          $error_inv = TRUE;
        }
        if ($error_inv == TRUE) { // if invitation fails, then do login again
          header("Location: ". PA::$url ."/login.php?msg=$inv_error&return=$return_url");
          exit;
        } else {
          $redirect_url = PA_ROUTE_USER_PRIVATE . '/' . 'msg_id=7014';
        }
    } else if (!empty($_GET['GInvID'])) { // if group invitation
        $group_invitation_id = $_GET['GInvID'];
	// User registration is in response to a group invitation, so
	// now that the user is registered, handle the group invitation.
        try {
          $is_valid_ginv =  Invitation::validate_group_invitation_id($group_invitation_id);
          if (!$is_valid_ginv) {
            $msg = 3001;
          }
        } catch (PAException $e) {
          $inv_error = "$e->message";
        } 
        if (empty($msg)) { //if group invitation is valid, and no error yet 
          try {
            $new_invite = new Invitation();
            $new_invite->inv_id = $group_invitation_id;
            $new_invite->inv_user_id = $newuser->user_id;
            $new_invite->accept();
            //get collection_id
            $Ginv = Invitation::load($group_invitation_id);
            $gid = $Ginv->inv_collection_id;
          } catch (PAException $e) {
            $inv_error = "$e->message";
          }
          $redirect_url = PA_ROUTE_GROUP . "/gid=$gid&action=join&GInvID=$group_invitation_id";
        } else { //else redirect registered user to its page.
          $redirect_url = PA_ROUTE_USER_PRIVATE . '/' . "msg_id=7014";
        } // end of if group invitation is valid
      } else {
        $redirect_url = PA_ROUTE_USER_PRIVATE . '/' . "msg_id=7014";
      }
      
    header("Location: ". PA::$url . $redirect_url);
    exit;
  } else {
      register_session($newuser->login_name,$newuser->user_id,$newuser->role,$newuser->first_name,$newuser->last_name,$newuser->email,$newuser->picture);
      PA::$login_user = $newuser;
      PA::$login_uid  = $newuser->user_id;
      if(isset($_GET['gid']))  { //if gid is available, redirect to group home page
        header("Location: ". PA::$url . PA_ROUTE_GROUP . "/gid=".$_GET['gid']);
        exit;
      }
      if(isset($_GET['aid']))  { //if gid is available, redirect to group home page
        header("Location: ". PA::$url ."/network_announcement.php?aid=".$_GET['aid']);
        exit;
      }
      if(isset($user->user_id)) { //if uid is set, then look for action
        if(isset($_GET['action']) && ($_GET['action']=='user')) {//redirect user to user's private page
          header("Location: ". PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user->user_id);
          exit;
        }
        if(isset($_GET['action']) && ($_GET['action']=='profile')) { //redirect user to edit his profile
          header("Location: ". PA::$url . PA_ROUTE_EDIT_PROFILE . "?uid=".$user->user_id);
          exit;
        }
        if(isset($_GET['uid'])) { //redirect someone to user's public page
          $login = User::get_login_name_from_id($_GET['uid']);
/*          
          $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$_GET['uid'];
          $url_perms = array('current_url' => $current_url,
                                    'login' => $login                  
                                  );
          $url = get_url(FILE_USER_BLOG, $url_perms);
*/          
          $url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login;
          header("Location: $url");
          exit;
        }
      } 
      if(isset($_GET['cid'])) { //redirect to content
        header("Location: ". PA::$url . PA_ROUTE_CONTENT . "/cid=".$_GET['cid']);
        exit;
      }
      if (isset($_GET['action']) && ($_GET['action'] == 'friend_request')) { // redirect to people who call me friend
        header("Location: ". PA::$url ."/view_all_members.php?view_type=in_relations&uid=".$newuser->user_id);
        exit;
      }
    } 
}
?>