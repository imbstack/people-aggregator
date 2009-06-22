<?php
$login_required = TRUE;
include_once("web/includes/page.php");
require_once "api/Theme/Template.php";
global $network_info, $login_uid;

if ($network_info->type == MOTHER_NETWORK_TYPE) {
  $delete = trim($_POST['Delete']);
  if($delete == 'Delete') {
       // and go home :)
    header("Location: delete_user.php?msg=own_delete");
    exit();
  } else {
    $msg = 7030;
  }
} else  {
  $msg = 7032;
}
header("Location: edit_profile.php?type=delete_account&msg_id=$msg");
?>
