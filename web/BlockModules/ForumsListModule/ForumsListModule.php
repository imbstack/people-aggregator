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
error_reporting(E_ALL);

require_once "api/Forum/PaForumBoard.class.php";
// require_once "web/includes/classes/...";

class ForumsListModule extends Module {
  
  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  private $class_name;
  private $nid;
  
  const max_title_length = 28;

  function __construct() {
    parent::__construct();
    $this->title = __('Forums List');
    $this->class_name = get_class($this);
    $this->html_block_id = 'ForumsListModule';
  }

  function initializeModule($request_method, $request_data) {
    switch($this->column) {
      case 'middle':
        throw new Exception(" $this->class_name error: ForumsList module can't be placed into the middle column.");
      break;

      case 'left':
      case 'right':
        $this->boards_info = $this->getBoardsInfo();
        $this->outer_template = 'outer_public_side_module.tpl';
        $this->set_inner_template('module_default.tpl.php');
      break;
      default:
    }
//  echo PA::$domain_suffix.'<pre>'.print_r($this->boards_info,1).'</pre>';
    $this->inner_HTML = $this->generate_inner_html(array('boards_info' => $this->boards_info));
  }

  private function getBoardsInfo() {
    $boards_info = array();
    $this->nid = (isset($this->shared_data['network_id'])) ? $this->shared_data['network_id'] : PA::$network_info->network_id;
    $boards = PaForumBoard::listPaForumBoard("network_id = $this->nid AND is_active = 1", 'type', 'ASC', 10);
    if(count($boards) > 0) {
      for($i = 0; $i < count($boards); $i++) {
        $title = $boards[$i]->get_title();
        $type  = $boards[$i]->get_type();
        $boards_info[$i]['title'] = (strlen($title) <= self::max_title_length)
                                  ? $title
                                  : substr($title, 0, self::max_title_length +3) . '...';
        $boards_info[$i]['type']  = $type;

        $net_id = $boards[$i]->get_network_id();
        if(Network::is_mother_network($net_id))  {
          $address = 'www';
        } else {
          $network = Network::get_by_id((int)$net_id);
          $address = $network->address;
        }

        $url = "http://$address." . PA::$domain_suffix . PA_ROUTE_FORUMS . "/network_id=" . $net_id;
        switch($type) {
          case PaForumBoard::network_board:
          break;
          case PaForumBoard::group_board:
            $url .= "&gid=" . $boards[$i]->get_owner_id();
          break;
          case PaForumBoard::personal_board:
            $url .= "&user_id=" . $boards[$i]->get_owner_id();
          break;
        }
        $boards_info[$i]['url']  = $url;
      }
    }
    return $boards_info;
  }

  
  function handleRequest($request_method, $request_data) {
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