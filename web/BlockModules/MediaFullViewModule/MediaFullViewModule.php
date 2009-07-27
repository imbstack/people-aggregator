<?php
require_once "api/ImageResize/ImageResize.php";
require_once "api/Comment/Comment.php";
require_once "web/includes/classes/Pagination.php";

class MediaFullViewModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_wide_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->html_block_id = "MediaFullViewModule";
    $this->outer_class_name = get_class_name(PAGE_MEDIA_FULL_VIEW);
  }
   
 function render() {
   global $error_msg;
    if(empty($this->media_data)) {
      $error_msg =  __("No such content or media file has been deleted from the gallery.");
      return;
    }

    $this->inner_HTML = $this->generate_inner_html ();
    
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
   
    $links = $this->media_data;
    // Here we get all the comment of that content 
    $comment = new Comment();
    
    $comment->parent_id = $links->content_id;
    $comment->parent_type = TYPE_CONTENT;
    
    $this->Paging["count"] = $comment->get_multiples_comment($cnt = TRUE);
    
    $result = $comment->get_multiples_comment($cnt = FALSE, $this->Paging["show"], $this->Paging["page"]);
    
    $this->comments = $result;
    $param = $this->handle_field_param($links);

    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();
    
    switch ( $this->mode ) {
     default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';   
    }
    
    
    $info = & new Template($inner_template);
    $info->set_object('param', $param);
    $info->set_object('uid', $this->uid);
    $info->set_object('links', $links);
    $info->set_object('comments', $this->comments);
    $info->set('back', @ $_SERVER['HTTP_REFERER']);
    $info->set('page_first', $this->page_first);
    $info->set('page_last', $this->page_last);
    $info->set('page_links', $this->page_links);
    // when we show Group media - check is user still Group member
    if(isset($_GET['gid'])) {             
      $is_author_member = $this->is_author_group_member($links->author_id, $_GET['gid']);
      $info->set('is_author_member', $is_author_member);
    }
    
    $inner_html = $info->fetch();
    return $inner_html;
  }

  function is_author_group_member($author_id, $gid) {
    $author_perm = Group::get_user_type($author_id, (int)$gid);
    return ($author_perm == NOT_A_MEMBER) ? false : true;
  }
  
  function handle_field_param($data) {
    global $login_uid, $page_uid, $network_info;
    if(isset($login_uid)) {
      $Image_owner = ($login_uid==$data->author_id) ? TRUE: FALSE;
      $relations_ids = Relation::get_all_relations((int)$data->author_id);
      $user_in_relation = array();
      foreach($relations_ids as $ids) {
        $user_in_relation['friends_id'][] = $ids['user_id'];/*
         if(!empty($ids['in_family']))
         $user_in_relation['in_family'][] = $ids['in_family'];*/
      }
    }
    // No one can view the media
    $param = FALSE;
    $network_owner = $network_info->owner_id;
    switch ($data->file_perm) {
      case NONE:
        if ((isset($login_uid) && ($Image_owner)) || ($login_uid == SUPER_USER_ID) || ($login_uid == $network_owner)) {
          $param = TRUE;
        }
      break;
      case ANYONE:
        $param = TRUE;
      break;
      case WITH_IN_DEGREE_1: 
        if (isset($login_uid) && ((in_array($login_uid, $user_in_relation['friends_id'])) || ($Image_owner) || ($login_uid == SUPER_USER_ID) || ($login_uid == $network_owner))) {
          $param = TRUE;
        }
      break;/*
     case IN_FAMILY:
         if (isset($login_uid) &&(is_array($user_in_relation)) && (in_array($login_uid,$user_in_relation['in_family']) || ($Image_owner)) ) {
           $param = TRUE;
         }
      break;*/
    
    } 
    // When user wants to see the Group image
   if(isset($login_uid) && isset($_GET['gid'])) {
       $is_author_member = $this->is_author_group_member($login_uid, $_GET['gid']); // any group member can view group gallery!
       if($is_author_member) $param = TRUE;
    }
    return $param;
  }

}
?>