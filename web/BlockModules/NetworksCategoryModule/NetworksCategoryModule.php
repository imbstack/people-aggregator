<?php

class NetworksCategoryModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public $sub_cid;
  public $links;
  
  public function __construct() {
  }
  
  public function initializeModule($request_method, $request_data) {
    $params = array('cnt'=>TRUE);
    $network_obj = new Network();
    $this->total_network = $network_obj->get($params);
  }
  
  private function get_links() {
    $links = Network::category_network_listing();
    $newarray = array();
    $links_count = count($links);
    for ($i = 0, $j = 1, $m = 0; $i < $links_count; $i++, $j++, $m++) {
      $networks_info = array();
      if ($links[$i]->category_id != @$links[$j]->category_id ) {
        $newarray[$m]['cat_id'] = $links[$i]->category_id;
        $newarray[$m]['cat_name'] = $links[$i]->category_name;
        if ($links[$i]->network_id) {
          $networks_info[0]['network_name'] = $links[$i]->network_name;
          $networks_info[0]['network_id']= $links[$i]->network_id;
          $members= 1;
          $networks_info[0]['address'] = $links[$i]->address;
        }
        else {
          $networks_info[0]['network_name'] = $links[$i]->network_name;
          $networks_info[0]['network_id'] = $links[$i]->network_id;
          $members= 0;
          $networks_info[0]['address'] = $links[$i]->address;
        }
        $newarray[$m]['members'] = $members;
        $newarray[$m]['networks_info'] = $networks_info;
      }
      else {
         $k = 0;
         $networks_info[$k]['network_name'] = $links[$i]->network_name;
         $networks_info[$k]['network_id'] = $links[$i]->network_id;
         $networks_info[$k]['address'] = $links[$i]->address;
         // FIXME: the following comparison will fail and throw Notices if one of the two indices in not set (which does happen)
         while ($links[$i]->category_id == $links[$j]->category_id)  {
            ++$k;
            $networks_info[$k]['network_name'] = $links[$j]->network_name;
            $networks_info[$k]['network_id'] = $links[$j]->network_id;
            $networks_info[$k]['address'] = $links[$j]->address;
            $i++;
            $j++;
         } 
         $newarray[$m]['members'] = ++$k;  
         $newarray[$m]['cat_id'] = $links[$i]->category_id;
         $newarray[$m]['cat_name'] = $links[$i]->category_name;
         $newarray[$m]['networks_info'] = $networks_info;
        }

    }
    $links = $newarray;
    return $links;
  }
  
  public function render() {
    $this->title = _n(";Browse %d networks\n0-2;Browse networks", $this->total_network);
    $this->links = $this->get_links();
    $this->inner_HTML = $this->generate_center_category_inner_html($this->links);
    $content = parent::render();
    return $content;
  }

  public function generate_center_category_inner_html ($links) {
    switch ($this->mode) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';   
    }
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('newarray', $links);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>