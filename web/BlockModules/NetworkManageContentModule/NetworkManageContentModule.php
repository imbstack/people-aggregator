<?php

class NetworkManageContentModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->main_block_id = "mod_network_manage_content";
    $this->title = __('Network Operator Controls');

  }

   function render() {

    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';   
    }
    $net_details = & new Template($inner_template);
    $inner_html = $net_details->fetch();
    return $inner_html;
  }
}
?>
