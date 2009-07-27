<?php

class PersonalAnnouncementModule extends Module {
  public $module_type = 'user';
  public $module_placement = 'left|right|middle';
  public $outer_template = 'outer_public_side_module.tpl';

  function __construct() {
    parent::__construct();
    $this->title = __('Shout-out');
    $this->html_block_id = 'PersonalAnnouncementModule';
  }

  function initializeModule($request_method, $request_data) {
		if (empty(PA::$page_uid)) return 'skip';

		$shoutout = null;
/*  
		$udg = User::load_user_profile(PA::$page_uid, PA::$login_uid, GENERAL);
		foreach ($udg as $d) if ($d['name']=='shoutout') $shoutout = $d['value'];
*/  
   $this->edit_permission = (PA::$page_uid == PA::$login_uid) ? true : false;
   $shoutout = PA::$page_user->get_profile_field(GENERAL, "shoutout");
  	if (!empty($shoutout)) {
  		$this->announcement = $shoutout;
  	} else {
    if($this->edit_permission) {
      $this->announcement = __("Click here to add your Shout-out.");
    } else {
      return "skip";
    }
  	}
  }
  
  function handleRequest($request_method, $request_data) {
    $class_name = get_class($this);
    if(!empty($request_data['action']) && !empty($request_data['module']) && ($request_data['module'] == $class_name)) { 
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


  private function handleAJAX_updateUserAnnouncement($request_data) {
    filter_all_post($request_data);
    $msg = 'success';
    $html = null;
    try {
      PA::$login_user->set_profile_field(GENERAL, "shoutout", $request_data['value']);
      $html = PA::$login_user->get_profile_field(GENERAL, "shoutout");
    } 
    catch(Exception $e) {
      $msg = $html = $e->getMessage();
    } 
    echo json_encode(array("msg"=>$msg, "result"=>$html));
    exit;
  }

  function render() {
  	switch ($this->column) {
  		case 'left':
  		case 'right':
       $this->outer_template = 'outer_public_side_module.tpl';
  		break;
  		case 'middle':
       $this->outer_template = 'outer_public_center_module.tpl';
      break;
    }

    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
     
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl.php';

    $inner_html_gen = & new Template($tmp_file, $this);

    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>
