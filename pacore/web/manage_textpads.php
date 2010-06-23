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
 * File:        manage_textpads.php, web file to  Manage the textpad modules
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file has the business logic for creating the text pads which will be displayed across all the pages *              in the application.
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 *
 */

// logged in user can view this page
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
require_once "api/Advertisement/Advertisement.php";
include_once "web/languages/english/MessagesHandler.php";

$error = FALSE;
$msg = $form_data = array();
$edit = FALSE;
$message = NULL;

if ((!empty($_GET['do'])) && $_GET['do'] == 'edit' && !empty($_GET['ad_id'])) {
  $edit = TRUE;
  $res = Advertisement::get($params = NULL, $condition = array('ad_id' => ((int)$_GET['ad_id'])));
  if (!empty($res)) {
    $form_data['ad_id'] = $res[0]->ad_id;
    $form_data['ad_title'] = $res[0]->title;
    $form_data['ad_description'] = $res[0]->description;
    $form_data['page_id'] = $res[0]->page_id;
    $form_data['orientation'] = $res[0]->orientation;
    $form_data['created'] = $res[0]->created;
  }
} else if ((!empty($_GET['action'])) && $_GET['action'] == 'delete' && !empty($_GET['ad_id'])) {
    if (!empty($_GET['ad_id'])) {
      try {
        Advertisement::delete((int)$_GET['ad_id']);
        header("Location: ".PA::$url.'/'.FILE_MANAGE_TEXTPADS.'?msg_id=19018');
        exit;
      } catch (PAException $e) {
        $msg[] = $e->message;
      }
    }
} else if (!empty($_GET['do']) && !empty($_GET['ad_id'])) {
  switch ($_GET['do']) {
    case 'disable':
      $field_value = DELETED;
      $msg_id = 19016;
    break;
    case 'enable':
      $field_value = ACTIVE;
       $msg_id = 19017;
    break;
  }
  $update_fields = array('is_active' => $field_value);
  $condition = array('ad_id' => $_GET['ad_id']);
  try {
    Advertisement::update($update_fields, $condition);
    header("Location: ".PA::$url.'/'.FILE_MANAGE_TEXTPADS."?msg_id=$msg_id");
    exit;
  } catch (PAException $e) {
    $msg[] = $e->message;
  }
}


$advertisement = new Advertisement();

if (!$error && !empty($_POST) && $_POST['btn_apply_name']) { // if page is submitted
  if (!empty($_POST['ad_id'])) {
    $advertisement->ad_id = $_POST['ad_id'];
    $advertisement->created = $_POST['created'];
    $msg_id = 19014;
  } else {
    $msg_id = 19015;
    $advertisement->created = time();
  }

  $advertisement->user_id = $login_uid;
  $advertisement->title = $form_data['ad_title'] = $_POST['ad_title'];
  if (strlen($advertisement->title) > 30) {
    $error = TRUE;
    $_GET['msg_id'] = 19020;
  }
  $advertisement->description = $form_data['ad_description'] = $_POST['ad_description'];
  $advertisement->page_id = $form_data['page_id'] = $_POST['page_id'];
  $advertisement->orientation = $form_data['orientation'] = $_POST['x_loc'].','.$_POST['y_loc'];


  $ad_data = Advertisement::get(NULL, array('orientation'=>$advertisement->orientation, 'is_active'=>TRUE));
  if (!empty($ad_data) && !empty($advertisement->ad_id) && $advertisement->orientation != '0,0') {
    $ad_data = $ad_data[0];
    if ($advertisement->ad_id != $ad_data->ad_id) {
      $error = TRUE;
      $message = ucfirst($ad_data->type).__(' is already enabled at specified orientation');
    }
  }
  $advertisement->changed = time();
  $advertisement->is_active = ACTIVE;
  $advertisement->type = 'textpad';

  if (!$error) {
    try {
      $advertisement->save();
      $form_data = array();
      header("Location: ".PA::$url.'/'.FILE_MANAGE_TEXTPADS."?msg_id=$msg_id");
      exit;
    } catch (PAException $e) {
      $msg[] = $e->message;
    }
  }
}

$page = new PageRenderer("setup_module", PAGE_MANAGE_TEXTPADS, __("Manage Textpads"), 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);
// removed $onload param as it is not set anywhere

if (!empty($msg)) {
  for ($counter = 0; $counter < count($msg); $counter++) {
    $message .= $msg[$counter]."<br>";
  }
}
// display message
if (!empty($_GET['msg_id'])) {
  $message = $_GET['msg_id'];
}
uihelper_error_msg($message);

$page->html_body_attributes ='class="no_second_tier network_config"';
uihelper_get_network_style();

//..end render the page

/*  ---------- FUNCTION DEFINITION ------------------*/
//call back function
function setup_module($column, $module, $obj) {
  global $form_data, $edit, $paging;

  switch ($module) {
    case 'ManageAdCenterModule':
      $obj->edit = $edit;
      $obj->title = __('Manage Textpads');
      $obj->mode = 'textpad';
      $obj->form_data = $form_data;
      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 5;
    break;
  }
}
echo $page->render();
?>