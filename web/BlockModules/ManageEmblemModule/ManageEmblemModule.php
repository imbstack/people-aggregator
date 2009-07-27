<?php

require_once "api/ModuleData/ModuleData.php";

class ManageEmblemModule extends Module {
 
  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

  function __construct() {
    parent::__construct();
    $this->html_block_id = 'ManageEmblemModule';
    $this->main_block_id = NULL;
  }

  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  function get_links() {
    $data = array();
    $emblem_data = ModuleData::get('LogoModule');
    if ($emblem_data) $data = unserialize($emblem_data);
    return $data;
  }
  function generate_inner_html () {
    $links = $this->get_links();
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('links', $links);
    $inner_html_gen->set('config_navigation_url',
                      network_config_navigation('manage_emblem'));
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>