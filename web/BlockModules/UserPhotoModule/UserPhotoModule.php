<?php

require_once "api/ImageResize/ImageResize.php";

class UserPhotoModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->title = __('Photo');
    $this->main_block_id = "mod_photo";
    $this->html_block_id = 'UserPhotoModule';
    $this->block_type == 'UserPhoto';

 }
 
 public function initializeModule($request_method, $request_data)  {
    if(!empty($this->shared_data['user_info'])) {
      $this->user = $this->shared_data['user_info'];
      $this->uid = $this->user->user_id;
    } else {
      return 'skip';
    }
 }

 function render() {
    // render
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
 }

 function generate_inner_html () {
    switch ( $this->mode ) {
     case PRI:
        $this->outer_template = 'outer_private_side_module.tpl';
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
    }
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('picture', $this->user->picture);
    $inner_html_gen->set('uid', $this->user->user_id);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>
