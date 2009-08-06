<?php
require_once "web/includes/classes/Pagination.php";
require_once "api/User/User.php";
require_once "api/Network/Network.php";
require_once "api/Roles/Roles.php";

class NetworkResultUserModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->main_block_id = "mod_network_user_result";
    $this->title = __('Manage Users');
  }

   //get list of users
  private function get_links() {
    //get total count
    $param = array( 'network_id'=>PA::$network_info->network_id, 'cnt'=>TRUE,'neglect_owner' =>TRUE );
    //search by login name
    if ( !empty($this->keyword) ) {
    	$param['search_keyword'] = $this->keyword;
    }
    $param['sort_by'] = $this->sort_by;
    $param['direction'] = $this->direction;
    $param['show_waiting_users'] = true;
    $this->Paging["count"] =  Network::get_members($param);
    //now we dont need $param['cnt']
    unset($param['cnt']);
    $param['show'] = $this->Paging['show'];
    $param['page']= $this->Paging['page'];
    //get actual list
    $users =  Network::get_members($param);
    $links = $users['users_data'];
    $objects = array('network' => true,
                     'groups'  => array(),
                     'forums'  => array()
                    );
    //echo serialize($objects);
//    echo '<pre>'.print_r($links,1).'</pre>';
    return $links;
  }
   //render the contents of the page   
   function render() {
  
    $this->links = $this->get_links();
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  //inner html of the module generation
  function generate_inner_html () {
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';   
    }
    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);    
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();
    $role = new Roles();
    $this->role_links = $role->get_multiple(null, DB_FETCHMODE_ASSOC);
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('link_role', $this->role_links);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html_gen->set('super_user_and_mothership', $this->super_user_and_mothership);
    $inner_html_gen->set('config_navigation_url',
                      network_config_navigation('manage_user'));
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>

