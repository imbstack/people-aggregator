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
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        NetworkModerateContentModule.php, BlockModule file to generate NetworkModerateContentModule
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class NetworkModerateContentModule which generates html to moderate
 * each and every content, being posted to the application 
 * form - it is a center module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once "web/includes/classes/Pagination.php";

class NetworkModerateContentModule extends Module {

    public $module_type = 'system|network';

    public $module_placement = 'middle';

    public $outer_template = 'outer_public_group_center_module.tpl';

    function __construct() {
        parent::__construct();
        $this->main_block_id = "mod_network_content_result";
        $this->title = __('Moderate Content');
    }
    // This function will return contents waiting for being moderated.
    private function get_links() {
        $network = new Network();
        $condition = array(
            'C.is_active' => MODERATION_WAITING,
        );
        $params['cnt']         = TRUE;
        $this->Paging["count"] = Content::load_all_content_for_moderation($params, $condition);
        $params['cnt']         = FALSE;
        $params['show']        = $this->Paging["show"];
        $params['page']        = $this->Paging["page"];
        $contents              = Content::load_all_content_for_moderation($params, $condition);
        $contents_link         = $this->manage_content($contents);
        $this->links           = $contents_link;
        return $this->links;
    }

    /**
  *  Function : render()
  *  Purpose  : produce html code from tpl file
  *  @return   type string 
  *            returns rendered html code 
  */
    function render() {
        $this->links      = $this->get_links();
        $this->inner_HTML = $this->generate_inner_html();
        $content          = parent::render();
        return $content;
    }

    /** this function set variable for tpls
    * and fetches the respective tpl
    */
    function generate_inner_html() {
        switch($this->mode) {
            default:
                $inner_template = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_private.tpl';
        }
        $Pagination = new Pagination;
        $Pagination->setPaging($this->Paging);
        $this->page_first = $Pagination->getFirstPage();
        $this->page_last  = $Pagination->getLastPage();
        $this->page_links = $Pagination->getPageLinks();
        $inner_html_gen   = &new Template($inner_template);
        $inner_html_gen->set('links', $this->links);
        $inner_html_gen->set('page_first', $this->page_first);
        $inner_html_gen->set('page_last', $this->page_last);
        $inner_html_gen->set('page_links', $this->page_links);
        $inner_html_gen->set('config_navigation_url', network_config_navigation('moderate_content'));
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
    // For manage Content listing . adding some links such as edit delete and hyperlink
    function manage_content($contents_list) {
        global $login_uid;
        $i = 0;
        if(empty($contents_list)) {
            $contents_list = array();
        }
        $new_content = array();
        foreach($contents_list as $contt) {
            $new_content[$i]['content_id']      = $contt['content_id'];
            $new_content[$i]['title']           = $contt['title'];
            $new_content[$i]['body']            = $contt['body'];
            $new_content[$i]['author_id']       = $contt['author_id'];
            $new_content[$i]['type']            = $contt['type'];
            $new_content[$i]['changed']         = $contt['changed'];
            $new_content[$i]['created']         = $contt['created'];
            $new_content[$i]['type_name']       = $contt['type_name'];
            $new_content[$i]['author_name']     = $contt['author_name'];
            $new_content[$i]['content_type_id'] = $contt['content_type_id'];
            if(!empty($contt['parent_info'])) {
                $type = ($contt['parent_info']['type'] == ALBUM_COLLECTION_TYPE) ? 'Album' : 'Group';
                $new_content[$i]['parent_name'] = $contt['parent_info']['title'].'('.$type.')';
            }
            $link_for_apv_dny  = PA::$url."/network_moderate_content.php?cid=".$new_content[$i]['content_id'];
            $link_for_approval = $link_for_apv_dny.'&amp;do=approve';
            $link_for_denial   = $link_for_apv_dny.'&do=deny';
            // Route media and normal content through to the correct display/editing pages
            if(!in_array($contt['content_type_id'], array(IMAGE, AUDIO, VIDEO))) {
                $image_hyperlink = PA::$url.PA_ROUTE_CONTENT."/cid=".$new_content[$i]['content_id'];
            }
            else {
                PA::$url."/edit_media.php?uid=".$login_uid."&amp;cid=".$new_content[$i]['content_id']."&amp;type=image";
                $image_hyperlink = PA::$url."/media_full_view.php?gid&cid=".$new_content[$i]['content_id'];
            }
            $new_content[$i]['approve_link'] = $link_for_approval;
            $new_content[$i]['deny_link']    = $link_for_denial;
            $new_content[$i]['hyper_link']   = $image_hyperlink;
            $new_content[$i]['user_url']     = PA::$url.PA_ROUTE_USER_PUBLIC."/".$new_content[$i]['author_id'];
            $i++;
        }
        return $new_content;
    }
}
?>

