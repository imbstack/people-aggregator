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

class SearchContentModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  
  public $uid;

  function __construct() {
    parent::__construct();
    $this->title = __('Search Content');
    $this->html_block_id = 'SearchContentModule';
    $this->id = 0;
  }

  function set_id($id) {
    $this->id = $id;
  }
 
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();    
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
     
    
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('current_theme_path', PA::$theme_url);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>