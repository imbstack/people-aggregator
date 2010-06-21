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
 * File:        create_network.php, web file to create network
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file displays form to create network uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = TRUE;
//including necessary files
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Validation/Validation.php";
require_once "web/includes/network.inc.php";
global $global_form_data, $global_form_error;
$permission_denied_msg = NULL;

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
function setup_module($column, $module, $obj) {
    global $form_data, $error, $error_msg, $global_form_data;
    switch($module) {
        case 'NetworkDefaultControlModule':
            $obj->tpl_to_load  = "stats";
            $obj->title        = 'Create your network';
            $obj->control_type = "basic";
            $obj->form_data    = $global_form_data;
            $obj->error        = $error;
            $obj->error_msg    = $error_msg;
            //add variables to BlockModule
            break;
    }
}
if(!PA::$network_capable) {
    die(__("Networks are disabled."));
}
if(!PA::$config->enable_network_spawning) {
    die(__("Network spawning disabled."));
}
$page                       = new PageRenderer("setup_module", PAGE_CREATE_NETWORK, sprintf(__("Create Network - %s"), PA::$network_info->name), 'container_three_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);
$page->html_body_attributes = 'class="no_second_tier"';
$permission_denied_msg      = (empty($global_form_error)) ? $permission_denied_msg : $global_form_error;
if(@$permission_denied_msg) {
    uihelper_error_msg($permission_denied_msg);
}
uihelper_get_network_style();
echo $page->render();
?>
