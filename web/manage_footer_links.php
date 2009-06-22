<?php
global $network_info;
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.

include_once("web/includes/page.php");
require_once "web/includes/network.inc.php";
require_once "ext/FooterLink/FooterLink.php";
require_once "web/includes/classes/Pagination.php";
$msg = array();
$edit = FALSE;
$error = FALSE;

$footer_link = new FooterLink();
filter_all_post($_POST);
if (!$error && !empty($_POST) && @$_POST['btn_footer_link']) { // if page is submitted
  if (!empty($_POST['id'])) {
    $footer_link->id = $_POST['id'];
    $msg_id = 11007;
  } else {
    $msg_id = 11008;
  }
  $mandatory_fields = array('caption'=>'Caption', 'url'=>'URL');
  foreach ($mandatory_fields as $key => $value) {
     if (empty($_POST[$key])) {
       $error = TRUE;
       $msg[] = $value . ' can\'t be empty.';
     }
  }
  if (!empty($_POST['url'])) { // if url is given then validate
    if(!Validation::isValidURL($_POST['url']) ) {
      $error = TRUE;
      $msg[] = MessagesHandler::get_message(19009);
    } 
  }
  $is_external = !empty($_POST['is_external']) ? 1 : 0;
  $extra_footer_data = array ('is_external' => $is_external);
  $footer_link->caption = $form_data['caption'] = $_POST['caption'];
  $footer_link->url = $form_data['url'] = $_POST['url'];
  $footer_link->extra = $form_data['extra'] = serialize($extra_footer_data);
  $footer_link->is_active = ACTIVE;
  if (!$error) { 
    try {
      $footer_link->save();
      $form_data = array();
      header("location: manage_footer_links.php?msg_id=$msg_id");exit;      
    } catch (PAException $e) {
      $msg[] = $e->message;
    }
  }
} else if ((!empty($_GET['do'])) && $_GET['do'] == 'edit' && !empty($_GET['id'])) {
  $edit = TRUE;
  $res = FooterLink::get(array('id' => ((int)$_GET['id'])));
  if (!empty($res)) {
    $form_data['id'] = $res[0]->id;
    $form_data['caption'] = $res[0]->caption;
    $form_data['url'] = $res[0]->url;
    $form_data['extra'] = $res[0]->extra;
  }
} else if ((!empty($_GET['action'])) && $_GET['action'] == 'delete' && !empty($_GET['id'])) {
    if (!empty($_GET['id'])) {
      try {
        FooterLink::delete((int)$_GET['id']);
        header("location: manage_footer_links.php?msg_id=11013");exit; 
      } catch (PAException $e) {
        $msg[] = $e->message;
      } 
    }
} else if (!empty($_GET['do']) && !empty($_GET['id'])) {
  switch ($_GET['do']) {    
    case 'disable':
      $field_value = DELETED;
      $msg_id = 11010;
    break;
    case 'enable':
      $field_value = ACTIVE;
       $msg_id = 11011;
    break;
  }
  $update_fields = array('is_active' => $field_value);
  $condition = array('id' => $_GET['id']);
  try {
    FooterLink::update($update_fields, $condition);
    header("location: manage_footer_links.php?msg_id=$msg_id");exit;    
  } catch (PAException $e) {
    $msg[] = $e->message;
  } 
}
function setup_module($column, $module, $obj) {
  global $paging, $edit, $form_data;
  switch ($module) {
    case 'ManageFooterLinksModule':
      $obj->edit = $edit;
      $obj->form_data = $form_data;
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 20;    
    break;
  }  
}

$page = new PageRenderer("setup_module", PAGE_MANAGE_FOOTERLINKS, "Manage Footer Links", "container_two_column.tpl", "header.tpl", NULL, PRI ,$network_info);

$message = '';
if (!empty($msg)) {
  for ($counter = 0; $counter < count($msg); $counter++) {
    $message .= $msg[$counter]."<br>";
  }
}
// display message
if (!empty($_GET['msg_id'])) {
  $message = $_GET['msg_id'];
}
uihelper_error_msg($message);
$page->html_body_attributes ='class="no_second_tier network_config"';
uihelper_get_network_style();
echo $page->render();

?>