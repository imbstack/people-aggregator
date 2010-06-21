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
class RelationsModule extends Module {

    public $module_type = 'user';

    public $module_placement = 'left|right';

    public $outer_template = 'outer_public_side_module.tpl';

    public $links;

    public $rel_term;

    public function __construct() {
        parent::__construct();
    }

    public function initializeModule($request_method, $request_data) {
        if(empty(PA::$login_uid)) {
            return 'skip';
        }
        $this->rel_term = __('Friend');
        if(isset(PA::$extra['relationship_show_mode']['term'])) {
            $this->rel_term = PA::$extra['relationship_show_mode']['term'];
        }
        switch($this->page_id) {
            case PAGE_USER_PUBLIC:
                if(empty(PA::$page_uid)) {
                    return 'skip';
                }
                $this->title = abbreviate_text(sprintf(__("%s's friends"), PA::$page_user->display_name), 18, 10);
                break;
            case PAGE_USER_PRIVATE:
                if(empty(PA::$login_uid)) {
                    return 'skip';
                }
                $this->title = __("My friends");
                break;
            default:
                $this->title = __("Friends");
        }
    }

    public function render() {
        $status = null;
        if(!empty(PA::$extra['reciprocated_relationship']) && PA::$extra['reciprocated_relationship'] == NET_YES) {
            $status = APPROVED;
        }
        $relations = Relation::get_all_relations((int) PA::$uid, 6, FALSE, 'ALL', 0, 'created', 'DESC', 'internal', $status, PA::$network_info->network_id);
        for($i = 0; $i < count($relations); $i++) {
            $count_relations = Relation::get_relations($relations[$i]['user_id'], $status, PA::$network_info->network_id);
            $relations[$i]['no_of_relations'] = count($count_relations);
        }
        $this->links = $relations;
        if(!empty($this->links)) {
            $this->view_all_url = PA::$url.'/'.FILE_VIEW_ALL_MEMBERS.'?view_type=relations&uid='.PA::$uid;
        }
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    public function generate_inner_html() {
        switch($this->mode) {
            case PRI:
                $this->outer_template = 'outer_private_side_module.tpl';
                $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/side_inner_public.tpl';
                break;
            default:
                $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/side_inner_public.tpl';
                break;
        }
        $inner_html_gen = &new Template($tmp_file);
        $inner_html_gen->set('links', $this->links);
        $inner_html_gen->set('rel_term', $this->rel_term);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>
