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
/*
 * Project:     PeopleAggregator: a social network developement platform
 * File:        ajax_sortby.php, generate inner html of facewall module
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This is ajax file to generate inner contents
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 */

 
$login_required = FALSE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once PA::$blockmodule_path . "/MembersFacewallModule/MembersFacewallModule.php";

$shared_data = array();
$uid = $app->getRequestParam('uid');
$gid = $app->getRequestParam('gid');
$sort_by = $selected_option = $app->getRequestParam('sort_by');

if($gid) {
  $group = ContentCollection::load_collection((int)$gid);
  $shared_data['group_info'] = $group;
}

$sorting_options[] = array('caption'=> __('Last Login'), 'value'=> 'last_login');
$sorting_options[] = array('caption'=> __('Latest Registered'), 'value'=> 'latest_registered');

$obj = new MembersFacewallModule($selected_option, $gid);
$obj->shared_data = $shared_data;
$obj->sorting_options = $sorting_options;
$obj->selected_option = $selected_option;
$obj->initializeModule('AJAX', $_REQUEST);
$obj->mode = $obj->sort_by = SORT_BY;

echo $obj->render();
?>