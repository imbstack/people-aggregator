<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        mymessage.php, web file display the list of messages in different folders.
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: User can delete and move the messages to folders. It uses 
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 * TODO:        Message module of the site needs some work to be efficient
 *
 */
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Message/Message.php";
require_once "web/includes/functions/user_page_functions.php";

$message = htmlspecialchars(@$_GET['message']);
$msg_count = null;//$msg_count has the number of messages deleted or transfered.

function setup_module($column, $moduleName, $obj) {
  global $login_uid, $paging;;
  switch ($column) {
    case 'middle':
      $obj->uid = $login_uid;
      if (@$_GET['view'] == 'Conversations') {
        $obj->mode = 'view_conversations';
      }
      $obj->folder_name = $folder_name = empty($_GET['folder']) ? INBOX : $_GET['folder'];
      $obj->page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
      $obj->Paging = $paging;
      if(isset($_GET['q'])) {
        $obj->search_string = $_GET['q'];
      }
  }
}

//check if user is checking out only his content
if ($uid != $login_uid) {
  throw new PAException(OPERATION_NOT_PERMITTED, "You cant access other user's messages.");
}

$page = new PageRenderer("setup_module", PAGE_MESSAGE, sprintf(__("%s - Private Messages - %s"), $login_user->get_name(), $network_info->name), "container_one_column.tpl",'header_user.tpl', PRI, HOMEPAGE, $network_info);

/*$css_path = $current_theme_path.'/layout.css';
$page->add_header_css($css_path);
$css_path = $current_theme_path.'/network_skin.css';
$page->add_header_css($css_path);
$css_path = $current_theme_path.'/edit_skin.css';
$page->add_header_css($css_path);*/

// Currently using the CSS from the live site, due to some problem in merging local and server CSS.
$theme_details = get_user_theme($uid);
if (is_array($theme_details['css_files'])) {
  foreach ($theme_details['css_files'] as $key => $value) {
    $page->add_header_css($value);
  }
}

if (!empty($_GET['msg'])) {
  $message = MessagesHandler::get_message($_GET['msg']);
}

if (!empty($message)) {  
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $message);
  $page->add_module("middle", "top", $msg_tpl->fetch());
}

// see if we have a user defined CSS 
$user_data_ui = sanitize_user_data(User::load_user_profile($uid,$uid, 'ui'));
if (!empty($user_data_ui['newcss'])) {
  $usercss = "<style>".$user_data_ui['newcss']."</style>";
  $page->add_header_html($usercss);
}

$page->add_header_html('<script type="text/javascript" language="javascript" src="'.$current_theme_path.'/javascript/messages.js"></script>');

// Get rid of the ", FALSE" below once the UI on this page is made
// compatible with themes.  Right now if you make this themeable, the
// floating left folder list causes the center column to drop down.
uihelper_set_user_heading($page, FALSE);

echo $page->render();

?>