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
$login_required = FALSE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");

/*including Js files */
$parameter = js_includes('common.js');


function setup_module($column, $module, $obj) {
    global $login_uid, $paging, $page_uid, $page_user,$total_groups;

    switch ($module) {
      case 'SearchByTag':
          $obj->Paging["page"] = $paging["page"];
          $obj->Paging["show"] = $paging["show"];
          if (@$_GET['keyword']) {
            $obj->name_string=$_GET['name_string'];
            $obj->keyword = $_GET['keyword'];
            $obj->sort_by = @$_GET['sort_by'];
          }
           else{
              $obj->sort_by = @$_GET['sort_by'];
           }
           if ( @$_GET['uid'] ) {
              $obj->uid =  $_GET['uid']; 
              $obj->sort_by = $_GET['sort_by'];
           }
          break; 
     }
      
      
}

$page = new PageRenderer("setup_module", PAGE_TAG_SEARCH, 'Search By Tag',  'container_three_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);

uihelper_get_network_style();
echo $page->render();

?>