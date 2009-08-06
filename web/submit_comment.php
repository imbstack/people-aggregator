<?php

// This file can be included by content.php or POSTed to directly to
// leave a comment on a user or a piece of media.  As such we
// require_once page.php to make sure we have a logged in user.

  $login_required = TRUE;
  require_once "web/includes/page.php"; 

//require_once "web/includes/functions/auto_email_notify.php";
//TO DO: comment should be posted to contents of other network rather then just mother network



global $login_uid;

if (!empty($_POST['addcomment']) && !empty(PA::$login_uid)) {
  
  $ccid_string = "";
      
  if(!empty($_POST['ccid'])) {
      $ccid_string = "&ccid=".$_POST['ccid'];
  }
  $error_message  = "";
  if(strlen(trim(strip_tags($_POST['comment']))) == 0) {
     $error_message = __("Your comment contains some illegal characters. Please try again.")."<br>";
  }
  if ( trim($_POST['comment']) == '' ) {
    $error_message = __("Comment can not be left blank")."<br>";
  }
  if(isset($_POST['name']) && trim($_POST['name'])=='') {
      $error_message .= "Please enter name<br>";
  }
  if(isset($_POST['email']) && trim($_POST['email'])=='') {
      $error_message .= "Please enter email address";
  } else if(isset($_POST['email']) && !validate_email($_POST['email'])) {
      $error_message .= "Please enter a valid email address";
  }
  
  if(strlen($error_message) > 0) {
      $location = PA::$url . PA_ROUTE_CONTENT . "/cid=".$_POST["cid"]."&err=".urlencode($error_message).$ccid_string;
      // header("Location: $location");
      //exit;
  }
  
  /* Function for Filtering the POST data Array */
  filter_all_post($_POST);
    
  if(empty($error_message) ) { // no errors occured
      $comment = new Comment();
      $id = trim($_POST['cid']);
      $comment->content_id = $id;
      $comment->subject = '';
      $comment->comment = trim($_POST['comment']);

      if ($_SESSION['user']['id']) {
        $user = new User();
        $user->load((int)$_SESSION['user']['id']);
        $comment->user_id = $user->user_id;
        $comment->name = '';
        $comment->email = '';
        $comment->homepage = '';
        unset($_GET['err']);
      }
      else {   
        $comment->name = trim($_POST['name']);
        $comment->email = trim($_POST['email']);
        if (!empty($_POST['homepage'])) {
          $comment->homepage = validate_url(trim($_POST['homepage']));
        }
        else {
          $comment->homepage = "";
        }
      }
      // In old method 
      $comment->parent_type = TYPE_CONTENT;
      $comment->parent_id = $id;
      
      if ($comment->spam_check()) {
	  $error_message = __("Sorry, your comment cannot be posted as it looks like spam. Try removing any links to possibly suspect sites, and re-submitting.");
	  Logger::log("Comment rejected by spam filter", LOGGER_ACTION);
      } else {
	  $error_message = __('Your comment has been posted successfully');
	  $comment->save();
	  if ($comment->spam_state != SPAM_STATE_OK) {
	    $error_message = __("Sorry, your comment cannot be posted as it was classified as spam by Akismet, or contained links to blacklisted sites.  Please check the links in your post, and that your name and e-mail address are correct.");
	  } else {
	    //TO DO: comment should be posted to contents of other network rather then just mother network
	    //$params['cid'] = $comment->content_id;
	    //auto_email_notification('comment_posted', $params );
	    //** when uncommenting the above line, don't forget to uncomment the include of auto_email_notify.php at the top of this file too!
	    unset($_POST);
	    //invalidate cache of content block as it is modified now
	    if(PA::$network_info) {
	      $nid = '_network_'.PA::$network_info->network_id;
	    } else {
	      $nid='';
	    }
	    //unique name
	    $cache_id = 'content_'.$id.$nid; 
	    CachedTemplate::invalidate_cache($cache_id);
	  }
      }
  }
}

// Code for submit comments 
// parent_type = ?  it can be "user", "contant_collection", "content", "network" ..etc
// parent_id = ? this is relative to parent_type if type = user than id will be user id  if it content than id will be content_id .....

if (!empty($_POST['submit'])) {
  $error_msg = NULL;
  filter_all_post($_POST);
  
  if (strlen(trim(strip_tags($_POST['comment']))) == 0) {
     $error_message = __("Your comment contains some illegal characters. Please try again.")."<br>";
  }
  
  if ( trim($_POST['comment']) == '' ) {
    $error_message = __("Comment can not be left blank")."<br>";
  }
  
  if (empty($error_message)) {
    $comment = new Comment();
    // setting some variables
    $usr = get_user();
    $comment->comment = $_POST['comment'];
    $comment->subject = $_POST['comment'];
    $comment->parent_type = TYPE_CONTENT;
    $comment->parent_id = $_POST['id'];
    $comment->content_id = $_POST['id'];
    $comment->user_id = $usr->user_id;
    $comment->name = $usr->login_name;
    $comment->email = $usr->email;
    $id = $_POST['id'];
    
    if ($comment->spam_check()) {
      $error_message = __("Sorry, your comment cannot be posted as it looks like spam. Try removing any links to possibly suspect sites, and re-submitting.");
      Logger::log("Comment rejected by spam filter", LOGGER_ACTION);
    }
    else {
      $error_message = __('Your comment has been posted successfully');
      $comment->save_comment();
      if ($comment->spam_state != SPAM_STATE_OK) {
        $error_message = __("Sorry, your comment cannot be posted as it was classified as spam by Akismet, or contained links to blacklisted sites. Please check the links in your post, and that your name and e-mail address are correct.");
      }
      else {
        //TO DO: comment should be posted to contents of other network rather then just mother network
        //$params['cid'] = $comment->content_id;
        //auto_email_notification('comment_posted', $params );
        //** when uncommenting the above line, don't forget to uncomment the include of auto_email_notify.php at the top of this file too!
        unset($_POST);
        //invalidate cache of content block as it is modified now
        if(PA::$network_info) {
          $nid = '_network_'.PA::$network_info->network_id;
        } else {
          $nid='';
        }
        //unique name
        $cache_id = 'content_'.$id.$nid; 
        CachedTemplate::invalidate_cache($cache_id);
      }
    }
    
    
  }
  $location = $_SERVER['HTTP_REFERER'].'&msg_id='.$error_message;
  header("Location: $location");
  exit;
}
?>