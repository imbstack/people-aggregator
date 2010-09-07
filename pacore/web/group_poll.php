<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.

include_once("web/includes/page.php");

require_once "web/includes/functions/auto_email_notify.php";
require_once "web/includes/functions/user_page_functions.php";
require_once("api/Group/Group.php");


/*including Js files */
$parameter = js_includes('common.js');
// for query count
global $query_count_on_page;
$query_count_on_page = 0;

$msg = $app->getRequestParam('msg');

// echo "<pre>" . print_r($group_var->title,true) . "</pre>"; exit;

function setup_module($column, $module, $obj) {
  $group_var = new Group();
  $group_var->load($_REQUEST['gid']);
      $obj->title = sprintf(__('%s Events'), PA::$group_noun);
      $obj->assoc_type = 'group';
      $obj->assoc_id = $_REQUEST['gid'];
      $obj->assoc_title = $group_var->title;
      $is_member = Group::get_user_type((int)$_SESSION['user']['id'], (int)$_REQUEST['gid']);
      if ($is_member == NOT_A_MEMBER) {
        $obj->may_edit = false;
      } else {
        $obj->may_edit = true;
      }
  $obj->mode = PUB;
}

$page = new PageRenderer("setup_module", PAGE_GROUP_POLL, "Poll - PeopleAggregator", "container_one_column.tpl", "header_group.tpl", PRI, NULL, PA::$network_info);

$css_path = PA::$theme_url . '/calendar.css';
$page->add_header_css($css_path);
$page->add_header_html(js_includes('calendar.js'));

uihelper_error_msg($msg);
uihelper_get_group_style((int)$_REQUEST['gid']);

$page->add_header_html($parameter);

echo $page->render();

?>
