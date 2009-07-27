<?php
require_once "api/Category/Category.php";
require_once "api/Network/Network.php";
require_once "web/includes/classes/Pagination.php";


class GroupsDirectoryModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  public $cid , $tag_id, $Paging;
  public $page_links, $page_prev, $page_next, $page_count,$name_string, $links;
  public $keyword, $sort_by, $total_groups;

  function __construct() {
    parent::__construct();
    $this->html_block_id = 'GroupsDirectoryModule';
  }
  
  /**
  * Get all the links of different group of given search String 
  */
  
  public function initializeModule($request_method, $request_data) {
    global $paging;
    $this->Paging = $paging;
    $this->total_groups = Group::get_total_groups();
    $this->sort_by = (!empty($request_data['sort_by'])) ? $request_data['sort_by'] : NULL;
    $this->name_string = (!empty($request_data['name_string'])) ? $request_data['name_string'] : NULL;
    $this->keyword = (!empty($request_data['keyword'])) ? $request_data['keyword'] : NULL;
    $this->uid = PA::$page_uid;
  }
  
  private function get_links() {
     $group = new Group();
    if ( @$this->name_string ) {
      if($this->name_string !='tags') {
        $condition = array('name_string'=>$this->name_string,'keyword'=>$this->keyword); 
        if ( $this->sort_by == 'alphabetic' || !$this->sort_by) {
            $this->Paging["count"] = $group->get_groups_info_by_search($condition,$cnt=TRUE);
            $links = $group->get_groups_info_by_search($condition,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'title','ASC');
        } 
        if ($this->sort_by == 'members') {
            $this->Paging["count"] = $group->get_groups_info_by_search($condition,$cnt=TRUE);
            $links = $group->get_groups_info_by_search($condition,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'members');
        }
        if ($this->sort_by == 'created') {
            $this->Paging["count"] = $group->get_groups_info_by_search($condition,$cnt=TRUE);
            $links = $group->get_groups_info_by_search($condition,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
        }
      }
      else {
        $tag_var = new Tag();
        $this->Paging["count"] = $tag_var->get_associated_contentcollectionids($this->keyword, $cnt=TRUE);
        $tag_list = $tag_var->get_associated_contentcollectionids($this->keyword, $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);

        $link = $this->filter_collections($tag_list);

        $sorted_array = objtoarray($link);

        if ($this->sort_by == 'alphabetic' || !$this->sort_by) {
          sortByFunc($sorted_array, create_function('$sorted_array','return $sorted_array["group_name"];'),'asc',1);
        }
        if ($this->sort_by == 'members') {
          sortByFunc($sorted_array, create_function('$sorted_array','return $sorted_array["members"];'), 'desc',1);
        }
        if ($this->sort_by == 'created') {
          sortByFunc($sorted_array, create_function('$sorted_array','return $sorted_array["created"];'), 'desc',1);
        }
        $links = $sorted_array;
      }
    }
    else { 
       if ($this->sort_by == 'alphabetic' || !$this->sort_by) {
          $this->Paging["count"] = $group->get_groups_by_user(FALSE,$cnt=TRUE);
          $links = $group->get_groups_by_user(FALSE,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'title','ASC');
       }
       if ($this->sort_by == 'members') {
          $this->Paging["count"] = $group->get_groups_by_user(FALSE,$cnt=TRUE);
          $links = $group->get_groups_by_user(FALSE,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'members');
       }
       if ($this->sort_by == 'created') {
          $this->Paging["count"] = $group->get_groups_by_user(FALSE,$cnt=TRUE);
          $links = $group->get_groups_by_user(FALSE,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
       }
     }
     
     return $links;
  }


  function render() {
    if (@$this->uid) {
       $this->links=$this->group_info_with_uid($this->uid,$this->sort_by);
       $this->total = count($this->links);
    } 
    else {
     $this->links = $this->get_links();
     $this->total = (count($this->links) == $this->Paging["show"]) ? $this->total_groups: count($this->links);
    }


    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function group_info_with_uid ($uid, $sort_by) {
    $group = new group();
     if ( $this->name_string ) {
       if($this->name_string !='tags') {
          $condition = array('name_string'=>$this->name_string,'keyword'=>$this->keyword); 
          if ( $this->sort_by == 'alphabetic' || !$this->sort_by) {
              $this->Paging["count"] = $group->get_groups_info_by_search($condition,$cnt=TRUE);
              $links = $group->get_groups_info_by_search($condition,$cnt=FALSE, $this->Paging["show"],$this->Paging["page"],'title','ASC', $uid);
          } 
          if ($this->sort_by == 'members') {
              $this->Paging["count"] = $group->get_groups_info_by_search($condition,$cnt=TRUE);
              $links = $group->get_groups_info_by_search($condition,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'members', $uid);
          }
          if ($this->sort_by == 'created') {
              $this->Paging["count"] = $group->get_groups_info_by_search($condition,$cnt=TRUE);
              $links = $group->get_groups_info_by_search($condition,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"], NULL, $uid);
          }

       }
       else { // Search By Tag ..
          $tag_var = new Tag();
          $this->Paging["count"] = $tag_var->get_associated_contentcollectionids($this->keyword, $cnt=TRUE);
          $tag_list = $tag_var->get_associated_contentcollectionids($this->keyword, $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);

          $link = $this->filter_collections($tag_list);

          $sorted_array = objtoarray($link);

          if ($this->sort_by == 'alphabetic' || !$this->sort_by) {
            sortByFunc($sorted_array, create_function('$sorted_array','return $sorted_array["group_name"];'),'asc',1);
          }
          if ($this->sort_by == 'members') {
            sortByFunc($sorted_array, create_function('$sorted_array','return $sorted_array["members"];'), 'desc',1);
          }
          if ($this->sort_by == 'created') {
            sortByFunc($sorted_array, create_function('$sorted_array','return $sorted_array["created"];'), 'desc',1);
          }
          $links = $sorted_array;         
       
       }
     }
     else {
       if ($sort_by == 'alphabetic' || !$sort_by) {
          $this->Paging["count"] = $group->get_groups_by_user($uid,$cnt=TRUE);
          $links = $group->get_groups_by_user($uid,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'title','ASC');
          $links = $this->manage_links($links);
       }
       if ($sort_by == 'members') {
          $this->Paging["count"] = $group->get_groups_by_user($uid,$cnt=TRUE);
          $links = $group->get_groups_by_user($uid,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'members');
          $links = $this->manage_links($links);
          $sorted_array = objtoarray($links);
          sortByFunc($sorted_array, create_function('$sorted_array','return $sorted_array["members"];'), 'desc',1);
          $links = $sorted_array;
       }
       if ($sort_by == 'created') {
          $this->Paging["count"] = $group->get_groups_by_user($uid,$cnt=TRUE);
          $links = $group->get_groups_by_user($uid,$cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
          $links = $this->manage_links($links);
       }
     }  
     return $links;
  
  }
  
  function generate_inner_html () {
    $Pagination = new Pagination;
    $group = new group();
    $user_group_ids = array();
    if ( @$_SESSION['user']['id'] ) {
         $user_group_ids = $group->get_user_groups ( $_SESSION['user']['id'] );
         $user_group_ids = $this->get_user_group_id ( $user_group_ids );
    }
    $Pagination->setPaging($this->Paging);
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();
    $inner_template = NULL;

    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';   
    }

    $inner_html_gen = & new Template($inner_template);
    
    $this->links = objtoarray($this->links);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('total', $this->total);
    $inner_html_gen->set('search_str', get_groups_search_options());
    $inner_html_gen->set('user_group_ids', $user_group_ids);
		$inner_html_gen->set('current_theme_path', PA::$theme_url);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html = $inner_html_gen->fetch();

    return $inner_html;
  }
  
  
  function manage_links ( $links ) {
    $group = new group();
    if ( $links ) {
       foreach ( $links as $link_var ) {
          $link_var->members = $group->get_member_count ( $link_var->group_id );
       }
    }
    return $links;
  }

   function get_user_group_id ( $user_group_info ) {
    $user_group_id = array();
    if ( $user_group_info ) {
       for ( $i = 0;$i < count($user_group_info); $i++) {
          $user_group_id[$i] = $user_group_info[$i]['gid'];
       }
    }
    return $user_group_id;
  }
   
  /**
  * This function will filter out the invite only groups from the groups list
  * which are not meant to be shown in the group search.
  */
  public function filter_collections($collections) {
    $link = array();
    if (count($collections)) {
      foreach ($collections as $group_info) {
        $details = Group::load_group($group_info['id']);
        if ($details->reg_type != REG_INVITE) {
          array_push($link, $details);
        }
      }
    }
    return $link;
  }

}
?>  
