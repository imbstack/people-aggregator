<?php

require_once "api/User/User.php";
require_once "api/Message/Message.php";
require_once "web/includes/classes/Pagination.php";

class MessageModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'middle';
  public $outer_template = 'outer_message_module.tpl';
  
  /**
  * uid is the user id of the user whose messages are to be displayed.
  */
  public $uid;
  
  /**
  * folder_name has name of the folder of whose messages are to be displayed. By Default it is Inbox
  */
  public $folder_name;
  
  /**
  * search_string has the string to be searched in messages. This string will be searched in subject, body and recipients of the 
  * user messages.
  */
  public $search_string;
  
  /**
  * variable used for paging
  */
  public $page_links, $page_prev, $page_next, $page_count, $Paging;

  function __construct() {
    $this->title = __('Mailbox');
    $this->page = 1;//by default first page will be displayed.  
  }

  public function initializeModule($request_method, $request_data) {
    global  $paging;

    if(empty(PA::$login_uid)) {
      return 'skip';
    } 
    $this->uid = PA::$login_uid;
    $this->mid = (!empty($request_data['mid'])) ? $request_data['mid'] : NULL;
    $this->search_string = (!empty($request_data['q'])) ? $request_data['q'] : NULL;
    $this->folder_name = (!empty($request_data['folder'])) ? $request_data['folder'] : INBOX;
    $this->folders = Message::get_user_folders($this->uid);
    $this->Paging = $paging;
    if (!empty($this->Paging['page'])) {
    	$this->page = $this->Paging['page'];
    }
    if(!isset($request_data['action'])) {
      if (!empty($this->search_string)) {
        $messages = Message::search($this->uid, $this->search_string);
        $this->Paging['count'] = count($messages);
        $messages = Message::search($this->uid, $this->search_string, $this->page, $this->Paging['show']);
        $this->title = __("Search Results");
      } else {
        $this->Paging['count'] = Message::load_folder_for_user($this->uid, $this->folder_name, true);         
        $messages = Message::load_folder_for_user($this->uid, $this->folder_name, false, $this->page, (int)$this->Paging['show']);
      }  
      $Pagination = new Pagination;
// echo "<pre>".print_r($messages,1)."</pre>";exit;
      $Pagination->setPaging($this->Paging);
      $this->page_prev = $Pagination->getPreviousPage();
      $this->page_next = $Pagination->getNextPage();
      $this->page_links = $Pagination->getPageLinks();      
      if(empty($this->search_string)) {
        $this->title .= ' : '.ucfirst($this->folder_name);
      }  
      
      $this->set_inner_template('center_inner_public.tpl');
      $this->inner_HTML = $this->generate_inner_html(array(
                                'messages'      => $messages,
                                'page_prev'     => $this->page_prev,
                                'page_next'     => $this->page_next,
                                'page_links'    => $this->page_links,
                                'folder_name'   => $this->folder_name,
                                'folders'       => $this->folders,
                                'current_theme_path' => PA::$theme_url,
                                'search_string' => $this->search_string
                          ));

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

  private function handleGET_view_message($request_data) {
    global $app, $error_msg;

    $message_details = Message::load_message(null, $this->mid, $this->uid);
    if((false === strpos($message_details['all_recipients'], PA::$login_user->login_name)) &&
      ($this->folder_name == INBOX))
    {
      $error_msg = __("You can't access other user's messages.");
      return;
    }
    if((false === strpos($message_details['sender_name'], PA::$login_user->login_name)) &&
      ($this->folder_name == 'Sent'))
    {
      $error_msg = __("You can't access other user's messages.");
      return;
    }
      $this->folder_name = Message::get_message_folder($message_details['index_id']);
      $mess = Message::load_folder_for_user($this->uid, $this->folder_name, false, $this->page);
      $total_msg = count($mess);
      for ($counter = 0; $counter < $total_msg; $counter++) {
        if ($mess[$counter]['message_id'] == $this->mid) {
          $prev_nxt_msg['previous'] = @$mess[$counter-1]['message_id'];            $prev_nxt_msg['next'] = @$mess[$counter+1]['message_id'];
          break; // ending the for loop
        }        
      }
      $this->title .= ' : '.ucfirst($this->folder_name);
      $this->set_inner_template('view_message.tpl');
      $this->inner_HTML = $this->generate_inner_html(array(
                                'prev_nxt_msg'    => $prev_nxt_msg,
                                'message_details' => $message_details,
                                'mid'             => $this->mid,
                                'folders'         => $this->folders,
                                'folder_name'     => $this->folder_name,
                                'current_theme_path' => PA::$theme_url,
                                'search_string'   => $this->search_string
                          ));
  }

  private function handleGET_conversation_view($request_data) {
      $conversations = Message::get_conversations($this->uid);
      $this->title .= ' : ' . __('Conversations');
      $this->set_inner_template('view_conversations.tpl');
      $this->inner_HTML = $this->generate_inner_html(array(
                                'conversations'    => $conversations,
                                'folders'         => $this->folders,
                                'folder_name'     => $this->folder_name,
                                'current_theme_path' => PA::$theme_url,
                                'search_string'   => $this->search_string
                          ));
  }

  private function handlePOST_delete($request_data) {
    global $msg;
    if (!empty($request_data['index_id'])) {
      Message::delete_message($request_data['index_id']);
      $msg_count = count($request_data['index_id']);
      if ($msg_count == 1) {
         $msg = __('1 message has been deleted successfully');
      } else {
        $msg = $msg_count.__(' messages have been deleted successfully');
      }
    }
    $folder = (!empty($request_data['folder'])) ? $request_data['folder'] : INBOX;
    $redirect_url = PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$folder&msg=$msg";
    $this->controller->redirect($redirect_url);
  }

  private function handlePOST_new_folder($request_data) {
    $error = false;
    // input validation for the folder name. Only alpha numerics are allowed.
    if(!Validation::validate_alpha_numeric($request_data['new_folder'], true)) {
      $msg = __('Folder creation failed. ').$request_data['new_folder'].__(' is not a valid folder name.');
      $redirect_url = PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$this->folder_name&msg=$msg";
      $error = true;
    }    
  
    if(!$error) {
      if(Message::create_folder(PA::$login_uid, $request_data['new_folder'])) {
        $msg = $request_data['new_folder'].' folder created successfully';
        $folder_name = $request_data['new_folder'];
        $redirect_url = PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$folder_name&msg=$msg";
      } else {
        $msg = __('Folder creation failed. You already have a folder named ').$request_data['new_folder'];
        $redirect_url = PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$this->folder_name&msg=$msg";
      }
    }
    $this->controller->redirect($redirect_url);
  }

  //code for moving the messages to some other folder.
  private function handlePOST_move($request_data) {
    // $request_data['sel_folder'] is -1 when there no folder has been made by the user
    if ($request_data['sel_folder'] == -1) {
      $msg = __('Please create a folder to move message(s)');
    } else {
      if(!empty($request_data['index_id'])) {
        //destination folder should not be the same
        if($folder_name != $request_data['sel_folder']) {
          $folder_id = Message::get_folder_by_name(PA::$login_uid, $request_data['sel_folder']);
          Message::move_message_to_folder($request_data['index_id'], $folder_id,$request_data['msgid']);
    
          $msg_count = count($request_data['index_id']);
          if($msg_count == 1) {
            $msg = __('1 message has been moved to folder ').$request_data['sel_folder'].__(' successfully');
          } else {
            $msg = $msg_count.__(' messages have been moved to folder ').$request_data['sel_folder'].__(' successfully');
          }
    
          //setting the folder selected to the one where messages have been moved.
          $folder_name = $request_data['sel_folder'];
        } else {
          $msg = __('Selected message(s) are already in ').$folder_name.__(' folder');
        }
      } else {
        $msg = __('Please select message(s) to move');
      }
    }
    $redirect_url = PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$this->folder_name&msg=$msg";
    $this->controller->redirect($redirect_url);
  }


  
  function set_inner_template($template_fname) {
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

/*
  public function handleEventHandler($request_data) { 
    $msg = NULL;
    $msg_array = array();
    $msg_array['failure_msg'] = $msg;
    $msg_array['success_msg'] = NULL;
    $redirect_url = NULL;
    $query_str = NULL;
    set_web_variables($msg_array, $redirect_url, $query_str); 
  }

  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html() {
    global $current_theme_path;
    
    switch ($this->mode) {
      case 'view_mesage':
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/view_message.tpl';
      break;
      case 'view_conversations':
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/view_conversations.tpl';
      break;
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $folders = Message::get_user_folders($this->uid);
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('current_theme_path', $current_theme_path);
    $inner_html_gen->set('folders', $folders);
    
    if ($this->mode == 'view_mesage') {
      $message_details = Message::load_message(null, $this->mid, $this->uid);
      $this->folder_name = Message::get_message_folder($message_details['index_id']);
      $mess = Message::load_folder_for_user($this->uid, $this->folder_name, false, $this->page);
      $total_msg = count($mess);
      for ($counter = 0; $counter < $total_msg; $counter++) {
        if ($mess[$counter]['message_id'] == $this->mid) {
          $prev_nxt_msg['previous'] = @$mess[$counter-1]['message_id'];            $prev_nxt_msg['next'] = @$mess[$counter+1]['message_id'];
          break; // ending the for loop
        }        
      }
      $this->title .= ' : '.ucfirst($this->folder_name);
      $inner_html_gen->set('prev_nxt_msg', $prev_nxt_msg); 
      $inner_html_gen->set('message_details', $message_details);
      $inner_html_gen->set('mid', $this->mid);
      $inner_html_gen->set('search_string', $this->search_string);
    } else if ($this->mode == 'view_conversations') {
    	$conversations = Message::get_conversations($this->uid);
      $inner_html_gen->set('conversations', $conversations); 
      $this->title .= ' : ' . __('Conversations');
    } else {
      if (empty($this->folder_name)) {
        $this->folder_name = INBOX;
      }
      //fetches all messages in the given folder
      if (!empty($this->search_string)) {
        $messages = Message::search($this->uid, $this->search_string);
        $this->Paging['count'] = count($messages);
        $messages = Message::search($this->uid, $this->search_string, $this->page, (int)$this->Paging['show']);
        $inner_html_gen->set('search_string', $this->search_string);
      } else {
        $this->Paging['count'] = Message::load_folder_for_user($this->uid, $this->folder_name, true);         
        $messages = Message::load_folder_for_user($this->uid, $this->folder_name, false, $this->page, (int)$this->Paging['show']);
      }  
      $Pagination = new Pagination;
      $Pagination->setPaging($this->Paging);
      $this->page_prev = $Pagination->getPreviousPage();
      $this->page_next = $Pagination->getNextPage();
      $this->page_links = $Pagination->getPageLinks();      
      $this->title .= ' : '.ucfirst($this->folder_name);
      
      $inner_html_gen->set('messages', $messages);
      $inner_html_gen->set('page_prev', $this->page_prev);
      $inner_html_gen->set('page_next', $this->page_next);
      $inner_html_gen->set('page_links', $this->page_links);      
    }
    $inner_html_gen->set('folder_name', $this->folder_name);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
*/
}