<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* EventCalendarSidebarModule.php is a part of PeopleAggregator.
* This module is like EventCalendarModule, except, not surprisingly,
*  the calendar this one produces either goes in the left or right sidebar.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau?
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php

require_once "api/Cache/Cache.php";

class EventCalendarSidebarModule extends Module {

  public $module_type = 'user|group';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  function __construct() {
    $this->html_block_id = "EventCalendarSidebarModule";
    parent::__construct();
  }
  
  /** !!
  * Initializes the module, by moving some variables around setting some
  *  things.
  * @param string $request_method Unused.
  * @param array $request_data Unused.
  */
  public function initializeModule($request_method, $request_data)  {
    if(!empty($this->shared_data['calendar_info'])) {
      $info = $this->shared_data['calendar_info'];
      $this->assoc_type = $info['assoc_type'];
      $this->assoc_id = $info['assoc_id'];
      $this->title = $info['title'];
      if (!empty($info{'group_info'})) {
      	$this->title = __('Group Events');
      }
      $this->may_edit = $info['may_edit'];
      $this->mode = $info['mode'];

      if (!empty($info['display_mode'])) {
				$this->display_mode = $info['display_mode'];
      }
    } else {
      return 'skip';
    }  
  }

  /** !!
  * Passes the HTML along to be rendered.
  *
  * @return string The HTML.
  */
  function render() {
    if (empty($this->title)) {
      $this->title = __('Personal Events');
    }
    if (empty($this->assoc_id)) {
      $this->assoc_id = $_GET['uid'];
    }

    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }
  
  /** !!
  * Generates the HTML to be passed along by {@see render()}
  * @return string HTML.
  */
  function generate_inner_html() {
    
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    $inner_html_gen = new Template($tmp_file, $this);
    
    $inner_html_gen->set('assoc_id', $this->assoc_id);
    $inner_html_gen->set('assoc_type', $this->assoc_type);
    //$inner_html_gen->set('assoc_title', $this->assoc_title);
    $inner_html_gen->set('may_edit', $this->may_edit);

    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>
