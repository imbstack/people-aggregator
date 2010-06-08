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

class ForumStatsModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';

  function __construct() { 
    parent::__construct();
    $this->title = __('Forum Statistics');
    $this->html_block_id = 'ForumStatsModule';
  }

  function handleRequest($request_method, $request_data) {
    $this->outer_template = 'outer_public_side_module.tpl';
    if(!isset($this->shared_data['board_statistics'])) {
      return 'skip';
    }
    $board = $this->shared_data['board'];
    $statistics = $this->shared_data['board_statistics'];
    $this->set_inner_template('statistics.tpl.php');
    $this->inner_HTML = $this->generate_inner_html(array('page_id'           => $this->page_id,
                                                         'board'             => $board,
                                                         'statistics'        => $statistics
                                                  ));
    
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
}
?>