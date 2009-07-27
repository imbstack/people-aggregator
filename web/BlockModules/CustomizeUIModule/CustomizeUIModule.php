<?php
// This Moduke is meant to be multi-purpose
// it contains five sections:
// * Style Editor
// * Theme Selector
// * Background Image 
// * Header Image
// * Enable Module

require_once "tools/modules/ModulesInfo.class.php";
require_once "web/includes/classes/xHtml.class.php";
require_once "web/includes/classes/file_uploader.php";

class CustomizeUIModule extends Module {
  
  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $base_url;
  
  private $page_templates = array('one'     => 'container_one_column.tpl',
                                  'two'     => 'container_two_column.tpl',
                                  'two_r'   => 'container_two_column_right.tpl',
                                  'three'   => 'container_three_column.tpl',
                                  'two_l'   => 'container_two_column_left.tpl');
  
  private $settings_type;  // user, group or network type
  private $assoc_id;
       
  private $restore_module_settings = false;
  private $restore_style = false;
  
  public $outer_template = 'outer_customize_ui_module.tpl';
  function __construct() {
    parent::__construct();
  }

  function initializeModule($request_method, $request_data) {
    $this->uid = (!empty($request_data['uid'])) ? $request_data['uid'] : null;
    $this->gid = (!empty($request_data['gid'])) ? $request_data['gid'] : null;
    $this->type = (!empty($request_data['type'])) ? $request_data['type'] : 'theme';
    $this->settings_type = (!empty($request_data['stype'])) ? $request_data['stype'] : 'network';
    
    switch($this->settings_type) {
      case 'user':
        if(!empty($this->shared_data['user_info'])) {
          $this->assoc_id = $this->shared_data['user_info']->user_id;
        } else {
           $this->assoc_id = PA::$login_uid;
        }
        $this->pid = (!empty($request_data['pid'])) ? $request_data['pid'] : PAGE_USER_PUBLIC;
        $this->url = PA_ROUTE_CUSTOMIZE_USER_GUI . "/$this->type/uid=$this->uid";
        $this->base_url = PA_ROUTE_CUSTOMIZE_USER_GUI;
        $this->side_modules_condition   = ModulesInfo::USER_MODULES_FILTER . " && (" .
                                          ModulesInfo::LEFT_MODULES_FILTER .
                                 " || " . ModulesInfo::RIGHT_MODULES_FILTER . ")";
        $this->middle_modules_condition = ModulesInfo::USER_MODULES_FILTER . " && " . ModulesInfo::MIDDLE_MODULES_FILTER;
      break;
      case 'group':
        if(!empty($this->shared_data['group_info'])) {
          $this->assoc_id = $this->shared_data['group_info']->collection_id;
        }
        $this->pid = (!empty($request_data['pid'])) ? $request_data['pid'] : PAGE_GROUP;
        $this->url = PA_ROUTE_CUSTOMIZE_GROUP_GUI . "/$this->type/gid=$this->gid";
        $this->base_url = PA_ROUTE_CUSTOMIZE_GROUP_GUI;
        $this->side_modules_condition   = ModulesInfo::GROUP_MODULES_FILTER . " && (" .
                                          ModulesInfo::LEFT_MODULES_FILTER .
                                 " || " . ModulesInfo::RIGHT_MODULES_FILTER . ")";
        $this->middle_modules_condition = ModulesInfo::GROUP_MODULES_FILTER . " && " . ModulesInfo::MIDDLE_MODULES_FILTER;
      break;
      case 'network':
        if(!empty($this->shared_data['network_info'])) {
          $this->assoc_id = $this->shared_data['network_info']->network_id;
        }
        $this->pid = (!empty($request_data['pid'])) ? $request_data['pid'] : PAGE_HOMEPAGE;
        $this->url = PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/$this->type";
        $this->base_url = PA_ROUTE_CUSTOMIZE_NETWORK_GUI;
        $this->side_modules_condition   = ModulesInfo::NETWORK_MODULES_FILTER .
                                " && !" . ModulesInfo::SYSTEM_MODULES_FILTER. " && (" .
                                          ModulesInfo::LEFT_MODULES_FILTER .
                                 " || " . ModulesInfo::RIGHT_MODULES_FILTER . ")";
        $this->middle_modules_condition = ModulesInfo::NETWORK_MODULES_FILTER .
                                " && "  . ModulesInfo::MIDDLE_MODULES_FILTER .
                                " && !" . ModulesInfo::SYSTEM_MODULES_FILTER;
      break;
    }
    if(!empty($request_data['type']) && ($request_method != 'POST')) {
      switch($request_data['type']) {
        case 'theme':
          $this->setupCustomizeTheme($request_data);
        break;
        case 'bg_image':
          $this->setupCustomizeBackgroundImage($request_data);
        break;
        case 'module':
          $this->setupCustomizeModuleSettings($request_data);
        break;
        case 'desktop_image':
          $this->setupCustomizeDesktopImage($request_data); 
        break;
        case 'style':
          $this->setupCustomizeStyle($request_data);
        break;
      }
    } 
/*    else {
      return 'skip';
    }
*/     
  }
  private function setupCustomizeTheme($request_data) {
 
    switch($this->settings_type) {
      case 'user':
        $user = $this->shared_data['user_info'];
        $user_skin = sanitize_user_data(User::load_user_profile($user->user_id, $user->user_id, 'skin'));
        $selected_theme = null;
        
        if(!empty($user_skin['theme'])) {
          $selected_theme['name'] = $user_skin['theme'];
        }
        $skins = get_skins('user');
      break;
      case 'group':
        $group = $this->shared_data['group_info'];
        $extra = $this->shared_data['group_extra'];
        $selected_theme = null;
        
        if(!empty($extra['theme'])) {
          $selected_theme['name'] = $extra['theme'];
        }
        $skins = get_skins('group');
      break;
      case 'network':
        $extra = $this->shared_data['extra'];
        $selected_theme = get_skin_details();
        $skins = get_skins('network');
      break;
    }
    $this->set_inner_template('theme_selector.php');
    $this->inner_HTML = $this->generate_inner_html(array('page_url' => $this->url,
                                                         'base_url' => $this->base_url,
                                                         'uid'      => $this->uid,
                                                         'gid'      => $this->gid,
                                                         'selected_theme' => $selected_theme,
                                                         'skins' => $skins,
                                                         'type' => 'theme',
                                                         'settings_type' => $this->settings_type));
  }

  private function setupCustomizeModuleSettings($request_data) {
    $page_templates = $this->page_templates;
    $this->template_selected = (!empty($request_data['page_template'])) ? $request_data['page_template'] : null;
    $this->side_selected = (!empty($request_data['side_module'])) ? $request_data['side_module'] : null;
    $this->middle_selected = (!empty($request_data['middle_module'])) ? $request_data['middle_module'] : null;
    
    $module_settings = ModuleSetting::load_setting($this->pid, $this->assoc_id, $this->settings_type);

    // QUESTION: Should we enable restore defaults from XML for user and group pages ?
    $from_XML = ($this->settings_type == 'network') ? true : false;
    $pages_default_setting = ModuleSetting::get_pages_default_setting( $this->settings_type, true,  $from_XML);
    $selected = null;
    $current_selecion = null;
    $select_options = array();
    foreach ($pages_default_setting as $page_details) {
      $select_options[$page_details->page_name] = $page_details->page_id;
      if($this->pid == $page_details->page_id) {
         $selected = $page_details->page_id;
         $current_selection = $page_details;
         $restore_settings  = $page_details->getPageSettings();
      }
    }

    if($this->restore_module_settings) {
      $module_settings = $restore_settings;
      $this->template_selected  = null;
      $this->side_selected = null;
      $this->middle_selected = null;
    }           
    $tag_attrs = array('name' => "form_data[page_id]",
                       'onchange' => "javascript: document.location='".$this->url ."&pid='+this.value");
    $select_tag = xHtml::selectTag($select_options, $tag_attrs, $selected);

    if(empty($module_settings['left'])) {
      $module_settings['left'] = array();
    }
    if(empty($module_settings['middle'])) {
      $module_settings['middle'] = array();
    }
    if(empty($module_settings['right'])) {
      $module_settings['right'] = array();
    }
    
    if($this->template_selected) {
      $selected_template = $this->template_selected;
    } else {           
      $page_tmpl = trim($module_settings['page_template']);
      $available_templates = array_flip($this->page_templates);
      if(array_key_exists($page_tmpl, $available_templates)) {
        $selected_template = $available_templates[$page_tmpl];
      }
    }  
    $template_tag_attrs = array('name' => "form_data[page_template]",
                                'onchange' => "javascript: document.location='".$this->url . "&pid=$this->pid&page_template='+this.value");
    $template_select_tag = xHtml::selectTag(array_flip($page_templates), $template_tag_attrs, $selected_template);
    
    
    $mod_info = new ModulesInfo(array(PA::$project_dir.DIRECTORY_SEPARATOR.PA::$blockmodule_path,
                                      PA::$core_dir.DIRECTORY_SEPARATOR.PA::$blockmodule_path));

    $side_modules  = $mod_info->getModulesByCondition($this->side_modules_condition);
    $middle_modules = $mod_info->getModulesByCondition($this->middle_modules_condition);
 
    $side_selected = null;
    $side_current_selecion = null;
    $side_select_options = array();
    foreach ($side_modules as $s_module) {
      $side_select_options[$s_module['name']] = $s_module['name'];
      if(@$this->side_selected == $s_module['name']) {
         $side_selected = $s_module['name'];
      }
    }
    $side_select_options[" "] = " ";
    ksort($side_select_options);
    $side_tag_attrs = array('name' => "add_side_module",
                            'onchange' => "javascript: document.location='".$this->url ."&pid=$this->pid&page_template=$this->template_selected&side_module='+this.value");
    $side_select_tag = xHtml::selectTag($side_select_options, $side_tag_attrs, $side_selected);
    
    $middle_selected = null;
    $middle_current_selecion = null;
    $middle_select_options = array();
    foreach ($middle_modules as $s_module) {
      $middle_select_options[$s_module['name']] = $s_module['name'];
      if(@$this->middle_selected == $s_module['name']) {
         $middle_selected = $s_module['name'];
      }
    }
    $middle_select_options[" "] = " ";
    ksort($middle_select_options);
    $middle_tag_attrs = array('name' => "add_middle_module",
                              'onchange' => "javascript: document.location='".$this->url ."&pid=$this->pid&page_template=$this->template_selected&middle_module='+this.value");
    $middle_select_tag = xHtml::selectTag($middle_select_options, $middle_tag_attrs, $middle_selected);

    
    if(!is_null($this->middle_selected)) {
      array_push($module_settings['middle'], $this->middle_selected);
    }
    if(!is_null($this->side_selected)) {
      array_push($module_settings['left'], $this->side_selected);
    }

    
    $side_dissabled = 'none';
    switch($selected_template) {
      case 'one':
        unset($module_settings['left']);
        unset($module_settings['right']);
        $side_dissabled = 'both';
        $show_columns = array('middle');
      break;
      case 'two':
      case 'two_l':
      case 'two_r':
/*           
        $all_modules = array_merge($module_settings['left'], $module_settings['right']);
        unset($module_settings['right']);
        $module_settings['left'] = $all_modules;
        $side_dissabled = 'right';
        $show_columns = array('left', 'middle');
*/        
        $show_columns = array('left', 'middle', 'right');
      break;
      case 'three':
        $show_columns = array('left', 'middle', 'right');
      break;
    }

    
    

    $this->set_inner_template('module_selector.tpl');
    $this->inner_HTML = $this->generate_inner_html(array('page_id'  => $this->pid,
                                                         'page_url' => $this->url,
                                                         'base_url' => $this->base_url,
                                                         'uid'      => $this->uid,
                                                         'gid'      => $this->gid,
                                                         'module_settings' => $module_settings,
                                                         'pages_default_setting' => $pages_default_setting,
                                                         'type' => 'module',
                                                         'settings_type' => $this->settings_type,
                                                         'select_tag' => $select_tag,
                                                         'template_select_tag' => $template_select_tag,
                                                         'side_select_tag' => $side_select_tag,
                                                         'middle_select_tag' => $middle_select_tag,
                                                         'current_selection' => $current_selection,
                                                         'side_dissabled' => $side_dissabled,
                                                         'show_columns' => $show_columns));
  }

  
  private function setupCustomizeDesktopImage($request_data) {
  
    $desktop_image_settings = array();
    $desktop_image_settings['header_image'] = null;
    $desktop_image_settings['header_image_action'] = null;
    $desktop_image_settings['display_header_image'] = null;
    
    switch($this->settings_type) {
      case 'user':
        $user = $this->shared_data['user_info'];
        $user_data_general = sanitize_user_data(User::load_user_profile($user->user_id, $user->user_id, GENERAL));
        $desktop_image_settings['header_image'] = @$user_data_general['user_caption_image'];
        $desktop_image_settings['header_image_action'] = @$user_data_general['desktop_image_action'];
        $desktop_image_settings['display_header_image'] = @$user_data_general['desktop_image_display'];
      break;
      case 'group':
        $group = $this->shared_data['group_info'];
        $extra = $this->shared_data['group_extra'];
        $desktop_image_settings['header_image'] = @$group->header_image;
        $desktop_image_settings['header_image_action'] = @$group->header_image_action;
        $desktop_image_settings['display_header_image'] = @$group->display_header_image;
      break;
      case 'network':
        $extra = $this->shared_data['extra'];
        $desktop_image_settings['header_image'] = @$extra['basic']['header_image']['name'];
        $desktop_image_settings['header_image_action'] = @$extra['basic']['header_image']['option'];
        $desktop_image_settings['display_header_image'] = @$extra['basic']['header_image']['display']; ;
      break; 
    }

    $header_image = $desktop_image_settings['header_image'];
    $header_image_action = $desktop_image_settings['header_image_action'];
    $display_header_image = $desktop_image_settings['display_header_image'];
    
    if (!empty($header_image_action)) {
      $opts = $header_image_action;
    } else {
      $opts = DESKTOP_IMAGE_ACTION_CROP;
      $header_image_action = DESKTOP_IMAGE_ACTION_CROP;
    }
    
    if (empty($display_header_image)) {
      $display_header_image = 0;
    }
    
    $image_info =  uihelper_resize_img($header_image, 422, 71, PA::$theme_rel."/images/header_image.jpg", $opts);
    $this->set_inner_template('desktop_image.php');
    $this->inner_HTML = $this->generate_inner_html(array('page_url' => $this->url,
                                                         'base_url' => $this->base_url,
                                                         'uid'      => $this->uid,
                                                         'gid'      => $this->gid,
                                                         'header_image_name' => $header_image,
                                                         'option' => $header_image_action,
                                                         'image_info' => $image_info,
                                                         'type' => 'desktop_image',
                                                         'dia' => $display_header_image,
                                                         'settings_type' => $this->settings_type));
  }


  private function setupCustomizeBackgroundImage($request_data) {
  
    $desktop_image_settings = array();
    $desktop_image_settings['background_image'] = null;
    
    switch($this->settings_type) {
      case 'user':
      break;
      case 'group':
      break;
      case 'network':
        $extra = $this->shared_data['extra'];
        $desktop_image_settings['background_image'] = @$extra['basic']['background_image']['name'];
      break;
    }

    $background_image = $desktop_image_settings['background_image'];
    
   
    $image_info = uihelper_resize_img($background_image, 1212, 1403, PA::$theme_rel."/skins/defaults/images/header_net.gif");
    $this->set_inner_template('bg_image.php');
    $this->inner_HTML = $this->generate_inner_html(array('page_url' => $this->url,
                                                         'base_url' => $this->base_url,
                                                         'uid'      => $this->uid,
                                                         'gid'      => $this->gid,
                                                         'backgr_image_name' => $background_image,
                                                         'image_info' => $image_info,
                                                         'type' => 'bg_image',
                                                         'settings_type' => $this->settings_type));
  }
  
  private function setupCustomizeStyle($request_data) {
  
    $css_path = PA::$theme_url . '/';
    $js_path  = PA::$theme_url . '/' . "javascript" . '/';
    
    $this->renderer->add_page_js($js_path . 'iutil.js');
    $this->renderer->add_page_js($js_path . 'json.js');
    $this->renderer->add_page_js($js_path . 'idrag.js');
    $this->renderer->add_page_js($js_path . 'isortables.js');
    // for style editor
    $this->renderer->add_page_js($js_path . 'jsonStringify.js');
    // $parameter .= js_includes('jquery.js');
    $this->renderer->add_page_js($js_path . 'jquery.compat-1.0.js');
    $this->renderer->add_page_js($js_path . 'configurator.js');
    $this->renderer->add_page_js($js_path . 'conf_css.js');
    $this->renderer->add_page_js($js_path . 'farbtastic.js ');
    // adding some New Css files require for this page only
    $this->renderer->add_header_css($css_path . "configurator.css");
    $this->renderer->add_header_css($css_path . "farbtastic.css");
    
    switch($this->settings_type) {
      case 'user':
        $user = $this->shared_data['user_info'];
        $user_data_ui = sanitize_user_data(User::load_user_profile($user->user_id, $user->user_id, 'ui'));
        $json_data = (!empty($user_data_ui['user_json'])) ? $user_data_ui['user_json'] : null;
      break;
      case 'group':
        $extra = $this->shared_data['group_extra'];
        $json_data = (!empty($extra['style']['user_json'])) ? $extra['style']['user_json']: null;
      break;
      case 'network':
        $extra = &$this->shared_data['extra'];
        $json_data = (!empty($extra['network_json'])) ? $extra['network_json'] : null;
      break;
    }
    // clean up the JSON a bit
		$json_data = preg_replace("/'/", '', $json_data);

    $this->set_inner_template('style_info.tpl');
    $this->inner_HTML = $this->generate_inner_html(array('json_data' => $json_data,
                                                         'page_url' => $this->url,
                                                         'base_url' => $this->base_url,
                                                         'uid'      => $this->uid,
                                                         'gid'      => $this->gid,
                                                         'type' => 'style',
                                                         'settings_type' => $this->settings_type));
  }

  function handleRequest($request_method, $request_data) {
    if(!empty($request_data['action'])) {
      $action = $request_data['action'];
      $class_name = get_class($this);
      switch($request_method) {
        case 'POST':
          $method_name = 'handlePOST_'. $action;
          if(method_exists($this, $method_name)) { 
             $this->{$method_name}($request_data);   
          } else {
             throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
          }
        break;
        case 'GET':
          $method_name = 'handleGET_'. $action;
          if(method_exists($this, $method_name)) { 
             $this->{$method_name}($request_data);   
          } else {
             throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
          }
        break;
        case 'AJAX':
          $method_name = 'handleAJAX_'. $action;
          if(method_exists($this, $method_name)) { 
             $this->{$method_name}($request_data);   
          } else {
             throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
          }
        break;
      }
    }
  }

  private function handlePOST_applyTheme($request_data) {
    global $error, $error_msg;

    $form_data = &$request_data['form_data'];
    
    switch($this->settings_type) {
      case 'user':
        $user = &$this->shared_data['user_info'];
        $user_skin = array('theme' => array('name'  => 'theme',
                                            'value' => $form_data['theme'],
                                            'perm' => ANYONE)
                          );
        $user->save_profile_section($user_skin, 'skin');
        // Now we save inline style = Empty
        // Such that user can see his theme 
        $user->save_profile_section(array(), 'ui');
      break;
      case 'group':
        $group = &$this->shared_data['group_info'];
        $extra = &$this->shared_data['group_extra'];
        $extra['theme'] = $form_data['theme'];
        $extra_param = serialize($extra);
        $ext = array('extra' => $extra_param);
        $group->save_group_theme($ext);
      break;
      case 'network':
        $network = &$this->shared_data['network_info'];
        $extra = &$this->shared_data['extra'];
        $extra['network_skin'] = $form_data['theme'];
        $data = array(
                  'extra'=>serialize($extra),
                  'network_id'=>$network->network_id,
                  'changed'=>time()
        );
        $network->set_params($data);
        try {
          $nid = $network->save();
          $network = get_network_info();// refreshing the network info
        } catch (PAException $e) {
          $error_msg = "$e->message";
        }
      break;
    }
    unset($request_data['form_data']);
    $this->controller->redirect($this->url);
  }

  
  private function handlePOST_saveModuleSettings($request_data) {
    $page_templates = $this->page_templates;
    $form_data = $request_data['form_data'];
    $page_template = $page_templates[$form_data['page_template']];
    $left  = null;
    $middle  = null;
    $right = null;
    $new_settings = array();
    foreach(array('left', 'middle', 'right') as $column) {
      if(isset($form_data[$column])) {
        foreach($form_data[$column] as $module_data) {
          if(!empty($module_data['name'])) {
            if($module_data['column'] == 'left') {
              $left[] = $module_data;
            } else if($module_data['column'] == 'middle') {
              $middle[] = $module_data;
            } else if($module_data['column'] == 'right') {
              $right[] = $module_data;
            } else {
            }
          }
        }
      }  
    }
    
    if($left) {
      usort($left, "sortByPosition");
      foreach($left as $key=>$lmodule) {
        if(isset($lmodule['name'])) {
          $left[$key] = $lmodule['name'];
        }
      }  
    }
    
    if($middle) {
      usort($middle, "sortByPosition");
      foreach($middle as $key=>$mmodule) {
        if(isset($mmodule['name'])) {
          $middle[$key] = $mmodule['name'];
        } 
      }
    }
      
    if($right) {
      usort($right, "sortByPosition");
      foreach($right as $key=>$rmodule) {
        if(isset($rmodule['name'])) {
          $right[$key] = $rmodule['name'];
        } 
      }
    }
      
    $new_settings['left'] = $left;
    $new_settings['middle'] = $middle;
    $new_settings['right'] = $right;
    $new_settings['page_template'] = $page_template;
    try {
       ModuleSetting::save_setting($this->assoc_id, $this->pid, $new_settings, $this->settings_type);
    } catch(Exception $e) {
      throw $e;
    }
    unset($request_data['form_data']);
    unset($request_data['page_template']);
    $this->setupCustomizeModuleSettings($request_data);
  }

  private function handlePOST_exportModuleSettings($request_data) {
    $page_templates = $this->page_templates;
    $form_data = $request_data['form_data'];
    $page_template = $page_templates[$form_data['page_template']];
    $left  = null;
    $middle  = null;
    $right = null;
    $new_settings = array();
    foreach(array('left', 'middle', 'right') as $column) {
      if(isset($form_data[$column])) {
        foreach($form_data[$column] as $module_data) {
          if(!empty($module_data['name'])) {
            if($module_data['column'] == 'left') {
              $left[] = $module_data;
            } else if($module_data['column'] == 'middle') {
              $middle[] = $module_data;
            } else if($module_data['column'] == 'right') {
              $right[] = $module_data;
            } else {
            }
          }
        }
      }  
    }
    
    if($left) {
      usort($left, "sortByPosition");
      foreach($left as $key=>$lmodule) {
        if(isset($lmodule['name'])) {
          $left[$key] = $lmodule['name'];
        }
      }  
    }
    
    if($middle) {
      usort($middle, "sortByPosition");
      foreach($middle as $key=>$mmodule) {
        if(isset($mmodule['name'])) {
          $middle[$key] = $mmodule['name'];
        } 
      }
    }
      
    if($right) {
      usort($right, "sortByPosition");
      foreach($right as $key=>$rmodule) {
        if(isset($rmodule['name'])) {
          $right[$key] = $rmodule['name'];
        } 
      }
    }
      
    $new_settings['left'] = $left;
    $new_settings['middle'] = $middle;
    $new_settings['right'] = $right;
    $new_settings['page_template'] = $page_template;
    $dynamic_page = new DynamicPage($this->pid);
    $dynamic_page->save_page = false;
    $dynamic_page->initialize($new_settings);
    $page_settings = $dynamic_page->saveXML();
    unset($request_data['form_data']);
    $file = pathinfo($dynamic_page->xml_file, PATHINFO_BASENAME);
/*
    $content_length = strlen($page_settings);
    header("Content-Type: application/XML; charset: UTF-8");
    header("Cache-Control: no-cache, no-store, max-age=0, must-revalidate");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Pragma: no-cache");
    header("Content-Length: " . (int)$content_length);
    flush();
    print($page_settings);
*/
//    exit;

      $header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
                   ? preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1)
                   : $file;
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: public", false);
      header("Content-Description: File Transfer");
      header("Content-Type: application/XML; charset: UTF-8");
      header("Accept-Ranges: bytes");
      header("Content-Disposition: attachment; filename=\"" . $header_file . "\";");
      header("Content-Transfer-Encoding: binary");
      header("Content-Length: " . strlen($page_settings));
      flush();
      print($page_settings);

    $this->setupCustomizeModuleSettings($request_data);
  }


  private function handlePOST_restoreModuleSettings($request_data) {
    $this->restore_module_settings = true;
    $this->setupCustomizeModuleSettings($request_data);
  }  

  private function handlePOST_applyDesktopImage($request_data) {
    global $error, $error_msg;
    $form_data = $request_data['form_data'];
    
    if(!empty($_FILES['header_image']['name'])) {
      $uploadfile = PA::$upload_path.basename($_FILES['header_image']['name']);
      $myUploadobj = new FileUploader; //creating instance of file.
      $image_type = 'image';
      $file = $myUploadobj->upload_file(PA::$upload_path, 'header_image', true, true, $image_type);
      if($file == false) {
        $error_msg = $myUploadobj->error;
        $error = TRUE;
      } else {
        $header_image = $file;
        Storage::link($header_image, array("role" => "header", "user" => PA::$login_user->user_id));
      }
    } else {
      $header_image = $form_data['header_image_name']; 
    }
    
    switch($this->settings_type) {
      case 'user':
        $user = $this->shared_data['user_info'];
        $user->set_profile_field(GENERAL, "desktop_image_display", $form_data['desktop_image_display']);
        $user->set_profile_field(GENERAL, "desktop_image_action", $form_data['header_image_option']);
        $user->set_profile_field(GENERAL, "user_caption_image", $header_image);
      break;
      case 'group':
        $group = &$this->shared_data['group_info'];
        $header_img = array('display_header_image' => $form_data['desktop_image_display'],
                            'header_image_action'  => $form_data['header_image_option'],
                            'header_image'         => $header_image);
        $group->save_group_theme($header_img);
        
        $group->header_image = $header_image;
        $group->header_image_action = $form_data['header_image_option'];
        $group->display_header_image = $form_data['desktop_image_display'];
      break;
      case 'network':
        $network = &$this->shared_data['network_info'];
        $extra = &$this->shared_data['extra'];
        $extra['basic']['header_image']['name'] =  $header_image;
        $extra['basic']['header_image']['option'] = $form_data['header_image_option'];
        $extra['basic']['header_image']['display'] = $form_data['desktop_image_display'];
        
        $data = array(
                  'extra'=>serialize($extra),
                  'network_id'=>$network->network_id,
                  'changed'=>time()
        );
        $network->set_params($data);
        try {
          $nid = $network->save();
          $network = get_network_info();// refreshing the network info
        } catch (PAException $e) {
          $error_msg = "$e->message";
        }  
      break;
    }
    unset($_FILES);
    unset($request_data['form_data']);
    $this->controller->redirect($this->url);
  }

  private function handlePOST_restoreDesktopImage($request_data) {
    $form_data = &$request_data['form_data'];
    $form_data['desktop_image_display'] = null;
    $form_data['header_image_option']  = null;
    $form_data['header_image_name'] = null;
    
    switch($this->settings_type) {
      case 'user':
      break;
      case 'group':
        $group = &$this->shared_data['group_info'];
        $group->header_image = null;
        $group->header_image_action = null;
        $group->display_header_image = null;
      break;
      case 'network':
        $extra = &$this->shared_data['extra'];
        $extra['basic']['header_image']['display'] = HEADER_IMAGE_DISPLAY_BLOCK;
      break;
    }
    $this->handlePOST_applyDesktopImage($request_data);
  }
  

  private function handlePOST_applyBackgroundImage($request_data) {
    global $error, $error_msg;
    $form_data = $request_data['form_data'];
    
    if(!empty($_FILES['network_image']['name'])) {
      $uploadfile = PA::$upload_path.basename($_FILES['network_image']['name']);
      $myUploadobj = new FileUploader; //creating instance of file.
      $image_type = 'image';
      $file = $myUploadobj->upload_file(PA::$upload_path, 'network_image', true, true, $image_type);
      if($file == false) {
        $error_msg = $myUploadobj->error;
        $error = TRUE;
      } else {
        $backgr_image = $file;
//        Storage::link($header_image, array("role" => "header", "user" => PA::$login_user->user_id));
      }
    } else {
      $backgr_image = (isset($form_data['backgr_image_name'])) ? $form_data['backgr_image_name'] : null;
    }
    
    switch($this->settings_type) {
      case 'user':
      break;
      case 'group':
      break;
      case 'network':
        $network = &$this->shared_data['network_info'];
        $extra = &$this->shared_data['extra'];
        $extra['basic']['background_image']['name'] =  $backgr_image;
       
        $data = array(
                  'extra'=>serialize($extra),
                  'network_id'=>$network->network_id,
                  'changed'=>time()
        );
        $network->set_params($data);
        try {
          $nid = $network->save();
          $network = get_network_info();   // refreshing the network info
        } catch (PAException $e) {
          $error_msg = "$e->message";
        }  
        $this->setupCustomizeBackgroundImage($request_data);
      break;
    }
    unset($_FILES);
    unset($request_data['form_data']);
    $this->controller->redirect($this->url);
  }
  
  private function handlePOST_restoreBackgroundImage($request_data) {
    switch($this->settings_type) {
      case 'user':
      break;
      case 'group':
      break;
      case 'network':
        $extra = &$this->shared_data['extra'];
        $extra['basic']['background_image']['name'] = null;
      break;
    }
    $this->handlePOST_applyBackgroundImage($request_data);
  }

  private function handlePOST_applyStyle($request_data) {
    global $error, $error_msg;

    $form_data = &$request_data['form_data'];
    
    if($this->restore_style) {
      $form_data['newcss'] = null;
      $form_data['user_json'] = null;
    }
    
    // clean up the JSON a bit
    $form_data['user_json'] = preg_replace("/'/", '', $form_data['user_json']);
    
    switch($this->settings_type) {
      case 'user':
        $user = &$this->shared_data['user_info'];
        $user_data_ui = array('newcss'    => array('name'  => 'newcss',
                                                   'value' => $form_data['newcss'],
                                                   'perm' => ANYONE),
                              'user_json' => array('name'  => 'user_json',
                                                   'value' => $form_data['user_json'],
                                                   'perm' => ANYONE)
                             );
         $user->save_profile_section($user_data_ui, 'ui');
      break;
      case 'group':
        $group = &$this->shared_data['group_info'];
        $extra = &$this->shared_data['group_extra'];
        $extra['style']['newcss'] = $form_data['newcss'];
        $extra['style']['user_json'] = $form_data['user_json'];
        $extra_param = serialize($extra);
        $ext = array('extra' => $extra_param);
        $group->save_group_theme($ext);
      break;
      case 'network':
        $network = &$this->shared_data['network_info'];
        $extra = &$this->shared_data['extra'];
        $extra['network_style'] = $form_data['newcss'];
        $extra['network_json'] = $form_data['user_json'];
        $data = array(
                  'extra'=>serialize($extra),
                  'network_id'=>$network->network_id,
                  'changed'=>time()
        );
        $network->set_params($data);
        try {
          $nid = $network->save();
          $network = get_network_info();// refreshing the network info
        } catch (PAException $e) {
          $error_msg = "$e->message";
        }
        unset($request_data['form_data']);
      break;
    }
    unset($request_data['form_data']);
    $this->controller->redirect($this->url);
  }

  private function handlePOST_restoreStyle($request_data) {
    $this->restore_style = true;
    $this->handlePOST_applyStyle($request_data);
  }
  
  function set_inner_template($template_fname) {
    $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/$template_fname";
  }

  function render() {
    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html($template_vars = array()) {
    
    $inner_html_gen = & new Template($this->inner_template);
    foreach($template_vars as $name => $value) {
      if(is_object($value)) {
        $inner_html_gen->set_object($name, $value);
      } else {
        $inner_html_gen->set($name, $value);
      }  
    }
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}

function sortByPosition($a, $b)
{
  if($a['position'] == $b['position']) return 0;
  return ($a['position'] < $b['position']) ? -1 : 1;
}

?>