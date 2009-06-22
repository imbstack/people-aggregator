<?php
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Comment/Comment.php";
require_once "api/Permissions/PermissionsHandler.class.php";

if ($_GET['comment_id']) {
  $comment_id = trim($_GET['comment_id']);
  $comment = new Comment();
  $comment->load($comment_id);

  if($comment->parent_type == TYPE_USER) {
     $recipient_id = $comment->parent_id;
     $redirect_url = PA::$url . PA_ROUTE_USER_PUBLIC . "/" . $comment->parent_id;
  } else if($comment->parent_type == TYPE_CONTENT) {
     $redirect_url = PA::$url . PA_ROUTE_CONTENT . "/cid=" . $comment->content_id;
  } else if(!empty($_REQUEST['back_page'])) {
     $redirect_url = $_REQUEST['back_page'];
  }
  $cid = $comment->content_id;//Content id for which comment has been posted.

  $params = array('comment_info'=>array('user_id'=>$comment->user_id, 'content_id'=>$comment->content_id, 'recipient_id' =>$recipient_id), 'permissions'=>'delete_comment');

  if(PermissionsHandler::can_user(PA::$login_uid, $params)) {
     $comment = new Comment();
     $comment->comment_id =  $comment_id;
     $comment->delete();
  } else {
      throw new PAException(CONTENT_NOT_AUTHORISED_TO_ACCESS, "You are not authorised to access this page.");
  }

  $msg = 7025;
  //invalidate cache of this content
  if ($network_info) {
    $nid = '_network_'.$network_info->network_id;
  } else {
    $nid='';
  }

 //unique name
  $cache_id = 'content_'.$cid.$nid;
  CachedTemplate::invalidate_cache($cache_id);
  $redirect_url .= "&msg_id=$msg";

/*
  $redirect_url = PA::$url . PA_ROUTE_CONTENT . "/cid=$cid&msg_id=$msg";
  if (isset($_GET['comment'])) {
    $redirect_url = $_SERVER['HTTP_REFERER'];
  }
  // TODO : Make Generic Redirection

  if (preg_match("/media_full_view/i", $_SERVER['HTTP_REFERER'])) {
    $redirect_url = $_SERVER['HTTP_REFERER'].'&msg_id=7025';
  }

  if (preg_match("/user_blog/i", $_SERVER['HTTP_REFERER'])) {
    $redirect_url = $_SERVER['HTTP_REFERER'].'&msg_id=7025';
  }
*/

  header("Location:$redirect_url");
  exit;
}

?>
