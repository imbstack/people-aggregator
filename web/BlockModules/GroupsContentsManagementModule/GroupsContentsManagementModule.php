<?php
require_once "web/includes/classes/Pagination.php";

class GroupsContentsManagementModule extends Module {
  
  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  public $keyword, $month;
  
  function __construct() {
    $this->main_block_id = "mod_network_content_result";
    $this->title = sprintf(__('Manage %s Contents'), PA::$group_noun);
  }

  private function get_links() {
    global $login_uid;
    $this->Paging["count"] = Group::get_all_content_for_collection($this->group_id, true);
    
    $group_data = Group::get_all_content_for_collection($this->group_id, $count=FALSE, $this->Paging["show"], $this->Paging["page"]);
    $cnt = count($group_data);

    for ( $i=0; $i < $cnt; $i++) {
      if (!in_array($group_data[$i]->type, array(IMAGE))) {
        $link_for_editing = PA::$url ."/post_content.php?cid=".$group_data[$i]->content_id;
        $image_hyperlink = PA::$url . PA_ROUTE_CONTENT . "/cid=" . $group_data[$i]->content_id;
        $delete_link = PA::$url . PA_ROUTE_CONTENT . "?action=deleteContent&cid=" . $group_data[$i]->content_id;
      }
      else {
        $link_for_editing = PA::$url ."/edit_media.php?uid=".$login_uid."&amp;cid=".$group_data[$i]->content_id."&amp;type=image";
        $image_hyperlink = PA::$url ."/media_full_view.php?gid&cid=".$group_data[$i]->content_id;
        $delete_link = PA::$url . PA_ROUTE_CONTENT . "/?action=deleteContent&cid=" . $group_data[$i]->content_id;
      }
      
      $group_data[$i]->edit_link = $link_for_editing;
      $group_data[$i]->hyper_link = $image_hyperlink;
      $group_data[$i]->delete_link = $delete_link;
    }
   
    return $group_data;
  }
      
   function render() {
    if ($this->type == 'forum') {
      $this->links = $this->get_forum_links();
    } else {
      $this->links = $this->get_links();
    }
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    global $app;
    
    switch ( $this->type ) {
      case 'forum':
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/forum_inner_template.tpl';
      break;
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
    $inner_html_gen->set('back_page', PA::$url . $app->current_route);
    $inner_html_gen->set('type', $this->type);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
  
  function get_forum_links () {
    global $paging;
    $request_info = load_info();
    $this->parent_id = $request_info['parent_id'];
    $this->parent_type = $request_info['parent_type'];
    $this->parent_name_hidden = $request_info['parent_name_hidden'];
    $this->header_title = $request_info['header_title'];
    $thread_obj = new MessageBoard();
    $thread_obj->set_parent($this->parent_id,$this->parent_type);
    $this->Paging["count"] = $thread_obj->get($count = TRUE);
    $forum_details = $thread_obj->get($count=FALSE, $this->Paging["show"], $this->Paging["page"]); 
    $cnt = count($forum_details);
    if ($cnt > 0 ) {
      for ($i = 1; $i <= $cnt; $i++) {
        $forum_details[$i]['hyper_link'] = PA::$url .'/forum_messages.php?mid='.$forum_details[$i]['boardmessage_id'].'&amp;ccid='.$_GET['gid'];
         $forum_details[$i]['edit_link'] = PA::$url .'/edit_forum.php?mid='.$forum_details[$i]['boardmessage_id'].'&amp;gid='.$_GET['gid'];
         $forum_details[$i]['delete_link'] = PA::$url .'/deleteforumbyadmin.php?mid='.$forum_details[$i]['boardmessage_id'].'&amp;ccid='.$_GET['gid'].'" onclick="javascript:return confirm(\'Are you sure you want to delete this forum ? \');"';
      }
    }
    return ($forum_details);
  }
  
}

?>