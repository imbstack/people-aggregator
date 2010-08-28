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

class StaticPageDisplayModule extends Module {

  public $module_type = 'system';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

  function __construct() {    
    parent::__construct();
    $this->block_type = 'StaticPageDisplayModule';
    $this->html_block_id = 'StaticPageDisplayModule';
  }

  function render() {
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html() {
    $inner_template = NULL;
    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_html.tpl';
    $obj_inner_template = new Template($inner_template); 
    $obj_inner_template->set('text', $this->text);    
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }
}
?>
