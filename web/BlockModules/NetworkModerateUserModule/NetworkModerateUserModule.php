<?php 
require_once "web/includes/classes/Pagination.php";
require_once "api/User/User.php";
require_once "api/Network/Network.php";

class NetworkModerateUserModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->main_block_id = "mod_network_user_result";
    $this->title = __('Moderate Users');
  }

   //get list of users
  private function get_links() {
    global $network_info;
    //get total count
    $param = array( 'network_id'=>$network_info->network_id, 'cnt'=>TRUE,'user_type' =>NETWORK_WAITING_MEMBER );
    $param['sort_by'] = $this->sort_by;
    $param['direction'] = $this->direction;
    $this->Paging["count"] =  Network::get_members_by_type($param);
    //now we dont need $param['cnt']
    unset($param['cnt']);
    $param['show'] = $this->Paging['show'];
    $param['page']= $this->Paging['page'];
    //get actual list
    $users =  Network::get_members_by_type($param);
    $links = $users['users_data'];
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
    
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html_gen->set('super_user_and_mothership', $this->super_user_and_mothership);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>

