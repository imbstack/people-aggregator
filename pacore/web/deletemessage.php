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
require_once '../api/Message/Message.php';
if($_GET['action'] == 'delete' || isset($_POST['delete'])) {
    $page_number = $_POST['page_number'];
    if($_POST['folder_id']) {
        $message_id = explode(",", $_POST['message_ids']);
        foreach($message_id as $mid) {
            if($mid) {
                $id[] = trim($mid);
            }
        }
        foreach($id as $mid) {
            if(isset($_POST[$mid])) {
                $del = explode("-", $mid);
                $message_del_id[] = $del[0];
            }
        }
        if($message_del_id) {
            Message::delete_message($message_del_id);
        }
        else {
            $message = __('There was a problem:<br>No message(s) selected. Please select at least one message and try again.');
        }
        $name = Message::get_folder_by_id($_POST['folder_id']);
        header("Location: ".PA_ROUTE_MYMESSAGE."/page_no=$page_number&result=$message&folder_name=".$name['name']);
    }
    if($_POST['mid']) {
        $del = explode("-", $_POST['mid']);
        $message_del_id[] = $del[0];
        Message::delete_message($message_del_id);
        $name = Message::get_folder_by_id($_POST['fid']);
        header("Location: ".PA_ROUTE_MYMESSAGE."/page_no=1&folder_name=".$name['name']);
    }
}
if(isset($_POST['search_delete'])) {
    $message_id = explode(",", $_POST['message_ids']);
    foreach($message_id as $mid) {
        if($mid) {
            $id[] = trim($mid);
        }
    }
    foreach($id as $mid) {
        if(isset($_POST[$mid])) {
            $message_del_id[] = $mid;
        }
    }
    if(empty($message_del_id)) {
        $loc = $_SERVER['HTTP_REFERER'];
        $loc .= '&err=1';
        header("Location: ".$loc);
    }
    else {
        foreach($message_del_id as $id) {
            $data = explode("-", $id);
            $del_id[] = $data[2];
            Message::delete_message($del_id);
            $loc = $_SERVER['HTTP_REFERER'];
            $loc .= '&err=0';
            header("Location: ".$loc);
        }
    }
}
if($_GET['action'] == 'move' || isset($_POST['move'])) {
    if($_POST['folder_id']) {
        $page_number = $_POST['page_number'];
        $message_id = explode(",", $_POST['message_ids']);
        foreach($message_id as $mid) {
            if($mid) {
                $id[] = trim($mid);
            }
        }
        foreach($id as $mid) {
            if(isset($_POST[$mid])) {
                $selected[]        = $mid;
                $del               = explode("-", $mid);
                $message_move_id[] = $del[0];
            }
        }
        if($_POST['move_folder'] == 'select folder') {
            $sel_message = implode(",", $selected);
            $name        = Message::get_folder_by_id($_POST['folder_id']);
            $message     = __('There was a problem:').'<br>'.__('No folder selected. Please select a folder and try again.');
            header("Location: ".PA_ROUTE_MYMESSAGE."/sel_message=$sel_message&page_no=$page_number&result=$message&folder_name=".$name['name']);
        }
        else {
            if($message_move_id) {
                Message::move_message_to_folder($message_move_id, $_POST['move_folder']);
                $name = Message::get_folder_by_id($_POST['move_folder']);
                header("Location: ".PA_ROUTE_MYMESSAGE."/page_no=1&folder_name=".$name['name']);
            }
            else {
                $name = Message::get_folder_by_id($_POST['folder_id']);
                $message = __('There was a problem:').'<br>'.__('No message(s) selected. Please select at least one message and try again.');
                header("Location: ".PA_ROUTE_MYMESSAGE."/page_no=$page_number&result=$message&folder_name=".$name['name']);
            }
        }
    }
    if($_POST['mid']) {
        if($_POST['move_folder'][0] == 'select folder' && $_POST['move_folder'][1] == 'select folder') {
            $del = explode("-", $_POST['mid']);
            $message = __('There was a problem:').'<br>'.__('No folder selected. Please select a folder and try again.');
            header("Location: ".PA_ROUTE_MYMESSAGE."/result=$message&index_id=".$del[0]."&mid=".$del[1]."&fid=".$_POST['fid']);
        }
        else {
            if($_POST['move_folder'][0] != 'select folder') {
                $move_folder = $_POST['move_folder'][0];
            }
            elseif($_POST['move_folder'][1] != 'select folder') {
                $move_folder = $_POST['move_folder'][1];
            }
            $del = explode("-", $_POST['mid']);
            $message_move_id[] = $del[0];
            Message::move_message_to_folder($message_move_id, $move_folder);
            $name = Message::get_folder_by_id($move_folder);
            header("Location: ".PA_ROUTE_MYMESSAGE."/page_no=1&folder_name=".$name['name']);
        }
    }
}
if(isset($_POST['search_move'])) {
    if($_POST['move_folder'] == 'select folder') {
        header("Location: ".$_SERVER['HTTP_REFERER']);
    }
    else {
        $message_id = explode(",", $_POST['message_ids']);
        foreach($message_id as $mid) {
            if($mid) {
                $id[] = trim($mid);
            }
        }
        foreach($id as $mid) {
            if(isset($_POST[$mid])) {
                $message_move_id[] = $mid;
            }
        }
        foreach($message_move_id as $id) {
            $data = explode("-", $id);
            $move_id[] = $data[2];
            Message::move_message_to_folder($move_id, $_POST['move_folder']);
        }
        header("Location: ".$_SERVER['HTTP_REFERER']);
    }
}
?>