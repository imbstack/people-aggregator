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

require_once "api/Category/Category.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "ext/Group/Group.php";
require_once "web/includes/classes/Pagination.php";

class GroupModerationModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  public $max_height,$view;
  public $uid, $members_data, $Paging;
  public $page_links, $page_prev, $page_next, $page_count;
  public $set_id;

  public function __construct() {
    parent::__construct();
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


  public function handlePOST_deleteMembers($request_data) {
  global $error_msg;
    if (!empty($request_data["members"]) && !empty($request_data["group_id"])) {
      $membersArr = array();
      $membersArr = $request_data["members"];
      $Group = new Group();
      $Group->collection_id = $request_data["group_id"];
      $membersArr_count = count($membersArr);
      for($counter = 0; $counter < $membersArr_count; $counter++) {
        $Group->leave((int)$membersArr[$counter]);
      }
      $error_msg = __("Member(s) Deleted");
    } else {
      $error_msg = __('Please select a member');
    }
  }


  public function handlePOST_changeStatus($request_data) {
  global $error_msg;
//echo "<pre>".print_r($request_data,1)."</pre>";

    if(Group::set_user_type($request_data['user_id'], $request_data['group_id'], $request_data['user_status'])) {
      $error_msg = __('User status sucessfuly changed.');
    } else {
      $error_msg = __("Can't change status for this user.");
    }
  }

  /**
    Get data for moderation option ie group moderation
   **/

  private function get_links() {
    $this->Paging["querystring"] = "view=members&gid=$this->set_id";
    $Group = new Group();
    $Group->collection_id = $this->set_id;
    $Group->is_active = 1;
    $this->Paging["count"] = $Group->get_members($cnt=TRUE, '', '', '', '',FALSE);
    $members = $Group->get_members($cnt=FALSE, $this->Paging["show"], $this->Paging["page"], '', '',FALSE);
    $User = new User();
    foreach($members as $membersDetails) {
      if($membersDetails["user_type"] != 'owner') {
        $User->load((int)$membersDetails["user_id"]);
        $this->members_data[] = array('user_id'=>$membersDetails["user_id"], 'first_name'=>$User->first_name, 'last_name'=>$User->last_name, 'email'=>$User->email, 'created'=>$membersDetails['join_date'], 'picture'=>$User->picture, 'user_type'=>$membersDetails["user_type"],'login_name'=>$User->login_name);
      }
    }
//    echo "<pre>".print_r($this->members_data,1)."</pre>";
    return;
  }

  public function render() {
    $this->get_links();
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  public function generate_inner_html () {
    switch ($this->mode) {
     default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $inner_html_gen = & new Template($tmp_file);

    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);

    $this->page_prev = $Pagination->getPreviousPage();
    $this->page_next = $Pagination->getNextPage();
    $this->page_links = $Pagination->getPageLinks();

    $inner_html_gen->set('links', $this->members_data);
    $inner_html_gen->set('page_prev', $this->page_prev);
    $inner_html_gen->set('page_next', $this->page_next);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('group_id', $this->set_id);
    $inner_html_gen->set('div_visible_for_moderation', $this->view);

    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>