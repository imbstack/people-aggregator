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
 * File:        ShowAnnouncementModule.php, BlockModule file to generate Announcement
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class ShowAnnouncementModule which generates html of 
 *              Announcement list - it is side module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once "ext/Announcement/Announcement.php";

/**
 * This class generates inner html of Announcement content type
 * @package BlockModules
 * @subpackage ShowAnnouncementModule
 */
class ShowAnnouncementModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

   function __construct() {
    parent::__construct();
    $this->html_block_id = "ShowAnnouncementModule";
    $this->main_block_id = "mod_announcement";
    $this->title = __('Announcements');
  }

  function render() {
    $condition['live'] = ANNOUNCE_LIVE; //live announcements having status = '1'
    $param = array();
    $this->announcement = Announcement::load_announcements_array($param, $condition);
    $this->inner_HTML = $this->generate_inner_html($this->announcement); 
    $announcement = parent::render();
    return $announcement;
  }

  function generate_inner_html ($announcement) {
     
    // global var $_base_url has been removed - please, use PA::$url static variable

    $inner_html = '';    
    if ($announcement) {
      //generating tpl for each announcement
      for ($i = 0; $i < count($announcement); $i++) {
          $inner_html .= uihelper_generate_center_content($announcement[$i]['content_id']);
      }
    }
    return $inner_html;
  }
}
