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
// This page us used for network creation
//anonymous user can not view this page;
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
//including necessary files
include_once("web/includes/page.php");

require_once "web/includes/classes/file_uploader.php";
require_once "api/Validation/Validation.php";
require_once "web/includes/network.inc.php";
require_once "web/includes/classes/NetworkConfig.class.php";

$authorization_required = TRUE;
$error = FALSE;
$error_msg = null;

if (!empty($_GET['msg_id'])) {
  $error_msg =  $_GET['msg_id'];
}

$restore_settings = null;
if(!empty($_REQUEST['config_action'])) {
  switch($_REQUEST['config_action']) {
    case 'load_general_settings':
      if(!empty($_FILES['local_file']['name']) && is_uploaded_file($_FILES['local_file']['tmp_name'])) {
        if($_FILES['local_file']['type'] != 'text/xml') {
          $error_msg = __('Invalid configuration file format. Configuration file should be a valid XML file. ');
        } else {
          try {
            $content = file_get_contents($_FILES['local_file']['tmp_name']);
            $imported_config = new NetworkConfig($content);
            $restore_settings = $imported_config->getGeneralNetworkSettings();
            $restore_settings['extra'] = serialize($restore_settings['extra']);
            $error_msg = __("File ") . $_FILES['local_file']['name'] . __(" loaded successfully.") . "<br />"
                       . __("Click \"Save\" button to save new settings.");
          } catch (Exception $e) {
            $error = TRUE;
            $error_msg = $e->getMessage();
          }
        }
      } else {
          $error_msg = __('Please, select a valid XML configuration file.');
      }
    break;
    case 'restore_defaults':
      try {
        $imported_config = new NetworkConfig();
        $restore_settings = $imported_config->getGeneralNetworkSettings();
        $restore_settings['extra'] = serialize($restore_settings['extra']);
        $error_msg = __('Default settings sucessfully restored.') . "<br />"
                   . __("Click \"Save\" button to save new settings.");
      } catch (Exception $e) {
        $error = TRUE;
        $error_msg = $e->getMessage();
      }
    break;
    case 'download_settings_file':
        $imported_config = new NetworkConfig();
        if(!empty($imported_config->settings_file)) {
          download($imported_config->settings_file, 'xml');
          exit;
        }
    break;
    case 'upload_settings_file':
      if(!empty($_FILES['config_file']['name']) && is_uploaded_file($_FILES['config_file']['tmp_name'])) {
        if($_FILES['config_file']['type'] != 'text/xml') {
          $error_msg = __('Invalid configuration file format. Configuration file should be a valid XML file. ');
        } else {
          try {
            $content = file_get_contents($_FILES['config_file']['tmp_name']);
            $imported_config = new NetworkConfig($content);
            $imported_config->storeSettingsLocal();
            //      echo "<pre>".print_r($restore_settings,1)."</pre>";
            $error_msg = __("File ") . $_FILES['config_file']['name'] . __(" uploaded successfully.");
          } catch (Exception $e) {
            $error = TRUE;
            $error_msg = $e->getMessage();
          }
        }
      } else {
          $error_msg = __('Please, select a valid XML configuration file.');
      }
    break;
  }
}

if($restore_settings) {
  $network_settings = (object)$restore_settings;
} else {
  $network_settings = PA::$network_info;
}

if (!empty($network_settings)) {
  $extra = unserialize($network_settings->extra);
  $form_data['name'] = $network_settings->name;
  $form_data['tagline'] = $network_settings->tagline;
  $form_data['category'] = $network_settings->category_id;
  $form_data['type'] = PA::$network_info->type;
  $form_data['desc'] = $network_settings->description;
/**
/ NOTE: all these initial setting should be defined and retrived from default XML settings file
/
**/
  $form_data['network_group_title'] = $extra['network_group_title'];
  $form_data['reciprocated_relationship'] = (isset($extra['reciprocated_relationship'])) ? $extra['reciprocated_relationship'] : NET_YES;
  $form_data['email_validation'] = @$extra['email_validation'];
  $form_data['captcha_required'] = @$extra['captcha_required'];
  $form_data['show_people_with_photo'] = @$extra['show_people_with_photo'];
  $form_data['top_navigation_bar'] = @$extra['top_navigation_bar'];
  $form_data['network_content_moderation'] = @$extra['network_content_moderation']; // can be missing
  $form_data['action'] = 'edit';
  $form_data['inner_logo_image'] = $network_settings->inner_logo_image;
  $form_data['language_bar_enabled'] = (isset($extra['language_bar_enabled'])) ? $extra['language_bar_enabled'] : NET_YES;
  $form_data['default_language'] = (isset($extra['default_language'])) ? $extra['default_language'] : 'english';
}
// also check if the person who is editing the network is really the owner of network


if ( @$_POST['action']=='delete' && !$error ) {
  if(@$_POST['delete_network']==1 ) {
    $delete_permission = false;
    if(PA::$login_uid) {
      $delete_permission = User::has_network_permissions(PA::$login_uid, array('delete_network'), true);
    }
    if ( $delete_permission ) {
      //delete network and redirect to mother network homepage
      // FIX ME where we want to send after deletion
      $m = mothership_info();
      Network::delete(PA::$network_info->network_id);
		  header('location:'.$m['url']."?msg=7029");exit;
    } else {
      throw new PAException(OPERATION_NOT_PERMITTED,'You are not authorised to delete the network.');
    }
  } else {
    $error = TRUE;
    $error_msg = __('Please confirm network deletion by selecting deletion checkbox.');
  }
}

if ( @$_POST['action'] == 'edit' && !$error) {
 $vartoset = array('name','tagline','category','desc','header_image','header_image_option','network_group_title', 'action', 'type');
  filter_all_post($_POST);//filters all data of html
  for ($i = 0; $i < count($vartoset); $i += 1) {
    $var = $vartoset[$i];
    if (!empty($_POST[$var])) {
      $form_data[$var] = $_POST[$var];
    }
    if ($var == 'type') {
      if (isset($_POST[$var])) {
        $form_data[$var] = $_POST[$var];
      }
    }

  }
  // No need to verify category of MotherNetwork
  if (PA::$network_info->type == MOTHER_NETWORK_TYPE) {
    $skip_check = array('address', 'category');
  }
  else {
    $skip_check = array('address');
  }

  $error_post = check_error($skip_check);//validation check

  if ($error_post['error']==TRUE) {
    $error = TRUE;
    $error_msg = $error_post['error_msg'];
  }
  if ( !$error_post ) {
    //code to upload the icon image
    if (!empty($_FILES['inner_logo_image']['name'])) {
      $uploadfile = PA::$upload_path . basename($_FILES['inner_logo_image']['name']);
      $myUploadobj = new FileUploader; //creating instance of file.
      $image_type = 'image';
      $file = $new_inner_logo_image = $myUploadobj->upload_file(PA::$upload_path, 'inner_logo_image', true, true, $image_type);
      if ($file == false) {
        $error = TRUE;
        $error_msg = $file_upload_result['error_msg'];
        unset($data_icon_image);
      } else {
        $data_icon_image = array('inner_logo_image' => $file);
      }

    } else {
      unset($data_icon_image);
    }
    //...code to upload the icon image

      $network_basic_controls = $extra;
      $network_basic_controls['network_group_title'] = $_POST['network_group_title'];
      if (!empty($_POST['reciprocated_relationship'])) {
        $network_basic_controls['reciprocated_relationship'] = NET_YES;
      } else {
        $network_basic_controls['reciprocated_relationship'] = NET_NO;
      }
      if (!empty($_POST['email_validation'])) {
        $network_basic_controls['email_validation'] = NET_YES;
      } else {
        $network_basic_controls['email_validation'] = NET_NO;
      }
      if (!empty($_POST['captcha_required'])) {
        $network_basic_controls['captcha_required'] = NET_YES;
      } else {
        $network_basic_controls['captcha_required'] = NET_NO;
      }
      if (!empty($_POST['language_bar_enabled'])) {
        $network_basic_controls['language_bar_enabled'] = NET_YES;
      } else {
        $network_basic_controls['language_bar_enabled'] = NET_NO;
      }

      if (!empty($_POST['show_people_with_photo'])) {
        $network_basic_controls['show_people_with_photo'] = NET_YES;
      } else {
        $network_basic_controls['show_people_with_photo'] = NET_NO;
      }
      // if top menu bar is to be hidden
      $network_basic_controls['top_navigation_bar'] = NET_YES;
      if (!empty($_POST['top_navigation_bar'])) {
        $network_basic_controls['top_navigation_bar'] = NET_NO;
      }
      $network_basic_controls['network_content_moderation'] = NET_NO;
      if (!empty($_POST['network_content_moderation'])) {
        $network_basic_controls['network_content_moderation'] = NET_YES;
      }
      $network_basic_controls['default_language'] = $_POST['default_language'];
      $data = array(
       'name' => $form_data['name'],
       'tagline' => $form_data['tagline'],
      'category_id' => $form_data['category'],
      'description' => $form_data['desc'],
      'type' => $form_data['type'],
      'extra'=>serialize($network_basic_controls),
      'network_id'=>PA::$network_info->network_id,
      'changed'=>time()
      );
      //add icon image
      if (is_array(@$data_icon_image) && !empty($data_icon_image['inner_logo_image'])) {
        $data = array_merge($data, $data_icon_image);
        $form_data['inner_logo_image'] = $data_icon_image['inner_logo_image'];
      }

    //try following line
    $network = new Network;
    $network->set_params($data);
    try{
      $nid = $network->save();
      PA::$network_info = get_network_info();//refreshing the network_info after saving it.
      $error_msg = 'Network Information Successfully Updated';

      if(!empty($_REQUEST['config_action']) && ($_REQUEST['config_action'] == 'store_as_defaults')) {
        $export_config = new NetworkConfig();
        $export_config->buildNetworkSettings($network);
        $export_config->storeSettingsLocal();
        $error_msg = 'Network default configuration file "' . $export_config->settings_file . '" successfully updated.';
      }

      if (!empty($new_inner_logo_image)) Storage::link($new_inner_logo_image, array("role" => "avatar"));

      //set $form_data['reciprocated_relationship']if reciprocated relationship is saved
      $form_data['reciprocated_relationship'] = $network_basic_controls['reciprocated_relationship'];
      $form_data['email_validation'] = $network_basic_controls['email_validation'];
      $form_data['captcha_required'] = $network_basic_controls['captcha_required'];
      $form_data['show_people_with_photo'] = $network_basic_controls['show_people_with_photo'];
      $form_data['top_navigation_bar'] = $network_basic_controls['top_navigation_bar'];
      $form_data['language_bar_enabled'] = $network_basic_controls['language_bar_enabled'];
      $form_data['default_language'] = $network_basic_controls['default_language'];
      $form_data['network_content_moderation'] = $network_basic_controls['network_content_moderation'];
    } catch (PAException $e) {
      $error = TRUE;
      $error_msg = "$e->message";
    }
  }

}//...$_POST if ends
//render the page
$page = new PageRenderer("setup_module", PAGE_NETWORK_STATISTICS, sprintf(__("Network Statistics - %s"), PA::$network_info->name), 'container_two_column.tpl','header.tpl',PRI,HOMEPAGE, PA::$network_info);

uihelper_error_msg($error_msg);
uihelper_get_network_style();
$page->html_body_attributes ='class="no_second_tier network_config"';

$css_path = PA::$theme_url . '/admin2.css';
$page->add_header_css($css_path);

//..end render the page

/*  ---------- FUNCTION DEFINITION ------------------*/
//call back function
function setup_module($column, $module, $obj) {
  global $form_data, $error, $error_msg;

  $can_mange_settings = false;
  if(PA::$login_uid) {
    // if user can delete network - he can change any settings data!
    $can_mange_settings = User::has_network_permissions(PA::$login_uid, array('delete_network'), true);
  }

  if($can_mange_settings) {
    $obj->tpl_to_load = "stats";
    $obj->title = __('General Network Settings');
  } else {
    $obj->tpl_to_load = "default";
    $obj->title = __('Administration panel');
  }
  $obj->control_type = "basic";
  $obj->form_data = $form_data;
  $obj->error = $error;
  $obj->error_msg = $error_msg;
  $obj->is_edit = TRUE;
  $param['network_id'] = PA::$network_info->network_id;
  $obj->network_stats = Network::get_network_statistics($param);
  // check for meta network control access
  $obj->meta_network_reci_relation = FALSE;
  if (PA::$login_uid == SUPER_USER_ID &&
      PA::$network_info->type == MOTHER_NETWORK_TYPE
     ) {
    $obj->meta_network_reci_relation = TRUE;
  }
 //add variables to BlockModule
}
echo $page->render();
?>