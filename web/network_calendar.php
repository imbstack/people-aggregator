<?php
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.

include_once("web/includes/page.php");

require_once "web/includes/functions/auto_email_notify.php";
require_once "web/includes/functions/user_page_functions.php";


global $query_count_on_page, $login_uid;
$query_count_on_page = 0;

$authorization_required = TRUE;


function setup_module($column, $module, $obj) {
  global $network_info, $login_uid;
  switch ($module) {
    case 'EventCalendarModule':
      $obj->title = 'Network Events';
      $obj->assoc_type = 'network';
      $obj->assoc_id = $network_info->network_id;
      $obj->assoc_title = $network_info->name;
      $is_admin = Network::is_admin($network_info->network_id, $login_uid);
      if ($is_admin) {
        $obj->may_edit = true;
      } else {
        $obj->may_edit = false;
      }
      break;
  }
  $obj->mode = PUB;
}

$page = new PageRenderer("setup_module", PAGE_NETWORK_CALENDAR, __("Network Events"), "container_one_column.tpl", "header.tpl", PRI, NULL, $network_info);

$page->html_body_attributes ='class="no_second_tier network_config"';
$css_path = PA::$theme_url . '/calendar.css';
$page->add_header_css($css_path);
$page->add_header_html(js_includes('calendar.js'));

uihelper_error_msg($msg);
uihelper_get_network_style();
echo $page->render();

?>