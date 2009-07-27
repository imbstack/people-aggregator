<?php
//require_once dirname(__FILE__)."/../config.inc";
// global var $path_prefix has been removed - please, use PA::$path static variable
//require_once "web/includes/functions/html_generate.php";
//require_once "web/includes/functions/validations.php";
//require_once "api/User/User.php";
require_once "api/Theme/Template.php";

$theme_url = PA::$theme_url;
$uname = ((empty($_SESSION['user']['name'])) ? '' : htmlspecialchars($_SESSION['user']['name']));

  $template_file = getShadowedPath('web/Themes/Default/maintenance.tpl');
  $template = & new Template($template_file);
  $template->set('theme_url', $theme_url);
  $template->set('uname', $uname);
  echo $template->fetch();

?>