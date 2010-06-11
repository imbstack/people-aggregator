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
 * File:        manage_domain_name.php, web file to set domain name  for PeopleAggregator
 * Author:      tekritisoftware
 * Version:     1.2
 * Description: This file displays, content of domain_name.txt
               
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
  
/** This file is used to change the skin of the network.
* Anonymous user can not access this page;
*/
error_reporting(E_ALL);

$login_required = TRUE;
//including necessary files
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "web/includes/network.inc.php";

// For authentication
$error = FALSE;
if(!empty($_POST)) {
  
//  if(!$handle = fopen("web/includes/domain_names.txt", 'w+')) {  changed by Z.Hron: "/web/includes" is not writable directory
  if(!$handle = fopen(PA::$project_dir . "/config/domain_names.txt", 'w+')) {
    if(!$handle = fopen(PA::$core_dir . "/config/domain_names.txt", 'w+')) {
      $msg = 5040;
      $error = TRUE;
    }  
  }
  
  if (fwrite($handle, $_POST['file_text']) === FALSE) {
    $msg = 5041;
    $error = TRUE;
  }

  fclose($handle);
  
  if(!$error) {
    $msg = 5042;
  }
}


  function setup_module($column, $module, $obj) {
   switch ($module) {
      case 'ManageFileContents':
      break;
    }
    
  }

  
$page = new PageRenderer("setup_module", PAGE_MANAGE_DOMAIN, "Manage Allowed Domains", 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);

if(isset($msg)) uihelper_error_msg($msg);

$page->html_body_attributes ='class="no_second_tier"';

if(isset($parameter)) $page->add_header_html($parameter);
uihelper_get_network_style();
echo $page->render();
  
?>