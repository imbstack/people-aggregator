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
 * File:        ajax_sortgroups.php, generate inner html of group module
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This is ajax file to generate inner contents
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 * 
 */
$login_required = FALSE;
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require PA::$blockmodule_path."/NewestNetworkModule/NewestNetworkModule.php";
$uid = @$_GET['uid'];
$selected_option = @$_GET['sort_by'];
$sorting_options[] = array(
    'caption' => __('Recently Created'),
    'value' => 'created',
);
$sorting_options[] = array(
    'caption' => __('Recently Modified'),
    'value' => 'changed',
);
$sorting_options[] = array(
    'caption' => __('Largest Group'),
    'value' => 'members',
);
$links = '';
if($selected_option == 'members') {
    $links = Network::get_largest_networks(false, 5, 1, 'member_count');
}
elseif($selected_option == 'created') {
    $links = Network::get_largest_networks(false, 5, 1, 'created');
}
elseif($selected_option == 'changed') {
    $links = Network::get_largest_networks(false, 5, 1, 'changed');
}
$obj                  = new NewestNetworkModule;
$obj->mode            = $obj->sort_by = SORT_BY;
$obj->links           = $links;
$obj->sorting_options = $sorting_options;
$obj->selected_option = $selected_option;
echo $obj->render();
?>