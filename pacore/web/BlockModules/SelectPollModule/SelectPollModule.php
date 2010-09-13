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
require_once "api/Content/Content.php";
require_once "api/Poll/Poll.php";
/**
 * This class generates inner html of selecting polls 
 * @package BlockModules
 * @subpackage 
 */ 

class SelectPollModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  
  public $poll_id;
  public $content_id;
  public $options;
  public $topic;
  public $current_poll;
  public $group_id;

  public function __construct() {
    parent::__construct();
    $this->poll_id = array();
    $this->content_id = array();
    $this->options = array();
  }

  public function initializeModule($request_method, $request_data) {
	  if (empty(PA::$login_uid)) return 'skip';
    if (!empty($request_data['type'])) { 
      $this->mode = htmlspecialchars($request_data['type']);
	}
    if (!empty($request_data['action']) && $request_data['action'] == "delete") {
      $obj = new poll();
      $p_id = $request_data['id'];
      $c_id = $request_data['cid'];
      $obj->delete_poll($p_id, $c_id);
      $this->message = __('Poll has been deleted successfully.');
        if ($obj->group_id != 0) {
		  $this->redirect2 = PA::$url."/group_poll.php?gid=".$obj->group_id."&type=select"; 
	}else{
	      $this->redirect2 = PA::$url."/".FILE_DYNAMIC;
	}
      $this->queryString = '?page_id='.PAGE_POLL.'&type=select';
      $this->isError = FALSE;	
      $this->setWebPageMessage();
	}
  }

  public function handleSelectPollModuleSubmit($request_method, $request_data) {
    switch ($request_method) {
      case 'POST':
        if(method_exists($this, 'handlePOST')) { 
          $this->handlePOST($request_data);
        }
      break;
      case 'GET':
        if(method_exists($this, 'handleGET')) {
          $this->handleGET($request_data);
      } 
    }
  }

  public function handlePOST($request_data) {
    if (!empty($request_data['submit'])) {
		$obj = new Poll();
      $obj->poll_id = $request_data['poll'];
      $obj->prev_changed = $request_data['prev_poll_changed'];
      $obj->prev_poll_id = $request_data['prev_poll_id'];
      $obj->save_current();
      $this->message = __('Poll has been saved successfully.');
      $this->redirect2 = NULL;
      $this->queryString = NULL;
      $this->isError = FALSE;
      $this->setWebPageMessage();
    }

    if(!empty($request_data['create'])) {
      $poll_topic = $request_data['topic'];
      $cnt = $request_data['num_option'];
      $poll_option = array();
      for ($i =1;$i<=$cnt;$i++) {
        $poll_option['option'.$i] = $request_data['option'.$i];
      }
      $option = serialize($poll_option);
      $obj = new Poll(); 
      $obj->author_id = PA::$login_uid;
      $obj->type = POLL;
      $obj->title = $poll_topic;
      $obj->body = $option;
      $obj->parent_collection_id = 0;
	  $obj->user_id = PA::$login_uid;
		$obj->group_id = 0;
		if ($request_data['group_id'] != NULL) {
			$obj->group_id = (int)$request_data['group_id'];
		}
      $obj->options = $option;
      $obj->is_active = INACTIVE;
      $obj->save_poll();
	  $this->message = __('Poll has been created successfully.');
	  if ($obj->group_id != 0) {
		  $this->redirect2 = PA::$url."/group_poll.php?gid=".$obj->group_id."&type=select"; 
  		} else {
		  $this->redirect2 = PA::$url.PA_ROUTE_CONFIG_POLL."?type=select";
	  }
      $this->queryString = NULL;
      $this->isError = FALSE;
      $this->setWebPageMessage();
    }
  }

  public function render() {
    $obj = new Poll();
    $topic = $obj->load_poll(0,$_GET['gid']);
    foreach($topic as $t) {
      if(is_object($t)) {
        $this->poll_id[] = $t->poll_id;
        $this->content_id[] = $t->content_id;
        $this->options[] = unserialize($t->options);
      } 
    }
    $this->topic = $topic;
    $this->current_poll = $obj->load_current();
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }
  
  public function generate_inner_html () {
    switch ($this->mode) {
      case 'select':
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/select_poll.tpl';
        $this->title = 'Select Poll';
      break;
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/create_poll.tpl';
        $this->title = 'Create Poll';
      break;  
    }
    $inner_html_gen= new Template($inner_template);
    $inner_html_gen->set('topic',$this->topic);
    $inner_html_gen->set('poll_id',$this->poll_id);
    $inner_html_gen->set('content_id',$this->content_id);
    $inner_html_gen->set('current_poll',$this->current_poll);
    $inner_html_gen->set('options',$this->options);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>
