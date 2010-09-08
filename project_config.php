<?php

// This file is for settings and data that are unique for a project.
// project.
//
// So, there should be placed constants and variables that are specific
// for this project only and that are not defined in any other project
// configuration file.
//
// If you need to override any of already defind datam variables or
// constant, please do that in the appropriate config file located in:
//
//      /pa/paproject/web/config
//
//
// For example:
//
//   If you want to define a new site name for your new project, you need to
//   override default value of this config variable defined in the CORE
//   default config file: /pa/pacore/web/config/default_config.php
//   So, your new value for this config variable should be placed on the bottom
//   of the project config file: /pa/paproject/web/config/default_config.php
//
//   e.g.:
//   PA::$site_name = "My Custom PA Project";
//
// - local_config.php file contains server-specific settings like the database password,
//   the base URL of this installation etc.
//
// - All default settings of your new project that need to override the CORE default
//   settings should go in /pa/paproject/web/config/default_config.php
//
// - All **dynamic settings of your new project that need to override the CORE
//   **dynamic settings data should go in /pa/paproject/web/config/dynamic_config.php
//
// LEGEND:
//
//   ** = settings data stored in PA class or data that
//        need raw PHP code in the initialization process
//


error_reporting(E_ALL | E_STRICT);

  $path_separator = ":";
  $dir_separator  = "/";
  $line_break     = "\n";

  if(substr(PHP_OS, 0, 3) == "WIN") {
    $path_separator = ";";
    $dir_separator  = "\\";
    $line_break     = "\r\n";
  }

  if(!defined('PATH_SEPARATOR')) {
    define('PATH_SEPARATOR', $path_separator);
  }
  if(!defined('DIRECTORY_SEPARATOR')) {
    define('DIRECTORY_SEPARATOR', $dir_separator);
  }
  if(!defined('LINE_BREAK')) {
    define('LINE_BREAK', $line_break);
  }

define('PA_CORE_NAME', 'pacore');
define('PA_PROJECT_NAME', 'paproject');
define('DEFAULT_INSTALL_SCRIPT', 'web/install/install.php');

define('PA_PROJECT_ROOT_DIR', realpath(dirname(__FILE__)));
define('PA_PROJECT_CORE_DIR', realpath(PA_PROJECT_ROOT_DIR . DIRECTORY_SEPARATOR . PA_CORE_NAME));
define('PA_PROJECT_PROJECT_DIR', realpath(PA_PROJECT_ROOT_DIR . DIRECTORY_SEPARATOR . PA_PROJECT_NAME));

// define include paths
ini_set('include_path', 
	PA_PROJECT_PROJECT_DIR
	. PATH_SEPARATOR . PA_PROJECT_CORE_DIR
	. PATH_SEPARATOR . PA_PROJECT_CORE_DIR . DIRECTORY_SEPARATOR . 'ext'
	. PATH_SEPARATOR . ini_get('include_path')
	);

define('APPLICATION_CONFIG_FILE', '/config/AppConfig.xml');

// this is to avoid E_STRICT warming, set it to your TZ
date_default_timezone_set('UTC');
?>
