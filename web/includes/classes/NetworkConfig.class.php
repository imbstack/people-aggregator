<?php
require_once "api/Tasks/Tasks.php";
require_once "api/Roles/Roles.php";
require_once "web/includes/classes/EmailMessagesConfig.class.php";

class NetworkConfig {
  public $error;
  private $var_names = array(
    'network_id',
    'category_id',
    'stop_after_limit',
    'maximum_members',
    'name',
    'address',
    'tagline',
    'description',
    'header_image',
    'inner_logo_image',
    'network_alt_text',
    'is_active',
    'created',
    'changed',
    'type',
    'extra' => array('basic',
                     'notify_owner',
                     'notify_members',
                     'user_defaults',
                     'relationship_options',
                     'relationship_show_mode',
                     'msg_waiting_blink',
                     'email_validation',
                     'network_group_title',
                     'network_feature',
                     'reciprocated_relationship',
                     'top_navigation_bar',
                     'network_content_moderation',
                     'network_skin',
                     'network_style',
                     'network_json',
                     'theme'
               ),
    'email_messages',
    'roles_tasks'
  );

  public function getGeneralNetworkSettings() {
    $this->error = null;
    $required = array(
        'name',
        'tagline',
        'category_id',
        'description',
        'extra' => array('network_group_title')
    );

    $general_settings = array(
        'name',
        'tagline',
        'category_id',
        'description',
        'inner_logo_image',
        'extra' => array ('basic',
                          'network_group_title',
                          'reciprocated_relationship',
                          'email_validation',
                          'captcha_required',
                          'show_people_with_photo',
                          'top_navigation_bar',
                          'network_content_moderation',
                          'language_bar_enabled',
                          'default_language'
                   )
    );

    $this->checkRequiredParams($required);
    if(!empty($this->error)) {
        throw new Exception("Invalid configuration file. <br />" . $this->error);
    }
    return $this->getSettingsData($general_settings, $this->settings);
  }

  public function getUserDefaults() {
    $user_defaults = array(
        'user_defaults' => array('desktop_image',
                                 'default_image_gallery',
                                 'default_audio_gallery',
                                 'default_video_gallery',
                                 'default_blog',
                                 'user_friends'
                           )
    );
    $res = $this->getSettingsData($user_defaults, $this->settings['extra']);
    return $res['user_defaults'];
  }


  public function getRelationShipSettings() {
    $relationship_options = array(
        'relationship_show_mode',
        'relationship_options' => array('closest_relation',
                                        'close_relation',
                                        'relation',
                                        'distant_relation',
                                        'most_distant_relation'
                                  )
    );
    $this->checkRequiredParams($relationship_options);
    $res = $this->getSettingsData($relationship_options, $this->settings['extra']);
    return $res;
  }

  public function getNotificationsSettings() {
    $notify_options = array(
       'msg_waiting_blink',
       'notify_owner' => array_keys(PA::$extra['notify_owner']),
/*
                         array('some_joins_a_network',
                               'content_posted',
                               'group_created',
                               'group_settings_updated',
                               'media_uploaded',
                               'relation_added',
                               'content_to_homepage',
                               'report_abuse_on_content',
                               'content_modified'
                         ),
*/
        'notify_members' => array_keys(PA::$extra['notify_members'])
/*
                            array('invitation_accept',
                                  'relationship_created_with_other_member',
                                  'someone_join_their_group',
                                  'friend_request_sent',
                                  'friend_request_denial',
                                  'bulletin_sent',
                                  'welcome_message',
                                  'msg_waiting_blink'
                            )
*/
    );
    $this->checkRequiredParams($notify_options);
    $res = $this->getSettingsData($notify_options, $this->settings['extra']);
    return $res;
  }

  public function getRolesSettings() {
    return $this->settings['roles_tasks'];
  }

  public function getEmailMessagesSettings() {
    return $this->settings['email_messages'];
  }

  private function getSettingsData($var_names, $settings) {
    $settings_data = array();
    foreach($var_names as $key => $var_name) {
      if(is_numeric($key)) {
        if(key_exists($var_name, $settings)) {
          $settings_data[$var_name] = $settings[$var_name];
        }
      } else if(is_string($key) && is_array($var_name)) {
        foreach($var_name as $sub_var) {
          if(key_exists($sub_var, @$settings[$key])) {
            $settings_data[$key][$sub_var] = $settings[$key][$sub_var];
          }
        }
      }
    }
    return $settings_data;
  }

  private function checkRequiredParams($required_params = array()) {
    foreach($required_params as $key => $var_name) {
      if(is_numeric($key)) {
        if(!isset($this->settings[$var_name])) {
          $this->addError("Parameter \"$var_name\" is required!");
        }
      } else if(is_string($key) && is_array($var_name)) {
        foreach($var_name as $sub_var) {
          if(!isset($this->settings[$key][$sub_var])) {
            $this->addError("Parameter \"$sub_var\" is required!");
          }
        }
      }
    }
  }

  private function addError($msg) {
    $this->error .= $msg . "<br />";
  }

  public function __construct($content = null) {
    $this->error = null;
    $this->loaded = $this->loadSettingsLocal();
    $this->buildNetworkSettings($content);
    if(!$this->loaded) { // first time called - XML config file not exist yet
      $this->settings['email_messages'] = $this->getDefaultMessages(false);
      $this->settings['roles_tasks'] = $this->importRolesInfo();
      $this->storeSettingsLocal();
      $this->loaded = $this->loadSettingsLocal();
    }
  }

  public function parseSettingsData($vars_arr, $var_names) {
    foreach($vars_arr as $key => $value) {
      if(!in_array($key, $var_names) && !array_key_exists($key, $var_names)) {
        unset($vars_arr[$key]);
        continue;
      }
      if(array_key_exists($key, $var_names) && is_array($var_names[$key])) {
        $vars_arr[$key] = $this->parseSettingsData($vars_arr[$key], $var_names[$key]);
      }
    }
    return $vars_arr;
  }

  public function storeSettingsLocal() {

    $net_name = (!empty($this->settings['address'])) ? $this->settings['address'] : PA::$network_info->address;
    $file_path = DIRECTORY_SEPARATOR . 'networks' . DIRECTORY_SEPARATOR . $net_name;

    if(is_dir(PA::$project_dir . $file_path)) {
      if(!is_writable(PA::$project_dir . $file_path)) {
        if(!chmod(PA::$project_dir . $file_path, 0777)) {
          throw new PAException(NETWORK_DIRECTORY_PERMISSION_ERROR, "Can't change permissions - Directory \"".PA::$project_dir . $file_path."\" should be writtable.");
        }
      }
      $file_name = PA::$project_dir . $file_path . DIRECTORY_SEPARATOR . "$net_name.xml";
    } else if(is_dir(PA::$core_dir . $file_path)) {
        if(!is_writable(PA::$core_dir . $file_path)) {
          if(!chmod(PA::$core_dir . $file_path, 0777)) {
            throw new PAException(NETWORK_DIRECTORY_PERMISSION_ERROR, "Can't change permissions - Directory \"".PA::$core_dir . $file_path."\" should be writtable.");
          }
        }
        $file_name = PA::$core_dir . $file_path . DIRECTORY_SEPARATOR . "$net_name.xml";
    }
    if(file_exists($file_name)) {
      unlink($file_name);
    }
    foreach($this->settings['email_messages'] as &$message) {  // put message subject and body in CDATA section
      $message['subject'] = "<![CDATA[" . htmlspecialchars_decode($message['subject']) . "]]>";
      $message['message'] = "<![CDATA[" . htmlspecialchars_decode($message['message']) . "]]>";
    }

    $store = new XmlConfig($file_name);
    $store->loadFromArray($this->settings, $store->root_node);
    $store->saveToFile();
    $this->settings_file = $file_name;
  }

  public function loadSettingsLocal() {

    $net_name = PA::$network_info->address;
    $file_path = DIRECTORY_SEPARATOR . 'networks' . DIRECTORY_SEPARATOR . $net_name;

    if(is_dir(PA::$project_dir . $file_path)) {
      if(is_readable(PA::$project_dir . $file_path)) {
        $file_name = PA::$project_dir . $file_path . DIRECTORY_SEPARATOR . "$net_name.xml";
      } else {
        throw new PAException(NETWORK_DIRECTORY_PERMISSION_ERROR, "Can't read data - Directory \"".PA::$project_dir . $file_path."\" is not readable.");
      }
    } else if(is_dir(PA::$core_dir . $file_path)) {
        if(is_readable(PA::$core_dir . $file_path)) {
          $file_name = PA::$core_dir . $file_path . DIRECTORY_SEPARATOR . "$net_name.xml";
        } else {
          throw new PAException(NETWORK_DIRECTORY_PERMISSION_ERROR, "Can't read data - Directory \"".PA::$core_dir . $file_path."\" is not readable.");
        }
    }
    if(file_exists($file_name) && is_readable($file_name)) {
      $this->settings_file = $file_name;
      $store = new XmlConfig($file_name);
      $vars_arr = $store->asArray();

      $this->settings = $this->parseSettingsData($vars_arr, $this->var_names);
      return true;
    }
    return false;
  }

  public function buildNetworkSettings($content = null) {
    if(is_string($content)) {
      $this->loadSettingsFromXml($content);
    } else if(is_object($content)) {
       $vars_arr = get_object_vars($content);
       $vars_arr['extra'] = unserialize($vars_arr['extra']);

       $vars_arr['email_messages'] = $this->settings['email_messages'];
       $vars_arr['roles_tasks'] = $this->settings['roles_tasks'];

       $this->settings = $this->parseSettingsData($vars_arr, $this->var_names);
    } else {
       if(!$this->loaded) {
          $vars_arr = get_object_vars(PA::$network_info);
          $vars_arr['extra'] = unserialize($vars_arr['extra']);
          $vars_arr['email_messages'] = $this->getDefaultMessages(false);
          $vars_arr['roles_tasks'] = $this->importRolesInfo();
          $this->settings = $this->parseSettingsData($vars_arr, $this->var_names);
       }
    }
  }

  private function loadSettingsFromXml($xml_string) {
      $store = new XmlConfig();
      $res = @$store->loadXML($xml_string);
      if(!$res) {
        throw new Exception("Invalid XML configuration file.");
      }
      $vars_arr = $store->asArray();
      $this->settings = $this->parseSettingsData($vars_arr, $this->var_names);
      $this->loaded = true;
  }

  public function getDefaultMessages($cdata = false) {
    $EmailMessageFile = PA::resolveRelativePath("web/config/email_messages.xml");
    if(!$EmailMessageFile) {
      throw new Exception("NetworkConfig::getDefaultMessages() - Message template file: 'web/config/email_messages.xml' missing!");
    }
    $emails = new EmailMessagesConfig($EmailMessageFile);

    return ($cdata) ? $emails->asArray() : $emails->messages;
  }

  public function importEmailMessagesOld() {
    global $email_messages;
    $e_messages = array();

      foreach ($email_messages as $type_id=>$data) {
        $msg_type = $data['type'];
        $description = $data['description'];
        $subject = $data['subject'];
        $message_file = $data['message'];
        $EmailMessageFile = PA::resolveRelativePath("web/config/email_msg_text/$message_file");
        if(!$EmailMessageFile) {
          throw new Exception("NetworkConfig::importEmailMessagesOld() - Message template file: " . "'web/config/email_msg_text/$message_file' missing!");
        }

        $fh = fopen($EmailMessageFile, 'r');
        if(!is_resource($fh)) {
          throw new Exception("NetworkConfig::importEmailMessagesOld() - Unable to read message template file: '$EmailMessageFile'!");
        }
        $message_body = null;
        if(filesize($EmailMessageFile)) {
          $message_body = fread($fh, filesize($EmailMessageFile));
          fclose($fh);
        }
        $configurable_variables = serialize($data['configurable_variables']);
        $e_messages[$msg_type] = array('subject'                => $subject,
                                       'message'                => $message_body,
                                       'description'            => $description,
                                       'configurable_variables' => $configurable_variables);
      }
//    echo "<pre>" . print_r($e_messages, 1) . "</pre>";
    return $e_messages;
  }

  public function importRolesInfo() {
    $roles = new Roles();
    $roles_data = array();
    $roles_info = $roles->get_multiple(null, DB_FETCHMODE_ASSOC);
    foreach($roles_info as &$role) {
      $roles_data[] = Roles::getRoleInfoByID($role['id'], DB_FETCHMODE_ASSOC);
    }
    return $roles_data;
  }

}
?>