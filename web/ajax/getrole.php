<?php
$login_required = TRUE;
include_once("web/includes/page.php");
require_once "api/Roles/Roles.php";
if ($_POST['id']){
  $role = new Roles();
  $role->id = $_POST['id'];
  $roles = $role->get($_POST['id']);
  $div_generate = '<fieldset class="center_box"><input type="hidden"
name="role_id" value="'.$_POST['id'].' "/><div class="field"><h4>Name   
</h4><input type="text" name="role_name" class="text longer" value =
"'.$roles->name.'" /></div><div class="field_bigger"><h4>Description :
</h4><textarea name="desc">'.$roles->description .'</textarea></fieldset><div
class="button_position"><input type="submit" class="button-submit" name="submit"
value="Save" /></div>';
}
echo $div_generate;
?>