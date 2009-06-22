<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:       View All Media, web file to display celebrities media gallery contents can be later used for displaying sites media
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 */
 
$login_required = FALSE;
// Only log in user can Uploaded media 
if(!empty($_POST)) {
  $login_required = TRUE;
}
$msg = NULL;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "web/includes/functions/user_page_functions.php";

  //Handling The Views 
  $show_view = (!empty($_GET['gallery'])) ? $_GET['gallery']: 'thumb';
  // Handling the type   
  $type_prefix = array('Images','Videos');//for the time being videao and Audios
//are not included in the array.
  if (empty($_GET['type']) || (!in_array($_GET['type'],$type_prefix))) { 
  // When we doesn't found type 
    $type = 'Images';
  } else {
    $type = $_GET['type'];
  }
  $module_name = $type.'ViewAllModule';
  $setting_data = array('middle' => array($module_name));


     
/* This function is a Callback function which initialize the value for the BLOCK MODULES */
function setup_module($column, $moduleName, $obj) {
/* in this module we have to set user_id , group_id, as well as netwrok_id */  
  global $uid, $type, $show_view;
  $obj->uid = $uid;
  $obj->show_view = $show_view;
  if (!empty($_GET['uid'])) {
    $obj->uid = $_GET['uid'];
  }
  $obj->type = $type;
}



// fetching the data from the Constants.php and Rendering the data of the page 
// at present we are setting the value of setting data 
$page = new PageRenderer("setup_module", PAGE_VIEW_ALL_MEDIA, 'View All', "container_one_column_media_gallery.tpl", "header_user.tpl", PUB, NULL, $network_info, null, $setting_data);

$msg = (!empty($_GET['msg']))? $_GET['msg'] : $msg;
$msg = (!empty($_GET['msg_id']))? $_GET['msg_id'] : $msg;
uihelper_error_msg(@$msg);
// Jtip is shown when user watching the image media
if ($type == 'Images') { 
  $parameter = js_includes('jtip.js');
  $page->add_header_html($parameter);
  $css = $current_theme_path.'/jtip.css';
  $page->add_header_css($css);
}
// if user is not log in as well as not viewing any user page 
if (!empty($uid)) {
  uihelper_set_user_heading($page,$do_theme=TRUE, $uid);
}
else {
  uihelper_get_network_style();
}

echo $page->render();
?>