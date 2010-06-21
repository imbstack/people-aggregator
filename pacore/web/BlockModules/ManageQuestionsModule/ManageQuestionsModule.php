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
 * File:        ManageQuestionsModule.php, BlockModule file to generate ManageQuestionsModule
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class ManageQuestionsModule which generates html of 
 *              question, entry form - it is a center module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once "web/includes/classes/Pagination.php";
require_once "api/Question/Question.php";

class ManageQuestionsModule extends Module {

    public $module_type = 'system|network';

    public $module_placement = 'middle';

    public $outer_template = 'outer_public_center_module.tpl';

    function __construct() {
        parent::__construct();
        $this->title = 'Manage Questions';
        $this->html_block_id = 'ManageQuestionsModule';
    }

    function render() {
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    function get_links() {
        $question = new Question();
        $param = array(
            'cnt' => TRUE,
        );
        $this->Paging["count"] = $question->load_many($param);
        $params = array(
            'cnt'       => FALSE,
            'show'      => $this->Paging["show"],
            'page'      => $this->Paging["page"],
            'sort_by'   => 'changed',
            'direction' => 'DESC',
        );
        $data = $question->load_many($params);
        $links = objtoarray($data);
        $params = array(
            'cnt'       => FALSE,
            'show'      => 1,
            'page'      => 1,
            'is_active' => 1,
            'sort_by'   => 'changed',
            'direction' => 'DESC',
        );
        $data = $question->load_many($params);
        $this->selected = @$data[0]->content_id;
        return $links;
    }

    function generate_inner_html() {
        $links = $this->get_links();
        // set links for pagination
        $Pagination = new Pagination;
        $Pagination->setPaging($this->Paging);
        $this->page_first = $Pagination->getFirstPage();
        $this->page_last  = $Pagination->getLastPage();
        $this->page_links = $Pagination->getPageLinks();
        $tmp_file         = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_private.tpl';
        $inner_html_gen   = &new Template($tmp_file);
        $inner_html_gen->set('links', $links);
        $inner_html_gen->set('selected', $this->selected);
        $inner_html_gen->set('form_data', @$this->form_data);
        $inner_html_gen->set('page_first', $this->page_first);
        $inner_html_gen->set('page_last', $this->page_last);
        $inner_html_gen->set('page_links', $this->page_links);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>