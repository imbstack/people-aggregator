<?php
$login_required = TRUE;
require_once dirname(__FILE__).'/../../config.inc';
include_once("web/includes/page.php");
require_once "api/Roles/Roles.php";
require_once "api/User/User.php";

if ($_POST['uid']) {
  $msg = null;
  $user_id = (int)$_POST['uid'];
  $roles = $_REQUEST['role_extra'];
  foreach($roles as $role_id => &$extra) {
    $_groups = array();
    $extra['user'] = (bool)$extra['user'];
    $extra['network'] = (bool)$extra['network'];
    foreach($extra['groups'] as $key => $value) {
      if($value) {
        $_groups[] = $key;
      }
    }
    $extra['groups'] = $_groups;
    try {
      $sql = 'UPDATE {users_roles} SET extra = ? WHERE user_id = ? AND role_id = ?';
      $data = array(serialize($extra), $user_id, $role_id);
      Dal::query($sql, $data);
      Dal::commit();
    } catch (PAException $e) {
      Dal::rollback();
      $msg = "Error: " . $e->getMessage();
      echo $msg;
    }
    
  }
  echo "Ok";
}
?>