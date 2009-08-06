<?php
  //anonymous user can not view this page;
  $login_required = TRUE;
  //including necessary files
  $use_theme = 'Beta';
  include_once("web/includes/page.php");
  require_once "api/Activities/Activities.php";
  require_once "web/includes/network.inc.php";
  $error = FALSE;
  $authorization_required = TRUE;

  if (!empty($_GET['delete_gid'])) {

    ///for deleting single group.
    if (!empty($_GET['action']) && $_GET['action'] == 'delete') {
      $gid = (int)$_GET['delete_gid'];

      $group = ContentCollection::load_collection((int)$gid, $login_uid);
      $group->delete();
      //Deleting all the activities of this group from activities table for rivers of people module
      Activities::delete_for_group($gid);
      $message = __("Group deleted successfully");
      unset($_GET['delete_gid']);

    }
 }
	if (!empty($_POST['gid'])) {

		 $group_ids = $_POST['gid'];

		 foreach ($group_ids as $i=>$delete_gid) {

			 $group = ContentCollection::load_collection((int)$delete_gid, $login_uid);

			 $group->delete();

			 //Deleting all the activities of this group from activities table for rivers of people module

			 Activities::delete_for_group($delete_gid);

			 unset($_POST['gid']);

		 }

		 $msg = uihelper_plural(count($group_ids), 'group');

		 $message = sprintf(__("%s groups deleted successfully"), $msg);

	}


 function setup_module($column, $module, $obj) {
   switch ($module) {
       case 'ManageGroupsModule':
         if (!empty($_GET['sort_by'])) $obj->set_sort_by($_GET['sort_by']);
         if (!empty($_GET['sort_dir'])) $obj->set_sort_dir($_GET['sort_dir']);
         if (!empty($_GET['search_str'])) $obj->search_str = $_GET['search_str'];
    }
 }
 $page = new PageRenderer("setup_module", PAGE_NETWORK_MANAGE_GROUPS, "Manage Groups", 'container_two_column.tpl','header.tpl',PUB, HOMEPAGE, PA::$network_info);

uihelper_error_msg(@$message);
$page->html_body_attributes ='class="no_second_tier network_config"';
uihelper_get_network_style();

echo $page->render();
?>