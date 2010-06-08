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
class RequestModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  public $error = FALSE;
  public $error_msg;
  public $success = FALSE; 
  public $success_msg;

  public function __construct() {
    parent::__construct();
    $this->title = __('Send request to join this network');
  }

  public function initializeModule($request_method, $request_data) {  
    if (empty(PA::$login_uid)) return 'skip';
  }

  public function handleRequestModuleSubmit($request_method, $request_data) {
    switch($request_method) {
      case 'POST':
        if(method_exists($this, 'handlePOST')) { 
           $this->handlePOST($request_data);
        }
    }
  }

  public function handlePOST($request_data) {
    global $mothership_info;
    if (!empty($request_data['request'])) {
      //code for requesting
      $this->error = FALSE;
      $this->success = FALSE;
      try {
        $request_sent = Network::join(PA::$network_info->network_id, PA::$login_uid);
        if (!empty($request_sent)) {
          $this->success = TRUE;
        }
      } catch (PAException $e) { 
        $join_error = $e->message;
        $this->error = TRUE;
      }
      if (!empty($this->error)) {
        $this->error_msg = sprintf(__('Your request to join this network could not be sent due to following reason: %s. You can go back to the home network by clicking Return to home network'), $join_error);
      } else {
        $this->success_msg = __('Your request to join this network has been successfully sent to the moderator of this network. The Moderator will check this request and approve or deny the request. You can go back to mother network by clicking the button Return to home network');
      }
       $this->mode = 'msg_display';
    } 
    if (!empty($request_data['back'])) {
      //redirect to mother network 
      global $mothership_info;
      $this->message = __('Return to home network successfully.');
      $this->redirect2 = NULL;
      $his->queryString = NULL;
      $this->isError = FALSE;
      $this->setWebPageMessage();
    }
  }

  public function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  public function generate_inner_html () {
    switch ($this->mode) {
      case 'msg_display':
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public_msg.tpl';
        $this->title = __('Request Message');
      break;
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('error', $this->error);
    $inner_html_gen->set('error_msg', $this->error_msg);
    $inner_html_gen->set('success', $this->success);
    $inner_html_gen->set('success_msg', $this->success_msg);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>