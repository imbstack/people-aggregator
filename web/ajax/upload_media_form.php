<?php
$login_required = FALSE;
$login_never_required = TRUE;
// error_reporting(0);
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once(PA::$blockmodule_path."/UploadMediaModule/UploadMediaModule.php");

$obj = new UploadMediaModule();
$obj->mode = $obj->type = (!empty($_REQUEST['type'])) ? $_REQUEST['type'] : 'Images';
$obj->view = 'ajax';

if (!empty($_REQUEST['gid'])) {
  $obj->group_id = $_REQUEST['gid'];
} 
$obj->user_id = PA::$login_uid;

echo $obj->render();

?>