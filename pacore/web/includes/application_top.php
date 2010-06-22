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
/**
Purpose of file: To initialize common variables
**/
//find out the session of user
// Set user session related variables here
// Make sure that variable names on different files dont conflict
if(!@$_GET['uid']) {
    $uid = @$_SESSION['user']['id'];
}
else {
    $uid = $_GET['uid'];
}
if(isset($_SESSION['user']['id']) && $uid == $_SESSION['user']['id']) {
    $my_page = TRUE;
}
else {
    $my_page = FALSE;
}
if(!empty($_SESSION['user']['id'])) {
    $logged_in_id = $_SESSION['user']['id'];
}
else {
    $logged_in_id =-1;
}

/*
* Variable Used for Paging: Starts
*/
if(!isset($_GET['page']) || $_GET['page'] == '') {
    $pagination_page = 1;
}
else {
    $pagination_page = $_GET['page'];
}
$paging['page'] = $pagination_page;
$show = (isset($_GET['show']) && $_GET['show'] != '') ? $_GET['show'] : 20;
//it is limit how many records to show on a single page
$paging['show'] = $show;
$sort_by        = (isset($_GET['sort_by']) && $_GET['sort_by'] != '') ? $_GET['sort_by'] : 'changed';
$direction      = (isset($_GET['direction']) && $_GET['direction'] != '') ? $_GET['direction'] : 'DESC';

/*
* Variable Used for Paging: Ends
*/
//used in pabase not sure will be used in alpha
if(!empty($uid)) {
    $user = new User();
    try {
        $user->load((int) $uid);
    }
    catch(PAException$e) {
        $msg   = "Error occured in retreiving user information\n";
        $msg  .= "<br><center><font color=\"red\">".$e->message."</font></center>";
        $error = TRUE;
    }
    $login_name   = $user->login_name;
    $first_name   = $user->first_name;
    $last_name    = $user->last_name;
    $email        = $user->email;
    $user_picture = $user->picture;
}
//..eof find out the session of user
// generate html title
if(isset($_SESSION['user'])) {
    $HTML_TITLE = $first_name.' '.$last_name;
    $HTML_TITLE .= " | PeopleAggregator Demo ";
}
else {
    $HTML_TITLE = " PeopleAggregator Demo ";
}
// ..eof generate html title
require_once "api/ImageResize/ImageResize.php";
?>
