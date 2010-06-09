<?php
$login_required = FALSE;
include_once("web/includes/page.php");

function setup_module($column, $module, $obj) {
    global $paging;

    switch ($column) {
    case 'left':
        if ($module=='RecentTagsModule') {
            $limit = 100;
            $obj->mode = PRI;
            
            $obj->block_type = HOMEPAGE;
            $obj->get_recent_tags($limit);
        }
        break;

    case 'middle':
        if($module == "ShowContentModule") {
            $obj->show_all = 1;
            
            $obj->Paging["page"] = $paging["page"];
            $obj->Paging["show"] = $paging["show"];
        }
        $array_middle_modules[] = $obj->render();  
        break;
    }
}

$page = new PageRenderer("setup_module", PAGE_SEARCH, "Search Content", "groups.tpl",'header.tpl',PRI,HOMEPAGE,$network_info);

echo $page->render();

?>