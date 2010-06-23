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

require_once "api/Invitation/Invitation.php";

class InvitationStatusModule extends Module {
  public $module_type = 'group|network';
  public $module_placement = 'middle';
  public $collection_id_array;
  public $outer_template = 'outer_public_center_module.tpl';
  public $accepted_invitation;
  public $pending_invitation;

  public function __construct() {
    parent::__construct();
    $this->title = __("Invitation Status");
  }

  public function initializeModule($request_method, $request_data) {
    if (empty(PA::$login_uid)) {
      return 'skip';
    } else if ($this->page_id == PAGE_GROUP_INVITE) {
      $groups = Group::get_user_groups(PA::$login_uid, FALSE, 'ALL');
      $user_groups = array();
      $groups_count = count($groups);
      for ($i = 0; $i < $groups_count; $i++) {
        $user_groups[] = $groups[$i]['gid'];
      }
      $user_groups = array();
      if (!empty($request_data['gid'])) {
        $this->collection_id_array = array($request_data['gid']);
      } else if(!empty($user_groups)) {
        $this->collection_id_array = array(current($user_groups));
      }
    }
  }
  
  public function render() {
    // invite status
    // if we have array of collection ids then find the invitation of each collection (group)
    if (is_array($this->collection_id_array) && count($this->collection_id_array) > 0) {
      $accepted = Invitation::get_accepted_invitations(PA::$login_uid,'-1',$this->collection_id_array);
      $pending = Invitation::get_pending_invitations(PA::$login_uid,'-1',$this->collection_id_array);
    } else {
      $accepted = Invitation::get_accepted_invitations(PA::$login_uid);
      $pending = Invitation::get_pending_invitations(PA::$login_uid);
    }
    $accepted_invitation = array();
    if (!empty($accepted)) {
      $i = 0;
      foreach ($accepted as $ac) {
        $inv_user = new User();
        $inv_user->load((int)$ac['inv_user_id']);
        $accepted_invitation[$i]['user_name'] = $inv_user->login_name;
        $accepted_invitation[$i]['first_name'] = $inv_user->first_name;
        $accepted_invitation[$i]['last_name'] = $inv_user->last_name;
        $accepted_invitation[$i]['picture'] = $inv_user->picture;
        $accepted_invitation[$i]['user_id'] = $inv_user->user_id;
        $i++;
      }
    }
    $pending_invitation = array(); 
    if (!empty($pending)) {
      $i = 0;
      foreach ($pending as $pe) {
        $pending_invitation[$i]['user_email'] = $pe['inv_user_email'];
        $i++;
      }
    }

    $this->accepted_invitation = $accepted_invitation;
    $this->pending_invitation = $pending_invitation;

    $this->inner_HTML = $this->generate_inner_html ();    
    $content = parent::render();
    return $content;
  }

  public function generate_inner_html () {
    switch ($this->mode) {
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . "/side_inner_public.tpl";
    }
    $invite_status = & new Template($tmp_file);
    $invite_status->set('accepted_invitation', $this->accepted_invitation);
    $invite_status->set('pending_invitation', $this->pending_invitation);
    $inner_html = $invite_status->fetch();
    return $inner_html;
  }

}
?>