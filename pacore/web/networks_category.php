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
$login_required = FALSE;
include_once("web/includes/page.php");
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/Content/Content.php";
require_once "api/Tag/Tag.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Comment/Comment.php";
include_once "api/ModuleSetting/ModuleSetting.php";
require_once "api/Group/Group.php";
include_once "api/Theme/Template.php";
require_once "api/Category/Category.php";
//require_once "web/includes/functions/auto_email_notify.php";
require_once "api/Messaging/MessageDispatcher.class.php";

 

$cid = (int) $_REQUEST['cid'];
if ( !$cid) {
  Logger::log("Thowing Exception NETWORK_INVALID_CATEGORY");
  throw new PAException(NETWORK_INVALID_CATEGORY,"Invalid url");
}

if ( $_GET['action'] == 'join' ) {
  $nid = (int) $_GET['nid'];
  $error = 0;
  if(!$nid) {
    $error = 1;
  }
   try {
   //$location = "http://".$redirect_url.'.'.PA::$domain_suffix.'/web/homepage.php';
       if ($_SESSION['user']['id']) {
        $suc = Network::join($nid,$_SESSION['user']['id']);
        $network = new Network;
        $network->set_params(array('network_id'=>$nid));
        $netinfo = $network->get();        
        $netinfo = $netinfo[0];
        $msg = "You have successfully joined the '".stripslashes($netinfo->name)."' network. Click <a href='http://".$netinfo->address.".".PA::$domain_suffix.BASE_URL_REL.PA_ROUTE_HOME_PAGE."'>here</a> to go to the network.";

        $requester = new User();
        $requester->load((int)$_SESSION['user']['id']);
        $recipient = type_cast($netinfo, 'Network');           // defined in helper_functions.php
        PANotify::send("network_join", $recipient, $requester, array());
       } else {
         //$msg = "Please login first to join the network.";
         header("Location: ". PA::$url ."/login.php?error=1&return=".urlencode($_SERVER['REQUEST_URI']));
       }
       
     }
     catch (PAException $e) {
       $msg .= $e->message;
       
     }
}

default_exception();
$parameter = '<script type="text/javascript" language="javascript" src="'.PA::$theme_url . '/base_javascript.js"></script></script>
<script type="text/javascript" language="javascript" src="'.PA::$theme_url . '/javascript/prototype.js"></script>
<script type="text/javascript" language="javascript" src="'.PA::$theme_url . '/javascript/scriptaculous.js"></script>';
html_header("Networks in category", $parameter);
$setting_data = ModuleSetting::load_setting(PAGE_NETWORKS_CATEGORY, $uid);
$leftModulesFromDB = $setting_data['left'];
$middleModulesFromDB = $setting_data['middle'];
$rightModulesFromDB = $setting_data['right'];

$page = new Template(CURRENT_THEME_FSPATH."/groups.tpl");

$page->set('current_theme_path', PA::$theme_url);


//header of group page
$optional_parameters = "onload=\"$onload\"";
html_body($optional_parameters);

//header of group page
$header = new Template(CURRENT_THEME_FSPATH."/header.tpl");
$header->set('current_theme_path', PA::$theme_url);
$header->set('current_theme_rel_path', PA::$theme_rel);
// find navigation link for header
$navigation = new Navigation;
$navigation_links = $navigation->get_links();
$header->set('navigation_links', $navigation_links);
$header->set('onload', $onload);

$header->tier_one_tab = $main_tier;
$header->tier_two_tab = $second_tier;
$header->tier_three_tab = $third_tier;
if (PA::$network_info) {
  $header->set_object('network_info', PA::$network_info);
}

// This block of code has to be removed when this page will be rendered using PageRenderer.
$top_navigation_bar = new Template(CURRENT_THEME_FSPATH."/top_navigation_bar.tpl");
$top_navigation_bar->set('navigation_links', $navigation_links);

//left of group page
foreach ( $leftModulesFromDB as $leftModule)
{
  $file = "BlockModules/$leftModule/$leftModule.php";
  require_once $file;
  $obj = new $leftModule;
  $array_left_modules[] = $obj->render();
}

//$msg = "You have successfully joined the network. Please click <a href='#'>here</a> to go to the network.";
if (!empty($msg)) {
  $msg_tpl = new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg);
  $array_middle_modules[] = $msg_tpl->fetch();
}
//middle of group page
foreach ( $middleModulesFromDB as $middleModule)
{
  $file = "BlockModules/$middleModule/$middleModule.php";
  require_once $file;
  $obj = new $middleModule;
  $obj->cid = $cid;
  $array_middle_modules[] = $obj->render();
}

//right of group page
foreach ( $rightModulesFromDB as $rightModule)
{
  $file = "BlockModules/$rightModule/$rightModule.php";
  require_once $file;
  $obj = new $rightModule;
  $array_right_modules[] = $obj->render();
}
//right of group page
$footer = new Template(CURRENT_THEME_FSPATH."/footer.tpl");
$footer->set('current_theme_path', PA::$theme_url);

//page settings
$page->set('top_navigation_bar', $top_navigation_bar);
$page->set('header', $header);
$page->set('array_left_modules', $array_left_modules);
$page->set('array_middle_modules', $array_middle_modules);
$page->set('array_right_modules', $array_right_modules);
$page->set('footer', $footer);
$page->set('current_theme_path', PA::$theme_url);
echo $page->fetch();
print '</body></html>';
?>
