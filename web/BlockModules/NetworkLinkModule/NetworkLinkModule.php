<?php

require_once "ext/NetworkLinks/NetworkLinks.php";

class NetworkLinkModule extends Module {
  
  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->title = __("Manage Network Links");
    $this->html_block_id = "NetworkLinkModule";
    $this->main_block_id = "mod_link";
  }

   function render() {    
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  
   function get_link_categories () {
      $Links = new NetworkLinks();
      $condition = array('is_active'=> 1);
      $link_categories_array = $Links->load_category ($condition);
      return $link_categories_array;      
  }

  function generate_inner_html () {
     
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/public_center_inner_html.tpl';
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('link_categories_array', $this->get_link_categories());
    $inner_html_gen->set('config_navigation_url',
                          network_config_navigation( 'manage_link' ) );
    $inner_html = $inner_html_gen->fetch();
    
    return $inner_html;
  }
}
?>