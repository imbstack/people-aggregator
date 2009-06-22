<?php

 require_once dirname(__FILE__).'/../../config.inc';
 require_once "api/Roles/Roles.php";
 require_once "ext/Group/Group.php";

 global $current_theme_path;

      $user_id = (int)$_REQUEST['uid'];
      $group_id = (!empty($_REQUEST['gid']) && ($_REQUEST['gid'] != '-1')) ? $_REQUEST['gid'] : null;
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

      $r_params = ($group_id) ? array('type' => 'group', 'gid' => $group_id) : null;
      $u_roles  = Roles::get_user_roles($user_id, DB_FETCHMODE_ASSOC, $r_params);
      foreach($u_roles  as $role) {
         $role_id = $role['role_id'];
         $role_type = $role['type'];
         $role_name = Roles::get_role_name($role_id);
         $role_tasks = Roles::get_tasks_of_role($role_id);
         $role_extra = unserialize($role['extra']);
         $role_info  = array('role_id' => $role_id, 'name' => $role_name, 'type' => $role_type, 'extra' => $role_extra, 'tasks' => $role_tasks);
         $user_roles[$role_id] = $role_info;
       }
?>

      <?php foreach( $user_roles  as $role) : $curr_role_id = $role['role_id']; $extra = $role['extra'] ?>
        <div id="extra_info_<?= $curr_role_id ?>" style="float: left; width: auto; display: none">
        <?php if(!$group_id) : ?>
          <div style="float: left; width: auto; margin-left: 8px;">
            <div style="font-weight: bold; padding-bottom: 0.5em"><?= $role['name'] ?></div>
            <?php if(($role['type'] == 'user') || ($role['type'] == 'network')) : ?>
              <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][user]" id="role_extra_user_<?= $curr_role_id ?>" <?= ($extra['user']) ? "checked='checked'":null ?> value="<?= ($extra['user']) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_user_<?= $curr_role_id ?>');" />&nbsp;<?= __('Apply to User pages') ?><br />
              <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][network]" id="role_extra_network_<?= $curr_role_id ?>" <?= ($extra['network']) ? "checked='checked'":null ?> value="<?= ($extra['network']) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_network_<?= $curr_role_id ?>');" />&nbsp;<?= __('Apply to this Network') ?><br />
            <?php endif; ?>
            <?php if($role['type'] == 'group') : ?>
              <?php foreach($user_groups as $g_id => $g_name) : $cheked = ((count($extra['groups']) > 0) and in_array($g_id, $extra['groups'])) ? "checked='checked'" : null ?>
                <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][groups][<?= $g_id ?>]" id="role_extra_groups_<?= $curr_role_id .'_'. $g_id ?>" <?= $cheked ?> value="<?= ($cheked) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_groups_<?= $curr_role_id .'_'. $g_id ?>');" />&nbsp;<?= __('Apply to ') . chop_string($g_name, 15) . __(' group') ?><br />
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
          <div style="float: left; width: auto; margin-left: 16px;">
            <div style="font-weight: bold; padding-bottom: 0.5em"><?= __('Tasks/Permissions') ?></div>
            <?php if(is_array($role['tasks'])) foreach($role['tasks'] as $task) : ?>
              <?= $task->name ?><br />
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
