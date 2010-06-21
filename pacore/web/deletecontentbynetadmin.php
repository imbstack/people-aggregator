<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
$login_required = TRUE;
include_once("web/includes/page.php");
require_once "api/Content/Content.php";
if($_GET['cid']) {
    Content::delete_by_id($_GET['cid']);
}
if(PA::$network_info) {
    $nid = PA::$network_info->network_id;
}
else {
    $nid = '';
}
//unique name
$cache_id = 'content_'.$_GET['cid'].$nid;
CachedTemplate::invalidate_cache($cache_id);
$location = PA::$url.'/network_manage_content.php?nid='.$nid.'&msg_id=7024';
if(isset($_REQUEST['gid'])) {
    $location = PA::$url.'/manage_group_content.php?gid='.$_GET['gid'].'&msg_id=7024';
}
header("Location: $location");
exit;
?>