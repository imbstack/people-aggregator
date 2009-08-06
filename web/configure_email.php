<?php

$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.

include_once("web/includes/page.php");
require_once "ext/EmailMessages/EmailMessages.php";
require_once "web/includes/classes/NetworkConfig.class.php";
require_once "web/includes/classes/EmailMessagesConfig.class.php";
// require_once "web/config/default_email_messages.php";

$curr_user = (isset(PA::$login_uid)) ? PA::$login_uid : 0;
$authorization_required = TRUE;

// getting values from $_GET variable
$email_type = !empty($_GET['email_type']) ? $_GET['email_type'] : NULL;
//$msg = NULL;
$error     =  false;
$error_msg =  null;

  if(!empty($_REQUEST['error_msg'])) {
      $error = true;
      $error_msg = $_REQUEST['error_msg'];
  } else if(!empty($_REQUEST['msg'])) {
      $error_msg = $_REQUEST['msg'];
  } else if(!empty($_REQUEST['msg_id'])) {
      $error_msg = $_REQUEST['msg_id'];
  }


// strip off unnecesary tags from posted values
if ($email_type) {
  $email_data = EmailMessages::get($email_type);
  $subject  = $email_data['subject'];
  $message  = $email_data['message'];
  $category = $email_data['category'];
  $template = $email_data['template'];
  $description = $email_data['description'];
  $configurable_variables = $email_data['configurable_variables'];
}

$restore_settings = null;
if(!empty($_REQUEST['config_action'])) {
  switch($_REQUEST['config_action']) {
    case 'preview_email':
      filter_all_post($_POST);
      $subject = $_POST['subject'];
      $message = $_POST['email_message'];
      $container_html = $_POST['template'];
      if($container_html != 'text_only') {
        $email_container = & new Template(PA::$config_path . "/email_containers/$container_html");
        $email_container->set('subject', $subject);
        $email_container->set('message', $message);
        $preview_msg = $email_container->fetch();
      } else {
        $preview_msg = "<br /><div><h4>$subject</h4><br /><br />$message</div>";
      }
    break;
    case 'save_email':
      if(saveEmail(&$error_msg)) {
        header("location: configure_email.php?msg=13001&email_type=" . $email_type);
      }
    break;
    case 'load_email_messages':
      if(!empty($_FILES['local_file']['name']) && is_uploaded_file($_FILES['local_file']['tmp_name'])) {
        if($_FILES['local_file']['type'] != 'text/xml') {
          $error_msg = __('Invalid configuration file format. Configuration file should be a valid XML file. ');
        } else {
          try {
            $content = file_get_contents($_FILES['local_file']['tmp_name']);
            $imported_config = new NetworkConfig($content);
            $restore_settings = $imported_config->getEmailMessagesSettings();
            if(!empty($restore_settings)) {
              foreach($restore_settings as $type=>$message) {
                $email = new EmailMessages();
                $email->type = $type;
                $email->description = $message['description'];
                $email->category = $message['category'];
                $email->template = $message['template'];
                $email->subject = $message['subject'];
                $email->message = $message['message'];
                $email->configurable_variables = array_flip($message['configurable_variables']);
                $email->update();
              }
              $error_msg = __("Email Messages settings data successfully loaded from ") . $_FILES['local_file']['name'] . __(" file.");
            }
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
      if($email_type) {
        try {
          $imported_config = new NetworkConfig();
          $restore_settings = $imported_config->getDefaultMessages(false);
          if(!empty($restore_settings[$email_type]) && is_array($restore_settings[$email_type])) {
            $email_data = $restore_settings[$email_type];
            $subject = $email_data['subject'];
            $message = $email_data['message'];
            $category = $email_data['category'];
            $template = $email_data['template'];
            $description = $email_data['description'];
            $configurable_variables = array_flip($email_data['configurable_variables']);
                $email = new EmailMessages();
                $email->type = $email_type;
                $email->description = $description;
                $email->subject = $subject;
                $email->message = $message;
                $email->category = $category;
                $email->template = $template;
                $email->configurable_variables = $configurable_variables;
                $email->update();
            $error_msg = __('Email message settings successfully restored.');
          } else {
            $error_msg = __("Message type \"$email_type\" not found in default settings file.");
          }
        } catch (Exception $e) {
          $error = TRUE;
          $error_msg = $e->getMessage();
        }
      }
    break;
    case 'revert_all_messages':
        try {
          $current_config = new NetworkConfig();
          $restored_messages = $current_config->getDefaultMessages(false);
          $current_config->settings['email_messages'] = $restored_messages;
          $current_config->storeSettingsLocal();
          $error_msg = __('Email message settings successfully restored.');
        } catch (Exception $e) {
          $error = TRUE;
          $error_msg = $e->getMessage();
        }
    break;
    case 'store_as_defaults':
/*
         if(saveEmail(&$error_msg, true)) {
           $error_msg = 'Message template file successfully saved.';
         }
         header("location: configure_email.php?msg=".$error_msg."&email_type=" . $email_type);
*/
    break;
  }
}

function saveEmail(&$err_msg, $save_to_file = false) {
  global $email_type;
  $error = false;
  $res =  false;
  filter_all_post($_POST);
  $mandatory_fields = array('subject'=>'Caption', "description" => "Description", 'email_message'=>'Message');
  foreach($mandatory_fields as $key => $value) {
    if(empty($_POST[$key])) {
      $error = true;
      $err_msg[] = $value . ' can\'t be empty.';
    }
  }
  if(!$error) {
    $res = true;
    $err_msg = 13001;
    $email = new EmailMessages();
    $email->subject  = $_POST['subject'];
    $email->category = $_POST['category'];
    $email->template = $_POST['template'];
    $email->message = $_POST['email_message'];
    $email->description = $_POST['description'];
    $email->type = $email_type;
    $email->update();
    if($save_to_file) {
      try {
        $email->saveToFile();
      }
      catch(Exception $e) {
        $error = true;
        $err_msg= $e->getMessage();
        $res =  false;
      }
    }
  }
  return $res;
}

function setup_module($column, $module, $obj) {
  global $subject, $message, $description, $category, $template, $configurable_variables, $preview_msg;
  switch ($module) {
    case 'ConfigureEmailModule':
      $obj->subject  = $subject;
      $obj->message  = $message;
      $obj->category = $category;
      $obj->template = $template;
      $obj->description = $description;
      $obj->configurable_variables = $configurable_variables;
      $obj->preview_msg = $preview_msg;
    break;
  }
}

$page = new PageRenderer("setup_module", PAGE_CONFIGURE_EMAIL, __('Configure Email'), "container_two_column.tpl", "header.tpl", NULL, PRI, PA::$network_info);

uihelper_error_msg($error_msg);
uihelper_get_network_style($page);

$page->html_body_attributes ='class="no_second_tier network_config"';
echo $page->render();
?>