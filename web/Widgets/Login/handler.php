<?php
//error_reporting(E_ALL);
include_once(dirname(__FILE__)."/../../../config.inc");

$template_file = 'web/Widgets/'.$widget_name.'/widget.tpl';
$template = & new Template($template_file);
$template->set('login_uid', PA::$login_uid);
$html .= $template->fetch();
header("Content-Type: application/x-javascript");
echo "document.getElementById('pa_widget_login').innerHTML = ".js_quote($html).";";

?>