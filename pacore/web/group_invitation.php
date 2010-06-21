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
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Group/Group.php";
global $login_uid, $login_name;
// condition for checking the user _ group
$cnt = Group::get_user_groups($login_uid, TRUE);
if(empty($cnt)) {
    $location = PA::$url.PA_ROUTE_GROUPS.'/msg_id=6005';
    header("Location: $location");
    exit;
}

function setup_module($column, $module, $obj) {
    global $login_uid, $entered_people;
    switch($column) {
        case 'middle':
            $obj->mode       = PUB;
            $obj->uid        = $login_uid;
            $obj->block_type = 'media_management';
            if($module == 'InvitationStatusModule') {
                $groups = Group::get_user_groups($login_uid, FALSE, 'ALL');
                $user_groups = array();
                for($i = 0; $i < count($groups); $i++) {
                    $user_groups[] = $groups[$i]['gid'];
                }
                if(!empty($_REQUEST['gid'])) {
                    $obj->collection_id_array = array(
                        $_REQUEST['gid'],
                    );
                }
                else {
                    $obj->collection_id_array = array(
                        $user_groups[0],
                    );
                }
            }
            break;
    }
}
$page = new PageRenderer("setup_module", PAGE_GROUP_INVITE, "Invite people into a group", "container_three_column.tpl", 'header.tpl', PRI, HOMEPAGE, PA::$network_info);
uihelper_error_msg(@$_GET['msg_id']);
uihelper_get_network_style();
echo $page->render();
?>
