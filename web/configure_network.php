<?php
// This page us used for network creation
//anonymous user can not view this page;
$login_required = TRUE;
//including necessary files
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
include_once "api/ModuleSetting/ModuleSetting.php";
include_once "api/Theme/Template.php";
require_once "api/Category/Category.php";
require_once "web/includes/classes/file_uploader.php";
require_once "api/Validation/Validation.php";
require_once "api/Tag/Tag.php";
require_once "web/includes/network.inc.php";

// function checks initial settings for network creation


//render the page
$page = new PageRenderer("setup_module", PAGE_CONFIGURE_NETWORK, "Network Statistics", 'container_one_column.tpl','header.tpl',PRI,HOMEPAGE,$network_info);


$css_path = $current_theme_path.'/layout.css';
$page->add_header_css($css_path);
$css_path = $current_theme_path.'/network_skin.css';
$page->add_header_css($css_path);

if ( $error ) {  
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $error_msg);
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
} elseif (( @$_GET['created'] == 1 )) {
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', ' Network created successfully. Now you can configure your network by following links - ');
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}
elseif (( @$_GET['created'] == 'bulletin' )) {
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', ' Network Bulletin has been posted ');
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}
elseif (( @$_GET['created'] == 'announce' )) {
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', ' Network Announcement has been made ');
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}
echo $page->render();
//..end render the page

/*  ---------- FUNCTION DEFINITION ------------------*/
//call back function
function setup_module($column, $module, $obj) {
 global $form_data, $error, $error_msg;	
 $obj->tpl_to_load = "options";
 $obj->control_type = "options";
  $obj->error = $error;
 $obj->error_msg = $error_msg;
 //add variables to BlockModule 
}
?>