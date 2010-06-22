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
$login_required = FALSE;
$use_theme = 'Beta';
include_once("web/includes/page.php");
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Comment/Comment.php";
require_once "api/Group/Group.php";
require_once "api/Category/Category.php";
$request_info       = load_info();
$parent_id          = $request_info['parent_id'];
$parent_name_hidden = $request_info['parent_name_hidden'];
$parent_type        = $request_info['parent_type'];
$header_title       = $request_info['header_title'];
//get details of group
if($_REQUEST['ccid']) {
    //get details of group
    include_once 'web/includes/blocks/group_helper.php';
    //..get details of group ends
}
//..ccid
$group = ContentCollection::load_collection((int) $gid, $_SESSION['user']['id']);
if(isset($_POST['submit']) && (($group->access_type == 1 && Group::member_exists((int) $_GET['ccid'], (int) $_SESSION['user']['id'])) || ($group->access_type == 0))) {

    /* Function for Filtering the POST data Array */
    filter_all_post($_POST);
    $error = FALSE;
    $msg   = '';
    $title = trim($_POST['title_form']);
    $body  = trim($_POST['comment']);
    $name  = '';
    $email = '';
    if(isset($_POST['name'])) {
        if(empty($_POST['name'])) {
            $error = TRUE;
            $msg[] = "Please enter your name";
        }
        else {
            $name = trim($_POST['name']);
        }
    }
    if(isset($_POST['email'])) {
        if(empty($_POST['email'])) {
            $error = TRUE;
            $msg[] = "Please enter your email address";
        }
        elseif(!validate_email($_POST['email'])) {
            $error = TRUE;
            $msg[] = "Please enter a valid email address";
        }
        else {
            $email = trim($_POST['email']);
        }
    }
    if($title == '') {
        $error = TRUE;
        $msg[] = "-> Please specify title for your comments.";
    }
    if($body == '') {
        $error = TRUE;
        $msg[] = "-> Please enter your comments.";
    }
    if(!$error) {
        $cat_obj = new MessageBoard();
        $cat_obj->set_parent($parent_id, $parent_type);
        $cat_obj->title     = $title;
        $cat_obj->body      = $body;
        $cat_obj->user_id   = $uid;
        $cat_obj->user_name = $name;
        $cat_obj->email     = $email;
        if(!$_POST['chk_allow_anonymous']) {
            $cat_obj->allow_anonymous = 0;
        }
        else {
            $cat_obj->allow_anonymous = 1;
        }
        try {
            $mid = $cat_obj->save($_SESSION['user']['id']);
        }
        catch(PAException$e) {
            $msg   = "Error occured in saving thread\n";
            $msg  .= "<br><center><font color=\"red\">".$e->message."</font></center>";
            $error = TRUE;
        }
    }
    if($mid) {
        //echo 'data has been saved';
        if($_GET['ccid']) {
            header("Location: ".PA::$url."/forum_messages.php?mid=$parent_id&ccid=".$_GET['ccid']);
        }
        else {
            header("Location: ".PA::$url."/forum_messages.php?mid=$parent_id");
        }
        exit;
    }
}
elseif(isset($_POST['submit']) && !(Group::member_exists((int) $_GET['ccid'], (int) $_SESSION['user']['id']))) {
    $msg = "You are not a member of this group.";
    $error = TRUE;
}
$setting_data = ModuleSetting::load_setting(PAGE_FORUM_MESSAGES, $uid);
if($_REQUEST['ccid']) {
    array_unshift($setting_data['left'], 'GroupAccessModule', 'MembersFacewallModule');
    array_unshift($setting_data['right'], 'GroupStatsModule', 'RecentPostModule');
}
$param['action'] = 'edit_forum';

function setup_module($column, $moduleName, $obj) {
    global $request_info, $title, $body, $name, $email, $paging, $msg, $error;
    global $group_details, $users, $param;
    switch($moduleName) {
        case 'GroupAccessModule':
        case 'GroupStatsModule':
            $obj->group_details = $group_details;
            break;
        case 'MembersFacewallModule':
            $obj->group_details = $group_details;
            $obj->mode          = PRI;
            $obj->block_type    = HOMEPAGE;
            $obj->links         = $users;
            $obj->gid           = $_REQUEST['ccid'];
            break;
        case 'RecentPostModule':
            $obj->block_type    = HOMEPAGE;
            $obj->type          = 'group';
            $obj->mode          = PRI;
            $obj->gid           = $_REQUEST['ccid'];
            $obj->group_details = $group_details;
            break;
        case 'GroupForumPermalinkModule':
            return "skip";
            // This module is skip, Now this module has been implemented using dynamic.php
            global $group_top_mesg;
            $gid       = $_REQUEST['ccid'];
            $group     = ContentCollection::load_collection((int) $gid, $_SESSION['user']['id']);
            $is_member = Group::member_exists((int) $gid, $_SESSION['user']['id']);
            $is_admin  = Group::is_admin((int) $gid, $_SESSION['user']['id']);
            if($group->reg_type == REG_INVITE && !$is_member && !$is_admin && !user_can($param)) {
                $msg = 9005;
                return "skip";
            }
            $obj->is_member          = $is_member;
            $obj->parent_id          = $request_info['parent_id'];
            $obj->parent_name_hidden = $request_info['parent_name_hidden'];
            $obj->parent_type        = $request_info['parent_type'];
            $obj->header_title       = $request_info['header_title'];
            $obj->title_form         = $title;
            $obj->body               = $body;
            $obj->name               = $name;
            $obj->email              = $email;
            $obj->Paging["page"]     = $paging["page"];
            $obj->Paging["show"]     = 5;
            //five records
            if($error) {
                $obj->msg = $msg;
            }
            break;
    }
}
$page = new PageRenderer("setup_module", PAGE_FORUM_MESSAGES, "Message forum", "container_three_column.tpl", "header_group.tpl", PUB, HOMEPAGE, PA::$network_info, '', $setting_data);
uihelper_error_msg($msg);
uihelper_get_group_style($_REQUEST['ccid']);
echo $page->render();
?>