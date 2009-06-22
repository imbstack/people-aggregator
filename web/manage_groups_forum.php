<?php
  //anonymous user can not view this page;
  $login_required = TRUE;
  //including necessary files
  $use_theme = 'Beta';
  include_once("web/includes/page.php");
  require_once "web/includes/network.inc.php";
  $error = FALSE;

  
  function setup_module($column, $module, $obj) {
    global $paging;
   
    switch($module) {
      case 'NetworkForumManagementModule':
        $obj->Paging["page"] = $paging["page"];
        $obj->Paging["show"] = 50;
        break;
      }
  } // end function
  $page = new PageRenderer("setup_module", PAGE_NETWORK_FORUM_MANAGEMENT, "Manage Groups Forums", 'container_two_column.tpl','header.tpl',PRI, HOMEPAGE,$network_info);

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