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
$parameter = '<script type="text/javascript" language="javascript" src="'.PA::$theme_url.'/base_javascript.js"></script></script>
<script type="text/javascript" language="javascript" src="'.PA::$theme_url.'/javascript/prototype.js"></script>
<script type="text/javascript" language="javascript" src="'.PA::$theme_url.'/javascript/scriptaculous.js"></script>';
html_header("Group Home", $parameter);
//print '<body style="background-color: #363636;">';
$setting_data        = ModuleSetting::load_setting(PAGE_GROUPS_CATEGORY, $uid);
$leftModulesFromDB   = $setting_data['left'];
$middleModulesFromDB = $setting_data['middle'];
$rightModulesFromDB  = $setting_data['right'];
$page                = &new Template(CURRENT_THEME_FSPATH."/groups.tpl");
$page->set('current_theme_path', PA::$theme_url);
//header of group page
$optional_parameters = "onload=\"$onload\"";
html_body($optional_parameters);
//header of group page
$header = &new Template(CURRENT_THEME_FSPATH."/header.tpl");
$header->set('current_theme_path', PA::$theme_url);
$header->set('current_theme_rel_path', PA::$theme_rel);
// find navigation link for header
$navigation = new Navigation;
$navigation_links = $navigation->get_links();
$header->set('navigation_links', $navigation_links);
$header->set('onload', $onload);
$header->tier_one_tab   = $main_tier;
$header->tier_two_tab   = $second_tier;
$header->tier_three_tab = $third_tier;
if(PA::$network_info) {
    $header->set_object('network_info', PA::$network_info);
}
// This block of code has to be removed when this page will be rendered using PageRenderer.
$top_navigation_bar = &new Template(CURRENT_THEME_FSPATH."/top_navigation_bar.tpl");
$top_navigation_bar->set('navigation_links', $navigation_links);
//left of group page
foreach($leftModulesFromDB as $leftModule) {
    $file = PA::$blockmodule_path."/$leftModule/$leftModule.php";
    require_once $file;
    $obj = new $leftModule;
    $array_left_modules[] = $obj->render();
}
//middle of group page
foreach($middleModulesFromDB as $middleModule) {
    $file = PA::$blockmodule_path."/$middleModule/$middleModule.php";
    require_once $file;
    $obj = new $middleModule;
    if(!empty($_GET["cid"]) && is_numeric($_GET["cid"]) && $_GET["cid"] > 0) {
        $obj->cid = $_GET['cid'];
    }
    else {
        if(!empty($_GET["tag_id"])) {
            $obj->tag_id = trim($_GET["tag_id"]);
        }
        else {
            $obj->tag_id = "";
        }
        $obj->cid =-1;
    }
    $obj->Paging["page"]    = $paging["page"];
    $obj->Paging["show"]    = $paging["show"];
    $array_middle_modules[] = $obj->render();
}

/*if(count(Category::build_children_list($_GET["cid"], 'Group')) > 0) {
  $middleModule = "GroupsByCategoryModule";
  $file = PA::$blockmodule_path."/$middleModule/$middleModule.php";
  require_once $file;
  $obj                    = new $middleModule;
  $obj->sub_cid           = $_GET["cid"];
  $array_middle_modules[] = $obj->render();
}
*/
//right of group page
foreach($rightModulesFromDB as $rightModule) {
    $file = PA::$blockmodule_path."/$rightModule/$rightModule.php";
    require_once $file;
    $obj = new $rightModule;
    $array_right_modules[] = $obj->render();
}
//right of group page
$footer = &new Template(CURRENT_THEME_FSPATH."/footer.tpl");
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
