<?php
require_once "api/Tasks/Tasks.php";
require_once "api/Content/Content.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/PAException/PAException.php";
require_once 'api/Entity/FamilyTypedGroupEntity.php';


/**
 * @class PermissionsHandler
 *
 * The PermissionsHandler class implements the basics methods for handling
 * static and dynamic user permissions
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.0.1
 *
 *
 */
class PermissionsHandler
{
  public  $uid;
  public  $user_permissions;
  private $tasks;
  private $static_permissions;
  private $is_net_admin;

  /**
  /  There should be placed names and pointers to permission handler
  /  functions for all the permissions that can not be (or are not)
  /  statically defined, ie, for those which must be calculated dynamically.
  /
  /  Format:
  /
  /  'permission_name' => 'handler_function_name'
  /
  /  Note: You can also change or expand the way on which the already
  /  existing static permissions will be treated.
  /  (see for example 'edit_content')
  **/
  private $dynamic_permissions = array(
            'edit_content'                 => 'can_edit_content',
            'delete_content'               => 'can_edit_content',
            'delete_comment'               => 'can_delete_comment',
            'view_content'                 => 'can_view_content',
            'manage_forum'                 => 'can_manage_forum',
//            'delete_rep'                   => '',
            'view_abuse_report_form'       => 'can_view_abuse_report_form',
            'delete_comment_authorization' => 'can_delete_comment_authorization',
            'post_to_community'            => 'can_post_to_community',
          );

  public function __construct($user_id) {
    if(!isset($user_id)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "PermissionsHandler::__construct() must be called with User object or user_id parameter");
    }

    $tasks_obj   = Tasks::get_instance();
    $this->tasks = $tasks_obj->get_tasks();
    foreach($this->tasks as $task) {
      $this->static_permissions[] = $task->task_value;
    }
    $this->uid = (int)$user_id;
    $this->is_net_admin = Network::is_admin(PA::$network_info->network_id, $this->uid);
    $roles = Roles::get_user_roles((int)$user_id, DB_FETCHMODE_OBJECT);
//    echo "User Roles <pre>".print_r(PA::$login_user, 1). "</pre>";
    $this->user_permissions = array();
    $user_perms = array();
    $network_perms = array();
    $groups_perms = array();
    foreach(array('user', 'network', 'groups') as $type) {
      foreach($roles as $role) {
        $role_extra = unserialize($role->extra);
        if($type == 'user') {
          $condition = ($role_extra['user'] == true);
        } else if ($type == 'network') {
          $condition = ($role_extra['network'] == true);
        } else {
          $condition = (count($role_extra['groups']) > 0);
        }
        if($condition) {
          $role_tasks = Roles::get_tasks_of_role($role->role_id);
 //         echo "RoleID: $role->role_id<pre>".print_r($role_tasks,1)."</pre>";
          if($role_tasks) {
            foreach($role_tasks as $rt) {
              if($type == 'user') {
                $user_perms[] = $rt->task_value;
              } else if($type == 'network') {
                $network_perms[] = $rt->task_value;
              } else {
                foreach($role_extra['groups'] as $group_id) {
                  if(isset($groups_perms[$group_id]) && is_array($groups_perms[$group_id])) {
                    array_push($groups_perms[$group_id], $rt->task_value);
                  } else {
                    $groups_perms[$group_id] = array($rt->task_value);
                  }
                }
              }
            }
          }
        }
      }
    }
    $this->user_permissions['user'] = $user_perms;
    $this->user_permissions['network'] = $network_perms;
    $this->user_permissions['groups'] = $groups_perms;
    if($this->is_net_admin) {  // user is network admin, grant him same privileges for all network groups
      foreach($this->user_permissions['groups'] as &$gr_perms) {
        $gr_perms = array_unique(array_merge($gr_perms, $this->user_permissions['network']));
      }
    }
//    echo "<pre>".print_r($this->user_permissions,1)."</pre>";
  }

  /**
   * @name   PermissionsHandler::can_user()
   * @access private
   * @param int $uid
   * @param array $params; format:  array('permissions' => array('perm1, perm2 ...'),
   *                                      'anything' => ... );
   * @param bool $strict;  true  = user must have all required permissions
   *                       false = user can have one of required permissions
   * @brief  Check user permissions for User personal pages
   */
  public static function can_user($uid, $params = array(), $strict = false) {
    $self = new self((int)$uid);
    return $self->_can_user((int)$uid, null, 'user', $strict, $params);
  }

  /**
   * @name   PermissionsHandler::can_network_user()
   * @access private
   * @param int $uid
   * @param int $nid
   * @param array $params; format:  array('permissions' => array('perm1, perm2 ...'),
   *                                      'anything' => ... );
   * @param bool $strict;  true  = user must have all required permissions
   *                       false = user can have one of required permissions
   * @brief  Check user permissions for a Network
   */
  public static function can_network_user($uid, $nid, $params = array(), $strict = false) {
    $self = new self((int)$uid);
    return $self->_can_user((int)$uid, $nid, 'network', $strict, $params);
  }

  /**
   * @name   PermissionsHandler::can_group_user()
   * @access private
   * @param int $uid
   * @param int $gid
   * @param array $params; format:  array('permissions' => array('perm1, perm2 ...'),
   *                                      'anything' => ... );
   * @param bool $strict;  true  = user must have all required permissions
   *                       false = user can have one of required permissions
   * @brief  Check user permissions for a Group
   */
  public static function can_group_user($uid, $gid, $params = array(), $strict = false) {
    $self = new self((int)$uid);
    if($self->is_net_admin) {  // user is network admin, grant him same privileges as for network
      $self->user_permissions['groups'][$gid] = $self->user_permissions['network'];
    }
    return $self->_can_user((int)$uid, $gid, 'groups', $strict, $params);
  }


  // private methods -----------------------------------------------------------------------------------------------------------------

  private function _can_user($uid, $target_id, $target_type, $strict, $params) {

    if (SUPER_USER_ID == $uid) { // SUPER USER has all permissions!
      return TRUE;
    }

    $result = false;

    if(empty($params['permissions'])) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "PermissionsHandler::can_user() - \$params['permissions'] array not defined!");
    }

    if(false !== strpos($params['permissions'], ',')) {
      $_strict = true;            // permissions separated by comas - that means 'must have all required permiss.'
      $required_permissions = explode(',', $params['permissions']);
    } else if(false !== strpos($params['permissions'], '|')) {
      $_strict = false;           // permissions separated by 'OR' - that means 'must have one of required permiss.'
      $required_permissions = explode('|', $params['permissions']);
    } else {
      $_strict = true;            // only one permission - that means 'must have this required permiss.'
      $required_permissions = $params['permissions'];
    }

    $strict = ($strict | $_strict);
    if(!is_array($required_permissions)) {
      $required_permissions = array($required_permissions);
    }
    foreach($required_permissions as &$_perm) {
      $_perm = trim($_perm);
    }

    $user_groups_perms  = $this->user_permissions['groups'];
//    echo "$target_type<pre>".print_r($user_groups_perms,1)."</pre>";

    switch($target_type) {
      case 'user':
        $params['user_id'] = $uid;
        $result = $this->check_user_permissions($required_permissions, $this->user_permissions['user'], $params, $strict, $target_type);
      break;
      case 'network':
        $params['network_id'] = $target_id;
        $result = $this->check_user_permissions($required_permissions, $this->user_permissions['network'], $params, $strict, $target_type);
      break;
      case 'groups':
        $gid = $target_id;
        if(!empty($this->user_permissions['groups'][$gid])) {
          $params['group_id'] = $gid;
          $result = $this->check_user_permissions($required_permissions, $this->user_permissions['groups'][$gid], $params, $strict, $target_type);
        } else {
          $params['group_id'] = null;
          $_cnt = 0;
          $_res = false;
          $req_cnt = count($required_permissions);
          foreach($required_permissions as $perms) {
            if(array_key_exists($perms, $this->dynamic_permissions)) {
              $method_name = $this->dynamic_permissions[$perms];
              if(method_exists($this, $method_name)) {
                $_res = $this->{$method_name}($params, 'group');
              } else {
                throw new PAException(BAD_PARAMETER, "PermissionsHandler::can_user() - permission handler function \"$method_name\" missing!");
              }
              if($strict == true) {
                if($_res) $_cnt++;
                continue;
              } else {
                if($_res) $result = true;
               break;
              }
            }
          }
          if($strict == true) {
            $result = ($_cnt == $req_cnt) ? true : false;
          }
      }
      break;
    }
    return $result;
  }

  private function check_user_permissions($required_permissions, $user_permissions, $params, $strict, $type) {
    $result = false;
    $found = 0;
    $nb_perms = count($required_permissions);

      foreach($required_permissions as $req_perm) {

        if(in_array($req_perm, $user_permissions)) {
          if(!array_key_exists($req_perm, $this->dynamic_permissions)) {
            if($strict == true) {
              $found++;
              continue;
            } else {
              $result = true;
              break;
            }
          } else {
            $_res = false;
            $method_name = $this->dynamic_permissions[$req_perm];
            if(method_exists($this, $method_name)) {
              $_res = $this->{$method_name}($params, $type);
            } else {
              throw new PAException(BAD_PARAMETER, "PermissionsHandler::can_user() - permission handler function \"$method_name\" missing!");
            }
            if($strict == true) {
              if($_res) $found++;
              continue;
            } else {
              if($_res) $result = true;
              break;
            }
          }
        } else {
          if(!array_key_exists($req_perm, $this->dynamic_permissions)) {
            if($strict == true) {
              $result = false;
              break;
            } else {
              continue;
            }
          } else {
            $_res = false;
            $method_name = $this->dynamic_permissions[$req_perm];
            if(method_exists($this, $method_name)) {
              $_res = $this->{$method_name}($params, $type);
            } else {
              throw new PAException(BAD_PARAMETER, "PermissionsHandler::can_user() - permission handler function \"$method_name\" missing!");
            }
            if($strict == true) {
              if($_res) $found++;
              continue;
            } else {
              if($_res) $result = true;
              break;
            }
          }
        }
      }
      if($strict == true) {
        $result = ($found == $nb_perms) ? true : false;
      }
    return $result;
  }

  private function get_available_permiss_by_type($params, $type) {
    $available_permiss = array();
    switch($type) {
      case 'user':
        $available_permiss = $this->user_permissions['user'];
      break;
      case 'network':
        $available_permiss = $this->user_permissions['network'];
      break;
      case 'groups':
        $gid = @$params['group_id'];
          if(!empty($this->user_permissions['groups'][$gid])) {
            $available_permiss = $this->user_permissions['groups'][$gid];
          } else {
            foreach($this->user_permissions['groups'] as $perm_arr) {
              $available_permiss = array_merge($available_permiss, $perm_arr);
            }
          }
      break;
    }
    return $available_permiss;
  }

  // permissions handling methods ---------------------------------------------------------------------------------------------------------------

  private function can_edit_content($params, $type) {
        $available_permiss = $this->get_available_permiss_by_type($params, $type);
        if(in_array('edit_content', $available_permiss)) {
          return true;
        }

        if(!empty($params['cid'])) {
          //Loading content
          $content_obj = Content::load_content((int)$params['cid'], $this->uid);

          //author of the content can perform the action
          if( $content_obj->author_id == $this->uid ) {
            return true;
          }

          // content is a part of some collection
          if( $content_obj->parent_collection_id != -1 ) {
            // Loading collection
            $collection_obj = ContentCollection::load_collection((int)$content_obj->parent_collection_id, $this->uid);
            // owner of collection can also edit the content
            if ( $collection_obj->author_id == $this->uid) {
              return true;
            }
          }
        }
        return false;
  }

  private function can_delete_comment($params, $type) {

      $available_permiss = $this->get_available_permiss_by_type($params, $type);
      if(in_array('delete_comment', $available_permiss)) {
        return true;
      }

      if(!empty($params['comment_info'])) {
        $comment = $params['comment_info']; //array having the comment details
        if (isset($comment['user_id']) and ($comment['user_id'] == $this->uid)) { //Author of comment can delete the comment
          return true;
        } else if (isset($comment['recipient_id']) and ($comment['recipient_id'] == $this->uid)) {
          return true;
        }

        $content = Content::load_content((int)$comment['content_id'], $this->uid);
        if ($content->author_id == $this->uid) {           //Author of the content can delete the comment.
          return true;
        } else if ($content->parent_collection_id != -1) { // means content belongs to some collection
          $collection = ContentCollection::load_collection($content->parent_collection_id, $this->uid);
          if ($collection->author_id == $this->uid) {        //If content on which comment has been posted belongs to some collection then author of that collection can delete the comment
            return true;
          }
        }
      }
      return false;  // return false in all the other cases
  }

  private function can_view_abuse_report_form($params, $type) {
      $available_permiss = $this->get_available_permiss_by_type($params, $type);
      if(in_array('view_abuse_report_form', $available_permiss)) {
        return true;
      }

      if(empty(PA::$login_uid)) return false;       // only logged users can view
      $extra = unserialize(PA::$network_info->extra);
      if (! empty($extra['notify_owner']['report_abuse_on_content']['value'])) {
				$pram = (int)$extra['notify_owner']['report_abuse_on_content']['value'];
				if ($pram > 0) {
					return true;
				}
      }

      return false;
  }

  private function can_delete_comment_authorization($params, $type) {
      $available_permiss = $this->get_available_permiss_by_type($params, $type);
      if(in_array('delete_comment_authorization', $available_permiss)) {
        return true;
      }
      $perm_array = array(@$params['group_owner'], $params['content_owner'], $params['comment_owner']);
      return in_array(PA::$login_uid, $perm_array);
  }

  private function can_post_to_community ($params, $type) {
      $user_permiss = $this->get_available_permiss_by_type($params, 'user');
      if(in_array('post_to_community', $user_permiss)) {
        return true;
      }
      $group_permiss = $this->get_available_permiss_by_type($params, 'groups');
      if(in_array('post_to_community', $group_permiss)) {
        return true;
      }
      $network_permiss = $this->get_available_permiss_by_type($params, 'network');
      if(in_array('post_to_community', $network_permiss)) {
        return true;
      }
      return false;
  }

  private function can_view_content ($params, $type) {
    global $app;

    if(PAGE_USER_PUBLIC == $app->getRequestParam('page_id') && PA::$page_user->has_role_id(CHILD_MEMBER_ROLE)) {
        if(!empty(PA::$login_user)) {
          if(PA::$login_uid == PA::$page_uid) {
            return true;    // page owner always should be able to view its own public page
          }
          $user_dob = PA::$page_user->get_profile_field(GENERAL, 'dob_year');
          $own_age = date('Y') - $user_dob;
          if($own_age < CHILD_LOWER_AGES) {     // page owner is a child below the age
            $is_in_family = FamilyTypedGroupEntity::in_same_family(PA::$login_uid, PA::$page_uid);
            if(count($is_in_family) > 0) {      // so, check whether the visitor is a member of family
              return true;                     
            } else {
              return false;
            }
          } else {
             return true;                       // the child is over age - allow visitors to view page 
          }
        } else {
          return false;     // anonymous users should not be able to see a Child page
        }
    } else {
      $user_permiss = $this->get_available_permiss_by_type($params, 'user');
      if(in_array('view_content', $user_permiss)) {
        return true;
      }
      $group_permiss = $this->get_available_permiss_by_type($params, 'groups');
      if(in_array('view_content', $group_permiss)) {
        return true;
      }
      $network_permiss = $this->get_available_permiss_by_type($params, 'network');
      if(in_array('view_content', $network_permiss)) {
        return true;
      }
    }  
    return false;
  }

  private function can_manage_forum ($params, $type) {
    $board_type = $params['board']->get_type();
    if(($board_type == 'group') && (!empty($params['gid']))) {                    // it is a group forum
      $user_type = Group::get_user_type($params['user_id'], $params['gid']);
      if(($user_type == MODERATOR) || ($user_type == OWNER)) {
        return true;
      }
      $group_permiss = @$this->user_permissions['groups'][$params['gid']];
      if(!empty($group_permiss)) {
        if(in_array('manage_content', $group_permiss)) {
          return true;
        }
      }
    }

    if($board_type == 'network') {
      $is_net_admin = Network::is_admin($params['board']->get_network_id(), $params['user_id']);
      if($is_net_admin) {
        return true;
      }
      $net_permiss = @$this->user_permissions['network'];
      if(!empty($net_permiss)) {
        if(in_array('manage_content', $net_permiss)) {
          return true;
        }
      }
    }

    if($board_type == 'user') {
      if($params['user_id'] == $params['board']->get_owner_id()) {
        return true;
      }
    }
    return false;
  }
}

/**
 * @class PermissionException
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.0.1
 *
 *
 */
class PermissionException extends Exception
{
    public function __construct($message) {
       parent::__construct($message, 65535);
    }
}
