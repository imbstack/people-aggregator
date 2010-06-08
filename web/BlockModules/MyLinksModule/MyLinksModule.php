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

require_once "api/Links/Links.php";

class MyLinksModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public  $uid, $links_data_array;

  function __construct() {
    parent::__construct();
    $this->title = __("My Links");
    $this->html_block_id = 'MyLinksModule';
  }

  public function initializeModule($request_method, $request_data)  {
    $this->uid = (!empty(PA::$page_uid)) ? PA::$page_uid : PA::$login_uid; 
    if($this->uid == PA::$login_uid) {
      $this->outer_template = 'outer_private_side_module.tpl';
    } else {
      $this->outer_template = 'outer_public_side_module.tpl';
    }
    switch ($this->page_id) {
      case PAGE_USER_PRIVATE:
        $this->title = __("My Links");;
      break;
      case PAGE_USER_PUBLIC:
      default:
        $this->title = abbreviate_text(ucfirst(ucfirst(PA::$page_user->first_name).'\'s '), 18, 10);
        $this->title .= __("Links");;
    }
    $this->manage_links_url = PA::$url . "/links_management.php";
    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
  }


 function render() {
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    // global var $_base_url has been removed - please, use PA::$url static variable

    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    $inner_html_gen = & new Template($inner_template);

    $links_data_array = $this->get_user_links();
    $inner_html_gen->set('links_data_array', $links_data_array);

    if(count($links_data_array) > 0) {
        $this->manage_links_url = PA::$url .'/links_management.php';
        $inner_html_gen->set('manage_links_url', $this->manage_links_url);
    }

    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

  function get_user_links () {
      $condition = array('user_id'=> $this->uid, 'is_active'=> 1);
      $Links = new Links();
      $category_list = $Links->load_category ($condition);
      if( count($category_list) > 0 ) {
        $links_data_array  = array();
        for($counter = 0; $counter < count($category_list); $counter++) {
            $links_data_array[$counter]['category_id'] = $category_list[$counter]->category_id;
            $links_data_array[$counter]['category_name'] = $category_list[$counter]->category_name;

            $Links->user_id = $this->uid;
            $condition = array('category_id'=>$category_list[$counter]->category_id, 'is_active'=> 1);
            $links_array = $Links->load_link ($condition);
            $links_data_array[$counter]['links'] = $links_array;
        }
      return $links_data_array;
      }
  }
}
?>
