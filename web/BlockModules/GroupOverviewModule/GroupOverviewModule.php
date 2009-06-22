<?php

class GroupOverviewModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  public  $group_details;
  function __construct() {
   
    $this->html_block_id = 'GroupOverviewModule';
  }
  
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    global $current_theme_path;
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $this->title = ucfirst($this->group_details['title']);
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('current_theme_path', $current_theme_path);
    $inner_html_gen->set('group_details', $this->group_details);
    $inner_html_gen->set('group_action', group_user_authentication ($this->group_details['collection_id']));
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
  

  
}