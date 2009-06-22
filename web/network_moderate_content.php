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

  $do     = (isset($_GET['do'])) ? $_GET['do'] : '';
  $action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
  
  if (!$error && ($do == 'approve')) {
    Network::approve_content((int)$_GET['cid']);
    header("Location: ". PA::$url ."/network_moderate_content.php?msg_id=1002");exit;
  } else if (!$error && ($do == 'deny')) {
    Network::disapprove_content((int)$_GET['cid']);
    header("Location: ". PA::$url ."/network_moderate_content.php?msg_id=1003");exit;
  }
  if (!empty($_POST['submit'])) {
    if (empty($_POST['cid'])) {
      header("Location: ". PA::$url ."/network_moderate_content.php?msg_id=1007");exit;
    }
    switch( $action ) {
      case 'deny':
        try {
          $content_array = $_POST['cid'];      
          $total_content = count($content_array);
          for ($counter = 0; $counter < $total_content; $counter++) {            
            Network::disapprove_content((int)$content_array[$counter]);
          }
          header("Location: ". PA::$url ."/network_moderate_content.php?msg_id=1003");exit;
        }
        catch ( PAException $e ) {
          $message = $e->message;
        }
      break;        
      case 'approve':
        try {
          $content_array = $_POST['cid'];      
          $total_content = count($content_array);
          for ($counter = 0; $counter < $total_content; $counter++) {            
            Network::approve_content((int)$content_array[$counter]);
          }
          header("Location: ". PA::$url ."/network_moderate_content.php?msg_id=1002");exit;
        }
        catch ( PAException $e ) {
          $message = $e->message;
        }
      break;
      default:
        header("Location: ". PA::$url ."/network_moderate_content.php?msg_id=1006");exit;  
    }
  }
  
  function setup_module($column, $module, $obj) {
    global $paging;
    switch($module){    
      case 'NetworkModerateContentModule':
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 50;
      break;
    } //end switch
  } // end function
  $page = new PageRenderer("setup_module", PAGE_NETWORK_MODERATE_CONTENTS, "Moderate Network Content", 'container_two_column.tpl','header.tpl',PRI, HOMEPAGE,$network_info);
  if (!empty($_GET['msg_id']) && empty($msg)) {
    $msg = MessagesHandler::get_message($_GET['msg_id']);
  }  

  if(isset($msg)) uihelper_error_msg($msg);

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