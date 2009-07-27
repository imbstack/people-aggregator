<?php

class LoginModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_single_wide_module.tpl';  
  
  function __construct() {
    parent::__construct();
    $this->html_block_id = "LoginModule";
  }

   function render() {
    $this->outer = get_class_name(PAGE_LOGIN);
    $this->inner_HTML = $this->generate_center_public_inner_html();
    $content = parent::render();
    return $content;
  }
  
  function generate_center_public_inner_html () {
         

    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . "/inner_html.tpl";

    $info = & new Template($tmp_file, $this);
    $info->set_object('uid', @$this->uid);    
    $info->set('msg', @$this->msg);
    $info->set('array_of_errors', @$this->array_of_errors);
    $info->set('user_picture', @$this->user_picture);
    $info->set('login_name', @$this->login_name);
    $info->set('rel_type', @$this->rel_type);

    $inner_html = $info->fetch();

    return $inner_html;
  }

}
?>