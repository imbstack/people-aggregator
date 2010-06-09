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

class ShowUserCommentModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

  function __construct() {
    parent::__construct();
    $this->html_block_id = 'ShowUserCommentModule';
  }


  public function initializeModule($request_method, $request_data)  {
    global $paging;

    $count_comment = Comment::count_comment(PA::$page_uid, TYPE_USER);
    if(($this->page_id == PAGE_USER_PRIVATE) && $count_comment == 0) return 'skip';
    $this->Paging["page"] = $paging["page"];
    $this->Paging["show"] = 3;
    $this->title = __('Testimonials for ').ucfirst(PA::$page_user->first_name);
  }
  
  function handleRequest($request_method, $request_data) {
    $class_name = get_class($this);
    if(!empty($request_data['action']) && !empty($request_data['module']) && ($request_data['module'] == $class_name)) { 
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

  private function handleAJAX_addUserComment($request_data) {
    $msg = 'success';
    $html = 'null';
    filter_all_post($request_data);
    if(!empty($request_data['content'])) {
       $comment = new Comment();
       $usr = PA::$login_user;
       $comment->comment = $request_data['content'];
       $comment->subject = $request_data['content'];
       $comment->parent_type = TYPE_USER;
       $comment->parent_id = PA::$page_uid;
       $comment->content_id = PA::$page_uid;
       $comment->user_id = $usr->user_id;
       $comment->name = $usr->login_name;
       $comment->email = $usr->email;
       $id = PA::$page_uid;
       if($comment->spam_check()) {
         Logger::log("Comment rejected by spam filter", LOGGER_ACTION);
         $msg = $html = __("Comment rejected by spam filter");
       }
       else {
         try {
           $comment->save_comment();
           if($comment->spam_state != SPAM_STATE_OK) {
             $msg = $html = __("Comment rejected by spam filter");
           }
           $html = $this->render();
         }  
         catch(Exception $e) {
           $msg = $html = $e->getMessage();
         }
       }
    } else {
      $msg= __("Comment can't be empty.");
    }
    echo json_encode(array("msg"=>$msg, "result"=>htmlspecialchars($html)));
    exit;
  }
  
  function render() {
    global $login_uid, $page_uid;

    $comment = new Comment();

    $comment->parent_type = TYPE_USER;
    $comment->parent_id = $page_uid;

    $this->Paging["count"] = $comment->get_multiples_comment(TRUE);
    $result = $comment->get_multiples_comment(FALSE,$this->Paging["show"],$this->Paging["page"]);

    $this->links = $this->manage_links($result);
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {

    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';

    $inner_html_gen = & new Template($tmp_file);

    $inner_html_gen->set('links', $this->links);
    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();

    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);

    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

  function manage_links($links) {
    global $app;
    global  $login_uid, $page_uid;
    $cnt = count($links);
    if($cnt == 0) return $links;
    $result = array();

    for($i = 0; $i < $cnt; $i++) {
      $result[$i]['comment_id'] = $links[$i]['comment_id'];
      $result[$i]['user_id'] = $links[$i]['user_id'];
      $result[$i]['comment'] = $links[$i]['comment'];
      $result[$i]['created'] = $links[$i]['created'];
      $usr = new User();
      $usr->load((int)$links[$i]['user_id']);
      $result[$i]['user_name'] = $usr->login_name;
      $result[$i]['picture'] = $usr->picture;
      $result[$i]['first_name'] = $usr->first_name;
      $result[$i]['last_name'] = $usr->last_name;
      $temp_array = array($links[$i]['parent_id'], $links[$i]['user_id']);
      if (in_array($login_uid, $temp_array)) {
        $result[$i]['delete_link']= PA::$url .'/deletecomment.php?comment_id='.$links[$i]['comment_id']."&back_page=" . urlencode(PA::$url . $app->current_route);
      }

      $login = User::get_login_name_from_id($links[$i]['user_id']);
/*
      $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$links[$i]['user_id'];
      $url_perms = array('current_url' => $current_url,
                          'login' => $login
                        );
      $url = get_url(FILE_USER_BLOG, $url_perms);
*/
      $url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login;
      $result[$i]['hyper_link']= $url;
    }
    return $result;
  }
}
?>
