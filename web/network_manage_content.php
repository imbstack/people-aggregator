<?php
  //anonymous user can not view this page;
  $login_required = TRUE;
  //including necessary files
  $use_theme = 'Beta';
  include_once("web/includes/page.php");
  include_once "api/ModuleSetting/ModuleSetting.php";
  include_once "api/Theme/Template.php";
  require_once "web/includes/network.inc.php";
  $error = FALSE;
  $authorization_required = TRUE;

  
  function setup_module($column, $module, $obj) {
    global $paging;
    switch($module){
    
      case 'NetworkResultContentModule':
      $obj->keyword = '';
      $obj->month = '';
      if(@$_GET['search']) {
        if (!empty($_GET['keyword'])) {
          $obj->keyword = trim($_GET['keyword']);
        }
        if (!empty($_GET['select_month'])) {
          $obj->month = trim($_GET['select_month']);
        }
      } 
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 50;
      break;
    } //end switch
  } // end function
  $page = new PageRenderer("setup_module", PAGE_NETWORK_MANAGE_CONTENTS, "Manage Network Content", 'container_two_column.tpl','header.tpl',PRI, HOMEPAGE,PA::$network_info);
  if (!empty($_GET['msg_id']) && empty($msg)) {
    $msg = MessagesHandler::get_message($_GET['msg_id']);
    uihelper_error_msg($msg);
  }  
  $page->html_body_attributes ='class="no_second_tier network_config"';
  $css_array = get_network_css();
  if (is_array($css_array)) {
    foreach ($css_array as $key => $value) {
      $page->add_header_css($value);
    }
  }
  
  $css_data = inline_css_style();
  if (!empty($css_data['newcss']['value'])) {
    $css_data = '<style type="text/css">'.$css_data['newcss']['value'].'</style>';
    $page->add_header_html($css_data);
  }
  echo $page->render();
?>