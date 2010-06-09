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

class FeaturedNetworkModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->html_block_id = 'mod_featured_network';
  }
  
  function render() {
    $network = new Network();
    $extra = unserialize( PA::$network_info->extra );
    $this->network_data ='';
    if (!empty($extra['network_feature'])) {
      $network->network_id = $extra['network_feature'];
      $this->network_data = $network->get();
    }
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render ();
    return $content;
  }

  function generate_inner_html() {
        
    switch ( $this->mode ) {
     default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';   
    }
  
    $inner_html_gen = & new Template ( $inner_template );
    $inner_html_gen->set ( 'network_data', $this->network_data );
    $inner_html = $inner_html_gen->fetch ();
    return $inner_html;
  }
}
?>  