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
require_once "api/Forum/PaForumBoard.class.php";
require_once "api/Forum/PaForumsUsers.class.php";

function cmpThreadCreated($a, $b) {
    if(strtotime($a->get_created_at()) == strtotime($b->get_created_at())) {
        return 0;
    }
    return(strtotime($a->get_created_at()) < strtotime($b->get_created_at())) ? 1 :-1;
}

class ForumLatestPostsModule extends Module {

    public $module_type = 'user|group|network';

    public $module_placement = 'left|right|middle';

    public $outer_template = 'outer_public_side_module.tpl';

    function __construct() {
        parent::__construct();
        $this->title         = __('Latest Forum Posts');
        $this->html_block_id = 'ForumLatestPostsModule';
        $this->forums_url    = PA::$url."/forums";
    }

    function initializeModule($request_method, $request_data) {
        if(!isset($this->shared_data['board_statistics'])) {
            if($board = $this->getDefaultBoard($request_data)) {
                $this->shared_data['board'] = $board;
                $this->shared_data['board_statistics'] = $this->buildStatistics($board);
            }
            else {
                return 'skip';
            }
        }
    }

    function handleRequest($request_method, $request_data) {
        $board   = $this->shared_data['board'];
        $posts   = $this->shared_data['board_statistics']['last_posts'];
        $threads = @$this->shared_data['board_statistics']['threads'];
        if(($this->column == 'left') || ($this->column == 'right')) {
            $this->outer_template = 'outer_public_side_module.tpl';
            $this->set_inner_template('latest_posts.tpl.php');
        }
        else {
            $this->outer_template = 'outer_public_center_module.tpl';
            $this->set_inner_template('latest_posts_middle.tpl.php');
            $this->title = '';
        }
        $this->inner_HTML = $this->generate_inner_html(array('page_id' => $this->page_id, 'board' => $board, 'posts' => $posts, 'threads' => $threads, 'forums_url' => $this->forums_url, 'obj' => $this));
    }

    private function getDefaultBoard($request_data) {
        $board            = null;
        $this->parent_id  = PA::$network_info->network_id;
        $this->nid        = PA::$network_info->network_id;
        $this->forums_url = PA::$url."/forums/network_id=$this->nid";
        $this->board_type = PaForumBoard::network_board;
        $boards           = PaForumBoard::listPaForumBoard("owner_id = $this->parent_id AND network_id = $this->nid AND type = '$this->board_type' AND is_active = 1");
        if(count($boards) > 0) {
            $board = $boards[0];
        }
        return $board;
    }

    private function buildStatistics($board) {
        $board_statistics          = $board->getBoardStatistics();
        $statistics                = array();
        $statistics['title']       = $board->get_title();
        $statistics['description'] = $board->get_description();
        $statistics['type']        = $board->get_type()." board";
        $statistics['created_at']  = $board->get_created_at();
        switch($board->get_type()) {
            case PaForumBoard::network_board:
                $net_id = $board->get_owner_id();
                if($net_id == 1) {
                    // mother network - owner_id always is '1' !
                    $owner_id = 1;
                }
                else {
                    $owner_id = Network::get_network_owner((int) $board->get_owner_id());
                }
                break;
            case PaForumBoard::group_board:
                $owner_id = Group::get_owner_id((int) $board->get_owner_id());
                break;
            case PaForumBoard::personal_board:
                $owner_id = $board->get_owner_id();
                break;
        }
        $user = new User();
        $user->load((int) $owner_id);
        $statistics['owner']         = $user;
        $statistics['nb_categories'] = $board_statistics['nb_categories'];
        $nb_forums                   = 0;
        $nb_threads                  = 0;
        $nb_posts                    = 0;
        $threads                     = array();
        $last_posts                  = array();
        foreach($board_statistics['categories'] as $category) {
            if($category->statistics['nb_forums'] > 0) {
                $nb_forums += $category->statistics['nb_forums'];
                foreach($category->statistics['forums'] as $forum) {
                    if(!empty($forum->statistics['threads']) and (count($forum->statistics['threads']) > 0)) {
                        foreach($forum->statistics['threads'] as &$thr) {
                            $thr->forum = $forum;
                        }
                        $threads     = array_merge($threads, $forum->statistics['threads']);
                        $nb_threads += $forum->statistics['nb_threads'];
                        $nb_posts   += $forum->statistics['nb_posts'];
                        if(!empty($forum->statistics['last_post'])) {
                            $last_posts[] = $forum->statistics['last_post'];
                        }
                    }
                }
            }
        }
        if(count($threads) > 1) {
            usort($threads, "cmpThreadCreated");
        }
        $statistics['threads']    = $threads;
        $statistics['nb_forums']  = $nb_forums;
        $statistics['nb_threads'] = $nb_threads;
        $statistics['nb_posts']   = $nb_posts;
        $statistics['last_posts'] = $last_posts;
        $statistics['nb_users']   = PaForumsUsers::countPaForumsUsers("board_id = ".$board->get_id());
        return $statistics;
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