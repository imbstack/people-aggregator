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
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "web/includes/functions/auto_email_notify.php";
require_once "web/includes/functions/user_page_functions.php";

/* including Js files */
$parameter = js_includes('common.js');
// for query count
global $query_count_on_page;
$query_count_on_page = 0;

function setup_module($column, $module, $obj) {
    switch($module) {
        case 'EventCalendarModule':
            $obj->title      = __('Edit Events');
            $obj->assoc_type = 'user';
            $obj->assoc_id   = (int) PA::$login_uid;
            $obj->may_edit   = true;
            // user may edit own events, right? ;)
            $obj->assoc_title = PA::$login_user->login_name;
            break;
    }
    $obj->mode = PUB;
}
global $msg;
$page = new PageRenderer("setup_module", PAGE_CALENDAR, __("My Events"), "container_one_column.tpl", "header_user.tpl", NULL, PRI, PA::$network_info);
$css_path = PA::$theme_url.'/calendar.css';
$page->add_header_css($css_path);
$page->add_header_html(js_includes('calendar.js'));
$page->add_header_html($parameter);
uihelper_error_msg($msg);
uihelper_set_user_heading($page);
echo $page->render();
?>