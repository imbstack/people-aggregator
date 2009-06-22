<?php 
class NetworkStatisticsModule extends Module {
  //selecting outer for the module
  public $module_type = 'network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
 
  function __construct() {
  }
  
  function render() {
    $this->title = __("Network Statistics");
    $param['network_id'] = PA::$network_info->network_id;
    $this->network_statistics = Network::get_network_statistics( $param );
    $this->inner_HTML = $this->generate_inner_html( $this->network_statistics ); 
    $network_stats = parent::render();
    return $network_stats;
  }
  
  function generate_inner_html ($network_statistics) {
    switch ($this->mode) {
      case PRI:
        $this->outer_template = 'outer_private_side_module.tpl';
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;  
    }  
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('network_stats', $network_statistics);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>