<?php

require_once "api/Network/Network.php";

class ActionsModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public  $uid, $_PA;  
  public $outer_template = 'outer_public_side_module.tpl';
  function __construct() {
    
  parent::__construct();
    $this->title = __('Actions');
    $this->html_block_id = 'ActionsModule';
        
  }
    
 function render() {
    $this->inner_HTML = $this->generate_inner_html();
    if (! $this->inner_HTML) {
      return "";
    }
    $content = parent::render();
    return $content;
  }

  public function initializeModule($request_method, $request_data) {
  
    global $_PA;
    $this->navigation_links = 
    	$this->renderer->top_navigation_bar->vars['navigation_links'];

    $ac = 
	  	(array)@$this->navigation_links['level_3']
	  	+ (array)@$this->navigation_links['left_user_public_links'] 
		  ;
		  $actions = array();
		  foreach($ac as $k=>$action) {
		  	if ($k=='highlight') continue;
		  	$actions[$k] = $action;
		  }


    if (empty($actions)) {
      return 'skip';
    }
    $this->actions = $actions;
  }

  function generate_inner_html() {
    
    // $this->title .= "$page_name";
    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    $inner_html_gen = & new Template($inner_template, $this);
    $inner_html_gen->set('actions', $this->actions);    
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>
