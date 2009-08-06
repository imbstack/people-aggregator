<?php
$login_required = FALSE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");


$msg = NULL;

function setup_module($column, $moduleName, $obj) {
    global $error, $Paging, $group_ids;
    global $members;
    switch ($column) {
    case 'left':       
       if ($moduleName != 'LogoModule') {
          $obj->block_type = HOMEPAGE;
       }
       if ($moduleName == 'MembersFacewallModule') {
          $obj->sort_by = TRUE;
       }
     break;

    case 'middle':        
        $obj->block_type = 'media_management';
        $obj->error = $error;
    break;

    case 'right':       
       if ($moduleName != 'AdsByGoogleModule') {
         $obj->block_type = HOMEPAGE;
       }
       if ($moduleName == 'NewestGroupsModule') {
            $obj->sort_by = TRUE;
        }
    break;
    }
    $obj->mode = PUB;
}
$page = new PageRenderer("setup_module", PAGE_FORGOT_PASSWORD, "Forgot Password", "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info);

uihelper_error_msg($msg);
uihelper_get_network_style();

echo $page->render();

?>