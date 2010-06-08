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
$use_theme = 'Beta';

include_once("web/includes/page.php");
require_once "web/includes/functions/user_page_functions.php";
require_once "ext/Image/Image.php";
require_once "ext/Audio/Audio.php";
require_once "api/Video/Video.php";

// for query count
global $query_count_on_page;
$query_count_on_page = 0;

$type = @$_GET['type'];
function setup_module($column, $moduleName, $obj) {
    global $type, $paging;
    global $login_uid; 
    switch ($column) {
    case 'middle':
      $obj->group_id = $_REQUEST['gid'];
      $obj->uid = $login_uid;
      $obj->type = $type;
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 50;
    break;
    }
}

$page = new PageRenderer("setup_module", PAGE_MANAGE_GROUP_CONTENT, "Group Content Management", "container_one_column_media_gallery.tpl", "header_group.tpl", PUB, HOMEPAGE, PA::$network_info);
$error_message = @$_GET['msg_id'];
uihelper_error_msg($error_message);
uihelper_get_group_style($_GET['gid']);
echo $page->render();

?>