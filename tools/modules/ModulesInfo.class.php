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

require_once "api/Cache/FileCache.php";


class ModulesInfo {

  const MIDDLE_MODULES_FILTER     = "(in_array('middle', explode('|', \$module_info['module_placement'])))";
  const LEFT_MODULES_FILTER       = "(in_array('left', explode('|', \$module_info['module_placement'])))";
  const RIGHT_MODULES_FILTER      = "(in_array('right', explode('|', \$module_info['module_placement'])))";
  const USER_MODULES_FILTER       = "(in_array('user', explode('|', \$module_info['module_type'])))";
  const GROUP_MODULES_FILTER      = "(in_array('group', explode('|', \$module_info['module_type'])))";
  const NETWORK_MODULES_FILTER    = "(in_array('network', explode('|', \$module_info['module_type'])))";
  const SYSTEM_MODULES_FILTER     = "(in_array('system', explode('|', \$module_info['module_type'])))";

  public  $modules_info;

  private $default_conditions = array("has_action_handler" => "#function[\s]*handle[^\s]*[\s]*[\(]{1,1}#",
                                      "has_init_module"    => "#function[\s]*initializeModule[\s]*[\(]{1,1}#",
                                      "has_set_inner_tpl"  => "#function[\s]*set_inner_template[\s]*[\(]{1,1}#"
                                     );

  private $module_types = array("user",
                                "group",
                                "network",
                                "action",
                                "system"
                               );

  public function __construct($search_paths = array()) {
    $this->paths   = $search_paths;
    $this->modules = $this->getModules();
    if(FileCache::is_cached('modules_info', 180)) { // cache expire after 3 mins
      $this->modules_info = FileCache::fetch('modules_info', null, 180);
    } else {
      $this->modules_info = array();
      $this->getModulesInfo();
      FileCache::store('modules_info', $this->modules_info, 180);
    }
  }

  private function getModules($mask = null) {
    $module_names = array();
    $files = array();
    $cnt   = 0;
    foreach($this->paths as $path) {
      try {
        foreach(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME) as $_path => $info) {
          $_entry = $info->getFilename();
//          if (!($info->isDot()) && ($info->isDir()) && ($_entry != '.svn'))
          if (($_entry != '.') && ($_entry != '..') && ($info->isDir()) && ($_entry != '.svn'))
          {
            if($mask) {
              if(preg_match($mask, $_entry)) {
                $module_name = trim($_entry);
                if(!in_array($module_name, $module_names)) {  // copy of module exists in paproject
                  $files[$cnt]['name'] = $module_name;
                  $files[$cnt]['path'] = $path . DIRECTORY_SEPARATOR . $module_name;
                  $module_names[] = $module_name;
                }
              }
            } else {
              $module_name = trim($_entry);
              if(!in_array($module_name, $module_names)) {  // copy of module exists in paproject
                $files[$cnt]['name'] = $module_name;
                $files[$cnt]['path'] = $path . DIRECTORY_SEPARATOR . $module_name;
                $module_names[] = $module_name;
              }
            }
            $cnt++;
          }
        }
      } catch (Exception $e) {
        continue;
      }
    }
    return $files;
  }

  private function getModulesInfo($conditions = null) {

    if(!$conditions) {
      $conditions = $this->default_conditions;
    }

    $counter = 0;
    foreach($this->modules as $module_data) {
      $match_patterns = $conditions;
      $class_name = $module_data['name'];
      $file_path  = $module_data['path'];
      $full_path  = $file_path . DIRECTORY_SEPARATOR . $class_name . ".php";
      if(!file_exists($full_path)) {
        continue;
//        throw new Exception("ModulesInfo class: Module class file \"$full_path\" not found!");
      }
      require_once $full_path;
      $module = new $class_name;
      $this->modules_info[$counter]['name'] = $class_name;
      $this->modules_info[$counter]['module_type'] = @$module->module_type;
      $this->modules_info[$counter]['module_placement'] = @$module->module_placement;
      $this->modules_info[$counter]['outer_template'] = @$module->outer_template;
      $this->modules_info[$counter]['architecture_info'] = array();
      if(!in_array("middle", explode("|", $module->module_placement))) {
         array_shift($match_patterns);  // only middle modules contain action handlers
      }
      $this->getModuleArchInfo($full_path, $match_patterns, $this->modules_info[$counter]['architecture_info']);
      $counter++;
    }

  }

  private function getModuleArchInfo($file_path, $match_patterns, &$architecture_info) {
    $content = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach($content as $content_line) {
      foreach($match_patterns as $name => $pattern) {
//        $architecture_info[$name] = false;
        if(preg_match($pattern, $content_line)) {
          $architecture_info[$name] = true;
          break;
        }
      }
    }
  }

  public function getModulesByCondition($condition) {
    $modules = array();
    if(!empty($condition)) {
      foreach($this->modules_info as $module_info) {
        $res = false;
        if(!eval("\$res = ($condition) ? true: false; return true;")) {
          throw new Exception("ModulesInfo class: Invalid expression.");
        }
        if($res) {
          $modules[] = $module_info;
        }
      }
    }
    return $modules;
  }
}
?>