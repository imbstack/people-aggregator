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
// we need to prevent premature sending of headers
ob_start();
 
$login_required = FALSE;
$login_never_required = TRUE; // because this page must be visible even if you are not logged in and on a private network!
$use_theme = 'Beta';
include_once("web/includes/page.php");
include_once "web/languages/english/MessagesHandler.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Relation/Relation.php";
//require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Invitation/Invitation.php";
require_once "web/includes/urls.php";
require_once "api/Messaging/MessageDispatcher.class.php";


// if user is logged in (and has entered their password recently) redirect to the User's page
if (
  isset(PA::$login_uid)
  && isset($_SESSION['login_source'])
  && $_SESSION['login_source'] == 'password'
  && empty($_GET['action'])
  && empty($_GET['enable']) // this is if we want to enable a new ID for this user
  && empty($_GET['auth']) // second step in a login form
  && empty($_GET['openid_mode']) // this is if we come in via OpenID and are already logged in
  && empty($_GET['GInvID'])
  )  {
  $location = PA::$url . PA_ROUTE_USER_PRIVATE;
  header("Location: $location");
  exit;
}

require_once "ext/ConfigurableText/ConfigurableText.php";

// middle content
if (isset($_POST['submit'])) {//this is code for forgot password
  $error = FALSE;
  $email_pass = User::get_user_data($_POST['email']);
  if ($email_pass['email_exist'] == TRUE) {
    User::send_email_to_change_password($_POST['email']);
  }
  else {
    $error = TRUE;
  }
}

if (!empty($_SESSION['user']['id']) && (isset($_GET['action']) && $_GET['action'] == 'accept') && !empty($_GET['token'])) {
  $token = NULL;
  if (!empty($_GET['token'])) {
    $token = $_GET['token'];
    try {
       $token_arr = authenticate_invitation_token($token);
     } catch(PAException $e) {
       $token_arr[1] = "$e->message";
     }
  }
  //write description of the case
  if($token_arr[0] == TRUE && $token_arr[1] == $_SESSION['user']['email']) {
  //if token is not specified at all or token is valid then accept invitation
    if (!empty($_GET['GInvID'])) {
      $group_invitation_id =  $_GET['GInvID'] ;
      try {
        $new_invite = new Invitation();
        $new_invite->inv_id = $group_invitation_id;
        $new_invite->inv_user_id = $_SESSION['user']['id'];
        $new_invite->accept();
        $Ginv = Invitation::load($group_invitation_id);
        $gid = $Ginv->inv_collection_id;
        }
        catch (PAException $e) {
          $msg = "$e->message";
          $error = TRUE;
          print $msg;
        }
        if (!empty($gid))
        header("Location: ". PA::$url . PA_ROUTE_GROUP . "/gid=$gid&action=join&GInvID=$group_invitation_id");exit;
    }
    if (!empty($_GET['InvID'])) {
        $invitation_id =  $_GET['InvID'] ;
        try {
          $new_invite = new Invitation();
          $new_invite->inv_id = $invitation_id;
          $new_invite->inv_user_id = $_SESSION['user']['id'];
          $new_invite->accept();
          $inv_obj = Invitation::load($invitation_id);
          $user_obj = new User();
          $user_obj->load((int)$inv_obj->user_id);
          $user_accepting_inv_obj = new User();
          $user_accepting_inv_obj->load((int)$_SESSION['user']['id']);
          $relation_type_id = Relation::get_relation((int)$inv_obj->user_id, (int)$user_accepting_inv_obj->user_id, PA::$network_info->network_id);
          $relation_type = Relation::lookup_relation_type($relation_type_id);
          $new_invite->inv_relation_type = $relation_type;
          PANotify::send("invitation_accept", $user_obj, $user_accepting_inv_obj, $new_invite);

/*  - Replaced with new PANotify code       

          $invited_user_url = url_for('user_blog', array('login'=>$user_obj->login_name));
          // data for passing in common mail method
          $array_of_data  = array('first_name' => $user_accepting_inv_obj->first_name,
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
                                  'config_site_name'=>PA::$site_name,
                                  'invited_user_url'=>"<a href=\"$invited_user_url\">$invited_user_url</a>");
        auto_email_notification_members('invitation_accept', $array_of_data);
*/        
        if (!Network::member_exists(PA::$network_info->network_id, (int)$_SESSION['user']['id'])) {
          Network::join(PA::$network_info->network_id, $_SESSION['user']['id']);
          PANotify::send("network_join", PA::$network_info, $user_accepting_inv_obj, array());
/*  - Replaced with new PANotify code  
          $params['uid'] = $u;
          auto_email_notification('some_joins_a_network', $params );
*/          
        }
          header("Location: " . PA::$url . PA_ROUTE_USER_PRIVATE . '/' . "msg_id=7016");
          exit;
      }
      catch (PAException $e) {
        $msg = $e->message;
      }
    }
  } // end if token is valid
  else {
    $msg = ($token_arr[0] == FALSE) ? $token_arr[1] : 7018;
    header("Location: " .PA_ROUTE_HOME_PAGE. "/msg=$msg");exit;
  }
}

$ConfigurableText = new ConfigurableText();
$render_text_array = $ConfigurableText->load ( NULL, 1);

function setup_module($column, $moduleName, $obj) {
    global $content_type, $users,$uid,$_GET,$user;

    switch ($column) {
    case 'left':
        if (PA::$network_info->type == PRIVATE_NETWORK_TYPE) {
          return 'skip';
        }
        $obj->mode = PRI;
        if ($moduleName != 'LogoModule') {
            $obj->block_type = HOMEPAGE;
         }
     break;

    case 'middle':
    break;

    case 'right':
        if (PA::$network_info->type == PRIVATE_NETWORK_TYPE) {
          return 'skip';
        }
        $obj->mode = PRI;
    break;
    }
}

$page = new PageRenderer("setup_module", PAGE_LOGIN, "Login page", "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info);

// added by Zoran Hron: JQuery validation & AJAX file upload --
$page->add_header_html(js_includes('jquery.validate.js'));
$page->add_header_html(js_includes('jquery.metadata.js'));
$page->add_header_html(js_includes('ajaxfileupload.js'));
$page->add_header_html(js_includes('user_registration.js'));
// ------------------------------------------------------------
$page->add_header_html(js_includes('common.js'));
$page->add_header_html(js_includes('login.js'));


if(isset($_GET['msg_id'])){
  $msg = MessagesHandler::get_message($_GET['msg_id']);
}
if (isset($msg)) {
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg);
  $page->add_module("middle", "top", $msg_tpl->fetch());
}
$page->html_body_attributes ='class="no_second_tier"';

$page->header->set('render_text_array',$render_text_array);
uihelper_get_network_style();
echo $page->render();

?>
