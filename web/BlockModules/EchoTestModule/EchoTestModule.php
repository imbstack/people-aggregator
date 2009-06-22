<?php

class EchoTestModule extends Module {

  public $module_type = 'group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  function __construct() {
    $this->html_block_id = "EchoTestModule";
    $this->main_block_id = "mod_echo_test";
    parent::__construct();
  }

  function render() {
    if (empty(PA::$login_user)) return "Login required";

    $this->title = __('Echo Test');

    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function render_for_post() {
    if (empty(PA::$login_user)) return "Login required";
    
    return "here's what happens when you post.  data: ".print_r($this->post_params, TRUE);
  }
  
  function generate_inner_html() {
    $tpl = & new Template(PA::$blockmodule_path .'/'. get_class($this) . "/center_inner_public.tpl", $this);
    $tpl->set("test_blob", PA::$login_user->get_profile_field("echo_test_module", "test_blob"));
    $inner_html = $tpl->fetch();
    return $inner_html;
  }
}
?>