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

  
//  echo "<pre>".print_r($_REQUEST,1)."</pre>";

    $selected_role = null;
    $sel_role_id = (!empty($_REQUEST['role_id'])) ? $_REQUEST['role_id'] : null;
    $group_id = (!empty($_REQUEST['gid']) && ($_REQUEST['gid'] != '-1')) ? $_REQUEST['gid'] : null;
    $user_id = (int)$_REQUEST['uid'];
    $roles_list = array();
    $user_roles = array();
    $user = new User();
    $user->load($user_id);
    $role = new Roles();
    $params = ($group_id) ? array('condition' => 'type = \'group\'', 'cnt' => false) : array('condition' => 'type <> \'null\'', 'cnt' => false);
    $all_roles = $role->get_multiple($params, DB_FETCHMODE_ASSOC);
    foreach( $all_roles  as $a_role ) {
      $roles_list[$a_role['id']] = $a_role['name'];
    }
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
    if($group_id) {    // show only Group roles
      $u_roles  = Roles::get_user_roles($user_id, DB_FETCHMODE_ASSOC, array('type' => 'group', 'gid' => $group_id));
    } else {           // show network and user personal pages roles
      $g_roles   = array();
      $u_roles   = array();
      $net_roles = array();
      $g_roles   = Roles::get_user_roles($user_id, DB_FETCHMODE_ASSOC, array('type' => 'group', 'gid' => $group_id));
      $u_roles    = Roles::get_user_roles($user_id, DB_FETCHMODE_ASSOC, array('type' => 'user'));
      $net_roles  = Roles::get_user_roles($user_id, DB_FETCHMODE_ASSOC, array('type' => 'network'));
      $u_roles    = array_merge($u_roles, $net_roles);
      $u_roles    = array_merge($u_roles, $g_roles);
    }


    foreach($u_roles  as $role) {
         $role_id = $role['role_id'];
         $role_type = $role['type'];
         $role_name = Roles::get_role_name($role_id);
         $role_tasks = Roles::get_tasks_of_role($role_id);
         $role_extra = unserialize($role['extra']);
         $role_info  = array('role_id' => $role_id, 'name' => $role_name, 'type' => $role_type, 'extra' => $role_extra, 'tasks' => $role_tasks);
         $user_roles[$role_id] = $role_info;
         if($role_id == $sel_role_id) {
           $selected_role = $role_info;
         }
     }

?>

<form action="" class="inputrow" method="post" name="assign_role_form" id="assign_role_form">
  <input type="hidden" name="uid" id = "user_role_id" />
  <fieldset>
  <legend> <?= __('Edit/Assign Role for user: ') . $user->login_name ?> </legend>
  <div style="position: relative; float: left; padding: 6px;">
    <div style="float:left;">

      <div id="unassociated_roles_selectbox" style="position: relative; float: left; margin: 4px;">
        <div style="font-weight: bold; padding-bottom: 0.5em">Unassociated</div>
        <select name="unassociated_roles[]" id="unassociated_roles"  class="multiple-selected" size="10">
        <?php foreach( $roles_list  as $id => $name) : ?>
          <?php if(!array_key_exists($id, $user_roles)) : ?>
            <option value="<?=$id;?>"><?=$name;?></option>
          <?php endif; ?>
        <?php endforeach; ?>
        </select>
      </div>

      <div style="position: relative; float: left;margin: 64px 0 8px 8px;">
        <input type="image" name="commit" src="<?=PA::$theme_url ?>/images/arrow_right.gif" style="border: 0" onclick="javascript: roles.double_list_move('<?= $user_id ?>', 'unassociated_roles', 'associated_roles', '<?= ($group_id) ? $group_id : "-1" ?>'); return false;" /><br />
        <input type="image" name="commit" src="<?=PA::$theme_url ?>/images/arrow_left.gif" style="border: 0" onclick="javascript: roles.double_list_move('<?= $user_id ?>', 'associated_roles', 'unassociated_roles', '<?= ($group_id) ? $group_id : "-1" ?>'); return false;" />
      </div>

      <div id="associated_roles_selectbox" style="position: relative; float: left; margin: 4px;">
        <div style="font-weight: bold; padding-bottom: 0.5em">Associated</div>
        <select name="associated_roles[]" id="associated_roles"  class="multiple-selected" size="10" onchange="javascript: roles.show_role_extra('<?= $user_id ?>', 'associated_roles');">
        <?php foreach( $user_roles  as $role) : $selected = (($sel_role_id && ($sel_role_id == $role['role_id'])) ? "selected='selected'" : null) ?>
          <option value="<?= $role['role_id'] ?>" <?= $selected ?> ><?= $role['name'] ?></option>
        <?php endforeach; ?>
        </select>
      </div>

      <div style="position: relative; float: left; width: auto; margin: 4px;">
        <div id="roles_extra_info">
          <?php foreach( $user_roles  as $role) : $curr_role_id = $role['role_id']; $extra = $role['extra'] ?>
          <?php $display = ($sel_role_id && ($sel_role_id == $curr_role_id)) ? $display = " display:block;" : " display:none;" ?>
            <div id="extra_info_<?= $curr_role_id ?>" style="float: left; width: auto;<?=$display?>">
              <?php if(!$group_id) : ?>
               <div style="float: left; width: auto; margin-left: 8px;">
                <div style="font-weight: bold; padding-bottom: 0.5em"><?= $role['name'] ?></div>
                <?php if(($role['type'] == 'user') || ($role['type'] == 'network')) : ?>
                  <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][user]" id="role_extra_user_<?= $curr_role_id ?>" <?= ($extra['user']) ? "checked='checked'":null ?> value="<?= ($extra['user']) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_user_<?= $curr_role_id ?>');" />&nbsp;<?= __('Apply to User pages') ?><br />
                  <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][network]" id="role_extra_network_<?= $curr_role_id ?>" <?= ($extra['network']) ? "checked='checked'":null ?> value="<?= ($extra['network']) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_network_<?= $curr_role_id ?>');" />&nbsp;<?= __('Apply to this Network') ?><br />
                <?php endif; ?>
                <?php if($role['type'] == 'group') : ?>
                  <?php foreach($user_groups as $g_id => $g_name) : $cheked = ((count($extra['groups']) > 0) and in_array($g_id, $extra['groups'])) ? "checked='checked'" : null ?>
                    <input type="checkbox" name="role_extra[<?= $curr_role_id ?>][groups][<?= $g_id ?>]" id="role_extra_groups_<?= $curr_role_id .'_'. $g_id ?>" <?= $cheked ?> value="<?= ($cheked) ? '1' : '0'?>" onclick="javascript: toggle_chhkbox('role_extra_groups_<?= $curr_role_id .'_'. $g_id ?>');" />&nbsp;<?= __('Apply to ') .$g_name. __(' group') ?><br />
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
         </div>
      </div>

    </div>

    <div style="float:left;">
        <div style="position: relative; float: left; padding: 8px;">
          <div class="text">
            <?php if(!$group_id) : ?>
              <?php echo __("Select roles you want to associate with this user and press left/right arrow, <br />".
                            "or click on any of associated roles for managing Role extra data.") ?>
            <?php else: ?>
              <?php echo __("Select roles you want to associate with this user and press left/right arrow, <br />".
                            "or click on any of associated roles for more informations about Role permissions.") ?>
            <?php endif; ?>
          </div>
        </div>
        <div id="buttonbar" style="position: relative; float:right;">
        <?php if(!$group_id) : ?>
          <input name="search" type="button" id="search" value="Save Role Assignment"  onclick="javascript: roles.update_role_extra('<?= $user_id ?>');"/>
        <?php endif; ?>
          <input name="cancel" type="button" id="cancel_btn" value="Cancel" onclick="javascript: modal_hide();"/>
        </div>
    </div>
  </div>
</fieldset>
</form>
