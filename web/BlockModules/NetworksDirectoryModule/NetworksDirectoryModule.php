<?php

require_once "api/Category/Category.php";
require_once "web/includes/classes/Pagination.php";


class NetworksDirectoryModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  public  $cid , $tag_id, $Paging;
  public $page_links, $page_prev, $page_next, $page_count;
  public $name_string;
  public $keyword;
  public $sort_by;
  public $users_network;

  public function __construct() {
    parent::__construct();
  }

  public function initializeModule($request_method, $request_data) {
    global $paging;
    $this->Paging = $paging;
  }


  public function handleNetworkSearch($request_method, $request_data) {
    switch ($request_method) {
      case 'GET':
        if(method_exists($this, 'handleGETPageSubmit')) {
            $this->handleGETPageSubmit($request_data);
        }
    }
  }

  public function handleGETPageSubmit($request_data) {
    if (!empty($request_data['keyword'])) {
      $this->name_string = @$request_data['name_string'];
      $this->keyword = $request_data['keyword'];
    }
    if (!empty($request_data['sort_by'])) {
      $this->sort_by = $request_data['sort_by'];
    }
    if (!empty(PA::$page_uid)) {
      $this->uid =  PA::$page_uid;
      $this->sort_by = $request_data['sort_by'];
    }
    $params = array('cnt'=>TRUE);
    $network_obj = new Network();
    $this->total_network = $network_obj->get($params);
  }
  
  /**
  Get all the links of different network of given search String 
  **/
  private function get_links() {
    $network = new Network();
    if (!empty($this->name_string)) {
      $condition = array('name_string'=>$this->name_string,'keyword'=>$this->keyword); 
      $this->Paging["count"] = $network->get_networks_info_by_search($condition,TRUE);
      if (empty($this->sort_by) || $this->sort_by == 'alphabetic') {
        $links = $network->get_networks_info_by_search($condition, FALSE, $this->Paging["show"],$this->Paging["page"],'name','ASC');
      } 
      if (!empty($this->sort_by) && $this->sort_by == 'members') {
        $links = $network->get_networks_info_by_search($condition, FALSE, $this->Paging["show"], $this->Paging["page"],'members');
      }
      if (!empty($this->sort_by) && $this->sort_by == 'created') {
        $links = $network->get_networks_info_by_search($condition, FALSE, $this->Paging["show"], $this->Paging["page"]);
      }
    } else { 
        $this->Paging["count"] = $network->get_largest_networks(TRUE);
      if (!empty($this->sort_by) && $this->sort_by == 'alphabetic' || !$this->sort_by) {
        $links = $network->get_largest_networks(FALSE, $this->Paging["show"],     $this->Paging["page"],'name','ASC');
      }
      if (!empty($this->sort_by) && $this->sort_by == 'members') {
        $links = $network->get_largest_networks(FALSE, $this->Paging["show"], $this->Paging["page"],'members');
      }
      if (!empty($this->sort_by) && $this->sort_by == 'created') {
        $links = $network->get_largest_networks(FALSE, $this->Paging["show"], $this->Paging["page"]);
      }
    }
    return $links;
  }
  
  public function render() {
    if (!empty($this->uid)) {
       $this->links = $this->Network_info_with_uid($this->uid,$this->sort_by);
    } 
    else {
     $this->links = $this->get_links();     
    }
    $this->inner_HTML = $this->generate_inner_html ($this->links);
    $content = parent::render();
    return $content;
  }

  public function Network_info_with_uid ($uid,$sort_by) {
    $network = new Network();
    $show_network = REGULAR_NETWORK_TYPE;
    $links = array();
    if (($uid == PA::$login_uid)) {
      $show_network = ALL_NETWORKS;
    }
    $this->Paging["count"] = $network->get_networks_by_user($uid,TRUE);
    
    if ($sort_by == 'alphabetic' || empty($sort_by)) {
      $links = $network->get_networks_by_user($uid,FALSE, $this->Paging["show"],     $this->Paging["page"],'name','ASC',$show_network);
    }
    if ($sort_by == 'members') {
      $links = $network->get_networks_by_user($uid,FALSE, $this->Paging["show"], $this->Paging["page"],'members','DESC',$show_network);
    }
    if ($sort_by == 'created') {
      $links = $network->get_networks_by_user($uid,FALSE, $this->Paging["show"], $this->Paging["page"],'created','DESC',$show_network);
    }
    return $links;
  }



  public function generate_inner_html ($links) {
    $usr = new User();
    $network = new Network();
    if(!empty($links)) {
      foreach ($links as $link_var) {
	      if ($link_var->owner_id) {
	         $usr->load((int)$link_var->owner_id);
	         $owner_info[$link_var->network_id]['name'] = $usr->login_name;
	       }
         else {
	         $owner_info[$link_var->network_id]['name'] = "None";
	       } 
	     }// End of foreach       
    }
    $users_network = NULL;
    if (!is_null(PA::$login_uid) && (PA::$login_uid == PA::$page_uid)) {
      $users_network = $network->get_networks_by_user(PA::$login_uid,FALSE,'ALL',1,'created','DESC',ALL_NETWORKS);
    } else if (!is_null(PA::$login_uid)) {
      $users_network = $network->get_networks_by_user(PA::$login_uid);
      $users_network = $this->get_user_network_id ($users_network);
    }
    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();
    switch ($this->mode) {
     default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';   
    }
    $this->Paging["count"] = (@$this->uid) ?  count($links): $this->Paging["count"];
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('links', $links);
    if (!empty($owner_info)) {
      $inner_html_gen->set('owner_info', $owner_info);
    }
    $inner_html_gen->set('users_network', $users_network);
    $inner_html_gen->set('total', $this->Paging["count"]);
    $inner_html_gen->set('search_str', get_network_search_options());
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
 
  public function get_user_network_id ( $user_network_info ) {
    $user_network_id = array();
    if (!empty($user_network_info)) {
       foreach ($user_network_info as $key_var) {
          $user_network_id[] = $key_var->network_id;
       }
    }
    return $user_network_id;
  }
}
?>
