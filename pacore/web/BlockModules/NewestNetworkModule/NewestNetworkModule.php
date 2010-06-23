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

class NewestNetworkModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public $sorting_options, $selected_option;

  public function __construct() {
    parent::__construct();
  }

  public function initializeModule($request_method, $request_data) {
    $this->title = __('Networks');
    $this->sort_by = TRUE;
  }

  public function render() {
    if (empty($this->links)) {
      $this->links = Network::get_largest_networks(false,5,1);
    }
    $this->inner_HTML = $this->generate_inner_html ($this->links);    
    if ($this->mode == SORT_BY) {
      $content = $this->inner_HTML;
    }
    else {
      $content = parent::render();
    }
    return $content;
  }

  public function generate_inner_html ($links) {
    if(empty($links)) {
      $this->sort_by=FALSE;
    }
    $inner_template = NULL;
    switch ($this->mode) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';      
    }

    $obj_inner_template = & new Template($inner_template);
    $obj_inner_template->set('links', $links);
    $obj_inner_template->set('block_name', $this->html_block_id);
    if ($this->sort_by) {
      $obj_inner_template->set('sort_by', $this->sort_by);
      $obj_inner_template->set('sorting_options', $this->sorting_options);
      $obj_inner_template->set('selected_option', $this->selected_option);
    }
 
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }
}
?>