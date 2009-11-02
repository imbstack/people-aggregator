<?php

 require_once "web/includes/classes/ConfigurablePage.class.php";

 define("DEFAULT_PAGE_THEME", 'Default');
 define("DEFAULT_PAGE_TEMPLATE"   , "container_three_column.tpl");
 define("DEFAULT_HEADER_TEMPLATE" , "header.tpl");
 define("DEFAULT_PAGE_MODE" , 'public');
 define("DEFAULT_BLOCK_TYPE", 'Homepage');
 define("DEFAULT_BODY_ATTR",  "");
 define("DEFAULT_PAGE_TYPE", 'network');


/**
 *
 * @class DynamicPage
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
 class DynamicPage extends ConfigurablePage {

  public $page_name       = null;
  public $page_type       = null;     // user, group, network types
  public $is_configurable = false;
  public $left    = array();
  public $middle  = array();
  public $right   = array();
  public $javascripts     = array();
  public $page_css        = array();
  public $page_theme      = null;
  public $page_template   = null;
  public $header_template = null;
  public $page_mode       = null; // PRI,PUB
  public $block_type      = null; // HOMEPAGE
  public $body_attributes = null;
  public $access_permission = null;
  public $navigation_code = null;
  public $boot_code       = null;
  public $save_page       = false;

  public function __construct($page_id = 0, $old_conf_array = array(), $config_dir = DYNAMIC_PAGES_DIR) {
    parent::__construct($page_id, $config_dir, $old_conf_array);
  }

  public function __destruct() {
    if($this->save_page) {        // page data will be stored on exit
      parent::__destruct();
    }
  }

  public function initialize($new_settings = null) {

//    $this->checkXmlConfigStructure($new_settings);
    $this->page_name       = (string)$this->getConfigData('page_name', '//page');
    $this->is_configurable = (bool)$this->getConfigData('is_configurable', '//page');
    $this->toprow    = $this->getModules("toprow");
    $this->left    = $this->getModules("left");
    $this->middle  = $this->getModules("middle");
    $this->right   = $this->getModules("right");
    $this->javascripts     = $this->getJavascripts();
    $this->page_css        = $this->getPageCSS();
    $this->page_theme      = $this->getConfigData('page_theme', '//page/data');
    $this->page_template   = $this->getConfigData('page_template', '//page/data');
    $this->header_template = $this->getConfigData('header_template', '//page/data');
    $this->page_mode       = $this->getConfigData('page_mode', '//page/data');
    $this->block_type      = $this->getConfigData('block_type', '//page/data');
    $this->body_attributes = $this->getConfigData('body_attributes', '//page/data');
    $this->access_permission = $this->getConfigData('access_permission', '//page/data');
    $this->navigation_code   = $this->getConfigData('navigation_code', '//page/data');
    $this->boot_code         = $this->getConfigData('boot_code', '//page/data');
    $this->page_type         = $this->getConfigData('page_type', '//page/data');
  }

  private function checkXmlConfigStructure($new_settings) {
    $default_navigation_code = "";
    $default_boot_code =
      "
      if(\$uid = \$app->getRequestParam('uid')) {
        \$user = new User();
        try {
          \$user->load((int)\$uid);
          \$module_shared_data['user_info'] = \$user;
        } catch(Exception \$e) {
          \$module_shared_data['user_info'] = null;
        }
      }
      if(\$gid = \$app->getRequestParam('gid')) {
        try {
          \$group = ContentCollection::load_collection((int)\$gid);
          \$module_shared_data['group_info'] = \$group;
        } catch(Exception \$e) {
          \$module_shared_data['group_info'] = null;
        }
      }
      if(\$nid = \$app->getRequestParam('nid')) {
        try {
          \$network = Network::get_by_id((int)\$nid);
          \$module_shared_data['network_info'] = \$network;
          \$extra = unserialize(\$network->extra);
          \$module_shared_data['extra'] = \$extra;
        } catch(Exception \$e) {
          \$module_shared_data['network_info'] = null;
          \$module_shared_data['extra'] = null;
        }
      } else if(!empty(PA::\$network_info)) {
        \$module_shared_data['network_info'] = PA::\$network_info;
        \$extra = unserialize(PA::\$network_info->extra);
        \$module_shared_data['extra'] = \$extra;
      } else {
        \$module_shared_data['network_info'] = null;
        \$module_shared_data['extra'] = null;
      }
      if(\$cid = \$app->getRequestParam('cid')) {
        try {
          \$content = Content::load((int)\$cid);
          \$module_shared_data['content_info'] = \$content;
        } catch(Exception \$e) {
          \$module_shared_data['content_info'] = null;
        }
      }
      ";

    if(!$this->hasConfigSection('data', '//page')) {
      $this->addConfigSection('data', '//page');
    }

    if(!$this->hasConfigSection('page_id', '//page')) {
      $this->addConfigData('page_id', $this->page_id, '//page');
    }

    if(isset($new_settings['page_name']) /* && ($new_settings['page_name'] != null) */) {
      if($this->hasConfigSection('page_name', '//page')) {
        $this->removeConfigData('page_name', '//page');
      }
      $this->addConfigData('page_name', $new_settings['page_name'], '//page');
    } else if(!$this->hasConfigSection('page_name', '//page')) {
      $this->addConfigData('page_name', 'Page name not defined!', '//page');
    }

    if(isset($new_settings['is_configurable'])) {
      if($this->hasConfigSection('is_configurable', '//page')) {
        $this->removeConfigData('is_configurable', '//page');
      }
      $this->addConfigData('is_configurable', $new_settings['is_configurable'], '//page');
    } else if(!$this->hasConfigSection('is_configurable', '//page')) {
      $this->addConfigData('is_configurable', true, '//page');
    }

    $layouts = array('left', 'middle', 'right');
    foreach($layouts as $layout) {
      if(isset($new_settings[$layout])/* && ($new_settings[$layout] != null)*/) {
        $this->removeConfigSection($layout, '//page/data');      // clear modules data
        $this->addConfigSection($layout, '//page/data');         //
        foreach($new_settings[$layout] as $module_name) {
            $this->addModule($layout, $module_name);
        }
      } else if(!$this->hasConfigSection($layout, '//page/data')) {
        $this->addConfigSection($layout, '//page/data');
      }
    }

    if(isset($new_settings['javascripts']) /* && ($new_settings['javascripts'] != null) */) {
      $this->removeConfigSection('javascripts', '//page/data');  // clear javascripts data
      $this->addConfigSection('javascripts', '//page/data');     //
      foreach($new_settings['javascripts'] as $script_name) {
        $this->addJavascripts($script_name);
      }
    } else if(!$this->hasConfigSection('javascripts', '//page/data')) {
      $this->addConfigSection('javascripts', '//page/data');
    }

    if(isset($new_settings['page_css']) /* && ($new_settings['page_css'] != null) */) {
      $this->removeConfigData('page_css', '//page/data');      // clear CSS data
      $this->addConfigSection('page_css', '//page/data');      //
      foreach($new_settings['page_css'] as $css_name) {
        $this->addPageCSS($css_name);
      }
    } else if(!$this->hasConfigSection('page_css', '//page/data')) {
      $this->addConfigSection('page_css', '//page/data');
    }

    $defaults = array('page_theme'        => DEFAULT_PAGE_THEME,
                      'page_type'         => DEFAULT_PAGE_TYPE,
                      'page_template'     => DEFAULT_PAGE_TEMPLATE,
                      'header_template'   => DEFAULT_HEADER_TEMPLATE,
                      'page_mode'         => DEFAULT_PAGE_MODE,
                      'block_type'        => DEFAULT_BLOCK_TYPE,
                      'body_attributes'   => DEFAULT_BODY_ATTR,
                      'access_permission' => null,
                      'navigation_code'   => $default_navigation_code,
                      'boot_code'         => $default_boot_code
    );
    foreach($defaults as $param_name => $defaul_value) {
      $value = '';
      if(isset($new_settings[$param_name]) /* && ($new_settings[$param_name] != null) */) {
        $this->removeConfigData($param_name, '//page/data');
        $value = $new_settings[$param_name];
        if(($param_name == 'boot_code') || ($param_name == 'navigation_code') || ($param_name == 'body_attributes')) {
          $this->addConfigData($param_name, $value, '//page/data', true);
        } else {
          $this->addConfigData($param_name, $value, '//page/data', false);
        }
      } else if(!$this->getConfigData($param_name, '//page/data')) {
        $value = $defaul_value;
        if(($param_name == 'boot_code') || ($param_name == 'navigation_code') || ($param_name == 'body_attributes')) {
          $this->addConfigData($param_name, $value, '//page/data', true);
        } else {
          $this->addConfigData($param_name, $value, '//page/data', false);
        }
      }
    }
  }

  public function getModules($layout_string) {
     $module_names = array();
     if($modules = $this->getConfigData('item', "//page/data/$layout_string")) {
       $module_names = $modules;
     }
     return $module_names;
  }

  public function getAllModules() {
     $modules = array();
     foreach(array('toprow', 'middle', 'left', 'right') as $layout) {
       $modules[$layout] = $this->getModules($layout);
     }
     return $modules;
  }


  public function addModule($layout_string, $module_name) {
     $this->addConfigData('item', $module_name, "//page/data/$layout_string");
     $this->modified = true;
//     $this->initialize();
  }

  public function removeModule($layout_string, $module_name) {
    $this->removeConfigData('item', "//page/data/$layout_string", $module_name);
//    $this->initialize();
  }

  public function getJavascripts() {
     $scripts = array();
     if($_scripts = $this->getConfigData('item', '//page/data/javascripts')) {
       $scripts = $_scripts;
     }
     return $scripts;
  }

  public function addJavascripts($scriptname) {
     $this->addConfigData('item', $scriptname, '//page/data/javascripts');
//     $this->initialize();
  }

  public function removeJavascripts($scriptname) {
     $this->removeConfigData('item', '//page/data/javascripts', $scriptname);
//     $this->initialize();
  }

  public function getPageCSS() {
     $css_fnames = array();
     if($_css_fnames = $this->getConfigData('item', '//page/data/page_css')) {
       $css_fnames = $_css_fnames;
     }
     return $css_fnames;
  }

  public function addPageCSS($fname) {
     $this->addConfigData('item', $fname, '//page/data/page_css');
//     $this->initialize();
  }

  public function removePageCSS($fname) {
     $this->removeConfigData('item', '//page/data/page_css', $fname);
//     $this->initialize();
  }


  public function buildPageSettings($new_settings) {
    $this->initialize($new_settings);
    return $this->getPageSettings();
  }

  public function getPageSettings() {
    $page_settings = array();
    $page_settings['page_id']         = $this->page_id;
    $page_settings['page_name']       = $this->page_name;
    $page_settings['page_type']       = $this->page_type;
    $page_settings['is_configurable'] = $this->is_configurable;
    $page_settings['toprow']            = $this->toprow;
    $page_settings['left']            = $this->left;
    $page_settings['middle']          = $this->middle;
    $page_settings['right']           = $this->right;
    $page_settings['javascripts']     = $this->javascripts;
    $page_settings['page_css']        = $this->page_css;
    $page_settings['page_theme']      = $this->page_theme;
    $page_settings['page_template']   = $this->page_template;
    $page_settings['header_template'] = $this->header_template;
    $page_settings['page_mode']       = $this->page_mode;
    $page_settings['block_type']      = $this->block_type;
    $page_settings['body_attributes'] = $this->body_attributes;
    $page_settings['access_permission'] = $this->access_permission;
    $page_settings['navigation_code']   = $this->navigation_code;
    $page_settings['boot_code']         = $this->boot_code;
    return $page_settings;
  }
}

/**
 *
 * @class DynamicPageException
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
 class DynamicPageException extends Exception {

    public function __construct($message, $code = 0) {
      parent::__construct('DynamicPageException: ' . $message, $code);
    }
 }

?>