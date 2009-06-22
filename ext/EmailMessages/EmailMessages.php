<?php
// require_once "web/config/default_email_messages.php";

require_once "web/includes/classes/PaConfiguration.class.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";

/**
* Class EmailMessagess to configure email messages
*
* @package EmailMessagess
* @author Zoran Hron, Jan. 2009.
*/
class EmailMessages {

  public static $network_template_vars = array (
     "%network.owner.first_name%",
     "%network.owner.last_name%",
     "%network.owner.login_name%",
     "%network.owner.display_name%",
     "%network.owner.profile_url%",
     "%network.owner.profile_link%",
     "%network.owner.image%",
     "%network.owner.email_address%",
     "%network.icon_image%",
     "%network.name%",
     "%network.description%",
     "%network.member_count%",
     "%network.join_url%",
     "%network.join_link%",
     "%network.url%",
     "%network.link%",
     "%network.member_moderation_url%",
     "%network.member_moderation_link%",
     "%network.reci_relation_count%",
     "%config_site_name%"
  );

  public static $group_template_vars = array (
     "%group.owner.first_name%",
     "%group.owner.last_name%",
     "%group.owner.login_name%",
     "%group.owner.display_name%",
     "%group.owner.profile_url%",
     "%group.owner.profile_link%",
     "%group.owner.image%",
     "%group.owner.email_address%",
     "%group.icon_image%",
     "%group.name%",
     "%group.description%",
     "%group.member_count%",
     "%group.join_url%",
     "%group.join_link%",
     "%group.url%",
     "%group.link%",
     "%group.moderation_url%",
     "%group.moderation_link%"
  );

  public static $recipient_requester_template_vars = array (
     "%recipient.first_name%",
     "%recipient.last_name%",
     "%recipient.login_name%",
     "%recipient.display_name%",
     "%recipient.profile_url%",
     "%recipient.profile_link%",
     "%recipient.image%",
     "%recipient.email_address%",
     "%recipient.messages_link%",
     "%requester.first_name%",
     "%requester.last_name%",
     "%requester.login_name%",
     "%requester.display_name%",
     "%requester.profile_url%",
     "%requester.profile_link%",
     "%requester.image%",
     "%requester.email_address%"
  );

  /**
  * id of email.
  * @access public
  * @var int
  */
  public $id;

  /**
  * type of email like 'invite_pa'
  * @access public
  * @var string
  */
  public $type;

  /**
  * description of email type like 'Invite into PeopleAggregator'
  * @access public
  * @var string
  */
  public $description;

  /**
  * category of email type like 'outgoing_email'
  * @access public
  * @var string
  */
  public $category;

  /**
  * email container template
  * @access public
  * @var string
  */
  public $template;

  /**
  * subject of the email
  * @access public
  * @var string
  */
  public $subject;

  /**
  * message of the email
  * @access public
  * @var string
  */
  public $message;

  /**
  * configurable variables of the email: variable that can be changed from email to email
  * @access public
  * @var string
  */
  public $configurable_variables;

  private $config_obj;

  /**
  * The default constructor for EmailMessagess class.
  */
  public function __construct() {
    $this->config_obj = new PaConfiguration();
  }
 /**
 * This function updates subject and message
 */
  public function update() {
    Logger::log("Enter: function EmailMessages::update");
    if(empty($this->type)) {
      Logger::log("Error Exit: function EmailMessages::update");
      throw new Exception("EmailMessages::update() property 'type' is undefined!");
    }
    $this->load($this->type);
    $this->config_obj->settings['email_messages'][$this->type] = array('subject'                => $this->subject,
                                                                       'message'                => $this->message,
                                                                       'category'               => $this->category,
                                                                       'template'               => $this->template,
                                                                       'description'            => $this->description,
                                                                       'configurable_variables' => array_flip($this->configurable_variables));
    $this->config_obj->storeSettingsLocal();

    Logger::log("Exit: function EmailMessages::update");
  }


  public function load($type) {
    $e_msg = EmailMessages::get($type);
    if(empty($this->subject)) $this->subject = $e_msg['subject'];
    if(empty($this->message)) $this->message = $e_msg['message'];
    if(empty($this->category)) $this->message = $e_msg['category'];
    if(empty($this->template)) $this->message = $e_msg['template'];
    if(empty($this->description)) $this->description = $e_msg['description'];
    if(empty($this->configurable_variables)) $this->configurable_variables = $e_msg['configurable_variables'];
  }

 /**
 * This function insert a new message
 */
  public function insert() {
    Logger::log("Enter: function EmailMessages::insert");
    Logger::log("Exit: function EmailMessages::insert");
  }


  //This function will return subject and message for the supplied email $type
  public static function get($type, $array_of_data = NULL) {
    Logger::log("Enter: function EmailMessages::get");
    $return = null;
    $config_obj = new PaConfiguration();
    $e_message = (!empty($config_obj->settings['email_messages'][$type])) ? $config_obj->settings['email_messages'][$type] : null;
    if (!empty($e_message)) {
      $subject = $e_message['subject'];
      $message = $e_message['message'];
      $category = $e_message['category'];
      $template = $e_message['template'];
      $description = $e_message['description'];
      $configurable_variables = array_flip($e_message['configurable_variables']);
      if ($array_of_data != NULL) {
        $config_vars = $configurable_variables;
        if (!empty($config_vars)) {
          foreach ($config_vars as $key => $value) {
        	  // FIXME: $array_of_data[$value] is not valid in some cases
            $subject = str_replace($key, @$array_of_data[$value], $subject);
            $message = str_replace($key, @$array_of_data[$value], $message);
          }
        }
      }
      $return = array('subject'               => $subject,
                      'type'                  => $type,
                      'category'              => $category,
                      'template'              => $template,
                      'description'           => $description,
                      'message'               => $message,
                      'configurable_variables'=> $configurable_variables);
    }
    Logger::log("Exit: function EmailMessages::get");
    return $return;
  }

  //This function will return all messages as associative array
  public static function get_all_messages($list_only = false) {
    $email_msgs = array();
    $result = array();
    Logger::log("Enter: function EmailMessages::get_all_messages");
    $config_obj = new PaConfiguration();
    if(!empty($config_obj->settings['email_messages'])) {
      foreach($config_obj->settings['email_messages'] as $type => $data ) {
        if($list_only) {
          $result[] = (object)array('type' => $type, 'description' => $data['description']);
        }
        else {
          $result[] = array(
                            'type' => $type,
                            'description' => $data['description'],
                            'subject'  => $data['subject'],
                            'message'  => $data['message'],
                            'category' => $data['category'],
                            'template' => $data['template'],
                            'configurable_variables' => array_flip($data['configurable_variables'])
                     );
        }
      }
      return $result;
    }
    return false;
  }

  //This function will be used to populate select box with available email types  for the admin screen
  public function get_email_list() {
    Logger::log("Enter: function EmailMessages::get_email_list");
    $email_list = self::get_all_messages(true);
    Logger::log("Exit: function EmailMessages::get_email_list");
    return $email_list;
  }

  public function saveToFile() {
    global $email_messages;
    Logger::log("Enter: function EmailMessages::saveToFile");
    $file_path = PA::$project_dir . "/web/config/email_msg_text";

      foreach ($email_messages as $type_id=>$data) {
        if($data['type'] == $this->type) {
          $message_file = $data['message'];
          if(!is_writable($file_path)) {
            if(!chmod($file_path, 0777)) {
              throw new Exception("EmailMessages::saveToFile() - Can't change permissions - Directory \"".$file_path."\" should be writtable.");
            }
          }
          $EmailMessageFile = "$file_path/$message_file";
          $fh = fopen($EmailMessageFile, 'w+');
          $res = fwrite($fh, $this->message);
          fclose($fh);
          if($res === false) {
            throw new Exception("EmailMessages::saveToFile() - File '$EmailMessageFile'. is not writtable. Check permissions.");
          }
          break;
        }
      }
      Logger::log("Exit: function EmailMessages::saveToFile");
  }

}
?>
