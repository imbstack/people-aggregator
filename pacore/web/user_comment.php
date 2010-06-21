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
 * File:        user_comment.php, web file to write comments on users
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file displays the main page of the site. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = TRUE;
//for session protection pages
$use_theme = 'Beta';
include_once("web/includes/page.php");
$parameter = '';
$parameter .= js_includes('common.js');
global $query_count_on_page, $page_uid;
$query_count_on_page = 0;

/**
 *  Function : setup_module()
 *  Purpose  : call back function to set up variables 
 *             used in PageRenderer class
 *             To see how it is used see api/PageRenderer/PageRenderer.php 
 *  @param    $column - string - contains left, middle, right
 *            position of the block module 
 *  @param    $moduleName - string - contains name of the block module
 *  @param    $obj - object - object reference of the block module
 *  @return   type string - returns skip means skip the block module
 *            returns rendered html code of block module
 */
function setup_module($column, $moduleName, $obj) {
}
$page = new PageRenderer("setup_module", PAGE_USER_COMMENT, 'Write comment', "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info);
if(isset($error_message)) {
    uihelper_error_msg($error_message);
}
$page->add_header_html($parameter);
$page->html_body_attributes = 'class="no_second_tier" id="pg_homepage"';
uihelper_set_user_heading($page);
echo $page->render();
?>