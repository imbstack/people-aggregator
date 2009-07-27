<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        ManageFooterLinksModule.php, BlockModule file to generate configurable footer links
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class ManageFooterLinksModule which generates html of 
 *              footer link's add/edit form - it is a center module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once "ext/FooterLink/FooterLink.php";

 
class ManageFooterLinksModule extends Module {

  
  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

  function __construct() {
    parent::__construct();
    $this->block_type = 'ManageFooterLinksModule';
    $this->html_block_id = 'ManageFooterLinksModule';
    $this->title = __('Manage Footer Links');
  }

  // This function renders ManageFooterLinksModule
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  } 

  // This function returns all the footer links within the network
  function get_links() {
    $this->Paging["count"] = FooterLink::get(NULL, true);
    $footer_links = FooterLink::get(NULL, false, $this->Paging['page'], $this->Paging['show']);
    return $footer_links;
  }
  // This function generates inner html for the ManageFooterLinksModule
  function generate_inner_html () {    
    $links = $this->get_links();
    // set links for pagination
    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('links', $links);
    $inner_html_gen->set('edit', $this->edit);
    $inner_html_gen->set('form_data', $this->form_data);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
//     $inner_html_gen->set('config_navigation_url',
//                        network_config_navigation('manage_ad_center'));
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}