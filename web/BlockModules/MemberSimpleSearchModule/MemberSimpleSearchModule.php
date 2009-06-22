<?php

class MemberSimpleSearchModule extends Module {
  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';


  function __construct() { 
    $this->outer_template = 'outer_public_side_module.tpl';
	  $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/member_search_sidebar.tpl.php";
    $this->title = __('Member Search');
    $this->html_block_id = 'MemberSimpleSearchModule';
  }

  function handleRequest($request_method, $request_data) {
  }

  function initializeModule($request_method, $request_data) {
  	switch($this->column) {
    	case 'middle':
      break;
      case 'left':
      case 'right':
      default:
      break;
    }
  }
  
  function render() {
    $this->inner_HTML = $this->generate_inner_html();

    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html() {
    $inner_html_gen = & new Template($this->inner_template, $this);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>