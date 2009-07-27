<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        manage_taketour.php, web file to  Manage the take a tour block module
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file manages take a tour block module.
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 *
 */
// This page us used for Manage Take A Tour
//Super  user can  view this page;
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
//including necessary files
include_once("web/includes/page.php");
include_once "api/ModuleSetting/ModuleSetting.php";
include_once "api/Theme/Template.php";
require_once "api/Category/Category.php";
require_once "web/includes/classes/file_uploader.php";
require_once "api/Validation/Validation.php";
require_once "api/Tag/Tag.php";
require_once "web/includes/network.inc.php";
require_once "api/ModuleData/ModuleData.php";
include_once "web/languages/english/MessagesHandler.php";

$authorization_required = TRUE;
$error = FALSE;

if ( @$_GET['msg_id'] ) {
  $error_msg =  MessagesHandler::get_message($_GET['msg_id']);
}

$file = null;

if (@$_POST['submit']=='Submit') {
  if (!empty($_FILES['userfile_0']['name'])) {
    $myUploadobj = new FileUploader; //creating instance of file.
    $file = $myUploadobj->upload_file(PA::$upload_path,'userfile_0',TRUE);
    if (!$file) {
      $msg = $myUploadobj->error;
      $error = TRUE;
    } else {
      $msg=__('Successfully updated');
      Storage::link($file, array("role" => "tour_img"));
    }
  }
  $data=array();
  if ($_POST["userfile_url_0"]){
    $data[0]['url'] = $_POST["userfile_url_0"];
  }
  if ($_POST['caption'][0]) {
    $data[0]['title'] = $_POST['caption'][0];
  }
  $data[0]['file_name'] = Storage::validateFileId($file ? $file : $_POST['userimage_0']);

  $data=serialize($data);
  $id=2; // stands for the Update for Take Tour
  if ( !$error ) {
    ModuleData::update($data,$id);
  }
}

//render the page
$page = new PageRenderer("setup_module", PAGE_MANAGE_TAKETOUR, "Manage Take A Tour", 'container_two_column.tpl','header.tpl',PRI,HOMEPAGE,$network_info);

if (!empty($msg)) {
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg);
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}

$page->add_header_html(js_includes('edit_profile.js'));
$page->add_header_html(js_includes('manage_data.js'));
$page->html_body_attributes ='class="no_second_tier network_config"';

  $css_array = get_network_css();
  if (is_array($css_array)) {
    foreach ($css_array as $key => $value) {
      $page->add_header_css($value);
    }
  }

  $css_data = inline_css_style();
  if (!empty($css_data['newcss']['value'])) {
    $css_data = '<style type="text/css">'.$css_data['newcss']['value'].'</style>';
    $page->add_header_html($css_data);
  }


//..end render the page

/*  ---------- FUNCTION DEFINITION ------------------*/
//call back function
function setup_module($column, $module, $obj) {
  global $form_data, $error, $error_msg,$network_info,$perm;
  $obj->perm = $perm;
}
echo $page->render();
?>