<?php
require_once "api/Tasks/Tasks.php";
require_once "web/includes/classes/xhtmlTagHelper.class.php";
require_once "tools/modules/ModulesInfo.class.php";

class CreatePageModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  private $page = null;

  function __construct() {
    $this->title = __('Edit/Create Dynamic Page');
    $this->page = new DynamicPage();
    $this->page->initialize();
  }

  function initializeModule($request_method, $request_data) {
    global $error_msg, $settings_new;

    $this->id = (!empty($request_data['id'])) ? $request_data['id'] : 0;
    $this->module = (!empty($request_data['module'])) ? $request_data['module'] : null;
    $mod_info = new ModulesInfo(array(PA::$core_dir.DIRECTORY_SEPARATOR.PA::$blockmodule_path,
                                      PA::$project_dir.DIRECTORY_SEPARATOR.PA::$blockmodule_path ));
    $condition = ModulesInfo::USER_MODULES_FILTER . " || " . ModulesInfo::GROUP_MODULES_FILTER. " || " . ModulesInfo::NETWORK_MODULES_FILTER;
    $modules = $mod_info->getModulesByCondition($condition);

    $module_info = null;
    $mod_selected = null;
    $mod_select_options = array();
    $mod_select_options[" "] = " ";
    foreach($modules as $module) {
      $mod_select_options[$module['name']] = $module['name'];
      if(@$request_data['module'] == $module['name']) {
        $mod_selected = $module['name'];
        $module_info['name'] = $module['name'];
        $module_info['module_type'] = $module['module_type'];
        $module_info['module_placement'] = $module['module_placement'];
        $module_info['status_points'] = 0;
        if(!empty($module['architecture_info']['has_init_module'])) {
          $module_info['status_points'] += 33;
        }
        if(!empty($module['architecture_info']['has_action_handler'])) {
          $module_info['status_points'] += 33;
        }
        if(!empty($module['architecture_info']['has_set_inner_tpl'])) {
          $module_info['status_points'] += 33;
        }
      }
    }
    asort($mod_select_options);
    $mod_tag_attrs = array('name' => "form_data[module]",
                           'onchange' => "javascript: document.location='".PA_ROUTE_CREATE_DYN_PAGE ."?action=edit&id=$this->id&module='+this.value");
    $mod_select_tag = xhtmlTagHelper::selectTag($mod_select_options, $mod_tag_attrs, $mod_selected);

    $pages_default_setting = ModuleSetting::get_pages_default_setting( 'network' );
    $selected = null;
    $current_selecion = null;
    $select_options = array();
    $select_options[" "] = "0";
    foreach ($pages_default_setting as $page_details) {
      $select_options[$page_details->page_name] = $page_details->page_id;
      if(@$request_data['id'] == $page_details->page_id) {
         $selected = $page_details->page_id;
         $current_selection = $page_details;
         $restore_settings  = $page_details->getPageSettings();
      }
    }

    $tag_attrs = array('name' => "form_data[page_id]",
                       'onchange' => "javascript: document.location='".PA_ROUTE_CREATE_DYN_PAGE ."?action=edit&module=$this->module&id='+this.value");
    $select_tag = xhtmlTagHelper::selectTag($select_options, $tag_attrs, $selected);


    $this->outer_template = 'outer_public_center_module.tpl';
//    $this->shared_data['OVO_JE_DODANO'] = "Ovo je dodano unutar modula!";
    $task_obj = Tasks::get_instance();
    $tasks    = $task_obj->get_tasks();
    $permiss  = array();
    $permiss[] = 'configure_system';         // NOTE: system administrator permissions!!
    foreach($tasks as $task) {
      $permiss[] = $task->task_value;
    }
    $this->adm_permissions = implode(', ', $permiss);

    if($request_method == 'GET') {
      if(!empty($request_data['action']) && !empty($request_data['id']) && ($request_data['action']) == 'edit' ) {
        $this->page = new DynamicPage((int)$request_data['id'], $settings_new);
        if(!empty($request_data['add'])) {
          $this->page->addModule($request_data['add'], $this->module);
        }
        $this->page->initialize();
      }
    }
    $this->set_inner_template('center_inner_public.tpl'); // initial template
    $this->inner_HTML = $this->generate_inner_html(array('page_id' => $this->page_id,
                                                         'page' => $this->page,
                                                         'select_tag' => $select_tag,
                                                         'type' => 'theme',
                                                         'base_url' => PA_ROUTE_CREATE_DYN_PAGE,
                                                         'mod_select_tag' => $mod_select_tag,
                                                         'module_info'   => $module_info,
                                                         'adm_permissions' => $this->adm_permissions
                                                        ));
  }

  function handleCreatePageSubmit($request_method, $request_data) {
    switch($request_method) {
      case 'POST':
        if(method_exists($this, 'handlePOSTPageSubmit')) {  // function handlePOSTPageSubmit implemented?
           $this->handlePOSTPageSubmit($request_data);      // yes, use this function to handle POST data!
        }
      break;
      case 'GET':
        if(method_exists($this, 'handleGETPageSubmit')) {   // function handleGETPageSubmit implemented?
           $this->handleGETPageSubmit($request_data);       // yes, use this function to handle GET data!
        }
      break;
      case 'AJAX':
        if(method_exists($this, 'handleAJAXPageSubmit')) {  // function handleAJAXPageSubmit implemented?
           $this->handleAJAXPageSubmit($request_data);      // yes, use this function to handle POST data!
        }
      break;
    }
  }

  function handlePOSTPageSubmit($request_data) {
    global $error_msg;
    if(isset($request_data['form_data'])) {
      $new_page_settings = $request_data['form_data'];
      $new_page_settings['left']        = array();
      $new_page_settings['middle']      = array();
      $new_page_settings['right']       = array();
      $new_page_settings['javascripts'] = array();
      $new_page_settings['page_css']    = array();
      if(!empty($request_data['form_data']['left']))
        $new_page_settings['left']        = explode(',', $request_data['form_data']['left']);
      if(!empty($request_data['form_data']['middle']))
        $new_page_settings['middle']      = explode(',', $request_data['form_data']['middle']);
      if(!empty($request_data['form_data']['right']))
        $new_page_settings['right']       = explode(',', $request_data['form_data']['right']);
      if(!empty($request_data['form_data']['javascripts']))
        $new_page_settings['javascripts'] = explode(',', $request_data['form_data']['javascripts']);
      if(!empty($request_data['form_data']['page_css']))
        $new_page_settings['page_css']    = explode(',', $request_data['form_data']['page_css']);

      if(!empty($request_data['form_data']['navigation_code'])) {
        $code = trim($request_data['form_data']['navigation_code']);
        $new_page_settings['navigation_code'] = $code;
      }

      if(!empty($request_data['form_data']['boot_code'])) {
        $bcode = trim($request_data['form_data']['boot_code']);
        $new_page_settings['boot_code'] = $bcode;
      }
      $page_settings = array();
      try {

        $dyn = new DynamicPage($new_page_settings['page_id']);
        $dyn->initialize();
        $save_page = (isset($request_data['save_page'])) ? true : false;
        $page_settings = $dyn->buildPageSettings($new_page_settings);
        $serialized_settings = serialize($page_settings);
        $this->set_inner_template('submit_success_inner.tpl');
        $this->inner_HTML = $this->generate_inner_html(array('page_id'             => $this->page_id,
                                                             'page_settings'       => $page_settings,
                                                             'serialized_settings' => $serialized_settings,
                                                             'save_page'           => $save_page,
                                                             'adm_permissions'     => $this->adm_permissions
                                                            )
                                                      );
      } catch (DynamicPageException $e) {
        $error_msg = $e->getMessage();
      }
    }
  }

  function set_inner_template($template_fname) {
    global $current_blockmodule_path;
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

?>