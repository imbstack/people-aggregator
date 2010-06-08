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

class EventCalendarModule extends Module {

  public $module_type = 'user|group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_edit_profile_module.tpl';

  function __construct() {
    parent::__construct();
    $this->html_block_id = "EventCalendarModule";
    $this->block_type = 'EventCalendar';
  }

  public function initializeModule($request_method, $request_data)  {
    if(!empty($this->shared_data['calendar_info'])) {
      $info = $this->shared_data['calendar_info'];
      $this->assoc_type = $info['assoc_type'];
      $this->assoc_id = $info['assoc_id'];
      $this->title = $info['title'];
      if(!empty($this->shared_data['user_info'])) {
        $this->assoc_title = $this->shared_data['user_info']->login_name;
      } else if(!empty($this->shared_data['group_info'])) {
        $this->assoc_title = $this->shared_data['group_info']->title;
      } else if(!empty($this->shared_data['network_info'])) {
        $this->assoc_title = $this->shared_data['network_info']->name;
      } else {
        $this->assoc_title = __("Unknown event association");
      }
      $this->may_edit = $info['may_edit'];
      $this->mode = $info['mode'];
    } // here should be added alternative initialization code !
  }

   function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    switch ( $this->mode ) {
     default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';   
    }
    
    $info = & new Template($inner_template);

    $info->set('assoc_id', $this->assoc_id);
    $info->set('assoc_type', $this->assoc_type);
    $info->set('assoc_title', $this->assoc_title);
    $info->set('title', $this->title);
    $info->set('may_edit', $this->may_edit);
    $info->set('msg', @$this->msg);
    $info->set('msg2', @$this->msg2);

    $inner_html = $info->fetch();

    return $inner_html;
  }

}
?>