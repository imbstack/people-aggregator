<?php
$login_required = TRUE;
require_once dirname(__FILE__).'/../../config.inc';
include_once("web/includes/page.php");
require_once "api/Roles/Roles.php";
if ($_POST['id']){
  $msg = __("Role can't be deleted.");
  $role = new Roles();
  if($role->delete((int)$_POST['id'])) {
    $msg = __("Successfully deleted.");
  }
}
echo $msg;
?>