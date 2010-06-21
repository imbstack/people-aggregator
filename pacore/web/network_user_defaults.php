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

  //anonymous user can not view this page;
$login_required = TRUE;
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
//including necessary files
include_once("web/includes/page.php");
include_once "api/ModuleSetting/ModuleSetting.php";
include_once "api/Theme/Template.php";
require_once "web/includes/network.inc.php";
require_once "web/includes/classes/file_uploader.php";
require_once "web/includes/classes/NetworkConfig.class.php";
$parameter              = js_includes("all");
$error                  = FALSE;
$curr_user              = (isset(PA::$login_uid)) ? PA::$login_uid : 0;
$authorization_required = TRUE;
if($curr_user == 0) {
    $configure_permission = false;
}
else {
    $configure_permission = Roles::check_administration_permissions($curr_user);
}
$error = FALSE;
$error_msg = NULL;
if(!$configure_permission) {
    $error = TRUE;
    $error_msg = __("Sorry! you are not authorized to view content of this page");
}
$imported_defaults = null;
if(!empty($_REQUEST['config_action'])) {
    switch($_REQUEST['config_action']) {
        case 'load_general_settings':
            if(!empty($_FILES['local_file']['name']) && is_uploaded_file($_FILES['local_file']['tmp_name'])) {
                if($_FILES['local_file']['type'] != 'text/xml') {
                    $error_msg = __('Invalid configuration file format. Configuration file should be a valid XML file. ');
                }
                else {
                    //  echo "<pre>".print_r($_REQUEST,1)."</pre>";
                    try {
                        $content           = file_get_contents($_FILES['local_file']['tmp_name']);
                        $imported_config   = new NetworkConfig($content);
                        $imported_defaults = $imported_config->getUserDefaults();
                        $msg               = __("File ").$_FILES['local_file']['name'].__(" loaded successfully.")."<br />".__("Click \"Save\" button to save new settings.");
                    }
                    catch(Exception$e) {
                        $error = TRUE;
                        $error_msg = $e->getMessage();
                    }
                }
            }
            else {
                $msg = __('Please, select a valid XML configuration file.');
            }
            break;
        case 'restore_defaults':
            try {
                $imported_config   = new NetworkConfig();
                $imported_defaults = $imported_config->getUserDefaults();
                $msg               = __('Default settings sucessfully restored.')."<br />".__("Click \"Save\" button to save new settings.");
            }
            catch(Exception$e) {
                $error = TRUE;
                $error_msg = $e->getMessage();
            }
            break;
    }
}
if(PA::$network_info) {
    $db_extra = unserialize(PA::$network_info->extra);
    $form_data['extra'] = $db_extra;
    if(!empty($imported_defaults)) {
        $form_data['extra']['user_defaults'] = $imported_defaults;
    }
}
$action = (isset($_POST['config_action'])) ? $_POST['config_action'] : null;
if(($action == 'save' || $action == 'store_as_defaults') && !$error && !$imported_defaults) {
    //if data is posted
    filter_all_post($_POST);
    //filters all data of html
    if((isset($_POST['relate_me'])) && ($_POST['relate_me'] == 1)) {
        //if add me as a friend check box is checked
        $form_data['extra']['user_defaults']['user_friends'] = $_SESSION['user']['name'];
    }
    if(isset($_POST['user_friends']) && ($_POST['user_friends'] != '')) {
        //check whether supplied login names exist
        $not_found  = array();
        $found      = array();
        $found_name = array();
        $user_names = explode(",", $_POST['user_friends']);
        foreach($user_names as $name) {
            $n = trim($name);
            if(!empty($n)) {
                if(!in_array($n, $found_name)) {
                    //check for repeated login names
                    try {
                        $related_user_id_array = User::map_logins_to_ids($n);
                        //getting login_name=>user_id array
                        foreach($related_user_id_array as $key => $values) {
                            $related_user_id = $values;
                            //getting user_id
                        }
                        if(Network::member_exists((int) PA::$network_info->network_id, (int) $related_user_id)) {
                            //if user_id exist for this network
                            $found[] = (int) $related_user_id;
                            $found_name[] = $n;
                        }
                        else {
                            $not_found[] = $n;
                        }
                    }
                    catch(PAException$e) {
                        $not_found[] = $n;
                    }
                }
            }
        }
        if(sizeof($found)) {
            $related = implode(",", $found_name);
        }
        if(!empty($not_found)) {
            $no_such_user = implode(", ", $not_found);
            $ack_message = "<br>".__("Following relations could not be added as default relation ")."<br>";
            for($i = 0; $i < count($not_found); $i++) {
                $ack_message .= "$not_found[$i]<br>";
            }
        }
        if(isset($_POST['relate_me']) && ($_POST['relate_me'] == 1)) {
            //and some names are specified for relationships
            $comma_separated = $_SESSION['user']['name'];
            if(!empty($related)) {
                $comma_separated .= ','.$related;
            }
            $form_data['extra']['user_defaults']['user_friends'] = $comma_separated;
        }
        else {
            $form_data['extra']['user_defaults']['user_friends'] = $related;
        }
    }
    //.. end of  $_POST['user_friends']
    // if nothing is supplied
    if(empty($_POST['relate_me']) && empty($_POST['user_friends'])) {
        $form_data['extra']['user_defaults']['user_friends'] = '';
    }
    //user's default desktop image start
    if(!empty($_FILES['desktop_image']['name'])) {
        $myUploadobj = new FileUploader;
        //creating instance of file.
        $file = $myUploadobj->upload_file(PA::$upload_path, 'desktop_image', true, true, 'image');
        if(!$file) {
            $error_msg = $myUploadobj->error;
            $error = TRUE;
        }
        else {
            $form_data['extra']['user_defaults']['desktop_image']['name'] = $file;
        }
    }
    //image options stretch, crop or tile
    $form_data['extra']['user_defaults']['desktop_image']['option'] = ($_POST['header_image_option']) ? ($_POST['header_image_option']) : DESKTOP_IMAGE_ACTION_STRETCH;
    $form_data['extra']['user_defaults']['desktop_image']['display'] = $_POST['desktop_image_display'];
    //image album
    if(isset($_POST['multiple_images'])) {
        $image_albums[] = $_POST['multiple_images'];
        foreach($image_albums as $image) {
            $comma_separated_image = implode(',', $image);
        }
        $form_data['extra']['user_defaults']['default_image_gallery'] = $comma_separated_image;
    }
    else {
        $form_data['extra']['user_defaults']['default_image_gallery'] = '';
    }
    //audio album
    if(isset($_POST['multiple_audios'])) {
        $audio_albums[] = $_POST['multiple_audios'];
        foreach($audio_albums as $audio) {
            $comma_separated_audio = implode(',', $audio);
        }
        $form_data['extra']['user_defaults']['default_audio_gallery'] = $comma_separated_audio;
    }
    else {
        $form_data['extra']['user_defaults']['default_audio_gallery'] = '';
    }
    //video album
    if(isset($_POST['multiple_videos'])) {
        $video_albums[] = $_POST['multiple_videos'];
        foreach($video_albums as $video) {
            $comma_separated_video = implode(',', $video);
        }
        $form_data['extra']['user_defaults']['default_video_gallery'] = $comma_separated_video;
    }
    else {
        $form_data['extra']['user_defaults']['default_video_gallery'] = '';
    }
    // if default blog is set
    if(isset($_POST['default_blog'])) {
        $form_data['extra']['user_defaults']['default_blog'] = (int) $_POST['default_blog'];
    }
    // end if default blog
    //now save
    $network = PA::$network_info;
    $extra = $form_data['extra'];
    $data = array(
        'extra' => serialize($extra),
        'changed' => time(),
    );
    $network->set_params($data);

    /*
      $network = new Network;
      $extra = $form_data['extra'];
      $data = array(
          'network_id'=>PA::$network_info->network_id,
            'extra'=>serialize($extra),
            'changed'=>time()
          );
      $network->set_params($data);
    */
    $msg = "";
    try {
        $nid = $network->save();
        if(sizeof($nid)) {
            $msg = __("Default settings for the network has been saved");
            if(!empty($_REQUEST['config_action']) && ($_REQUEST['config_action'] == 'store_as_defaults')) {
                $export_config = new NetworkConfig();
                $export_config->buildNetworkSettings($network);
                $export_config->storeSettingsLocal();
                $msg = 'Network default configuration file "'.$export_config->settings_file.'" successfully updated.';
            }
            if(!empty($file)) {
                Storage::link($file, array("role" => "header"));
                // network header
            }
        }
    }
    catch(PAException$e) {
        $error = TRUE;
        $error_msg = "$e->message";
    }
}
//..end of $_POST
function setup_module($column, $module, $obj) {
    global $form_data, $ack_message, $configure_permission;
    if(!$configure_permission) {
        return 'skip';
    }
    $obj->tpl_to_load = "user_defaults";
    $obj->form_data   = $form_data;
    $obj->ack_message = $ack_message;
}
$page = new PageRenderer("setup_module", PAGE_NETWORK_USER_DEFAULTS, "Network User Defaults", 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);
$msg = (!empty($msg)) ? $msg : $error_msg;
if($msg != "") {
    $msg_tpl = &new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
    $msg_tpl->set('message', $msg);
    $m = $msg_tpl->fetch();
    $page->add_module("middle", "top", $m);
}
$page->html_body_attributes = 'class="no_second_tier network_config"';
$css_array = get_network_css();
if(is_array($css_array)) {
    foreach($css_array as $key => $value) {
        $page->add_header_css($value);
    }
}
$css_data = inline_css_style();
if(!empty($css_data['newcss']['value'])) {
    $css_data = '<style type="text/css">'.$css_data['newcss']['value'].'</style>';
    $page->add_header_html($css_data);
}
echo $page->render();
?>