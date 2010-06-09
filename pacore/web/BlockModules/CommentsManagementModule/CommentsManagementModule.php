<?php
/** !
* CommentsManagamentModule.php is a part of PeopleAggregator.
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @author Martin Spernau, [Owen Bell: 2 June 2010>
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* Controls all aspects of commenting such as comment title, the comment itself,
* the author, etc.  This module also tracks abuse within the comments.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/

require_once "web/includes/classes/Pagination.php";

class CommentsManagementModule extends Module {

    public $module_type = 'system|network';
    public $module_placement = 'middle';
    public $outer_template = 'outer_public_group_center_module.tpl';

    function __construct() {

        parent::__construct();
        $this->main_block_id = "CommentsManagementModule";
        $this->title = __('Manage Comments');

    }

    /** !!
    * Takes all comments on a page and feeds them through manage_links
    * @return array $links the output of managelinks() 
    *		all commments and their various attributes
    */
    private function get_links() {
        $this->Paging["count"] = count(Comment::get_all_comments());
        $contents = Comment::get_all_comments(NULL, $this->Paging["show"], $this->Paging["page"], 'created', 'DESC');
        $contents_link = $this->manage_links($contents);
        $this->links = $contents_link;
        return $this->links;

    }

    /** !!
    * Calls generate_inner_html and returns the output to be displayed
    * @return string $content the output of generate_inner_html()
    */
    function render() {
        $this->links = $this->get_links();
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;

    }

    /** !!
    * Create the html including html for the links and to the first and last comments etc
    * @return string $inner_html the completed html for the page
    */
    function generate_inner_html() {

        switch($this->mode) {
            default:
                $inner_template = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_public.tpl';
        }
        $Pagination = new Pagination;
        $Pagination->setPaging($this->Paging);
        $this->page_first = $Pagination->getFirstPage();
        $this->page_last = $Pagination->getLastPage();
        $this->page_links = $Pagination->getPageLinks();
        $inner_html_gen = &new Template($inner_template);
        $inner_html_gen->set('links', $this->links);
        $inner_html_gen->set('page_first', $this->page_first);
        $inner_html_gen->set('page_last', $this->page_last);
        $inner_html_gen->set('page_links', $this->page_links);
        $inner_html_gen->set('config_navigation_url', network_config_navigation('manage_comments'));
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }

    /** !!
    * Manages the contents of comments such as user id, title, links, etc.
    * @param array $contents_list contains information about each comment
    *		such as title and contents
    * @return array $link_array contains all the information from $contents_list
    *		in addition to other data such as comment abuse
    */
    function manage_links($contents_list) {
        // global var $_base_url has been removed - please, use PA::$url static variable
        $cnt = count($contents_list);
        $link_array = array();
        if($cnt > 0) {
            // Loading all users
            for($i = 0; $i < $cnt; $i++) {
                $user_listing[$i] = $contents_list[$i]['user_id'];
            }
            $usr = new User();
            $user_info = $usr->load_users($user_listing, 'user_id');
            $cnt_no = count($user_info);
            for($j = 0; $j < $cnt_no; $j++) {
                $user_array[$user_info[$j]['user_id']] = $user_info[$j]['login_name'];
            }
            for($i = 0; $i < $cnt; $i++) {
                $link_array[$i]['time'] = $contents_list[$i]['created'];
                $link_array[$i]['comment'] = $contents_list[$i]['comment'];
                $link_array[$i]['comment_title'] = $contents_list[$i]['comment_title'];
                $link_array[$i]['type'] = 'comment';
                $link_array[$i]['author_id'] = $contents_list[$i]['user_id'];
                if($link_array[$i]['author_id'] == ANONYMOUS_USER_ID) {
                    $link_array[$i]['author_name'] = __("Anonymous");
                }
                else {
                    $login = User::get_login_name_from_id($link_array[$i]['author_id']);

                    /*          
                              $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$link_array[$i]['author_id'];
                              $url_perms = array('current_url' => $current_url,
                                                        'login' => $login                  
                                                      );
                              $url = get_url(FILE_USER_BLOG, $url_perms);
                    */
                    $url = PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$login;
                    $link_array[$i]['author_name'] = '<a href="'.$url.'">'.$user_array[$contents_list[$i]['user_id']].'</a>';
                }
                $link_array[$i]['comment_id'] = $contents_list[$i]['comment_id'];
                $link_array[$i]['delete_url'] = PA::$url.'/deletecomment.php?comment&comment_id='.$link_array[$i]['comment_id'];
                $link_array[$i]['hyper_link'] = PA::$url.PA_ROUTE_CONTENT.'/cid='.$contents_list[$i]['content_id'];
                // Here we calcuting the number of total abuse on comment
                $link_array[$i]['abuses'] = total_abuse($contents_list[$i]['comment_id'], TYPE_COMMENT);
            }
        }
        return $link_array;
    }
}
?>
