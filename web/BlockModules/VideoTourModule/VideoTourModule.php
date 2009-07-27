<?php

class VideoTourModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->html_block_id = 'mod_video_tour';
  }
  
  function render() {
    $this->inner_HTML = $this->generate_center_video_inner_html ();
    $content = parent::render();
    return $content;
  }
 function generate_center_video_inner_html () {
    switch ( $this->mode ) {
     default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';   
    }
 
    $inner_html_gen = & new Template($inner_template);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>  