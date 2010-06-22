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
require_once PA::$blockmodule_path."/GroupModule/GroupModule.php";
require_once "api/Group/Group.php";

class LargestGroupsModule extends GroupModule {

    public $module_type = 'group|network';

    public $module_placement = 'left|right';

    public $links;

    public $limit = 5;

    public function __construct() {
        parent::__construct();
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
        $this->inner_HTML = $this->generate_inner_html($this->links);
        $content = parent::render();
        return $content;
    }
}
?>
