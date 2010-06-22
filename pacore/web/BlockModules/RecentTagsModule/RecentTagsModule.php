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
require_once "api/Tag/Tag.php";
require_once "api/Comment/Comment.php";

class RecentTagsModule extends Module {

    public $module_type = 'user|group|network';

    public $module_placement = 'left|right';

    public $outer_template = 'outer_public_side_module.tpl';

    public $cid, $tags_id_name, $page_id, $limit;

    function __construct() {
        parent::__construct();
        $this->title = __('Browse Tags');
    }

    public function initializeModule($request_method, $request_data) {
        //    if ($this->page_id == PAGE_SEARCH) {
        $this->limit = 50;
        //    }
    }

    public function get_recent_tags() {
        $tags_id_name = Tag::load_tag_soup($this->limit);
        $this->tags_id_name = $tags_id_name;
    }

    public function render() {
        $this->get_recent_tags();
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    public function generate_inner_html() {
        switch($this->mode) {
            default:
                $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/side_inner_public.tpl';
        }
        $inner_html_gen = &new Template($tmp_file);
        $inner_html_gen->set('tags_id_name', $this->tags_id_name);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>