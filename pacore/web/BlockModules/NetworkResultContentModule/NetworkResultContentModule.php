<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
require_once "web/includes/classes/Pagination.php";

class NetworkResultContentModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';

  public $outer_template = 'outer_public_group_center_module.tpl';
  public $keyword, $month;

  function __construct() {
    parent::__construct();
    $this->main_block_id = "mod_network_content_result";
    $this->title = __('Manage Content');
  }

  private function get_links() {
    $network = new Network();
    $condition = array('keyword'=>$this->keyword, 'month'=>$this->month);

    $this->Paging["count"] = Content::load_all_content_id_array ($cnt=TRUE,'ALL',0,'created','DESC', $condition);

    $contents = Content::load_all_content_id_array ($cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'created', 'DESC', $condition);
    $contents_link = $this->manage_content($contents);
    $this->links = $contents_link;
    return $this->links;
  }

   function render() {

    $this->links = $this->get_links();
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

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
    $inner_html_gen = new Template($inner_template);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html_gen->set('config_navigation_url',
                      network_config_navigation('manage_content'));
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

  // For manage Content listing . adding some links such as edit delete and hyperlink
  function manage_content ($contents_list) {
    global $login_uid;
    $i=0;
    if (empty($contents_list)) {
      $contents_list = array();
    }
    $new_content = array();
    foreach ($contents_list as $contt) {
      $new_content[$i]['content_id'] = $contt['content_id'];
      $new_content[$i]['title'] = $contt['title'];
      $new_content[$i]['body'] = $contt['body'];
      $new_content[$i]['author_id'] = $contt['author_id'];
      $new_content[$i]['type'] = $contt['type'];
      $new_content[$i]['changed'] = $contt['changed'];
      $new_content[$i]['created'] = $contt['created'];
      $new_content[$i]['type_name'] = $contt['type_name'];
      $new_content[$i]['comment_count'] = $contt['comment_count'];
      $new_content[$i]['author_name'] = $contt['author_name'];
      $new_content[$i]['content_type_id'] = $contt['content_type_id'];
      $author = new User();
      $new_content[$i]['author_home_url'] = $author->url_from_id((int)$contt['author_id']);
      if (!empty($contt['parent_info'])) {
           $type = ($contt['parent_info']['type'] == ALBUM_COLLECTION_TYPE) ? 'Album': 'Group';
           $new_content[$i]['parent_name'] = $contt['parent_info']['title'].'('.$type.')';
      }
      // Route media and normal content through to the correct display/editing pages
      if (!in_array($contt['content_type_id'], array(IMAGE, AUDIO, VIDEO))) {
        $link_for_editing = PA::$url ."/post_content.php?cid=".$new_content[$i]['content_id'];
        $image_hyperlink = PA::$url . PA_ROUTE_CONTENT . "/cid=".$new_content[$i]['content_id'];
      }
      else {
        $link_for_editing = PA::$url ."/edit_media.php?cid=".$new_content[$i]['content_id']."&amp;type=".$new_content[$i]['type'];
        $image_hyperlink = PA::$url ."/media_full_view.php?gid&cid=".$new_content[$i]['content_id'];
      }
      $new_content[$i]['edit_link'] = $link_for_editing;
      $new_content[$i]['hyper_link'] = $image_hyperlink;
      $new_content[$i]['abuses'] = total_abuse($contt['content_id'], TYPE_CONTENT);
      $i++;
    }
    return $new_content;
  }
}
?>

