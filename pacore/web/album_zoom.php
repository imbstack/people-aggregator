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
include_once("web/includes/page.php");
require_once "api/Theme/Template.php";
if($uid == $_SESSION['user']['id']) {
    $my_page = TRUE;
}
else {
    $my_page = FALSE;
}
if($_SESSION['user']['id']) {
    $logged_in_id = $_SESSION['user']['id'];
}
else {
    $logged_in_id =-1;
}
if($uid) {
    $user = new User();
    try {
        $user->load((int) $uid);
    }
    catch(PAException$e) {
        $msg   = "Error occured in retreiving user information\n";
        $msg  .= "<br><center><font color=\"red\">".$e->message."</font></center>";
        $error = TRUE;
    }
}
print html_header();
?>

<?php
$content = &new Template(CURRENT_THEME_FSPATH."/album_zoom.tpl");
if($error == TRUE) {
    $content->set('msg', $msg);
}
$content->set('users', $users);
$header = &new Template(CURRENT_THEME_FSPATH."/header.tpl");
$header->set('user_name', $first_name." ".$last_name);
$content->set('header', $header);
echo $content->fetch();
$footer = &new Template(CURRENT_THEME_FSPATH."/footer.tpl");
echo $footer->fetch();
?>