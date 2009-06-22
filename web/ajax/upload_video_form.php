<?php
// sadly we need to switch off any error reporting
// so that this page can be pulled by a PHP script remotely
error_reporting(0);
$login_required = FALSE;
$login_never_required = TRUE;

include_once("web/includes/page.php");
require_once PA::$blockmodule_path.'/UploadMediaModule/UploadMediaModule.php';
$obj = new UploadMediaModule;

$obj->mode = 'Videos';
$obj->view = 'remote';

if (!empty($_REQUEST['gid'])) {
  $obj->group_id = $_REQUEST['gid'];
}

echo $obj->render();

?>
