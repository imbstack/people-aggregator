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
$login_required = TRUE;
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
 
$parameter = '<script type="text/javascript" language="javascript" src="'.PA::$theme_url . '/base_javascript.js"></script></script>
<script type="text/javascript" language="javascript" src="'.PA::$theme_url . '/javascript/prototype.js"></script>
<script type="text/javascript" language="javascript" src="'.PA::$theme_url . '/javascript/scriptaculous.js"></script>';
html_header("Group Home", $parameter);


$setting_data = ModuleSetting::load_setting(PAGE_MANAGECONTENT, $uid);
$leftModulesFromDB = $setting_data['left'];
$middleModulesFromDB = $setting_data['middle'];
$rightModulesFromDB = $setting_data['right'];

$page = new Template(CURRENT_THEME_FSPATH."/groups.tpl");

$page->set('current_theme_path', PA::$theme_url);

//header of group page
if ($_GET['tier_one']) {
  $main_tier = $_GET['tier_one'];
  //$tmp = $_GET['tier_one'].'pagedemo.php';
}
else {
  $main_tier = 'group';
}

if ($_GET['tier_two']) {
  $second_tier = $_GET['tier_two'];
}
if ($_GET['tier_three']) {
  $third_tier = $_GET['tier_three'];
}

$optional_parameters = "onload=\"$onload\"";
html_body($optional_parameters);

$header = new Template(CURRENT_THEME_FSPATH."/header.tpl");
if (PA::$network_info) {
  $header->set_object('network_info', PA::$network_info);
}
$header->set('current_theme_path', PA::$theme_url);
$header->set('onload', $onload);

$header->tier_one_tab = $main_tier;
$header->tier_two_tab = $second_tier;
$header->tier_three_tab = $third_tier;



//left of group page
foreach ( $leftModulesFromDB as $leftModule)
{
  $file = "BlockModules/$leftModule/$leftModule.php";
  require_once $file;
  $obj = new $leftModule;
  if ($leftModule=='RecentCommentsModule') {
    $obj->cid = $_REQUEST['cid'];
    $obj->block_type = HOMEPAGE;
    $obj->mode = PRI;
  }
  $array_left_modules[] = $obj->render();
}

//middle of group page
foreach ( $middleModulesFromDB as $middleModule)
{
  $file = "BlockModules/$middleModule/$middleModule.php";
  require_once $file;
  $obj = new $middleModule;
  $obj->content_id = $_REQUEST['cid'];
  $array_middle_modules[] = $obj->render();
}

//right of group page
foreach ( $rightModulesFromDB as $rightModule)
{
  $file = "BlockModules/$rightModule/$rightModule.php";
  require_once $file;
  $obj = new $rightModule;
  $obj->mode = PRI;
  if ($rightModule != 'AdsByGoogleModule') {
    $obj->block_type = HOMEPAGE;
  }
  $array_right_modules[] = $obj->render();
}
$footer = new Template(CURRENT_THEME_FSPATH."/footer.tpl");
$footer->set('current_theme_path', PA::$theme_url);
//page settings
$page->set('header', $header);
$page->set('array_left_modules', $array_left_modules);
$page->set('array_middle_modules', $array_middle_modules);
$page->set('array_right_modules', $array_right_modules);
$page->set('footer', $footer);
$page->set('current_theme_path', PA::$theme_url);
echo $page->fetch();
print '</body></html>';
?>