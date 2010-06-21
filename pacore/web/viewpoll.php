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
$login_required = FALSE;
$use_theme      = 'Beta';
$parameter      = '';
include_once("web/includes/page.php");
global $login_uid;
error_reporting(0);
$parameter .= js_includes('common.js');
// for memeber facewall module.
$users = Network::get_members(array('page' => 1, 'show' => 5, 'network_id' => PA::$network_info->network_id));

function setup_module($column, $moduleName, $obj) {
    global $users, $view_previous;
    switch($moduleName) {
        case 'MembersFacewallModule':
            $obj->links = $users;
            $obj->sort_by = TRUE;
            break;
        case 'PollModule':
            $obj->view_previous = TRUE;
            break;
    }
}
$page = new PageRenderer("setup_module", PAGE_VIEWPOLL, "viewpoll", "container_three_column.tpl", "header.tpl", PRI, HOMEPAGE, PA::$network_info);
$page->add_header_html($parameter);
$page->html_body_attributes = 'class="no_second_tier" id="pg_homepage"';
$css_array = get_network_css();
if(is_array($css_array)) {
    foreach($css_array as $key => $value) {
        $page->add_header_css($value);
    }
}
echo $page->render();
?>