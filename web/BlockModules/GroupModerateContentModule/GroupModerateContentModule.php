<?php

require_once "api/Category/Category.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "ext/Group/Group.php";
require_once "web/includes/classes/Pagination.php";

class GroupModerateContentModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';

  public $outer_template = 'outer_public_center_module.tpl';
  public $max_height;
  public $uid, $content_data, $Paging;
  public $page_links, $page_prev, $page_next, $page_count;

  function __construct() {
    parent::__construct();
    $this->html_block_id = 'GroupModerateContentModule';
    $this->block_type = 'GroupModerateContent';
    $this->id = 0;
  }


  public function initializeModule($request_method, $request_data) {
    global $paging;
    if(!empty($this->shared_data['group_info'])) {
      $group = $this->shared_data['group_info'];
      $this->set_id = $group->collection_id;
    } else if(!empty($request_data['gid'])) {
      $this->set_id = $request_data['gid'];
    } else {
      return 'skip';
    }
    if (!empty($request_data['view'])) {
      $this->view = $request_data['view'];
    }
    $this->Paging = $paging;
  }


  function handleRequest($request_method, $request_data) {
/*
    if(!$this->shared_data['moderation_permissions']) {
      $msg = __("Sorry you are not authorised to moderate this group");
      $this->controller->redirect(PA::$url . PA_ROUTE_GROUP . "/gid=" . $request_data['gid'] . "&msg=$msg");
    }
*/
    if(!empty($request_data['action'])) {
      $action = $request_data['action'];
      $class_name = get_class($this);
      switch($request_method) {
        case 'POST':
          $method_name = 'handlePOST_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
          }
        break;
        case 'GET':
          $method_name = 'handleGET_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
          }
        break;
        case 'AJAX':
          $method_name = 'handleAJAX_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
          }
        break;
      }
    }
  }

  private function handlePOST_approveContent($request_data) {
  global $error_msg;
    // Code for Approving and Denying the Pending Content Moderations: Starts
    if(!empty($request_data["contentIdArray"]) && !empty($request_data["group_id"])) {
      $contentIdArray = array();
      $type = 'content';
      $Group = new Group();
      $Group->collection_id = $request_data["group_id"];
      $contentIdArray = $request_data["contentIdArray"];
      if(!empty($request_data["btn_approve_content"])) {
        for($counter = 0; $counter < count($contentIdArray); $counter++) {
            $Group->approve ($contentIdArray[$counter], $type);
            Content::update_content_status($contentIdArray[$counter], 1);
        }
        $error_msg = __("Content Approved");
      }
      if(!empty($request_data["btn_deny_content"])) {
        for($counter = 0; $counter < count($contentIdArray); $counter++) {
           $Group->disapprove ($contentIdArray[$counter], $type);
           Content::update_content_status($contentIdArray[$counter], 0);
        }
        $error_msg = __("Content Denied");
      }
    } else if(isset($request_data['btn_approve_content'])) {
      $error_msg = __('Please select a content for approval');
    } else if(isset($request_data['btn_deny_content'])) {
      $error_msg = __('Please select a content for denial');
    }
  }


  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function get_moderation_queue() {
      $Group = new Group();
      $Group->collection_id = $this->set_id;
      $Group->is_active = 1;

      $this->Paging["count"] = $Group->get_moderation_queue('content', $cnt=TRUE);

      $contentIdArray = $Group->get_moderation_queue('content', $cnt=FALSE,
      $this->Paging["show"], $this->Paging["page"]);

      for($counter = 0; $counter < count($contentIdArray); $counter++) {
        $cid = $contentIdArray[$counter];
        $content = Content::load_content((int)$cid, (int)$_SESSION['user']['id']);
        $this->content_data[] = array('content_id'=>$cid, 'author_id'=>$content->author_id, 'title'=>$content->title, 'created'=>date("M - d - Y",$content->created));
      }

  }

  function generate_inner_html () {
    switch ( $this->mode ) {
     default:
       $tmp_file = PA::$blockmodule_path . "/GroupModerateContentModule/center_inner_public.tpl";
    }

    $inner_html_gen = & new Template($tmp_file);

    $this->get_moderation_queue();
    $Pagination = new Pagination;

    $Pagination->setPaging($this->Paging);
    $this->page_prev = $Pagination->getPreviousPage();
    $this->page_next = $Pagination->getNextPage();
    $this->page_links = $Pagination->getPageLinks();

    $inner_html_gen->set('links', $this->content_data);
    $inner_html_gen->set('page_prev', $this->page_prev);
    $inner_html_gen->set('page_next', $this->page_next);
    $inner_html_gen->set('page_links', $this->page_links);

    $inner_html_gen->set('group_id', $this->set_id);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>