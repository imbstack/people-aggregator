<?php
  $login_required = TRUE;
  $use_theme = 'Beta';
  include_once("web/includes/page.php");
  require_once "web/includes/network.inc.php";
  
  $error_msg = @$_REQUEST['msg'];

  function setup_module($column, $module, $obj) {
    global $paging;
    
    switch ($module) {
      case 'CommentsManagementModule':
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 50;
      break;
    } 
  } // end function
  
  $page = new PageRenderer("setup_module", PAGE_MANAGE_COMMENTS, "Manage Comments", 'container_two_column.tpl','header.tpl',PRI, HOMEPAGE,PA::$network_info);
  
  uihelper_error_msg($error_msg);
  
  $page->html_body_attributes ='class="no_second_tier network_config"';
  
  uihelper_get_network_style();
  
  echo $page->render();
?>