<?php
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.

include_once("web/includes/page.php");
require_once "web/includes/network.inc.php";
require_once "ext/StaticPage/StaticPage.php";
require_once "web/includes/classes/Pagination.php";

$msg = array();
$edit = FALSE;

$error = false; // reset any previous errors 

if (!empty($_POST) && !empty($_POST['btn_static_pages'])) { // if page is submitted
	$static_page = new StaticPage();
	filter_all_post($_POST);

  if (!empty($_REQUEST['id'])) {
    $static_page->id = $_REQUEST['id'];
    $msg_id = 12007;
  } else {
    $msg_id = 12008;
  }
  $mandatory_fields = array('caption'=>__('Caption'), 'page_text'=>__('Page Text'));
  foreach ($mandatory_fields as $key => $value) {
     if (empty($_POST[$key])) {
       $error = TRUE;
       $msg[] = sprintf("%s can't be emtpy.", $value);
     }
  }
  if (empty($_REQUEST['do'])) {
		if (!empty($_POST['preferred_caption'])) {
			$caption_taken = StaticPage::get(array('caption'=>$_POST['caption']));
			if (!empty($caption_taken)) {
				$error = TRUE;
				$msg[] = __('Caption has already been taken, please enter a different caption');
			}
		}
  }
  $static_page->caption = $form_data['caption'] = $_POST['caption'];
  $static_page->page_text = $form_data['page_text'] = $_POST['page_text'];
  $static_page->url = $form_data['preferred_caption'] = $_POST['preferred_caption'];
  if (!$error) { 
    try {
      $url = $static_page->save();
      $form_data = array();
      header("location: manage_static_pages.php?msg_id=$msg_id");exit;
    } catch (PAException $e) {
      $msg[] = $e->message;
    }
  }
} else if ((!empty($_GET['do'])) && $_GET['do'] == 'edit' && !empty($_GET['id'])) {
  $edit = TRUE;
  $res = StaticPage::get(array('id' => ((int)$_GET['id'])));
  if (!empty($res)) {
    $form_data['id'] = $res[0]->id;
    $form_data['caption'] = $res[0]->caption;
    $form_data['url'] = $res[0]->url;
    $form_data['page_text'] = $res[0]->page_text;
  }
} else if ((!empty($_GET['action'])) && $_GET['action'] == 'delete' && !empty($_GET['id'])) {
    if (!empty($_GET['id'])) {
      try {
        StaticPage::delete((int)$_GET['id']);
        header("location: manage_static_pages.php?msg_id=12013");exit; 
      } catch (PAException $e) {
        $msg[] = $e->message;
      } 
    }
} 
function setup_module($column, $module, $obj) {
  global $paging, $edit, $form_data;
  switch ($module) {
    case 'ManageStaticPageModule':
      $obj->edit = $edit;
      $obj->form_data = $form_data;
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 20;    
    break;
  }  
}

$page = new PageRenderer("setup_module", PAGE_MANAGE_STATICPAGES, "Manage Static Pages", "container_two_column.tpl", "header.tpl", NULL, PRI , PA::$network_info);

$message = '';
if (!empty($msg)) {
  for ($counter = 0; $counter < count($msg); $counter++) {
    $message .= $msg[$counter]."<br />";
  }
} else if (!empty($_GET['msg_id'])) { // message id was passed on URL
  $message = $_GET['msg_id'];
}
uihelper_error_msg($message);
$page->html_body_attributes = 'class="no_second_tier network_config"';
uihelper_get_network_style();
echo $page->render();

?>