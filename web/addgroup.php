<?php
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");

require_once 'api/Category/Category.php';
require_once 'web/includes/classes/file_uploader.php';
require_once 'api/Message/Message.php';
require_once 'web/includes/functions/auto_email_notify.php';
require_once 'web/includes/functions/mailing.php';
default_exception();
$parameter = js_includes("all");

$header = 'header.tpl';//default network header while creating groups. While group editing header_group.tpl will be used.
$edit = FALSE;
$title = __("Create Group");
if (!empty($_REQUEST['gid'])) {
  $title = __("Edit Group");
  $user_type = Group::get_user_type($_SESSION['user']['id'], $_REQUEST['gid']);

  $header = 'header_group.tpl';
  $edit = TRUE;

  $groups = new Group();
  $groups->load($_REQUEST['gid']);
  $group_type = $groups->group_type;
}

function setup_module($column, $module, $obj) {
    global $login_uid, $paging, $page_uid, $super_groups_available, $user_type, $group_type;

    switch ($module) {
      case 'AddGroupModule':
        if (!empty($_REQUEST['gid'])) {
          $obj->id = $_REQUEST['gid'];
        }
      break;
    }
}

$setting_data = ModuleSetting::load_setting(PAGE_ADDGROUP, PA::$login_uid);

$page = new PageRenderer("setup_module", PAGE_ADDGROUP, "$title - PA::$network_info->name", 'container_three_column.tpl', $header, PRI, HOMEPAGE, PA::$network_info, NULL, $setting_data);

$page->add_header_js('addgroup.js');

if(!empty($_REQUEST['msg'])) {
  $message = null;
} else if(!empty($_REQUEST['error_msg'])) {
  $message = null;
} else {
  $message = null;
}

uihelper_error_msg($message);


if($edit) {
  uihelper_get_group_style($_REQUEST['gid']);
}
else {
  uihelper_get_network_style();
}

echo $page->render();
?>
