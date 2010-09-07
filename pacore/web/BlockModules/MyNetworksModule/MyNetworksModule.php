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
// global var $path_prefix has been removed - please, use PA::$path static variable

require_once "api/Category/Category.php";
require_once "api/Group/Group.php";
require_once "web/includes/classes/Pagination.php";

class MyNetworksModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public $uid, $Paging;
  public $page_links, $page_prev, $page_next, $page_count;

  function __construct() {
    parent::__construct();
    $this->html_block_id = 'MyNetworksModule';
  }
  
  public function initializeModule($request_method, $request_data) {
    if (empty(PA::$login_uid)) return 'skip';
  }

  public function get_links() {
    //find total count
    ($this->title) ? $this->title : $this->title = __("My Networks");
    // Make the array of parameter
    if ( !isset($this->uid) || ($this->uid == PA::$login_uid)) {
      $sql_param=array();
      $sql_param[] = array('key'=>'NU.user_id', 'operator' => '=' , 'value' => PA::$login_uid);
      $sql_param[] = array('key'=>'N.is_active', 'operator' => '=' , 'value' => '1');
      $sql_param[] = array('key'=>'N.type', 'operator' => '<>' , 'value' => MOTHER_NETWORK_TYPE);
      $this->Paging["show"] = 5;
      $params1 = array('page'=>1,//page number
                    'show'=>5//how many records on the page
                    ) ;
    } else {
        $sql_param=array();
        $sql_param[] = array('key'=>'NU.user_id', 'operator' => '=' , 'value' => $this->uid);
        $sql_param[] = array('key'=>'N.is_active', 'operator' => '=' , 'value' => '1');
        $sql_param[] = array('key'=>'N.type', 'operator' => '=' , 'value' => REGULAR_NETWORK_TYPE);
        $this->Paging["show"] = 5;
        $params1 = array('page'=>1,//page number
                    'show'=>5//how many records on the page
                    ) ;
    }
    $usernetworks = Network::get_user_network_info($sql_param, $params1);
    //___end___ find paginated networks
    return $usernetworks;
  }

  function render() {
    $this->links = $this->get_links();
    if (sizeof($this->links)) {
      $this->view_all_url = PA::$url."/networks_home.php?uid=".$this->uid;
    }
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    switch ( $this->mode ) {
      case PRI:
        $this->outer_template = 'outer_private_side_module.tpl';
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    $inner_html_gen = new Template($tmp_file);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('uid', $this->uid);
    $inner_html_gen->set('page_prev', $this->page_prev);
    $inner_html_gen->set('page_next', $this->page_next);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html = $inner_html_gen->fetch();

    return $inner_html;
  }

}
?>
