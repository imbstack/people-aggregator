<?php
/**
* File to delete content . Parameters required to pass are
'back_page' => the page which will be redirected to after deletion.
'cid'=> content_id of the content that is to be deleted.
**/
$login_required = TRUE;
include_once("web/includes/page.php"); 
require_once "api/Content/Content.php";
require_once "api/Permissions/PermissionsHandler.class.php";
$location = $_REQUEST['back_page'];

if(!empty($_GET['cid'])) {
  $params = array( 'permissions'=>'delete_content', 'cid'=>$_GET['cid'] );
  if(PermissionsHandler::can_user(PA::$login_uid, $params)) {
    Content::delete_by_id($_GET['cid']);
  } else {
    $location .= (strpos($location ,'?') !== false) ? '&msg_id=7033' : '?msg_id=7033';
    header("Location: $location");
    exit;
  }
  if($network_info) {
    $nid = '_network_'.$network_info->network_id;
    } else {
    $nid='';
  }
  //unique name
  $cache_id = 'content_'.$_GET['cid'].$nid; 
  CachedTemplate::invalidate_cache($cache_id);
  $location .= (strpos($location ,'?') !== false) ? '&msg_id=7024' : '?msg_id=7024';
  header("Location: $location");
  exit;
} else {
  header("Location: $location");
  exit;
}
?>