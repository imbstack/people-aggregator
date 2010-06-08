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
  //TODO: Page Header & Comments
  class SplashPageModule extends Module {
    
  public $module_type = 'system|network';
  public $module_placement = 'middle';
    public $outer_template = 'outer_public_group_center_module.tpl';
    
    /**
    * $links will hold the data for featured networks on splash page.
    */
    public $links;
    
    function __construct() {
      parent::__construct();
      $this->title = 'Configure Splash Page';
      $this->html_block_id = "SplashPageModule";
    }
    
    function render() {
      $network = new Network();
      $this->network_links = $network->get();
      $this->inner_HTML = $this->generate_inner_html();
      $links = parent::render();
      return $links;
    }    
    
    function generate_inner_html() {
      switch ($this->mode) {
        case 'configure':
          $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/configure.tpl';
          $this->title = 'Configure Splash Page';
        break;
        case 'showcase':
          $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/showcase.tpl';
          $this->title = __('Showcase Module');
        break;
        case 'video_tours':
          $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/video_tours.tpl';
          $this->title = 'Video Tours';
        break;
        case 'register_today':
          $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/registration_today.tpl';
          $this->title = 'Register Today';
        break;
        case 'server_announcement':
          $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/server_announcement.tpl';
          $this->title = 'SERVER ANNOUNCEMENT';
        break;
          case 'survey':
          $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/survey.tpl';
          $this->title = 'SURVEY';
          break;
        default:  
          $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/info_boxes.tpl';
          $this->title = 'Info Boxes';
      }
      $config_navigation_url = network_config_navigation($_GET['section']);
      $inner_html_gen = & new Template($tmp_file);
      $inner_html_gen->set('network_links', $this->network_links);
      //$inner_html_gen->set ('featured_network', @$this->featured_network);     
      $inner_html_gen->set ('showcase', @$this->showcase); 
      $inner_html_gen->set ('survey', @$this->survey);  
      $inner_html_gen->set ('info_boxes', @$this->info_boxes);
      $inner_html_gen->set ('networks_data', $this->networks_data);
      $inner_html_gen->set ('section', $this->mode);
      $inner_html_gen->set ('config_navigation_url', $config_navigation_url);
      $inner_html = $inner_html_gen->fetch();
      return $inner_html;
    }
    
  }

?>
