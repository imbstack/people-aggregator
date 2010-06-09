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

$error = FALSE;
filter_all_post($_form);
if (isset($_form['comment']) && empty($_form['comment'])) {
  $error = TRUE;
  $failure_message = 9024;
}

if ($_form && empty($error)) {
  $comment = new Comment();
  $usr = get_login_user();
  $comment->comment = $_form['comment'];
  $comment->subject = $_form['comment'];
  $comment->parent_type = TYPE_USER;
  $comment->parent_id = $_form['id'];
  $comment->content_id = $_form['id'];
  $comment->user_id = $usr->user_id;
  $comment->name = $usr->login_name;
  $comment->email = $usr->email;
  $id = $_form['id'];
  if ($comment->spam_check()) {
      $failure_message = 9021;
      Logger::log("Comment rejected by spam filter", LOGGER_ACTION);
  }
  else {
      $success_message = 9022;
      $comment->save_comment();
    if ($comment->spam_state != SPAM_STATE_OK) {
        $failure_message = 9023;
    }
    else {
      unset($_form);
      if (PA::$network_info) {
        $nid = '_network_'.PA::$network_info->network_id;
      }
      else {
        $nid='';
      }
      $cache_id = 'content_'.$id.$nid; 
      CachedTemplate::invalidate_cache($cache_id);
    }
  }
  
}

// Here we call the function
$msg_array = array();
$msg_array['failure_msg'] = $failure_message;
$msg_array['success_msg'] = $success_message;
$login = User::get_login_name_from_id($_GET['uid']);

$current_url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login;

/*
$current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$_GET['uid'];
$url_perms = array('current_url' => $current_url,
                    'login' => $login                  
                  );
$url = get_url(FILE_USER_BLOG, $url_perms);
$redirect_url = $url;
*/

$redirect_url = $current_url;

set_web_variables($msg_array, $redirect_url);
?>