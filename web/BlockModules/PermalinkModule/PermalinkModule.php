<?php
require_once "api/Permissions/PermissionsHandler.class.php";
require_once "api/Activities/Activities.php";
require_once "api/Messaging/MessageDispatcher.class.php";

class PermalinkModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_single_wide_module.tpl';

  public  $content_id, $content;

  function __construct() {
    //$this->title = __("Post Permalink");
    $this->main_block_id = "mod_permalink";
    $this->html_block_id = "PermalinkModule";
  }

  function initializeModule($request_method, $request_data) {
    if(!empty($this->shared_data['content_info'])) {
      $this->content_id = $this->shared_data['content_info']->content_id;
    } else if(!empty($request_data['cid'])) {
      $this->content_id = $request_data['cid'];
    } else {
      return 'skip';
    }
  }

  function handleRequest($request_method, $request_data) {
    if(!empty($request_data['action'])) {
      $action = $request_data['action'];
      $class_name = get_class($this);
      switch($request_method) {
        case 'POST':
          $method_name = 'handlePOST_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
          }
        break;
        case 'GET':
          $method_name = 'handleGET_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
          }
        break;
        case 'AJAX':
          $method_name = 'handleAJAX_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
          }
        break;
      }
    }
  }

  private function handleGET_deleteContent($request_data) {
    $location = $request_data['back_page'];
    if(!empty($request_data['cid']) && !empty(PA::$login_uid)) {
      $params = array('permissions' => 'delete_content',
                      'uid'    => PA::$login_uid,
                      'cid'    => $request_data['cid']
                );
      if(PermissionsHandler::can_user(PA::$login_uid, $params)) {
        Content::delete_by_id($request_data['cid']);
        Activities::update(array('type' => 'user_post_a_blog',
                                 'subject' => PA::$login_uid,
                                 'object' => $request_data['cid']), 'deleted');
      } else {
        $location .= '&msg_id=7033';
        $this->controller->redirect($location);
        exit;
      }
      if(PA::$network_info) {
        $nid = '_network_' . PA::$network_info->network_id;
      } else {
        $nid='';
      }
      //unique name
      $cache_id = 'content_'.$request_data['cid'].$nid;
      CachedTemplate::invalidate_cache($cache_id);
      $location .= '&msg_id=7024';
      $this->controller->redirect($location);
      exit;
    } else {
      $this->controller->redirect($location);
      exit;
    }
  }


  function handlePOST_submitComment($request_data) {
    global $error_msg;

    if(($request_data['action'] == 'submitComment') && !empty(PA::$login_uid)) {
      $ccid_string = "";
      if(!empty($request_data['ccid'])) {
        $ccid_string = "&ccid=".$request_data['ccid'];
      }
      $error_msg  = "";
      if(strlen(trim(strip_tags($request_data['comment']))) == 0) {
        $error_msg = "Your comment contains some illegal characters. Please try again.<br>";
      }
      if( trim($request_data['comment']) == '' ) {
        $error_msg = "Comment can not be left blank<br>";
      }
      if(isset($request_data['name']) && trim($request_data['name'])=='') {
        $error_msg .= "Please enter name<br>";
      }
      if(isset($request_data['email']) && trim($request_data['email'])=='') {
        $error_msg .= "Please enter email address";
      } else if(isset($request_data['email']) && !validate_email($request_data['email'])) {
        $error_msg .= "Please enter a valid email address";
      }
/*
      if(strlen($error_msg) > 0) {
        $location = PA::$url . PA_ROUTE_PERMALINK . "/cid=" . $request_data["cid"];
        $this->controller->redirect($location);
      }
*/
      /* Function for Filtering the POST data Array */
      filter_all_post($request_data);

      if(empty($error_msg) ) { // no errors occured
        $comment = new Comment();
        $id = trim($request_data['cid']);
        $comment->content_id = $id;
        $comment->subject = '';
        $comment->comment = trim($request_data['comment']);

        if(PA::$login_uid) {
          $user = new User();
          $user->load((int)PA::$login_uid);
          $comment->user_id = $user->user_id;
          $comment->name = '';
          $comment->email = '';
          $comment->homepage = '';
          unset($request_data['err']);
        } else {
          $comment->name = trim($request_data['name']);
          $comment->email = trim($request_data['email']);
          if(!empty($request_data['homepage'])) {
            $comment->homepage = validate_url(trim($request_data['homepage']));
          } else {
            $comment->homepage = "";
          }
        }
        // In old method
        $comment->parent_type = TYPE_CONTENT;
        $comment->parent_id = $id;

        if($comment->spam_check()) {
          $error_msg = "Sorry, your comment cannot be posted as it looks like spam.  Try removing any links to possibly suspect sites, and re-submitting.";
          Logger::log("Comment rejected by spam filter", LOGGER_ACTION);
        } else {
          $error_msg = 'Your comment has been posted successfully';
          $comment->save();
          if($comment->spam_state != SPAM_STATE_OK) {
            $error_msg = "Sorry, your comment cannot be posted as it was classified as spam by Akismet, or contained links to blacklisted sites.  Please check the links in your post, and that your name and e-mail address are correct.";
          } else {

            //for rivers of people
            $activity = 'user_post_a_comment';
            $activity_extra['info'] = ($user->display_name.'has left a comment');
            $activity_extra['comment_id'] = $comment->comment_id;
            $activity_extra['content_url'] = PA::$url . PA_ROUTE_CONTENT . "/cid=$id";
            $extra = serialize($activity_extra);
            Activities::save($user->user_id, $activity, $comment->comment_id, $extra);

            //TO DO: comment should be posted to contents of other network rather then just mother network
            //$params['cid'] = $comment->content_id;
            //auto_email_notification('comment_posted', $params );
            //** when uncommenting the above line, don't forget to uncomment the include of auto_email_notify.php at the top of this file too!
            unset($request_data);
            //invalidate cache of content block as it is modified now
            if(PA::$network_info) {
              $nid = '_network_' . PA::$network_info->network_id;
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
  }

  function handlePOST_submitAbuse($request_data) {
    global $error_msg;

    if(($request_data['action'] == 'submitAbuse') && !empty(PA::$login_uid)) {
      filter_all_post($request_data);
      $abuse = trim($request_data['abuse']);
      $type = (isset($request_data['type']) && ($request_data['type'] == 'comment')) ? 'comment' : 'content';
      $mail_type = ($type == 'comment') ? "report_abuse_on_comment" : "report_abuse_on_content";
      if(!empty($abuse)) {
        $extra = $this->shared_data['extra'];
        $network_info = $this->shared_data['network_info'];
        $error_msg="";
        try {
          // Saving the abuse report
          $report_abuse_obj = new ReportAbuse();
          $report_abuse_obj->parent_type = ($type == 'comment') ? TYPE_COMMENT : TYPE_CONTENT;
          $report_abuse_obj->parent_id = $request_data['cid'];
          $report_abuse_obj->reporter_id = PA::$login_uid;
          $report_abuse_obj->body = $request_data['abuse'];
          $id = $report_abuse_obj->save();
        }
        catch(PAException $e) {
          $error_msg = $e->message;
        }

        $ccid_string = "";
        PANotify::send($mail_type, PA::$network_info, PA::$login_user, $report_abuse_obj);
        $error_msg = 9002;
/*
        if(!empty($request_data['gid'])) {
          $group = new Group();
          $group->load((int)$request_data['gid']);
          PANotify::send("report_abuse_grp_owner", $group, PA::$login_user, $report_abuse_obj);
        }
*/
        try {
          if(!empty($this->shared_data['content']) && !empty($this->shared_data['collection'])) {
            $content = $this->shared_data['content'];
            $collection = $this->shared_data['collection'];
            if($content && $content->parent_collection_id!= -1) {
              if($this->shared_data['is_group_content']) {
                $mail_type = ($type == 'comment') ? "report_abuse_on_comment_grp_owner" : "report_abuse_grp_owner";
                PANotify::send($mail_type, $this->shared_data['collection'], PA::$login_user, $report_abuse_obj);
                $error_msg = 9002;
              }
            }
          }
        } catch (PAException $e) {
          $error_msg = $e->message;
        }
      }
      else {
        $error_msg = 9004;
      }
    }
  }

/**
   function is added for sending the mail of Abuse report
   parameter required - sender name, subject of mail, message ..
*/
/* - Replaced with new PANotify code

  function send_message_to_user($user_name, $suject=null, $mail_sub_msg_array) {
    // Adding the message for newtork owner

    $network_info = PA::$network_info;
    $login_uid = PA::$login_uid;

    $network_name = $network_info->name;
    $report_name = $_SESSION['user']['name'];
    $site_name = PA::$site_name;
    $message = $mail_sub_msg_array['message'];
    $content_url = $mail_sub_msg_array['content_url'];
    $delete_url  = $mail_sub_msg_array['delete_url'];

    if(empty($suject)) {
      $subject = "$report_name has reported an abuse about some content in your network $network_name";
    }
    $msg = "<br>
      $report_name has reported an abuse about some comment in your network $network_name.<br>
      <br>
      $report_name reported:<br> $message
      <br>
      Click Here $content_url to view that comment as well as content.
      <br>
      <br>
      Click here $delete_url to delete that comment.
      <br>
      <br>
      Thanks<br>
      <br />
      The $site_name Team.
      <br />
      Everyone at $site_name respects your privacy. Your information will never be shared with third parties unless specifically requested by you.
    ";
    Message::add_message((int)$login_uid, null, $user_name, $subject, $msg);
    return;
  }
*/
  function render() {
    //$this->title = chop_string($this->content->title, TITLE_LENGTH);
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  function generate_inner_html () {
    global $current_theme_path;
    return uihelper_generate_center_content($this->content_id, 1);
  }
}
?>
