<?php
$login_required = FALSE;
include_once("web/includes/page.php");

$code_esc = intval(@$_REQUEST['code']);
$msg_esc = htmlspecialchars(@$_REQUEST['msg']);

// ---

$page = new PageRenderer(NULL, NULL, "Error $code_esc: $msg_esc", "generic_error.tpl",'header.tpl',PRI,HOMEPAGE,PA::$network_info);
$msg_tpl = & new Template(CURRENT_THEME_FSPATH."/error_middle.tpl");
$msg_tpl->set('code', $code_esc);
$msg_tpl->set('msg', $msg_esc);
$page->add_module("middle", "top", $msg_tpl->fetch());

$page->add_module("middle", "bottom",
                  '<p style="text-align: center"><a href="'.htmlspecialchars($_SERVER['HTTP_REFERER']).'">back</a></p>');

echo $page->render();

?>
