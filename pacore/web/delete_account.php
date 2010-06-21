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
require_once "api/Theme/Template.php";
global $login_uid;
if(PA::$network_info->type == MOTHER_NETWORK_TYPE) {
    $delete = trim($_POST['Delete']);
    if($delete == 'Delete') {
        // and go home :)
        header("Location: delete_user.php?msg=own_delete");
        exit();
    }
    else {
        $msg = 7030;
    }
}
else {
    $msg = 7032;
}
header("Location: ".PA::$url.PA_ROUTE_EDIT_PROFILE."?type=delete_account&msg_id=$msg");
?>
