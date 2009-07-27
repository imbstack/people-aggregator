<?php

require_once "api/Category/Category.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "ext/Group/Group.php";
require_once "web/includes/classes/Pagination.php";

class GroupModerateUserModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  public $max_height;
  public $uid, $members_data, $Paging, $links;
  public $page_links, $page_prev, $page_next, $page_count;

  function __construct() {
    parent::__construct();
    $this->html_block_id = 'GroupModerateUserModule';
    $this->block_type = 'GroupModerateUser';
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

  private function handlePOST_approveUser($request_data) {
  global $error_msg;
    // Code for Approving and Denying the Pending Moderations: Starts
    if(!empty($request_data["selectedArray"]) && !empty($request_data["group_id"])) {
      $selectedArray = array();
      $type = 'user';
      $Group = new Group();
      $Group->collection_id = $request_data["group_id"];
      $selectedArray = $request_data["selectedArray"];
      if(!empty($request_data["btn_approve"])) {
        for($counter = 0; $counter < count($selectedArray); $counter++) {
           $Group->approve ($selectedArray[$counter], $type);
           $this->send_approval_message_to_user($selectedArray[$counter], $request_data["group_id"], true);  // "group join approved" message!
        }
        $error_msg = __("User Approved");
      }

      if(!empty($request_data["btn_deny"])) {
        for($counter = 0; $counter < count($selectedArray); $counter++) {
           $Group->disapprove ($selectedArray[$counter], $type);
           $this->send_approval_message_to_user($selectedArray[$counter], $request_data["group_id"], false);  // "group join not approved" message!
        }
        $error_msg = __("User Denied");
      }
    } else if(isset($request_data['btn_approve']))  {
      $error_msg = __("Please select a user for approval");
    } else if(isset($request_data['btn_deny']))  {
      $error_msg = __("Please select a user for denial");
    }
  }

 private function send_approval_message_to_user($uid, $gid, $approved) {
 global $network_info;

    $site_name = PA::$site_name;
    $user = new User();
    $user->load((int)$uid);
    $group = Group::load_group_by_id((int)$gid);
    $group_owner_id = Group::get_owner_id((int)$gid);
    $group_owner = new User();
    $group_owner->load((int)$group_owner_id['user_id']);

    $group_name = $group->title;
    $network_name = $network_info->name;
    $group_member_count = Group::get_member_count((int)$gid);
    $group_owner_name = $group_owner->login_name;
    $group_joinee = $user->login_name;
    $group_url = '<a href="' . PA::$url . PA_ROUTE_GROUP . '/gid='.$gid . '">' . $group->title . '</a>' ;
    $approved_msg = ($approved) ? 'has approved' : 'has not approved';

    $subject = "$group_owner_name $approved_msg your request to join the \"$group_name\" Group";
    $msg ="
          <br />Dear $group_joinee,
          <br />
          <br />
          <b>$group_owner_name</b> $approved_msg your request to join the \"$group_name\" Group on the \"$network_name\" network.
          <br />
          To view the \"$group_name\" Group, click on the following link: $group_url
          <br />
          There are now $group_member_count members in the \"$group_name\" Group.
          <br />
          Thanks,
          The $site_name Team
          <br />
          <p>
          Everyone at $site_name respects your privacy. Your information will
          never be shared with third parties unless specifically requested by you.
          <p/>";

    Message::add_message((int)$group_owner_id['user_id'], null, $group_joinee, $subject, $msg);
    simple_pa_mail($user->email, $subject, $msg);
    return;
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
      $this->Paging["count"] = $Group->get_moderation_queue('user', $cnt=TRUE);
      $members = $Group->get_moderation_queue('user', $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
      $User = new User();
      foreach($members as $membersDetails) {

          $User->load((int)$membersDetails);
          $this->members_data[] = array('user_id'=>$User->user_id, 'first_name'=>$User->first_name, 'last_name'=>$User->last_name, 'email'=>$User->email, 'picture'=>$User->picture,'login_name' =>$User->login_name);
      }

  }

  function generate_inner_html () {
    switch ( $this->mode ) {
     default:
        $tmp_file = PA::$blockmodule_path . "/GroupModerateUserModule/center_inner_public.tpl";
    }

    $inner_html_gen = & new Template($tmp_file);
    $this->get_moderation_queue();
    $Pagination = new Pagination;

    $Pagination->setPaging($this->Paging);
    $this->page_prev = $Pagination->getPreviousPage();
    $this->page_next = $Pagination->getNextPage();
    $this->page_links = $Pagination->getPageLinks();

    $inner_html_gen->set('links', $this->members_data);
    $inner_html_gen->set('page_prev', $this->page_prev);
    $inner_html_gen->set('page_next', $this->page_next);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html_gen->set('group_id', $this->set_id);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>