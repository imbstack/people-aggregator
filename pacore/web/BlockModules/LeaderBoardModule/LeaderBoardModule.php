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
require_once "api/User/UserPopularity.class.php";

class LeaderBoardModule extends Module {

    public $module_type = 'network';

    public $module_placement = 'left|right|middle';

    function __construct() {
        parent::__construct();
        $this->html_block_id = 'LeaderBoardModule';
    }

    function initializeModule($request_method, $request_data) {
        global $error_msg;
        if($this->column == 'middle') {
            $this->page_size      = 10;
            $this->page           = (!empty($request_data['page'])) ? $request_data['page'] : 0;
            $this->title          = PA::$network_info->name.' '.__('Leader Board');
            $this->outer_template = 'outer_public_center_module.tpl';
            $this->set_inner_template('module_middle.tpl.php');
        }
        else {
            $this->page           = 0;
            $this->title          = __('Leader Board');
            $this->page_size      = 5;
            $this->outer_template = 'outer_public_side_module.tpl';
            $this->set_inner_template('module_default.tpl.php');
            $this->view_all_url = PA::$url.PA_ROUTE_LEADER_BOARD;
        }
        $pagination_links        = null;
        $users_ranking           = array();
        $users_counter_increment = 0;
        $nb_items                = UserPopularity::countUserPopularity();
        if($nb_items > 0) {
            $rankings   = UserPopularity::listUserPopularity(null, 'popularity', 'DESC');
            $max_rank   = $rankings[0]->get_popularity();
            $pagination = UserPopularity::getPagging($rankings, $this->page_size, $this->page);
            $page_items = $pagination->getPageItems();
            foreach($page_items as $idx => $item) {
                try {
                    $user = new User();
                    $user->load((int) $item->get_user_id());
                    $user_generaldata     = User::load_user_profile($item->get_user_id(), PA::$login_uid, GENERAL);
                    $user->profile_info   = sanitize_user_data($user_generaldata);
                    $user->ranking_points = $item->get_popularity();
                    $user->ranking_stars  = intval(($user->ranking_points*5)/$max_rank);
                    $user->last_activity  = $item->get_time();
                    $users_ranking[$idx]  = $user;
                }
                catch(Exception$e) {
                    $error_msg = "Exception in LeaderBoardModule, message: <br />".$e->getMessage();
                    return 'skip';
                }
            }
            $pagination_links = $pagination->getPaggingLinks(PA::$url.PA_ROUTE_LEADER_BOARD, 'page', 'pagging', 'pagging_selected');
            $users_counter_increment = $this->page_size*$this->page;
        }
        $this->inner_HTML = $this->generate_inner_html(array('page_id' => $this->page_id, 'users_ranking' => $users_ranking, 'pagination_links' => $pagination_links, 'increment' => $users_counter_increment));
    }

    function handleRequest($request_method, $request_data) {

        /*  
            if(!isset($this->shared_data['board_statistics'])) {
              return 'skip';
            }
            $board = $this->shared_data['board'];
            $statistics = $this->shared_data['board_statistics'];
            $this->set_inner_template('statistics.tpl.php');
            $this->inner_HTML = $this->generate_inner_html(array('page_id'           => $this->page_id,
                                                                 'board'             => $board,
                                                                 'statistics'        => $statistics
                                                          ));
        */
    }

    function set_inner_template($template_fname) {
        $this->inner_template = PA::$blockmodule_path.'/'.get_class($this)."/$template_fname";
    }

    function render() {
        $content = parent::render();
        return $content;
    }

    function generate_inner_html($template_vars = array()) {
        $inner_html_gen = &new Template($this->inner_template);
        foreach($template_vars as $name => $value) {
            if(is_object($value)) {
                $inner_html_gen->set_object($name, $value);
            }
            else {
                $inner_html_gen->set($name, $value);
            }
        }
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>