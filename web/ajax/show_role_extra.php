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

 require_once dirname(__FILE__).'/../../config.inc';
 require_once "api/Roles/Roles.php";
 require_once "ext/Group/Group.php";

  

    $selected_role = null;
    $sel_role_id = (!empty($_REQUEST['role_id'])) ? $_REQUEST['role_id'] : null;
    if($sel_role_id) {
      $user_id = (int)$_REQUEST['uid'];
      $user_roles = array();
      $user_groups = array();
      $u_groups = Group::get_user_groups($user_id);
      if(count($u_groups) < 1) {
        $u_groups = Group::get_all_groups_for_admin(FALSE);
        foreach($u_groups as $group) {
          $user_groups[$group['group_id']] = $group['title'];
        }
      } else {
        foreach($u_groups as $group) {
          $user_groups[$group['gid']] = $group['name'];
        }
      }

      $u_roles = Roles::get_user_role($user_id, DB_FETCHMODE_ASSOC);
      foreach($u_roles  as $role) {
         $role_id = $role['role_id'];
         $role_name = Roles::get_role_name($role_id);
         $role_tasks = Roles::get_tasks_of_role($role_id);
         $role_extra = unserialize($role['extra']);
         $role_info  = array('role_id' => $role_id, 'name' => $role_name, 'extra' => $role_extra, 'tasks' => $role_tasks);
         $user_roles[$role_id] = $role_info;
         if($role_id == $sel_role_id) {
           $selected_role = $role_info;
         }
       }
    }
?>

    <?php if($sel_role_id) : ?>
      <?php foreach( $user_roles  as $role) : $curr_role_id = $role['role_id']; $extra = $role['extra'] ?>
        <?php $display = ($sel_role_id && ($sel_role_id == $curr_role_id)) ? $display = " display:block;" : " display:none;" ?>
        <div id="extra_info_<?= $curr_role_id ?>" style="float: left; width: auto;<?=$display?>">
          <div style="float: left; width: auto; margin-left: 8px;">
            <div style="font-weight: bold; padding-bottom: 0.5em"><?= $role['name'] ?></div>
            <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][user]" id="role_extra_user_<?= $curr_role_id ?>" <?= ($extra['user']) ? "checked='checked'":null ?> value="<?= ($extra['user']) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_user_<?= $curr_role_id ?>');" />&nbsp;<?= __('Apply to User pages') ?><br />
            <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][network]" id="role_extra_network_<?= $curr_role_id ?>" <?= ($extra['network']) ? "checked='checked'":null ?> value="<?= ($extra['network']) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_network_<?= $curr_role_id ?>');" />&nbsp;<?= __('Apply to this Network') ?><br />
            <?php foreach($user_groups as $g_id => $g_name) : $cheked = (in_array($g_id, $extra['groups'])) ? "checked='checked'" : null ?>
              <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][groups][<?= $g_id ?>]" id="role_extra_groups_<?= $curr_role_id .'_'. $g_id ?>" <?= $cheked ?> value="<?= ($cheked) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_groups_<?= $curr_role_id .'_'. $g_id ?>');" />&nbsp;<?= __('Apply to ') .$g_name. __(' group') ?><br />
            <?php endforeach; ?>
          </div>
          <div style="float: left; width: auto; margin-left: 8px;">
            <div style="font-weight: bold; padding-bottom: 0.5em"><?= __('Tasks/Permissions') ?></div>
            <?php if(is_array($role['tasks'])) foreach($role['tasks'] as $task) : ?>
              <?= $task->name ?><br />
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
