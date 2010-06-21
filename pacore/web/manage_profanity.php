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
//including necessary files
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "web/includes/network.inc.php";
$authorization_required = TRUE;
if(!empty($_POST)) {
    $error = false;
    if(!$handle = fopen(PA::$project_dir."/config/profanity_words.txt", 'w+')) {
        if(!$handle = fopen(PA::$core_dir."/config/profanity_words.txt", 'w+')) {
            $msg = 5040;
            $error = TRUE;
        }
    }
    if(fwrite($handle, $_POST['file_text']) === FALSE) {
        $msg = 5041;
        $error = TRUE;
    }
    else {
        //'Profanity word list has been successfully updated.'
        $error                     = false;
        $msg                       = 5043;
        //  PA::$config->profanity = explode("\r\n", $_POST['file_text']);
    }
    fclose($handle);
}

function setup_module($column, $module, $obj) {
    switch($module) {
        case 'ManageProfanityFile':
            break;
    }
}
$page = new PageRenderer("setup_module", PAGE_MANAGE_PROFANITY, "Manage Profanity", 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);
if(!empty($msg)) {
    uihelper_error_msg($msg);
}
$page->html_body_attributes = 'class="no_second_tier"';
uihelper_get_network_style();
echo $page->render();
?>