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
require_once "api/Group/Group.php";
require PA::$blockmodule_path."/NewestGroupsModule/NewestGroupsModule.php";
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
if($selected_option == 'members') {
    $links = Group::get_largest_groups(5);
}
elseif($selected_option == 'created') {
    $obj_group = new Group();
    $links = $obj_group->get_all('', 5, FALSE, 5, 1);
}
elseif($selected_option == 'changed') {
    $obj_group = new Group();
    $links = $obj_group->get_all('', 5, FALSE, 5, 1, 'changed');
}
$obj                  = new NewestGroupsModule;
$obj->mode            = $obj->sort_by = SORT_BY;
$obj->links           = $links;
$obj->sorting_options = $sorting_options;
$obj->selected_option = $selected_option;
echo $obj->render();
?>