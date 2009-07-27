<?php

require_once "api/Comment/Comment.php";
require_once "api/Content/Content.php";
require_once "web/includes/functions/date_methods.php";

class RecentCommentsModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';

  public $cid;

  function __construct() {
    parent::__construct();
    $this->title = __('Recent Comments');
  }

  function initializeModule($request_method, $request_data) {
      switch ($this->page_id) {
        case PAGE_USER_PUBLIC:
          $this->title = abbreviate_text((ucfirst(PA::$page_user->display_name).'\'s '), 16, 10);
          $this->title .= __('Comments');
        break;
        case PAGE_USER_PRIVATE:
          $this->title = __("My Friend's Comments");
        break;
        default:
          $this->title = __('Recent Comments');
    }
  }

  function render() {
    $links = array();
    if($this->page_id == PAGE_USER_PUBLIC) {
      $links = Comment::get_comment_for_user(PA::$page_uid, 5);
    }
    else if($this->page_id == PAGE_USER_PRIVATE) {
     $relations_ids = Relation::get_all_relations((int)PA::$login_uid, 0, FALSE, 'ALL', 0, 'created', 'DESC', 'internal', APPROVED, PA::$network_info->network_id);
      $tmp_links = array();
      foreach($relations_ids as $relation) {
        $tmp_links[] = Comment::get_comment_for_user((int)$relation['user_id'], 5);
      }
      $cnt = 0;
      $links = array();
      $link_cnts = array();
      do {
         foreach($tmp_links as $idx => $rel_links) {
           if(empty($link_cnts[$idx])) $link_cnts[$idx] = 0;
           if(isset($rel_links[$link_cnts[$idx]])) {
             $links[] = $rel_links[$link_cnts[$idx]++];
             $cnt++;
           }
           if($cnt >= 5) break;
         }
      } while($cnt++ <= 5);
    }
    else {
      $links = Comment::get_comment_for_content(NULL, $count = 5, 'DESC',TRUE);
    }
    foreach($links as &$link) {
      if(!empty($link['content_id'])) {
        $post = Content::load_content((int)$link['content_id'], PA::$login_uid);
        $link['post_title'] = $post->title;
      } else {
        $link['post_title'] = __('No title');
      }
    }
    $this->inner_HTML = $this->generate_inner_html ($links);
    $content = parent::render();
    return $content;
  }

  function generate_inner_html ($links) {
    switch ( $this->mode ) {
      case PRI:
        $this->outer_template = 'outer_private_side_module.tpl';
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
    }
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('links', $links);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>