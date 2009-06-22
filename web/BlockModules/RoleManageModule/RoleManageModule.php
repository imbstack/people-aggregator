<?php
require_once "web/includes/classes/Pagination.php";
require_once "api/User/User.php";
require_once "api/Network/Network.php";
require_once "web/includes/classes/PaConfiguration.class.php";

class RoleManageModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';

  function __construct() {
    $this->main_block_id = "mod_network_user_result";
    $this->title = __('Manage Roles');
    $this->display = false;
  }


  function initializeModule($request_method, $request_data) {
    if(!empty($request_data['display']) && ($request_data['display'] == true)) {
      $this->display = true;
    }
  }


  function handleRequest($request_method, $request_data) {
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

  private function handlePOST_loadRolesSettings($request_data) {
    global $error_msg;
      if(!empty($_FILES['local_file']['name']) && is_uploaded_file($_FILES['local_file']['tmp_name'])) {
        if($_FILES['local_file']['type'] != 'text/xml') {
          $error_msg = __('Invalid configuration file format. Configuration file should be a valid XML file. ');
        } else {
          try {
            $content = file_get_contents($_FILES['local_file']['tmp_name']);
            $imported_config = new PaConfiguration($content);
            $restore_roles = $imported_config->getRolesSettings();
            $_msg = __("Roles settings successfully loaded from ") . $_FILES['local_file']['name'] . __(" file.");
            $error_msg = $this->restoreRoleSettings($restore_roles, $_msg);
          } catch (Exception $e) {
            $error = TRUE;
            $error_msg = $e->getMessage();
          }
        }
      } else {
          $error_msg = __('Please, select a valid XML configuration file.');
      }
  }

  private function handlePOST_storeRolesAsDefaults($request_data) {
    global $error_msg;
    $network = $this->shared_data['network_info'];
    $export_config = new PaConfiguration();
    $export_config->buildNetworkSettings($network);
    $export_config->storeSettingsLocal();
    $error_msg = 'Network default configuration file "' . $export_config->settings_file . '" successfully updated.';
  }

  private function handlePOST_restoreRoleToDefaults($request_data) {
    global $error_msg;
      $imported_config = new PaConfiguration();
      $restore_roles = $imported_config->importRolesInfo();
      $error_msg = $this->restoreRoleSettings($restore_roles);
  }

  private function handleAJAX_updateRole($request_data) {
//  echo "<pre>".print_r(serialize($request_data), 1)."</pre>";
    $role = new Roles();
    filter_all_post($request_data);
    $role->id = $request_data['role_id'];
    $role->description = $request_data['role_desc'];
    $role->name = $request_data['role_name'];
    $role->type = $request_data['role_type'];
    try {
      $role->update();
      Roles::delete_role_tasks($role->id);
      if(!empty($request_data['tasks'])) {
        $tasks = explode(',', $request_data['tasks']);
        Roles::assign_tasks_to_role($tasks, $role->id);
      }
      $msg = __('Role data sucessfully updated.');
    }
    catch (PAException $e) {
      $msg = "$e->message";
      $error = TRUE;
    }
    print($msg);
    exit;
  }

  private function handleAJAX_addRole($request_data) {
    $role = new Roles();
    filter_all_post($request_data);
    try {
      $role->description = $request_data['role_desc'];
      $role->name = $request_data['role_name'];
      $role->type = $request_data['role_type'];
      $role_id = $role->create();
      if($role_id && !empty($request_data['tasks'])) {
        $tasks = explode(',', $request_data['tasks']);
        Roles::assign_tasks_to_role($tasks, $role_id);
      }
      $msg = __('New Role sucessfully created.');
    }
    catch (PAException $e) {
      $msg = "$e->message";
    }
    print($msg);
    exit;
  }

  private function handleAJAX_delRole($request_data) {
    $roles = new Roles();
    filter_all_post($request_data);
    try {
      $role = $roles->get($request_data['role_id']);
      if(is_object($role)) {
        if(!$role->read_only) {
          $roles->delete((int)$request_data['role_id']);
          $msg = __('Role sucessfully deleted.');
        } else {
          $msg = __('This Role can\'t be deleted.');
        }
      }
    }
    catch (PAException $e) {
      $msg = "$e->message";
    }
    print($msg);
    exit;
  }

  private function task_perm_exists($perm_id, $perms_data_arr) {
     foreach($perms_data_arr as $perm) {
       if($perm['id'] == $perm_id) return true;
     }
     return false;
  }

  private function handleAJAX_getRole($request_data) {
    if($request_data['id']) {
      $tasks = Tasks::get_instance();
      $available_tasks = $tasks->get_tasks(DB_FETCHMODE_ASSOC);
      $role = Roles::getRoleInfoByID($request_data['id']);
      $read_only = ($role['read_only']) ? ' readonly="readonly"' : null;
      $info_msg = __("Select Task(s) you want to assign to this Role and press left/right arrow.");
      $div_generate = '
       <fieldset class="center_box">
         <div class="field">
           <h4>Name</h4>
           <input type="text" id="role_name" class="text longer" value="'.$role['name'] . '"' . $read_only . ' />
         </div>
         <div class="field_bigger">
           <h4>Description :</h4>
           <textarea name="desc" id="desc">'.$role['description'] .'</textarea>
         </div>
         <div class="field_big" style="float:left">
          <h4>Role type :</h4>
          <div class="center">
            <input name="role_type" id="role_type_user" type="radio" value="user" ' . (($role["type"] == "user") ? "checked=\"checked\"" : null) .'/>  Users Perosnal Role <br />
            <input name="role_type" id="role_type_network" type="radio" value="network" ' . (($role["type"] == "network") ? "checked=\"checked\"" : null) .'/>  Network Role <br />
            <input name="role_type" id="role_type_group" type="radio" value="group" ' . (($role["type"] == "group") ? "checked=\"checked\"" : null) .'/> Group Role
          </div>
         </div>';
      $div_generate .= '
            <div class="field_bigger" style="height:auto">
              <h4>Assign Tasks to Role: </h4>
              <div style="float: left">
                <div style="font-weight: bold; padding-bottom: 0.5em">Available Tasks</div>
                  <select name="unassociated_tasks[]" id="unassociated_tasks" multiple="multiple" class="multiple-selected" size="10">';
                    for($cnt = 0; $cnt < count($available_tasks); $cnt++) {
                      if(!$this->task_perm_exists($available_tasks[$cnt]['id'], $role['tasks'])) {
                        $div_generate .=
                        '<option value="' . $available_tasks[$cnt]['id'] .
                           '" onmouseover="javascript: roles_edit.showdescription(\'' . $available_tasks[$cnt]['description'] . '\');"' .
                           '" onmouseout="javascript: roles_edit.showdescription(\'' . $info_msg . '\');" >' .
                           $available_tasks[$cnt]['name'] .
                        '</option>';
                      }
                    }
      $div_generate .= '
                  </select>
                </div>
                <div style="float: left; margin: 48px 24px; ">
                  <input type="image" name="commit" src="' . PA::$theme_url . '/images/arrow_right.gif" style="border: 0" onclick="roles_edit.double_list_move(\'unassociated_tasks\', \'associated_tasks\'); return false;" /><br />
                  <input type="image" name="commit" src="' . PA::$theme_url . '/images/arrow_left.gif" style="border: 0" onclick="roles_edit.double_list_move(\'associated_tasks\', \'unassociated_tasks\'); return false;" />
                </div>
                <div style="float: left">
                  <div style="font-weight: bold; padding-bottom: 0.5em">Assigned Tasks</div>
                    <select name="associated_tasks[]" id="associated_tasks" multiple="multiple" class="multiple-selected" size="10">';
                    for($cnt = 0; $cnt < count($role['tasks']); $cnt++) {
                        $div_generate .=
                        '<option value="' . $role['tasks'][$cnt]['id'] .
                           '" onmouseover="javascript: roles_edit.showdescription(\'' . $role['tasks'][$cnt]['description'] . '\');"' .
                           '" onmouseout="javascript: roles_edit.showdescription(\'' . $info_msg . '\');" >' .
                           $role['tasks'][$cnt]['name'] .
                        '</option>';
                    }
      $div_generate .= '
                    </select>
                  </div>
                  <br style="clear: both" />
                </div>
                  <div class="text" id="role_description">
                    ' . $info_msg . '
                  </div>
            </div>';
      $div_generate .= '
         </fieldset>
       <div class="button_position">
         <input type="hidden" name="role_id" id="role_id" value="'.$request_data['id'].'"/>
         <input type="button" value="'. __("Save") . '"  onclick="roles_edit.saverole(\'updateRole\');" />
         <input type="button" value="'. __("Cancel") . '"  onclick="roles_edit.closeedit();" />
       </div>';

       print($div_generate);
       exit;
    }
  }

  private function handleAJAX_showRole($request_data) {
      $tasks = Tasks::get_instance();
      $available_tasks = $tasks->get_tasks(DB_FETCHMODE_ASSOC);
      $info_msg = __("Select Task(s) you want to assign to this Role and press left/right arrow.");
      $div_generate = '
       <fieldset class="center_box">
         <div class="field">
           <h4>Name</h4>
           <input type="text" name="role_name" id="role_name" class="text longer" value="" />
         </div>
         <div class="field_bigger">
           <h4>Description :</h4>
           <textarea name="desc" id="desc"></textarea>
         </div>
         <div class="field_big" style="float:left">
          <h4>Role type :</h4>
          <div class="center">
            <input name="role_type" id="role_type_user" type="radio" value="user" />  Users Perosnal Role <br />
            <input name="role_type" id="role_type_network" type="radio" value="network" />  Network Role <br />
            <input name="role_type" id="role_type_group" type="radio" value="group" /> Group Role
          </div>
         </div>';
      $div_generate .= '
            <div class="field_bigger" style="height:auto">
              <h4>Assign Tasks to Role: </h4>
              <div style="float: left">
                <div style="font-weight: bold; padding-bottom: 0.5em">Available Tasks</div>
                  <select name="unassociated_tasks[]" id="unassociated_tasks" multiple="multiple" class="multiple-selected" size="10">';
                    for($cnt = 0; $cnt < count($available_tasks); $cnt++) {
                        $div_generate .=
                        '<option value="' . $available_tasks[$cnt]['id'] .
                           '" onmouseover="javascript: roles_edit.showdescription(\'' . $available_tasks[$cnt]['description'] . '\');"' .
                           '" onmouseout="javascript: roles_edit.showdescription(\'' . $info_msg . '\');" >' .
                           $available_tasks[$cnt]['name'] .
                        '</option>';
                    }
      $div_generate .= '
                  </select>
                </div>
                <div style="float: left; margin: 48px 24px; ">
                  <input type="image" name="commit" src="' . PA::$theme_url . '/images/arrow_right.gif" style="border: 0" onclick="roles_edit.double_list_move(\'unassociated_tasks\', \'associated_tasks\'); return false;" /><br />
                  <input type="image" name="commit" src="' . PA::$theme_url . '/images/arrow_left.gif" style="border: 0" onclick="roles_edit.double_list_move(\'associated_tasks\', \'unassociated_tasks\'); return false;" />
                </div>
                <div style="float: left">
                  <div style="font-weight: bold; padding-bottom: 0.5em">Assigned Tasks</div>
                    <select name="associated_tasks[]" id="associated_tasks" multiple="multiple" class="multiple-selected" size="10">';
      $div_generate .= '
                    </select>
                  </div>
                  <br style="clear: both" />
                </div>
                  <div class="text" id="role_description">
                    ' . $info_msg . '
                  </div>
            </div>';
      $div_generate .= '
         </fieldset>
       <div class="button_position">
         <input type="hidden" name="role_id" id="role_id" value=""/>
         <input type="button" value="'. __("Save") . '"  onclick="roles_edit.saverole(\'addRole\');" />
         <input type="button" value="'. __("Cancel") . '"  onclick="roles_edit.closeedit();" />
       </div>';

       print($div_generate);
       exit;
  }

  private function getRolesInfo() {
    $roles = new Roles();
    $roles_info = $roles->get_multiple(null, DB_FETCHMODE_ASSOC);
    foreach($roles_info as &$role) {
      $role['tasks'] = Roles::get_tasks_of_role($role['id'], DB_FETCHMODE_ASSOC);
    }
    return $roles_info;
  }

  private function restoreRoleSettings($restore_roles, $_msg = null) {
    try {
      if(count($restore_roles) > 0) {

        $roles = new Roles();
        $roles_info = $roles->get_multiple(null);
        foreach($roles_info as $_role) {
          $role = $roles->get($_role->id);
          if(is_object($role)) {
            if(!$role->read_only) {
              $roles->delete((int)$_role->id);
            }
          }
        }
        foreach($restore_roles as $role) {
          $_role = Roles::getRoleInfoByID($role['id'], $fetch_mode = DB_FETCHMODE_ASSOC);
          if(isset($_role['id'])) {  // existing role, need to update only
            $new_role = new Roles();
            $new_role->id = $role['id'];
            $new_role->description = $role['description'];
            $new_role->name = $role['name'];
            $new_role->type = $role['type'];
            $new_role->update();
            Roles::delete_role_tasks($new_role->id);
            if(!empty($role['tasks'])) {
              $tasks = array();
              foreach($role['tasks'] as $task) {
                $tasks[] = $task['id'];
              }
              Roles::assign_tasks_to_role($tasks, $new_role->id);
            }
          } else {
            $new_role = new Roles();
            $new_role->description = $role['description'];
            $new_role->name = $role['name'];
            $role_id = $new_role->create();
            if($role_id && !empty($role['tasks'])) {
              $tasks = array();
              foreach($role['tasks'] as $task) {
                $tasks[] = $task['id'];
              }
              Roles::assign_tasks_to_role($tasks, $role_id);
            }
          }
        }
        $error_msg = ($_msg) ? $_msg : __('Default Roles settings sucessfully restored.');
      } else {
        $error_msg = __('There is no Roles data in default XML settings file.');
      }
    } catch (Exception $e) {
        $error = TRUE;
        $error_msg = $e->getMessage();
    }
    return $error_msg;
  }


 /*
  function set_inner_template($template_fname) {
    $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/$template_fname";
  }

  function render() {
    $content = parent::render();
    return $content;
  }

  function generate_inner_html($template_vars = array()) {

    $inner_html_gen = & new Template($this->inner_template);
    foreach($template_vars as $name => $value) {
      if(is_object($value)) {
        $inner_html_gen->set_object($name, $value);
      } else {
        $inner_html_gen->set($name, $value);
      }
    }
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
*/

   //render the contents of the page
   function render() {
     $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  //inner html of the module generation
  function generate_inner_html () {
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';
    }
    $inner_html_gen = & new Template($inner_template);
    $role = new Roles();
    $params = array('sort_by' => 'id', 'direction' => 'ASC', 'cnt' => false);
    $this->links = $role->get_multiple($params);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('display', @$this->display);
    $inner_html_gen->set('super_user_and_mothership', @$this->super_user_and_mothership);
    $inner_html_gen->set('config_navigation_url',
                      network_config_navigation('manage_roles'));
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>
