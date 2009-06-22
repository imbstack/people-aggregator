<?php
$login_required = FALSE;
include_once("web/includes/page.php");
global $network_info;
// global var $path_prefix has been removed - please, use PA::$path static variable

global $current_theme_path;

require_once "BlockModules/ImagesModule/ImagesModule.php";
require_once "BlockModules/FacewallModule/FacewallModule.php";
require_once "BlockModules/ContentModule/ContentModule.php";
require_once "BlockModules/RecentPostModule/RecentPostModule.php";
require_once "BlockModules/UserInformationModule/UserInformationModule.php";
require_once "BlockModules/RelationsModule/RelationsModule.php";
include_once "../api/ModuleSetting/ModuleSetting.php";

require_once "BlockModules/PopularTagsModule/PopularTagsModule.php";
require_once '../api/ImageResize/ImageResize.php';
require_once '../api/Relation/Relation.php';
include_once "../api/Theme/Template.php";
require_once '../ext/Image/Image.php';
require_once '../ext/Audio/Audio.php';
require_once '../ext/Video/Video.php';

$parameter = js_includes("all");

html_header("Media Full View", $parameter);



if ($_SESSION['user']['id']) {
  if(!$_GET['uid']) {
    $uid = $_SESSION['user']['id'];
  }
  else {
    $uid = $_GET['uid'];
  }
}

$optional_parameters = "onload=\"$onload\"";
html_body($optional_parameters);

// header
$header = & new Template(CURRENT_THEME_FSPATH."/header.tpl");
$header->set('current_theme_path', $current_theme_path);
if ($network_info) {
  $header->set_object('network_info', $network_info);
}

print '<div class="body"><div class="left-body">';

print $header->fetch();

// middle
$content = & new Template(CURRENT_THEME_FSPATH."/show_full_image.tpl");
if ($error == TRUE) {
  $content->set('msg', $msg);
}
print $content->fetch();

// footer
$footer = & new Template(CURRENT_THEME_FSPATH."/footer.tpl");
$footer->set('current_theme_path', $current_theme_path);
print $footer->fetch();

print '</div>';
print '</div>';

?>

</body>
</html>