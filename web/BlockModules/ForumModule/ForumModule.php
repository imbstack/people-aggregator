<?php
error_reporting(E_ALL);
require_once "api/Forum/PaForum.class.php";
require_once "api/Forum/PaForumCategory.class.php";
require_once "api/Forum/PaForumThread.class.php";
require_once "api/Forum/PaForumPost.class.php";
require_once "api/Forum/PaForumBoard.class.php";
require_once "api/Forum/PaForumsUsers.class.php";
require_once "web/includes/classes/TinyMCE.class.php";
require_once "api/Permissions/PermissionsHandler.class.php";

class ForumModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';

  private $page = null;
  private $description = null;
  private $forums_url = null;
  private $theme_path = null;
  private $theme_url  = null;
  private $forum_user = null;
  private $user_status = null;

  private $board_type     = null;
  private $board_settings = null;

  private $board    = null;
  private $category = null;
  private $forum    = null;
  private $thread   = null;
  private $current_page = null;
  private $current_post = null;


  function __construct() {
    $this->title = __('Forums');
    $this->forums_url = PA::$url . "/forums";
    $this->outer_template =  "outer_public_forums_center_module.tpl"; //'outer_public_center_module.tpl';
  }

  function initializeModule($request_method, $request_data) {

      $this->message = null;

      if(!empty($request_data['network_id'])) {
        $parent_id  = $request_data['network_id'];
        $this->nid  = $request_data['network_id'];
        $this->forums_url = PA::$url . "/forums/network_id=$this->nid";
        $board_type = PaForumBoard::network_board;
      } else if(!empty($this->shared_data['network_id'])) {
        $parent_id = $this->shared_data['network_id'];
        $this->nid  = $this->shared_data['network_id'];
        $this->forums_url = PA::$url . "/forums/network_id=$this->nid";
        $board_type = PaForumBoard::network_board;
      } else {
        $parent_id = PA::$network_info->network_id;
        $this->nid  = PA::$network_info->network_id;
        $this->forums_url = PA::$url . "/forums/network_id=$this->nid";
        $board_type = PaForumBoard::network_board;
      }

      if(!empty($request_data['user_id'])) {
        $parent_id  = $request_data['user_id'];
        $this->uid  = $request_data['user_id'];
        $this->forums_url = PA::$url . "/forums/network_id=$this->nid&user_id=$this->uid";
        $board_type = PaForumBoard::personal_board;
      }

      if(!empty($request_data['gid'])) {
        $parent_id  = $request_data['gid'];
        $this->gid  = $request_data['gid'];
        $this->forums_url = PA::$url . "/forums/network_id=$this->nid&gid=$this->gid";
        $board_type = PaForumBoard::group_board;
      }

      if(!empty($request_data['hidden_id'])) {
        $parent_id  = $request_data['hidden_id'];
        $this->hid  = $request_data['hidden_id'];
        $this->forums_url = PA::$url . "/forums/network_id=$this->nid&hid=$this->hid";
        $board_type = PaForumBoard::hidden_board;
      }

      $this->parent_id = $parent_id;
      $this->board_type = $board_type;

      if(!empty($request_data['forums_msg'])) {
        $msg = explode('|', base64_decode($request_data['forums_msg']));
        $this->message['message'] = $msg[0];
        if(!empty($msg[1])) $this->message['class'] = $msg[1];
      }
      $is_not_action = !isset($request_data['action']);  // render template if action isn't set

      if(!empty($request_data['category_id'])) {
          $this->setCategory($request_data['category_id'], $request_data, $is_not_action);
      } else if(!empty($request_data['forum_id'])) {
          $this->setForum($request_data['forum_id'], $request_data, $is_not_action);
      } else if(!empty($request_data['thread_id'])) {
          $this->setThread($request_data['thread_id'], $request_data, $is_not_action);
      } else if(!empty($request_data['board_id'])) {
          $this->setBoard($request_data['board_id'], $request_data, $is_not_action);
      } else {

         $board = $this->getDefaultBoard($request_data, $is_not_action);
      }
      if(!isset($this->board) && (@$request_data['action'] != 'newBoard')) {
           $this->setSplashPage($request_data);
      }
  }

  function setSplashPage($request_data) {
    $allowed = false;
    $url_src = null;
    $this->user_status = $this->checkUser($request_data);
    if(is_object($this->login_user)) {
      $owner = $this->getBoardOwner($request_data);
      $allowed = ($this->login_user->user_id == $owner->user_id) ? true : false;
    }
      switch($this->board_type) {
        case PaForumBoard::network_board:
          $user_msg = __("Forum Board for this network has not yet been created.");
          if($allowed) {
            $url_src  = $this->forums_url ."&parent_id=$this->parent_id" . "&owner_id=$owner->user_id" . "&action=newBoard";
          }
        break;
        case PaForumBoard::group_board:
          $user_msg = __("Forum Board for this group has not yet been created.");
          if($allowed) {
            $url_src  = $this->forums_url ."&parent_id=$this->parent_id" . "&owner_id=$owner->user_id" . "&action=newBoard";
          }
        break;
        case PaForumBoard::personal_board:
          $user_msg = __("The personal forum board for this user has not yet been created.");
          if($allowed) {
            $url_src  = $this->forums_url ."&parent_id=$this->parent_id" . "&owner_id=$owner->user_id" . "&action=newBoard";
          }
        break;
      }
      $this->setBoardTheme('default');
      $this->set_inner_template('splash.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array(
                                                       'forums_url'     => $this->forums_url,
                                                       'theme_url'      => $this->theme_url,
                                                       'allowed'        => $allowed,
                                                       'user_msg'       => $user_msg,
                                                       'url_src'        => $url_src,
                                                       'message'        => $this->message
                                                    ));
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


//------------------------------- GET Actions handlerss -------------------------------------------

  private function handleGET_quote($request_data) {
    $this->handleGET_reply($request_data, true);
  }

  private function handleGET_reply($request_data, $quote = false) {
    global $error_msg;
    if(!($this->user_status & PaForumsUsers::_waiting) && !($this->user_status & PaForumsUsers::_limited) &&
       !($this->user_status & PaForumsUsers::_banned) && ($this->user_status & PaForumsUsers::_allowed) ||
       (($this->user_status & PaForumsUsers::_anonymous) && ($this->board_settings['allow_anonymous_post'] == true))) {

    $reply_type = $request_data['mode'];
    if($reply_type == 'post') {
      $post = PaForumPost::getPaForumPost($request_data['post_id']);
      if(is_object($post)) {
        $this->setThread($post->get_thread_id(), $request_data, false);
        $back_url = $this->get_url_for(array(
                                        'thread_id' => $this->thread->get_id(),
                                        'page'      => $this->current_page,
                                        'post_id'   => $post->get_id()."#p_".$post->get_id()
                                      ));
      } else {
        $error_msg = __("Post does not exist!");
        return 'skip';
      }
    } else {
       $post = null;
       $this->setThread($request_data['thread_id'], $request_data, false);
       if(!$this->thread) {
          $error_msg = __("Thread does not exist!");
          return 'skip';
       }
       $back_url = $this->get_url_for(array(
                                       'thread_id' => $this->thread->get_id(),
                                       'page'      => $this->current_page
                                     ));

    }
    $this->set_inner_template('reply.tpl.php');
    $this->inner_HTML = $this->generate_inner_html(array('page_id'        => $this->page_id,
                                                         'thread'         => $this->thread,
                                                         'post'           => $post,
                                                         'reply_type'     => $reply_type,
                                                         'quote_post'     => $quote,
                                                         'current_page'   => $this->current_page,
                                                         'forums_url'     => $this->forums_url,
                                                         'theme_url'      => $this->theme_url,
                                                         'back_url'       => $back_url,
                                                         'user_status'    => $this->user_status,
                                                         'message'        => $this->message,
                                                         'allow_anonymous'=> $this->board_settings['allow_anonymous_post'],
                                                         'board'          => $this->board,
                                                         'tiny_mce'       => $this->tiny_mce
                                                      ));
    } else {
      $error_msg = __("You don't have permissions to post on this Forum!");
      return 'skip';
    }
  }


  private function handleGET_newTopic($request_data) {
    global $error_msg;

    if(!($this->user_status & PaForumsUsers::_waiting) && !($this->user_status & PaForumsUsers::_limited) &&
       !($this->user_status & PaForumsUsers::_anonymous) && ($this->user_status & PaForumsUsers::_allowed) ) {

      $action   = 'newTopic';
      $title    = __("New Thread");
      $back_url = $this->get_url_for(array('forum_id' => $request_data['forum_id']));


      $fields = array('fields' => array(array('name'  => 'edit_title',   'label'    => __('Thread title'),
                                              'class' => 'reply_title',  'content'  => null,
                                              'type'  => 'text',         'required' => true),
                                        array('name'  => 'edit_content', 'label'    => __('Thread content'),
                                              'class' => 'reply_content','content'  => null,
                                              'type'  => 'textarea',     'required' => true)
                                       ),
                      'hidden_fields' => array('forum_id' => $request_data['forum_id'])
                     );

      $this->set_inner_template('edit_form.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array(
                                                         'forums_url'     => $this->forums_url,
                                                         'theme_url'      => $this->theme_url,
                                                         'title'          => $title,
                                                         'action'         => $action,
                                                         'fields'         => $fields,
                                                         'back_url'       => $back_url,
                                                         'message'        => $this->message,
                                                         'board'          => $this->board,
                                                         'tiny_mce'       => $this->tiny_mce
                                                      ));

    } else {
      $error_msg = __("You don't have permissions to create a new thread!");
      return 'skip';
    }
  }

  private function handleGET_newForum($request_data) {
    $this->handleGET_editForum($request_data, 'new');
  }

  private function handleGET_editForum($request_data, $edit_mode = 'edit') {
    global $error_msg;
    if((@$this->board_settings['allow_users_create_forum']) || ($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {
      if($edit_mode == 'edit') {
        $this->setForum($request_data['forum_id'], $request_data, false);
        if(!is_object($this->forum)) {
          $error_msg = __("Forum does not exists!");
          return 'skip';
        }
        $action   = 'editForum';
        $title    = __("Edit Forum");
        $forum_title   = $this->forum->get_title();
        $forum_descr   = $this->forum->get_description();
        $category_id   = $this->forum->get_category_id();
        $hidden_fields = array('category_id' => $category_id, 'forum_id' => $request_data['forum_id']);
        $back_url = $this->get_url_for(array('category_id' => $category_id));

      } else {
        $action   = 'newForum';
        $title    = __("New Forum");
        $forum_title = null;
        $forum_descr = null;
        $hidden_fields = array('category_id' => $request_data['category_id']);
        $back_url = $this->get_url_for(array('category_id' => $request_data['category_id']));
      }
      $fields = array('fields' => array(array('name'  => 'edit_title',   'label'    => __('Forum title'),
                                              'class' => 'reply_title',  'content'  => $forum_title,
                                              'type'  => 'text',         'required' => true),
                                        array('name'  => 'edit_content', 'label'    => __('Short description'),
                                              'class' => 'reply_content','content'  => $forum_descr,
                                              'type'  => 'textarea',     'required' => true)
                                       ),
                      'hidden_fields' => $hidden_fields
                     );

      $this->set_inner_template('edit_form.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array(
                                                         'forums_url'     => $this->forums_url,
                                                         'theme_url'      => $this->theme_url,
                                                         'title'          => $title,
                                                         'action'         => $action,
                                                         'fields'         => $fields,
                                                         'back_url'       => $back_url,
                                                         'message'        => $this->message,
                                                         'board'          => $this->board,
                                                         'tiny_mce'       => $this->tiny_mce
                                                      ));

    } else {
      $error_msg = __("You don't have permissions to create a new forum!");
      return 'skip';
    }
  }

  private function handleGET_newCategory($request_data) {
    $this->handleGET_editCategory($request_data, 'new');
  }
  private function handleGET_editCategory($request_data, $edit_mode = 'edit') {
    global $error_msg;
    if((@$this->board_settings['allow_users_create_category']) || ($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {
      if($edit_mode == 'edit') {
        $this->setCategory($request_data['category_id'], $request_data, false);
        if(!is_object($this->category)) {
          $error_msg = __("Category does not exists!");
          return 'skip';
        }
        $action   = 'editCategory';
        $title    = __("Edit Category");
        $category_name    = $this->category->get_name();
        $category_descr   = $this->category->get_description();
        $category_id   = $this->category->get_id();
        $hidden_fields = array('category_id' => $category_id);
        $back_url = $this->get_url_for(array('category_id' => $category_id));
      } else {
        $action   = 'newCategory';
        $title    = __("New Category");
        $category_name  = null;
        $category_descr = null;
        $board_id = $this->board->get_id();
        $hidden_fields = array('board_id' => $board_id);
        $back_url = $this->get_url_for(array('board_id' => $board_id));
      }
      $fields = array('fields' => array(array('name'  => 'edit_title',   'label'    => __('Category title'),
                                              'class' => 'reply_title',  'content'  => $category_name,
                                              'type'  => 'text',         'required' => true),
                                        array('name'  => 'edit_content', 'label'    => __('Short description'),
                                              'class' => 'reply_content','content'  => $category_descr,
                                              'type'  => 'textarea',     'required' => true)
                                       ),
                      'hidden_fields' => $hidden_fields
                     );

      $this->set_inner_template('edit_form.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array(
                                                         'forums_url'     => $this->forums_url,
                                                         'theme_url'      => $this->theme_url,
                                                         'title'          => $title,
                                                         'action'         => $action,
                                                         'fields'         => $fields,
                                                         'back_url'       => $back_url,
                                                         'message'        => $this->message,
                                                         'board'          => $this->board,
                                                         'tiny_mce'       => $this->tiny_mce
                                                      ));

    } else {
      $error_msg = __("You don't have permissions to create a new category!");
      return 'skip';
    }
  }

  private function handleGET_newBoard($request_data) {
    global $error_msg;
    $forum_defaults = array('allow_anonymous_post'   => false,
                            'membership_approval'    => false,
                            'allow_users_create_category' => true,
                            'allow_users_create_forum' => true,
                            'threads_per_page'       => 20,
                            'posts_per_page'         => 20,
                            'post_edit_mode'         => 'tinymce_base',
                            'avatar_size'            => '85x100',
                            'banned_message'         => 'You are banned from this forum!',
                            'join_requested_message' => 'Your membership request has been sent.',
                            'join_approved_message'  => 'Your membership request has been approved.'
                            );

    switch($this->board_type) {
      case PaForumBoard::network_board:
        $network = Network::get_by_id((int)$request_data['parent_id']);
        $title = $network->name . " ". __("network forum board");
      break;
      case PaForumBoard::group_board:
        $group = Group::load_group_by_id((int)$request_data['parent_id']);
        $title = $group->title . " ". __("group forum board");
      break;
      case PaForumBoard::personal_board:
        $user = new User();
        $user->load((int)$request_data['parent_id']);
        $title = $user->login_name . " ". __("personal forum board");
      break;
    }

    if($request_data['owner_id'] == PA::$login_uid) {
        $params = array( "owner_id"    => $request_data['parent_id'],
                         "network_id"  => $this->nid,
                         "title"       => $title,
                         "description" => __("Forum Board description goes here"),
                         "type"        => $this->board_type,
                         "theme"       => 'default',
                         "settings"    => serialize($forum_defaults),
                         "is_active"   => 1,
                         "created_at" => date("Y-m-d H:i:s")
                       );
        try {
          $board_id = PaForumBoard::insertPaForumBoard($params);
        } catch (Exception $e) {
          unset($request_data['action']);
          unset($request_data['owner_id']);
          $err = "Exception in ForumModule, function handleGET_newBoard();<br />Message: " . $e->getMessage();
          $this->redirectWithMessage($err, $request_data);
        }

        $status  = (PaForumsUsers::_owner | PaForumsUsers::_allowed);
        try {
            $params = array( "user_id"     => $request_data['owner_id'],
                             "board_id"    => $board_id,
                             "user_status" => $status,
                             "is_active"   => 1,
                             "date_join"   => date("Y-m-d H:i:s")
                      );
            PaForumsUsers::insertPaForumsUsers($params);
        } catch (Exception $e) {
            unset($request_data['action']);
            unset($request_data['owner_id']);
            $err = "Exception in ForumModule, function handleGET_newBoard();<br />Message: " . $e->getMessage();
            $this->redirectWithMessage($err, $request_data);
        }

        unset($request_data);
        $request_data['board_id'] = $board_id;
        $this->redirectWithMessage(__("Forum Board sucessfully saved"), $request_data, 'info_message');
    }  else {
      $error_msg = __("You don't have permissions to create this Forum Board!");
      return 'skip';
    }
  }

  private function handleGET_editSettings($request_data) {
    global $error_msg;
    if(($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {
      $tiny_mce_opt['options'] = array("tinymce_rich" => "Rich TinyMCE",
                                       "tinymce_base" => "Base TinyMCE",
                                       "no_tinymce"   => "No TinyMCE");

      $avatar_size_opt['options'] = array("65x75"   => "Small",
                                          "85x100"  => "Medium",
                                          "110x130" => "Large");

      $themes_list['options'] = $this->getBoardThemes();

      $action   = 'editSettings';
      $title    = __("Forum Settings");
      $settings = $this->board->get_settings();
      $board_id = $this->board->get_id();
      $hidden_fields = array('board_id' => $board_id);
      $back_url = $this->get_url_for(array('board_id' => $board_id));

      $tiny_mce_opt['selected']    = (isset($settings['post_edit_mode'])) ? $settings['post_edit_mode'] : "no_tinymce";
      $avatar_size_opt['selected'] = (isset($settings['avatar_size']))    ? $settings['avatar_size'] : "65x75";
      $themes_list['selected']     = $this->board->get_theme() ;
      $fields = array('fields' =>
          array(
                array('name'  => 'title',                  'label'    => __('Forum name'),
                      'class' => 'long_field',             'content'  => $this->board->get_title(),
                      'type'  => 'text',                   'required' => true),
                array('name'  => 'description',            'label'    => __('Forum description'),
                      'class' => 'textarea_field',         'content'  => $this->board->get_description(),
                      'type'  => 'textarea',               'required' => false),
                array('name'  => 'allow_anonymous_post',   'label'    => __('Allow anonymous post'),
                      'class' => 'check_box',              'content'  => (($settings['allow_anonymous_post']) ? "checked" : null),
                      'type'  => 'checkbox',               'required' => true),
                array('name'  => 'allow_users_create_category', 'label'    => __('Allow users to create Categories'),
                      'class' => 'check_box',              'content'  => ((@$settings['allow_users_create_category']) ? "checked" : null),
                      'type'  => 'checkbox',               'required' => true),
                array('name'  => 'allow_users_create_forum', 'label'    => __('Allow users to create Forums'),
                      'class' => 'check_box',              'content'  => ((@$settings['allow_users_create_forum']) ? "checked" : null),
                      'type'  => 'checkbox',               'required' => true),
                array('name'  => 'membership_approval',    'label'    => __('Membership approval required'),
                      'class' => 'check_box',              'content'  => (($settings['membership_approval']) ? "checked" : null),
                      'type'  => 'checkbox',               'required' => true),
                array('name'  => 'threads_per_page',       'label'    => __('Threads per page'),
                      'class' => 'short_field',            'content'  => $settings['threads_per_page'],
                      'type'  => 'text',                   'required' => true),
                array('name'  => 'posts_per_page',         'label'    => __('Posts per page'),
                      'class' => 'short_field',            'content'  => $settings['posts_per_page'],
                      'type'  => 'text',                   'required' => true),
                array('name'  => 'avatar_size',            'label'    => __('Avatar size'),
                      'class' => 'medium_field',           'content'  => $avatar_size_opt,
                      'type'  => 'select',                 'required' => true),
                array('name'  => 'post_edit_mode',         'label'    => __('Edit mode'),
                      'class' => 'medium_field',           'content'  => $tiny_mce_opt,
                      'type'  => 'select',                 'required' => true),
                array('name'  => 'theme',                  'label'    => __('Forum theme'),
                      'class' => 'medium_field',           'content'  => $themes_list,
                      'type'  => 'select',                 'required' => true),
                array('name'  => 'banned_message',         'label'    => __('Banned users message'),
                      'class' => 'textarea_field',         'content'  => $settings['banned_message'],
                      'type'  => 'textarea',               'required' => false),
                array('name'  => 'join_requested_message', 'label'    => __('Join requested message'),
                      'class' => 'textarea_field',         'content'  => $settings['join_requested_message'],
                      'type'  => 'textarea',               'required' => false),
                array('name'  => 'join_approved_message',  'label'    => __('Join approved message'),
                      'class' => 'textarea_field',         'content'  => $settings['join_approved_message'],
                      'type'  => 'textarea',               'required' => false),
               ),
                      'hidden_fields' => $hidden_fields
      );

      $this->set_inner_template('edit_form.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array(
                                                         'forums_url'     => $this->forums_url,
                                                         'theme_url'      => $this->theme_url,
                                                         'title'          => $title,
                                                         'action'         => $action,
                                                         'fields'         => $fields,
                                                         'back_url'       => $back_url,
                                                         'message'        => $this->message,
                                                         'board'          => $this->board,
                                                         'tiny_mce'       => $this->tiny_mce
                                                      ));

    } else {
      $error_msg = __("You don't have permissions to edit Forum Board settings!");
      return 'skip';
    }
  }


  private function handleGET_edit($request_data, $edit_mode = 'edit') {
    global $error_msg;
      $edit_type = (!empty($request_data['post_id'])) ? 'post' : 'thread';
      if($edit_type == 'post') {
        $post = PaForumPost::getPaForumPost($request_data['post_id']);
        if(is_object($post)) {
          if((!$this->user_status & PaForumsUsers::_owner)
          && !($this->user_status & PaForumsUsers::_admin)
          && !((PA::$login_uid == $post->get_user_id()))) {
            $error_msg = __("You don't have required permissions for this task!");
            return 'skip';
          }
          $this->setThread($post->get_thread_id(), $request_data, false);
          $back_url = $this->get_url_for(array(
                                         'thread_id' => $this->thread->get_id(),
                                         'page'      => $this->current_page,
                                         'post_id'   => $post->get_id()."#p_".$post->get_id()
                                        ));
        } else {
          $error_msg = __("Post does not exists!");
          return 'skip';
        }
      } else if($edit_type == 'thread') {
         $post = null;
         $this->setThread($request_data['thread_id'], $request_data, false);
         if(!$this->thread) {
            $error_msg = __("Thread does not exists!");
            return 'skip';
         }
         if((!$this->user_status & PaForumsUsers::_owner)
         && !($this->user_status & PaForumsUsers::_admin)
         && !((PA::$login_uid == $this->thread->get_user_id()))) {
            $error_msg = __("You don't have required permissions for this task!");
            return 'skip';
         }

         $back_url = $this->get_url_for(array(
                                         'thread_id' => $this->thread->get_id(),
                                         'page'      => $this->current_page
                                        ));
      }

      $this->set_inner_template('edit_post.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array('page_id'        => $this->page_id,
                                                           'thread'         => $this->thread,
                                                           'post'           => $post,
                                                           'edit_type'      => $edit_type,
                                                           'current_page'   => $this->current_page,
                                                           'forums_url'     => $this->forums_url,
                                                           'theme_url'      => $this->theme_url,
                                                           'back_url'       => $back_url,
                                                           'user_status'    => $this->user_status,
                                                           'message'  => $this->message,
                                                           'allow_anonymous'=> $this->board_settings['allow_anonymous_post'],
                                                           'board'          => $this->board,
                                                           'tiny_mce'       => $this->tiny_mce
                                                        ));
  }

  private function handleGET_removeThread($request_data) {
    global $error_msg;
    if(($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {
      try {
        PaForumThread::deletePaForumThread($request_data['thread_id']);
        unset($request_data['thread_id']);
        unset($request_data['action']);
        $request_data['forum_id'] = $this->thread->get_forum_id();
        $msg = __("Thread sucessfully removed");
        $this->redirectWithMessage($msg, $request_data, 'info_message');
      } catch (Exception $e) {
        $error_msg = "Exception in ForumModule, function handleGET_removeThread();<br />Message: " . $e->getMessage();
      }
    } else {
      $error_msg = __("You don't have required permissions for this task!");
      return 'skip';
    }
  }

  private function handleGET_delForum($request_data) {
    global $error_msg;
    if(($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {
      try {
        PaForum::deletePaForum($request_data['forum_id']);
        unset($request_data['forum_id']);
        unset($request_data['action']);
        $msg = __("Forum sucessfully deleted");
        $this->redirectWithMessage($msg, $request_data, 'info_message');
      } catch (Exception $e) {
        $error_msg = "Exception in ForumModule, function handleGET_delForum();<br />Message: " . $e->getMessage();
      }
    } else {
      $error_msg = __("You don't have required permissions for this task!");
      return 'skip';
    }
  }

  private function handleGET_delCategory($request_data) {
    global $error_msg;
    if(($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {
      try {
        PaForumCategory::deletePaForumCategory($request_data['category_id']);
        unset($request_data['category_id']);
        unset($request_data['action']);
        $msg = __("Category sucessfully deleted");
        $this->redirectWithMessage($msg, $request_data, 'info_message');
      } catch (Exception $e) {
        $error_msg = "Exception in ForumModule, function handleGET_delForum();<br />Message: " . $e->getMessage();
      }
    } else {
      $error_msg = __("You don't have required permissions for this task!");
      return 'skip';
    }
  }

  private function handleGET_delPost($request_data) {
    global $error_msg;
    if(($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {
      try {
        PaForumPost::deletePaForumPost($request_data['post_id']);
        unset($request_data['post_id']);
        unset($request_data['action']);
        $msg = __("Post sucessfully deleted");
        $this->redirectWithMessage($msg, $request_data, 'info_message');
      } catch (Exception $e) {
        $error_msg = "Exception in ForumModule, function handleGET_delPost();<br />Message: " . $e->getMessage();
      }
    } else {
      $error_msg = __("You don't have required permissions for this task!");
      return 'skip';
    }
  }

  private function handleGET_threadStatus($request_data) {
    global $error_msg;
    if(($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {
      try {
        $mode = $request_data['mode'];
        $thread = PaForumThread::getPaForumThread($request_data['thread_id']);
        if(!$thread) {
          $error_msg = __("Thread not found!");
          return 'skip';
        }
        $thread_status = $thread->get_status();
        switch($mode) {
           case 'sticky':
              if($thread_status & PaForumThread::_sticky) {
                $set_opt = 'reset';
                $sub_msg = ", " . __("sticky") . " = OFF";
              } else {
                $set_opt = 'set';
                $sub_msg = ", " . __("sticky") . " = ON";
              }
              $status_bit = PaForumThread::_sticky;
           break;
           case 'lock':
              if($thread_status & PaForumThread::_locked) {
                $set_opt = 'reset';
                $sub_msg = ", " . __("locked") . " = OFF";
              } else {
                $set_opt = 'set';
                $sub_msg = ", " . __("locked") . " = ON";
              }
              $status_bit = PaForumThread::_locked;
           break;
        }
        $thread->updateThreadStatus($status_bit, $set_opt);
        $thread->save_PaForumThread();
        unset($request_data['mode']);
        unset($request_data['action']);
        $msg = __("Thread status sucessfully changed") . $sub_msg;
        $this->redirectWithMessage($msg, $request_data, 'info_message');
      } catch (Exception $e) {
        $error_msg = "Exception in ForumModule, function handleGET_threadStatus();<br />Message: " . $e->getMessage();
      }
    } else {
      $error_msg = __("You don't have required permissions for this task!");
      return 'skip';
    }
  }

  private function handleGET_join($request_data) {
    global $error_msg;
    switch($this->board_settings['membership_approval']) {
      case true:
           $message = $this->board_settings['join_requested_message'];
           $status  = PaForumsUsers::_waiting;
      break;
      case false:
           $message = $this->board_settings['join_approved_message'];
           $status  = PaForumsUsers::_allowed;
      break;
    }
    try {
        $params = array( "user_id"     => PA::$login_uid,
                         "board_id"    => $this->board->get_id(),
                         "user_status" => $status,
                         "is_active"   => 1,
                         "date_join"   => date("Y-m-d H:i:s")
                  );
        PaForumsUsers::insertPaForumsUsers($params);
        $error_msg = $message;
    } catch (Exception $e) {
       $error_msg = "Exception in ForumModule, function handleGET_join();<br />Message: " . $e->getMessage();
    }
  }

  private function handleGET_banUser($request_data) {
    $err = null;
    if(($this->user_status & PaForumsUsers::_owner) || ($this->user_status & PaForumsUsers::_admin)) {

      $status_bit  = PaForumsUsers::_banned;
      if($request_data['user_id'] == -1) {
        $err = __("Anonymous user can't be banned");
      }
      if($request_data['user_id'] == $this->login_user->user_id) {
        $err = __("You can't ban itself.");
      }

      if($err) {
        unset($request_data['action']);
        unset($request_data['user_id']);
        $this->redirectWithMessage($err, $request_data);
        exit;
      }

      try {
          $members = PaForumsUsers::listPaForumsUsers("user_id = " . $request_data['user_id'] . " AND board_id = " . $this->board->get_id());
          unset($request_data['action']);
          unset($request_data['user_id']);
          if(isset($members[0])) {
            $member = $members[0];
          } else {
            $err = __("Can't ban user - user not found!");
            $this->redirectWithMessage($err, $request_data);
            exit;
          }
          $is_banned = ($member->get_user_status() & PaForumsUsers::_banned);
          $member->updateUserStatus($status_bit, (($is_banned) ? "reset" : 'set'));
          $member->save_PaForumsUsers();
          $msg = __("User status sucessfully changed.") ." ".
                 __("User is") ." ". (($is_banned) ? __("not") . " " : null) .
                 __("banned now.");
          $this->redirectWithMessage($msg, $request_data, "info_message");
      } catch (Exception $e) {
          $err = "Exception in ForumModule, function handleGET_banUser();<br />Message: " . $e->getMessage();
          $this->redirectWithMessage($err, $request_data);
      }
    }
  }

//------------------------------- POST Actions handlerss ------------------------------------------

  private function handlePOST_reply($request_data) {
    $err = null;
    $form_data = $request_data['form_data'];
    if($this->board_settings['allow_anonymous_post'] && ($this->user_status & PaForumsUsers::_anonymous)) {
      if(empty($form_data['anonymous_name'])) {
        $err .= "* " . __("Please, enter your name or email address") .".<br />";
      }
      if(md5(strtoupper($request_data['txtNumber'])) != $_SESSION['image_random_value']) {
        $_SESSION['image_is_logged_in'] = true;
        $_SESSION['image_random_value'] = '';
        $err .= "* " . __("Please, enter correct verification code") .".<br />";
      }
    }

    if(empty($form_data['reply_title'])) {
      $err .= "* " . __("Please, enter reply title") .".<br />";
    }
    if(empty($form_data['reply_content'])) {
      $err .= "* " . __("Please, enter reply message text") .".<br />";
    }
    if($err) {
      $this->redirectWithMessage($err, $request_data);
      exit;
    }

    $user_id = ($this->user_status & PaForumsUsers::_anonymous)
             ? -1             // anonymous user
             : $this->forum_user->get_user_id();

    $parent_id = ($request_data['mode'] == 'post')
               ? $request_data['post_id']
               : null;

    $content  = $form_data['reply_content'];
    if($this->user_status & PaForumsUsers::_anonymous) {
      $content .= "<div class=\"edited_by\">\n" .
                  __("Posted by") .": " . $form_data['anonymous_name'] .
                  ", " . date("Y-m-d H:i:s") . "\n</div>";
    }

    $params = array( "title"      => $form_data['reply_title'],
                     "content"    => $content,
                     "user_id"    => $user_id,
                     "parent_id"  => $parent_id,
                     "thread_id"  => $request_data['thread_id'],
                     "is_active"  => true,
                     "created_at" => date("Y-m-d H:i:s"),
                     "updated_at" => date("Y-m-d H:i:s"),
                     "modified_by" => null
                   );

    unset($request_data['mode']);
    unset($request_data['action']);
    try {
      PaForumPost::insertPaForumPost($params);
      $request_data['page'] = 'last';
      $this->redirectWithMessage(__("Reply post sucessfully saved"), $request_data, 'info_message');
    } catch (Exception $e) {
      $this->redirectWithMessage($e->getMessage(), $request_data);
    }
  }

  private function handlePOST_newTopic($request_data) {
    $err = null;
    $form_data = $request_data['form_data'];

    if(empty($form_data['edit_title'])) {
      $err .= "* " . __("Please, enter thread title") .".<br />";
    }
    if(empty($form_data['edit_content'])) {
      $err .= "* " . __("Please, enter thread text") .".<br />";
    }
    if($err) {
      $this->redirectWithMessage($err, $request_data);
      exit;
    }

    $user_id = $this->forum_user->get_user_id();

    $params = array( "related_content_id" => -1,
                     "title"      => $form_data['edit_title'],
                     "content"    => $form_data['edit_content'],
                     "status"     => 0,
                     "forum_id"   => $request_data['forum_id'],
                     "user_id"    => $user_id,
                     "viewed"     => 0,
                     "is_active"  => true,
                     "created_at" => date("Y-m-d H:i:s"),
                     "updated_at" => date("Y-m-d H:i:s"),
                     "modified_by" => null
                   );
    unset($request_data['action']);
    try {
      PaForumThread::insertPaForumThread($params);
      $this->redirectWithMessage(__("Thread sucessfully created"), $request_data, 'info_message');
    } catch (Exception $e) {
      $this->redirectWithMessage($e->getMessage(), $request_data);
    }
  }

  private function handlePOST_newForum($request_data) {
    $this->handlePOST_editForum($request_data, 'new');
  }

  private function handlePOST_editForum($request_data, $mode = 'edit') {
    $err = null;
    $form_data = $request_data['form_data'];

    if(empty($form_data['edit_title'])) {
      $err .= "* " . __("Please, enter forum title") .".<br />";
    }
    if(empty($form_data['edit_content'])) {
      $err .= "* " . __("Please, enter forum description text") .".<br />";
    }
    if($err) {
      $this->redirectWithMessage($err, $request_data);
      exit;
    }

    $params = array( "title"       => $form_data['edit_title'],
                     "description" => $form_data['edit_content'],
                     "updated_at"  => date("Y-m-d H:i:s")
                   );

    if($mode == 'new') {
      $params = array_merge($params,
                            array( "is_active"   => true,
                                   "category_id" => $request_data['category_id'],
                                   "sort_order"  => null,
                                   "icon"        => 'forum_default.gif',
                                   "created_at"  => date("Y-m-d H:i:s")
                            )
                );
    }

    unset($request_data['action']);
    try {
      if($mode == 'new') {
        PaForum::insertPaForum($params);
        $msg = __("Forum sucessfully created");
      } else {
        PaForum::updatePaForum($request_data['forum_id'], $params);
        $msg = __("Forum data sucessfully updated");
      }
      $this->redirectWithMessage($msg, $request_data, 'info_message');
    } catch (Exception $e) {
      $this->redirectWithMessage($e->getMessage(), $request_data);
    }
  }

  private function handlePOST_newCategory($request_data) {
    $this->handlePOST_editCategory($request_data, 'new');
  }

  private function handlePOST_editCategory($request_data, $mode = 'edit') {
    $err = null;
    $form_data = $request_data['form_data'];

    if(empty($form_data['edit_title'])) {
      $err .= "* " . __("Please, enter category title") .".<br />";
    }
    if(empty($form_data['edit_content'])) {
      $err .= "* " . __("Please, enter category description text") .".<br />";
    }
    if($err) {
      $this->redirectWithMessage($err, $request_data);
      exit;
    }

    $params = array( "name"       => $form_data['edit_title'],
                     "description" => $form_data['edit_content'],
                     "updated_at"  => date("Y-m-d H:i:s")
                   );

    if($mode == 'new') {
      $params = array_merge($params,
                            array( "is_active"   => true,
                                   "board_id"    => $request_data['board_id'],
                                   "sort_order"  => null,
                                   "created_at"  => date("Y-m-d H:i:s")
                            )
                );
    }

    unset($request_data['action']);
    try {
      if($mode == 'new') {
        PaForumCategory::insertPaForumCategory($params);
        $msg = __("Category sucessfully created");
      } else {
        PaForumCategory::updatePaForumCategory($request_data['category_id'], $params);
        $msg = __("Category data sucessfully updated");
      }
      $this->redirectWithMessage($msg, $request_data, 'info_message');
    } catch (Exception $e) {
      $this->redirectWithMessage($e->getMessage(), $request_data);
    }
  }

  private function handlePOST_editSettings($request_data) {
    $err = null;
    $form_data = $request_data['form_data'];

    if(empty($form_data['title'])) {
      $err .= "* " . __("Please, enter forum title") .".<br />";
    }
    if(empty($form_data['threads_per_page']) || !is_numeric($form_data['threads_per_page'])) {
      $err .= "* " . __("Please, enter numeric value into the \"threads_per_page\" field") .".<br />";
    }
    if(empty($form_data['posts_per_page']) || !is_numeric($form_data['posts_per_page'])) {
      $err .= "* " . __("Please, enter numeric value into the \"posts_per_page\" field") .".<br />";
    }
    if($err) {
      $this->redirectWithMessage($err, $request_data);
      exit;
    }

    $this->board->set_title($form_data['title']);
    unset($form_data['title']);

    $this->board->set_description($form_data['description']);
    unset($form_data['description']);

    $this->board->set_theme($form_data['theme']);
    unset($form_data['theme']);

    if(isset($form_data['allow_anonymous_post'])) {
      $form_data['allow_anonymous_post'] = 1;
    } else {
      $form_data['allow_anonymous_post'] = 0;
    }

    if(isset($form_data['allow_users_create_category'])) {
      $form_data['allow_users_create_category'] = 1;
    } else {
      $form_data['allow_users_create_category'] = 0;
    }

    if(isset($form_data['allow_users_create_forum'])) {
      $form_data['allow_users_create_forum'] = 1;
    } else {
      $form_data['allow_users_create_forum'] = 0;
    }

    if(isset($form_data['membership_approval'])) {
      $form_data['membership_approval'] = 1;
    } else {
      $form_data['membership_approval'] = 0;
    }

    $this->board->set_settings($form_data);

    unset($request_data['action']);
    try {
      $this->board->save_PaForumBoard();
      $msg = __("Settings data sucessfully updated");
      $this->redirectWithMessage($msg, $request_data, 'info_message');
    } catch (Exception $e) {
      $this->redirectWithMessage($e->getMessage(), $request_data);
    }
  }

  private function handlePOST_quote($request_data) {
    $this->handlePOST_reply($request_data);
  }

  private function handlePOST_updatePost($request_data, $mode = 'post') {
    $err = null;
    $form_data = $request_data['form_data'];
    if(empty($form_data['edit_title'])) {
      $err .= "* " . __("Title field can't be empty") .".<br />";
    }
    if(empty($form_data['edit_content'])) {
      $err .= "* " . __("Content field can't be empty") .".<br />";
    }
    if($err) {
      $request_data['action'] = 'edit';
      $this->redirectWithMessage($err, $request_data);
      exit;
    }

    $edited_by  = new User();
    $edited_by->load((int)$this->forum_user->get_user_id());
    $content  = $form_data['edit_content'];
/*
    $content .= "<div class=\"edited_by\">\n" .
                __("Edited by") .": " . $edited_by->login_name .
                ", " . PA::datetime(time(), 'long', 'short') . "\n</div>";
*/
    $params = array( "title"       => $form_data['edit_title'],
                     "content"     => $content,
                     "updated_at"  => date("Y-m-d H:i:s"),
                     "modified_by" => $edited_by->login_name
                   );

    unset($request_data['mode']);
    unset($request_data['action']);
    try {
      if($mode == 'post') {
        PaForumPost::updatePaForumPost($request_data['post_id'], $params);
        $msg = __("Post sucessfully saved");
      } else if($mode == 'thread') {
        PaForumThread::updatePaForumThread($request_data['thread_id'], $params);
        $msg = __("Thread sucessfully saved");
      }
      $this->redirectWithMessage($msg, $request_data, 'info_message');
    } catch (Exception $e) {
      $this->redirectWithMessage($e->getMessage(), $request_data);
    }
  }

  private function handlePOST_updateThread($request_data) {
    $this->handlePOST_updatePost($request_data, 'thread');
  }


  private function getDefaultBoard($request_data, $render = true) {
    $board = null;
    $boards = PaForumBoard::listPaForumBoard("owner_id = $this->parent_id AND network_id = $this->nid AND type = '$this->board_type' AND is_active = 1");
    if(count($boards) > 0) {
      $board = $boards[0];
    }

    if(is_object($board)) {
      $this->setupBoard($board);
      $this->user_status = $this->checkUser($request_data);
      if($render) {
        $this->buildBoard($board);
      }
    }

    return $board;
  }

  private function setBoard($board_id, $request_data, $render = true) {
    $board = null;
    $board = PaForumBoard::getPaForumBoard($board_id);
    if(is_object($board)) {
      $this->setupBoard($board);
      $this->user_status = $this->checkUser($request_data);
      if($render) {
        $this->buildBoard($board);
      }
    }
  }

  private function setupBoard(PaForumBoard $board) {
      $this->board = $board;
      $this->board_settings = $this->board->get_settings();

      $this->title       = $board->get_title();
      $this->board_type  = $board->get_type();
      $this->description = $board->get_description();

      $theme = $board->get_theme();
      $this->setBoardTheme($theme);

      switch($this->board_settings['post_edit_mode']) {
        case 'tinymce_rich': $this->tiny_mce = $this->getTinyMCE('base'); break;
        case 'tinymce_base': $this->tiny_mce = $this->getTinyMCE('minimal'); break;
        default:             $this->tiny_mce = null;
      }

      $this->shared_data['board'] = $this->board;
      $this->shared_data['board_settings'] = $this->board_settings;
      $this->shared_data['board_statistics'] = $this->buildStatistics($this->board);
  }

  private function setCategory($category_id, $request_data, $render = true) {
    $forums   = array();
    $category = PaForumCategory::getPaForumCategory($category_id);
    if($category) {
      $this->category = $category;
      $category->statistics = $category->getCategoryStatistics();
      if(!empty($category->statistics['forums'])) {
        $forums = $category->statistics['forums'];
      }
      $board = $category->getBoard();
      $this->setupBoard($board);
      $this->user_status = $this->checkUser($request_data);
    }
    if($render && $this->category) {
      $this->set_inner_template('forum_category.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array('page_id'        => $this->page_id,
                                                           'forums'         => $forums,
                                                           'category'       => $category,
                                                           'forums_url'     => $this->forums_url,
                                                           'theme_url'      => $this->theme_url,
                                                           'user_status'    => $this->user_status,
                                                           'message'        => $this->message,
                                                           'description'    => $this->category->get_description(),
                                                           'board'          => $this->board,
                                                           'board_settings' => $this->board_settings
                                                    ));
    }
  }

  private function setForum($forum_id, $request_data, $render = true) {
    $threads = array();
    $current_page = (!empty($request_data['page'])) ? $request_data['page'] : 0;
    $forum = PaForum::getPaForum($forum_id);
    if($forum) {
      $this->forum  = $forum;
      $this->current_page = $current_page;
      $threads = array();
      $board = $forum->getBoard();
      $this->setupBoard($board);
      $this->user_status = $this->checkUser($request_data);
      $threads_pagging = $this->board_settings['threads_per_page'];
      $forum->statistics = $forum->getForumStatistics($threads_pagging, $current_page);
      if(!empty($forum->statistics['threads'])) {
        $threads = $forum->statistics['threads'];
      }
    }
    if($render && $this->forum) {
      $this->set_inner_template('forum_threads.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array('page_id'        => $this->page_id,
                                                           'forum'          => $forum,
                                                           'threads'        => $threads,
                                                           'current_page'   => $current_page,
                                                           'forums_url'     => $this->forums_url,
                                                           'theme_url'      => $this->theme_url,
                                                           'user_status'    => $this->user_status,
                                                           'message'        => $this->message,
                                                           'description'    => $this->forum->get_description(),
                                                           'board'          => $this->board,
                                                           'board_settings' => $this->board_settings
                                                    ));
    }
  }

  private function setThread($thread_id, $request_data, $render = true) {
    $posts = array();
    $current_page = (!empty($request_data['page'])) ? $request_data['page'] : 0;
    $current_post = (!empty($request_data['post_id'])) ? $request_data['post_id'] : null;
    $thread_status = null;
    $thread = PaForumThread::getPaForumThread($thread_id);
    if($thread) {
      $this->thread = $thread;
      $this->current_post = $current_post;
      $board = $thread->getBoard();
      $this->setupBoard($board);
      $this->user_status = $this->checkUser($request_data);
      $posts_pagging = $this->board_settings['posts_per_page'];
      $avatar_size = $this->board->getAvatarSize();
      $created_by = new User();
      $created_by->load((int)$thread->get_user_id());
      if($current_post) {
        $posts = $thread->getPosts($posts_pagging, null, $current_post);
      } else {
        $posts = $thread->getPosts($posts_pagging, $current_page);
      }
      $this->current_page  = $thread->get_current_page();
      $this->thread_status = $thread->get_status();
    }
    if($render && $this->thread) {
      $viewed = $this->thread->get_viewed();
      $this->thread->set_viewed($viewed+1);
      $this->thread->save_PaForumThread();
      $this->set_inner_template('forum_posts.tpl.php');
      $this->inner_HTML = $this->generate_inner_html(array('page_id'        => $this->page_id,
                                                           'thread'         => $this->thread,
                                                           'created_by'     => $created_by,
                                                           'posts'          => $posts,
                                                           'current_post'   => $this->current_post,
                                                           'current_page'   => $this->current_page,
                                                           'forums_url'     => $this->forums_url,
                                                           'theme_url'      => $this->theme_url,
                                                           'user_status'    => $this->user_status,
                                                           'thread_status'  => $this->thread_status,
                                                           'message'        => $this->message,
                                                           'avatar_size'    => $avatar_size,
                                                           'board'          => $this->board,
                                                           'board_settings' => $this->board_settings
                                                    ));
    }
  }

  private function buildBoard(PaForumBoard $board) {
    $bstat = $board->getBoardStatistics();
    $categories = $bstat['categories'];
    $this->set_inner_template('forum_main.tpl.php');
    $this->inner_HTML = $this->generate_inner_html(array('page_id' => $this->page_id,
                                                         'categories'     => $categories,
                                                         'forums_url'     => $this->forums_url,
                                                         'theme_url'      => $this->theme_url,
                                                         'user_status'    => $this->user_status,
                                                         'message'        => $this->message,
                                                         'description'    => $board->get_description(),
                                                         'board_settings' => $this->board_settings,
                                                         'board'          => $board
                                                        ));
  }

  private function checkUser($request_data) {
    $this->login_user = PA::$login_user;
    if(PA::$login_uid && (is_object($this->board))) {

      $member = null;
      $members = PaForumsUsers::listPaForumsUsers("user_id = " . PA::$login_uid . " AND board_id = " . $this->board->get_id());
      if(isset($members[0])) {
         $member = $members[0];
      }
      if($member) { // check is user a member
        $this->forum_user = $member;
        $user_status = $this->forum_user->get_user_status();
        $this->shared_data['board_member'] = $this->forum_user;
      } else {                 // logged user but not member of this board!
        $is_member = false;
        switch($this->board_type) {
          case PaForumBoard::network_board:
            $is_member = Network::member_exists($this->shared_data['network_id'], PA::$login_uid);
          break;
          case PaForumBoard::group_board:
            $is_member = Group::member_exists($this->gid, PA::$login_uid);
          break;
          case PaForumBoard::personal_board:
            $is_member = false;
          break;
          default:
            $is_member = false;
        }
        if($is_member) {
          $user_status = PaForumsUsers::_allowed;
          try {
            $params = array( "user_id"     => PA::$login_uid,
                             "board_id"    => $this->board->get_id(),
                             "user_status" => $user_status,
                             "is_active"   => 1,
                             "date_join"   => date("Y-m-d H:i:s")
            );
            PaForumsUsers::insertPaForumsUsers($params);
            $members = PaForumsUsers::listPaForumsUsers("user_id = " . PA::$login_uid . " AND board_id = " . $this->board->get_id());
            if(isset($members[0])) {
              $this->forum_user = $members[0];
            }
          } catch (Exception $e) {
            $error_msg = "Exception in ForumModule, function checkUser();<br />Message: " . $e->getMessage();
          }
        } else {
          $user_status = (PaForumsUsers::_notmember | PaForumsUsers::_anonymous);
          $this->forum_user = null;
        }
      }

      $params = array('permissions'=>'manage_forum',
                      'board'=>  $this->board,
                      'gid' => (!empty($this->gid)) ? $this->gid: null
                     );
      if(PermissionsHandler::can_user(PA::$login_uid, $params)) {
        $user_status = $user_status | PaForumsUsers::_admin;
      }

      if($user_status & PaForumsUsers::_banned) {
        $board = $this->getDefaultBoard($request_data, false);
        $this->setupBoard($board);
        $user  = new User();
        $user->load((int)$this->forum_user->get_user_id());
        $this->title = __('Banned User');
        $this->set_inner_template('banned_user.tpl.php');
        $this->inner_HTML = $this->generate_inner_html(array('page_id'        => $this->page_id,
                                                             'forum_user'     => $user,
                                                             'user_status'    => $user_status,
                                                             'board_settings' => $this->board_settings
                                                            ));
        return false;
      }
    } else {
      $this->forum_user = null;
      $user_status = PaForumsUsers::_anonymous;
    }

    return $user_status;
  }

  private function buildStatistics($board) {
    $board_statistics = $board->getBoardStatistics();
    $statistics = array();
    $statistics['title'] = $board->get_title();
    $statistics['description'] = $board->get_description();
    $statistics['type'] = $board->get_type() . " board";
    $statistics['created_at'] = $board->get_created_at();

    switch($board->get_type()) {
      case PaForumBoard::network_board:
        $net_id = $board->get_owner_id();
        if($net_id == 1) {                   // mother network - owner_id always is '1' !
          $owner_id = 1;
        } else {
          $owner_id = Network::get_network_owner((int)$board->get_owner_id());
        }
      break;
      case PaForumBoard::group_board:
        $owner_id = Group::get_owner_id((int)$board->get_owner_id());
      break;
      case PaForumBoard::personal_board:
        $owner_id = $board->get_owner_id();
      break;
    }
    $user = new User();
    $user->load((int)$owner_id);
    $statistics['owner'] = $user;
    $statistics['nb_categories'] = $board_statistics['nb_categories'];

    $nb_forums  = 0;
    $nb_threads = 0;
    $nb_posts   = 0;
    $last_posts = array();
    foreach($board_statistics['categories'] as $category)  {
      $nb_forums += $category->statistics['nb_forums'];
      foreach($category->statistics['forums'] as $forum)  {
        $nb_threads += $forum->statistics['nb_threads'];
        $nb_posts += $forum->statistics['nb_posts'];
        if(!empty($forum->statistics['last_post'])) {
          $last_posts[] = $forum->statistics['last_post'];
        }
      }
    }

    $statistics['nb_forums']  = $nb_forums;
    $statistics['nb_threads'] = $nb_threads;
    $statistics['nb_posts']   = $nb_posts;
    $statistics['last_posts'] = $last_posts;
    $statistics['nb_users']   = PaForumsUsers::countPaForumsUsers("board_id = ".$board->get_id());
    return $statistics;
  }


  private function getBoardThemes() {

    $themes_list = array();
    $theme_path = "web".DIRECTORY_SEPARATOR . PA::$theme_rel .
                   DIRECTORY_SEPARATOR . "forum" .
                   DIRECTORY_SEPARATOR . "themes";

    $paths  = array(PA::$core_dir . DIRECTORY_SEPARATOR . $theme_path,
                    PA::$project_dir . DIRECTORY_SEPARATOR . $theme_path );

    foreach($paths as $path) {
      try {
        foreach(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME) as $_path => $info) {
          $_entry = $info->getFilename();
          if ($info->isDir() && $_entry != '.svn')
          {
            $themes_list[$_entry] = ucwords(str_replace('_', ' ', $_entry));
          }
        }
      } catch (Exception $e) {
        continue;
      }
    }
    return $themes_list;
  }

  private function setBoardTheme($theme) {
      $this->theme_url   = PA::$url . "/" .PA::$theme_rel. "/forum/themes/" . $theme;
      $this->theme_path = DIRECTORY_SEPARATOR . PA::$theme_rel .
                          DIRECTORY_SEPARATOR . "forum" .
                          DIRECTORY_SEPARATOR . "themes" .
                          DIRECTORY_SEPARATOR . $theme;

      $this->renderer->add_header_css($this->theme_path . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . "forum.css");
      $this->renderer->add_page_js($this->theme_path . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . "forum.js");
  }

  private function getBoardOwner($request_data) {
    switch($this->board_type) {
      case PaForumBoard::network_board:
        $owner_id = Network::get_network_owner((int)$this->parent_id);
      break;
      case PaForumBoard::group_board:
        $group_owner = Group::get_owner_id((int)$this->parent_id);
        $owner_id = $group_owner['user_id'];
      break;
      case PaForumBoard::personal_board:
        $owner_id = ((int)$this->parent_id);
      break;
    }
    $this->owner_id = $owner_id;
    $user = new User();
    $user->load((int)$owner_id);
    return $user;
  }

  private function redirectWithMessage($message, $request_data, $type = 'error_message') {
    $query_str = '' ;
    $redirect_url = $this->forums_url;

    if(!empty($request_data['board_id'])) {
      $query_str = add_querystring_var($query_str, "board_id", $request_data['board_id']);
    }
    if(!empty($request_data['category_id'])) {
      $query_str = add_querystring_var($query_str, "category_id", $request_data['category_id']);
    }
    if(!empty($request_data['forum_id'])) {
      $query_str = add_querystring_var($query_str, "forum_id", $request_data['forum_id']);
    }
    if(!empty($request_data['thread_id'])) {
      $query_str = add_querystring_var($query_str, "thread_id", $request_data['thread_id']);
    }
    if(!empty($request_data['post_id'])) {
      $query_str = add_querystring_var($query_str, "post_id", $request_data['post_id']);
    }
    if(!empty($request_data['page'])) {
      $query_str = add_querystring_var($query_str, "page", $request_data['page']);
    }

    if(!empty($request_data['mode'])) {
      $query_str = add_querystring_var($query_str, "mode", $request_data['mode']);
    }

    if(!empty($request_data['action'])) {
      $query_str = add_querystring_var($query_str, "action", $request_data['action']);
    }

    $query_str = add_querystring_var($query_str, "forums_msg", base64_encode("$message|$type"));
    $query_str = ltrim($query_str, '?');
    $this->controller->redirect($redirect_url .'&'. $query_str);
  }

  private function get_url_for($params = array(), $_base_url = null) {
    $query_str = '';
    $url = ($_base_url) ? $_base_url : $this->forums_url;

    foreach($params as $name => $value) {
      $query_str = add_querystring_var($query_str, $name, $value);
    }
    return ($url . $query_str);
  }


  function set_inner_template($template_fname) {
    global $current_blockmodule_path;
    $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/$template_fname";
  }

  function render() {
    $content = parent::render();
    return $content;
  }

  function generate_inner_html($template_vars = array()) {

    $inner_html_gen = & new Template($this->inner_template);
    foreach($template_vars as $name => $value) {
      if(is_object($value)) {
        $inner_html_gen->set_object($name, $value);
      } else {
        $inner_html_gen->set($name, $value);
      }
    }
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

  private function getTinyMCE($mode) {
    $tiny_css = $this->theme_path . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . "tiny.css";
    $tiny = new TinyMCE($mode);
//    $tiny->unsetTinyParam('skin_variant');
    $tiny->setTinyParam('content_css', $tiny_css);
    return $tiny;
  }

}
?>
