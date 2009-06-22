<?php
error_reporting(E_ALL);
$login_required = "password";
$use_theme = 'Beta';
include_once("web/includes/page.php");
// global var $path_prefix has been removed - please, use PA::$path static variable

require_once "api/Content/Content.php";
require_once "api/User/User.php";
require_once "api/Tag/Tag.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Comment/Comment.php";
include_once "api/ModuleSetting/ModuleSetting.php";
require_once "ext/Group/Group.php";
include_once "api/Theme/Template.php";
require_once "api/Category/Category.php";
require_once "api/ImageResize/ImageResize.php";
require_once "web/includes/classes/file_uploader.php";
require_once "web/includes/functions/user_page_functions.php";

// for query count
global $query_count_on_page;
$query_count_on_page = 0;
$msg1 = $msg2 = $msg = NULL;


if ($_POST) {
  /* Function for Filtering the POST data Array */
  filter_all_post($_POST);  
  
  //type of profile to be updated will come in $_POST['profile_type']
  $profile_type = (isset($_POST['profile_type'])) ? $_POST['profile_type'] : null;
}

$user = new User();
$user->load($login_uid);
$login_name = $user->login_name;
$first_name = $user->first_name;
$last_name = $user->last_name;
$email = $user->email;
$user_picture = $user->picture;

$user_data_general = sanitize_user_data(User::load_user_profile($login_uid, $login_uid, GENERAL));

$delicious_id = @$user_data_general['delicious'];
$flickr_id = @$user_data_general['flickr'];

$user_personal_data = 
  sanitize_user_data(User::load_user_profile($login_uid, $login_uid, PERSONAL));

$user_professional_data = 
  sanitize_user_data(User::load_user_profile($login_uid, $login_uid, PROFESSIONAL));

$params_profile = Array('field_name'=>'BlogSetting','user_id'=>$login_uid);
$data_profile = User::get_profile_data($params_profile);
function setup_module($column, $moduleName, $obj) {
    global $setting_data,$setting_data_enable, $login_uid, $flickr_id, $delicious_id;
    global  $array_of_errors, $login_name, $first_name, $last_name, $email, $user_picture, $user_data_general, $user_personal_data, $user_professional_data,$data_profile;

    switch ($column) {
      case 'left':
	      $obj->mode = PRI;
	      if ( $moduleName == 'EnableModule' ) {
	        $obj->setting_data = $setting_data_enable;
	        $obj->Paging['show'] = 5 ;
	      }
	  	  $obj->uid = $login_uid;
	  break;
    case 'middle':
	    $obj->content_id = @$_REQUEST['cid'];
	    $obj->mode = PRI;
	    $obj->uid = $login_uid;
	 
	    if ($moduleName == 'EditProfileModule') {
	      $obj->array_of_errors = $array_of_errors;
	      $obj->login_name = $login_name;
	      $obj->first_name = $first_name;
	      $obj->last_name = $last_name;
	      $obj->email = $email;
        $obj->user_picture = $user_picture;
	      $obj->user_data = $user_data_general;
	      $obj->user_personal_data = $user_personal_data;
	      $obj->user_professional_data = $user_professional_data;
	      $obj->blogsetting_status = empty($data_profile) ? NULL : $data_profile[0]->field_value;
	    }
	   break;
    }
}

$page = new PageRenderer("setup_module", PAGE_EDIT_PROFILE, sprintf(__("%s - Edit your profile - %s"), $login_user->get_name(), $network_info->name), "container_one_column.tpl", "header_user.tpl", NULL, PRI ,$network_info);


$page->add_header_html(js_includes('edit_profile.js'));

// ------- added by Zoran Hron: JQuery validation -------------
$page->add_header_html(js_includes('jquery.validate.min.js'));
$page->add_header_html(js_includes('jquery.metadata.js'));
// ------------------------------------------------------------

$theme_details = get_user_theme($login_uid);
if (is_array($theme_details['css_files'])) {
  foreach ($theme_details['css_files'] as $key => $value) {
    $page->add_header_css($value);
  }
}

$css_path = $current_theme_path.'/modal.css';
$page->add_header_css($css_path);

if ( @$_GET['updated'] == 1) {
  $uploaded_msg = " Profile updated successfully";
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $uploaded_msg);
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}

if ( !empty($msg) || !empty($msg1) || !empty($msg2)) {
  $uploaded_msg = " Profile updated successfully";
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg.$msg1.$msg2);
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}

// see if we have a user defined CSS 
$user_data_ui = 
  sanitize_user_data(User::load_user_profile($uid,$uid, 'ui'));
if (isset($user_data_ui['newcss'])) {
  $usercss = '<style type="text/css">' . $user_data_ui['newcss'] . '</style>';
  $page->add_header_html($usercss);
}
//To print delete message
if (!empty($_GET['msg_id'])) { 
  uihelper_error_msg($_GET['msg_id']); 
}
// set caption value
if (isset($user_data_general['user_caption'])) {
  $caption1 = chop_string($user_data_general['user_caption'], 20);
} else {
  $caption1 = chop_string($user->first_name." ".$user->last_name, 20);  
}

$page->header->set('caption1', $caption1);
$page->header->set('caption2', chop_string(@$user_data_general['sub_caption'], 40));
$page->header->set('caption_image', @$user_data_general['user_caption_image']);
$page->header->set('desktop_image_action', @$user_data_general['desktop_image_action']);
$page->header->set('theme_details', $theme_details);
$page->header->set('display_image', @$user_data_general['desktop_image_display']);

echo $page->render();

?>