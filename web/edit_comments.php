<?
echo "This script has huge security problems, so has been disabled.  It's also not used in the Beta theme, so you should never get here.";
exit;

$login_required = TRUE;
include_once("web/includes/page.php");
require_once "../api/MessageBoard/MessageBoard.php";

$back = $_REQUEST['back_page'];
//print_r($_REQUEST);exit;
$mid = trim($_REQUEST['message_id']);
if ($_REQUEST['do'] == 'edit') {
  filter_all_post($_REQUEST);  
  $title = trim($_REQUEST['edit_title']);
  $body = trim($_REQUEST['edit_body']);
  
  $m = new MessageBoard();
  $m->title = $title;
  $m->body = $body;
  $m->boardmessage_id = $mid;
  $id = $m->save($uid=NULL,$is_insert=0);
}
if ($_REQUEST['do'] == 'delete') {
  
  MessageBoard::delete_all_in_parent($mid,PARENT_TYPE_MESSAGE);
}
if ($_REQUEST['groupurl']) {
  $url = $_REQUEST['groupurl'];
  header("Location:$url"); exit;
}
header("location:$back");exit;
?>