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

class GroupModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  public $max_height;
  public $homepage_sortby = FALSE;
  public $sorting_options,$selected_option;

  function __construct() {
    parent::__construct();
  }

  function render() {
    $this->inner_HTML = $this->generate_inner_html ($this->links);    
    if( $this->mode == SORT_BY ) {
      $content = $this->inner_HTML;
    }
    else {
      $content = parent::render();
    }
    return $content;
  }

  function generate_inner_html ($links) {
    
    if(empty($links)) {
      $this->sort_by=FALSE;
    }    
    $inner_template = NULL;
    switch ( $this->mode ) {
      case SORT_BY:
        $inner_template = PA::$blockmodule_path .'/'. ((get_parent_class($this)) ? get_parent_class($this) : get_class($this)) . '/side_inner_sortby.tpl';
      break;
      default:
        $inner_template = PA::$blockmodule_path .'/'. ((get_parent_class($this)) ? get_parent_class($this) : get_class($this)) . '/side_inner_public.tpl';
    }
    
    $obj_inner_template = new Template($inner_template);
    $obj_inner_template->set('links', $links);
    $obj_inner_template->set('block_name', $this->html_block_id);
    if (!empty($this->sort_by)) {
      $obj_inner_template->set('sort_by', $this->sort_by);
      $obj_inner_template->set('sorting_options', $this->sorting_options);
      $obj_inner_template->set('selected_option', $this->selected_option);
    }
    $obj_inner_template->set('current_theme_path', PA::$theme_url);
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }
}
?>