<?php

//require_once "api/Activities/ActivityType.class.php";
require_once "api/Activities/Activities.php";
require_once "api/Messaging/MessageDispatcher.class.php";

class GroupActionsModule {

  public $module_type = 'action_handler';
  public $module_placement = '';

  public $do_skip = false;

  function __construct() {
    $this->block_type = 'GroupActions';
    $this->mode = PRI;
  }

  function handleRequest($request_method, $request_data) {
    if(!empty($request_data['action']) && empty($request_data['module'])) { // when target module not defined!
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

  public function initializeModule($request_method, $request_data) {
  }

  private function handleGET_update($request_data) {
    global $error_msg;
    if (PA::$login_uid && !empty($this->shared_data['group_info'])) {
      $group = $this->shared_data['group_info'];
      if (Group::member_exists((int)$request_data['gid'], (int)PA::$login_uid)) {
        // deal with TypedGroup Relations
        if (!empty(PA::$config->useTypedGroups)) {
            require_once("api/Entity/TypedGroupEntityRelation.php");
            $uid = PA::$login_uid;
            $gid = $group->collection_id;
            $type = @$request_data['relation'];
            try {
                TypedGroupEntityRelation::set_relation($uid, $gid, $type);
                $error_msg = sprintf(__("You have updated your relation to \"%s\" successfully."), stripslashes($group->title));
            } catch (PAException $e) {
                $error_msg = $e->getMessage();
            }
        }
      }
        }
    }

  private function handleGET_join($request_data) {
    global $error_msg;
    if (PA::$login_uid && !empty($this->shared_data['group_info'])) {
      $group = $this->shared_data['group_info'];
      if (!Group::member_exists((int)$request_data['gid'], (int)PA::$login_uid)) {
        $user  = PA::$login_user;
        $login_name = $user->login_name;
        $group_invitation_id = (!empty($request_data['GInvID'])) ? $request_data['GInvID'] : null;
        try {
          $user_joined = $group->join((int)PA::$login_uid, $_SESSION['user']['email'],$group_invitation_id);
          // for rivers of people
          $activity = 'group_joined';//for rivers of people
          $activity_extra['info'] = ($login_name.' joined a new group');
          $activity_extra['group_name'] = $group->title;
          $activity_extra['group_id'] = $request_data['gid'];
          $extra = serialize($activity_extra);
          $object = $request_data['gid'];
          Activities::save(PA::$login_uid, $activity, $object, $extra);
          if (!empty($group_invitation_id)) { // if group is joined through group invitation
            $Ginv = Invitation::load($group_invitation_id);
            $gid = $Ginv->inv_collection_id;
            $user_obj = new User();
            $user_obj->load((int)$Ginv->user_id);
            $group = ContentCollection::load_collection((int)$gid, $Ginv->user_id);
            $user_type = Group::get_user_type($user_obj->user_id, $gid);
            if($group->reg_type == REG_MODERATED && $user_type == OWNER) {
              $group->collection_id = $gid;
              $group->approve(PA::$login_uid, 'user');
            }
            $user_accepting_ginv_obj = new User();
            $user_accepting_ginv_obj->load((int)PA::$login_uid);
            $Ginv->inv_user_id = PA::$login_uid;
            PANotify::send("invite_accept_group", $user_obj, $user_accepting_ginv_obj, $Ginv);
          }
        } catch (PAException $e) {
          if ($e->code == GROUP_NOT_INVITED) {
            $error_msg = $e->message;
//          header("Location: groups_home.php");
//          exit;
          }
          $error_msg = $e->message;
        }
      } else {
        $error_msg = sprintf(__("You are already a member of \"%s\""), stripslashes($group->title));
      }

      if (@$user_joined) {
        // deal with TypedGroup Relations
        if (!empty(PA::$config->useTypedGroups)) {
            require_once("api/Entity/TypedGroupEntityRelation.php");
            $uid = PA::$login_uid;
            $gid = $group->collection_id;
            $type = @$request_data['relation'];
            try {
                TypedGroupEntityRelation::set_relation($uid, $gid, $type);
            } catch (PAException $e) {
                $error_msg = $e->getMessage();
            }
        }

        $gid = (int)$request_data['gid'];
        if(!(Group::member_exists((int)$request_data['gid'], (int)PA::$login_uid)) && $group->reg_type == REG_MODERATED) { // if it is a manual join not an invited join
          $mail_type = 'group_join_request';
          $error_msg = sprintf(__("Your request to join \"%s\" has been submitted to the owner of the group."), stripslashes($group->title));
        } else {
          $mail_type = 'group_join';
          $error_msg = sprintf(__("You have joined \"%s\" successfully."), stripslashes($group->title));
        }
        PANotify::send($mail_type, $group, PA::$login_user, array());
      }
    } else {
        // redirect to login
        $msg = urlencode(__("You need to be logged in to join a group."));
        header("Location: ". PA::$url ."/login.php?".$msg."&return=".urlencode($_SERVER['REDIRECT_URL']
          .'?'. @$_SERVER['REDIRECT_QUERY_STRING']));

    }
  }


  private function handleGET_leave($request_data) {
    global $error_msg;
    if(PA::$login_uid && !empty($this->shared_data['group_info'])) {
      $group = $this->shared_data['group_info'];
      $user  = PA::$login_user;
      $user_type = Group::get_user_type(PA::$login_uid, (int)$request_data['gid']);
      if((Group::is_admin((int)$request_data['gid'], (int)PA::$login_uid)) && ($user_type == OWNER)) { // admin can leave a group but owner can't
        $error_msg = __("You can't leave your own group.");
      } else if (Group::member_exists((int)$request_data['gid'], (int)PA::$login_uid)) {
        try {
          $x = $group->leave((int)PA::$login_uid);
        } catch (PAException $e) {
          $error_msg = "Operation failed (".$e->message."). Please try again";
        }
      } else {
        $error_msg = sprintf(__("You are not member of \"%s\"."), stripslashes($group->title));
      }
      if (!empty(PA::$config->useTypedGroups)) {
        require_once 'api/Entity/TypedGroupEntityRelation.php';
        TypedGroupEntityRelation::delete_relation(PA::$login_uid, $request_data['gid'], PA::$network_info->network_id);
      }
      if(@$x) {
        $error_msg = sprintf(__("You have left \"%s\" successfully."), stripslashes($group->title));
      }
    }
  }

  private function handleGET_delete($request_data) {
    global $error_msg;
    if(PA::$login_uid && !empty($this->shared_data['group_info']) && ((@$_POST['content_type'] != 'media'))) {
      $group = $this->shared_data['group_info'];
      $user  = PA::$login_user;
      if(Group::is_admin((int)$request_data['gid'], (int)PA::$login_uid)) {
        $group->delete();
        // Deleting all the activities of this group from activities table for rivers of people module
        Activities::delete_for_group($request_data['gid']);

        if (!empty(PA::$config->useTypedGroups)) {
            require_once 'api/Entity/TypedGroupEntity.php';
            require_once("api/Entity/TypedGroupEntityRelation.php");
            TypedGroupEntityRelation::delete_all_relations($request_data['gid']);
            TypedGroupEntity::delete_for_group($request_data['gid']);
        }

        $this->controller->redirect(PA::$url . PA_ROUTE_GROUPS . "?error_msg=" . __("Group sucessfully deleted."));
      }
    }
  }


  function render() {
  }
}

?>