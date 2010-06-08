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
require_once "api/Logger/Logger.php";
require_once "api/PAException/PAException.php";
require_once "web/includes/classes/NetworkConfig.class.php";
require_once "web/includes/classes/UrlHelper.class.php";
require_once "api/Messaging/MessageRenderer.class.php";
require_once "api/EmailMessages/EmailMessages.php";
require_once "PHPMailer/class.phpmailer.php";

/**
* @short Class MessageDispatcher - email and notification messages dispatcher
*
* @author Zoran Hron, March 2009.
*/
class MessageDispatcher {

  /**
  * @short notification meassage id
  * @access public
  * @var $message_type
  */
  protected $message_type;

  /**
  * @short requester object
  * @access public
  * @var $requester_obj
  */
  protected $requester_obj;

  /**
  * @short recipient object
  * @access public
  * @var $recipient_obj
  */
  protected $recipient_obj;

  /**
  * @short associated object
  * @access public
  * @var $associated_obj
  */
  protected $associated_obj;

  /**
  * @short PA configuration object
  * @access public
  * @var $config_obj
  */
  private $config_obj;

  /**
  * @short message object
  * @access public
  * @var $message_obj
  */
  public $message_obj;

  /**
  * @param string $msg_type - notification ID string
  * @param Object $recipient_obj - the recipient object, could be of the 'User', 'Goup' or 'Network' type
  * @param Object $requester_obj - the requester object, could be of the 'User', 'Goup' or 'Network' type
  * @param Object $assoc_obj - the associated object - type depends of the notification message type
  */
  public function __construct($msg_type, $recipient_obj, $requester_obj, $assoc_obj) {
    $this->message_type    = $msg_type;
    $this->requester_obj   = $requester_obj;
    $this->recipient_obj   = $recipient_obj;
    $this->associated_obj  = $assoc_obj;
    $this->config_obj = new NetworkConfig();
    $this->message_obj = $this->getMessage();
  }

  private function getMessage() {
    $retval = null;
    if (!empty($this->config_obj->settings['email_messages'][$this->message_type])) {
      $message_data = $this->config_obj->settings['email_messages'][$this->message_type];
      $message_data['type'] = $this->message_type;
      $msg_obj = new MessageRenderer($message_data, $this->recipient_obj, $this->requester_obj, $this->associated_obj);
      $retval = $msg_obj;
    } else {
      Logger::log("Error exit: function MessageDispatcher::getMessage() - Undefined message type: $this->message_type");
      throw new Exception("MessageDispatcher::getMessage() - Undefined message type: $this->message_type");
    }
    return $retval;
  }

  protected function sendEmail($recipient = null) {
    if (empty($recipient)) {
      $recipient = $this->message_obj->template_vars["%recipient.email_address%"];
    }
    $subject   = $this->message_obj->message['subject'];
    $message   = $this->message_obj->message['message'];
    $container_html  = $this->message_obj->message['template'];
    if ($container_html != 'text_only') {
      // patching up message and subject in the email container
      $email_container = & new Template(PA::$config_path . "/email_containers/$container_html");
      $email_container->set('subject', $subject);
      $email_container->set('message', $message);

      // actual message to be sent through the mail
      $body = $email_container->fetch();

      //making the url relative.
      $body = str_replace(PA::$url.'/images', 'images', $body);

      //making the user picture or the other such files path relative.
      $body = str_replace(PA::$url.'/files', 'files', $body);
    } else {
      $body = strip_tags(preg_replace("(<br>|<br/>|<br />)", "\n", $message));
    }

    $mail    = new PHPMailer();
    $mail->CharSet = "UTF-8";
    $body    = eregi_replace("[\]",'',$body);
    $subject = eregi_replace("[\]",'',$subject);
    $mail->From = $mail->FromName = PA::$default_sender;
    $mail->AddAddress($recipient, $recipient);
    $mail->Subject = $subject;

    if ($container_html != 'text_only') {
      $mail->AltBody = strip_tags($body);
      $mail->MsgHTML($body);
    } else {
      $mail->Body = $body;
    }

    if (!$mail->Send()) {
       throw new PAException(MAIL_FUNCTION_FAILED, "Mail is not sent due to PHPMailer error: ".$mail->ErrorInfo);
    } else {
       return TRUE;
    }
    return FALSE;
  }

  protected function sendNotification() {
    if (empty($this->message_obj->template_vars["%requester.user_id%"]) || empty($this->message_obj->template_vars["%recipient.login_name%"])) {
       Logger::log("Error exit: function MessageDispatcher::sendNotification() - Missing Recipient or Requester info.");
       throw new Exception("MessageDispatcher::sendNotification() - Missing Recipient or Requester info.");
    }
    $recipient_notif_settings = null;
    $destination = NET_NONE;                              // initial set to NONE
    $msg_waiting_blink = NET_NONE;                        // initial set to NONE
    $msg_categ = $this->message_obj->message['category'];

    $recipient_id = $this->message_obj->template_vars["%recipient.user_id%"];
    $recipient_profile = User::load_user_profile($recipient_id, $recipient_id, 'notifications');
    if (!empty($recipient_profile)) {
      $recipient_notif_settings = unserialize($recipient_profile[0]['value']);
    }

    if ($msg_categ == 'notify_network_owner') {            // get network notification settings and notify network owner
      $extra = unserialize(PA::$network_info->extra);
      if (isset($extra['notify_owner'][$this->message_type]['value'])) {
        $destination = $extra['notify_owner'][$this->message_type]['value'];
      } else {                      // temporrary solution - if no notification settings data defined for a message
        $destination = NET_BOTH;    // message category is 'notify_network_owner', so notify owner
      }
    }
    else if ($msg_categ == 'notify_group_owner') {
      $destination = NET_BOTH;                            
      // temporrary solution - currently there is no group notification settings
    }
    else {
      if (!empty($recipient_notif_settings) && isset($recipient_notif_settings[$this->message_type]['value'])) {
        $destination = $recipient_notif_settings[$this->message_type]['value'];
      }
    }

    if ($msg_categ == 'outgoing_email') {
      $destination = NET_EMAIL;              // override destination if message category is outgoing_email
    }

    if ($destination != NET_NONE) {
    	$email_recipient = $this->message_obj->template_vars["%recipient.email_address%"];
    	$requester = $this->message_obj->template_vars["%requester.user_id%"];
    	$recipient = $this->message_obj->template_vars["%recipient.login_name%"];
      switch($destination) {
        case NET_MSG:
          self::internalMessage($recipient, $requester);
					// only send message_waiting if the user isn't already recieving the message as email ^^
					if (isset($recipient_notif_settings['msg_waiting_blink']) && ($recipient_notif_settings['msg_waiting_blink'] == NET_EMAIL)) {
							$msg_waiting_blink = $recipient_notif_settings['msg_waiting_blink'];
					}

        break;
        case NET_EMAIL:
          self::sendEmail($email_recipient);
        break;
        case NET_BOTH:
          self::internalMessage($recipient, $requester);
          self::sendEmail($email_recipient);
        break;
      }
    }

    if ($msg_waiting_blink == NET_EMAIL) {
      $recipient_id = $this->message_obj->template_vars["%recipient.user_id%"];
      $recipient = new User();
      $recipient->load((int)$recipient_id);
      $requester = PA::$network_info;       // msg_waiting_blink message is sent by network owner
      PAMail::send("msg_waiting_blink", $recipient, $requester, array());
    }
  }

  private function internalMessage($recipient, $requester) {
    $subject   = $this->message_obj->message['subject'];
    $message   = $this->message_obj->message['message'];
    $container_html   = $this->message_obj->message['template'];
    if ($container_html != 'text_only') {
      // patching up message and subject in the email container
      $message = nl2br($message);
      $email_container = & new Template(PA::$config_path . "/email_containers/$container_html");
      $email_container ->set('subject', $subject);
      $email_container ->set('message', $message);
      // actual message to be sent through the mail
      $body = $email_container->fetch();

    } else {
      $body = $message;
    }
    Message::add_message($requester, null, $recipient, $subject, $body);
  }
}

class PAMail extends MessageDispatcher {

  public static function send($msg_type, $recipient_obj, $requester_obj, $assoc_obj)  {
    $msg_disp = new self($msg_type, $recipient_obj, $requester_obj, $assoc_obj);
    return $msg_disp->sendEmail();
  }
}

class PANotify extends MessageDispatcher {

  public static function send($msg_type, $recipient_obj, $requester_obj, $assoc_obj)  {
    $msg_disp = new self($msg_type, $recipient_obj, $requester_obj, $assoc_obj);
    $msg_disp->sendNotification();
  }
}
?>