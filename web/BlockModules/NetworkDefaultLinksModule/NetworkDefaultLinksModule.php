<?php

require_once "ext/NetworkLinks/NetworkLinks.php";
require_once "api/Network/Network.php";

class NetworkDefaultLinksModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public  $uid, $links_data_array;
  function __construct() {

  parent::__construct();
    $this->title = __('Network Default Links');
  }

 function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    switch ($this->mode) {
      case PRI:
        $this->outer_template = 'outer_private_side_module.tpl';
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;

    }
    
    $inner_html_gen = & new Template($inner_template);
    $links_data_array = $this->get_user_links();
    $inner_html_gen->set('links_data_array', $links_data_array);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

  function get_user_links () {
    if (Network::is_mother_network(PA::$network_info->network_id)){
      $uid = SUPER_USER_ID;
    } else {
      $uid = Network::get_network_owner(PA::$network_info->network_id);
    }
    $condition = array('user_id'=> $uid, 'is_active'=> 1);
    //$limit = 5 ; // 5 lists to be display on home page
    $Links = new NetworkLinks();
    $category_list = $Links->load_category ($condition);
    if (!empty($category_list )) {
      for ($counter = 0; $counter < count($category_list); $counter++) {
          $links_data_array[$counter]['category_id'] = $category_list[$counter]->category_id;
          $links_data_array[$counter]['category_name'] = $category_list[$counter]->category_name;

          $Links->user_id = $uid;
          $condition = array('category_id'=>$category_list[$counter]->category_id, 'is_active'=> 1);
          $limit = 5; // 5 links to be display on home page
          $links_array = $Links->load_link ($condition, $limit);
          $links_data_array[$counter]['links'] = $links_array;
      }
      return $links_data_array;
    } else {
      $this->do_skip = TRUE;
    }
  }
}
?>