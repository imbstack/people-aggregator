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

require_once "api/Category/Category.php";
require_once "web/includes/classes/file_uploader.php";
require_once "api/Validation/Validation.php";
require_once "api/Tag/Tag.php";
require_once "web/includes/network.inc.php";
require_once "web/includes/constants.php";
require_once "web/includes/classes/NetworkConfig.class.php";
$error = FALSE;

$curr_user = (isset(PA::$login_uid)) ? PA::$login_uid : 0;

$authorization_required = TRUE;
if($curr_user == 0) {
  $configure_permission = false;
} else {
  $configure_permission = Roles::check_administration_permissions($curr_user);
}

$error = FALSE;
$msg = NULL;
if ( !$configure_permission ) {
  $error = TRUE;
  $msg = __("Sorry! you are not authorized to view content of this page");
}


$imported_defaults = null;
if(!empty($_REQUEST['config_action'])) {
  switch($_REQUEST['config_action']) {
    case 'load_general_settings':
      if(!empty($_FILES['local_file']['name']) && is_uploaded_file($_FILES['local_file']['tmp_name'])) {
        if($_FILES['local_file']['type'] != 'text/xml') {
          $msg = __('Invalid configuration file format. Configuration file should be a valid XML file. ');
        } else {
          //  echo "<pre>".print_r($_REQUEST,1)."</pre>";
          try {
            $content = file_get_contents($_FILES['local_file']['tmp_name']);
            $imported_config = new NetworkConfig($content);
            $imported_defaults = $imported_config->getRelationShipSettings();
 //           echo "<pre>".print_r($imported_defaults,1)."</pre>";
            $msg = __("File ") . $_FILES['local_file']['name'] . __(" loaded successfully.") . "<br />"
                       . __("Click \"Save\" button to store new settings.");
          } catch (Exception $e) {
            $error = TRUE;
            $msg = $e->getMessage();
          }  
        } 
      } else {
          $msg = __('Please, select a valid XML configuration file.');
      }
    break;
    case 'restore_defaults':
      try {
        $imported_config = new NetworkConfig();
        $imported_defaults = $imported_config->getRelationShipSettings();
        $msg = __('Default settings sucessfully restored.') . "<br />"
                     . __("Click \"Save\" button to store new settings.");
      } catch (Exception $e) {
        $error = TRUE;
        $msg = $e->getMessage();
      }
    break;
  }
}

if (PA::$network_info) {
  $form_data['extra'] = unserialize(PA::$network_info->extra);
  if(!empty($imported_defaults)) {
    $form_data['extra']['relationship_options'] = $imported_defaults['relationship_options'];
    $form_data['extra']['relationship_show_mode'] = $imported_defaults['relationship_show_mode'];
  }  
}

$action = (isset($_POST['config_action'])) ? $_POST['config_action'] : null;
if (($action == 'save' || $action == 'store_as_defaults') && !$error && !$imported_defaults) {//if data is posted
  filter_all_post($_POST);//filters all data of html
  $error  = FALSE;
  $msg = 'Network could not be saved due to following errors:<br>';
  foreach ( $form_data['extra']['relationship_options'] as $key => $value ) {
    if ( trim($_POST[$key]) == '' ) {
      $error = TRUE;
      $msg .= $value['caption'].' cant be empty<br>';
    } else {
      $form_data['extra']['relationship_options'][$key]['value'] = $_POST[$key];
    }
  }
  
  // added by: Z.Hron 
  // implements feature #0011955: 
  //                    Relationship Settings - add 'One Kind of Friend' admin option
  $form_data['extra']['relationship_show_mode']['value'] = $_POST['relationship_show_mode'];
  if($_POST['relationship_show_mode'] == '1') {
    if(!empty($_POST['relationship_term'])) {
      $form_data['extra']['relationship_show_mode']['term'] = $_POST['relationship_term'];
    } else {
      $error = TRUE;
      $msg .= 'Relationship term cant be empty<br>';
    }
  }

  // save
  if ( !$error ) {
/*  
    $network = new Network;
    $extra =  $form_data['extra'];
    $data = array(
        'network_id'=>PA::$network_info->network_id,
          'extra'=>serialize($extra),
          'changed'=>time()
        );
    $network->set_params($data);
*/
    $network = PA::$network_info;
    $extra = $form_data['extra'];
    $data = array(
        'extra'=>serialize($extra),
        'changed'=>time()
    );
    $network->set_params($data);
    
    try{
      $nid = $network->save();
      $msg = MessagesHandler::get_message(7012);
      if(!empty($_REQUEST['config_action']) && ($_REQUEST['config_action'] == 'store_as_defaults')) {
        $export_config = new NetworkConfig();
        $export_config->buildNetworkSettings($network);
        $export_config->storeSettingsLocal();
        $msg = 'Network default configuration file "' . $export_config->settings_file . '" successfully updated.';
      }
    } catch (PAException $e) {
      $error = TRUE;
      $msg = "$e->message";
    }
  }
}

function setup_module($column, $module, $obj) {
  global $form_data, $error, $configure_permission, $msg;
  if(!$configure_permission) return 'skip';
  $obj->tpl_to_load = "relation_settings";
  $obj->control_type = "relations";
  $obj->form_data = $form_data;
  $obj->error = $error;
  $obj->error_msg = $msg;
}

$page = new PageRenderer("setup_module", PAGE_RELATIONSHIP_SETTINGS, "Relationship Settings", 'container_two_column.tpl','header.tpl',PRI,HOMEPAGE, PA::$network_info);
if (empty($msg)) {
  $msg = @$succ_msg; // FIXME: where does $succ_msg come from I wonder?
  // if setting has been done successfully.  
}  
if (!empty($msg)) {
  $msg_tpl = new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg);
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}
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

echo $page->render();
?>