<?php
$login_required = FALSE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
include_once "api/Theme/Template.php";
/*including Js files */
$parameter = js_includes('common.js');
$page_id = PAGE_NETWORKS_HOME;

if (!PA::$network_capable) die(__("Networks are disabled."));

// which blocks are coming on this page
$setting_data = ModuleSetting::load_setting(PAGE_NETWORKS_HOME, @PA::$login_user->user_id);

$setting_data['left'] = 
  (is_array(@$setting_data['left'])) ? $setting_data['left'] : array(); 
  
if (@$_SESSION['user']['id']) {
  array_unshift($setting_data['left'],'MyNetworksModule');
}
array_unshift($setting_data['left'],'FeaturedNetworkModule','VideoTourModule');
$params = array();
$params['cnt'] = TRUE;
$network_obj = new Network();
 $total_network = $network_obj->get($params);
function setup_module($column, $moduleName, $obj) {
  global $user_edit, $error,$uid,$rel_type,$uid,$user,$paging,$page_uid,$login_uid,$total_network;
  switch ($moduleName) {
    case 'FeaturedNetworkModule':
       $obj->block_type = 'FeaturedNetwork';
    break;
    case 'VideoTourModule':
       $obj->block_type = 'VideoTour';
    break;
    case 'MyNetworksModule':
        if (!$_SESSION['user']['id']) return "skip";
        if ($page_uid && ($page_uid !=$login_uid)) {
          $obj->uid = $page_uid;
          $page_user = get_user();
          $obj->title = ucfirst($page_user->first_name).'\'s Networks'; /*INT*/
          $obj->user_name = $page_user->login_name;
        }
        else {
          $obj->uid = $login_uid;          
        }
    break;
    case 'LogoModule':
    break;
    case 'NetworksDirectoryModule':
           $obj->mode = PUB;          	
           if (@$_GET['keyword']) {
                $obj->name_string = @$_GET['name_string'];
                $obj->keyword = $_GET['keyword'];
                $obj->sort_by = @$_GET['sort_by'];
           }
           else{
                $obj->sort_by = @$_GET['sort_by'];

           }
           if ( @$_GET['uid'] ) {
              $obj->uid =  $_GET['uid'];
              $obj->sort_by = @$_GET['sort_by'];
           }
           $obj->Paging["page"] = $paging["page"];
           $obj->Paging["show"] = $paging["show"];
           $obj->total_network = $total_network;
    break;
    case 'NetworksCategoryModule':
        $obj->mode = PUB; 
        $obj->block_type = "NetworkCategory";
        $obj->total_network = $total_network;
    break;
    case 'SearchNetworksModule':
       return 'skip';
    break;
    case 'NewestNetworkModule':
      $obj->sort_by = TRUE;
      $obj->title = 'Networks';
    break;
    }
}
$page = new PageRenderer("setup_module", PAGE_NETWORKS_HOME, __("Network Directory"), "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info,'',$setting_data);

$page->add_header_html($parameter);

$page->html_body_attributes = ' class="no_second_tier" id="pg_network_home"';
uihelper_get_network_style ();
echo $page->render();
?>
