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
require_once "ext/Group/Group.php";

class ManageGroupsModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';

  /**
  * Parameter on which sorting of the group listing is requested.
  */
  public $sort_by;

  /**
  * Direction of the sorting, by default it will be ascending;
  */
  public $sort_dir;

  /**
  * Group search string. The given string will be looked for in the title and description of the group.
  */
  public $search_str;

  /**
  * Public setter function for setting the value of sort_by
  */
  public function set_sort_by($sort_by) {
    $allowed_options = array('created', 'name', 'category');
    if (in_array($sort_by, $allowed_options)) {
      $this->sort_by = $sort_by;
    } else {
      $this->sort_by = NULL;
    }
  }

  /**
  * Public setter function for setting the value of sort direction
  */
  public function set_sort_dir($sort_dir) {
    if ($sort_dir == 'ASC' || $sort_dir == 'DESC') {
      $this->sort_dir = $sort_dir;
    } else {
      $this->sort_dir = 'DESC';
    }
  }

  function __construct() {
    parent::__construct();
    $this->main_block_id = "ManageGroupsModule";
    $this->title = __('Manage Groups');
    $this->sort_dir = 'DESC';
    $this->search_str = NULL;
  }
   //render the contents of the page
   function render() {
    global $paging;
    $this->Paging["page"] = $paging["page"];
    $this->Paging["show"] = $paging["show"];

    $this->Paging["count"] = Group::get_all($this->search_str, 'all', TRUE);
    $groups = array();
    if (!empty($this->sort_by)) {
      switch ($this->sort_by) {
        case 'created':
          $sort_by = 'CC.created';
        break;
        case 'name':
          $sort_by = 'CC.title';
        break;
        case 'category':
          $sort_by = 'C.name';
        break;
      }
      $groups = Group::get_all ($this->search_str,'all', FALSE,$this->Paging["show"],$this->Paging["page"], $sort_by, $this->sort_dir);
    } else {
      $groups = Group::get_all ($this->search_str,'all', FALSE,$this->Paging["show"],$this->Paging["page"], 'created','DESC');
    }
    $this->links = $groups;
    $this->inner_HTML = $this->generate_inner_html();
    return parent::render();
  }
  //inner html of the module generation
  function generate_inner_html() {
    switch ($this->mode) {
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
    $inner_html_gen->set('search_str', $this->search_str);
    $inner_html_gen->set('config_navigation_url', network_config_navigation('manage_groups'));
    $inner_html_gen->set('sort_dir', $this->sort_dir);
    $inner_html_gen->set('sort_by', $this->sort_by);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>