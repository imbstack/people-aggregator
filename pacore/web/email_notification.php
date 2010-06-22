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
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
//including necessary files
include_once("web/includes/page.php");
include_once "api/ModuleSetting/ModuleSetting.php";
include_once "api/Theme/Template.php";
require_once "api/Category/Category.php";
require_once "web/includes/classes/file_uploader.php";
require_once "api/Validation/Validation.php";
require_once "api/Tag/Tag.php";
require_once "web/includes/network.inc.php";
require_once "web/includes/classes/NetworkConfig.class.php";
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
$msg = NULL;
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
                    $msg = __('Invalid configuration file format. Configuration file should be a valid XML file. ');
                }
                else {
                    // echo "<pre>".print_r($_REQUEST,1)."</pre>";
                    try {
                        $content           = file_get_contents($_FILES['local_file']['tmp_name']);
                        $imported_config   = new NetworkConfig($content);
                        $imported_defaults = $imported_config->getNotificationsSettings();
                        // echo "<pre>".print_r($imported_defaults,1)."</pre>";
                        $msg = __("File ").$_FILES['local_file']['name'].__(" loaded successfully.")."<br />".__("Click \"Save\" button to save new settings.");
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
                $imported_defaults = $imported_config->getNotificationsSettings();
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
    $form_data['extra'] = unserialize(PA::$network_info->extra);
    // initialize settings for new notification options
    if(!isset($form_data['extra']['notify_owner']['group_settings_updated'])) {
        $form_data['extra']['notify_owner']['group_settings_updated'] = PA::$network_defaults['notify_owner']['group_settings_updated'];
    }
    if(!isset($form_data['extra']['notify_owner']['content_modified'])) {
        $form_data['extra']['notify_owner']['content_modified'] = PA::$network_defaults['notify_owner']['content_modified'];
    }
    if(!empty($imported_defaults)) {
        $form_data['extra']['msg_waiting_blink'] = $imported_defaults['msg_waiting_blink'];
        $form_data['extra']['notify_owner']      = $imported_defaults['notify_owner'];
        $form_data['extra']['notify_members']    = $imported_defaults['notify_members'];
    }
}
$action = (isset($_POST['config_action'])) ? $_POST['config_action'] : null;
if(($action == 'save' || $action == 'store_as_defaults' || $action == 'update_user_defaults') && !$error && !$imported_defaults) {
    //if data is posted
    $notify_owner = PA::$network_defaults['notify_owner'];
    $notify_members = PA::$network_defaults['notify_members'];
    foreach($notify_owner as $k => $v) {
        $emailVal                                        = (empty($_POST[$k.'_email'])) ? 0 : 1;
        $msgVal                                          = (empty($_POST[$k.'_msg'])) ? 0 : 1;
        $s                                               = find_sum($emailVal, $msgVal);
        $form_data['extra']['notify_owner'][$k]['value'] = $s;
    }
    foreach($notify_members as $k => $v) {
        if($v['value'] <>-1) {
            $emailVal                                                  = (empty($_POST[$k.'_email'])) ? 0 : 1;
            $msgVal                                                    = (empty($_POST[$k.'_msg'])) ? 0 : 1;
            $settableVal                                               = (empty($_POST[$k.'_settable'])) ? 0 : 1;
            $s                                                         = find_sum($emailVal, $msgVal);
            $form_data['extra']['notify_members'][$k]['caption']       = $v['caption'];
            $form_data['extra']['notify_members'][$k]['value']         = $s;
            $form_data['extra']['notify_members'][$k]['user_settable'] = $settableVal;
        }
    }
    if(empty($_POST['msg_waiting_blink'])) {
        $form_data['extra']['msg_waiting_blink'] = NET_NO;
    }
    elseif($_POST['msg_waiting_blink'] == NET_YES) {
        $form_data['extra']['msg_waiting_blink'] = NET_YES;
    }
    $network = PA::$network_info;
    $extra = $form_data['extra'];
    $data = array(
        'extra' => serialize($extra),
        'changed' => time(),
    );
    $network->set_params($data);
    try {
        $nid = $network->save();
        $error_msg = 7011;
        if(!empty($_REQUEST['config_action']) && ($_REQUEST['config_action'] == 'store_as_defaults')) {
            $export_config = new NetworkConfig();
            $export_config->buildNetworkSettings($network);
            $export_config->storeSettingsLocal();
            $error_msg = 'Network default configuration file "'.$export_config->settings_file.'" successfully updated.';
        }
    }
    catch(PAException$e) {
        $error = TRUE;
        $error_msg = "$e->message";
    }
    if($action == 'update_user_defaults') {
        $users     = array();
        $users_ids = array();
        $users     = Network::get_members(array('network_id' => PA::$network_info->network_id));
        if($users['total_users']) {
            for($i = 0; $i < $users['total_users']; $i++) {
                $users_ids[] = $users['users_data'][$i]['user_id'];
            }
        }
        $notify_memb_defaults                      = $form_data['extra']['notify_members'];
        $notify_memb_defaults['msg_waiting_blink'] = $form_data['extra']['msg_waiting_blink'];
        $notify_memb_defaults                      = serialize($notify_memb_defaults);
        $error_msg                                 = __("User default Notifications Settings successfully updated.");
        foreach($users_ids as $user_id) {
            try {
                $curr_user = new User();
                $curr_user->load((int) $user_id);
                $curr_user->set_profile_field('notifications', 'settings', $notify_memb_defaults);
                $curr_user->save();
            }
            catch(PAException$e) {
                $error = TRUE;
                $error_msg = "$e->message";
            }
        }
    }
}

function find_sum($v1, $v2) {
    if($v1 == 1 && $v2 == 1) {
        $r = NET_BOTH;
    }
    elseif($v1 == 0 && $v2 == 0) {
        $r = NET_NONE;
    }
    elseif($v1 == 1 && $v2 == 0) {
        $r = NET_EMAIL;
    }
    elseif($v1 == 0 && $v2 == 1) {
        $r = NET_MSG;
    }
    return $r;
}
$page = new PageRenderer("setup_module", PAGE_EMAIL_NOTIFICATION, "Email Notification", 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);
// if any message code is set then fetch that message
if(is_int($error_msg)) {
    $msg_obj = new MessagesHandler();
    $error_msg = $msg_obj->get_message((int) $error_msg);
}
else {
    // else text message is set
    $error_msg = (!empty($error_msg)) ? $error_msg : $msg;
}
if(!empty($error_msg)) {
    $msg_tpl = &new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
    $msg_tpl->set('message', $error_msg);
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

function setup_module($column, $module, $obj) {
    global $form_data, $error, $error_msg, $configure_permission;
    if(!$configure_permission) {
        return 'skip';
    }
    $obj->tpl_to_load  = "email_notification";
    $obj->control_type = "email_notification";
    $obj->form_data    = $form_data;
    $obj->error        = $error;
    $obj->error_msg    = $error_msg;
}
?>
