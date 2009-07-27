<?php
require_once "web/includes/classes/Pagination.php";

class VideosViewAllModule extends Module {

  public $outer_template = 'outer_public_center_module.tpl';
  public $links = array();
  public $item_id;

  function __construct() {
    parent::__construct();
    $this->main_block_id = "VideosViewAllModule";
  }
   //render the contents of the page
   function render() {
    global $paging;
    $this->Paging["page"] = $paging["page"];
    $this->Paging["show"] = 40;

    $this->inner_HTML = $this->generate_inner_html();
    return parent::render();
  }

  //inner html of the module generation
  function generate_inner_html() {
    switch ($this->mode) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);    
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('show_view', $this->show_view);
    $inner_html_gen->set('item_id', $this->item_id);

    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>