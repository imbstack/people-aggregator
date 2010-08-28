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
 * File:        ModuleSelectorModule.php, BlockModule file to save page_default_settings(in 
 *              terms of module) as selected by user
 *              
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class ModuleSelectorModule which generates html of 
 *              Module list of given page_id - it is center module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once "api/ModuleSetting/ModuleSetting.php";

class ModuleSelectorModule extends Module {

  /**
   * $title is used to hold the title that appears 
   * on header of the module
   * @var string
   */
  public $title;
  
  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  /**
  * The default constructor for ModuleSelectorModule class.
  * It initializes the default values of vars
  */
  
  function __construct() {
    parent::__construct();
  }
  
  /**
  *  Function : render()
  *  Purpose  : produce html code from tpl file
  *  @return   type string 
  *            returns rendered html code 
  */
  
   function render() {
    $this->inner_HTML = $this->generate_inner_html();
    $links = parent::render();
    return $links;
  }

  function generate_inner_html () {
    switch ($this->mode) {
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_html.tpl';
    }
    $inner_html_generate = new Template($tmp_file);
    $inner_html_generate->set('module_settings', $this->module_settings);
    $inner_html_generate->set('page_id', $this->page_id);
    $inner_html_generate->set('config_navigation_url',
                         network_config_navigation( 'module_selector' ) );
    $inner_html = $inner_html_generate->fetch();
    
    return $inner_html;
  }
}