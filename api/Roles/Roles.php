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
require_once "db/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Cache/Cache.php";
require_once "api/User/User.php";
require_once "api/Network/Network.php";
require_once "api/Logger/Logger.php";
require_once "api/api_constants.php";
require_once "api/Tasks/Tasks.php";

/**
* Class Roles represents different user roles in the system.
* Purpose - this class is used to add/edit/delete roles
* @package Roles
* @author Tekriti Software
*/

class Roles {

  /**
  * The id associated with each role.
  * @var integer
  */
  public $id;

  /**
  * The name of role.
  * @var string
  */
  public $name;

  /**
  * The description of role.
  * @var string
  */
  public $description;

  /**
  * The created time of role.
  * @var integer
  */
  public $created;

  /**
  * The changed time of role.
  * @var integer
  */
  public $changed;

  /**
  * The role type (network or group).
  * @var string
  */
  public $type;


  /**
  * The properties of the class
  * @var array
  */
  public $properties = array('id', 'name', 'description', 'created', 'changed', 'type');

  /**
  * The default constructor for Roles class.
  */
  public function __construct($id = NULL) {
    Logger::log("Enter: function Roles::create");

    Logger::log("Exit: function Roles::create");
  }

  /**
  * Purpose: this function creates a new role
  * @param class variables that must be set prior to calling this function
  * Class variables are set by method set_vars();
  * @return id of the role created
  */
  public function create() {
    Logger::log("Enter: function Roles::create");
    $this->created = $this->changed = time();
     if (empty($this->name) ) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Please specify Role name,it can not be left blank.");
    }
    if (empty($this->description)   ) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Please specify Role description ,it can not be left blank.");
    }
    if (empty($this->type)   ) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Please specify Role type ,it can not be left blank.");
    }
    $res = Dal::query("INSERT INTO {roles} (name, description, created, changed, type) VALUES (?, ?, ?, ?, ?)", array($this->name, $this->description, $this->created, $this->changed, $this->type));
    $this->id = Dal::insert_id();
    Logger::log("Exit: function Roles::create");
    return $this->id;
  }

  /**
  * Purpose: this function updates the role
  * @param id of the role
  * @return id of the role created
  */

  public function update() {
    Logger::log("Exit: function Roles::update");
    $this->changed = time();
    if (empty($this->id)) {
       throw new PAException(REQUIRED_PARAMETERS_MISSING, "Some internal error occured while updating role.");
    }
    if (empty( $this->name)  ) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Please specify Role name,it can not be left blank.");
    }
    if (empty($this->description) ) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Please specify Role description ,it can not be left blank.");
    }
    if (empty($this->type) ) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Please specify Role type ,it can not be left blank.");
    }
    $update_fields = array('name', 'description', 'type');
    $sql = ' UPDATE {roles} SET changed = ?';
    $data = array(time());
    foreach ($update_fields as $field) {
      if (isset($this->$field)) {
        $sql .= " , $field = ? ";
        array_push($data, $this->$field);
      }
    }
    $sql .=" WHERE id = ? " ;
    array_push($data, $this->id);
    $res = Dal::query($sql,$data);
    Logger::log("Exit: function Roles::udpate");

  }

  /**
  * Purpose: this function gets one role based on id
  * id should be set
  * @return array of the role 1 record
  */
  public function get($id = null, $fetch_mode = DB_FETCHMODE_OBJECT) {
    Logger::log("Enter: function Roles::get");
    if($id) $this->id = $id;
    if (empty($this->id)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "ID not set.");
    }
    if (!empty($this->id) && !is_numeric($this->id)) {
      throw new PAException(INVALID_ID, "ID is invalid.");
    }
    $res = Dal::query(' SELECT * FROM {roles} WHERE id = ?', array($this->id));
    $row = $res->fetchRow($fetch_mode);
//    $r = Dal::row_object($res);
    Logger::log("Exit: function Roles::get");
//    return $r;
    return $row;

  }

  /**
  * Purpose: this function gets one role based on id and assigned tasks
  *
  * @return array of the role 1 record with tasks info
  */
  public static function getRoleInfoByID($id, $fetch_mode = DB_FETCHMODE_ASSOC) {
    $roles = new self();
    $role_info = $roles->get($id, $fetch_mode);
    $role_info['tasks'] = array();
    $res = self::get_tasks_of_role($id, DB_FETCHMODE_ASSOC);
    $role_info['tasks'] = ($res != false) ? $res : array();
    return $role_info;
  }

  /**
  * Purpose: this function gets one role based on id
  * @param class variables that must be set prior to calling this function
  * @return array of the collection of records
  *
  * NOTE: use DB_FETCHMODE_ASSOC if you need associative array result type
  *
  */

  public function get_multiple($params = NULL, $fetch_mode = DB_FETCHMODE_OBJECT) {
    Logger::log("Enter: function Roles::udpate");
    $roles = array();
    $condition = (!empty($params['condition'])) ? $params['condition'] : '1';

    $sql = "SELECT * FROM {roles} WHERE $condition ";
    // OK we want to find the details
    $sort_by = (!empty($params['sort_by'])) ? $params['sort_by'] : 'created';
    $direction = (!empty($params['direction'])) ? $params['direction'] : 'DESC';
    $order_by = ' ORDER BY '. $sort_by .' '. $direction;
    if (!empty($params['page']) && (!empty($params['show']))) {
      $start = ($params['page'] -1) * $params['show'];
      $limit = ' LIMIT '.$start.','.$params['show'];
    } else {
      $limit = "";
    }
    $sql = $sql . $order_by . $limit;
    $res = Dal::query($sql);
    if (!empty($params) && $params['cnt'] == TRUE) {
       return $res->numRows();
    }
    while ( $row = $res->fetchRow($fetch_mode) ) {
      $roles[] = $row;
    }
    Logger::log("Exit: function Roles::udpate");
    return $roles;
  }

// TODO: Add tasks_roles extra field
  /**
  * Purpose: this function assign list of tasks to a role
  * @param $role, $tasks
  * @return TRUE
  */
  public static function assign_tasks_to_role($tasks, $role) {
    Logger::log("Enter: function Roles::assign_tasks_to_role");
    if (empty($tasks)||empty($role)){
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Some internal error occured while updating role.");
    }
    if (is_array($tasks)){
      foreach($tasks as $task){
	      Dal::query('DELETE FROM {tasks_roles} WHERE task_id = ? AND role_id = ?', array($task, $role));
        Dal::query('INSERT INTO {tasks_roles} (task_id, role_id) VALUES(?, ?)', array($task, $role));
      }
    } else {
	     Dal::query('DELETE FROM {tasks_roles} WHERE task_id = ? AND role_id = ?', array($tasks, $role));
       Dal::query('INSERT INTO {tasks_roles} (task_id, role_id) VALUES(?, ?)', array($tasks, $role));
    }
    Logger::log("Exit: function Roles::assign_tasks_to_role");
  }


  /**
  * Purpose: this function delete all tasks of a role
  * @param $role_id
  *
  */
  public static function delete_role_tasks($role_id) {
    Logger::log("Enter: function Roles::delete_role_tasks");
    if (empty($role_id)){
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Some internal error occured while deleting role tasks.");
    }
    Dal::query('DELETE FROM {tasks_roles} WHERE role_id = ?', array($role_id));
    Logger::log("Exit: function Roles::delete_role_tasks");
  }


  /**
  * Purpose: this function assign single or multiple roles to a user
  * @param $role
  * @return TRUE
  *
  */
  public function assign_role_to_user($roles, $user_id) {
    Logger::log("Enter: function Roles::assign_role_to_user");
    if (empty($roles)||empty($user_id)){
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Some internal error occured while updating role.");
    }
    $user = new User();
    $user->load($user_id);
    if(!is_array($roles)) {
      $roles = array($roles);
    }
    $user->set_user_role($roles);
    Logger::log("Exit: function Roles::assign_role_to_user");
  }

  public static function set_user_role_for_network($user_id, $role_id, $network_address = null, $role_extra = NULL) {
         if($role_extra) {
            $sql = 'INSERT into ' .$network_address. '_users_roles (user_id, role_id, extra) values (?, ?, ?)';
            $data = array($user_id, $role_id, $role_extra);
         } else {
            $sql = 'INSERT into ' .$network_address. '_users_roles (user_id, role_id) values (?, ?)';
            $data = array($user_id, $role_id);
         }
         Dal::query($sql, $data);
  }


 /**
  * Purpose: this function deletes role(s) assigned to user
  * @param $role
  * @return TRUE
  *
  */

  public static function delete_user_roles($user_id, $roles_info = -1, $group_id = null) {  // if($role_id == -1) all user roles will be deleted !!!
    Logger::log("Enter: function Roles::delete_user_roles");
    if (empty($user_id)){
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Some internal error occured while deleting user roles.");
    }
    $user = new User();
    $user->load($user_id);
    if($roles_info == -1) {  // delete all user roles (default option)
      $user->delete_user_role();
    } else {
      if(!is_array($roles_info)) {
        $user->delete_user_role(array($roles_info), $group_id);
      } else {
        $user->delete_user_role($roles_info, $group_id);
      }
    }

    Logger::log("Exit: function Roles::delete_user_roles");
  }


  /**
  * Purpose: this function deletes the role and all its relations
  * @param $id
  * @return TRUE
  * Implemented now;
  * added by: Zoran Hron
  */
  public function delete($id) {
    Logger::log("Enter: function Roles::delete role");
    // delete role
    Dal::query('DELETE FROM {roles} WHERE id = ?', array($id));
    // delete tasks relations for this role id
    Dal::query('DELETE FROM {tasks_roles} WHERE role_id = ?', array($id));

    // delete users_roles relations for this role id from mother network
    Dal::query('DELETE FROM {users_roles} WHERE role_id = ?', array($id));
/*
    // set default users role - Login User
    $users_obj = new User();
    $users = $users_obj->load_users(array());                 // get all users
    $this->set_default_users_role_for_network($users);

    // delete users_roles relations for this role id from spawned networks
    $this->delete_role_for_spawned_networks($id);
*/
    Logger::log("Exit: function Roles::delete role");

    return true;
  }
/*
  private function delete_role_for_spawned_networks($role_id) {
     $n = new Network();
     $networks = $n->get();
//     echo "Total users: ".count($users).", Total networks: ". count($networks);
     foreach($networks as $network) {
       $naddress = $network->address;
       $nid      = $network->network_id;
       // delete users relations for this role id for network
       Dal::query("DELETE FROM $naddress"."_users_roles WHERE role_id = ?", array($role_id));
       $network_users_data = Network::get_network_members($nid);
       $network_users = $network_users_data['users_data'];
       $this->set_default_users_role_for_network($network_users, $naddress);
     }
  }

  public function set_default_users_role_for_network($network_users, $network_address = null) {
       foreach($network_users as $user) {
          $uid = $user['user_id'];
          Roles::set_user_role_for_network($uid, LOGINUSER_ROLE, $network_address);
       }
  }

  public static function set_user_role_for_network($user_id, $role_id, $network_address = null, $role_extra = NULL) {
      $a_user = new User();
      $a_user->load((int)$user_id, null, true);
      $user_roles = $a_user->get_user_roles_by_network(DB_FETCHMODE_ASSOC, $network_address);
      $already_has_role = false;
      foreach($user_roles as $u_role) {
         if($u_role['role_id'] == $role_id) {
            $already_has_role = true;
            break;
         }
      }
      if(!$already_has_role) {
         if($network_address && ($network_address != 'default')) {
            // for spawned networks
            $sql = 'INSERT into ' .$network_address. '_users_roles (user_id, role_id, extra) values (?, ?, ?)';
         } else {
            // for mother network
            $sql = 'INSERT into {users_roles} (user_id, role_id, extra) values (?, ?, ?)';
         }
         $data = array($user_id, $role_id, $role_extra);
         Dal::query($sql, $data);
      }
  }

  public static function unset_user_role_for_network($user_id, $role_id = 0, $network_address = null) {
         if($network_address && ($network_address != 'default')) {
            // for spawned networks
            $sql = 'DELETE FROM ' .$network_address. '_users_roles WHERE user_id = ?';
         } else {
            // for mother network
            $sql = 'DELETE FROM {users_roles} WHERE user_id = ?';
         }
         if($role_id == 0) {                // delete all user roles for this network
            $data = array($user_id);
         } else {                           // delete only this role
            $sql .= ' and role_id = ?';
            $data = array($user_id, $role_id);
         }
         Dal::query($sql, $data);
  }
*/

  /**
   * Purpose: this function returns role name based on $role_id argument
   * @return string role name
   */
  public static function get_role_name($role_id) {
    Logger::log("Enter: function Roles::get_role_name");
    if (empty($role_id)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Role id is not set.");
    }

    $res = Dal::query(' SELECT name  FROM {roles}  WHERE id = ?', array($role_id));
    $r = Dal::row_object($res);
    $name = ($r) ? $r->name : null;

    Logger::log("Exit: function Roles:get_role_name");
    return $name;
  }



  /**
   * @param $params - additional params such as role type: 'user', 'group', 'network' ...
   * Purpose: this function gets user role based on user id
   * user id should be set
   * @return array of the role 1 record
   */
  public static function get_user_roles($user_id, $fetch_mode = DB_FETCHMODE_OBJECT, $params = null) {

    Logger::log("Enter: function Roles::get_user_roles");
    if (!isset($user_id)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "User id is not set.");
    }

    if($user_id) {
      $user = new User();
      $user->load($user_id);
      $r = $user->get_user_roles($params, $fetch_mode);
    } else {  // get ANONYMOUS_ROLE
      $r[0] = (object)array( 'user_id' => 0,
                     'role_id' => ANONYMOUS_ROLE,
                     'extra' => "a:3:{s:4:\"user\";b:0;s:7:\"network\";b:1;s:6:\"groups\";a:0:{}}"
                    );
    }
    Logger::log("Exit: function Roles:get_user_roles");
    return $r;
  }


  /**
   * Purpose: this function check an task is exist for a  role or not
   * @return TRUE or FALSE
   */
  public static function is_roletask_exist($role_id, $task_id) {
    Logger::log("Enter: function Roles::is_roletask_exist");
    $res = Dal::query('SELECT count(*) AS CNT FROM {tasks_roles} WHERE role_id = ? and task_id = ? ', array($role_id, $task_id));
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $cnt = $row->CNT;
    if ($cnt > 0 ) {
      $r = TRUE;
    } else {
      $r = FALSE;
    }
    Logger::log("Exit: function Roles:is_roletask_exist");
    return $r;
  }


  /**
   * Purpose: this function  delete the  task role
   */
  public static function delete_taskrole($role_id, $task_id) {
    Logger::log("Enter: function Roles::delte_taskrole");
    $res = Dal::query('DELETE   FROM {tasks_roles}  WHERE role_id = ? and task_id = ? ', array($role_id, $task_id));
    Logger::log("Exit: function Roles:delte_taskrole");
  }

  /**
   * Purpose : this function checks if a given user has permission to given task
   * @param : $uid, $task_id
   * @return : TRUE, FALSE
   *
   *
   * NOTE: This function is obsolete now!
   *
  **/

/*
  used by:
    /opt/lampp/htdocs/pa/pacore/web/includes/authorize.inc.php
    /opt/lampp/htdocs/pa/pacore/web/includes/functions/functions.php
    /opt/lampp/htdocs/pa/pacore/web/config/pages/network_calendar.xml
    /opt/lampp/htdocs/pa/pacore/web/includes/network.inc.php
    /opt/lampp/htdocs/pa/pacore/web/network_statistics.php

*/


  public static function check_permission($uid, $task_id) {
    $result = false;
    Logger::log("Enter: function Roles::check_permission");
    if (SUPER_USER_ID == $uid) {
      return TRUE; //TODO:same holds true for network owner
    }
    $role_obj = Roles::get_user_roles($uid);
    foreach($role_obj as $r_obj) {
       $result = Roles::is_roletask_exist($r_obj->role_id, $task_id);
       if($result) break;                                              // user have permission for this task!
    }
    Logger::log("Exit: function Roles::check_permission");
    return $result;

  }

  /**
   * Purpose : this function return all user task permissions
   * @param : $uid
   * @return : array of tasks permissions ID's
   *
   *
   * NOTE: This function is obsolete now!
   *
  **/
/*
  public static function get_all_user_permissions($uid) {
    $result = array();
    Logger::log("Enter: function Roles::get_all_user_permissions");
    $role_obj = Roles::get_user_roles($uid);
    foreach($role_obj as $r_obj) {
       $role_tasks = Roles::get_tasks_of_role($r_obj->role_id);
       if($role_tasks) {
          foreach($role_tasks as $rt) {
            $result[] = $rt->id;
          }
       }
    }
    Logger::log("Exit: function Roles::get_all_user_permissions");
    return $result;
  }
*/

  /**
   * Purpose : this function check has a given user any administration privileges on the Network level
   * @param  : $uid
   * @return : true if user has any administration task/permission assigned
   */
   /*
     Used by:
      /opt/lampp/htdocs/pa/pacore/web/email_notification.php
      /opt/lampp/htdocs/pa/pacore/web/includes/classes/Navigation.php
      /opt/lampp/htdocs/pa/paproject/web/includes/classes/Navigation.php
      /opt/lampp/htdocs/pa/pacore/web/includes/network.inc.php
      /opt/lampp/htdocs/pa/pacore/web/network_statistics.php
      /opt/lampp/htdocs/pa/pacore/web/network_user_defaults.php
      /opt/lampp/htdocs/pa/pacore/web/relationship_settings.php
      /opt/lampp/htdocs/pa/pacore/api/Roles/Roles.php
      /opt/lampp/htdocs/pa/pacore/web/includes/shortcuts_menu.php
   */
  public static function check_administration_permissions($uid) {
    Logger::log("Enter: function Roles::check_administration_permissions");

    if($uid == SUPER_USER_ID) return true;  // meta admin!

    $user = new User();
    $user->load($uid);
    $roles = $user->get_user_roles(DB_FETCHMODE_OBJECT);

    $result = false;
    $user_tasks = array();
    foreach($roles as $role) {                                    // first get all user network roles
      if($role->extra['network'] == true) {
        $role_tasks = Roles::get_tasks_of_role($role->role_id);   // then get all tasks/permissions
        if($role_tasks) {
          foreach($role_tasks as $rt) {
            $user_tasks[] = $rt->name;                            // and task/permission names
          }
        }
      }
    }
    foreach($user_tasks as $task_name) {
      if((false !== stripos($task_name, 'Manage')) || (false !== stripos($task_name, 'Configure'))) {
        $result = true;                                           // if user have any task/permission that beggining
        break;                                                    // with 'Manage' or 'Configure', that means that this
      }                                                           // user have assigned one of administration permissions
    }
    Logger::log("Exit: function Roles::check_administration_permissions");
    return $result;
  }



  // this function checks if a given user has permission to perform a
  // given task, specified by task_value.
  //
  // used by: network_calendar.xml, network.inc.php
  // TODO: Replace these with new functions
  //
  public static function check_permission_by_value($uid, $task_value) {
    $task_id = Tasks::get_id_from_task_value($task_value);
    return Roles::check_permission($uid, $task_id);
  }


  /**
   * Purpose : this function gives the tasks associated with the role
   * @param : $role_id
   * @return : TRUE, FALSE
  **/
  public static function get_tasks_of_role($role_id, $fetch_mode = DB_FETCHMODE_OBJECT) {
    Logger::log("Enter: function Roles::get_tasks_of_role");
    $sql = 'SELECT T.id, T.name, T.description, T.task_value
            FROM {tasks} AS T, {tasks_roles} AS TR
            WHERE TR.role_id = ?
            AND TR.task_id = T.id
            ';
    $data = array($role_id);
    $res = Dal::query($sql, $data);
    if ($res->numRows() <=0 ) {
      return FALSE;
    } else {
      while ( $row = $res->fetchRow($fetch_mode) ) {
        $tasks[] = $row;
      }
    }
    Logger::log("Exit: function Roles::get_tasks_of_role");
    return $tasks;
  }
}
?>
