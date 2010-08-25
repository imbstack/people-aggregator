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

class GroupBulletinsModule extends Module {
  
  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->title = __("Send Group Bulletins");
    $this->html_block_id = "GroupBulletinsModule";
  }

   function render() {    
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html() {
     
    switch( $this->mode ) {
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $group_bulletin = & new Template($tmp_file, $this);
    $group_bulletin->set('config_navigation_url',
                       network_config_navigation('bulletins'));
    
    $group_bulletin->set('preview_msg', $this->preview_msg);
    $inner_html = $group_bulletin->fetch();
    
    return $inner_html;
  }
}
?>
