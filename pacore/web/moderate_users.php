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
  //including necessary files
  $use_theme = 'Beta';
  include_once("web/includes/page.php");
  
  require_once "web/includes/network.inc.php";
  $error = FALSE;
  
  $super_user_and_mothership = FALSE; //this flag will be set when logged in user is SU and is in Mothership
  if( ( SUPER_USER_ID == $_SESSION['user']['id'] )  && PA::$network_info->type == MOTHER_NETWORK_TYPE ) {
    $super_user_and_mothership = TRUE;
  }
  
  if( !$error && isset($_REQUEST['action']) ) {
    //there can be 4 possible actions
    if ($_REQUEST['action'] == 'approve') {
      Network::approve(PA::$network_info->network_id, $_REQUEST['uid']);
      $message = 7022;
    }
    if ($_REQUEST['action'] == 'deny') {
      Network::deny(PA::$network_info->network_id, $_REQUEST['uid']);
      $message = 7023;
    }
    if ($_REQUEST['action'] == 'multiple_approve') {
      if (is_array($_REQUEST['uid'])) {
        foreach ($_REQUEST['uid'] as $uid) {
          Network::approve(PA::$network_info->network_id, $uid);  
        }
        $message = 7022;
      }
    }
    if ($_REQUEST['action'] == 'multiple_deny') {
      if (is_array($_REQUEST['uid'])) {
        foreach ($_REQUEST['uid'] as $uid) {
          Network::deny(PA::$network_info->network_id, $uid);  
        }
        $message = 7023;
      }
    }
    
    
  }
  function setup_module($column, $module, $obj) {
    global $paging,$super_user_and_mothership;
    switch($module){
    
      case 'NetworkModerateUserModule':
    
      if (@$_GET['sort_by'] == 'alphabetic') {
        $obj->sort_by = 'U.login_name';
        $obj->direction = 'ASC';
      } else {
        $obj->sort_by = 'U.created';
        $obj->direction = 'DESC';
      } 
      
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 10;
      $obj->network_info = PA::$network_info;
      $obj->super_user_and_mothership = $super_user_and_mothership;
      break;
    }
  }
  $page = new PageRenderer("setup_module", PAGE_NETWORK_MODERATE_USERS, "Moderate Registered Users", 'container_two_column.tpl','header.tpl',PUB, HOMEPAGE, PA::$network_info);
  
  if (!empty($_GET['msg'])) {
    $message = $_GET['msg'];
  }
  
  if(!empty($message) && is_int($message)) {
    require_once 'web/languages/english/MessagesHandler.php';
    $msg_obj = new MessagesHandler();
    $message = $msg_obj->get_message($message);    
  }
  
  if (!empty($message)) {
    $msg_tpl = new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
    $msg_tpl->set('message', $message);
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