<?php
$login_required = FALSE;
$login_never_required = TRUE;
error_reporting(0);
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include "includes/page.php";
require_once PA::$blockmodule_path.'/UploadMediaModule/UploadMediaModule.php';
$obj = new UploadMediaModule;
//uihelper_get_network_style();
$obj->mode = 'Videos';
if(!empty($_GET['gid'])) {
  $obj->group_id = $_GET['gid'];
} else {
  $obj->user_id = $_GET['uid'];
}

$css_array = get_network_css();

echo $obj->render();

?>