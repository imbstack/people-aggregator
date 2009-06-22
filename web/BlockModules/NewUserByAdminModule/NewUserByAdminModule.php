<?php

class NewUserByAdminModule extends Module {
  
  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
  
    $this->html_block_id = "NewUserByAdminModule";
    $this->main_block_id = "mod_newuser_by_admin";
  }

   function render() {
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';   
    }
    
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('form_data', @$this->form_data);
     $inner_html_gen->set('config_navigation_url',
                          network_config_navigation( 'create_user' ) );
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>
