<?php
$login_required = TRUE;
include_once("web/includes/page.php");
require_once "api/Content/Content.php";
require_once "ext/Announcement/Announcement.php";
require_once "api/Permissions/PermissionsHandler.class.php";

if($_GET['aid'] ) {
  $params = array( 'permissions'=>'delete_content', 'cid'=>$_GET['aid'] );
  if(PermissionsHandler::can_user(PA::$login_uid, $params)) {
    $announcement = new Announcement;
    $announcement->content_id = $_GET['aid'];
    $announcement->delete();
  }
}

if(PA::$network_info) {

  $nid = PA::$network_info->network_id;
  } else {
  $nid='';
}
//unique name
$cache_id = 'content_'.$_GET['cid'].$nid;
CachedTemplate::invalidate_cache($cache_id);
$location = PA::$url .'/network_announcement.php';
header("Location: $location");
exit;
?>
