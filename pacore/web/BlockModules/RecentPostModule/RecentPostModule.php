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
require_once "api/Content/Content.php";

class RecentPostModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';

  public $gid;
  public $type;

  public function __construct() {
    parent::__construct();
    $this->title = __("Recent Posts");
    $this->limit = 10;
    $this->view_all_url = PA::$url . PA_ROUTE_HOME_PAGE; // default url
  }

  public function initializeModule($request_method, $request_data) {
    switch($this->page_id) {
      case PAGE_CREATE_FORUM_TOPIC:
      case PAGE_FORUM_HOME:
      case PAGE_GROUP:
      case PAGE_GROUP_MODERATION:
        if (empty($request_data['gid'])) return 'skip';
        $this->type = 'group';
        $this->gid = $request_data['gid'];
        $this->view_all_url = PA::$url . "/". FILE_SHOWCONTENT."?gid=$this->gid";
      break;
      case PAGE_PERMALINK:
        if ($collection = @$this->shared_data['collection']) {
          if ($collection->type == GROUP_COLLECTION_TYPE) {
            $this->type = 'group';
            $this->gid = $collection->collection_id;
            $this->view_all_url = PA::$url . "/". FILE_SHOWCONTENT."?gid=$this->gid";
          }
        } else {
          $this->type = 'permalink';
          $this->view_all_url = PA::$url . PA_ROUTE_HOME_PAGE;
        }
      break;
      case PAGE_INVITATION:
      case PAGE_HOMEPAGE:
        $this->type = 'homepage';
        $this->view_all_url = PA::$url . PA_ROUTE_HOME_PAGE;
      break;
      default:
        if(!empty($request_data['uid'])) {
          $this->uid = $request_data['uid'];
          $this->type = 'user';
          $this->view_all_url = PA::$url . PA_ROUTE_USER_PUBLIC . "/" . $this->uid;
        } else {
          $this->type = NULL;
        }
    }
  }

  public function render() {
    // group recent posts
    if ($this->type == 'group') {
      $group = new Group();
      $group->collection_id = $this->gid;
      $this->links =  $group->get_contents_for_collection('all', FALSE, $this->limit, 1, 'created', 'DESC', TRUE);
    // user recent posts
    } else if ($this->type == 'user') {
      $this->links = Content::get_user_content($this->uid);
    // network recent posts
    } else {
      $this->links = Content::load_content_id_array($user_id = 0, $type=NULL, $cnt=FALSE, $show=$this->limit, $page=1, $sort_by='created', $direction='DESC', $only_homepage = false);
    }
    $this->view_all_url = (count($this->links) > 0) ? $this->view_all_url : '';
    $this->inner_HTML = $this->generate_inner_html ($this->links);
    $content = parent::render();
    return $content;
  }

  public function generate_inner_html ($links) {
    $inner_template = NULL;
    switch ($this->mode) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    $obj_inner_template = & new Template($inner_template);
    $obj_inner_template->set('links', $links);
    $obj_inner_template->set('gid', $this->gid);
    $obj_inner_template->set('limit', $this->limit);
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }
}
?>
