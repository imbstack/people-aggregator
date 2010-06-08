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

$error = FALSE;
$error_msg = '';
$authorization_required = TRUE;

if (!empty($_POST)) {
	filter_all_post($_POST);
	$user = PA::$login_user;
	$value_to_validate = array('title'=>'Title','bulletin_body'=>'Bulletin body');
	foreach ($value_to_validate as $key=>$value) {
		$_POST[$key] = trim($_POST[$key]);
		if (empty($_POST[$key])) {
		$error_msg .= $value.' can not be empty<br>';
		}
	}
	if (empty($_POST['inbox']) && empty($_POST['mail']) && empty($_POST['network_home'])){
		// if no destination is selected
		$error_msg .= 'Please specify at least one destination';
	}
	
	if (!$error_msg) { // if no errors yet
		if (!empty($_POST['bulletins'])) { // send to all members
			$subject = $_POST['title'];
			$bull_message = $_POST['bulletin_body'];
			$from = (int)PA::$login_uid;
	
			if (PA::$network_info) { // getting network's users
				$param = array('network_id'=>PA::$network_info->network_id,'neglect_owner' =>FALSE);
				$to_member = Network::get_members($param);
			}
			if (!empty($to_member)) { // if member exists
				$no_reg_user = FALSE;
				foreach($to_member['users_data'] as $recipient) {
					PANotify::send("bulletin_sent", $recipient['user_obj'], 
					PA::$network_info, 
					array(
						'bulletin.message' => $bull_message,
						'bulletin.subject' => $subject
					));
				}
			}
			else { // else no registered member
				if ($_POST['inbox'] == 1 || $_POST['mail'] == 1) {
					$no_reg_user = TRUE;
				} else {
					$no_reg_user = FALSE;
				}
			}
			$terms = array();
			if (!empty($_POST['network_home'])) { // posting the bulletin to the community blog
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
			}
			if ($no_reg_user == TRUE) {
				$error_msg .= "No registered member in this network";
			}
			else {
				$error_msg .= " Bulletin has been sent ";
			}
		}
		else if (!empty($_POST['send_to_me_only'])) { // test send to admin user
			 if (!$error_msg) { // if no errors
				 $subject = $_POST['title'];
				 $bull_message = $_POST['bulletin_body'];
				 PANotify::send("bulletin_sent", $user, 
					PA::$network_info, 
					array(
						'bulletin.message' => $bull_message,
						'bulletin.subject' => $subject
					));
				 $error_msg = "Bulletin has been sent to you.";
			 }
		}
		else if (!empty($_POST['preview'])) { // if preview is selected.
			$subject = $_POST['title'];
			$bull_message = nl2br($_POST['bulletin_body']);
			$container_html = 'default_email_container.tpl';
			$email_container = & new Template('web/config/email_containers/'.$container_html);
			$email_container->set('subject', $subject);
			$email_container->set('message', $bull_message);
			$preview_msg = $email_container->fetch();
		}
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