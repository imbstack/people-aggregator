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

class NetworkBulletinsModule extends Module {
  
  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->title = __("Manage Network Bulletins");
    $this->html_block_id = "NetworkBulletinsModule";
  }

   function render() {    
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html() {
     
    switch( $this->mode ) {
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $net_details = new Template($tmp_file);
    $net_details->set('config_navigation_url',
                       network_config_navigation('bulletins'));
    
    $net_details->set('preview_msg', $this->preview_msg);
    $inner_html = $net_details->fetch();
    
    return $inner_html;
  }
}
?>
