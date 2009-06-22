<?php
require_once PA::$blockmodule_path."/GroupModule/GroupModule.php";
require_once "ext/Group/Group.php";

class LargestGroupsModule extends GroupModule {

  public $module_type = 'group|network';
  public $module_placement = 'left|right';
  public $links;
  public $limit = 5;
  
  public function __construct() {
    $this->title = sprintf(__("Largest %s"), PA::$group_noun_plural);
  }
  //module initialization code .
  public function initializeModule($request_method, $request_data) {

    $this->links = $this->get_links();
  }
  private function get_links() {
    $obj_group = new Group();
    $links = $obj_group->get_largest_groups($this->limit);
    return $links;
  }
  
  public function render() {
    $this->inner_HTML = $this->generate_inner_html ($this->links);
    $content = parent::render();
    return $content;
  }

  
}
?>
