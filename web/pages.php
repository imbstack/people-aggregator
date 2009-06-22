<?php
$login_required = FALSE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.

include_once("web/includes/page.php");
require_once "ext/StaticPage/StaticPage.php";

$msg = array();
$message = null;
$page = !empty($_GET['caption']) ? $_GET['caption'] : NULL;
$static_page = StaticPage::get(array('url'=>$page));
if (empty($static_page)) {
  $msg[] = MessagesHandler::get_message(12009);
}


function setup_module($column, $module, $obj) {
  global $static_page;
  switch ($module) {
    case 'StaticPageDisplayModule':
      if (!empty($msg) || empty($static_page)) { return 'skip';}
      $obj->text = $static_page[0]->page_text;
      $obj->title = $static_page[0]->caption;
    break;
  }
}

$page = new PageRenderer("setup_module", PAGE_STATIC_PAGE_DISPLAY, $static_page[0]->caption." - ".$network_info->name, "container_two_column.tpl", "header.tpl", NULL, PRI, $network_info);
if (!empty($msg)) {
  for ($counter = 0; $counter < count($msg); $counter++) {
    $message .= $msg[$counter]."<br>";
  }
}
uihelper_error_msg($message);
// $page->html_body_attributes .= 'class="no_second_tier network_config"';
uihelper_get_network_style($page);
echo $page->render();
?>
