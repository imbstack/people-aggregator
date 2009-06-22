<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        PopularTagsModule.php, BlockModule file to generate Popular tag's list
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class PopularTagsModule which generates html of 
 *              Popular tags list - it is side module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once "api/Tag/Tag.php";

/**
 * This class generates inner html of Popular tags across the site
 * @package BlockModules
 * @subpackage PopularTagsModule
 */ 

class PopularTagsModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public $links;

  function __construct() {
    $this->title = __("Most Popular Tags");
    $this->html_block_id = "tagcloud";
  }
  
  function render() {
    $tags = Tag::load_tag_soup(10);
    if (!empty($tags)) {
      $sorted_tags = asort($tags, @$tags['occurence']);
      $this->links = $tags;
    }
         
    $this->inner_HTML = $this->generate_inner_html ($this->links);
    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html ($links) {
    global $current_theme_path, $current_blockmodule_path;
    
    $inner_template = NULL;
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    
    $obj_inner_template = & new Template($inner_template);
    $obj_inner_template->set('links', $links);
    $obj_inner_template->set('current_theme_path', $current_theme_path);
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }
}
?>