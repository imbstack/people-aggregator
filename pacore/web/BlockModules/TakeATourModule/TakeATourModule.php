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
include_once "api/User/User.php";
require_once "api/ModuleData/ModuleData.php";

class TakeATourModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public $max_height,$links;

  function __construct() {
    $this->html_block_id = "TakeATourModule";
    $this->main_block_id = "mod_take_a_tour";
    parent::__construct();
    $this->title = __('Take A Tour');
  }


  function render() {
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {

    $inner_template = NULL;
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    $links = ModuleData::get('TakeTour');
    if (!empty($links)) $links = unserialize($links);
    if (empty($links) || !is_array($links)) {
      $this->do_skip = TRUE;
      return;
    }
    $obj_inner_template = & new Template($inner_template);
    $obj_inner_template->set('links', $links);
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }

}
?>