<?php

require_once "api/Activities/ActivityType.class.php";

class ManageRankingPointsModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';

  function __construct() {
    $this->title = __('Manage Ranking points');
  }

  function initializeModule($request_method, $request_data) {
    global $error_msg;
    $this->outer_template = 'outer_public_center_module.tpl';
    if(($request_method != 'POST') && ($request_method != 'AJAX')) {
      $this->set_inner_template('default_inner.tpl');                // initial template
      $activities = ActivityType::get_activity_types();
      $this->inner_HTML = $this->generate_inner_html(array('page_id' => $this->page_id, 'activities' => $activities));
    }
  }
  
  function handleUpdateActivities($request_method, $request_data) {
    switch($request_method) {
      case 'POST':
        if(method_exists($this, 'handlePOSTPageSubmit')) {  
           $this->handlePOSTPageSubmit($request_data);      
        }
      break;
      case 'GET':
        if(method_exists($this, 'handleGETPageSubmit')) {   
           $this->handleGETPageSubmit($request_data);       
        }   
      break;
      case 'AJAX':
        if(method_exists($this, 'handleAJAXPageSubmit')) {  
           $this->handleAJAXPageSubmit($request_data);      
        }   
      break;
    }
  }
  
  function handlePOSTPageSubmit($request_data) {
    global $error_msg;
//    echo '<pre>'.print_r($request_data,1).'</pre>';
    if(isset($request_data['form_data'])) {
        try {
          foreach($request_data['form_data'] as $id => $act_data) { 
            $msg = null;
            if(($id == 'new') && !empty($act_data['points'])) {
              if($this->validateFormData($act_data, &$msg)) {
                $activity = new ActivityType($act_data['title'],
                                             $act_data['description'],
                                             $act_data['type'],
                                             $act_data['points']);
                $activity->save();                             
              } else {
                throw new DynamicPageException($msg);
              }
            }
            if($id != 'new') {
              if($this->validateFormData($act_data, &$msg)) {
                 $activity = new ActivityType($act_data['title'],
                                              $act_data['description'],
                                              $act_data['type'],
                                              $act_data['points']);
                 $activity->id = $act_data['id'];
                 if(isset($request_data['submit_delete']) && isset($act_data['selected'])) {
                   $activity->delete();
                 } else {
                   $activity->save();
                 }  
              } else {
                 throw new DynamicPageException($msg);
              }  
            }
          }   
        } catch (DynamicPageException $e) {
          $error_msg = $e->getMessage();
        }
        unset($request_data['form_data']);
        $this->set_inner_template('default_inner.tpl');                // initial template
        $activities = ActivityType::get_activity_types();
        $this->inner_HTML = $this->generate_inner_html(array('page_id' => $this->page_id, 'activities' => $activities));
    }
  }

  private function validateFormData($data, &$msg) {
    $res = true;
    if(empty($data['title']) || empty($data['type'])) {
      $msg = __("'Title' and/or 'Type' filed should not be empty.");
      $res = false;
    }
    if(empty($data['points']) || (!is_numeric($data['points']))) {
      $msg = __("'Points' filed should be numeric and not empty.");
      $res = false;
    }
    return $res;
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
      $inner_html_gen->set($name, $value);
    }
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}

?>