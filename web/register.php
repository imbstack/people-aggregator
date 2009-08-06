<?php
$login_required = FALSE;
$login_never_required = TRUE; // because this page must be visible even if you are not logged in and on a private network!
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
include "web/includes/classes/RegistrationPage.php";


if (isset($_SESSION['user']['id'])) {
  header("Location: " . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . ($_SESSION['user']['id']));
  exit;
}



function setup_module($column, $moduleName, $obj) {
    global $rp;

    switch ($column) {
    case 'left':
        if ($moduleName=='RecentCommentsModule') {
          $obj->block_type = HOMEPAGE;
          $obj->mode = PRI;
        }
    break;

    case 'middle':
        $obj->mode = PUB;
        $obj->uid = PA::$uid;

        if ($moduleName == 'RegisterModule') {
          if (!empty($rp->inv_error)){
            continue;
          }

          if (isset($rp->reg_user)) {
            $obj->array_of_errors = @$rp->reg_user->array_of_errors;
          }
        }
    break;

    case 'right':
       $obj->mode = PRI;
       if ($moduleName != 'AdsByGoogleModule') {
          $obj->block_type = HOMEPAGE;
       }
    break;
    }
}

$rp = new RegistrationPage();
try {
  $rp->main();
  $msg = (!empty($rp->reg_user->msg)) ? nl2br($rp->reg_user->msg) : @$rp->inv_error;
} catch (PAException $e) {
  $msg = $e->getMessage();
}

$page = new PageRenderer("setup_module", PAGE_REGISTER, "Registration Page", "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info);

// added by Zoran Hron: JQuery validation & AJAX file upload --
$page->add_header_html(js_includes('jquery.validate.js'));
$page->add_header_html(js_includes('jquery.metadata.js'));
$page->add_header_html(js_includes('ajaxfileupload.js'));
$page->add_header_html(js_includes('user_registration.js'));


uihelper_error_msg($msg);
uihelper_get_network_style();


$page->html_body_attributes = ' id="registration_page"';

echo $page->render();
?>
