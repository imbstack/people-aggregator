<?php

require_once "web/includes/classes/Pagination.php";


class SearchByTag extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  public $cid , $tag_id, $Paging;
  public $page_links, $page_prev, $page_next, $page_count;

  function __construct() {
    $this->html_block_id = 'SearchByTag';
  }
  /**
  Get all the links of different group of given search String 
  **/
  
  private function get_links() {
    global $login_uid;
    
    $links = array();
    if ( @$this->name_string ) {
      $tag_var = new Tag();
      switch($this->name_string) {
        case 'group_tag':
          $this->Paging["count"] = $tag_var->get_associated_contentcollectionids($this->keyword, $cnt=TRUE);
          $tag_list = $tag_var->get_associated_contentcollectionids($this->keyword, $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
        
          $cnt = count($tag_list);
          if ($cnt > 0) {
            for($i = 0; $i < $cnt; $i++) {
              $link[$i] = Group::load_group($tag_list[$i]['id']);
            }
          $links['group_info'] = objtoarray($link);
          }
        break;
        case 'network_tag':
          // at present we are not using this
        break;
        case 'user_tag':
          $this->Paging["count"] = $tag_var->get_associated_userids($this->keyword, $cnt=TRUE);
          $tag_list = $tag_var->get_associated_userids($this->keyword, $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
          
          $cnt = count($tag_list);
          $link = array();
          if ($cnt > 0) {
            for($i = 0; $i < $cnt; $i++) {
              $usr = new User();
              $usr->load((int)$tag_list[$i]['id']);
              $link[$i] = $usr;
            }
          }
          $links['user_info'] = objtoarray($link);
          
        break;
        case 'content_tag':
          $this->Paging["count"] = $tag_var->get_associated_content_ids($this->keyword, $cnt=TRUE);
          $tag_list = $tag_var->get_associated_content_ids($this->keyword, $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
        
          $cnt = count($tag_list);
          $link = array();
          if ($cnt > 0) {
            for($i = 0; $i < $cnt; $i++) {
              $link[$i] = Content::load_content($tag_list[$i]['id'], $login_uid);
            }
          }
          $links['content_info'] = objtoarray($link);
          
        break;
      }
    
    }
     
     return $links;
  }
  
  function render() {
    $this->links = $this->get_links();
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    $Pagination = new Pagination;
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
    
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('search_str', get_tag_search_option());
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html = $inner_html_gen->fetch();

    return $inner_html;
  }

}
?>  