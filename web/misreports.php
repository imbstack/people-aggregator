<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        misreports .php, web file to get display of mis report for PeopleAggregator
 * Author:      tekritisoftware
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = TRUE;
//including necessary files
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "web/includes/network.inc.php";


$msg = @$_REQUEST['msg'];

function setup_module($column, $module, $obj) {
  global $msg, $error, $paging;
  if($msg) return 'skip';
  switch ($module) {
    case 'MISReportModule':
    $obj->market_report = FALSE;
    if (@$_GET['mis_type'] == 'mkt_rpt') {
      $obj->market_report = TRUE;
      $obj->email_sorting = (!empty($_GET['sort_by'])) ? $_GET['sort_by'] : NULL;
    }
    $obj->Paging["page"] = $paging["page"];
    $obj->Paging["show"] = 10;
    break;
  }
}

$page = new PageRenderer("setup_module", PAGE_MIS_REPORT, __("MIS Reports"), 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);
$page->html_body_attributes = 'class="no_second_tier network_config"';

uihelper_error_msg($msg);;
uihelper_get_network_style();

echo $page->render();
?>