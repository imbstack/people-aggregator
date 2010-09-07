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
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        manage_embleum.php, web file to  Manage the embleum image
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file manages the image of the embleum. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
// This page us used for Manage the Embleum image.
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
$msg = NULL;

if (@$_POST['submit']=='Submit') {
  $data=array(); //
  for ($i = 0;$i<count($_FILES);$i++ ) {
    if (!empty($_FILES['userfile_'.$i]['name'])) {
      $file_name = $_FILES['userfile_'.$i]['name']; 
      $myUploadobj = new FileUploader; //creating instance of file.
      $file = $myUploadobj->upload_file(PA::$upload_path,'userfile_'.$i,TRUE);
      if ($file == false) {
        $msg = $myUploadobj->error;
        $error = TRUE;
      }
      else {
        $error_file = FALSE;
        $msg='successfully updated';
      }
  }
  
  if ($_POST["userfile_url_$i"]){
      $data[$i]['url'] = $_POST["userfile_url_$i"];
    }    
  if ($_POST['caption'][$i]) {
      $data[$i]['title'] = $_POST['caption'][$i];
    }
   if (!empty($_FILES['userfile_'.$i]['name'])) {
      $data[$i]['file_name'] = $file;
      Storage::link($file, array("role" => "emblem"));
    }
      else {
      $data[$i]['file_name'] = $_POST['userimage_'.$i];
    }
      
 } 
      
 $data=serialize($data);
 $id=1; // stands for the Update for emblum data
 if (!$error) {
   ModuleData::update($data,$id);  // call the ModuleData to update the data
 }
  
}
//render the page
$page = new PageRenderer("setup_module", PAGE_MANAGE_EMBLEM, "Manage Emblem", 'container_two_column.tpl','header.tpl',PRI,HOMEPAGE,PA::$network_info);
if (!empty($msg)) {
  $msg_tpl = new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg);
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}
$page->html_body_attributes ='class="no_second_tier network_config"';
$page->add_header_html(js_includes('edit_profile.js'));
$page->add_header_html(js_includes('manage_data.js'));
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
  global $form_data, $error, $error_msg,$perm;
  $obj->perm = $perm;
  
}
echo $page->render();
?>