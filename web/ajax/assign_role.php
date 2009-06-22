<?php
$login_required = TRUE;
require_once dirname(__FILE__).'/../../config.inc';
include_once("web/includes/page.php");
require_once "api/Roles/Roles.php";
require_once "api/User/User.php";

// echo "Error <pre>" . print_r($_POST, 1) . "</pre>";
if ($_POST['uid']) {
    $msg = null;
    $uid = (int)$_POST['uid'];
    $role_id = (int)$_POST['rid'];
    $group_id = (!empty($_REQUEST['gid']) && ($_REQUEST['gid'] != '-1')) ? $_REQUEST['gid'] : null;

    if(!empty($_POST['role_extra'])) {
      $role_extra = $_POST['role_extra'];
      foreach($role_extra as $_role_id => &$extra) {
        $_groups = array();
        $extra['user'] = (!empty($extra['user'])) ? $extra['user'] : 0;
        $extra['network'] = (!empty($extra['network'])) ? $extra['network'] : 0;
        if(!empty($extra['groups'])) {
          foreach($extra['groups'] as $key => $value) {
            if($value) {
              $_groups[] = $key;
            }
          }
        }
        $extra['groups'] = $_groups;
      }
    }

    $user_roles = array();
    $role = new Roles();
    $_extra = serialize(array('user' => true, 'network' => true, 'groups' => array()));
    if(!empty($role_extra[$role_id])) {
      $_extra = serialize($role_extra[$role_id]);
    } else {
      if($group_id) {
        $_extra = array( 'user' => false, 'network' => false, 'groups' => array($group_id) );
        $_extra = serialize($_extra);
      }
    }
    $user_roles[] = array('role_id' => $role_id, 'extra' => $_extra);

    if($_POST['roles_action'] == 'delete') {
      $role->delete_user_roles($uid, $user_roles, $group_id);
    } else {
      $role->assign_role_to_user($user_roles, $uid) ;
    }
    $names = array();
    $r_params = ($group_id) ? array('type' => 'group', 'gid' => $group_id) : null;
    $saved_roles  = Roles::get_user_roles($uid, DB_FETCHMODE_OBJECT, $r_params);
    foreach($saved_roles as $s_role) {
      $names[] = Roles::get_role_name($s_role->role_id);
    }
    $msg = implode("<br />", $names);
    echo $msg;
}
?>
