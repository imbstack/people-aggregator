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

require_once "api/Category/Category.php";
require_once "api/Content/Content.php";
require_once "ext/Group/Group.php";


class LinksModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'middle';
  public $uid;
  public $targets;
  public $show_external_blogs;
  public $outer_template = 'outer_public_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->main_block_id = "mod_links_management";
    $this->title = __('Links Control Panel');
    $this->html_block_id = 'LinksModule';
    $this->block_type = 'Links';   
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
   
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('link_categories_array', $this->get_link_categories());
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
  
  function get_link_categories () {
      $Links = new Links();
      $conditon = array('user_id'=> $_SESSION['user']['id'], 'is_active'=> 1);
      $link_categories_array = $Links->load_category ($conditon);
      return $link_categories_array;      
  }
}
?>