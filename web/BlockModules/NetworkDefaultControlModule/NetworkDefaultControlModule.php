<?php
require_once "ext/Album/Album.php";
require_once "web/includes/constants.php";
require_once "web/includes/classes/XmlConfig.class.php";
require_once "web/includes/classes/NetworkConfig.class.php";

class NetworkDefaultControlModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';

  function __construct() {
    parent::__construct();
    $this->html_block_id = "AdminNetSettings";
  }

   function render() {
    $conf = new NetworkConfig();
//    echo "<pre>".print_r($conf,1)."</pre>";
/*
    $store = new XmlConfig("web/config/backend.xml");
    $store->loadFromArray($net_vars_arr, $store->root_node);
    $store->saveToFile();
*/
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  function get_albums() {
    $images = Album::load_all($_SESSION['user']['id'], IMAGE_ALBUM);
    $audios = Album::load_all($_SESSION['user']['id'], AUDIO_ALBUM);
    $videos = Album::load_all($_SESSION['user']['id'], VIDEO_ALBUM);
    return array('images'=>$images,'audios'=>$audios,'videos'=>$videos);
  }
  function get_content() {
    $param['page'] = 1;
    $param['show'] = 20; // get recent 20 contents
    $condition = 'type = '.BLOGPOST.' AND author_id = '.$_SESSION['user']['id'];
    $content = Content::get($param, $condition);
    return $content;
  }
  function generate_inner_html() {
     

    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_html_'.$this->tpl_to_load.'.tpl';
    $net_details = & new Template($tmp_file);
    if ($this->tpl_to_load == 'stats') {
      $net_details->set('is_edit', @$this->is_edit);
      $net_details->set('network_stats', @$this->network_stats);
      $net_details->set('meta_network_reci_relation', @$this->meta_network_reci_relation);
      $net_details->set('available_languages', $this->getLanguages());
    }  else {
      $net_details->set('user_albums', $this->get_albums());
      $net_details->set('content', $this->get_content());
      $net_details->set('ack_message', @$this->ack_message);
    }
    $net_details->set('form_data', $this->form_data);
    if (@$this->control_type == 'basic') {
      $categories = $this->get_categories();
      $net_details->set('categories', $categories);
    }
    $net_details->set('page_id', $this->page_id);

    $net_details->set('config_navigation_url',
                       network_config_navigation($this->tpl_to_load));
    $inner_html = $net_details->fetch();
    return $inner_html;
  }


  function getLanguages() {
    global $app;
      $languages = array();
      foreach($app->installed_languages as $name => $path) {
        $languages[$name] = ucwords($name);
      }
      return $languages;
  }

  /**
  *  Function to get the list of categories.
  */

  function get_categories() {
      $category_array = array();
      $category_list = Category::build_root_list();
      if(is_array($category_list)){
        foreach ($category_list  as $category) {
          $category_array[] = array('category_id'=>$category->category_id,'name'=>$category->name);
        }
      }
      return $category_array;
  }

}
