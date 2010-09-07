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
// global var $path_prefix has been removed - please, use PA::$path static variable//todo polish this file
require_once "api/Roles/Roles.php";
require_once "api/Tasks/Tasks.php";
class NetworkLeftLinksModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'left';
  public $outer_template = 'outer_network_settings_left.tpl';

  function __construct() {
    parent::__construct();
    $this->html_block_id = 'SearchGroupsModule';
    $this->task_perms = array('manage_settings'=>FALSE,
                        'meta_networks'=>FALSE,
                        'manage_ads'=>FALSE,
                        'notifications'=>FALSE,
                        'manage_links'=>FALSE,
                        'manage_events'=>FALSE,
                        'manage_content'=>FALSE,
                        'user_defaults'=>FALSE,
                        'manage_themes'=>FALSE,
                       );
  }

  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  //this function sets value of perms
  public function set_perms($perms) {
    if ($perms == 'all') {
      foreach ($this->task_perms as $key=>$value) {
        $this->task_perms[$key] = TRUE;
      }
    }
    if ($perms == 'none') {
      foreach ($this->task_perms as $key=>$value) {
        $this->task_perms[$key] = FALSE;
      }
    }
    if (is_array($perms)) {
      foreach ($perms AS $perm) {
        $task_value = $perm->task_value;
        $this -> task_perms[$task_value] = TRUE;
      }
    }
  }

  function generate_inner_html () {

    if (PA::$network_info->type == MOTHER_NETWORK_TYPE) {
      if (PA::$login_uid == SUPER_USER_ID) {
        $this->set_perms('all');
      } else {
        $tasks = $this->get_user_task_permissions(PA::$login_uid);
        if (count($tasks) == 0) {
          $this->set_perms('none');
        } else {
          $this->set_perms($tasks);
        }
      }
    } else { //spawned networks admin has all permissions
        if (Network::is_admin(PA::$network_info->network_id, PA::$login_uid) || PA::$login_uid == SUPER_USER_ID) {//owner of network
          $this->set_perms('all');
          //todo - quick fix here
          $this->task_perms['meta_networks'] = FALSE;
        } else {
          $tasks = $this->get_user_task_permissions(PA::$login_uid);
          if (count($tasks) == 0) {
            $this->set_perms('none');
          } else {
            $this->set_perms($tasks);
          }
        }

    }
    $extra = unserialize(PA::$network_info->extra);
    $network_content_moderation = FALSE;
    if (@$extra['network_content_moderation'] == NET_YES) { // this can be empty or not set
      $network_content_moderation = TRUE;
    }
    $inner_template = NULL;
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';
    }

    $obj_inner_template = new Template($inner_template);
    $obj_inner_template->set('task_perms', $this->task_perms);
    $obj_inner_template->set('network_content_moderation', $network_content_moderation);
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }

  private function get_user_task_permissions($uid) {
     $tasks = array();
     $role_obj = Roles::get_user_roles($uid);
     if (!empty($role_obj)) {
       $tasks = array();
       foreach($role_obj as $r_obj) {
         $tasks_roles = Roles::get_tasks_of_role($r_obj->role_id);
         if($tasks_roles) {
           $tasks = array_merge($tasks, $tasks_roles);
         }
       }
     }
     return $tasks;
  }

}
?>
