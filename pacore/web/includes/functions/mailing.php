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
require_once "web/includes/email_msg/english.php";
require_once "api/PAException/PAException.php";
require_once "web/includes/image_resize.php";
require_once "api/EmailMessages/EmailMessages.php";
include_once "PHPMailer/class.phpmailer.php";

global $default_sender, $mail_testing_callback;
define("DEFAULT_SENDER", $default_sender);
$mail_testing_callback = NULL; // see pa_mail() for usage

  function pa_mail($to, $type, $array_of_data, $from = DEFAULT_SENDER) {
    global $default_sender;
    $container_html = 'default_email_container.tpl';
    if (empty($from)) {
      $from = DEFAULT_SENDER;
    }
    // getting email data ie subject and message for the specified type $type
    // here $array_of_data will be containing actual data to be replaced in subject and message frame
    $email_data = EmailMessages::get($type, $array_of_data);
    $subject = @$email_data['subject']; 
    $message = @$email_data['message'];
    // patching up message and subject in the email container
    $email_container = & new Template('web/config/email_containers/'.$container_html);
    $email_container ->set('subject', $subject);
    $email_container ->set('message', $message);
    // actual message to be sent through the mail
    $body = $email_container->fetch();
    //making the url relative. 
    $body = str_replace(PA::$url.'/images', 'images', $body);
    //making the user picture or the other such files path relative.
    $body = str_replace(PA::$url.'/files', 'files', $body);
    $mail    = new PHPMailer();
    $body    = eregi_replace("[\]",'',$body);
    $subject = eregi_replace("[\]",'',$subject);
//    $mail->Sender = DEFAULT_SENDER;
    $mail->From = $mail->FromName = $default_sender;
    $mail->Subject = $subject;
    $mail->AltBody = strip_tags($body);
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $to);
// echo "<pre>".print_r($mail,1)."</pre>";
    if(!$mail->Send()) {
       // echo "<pre>".print_r($mail,1)."</pre>";exit;
       throw new PAException(MAIL_FUNCTION_FAILED, "Mail is not sent due to PHPMailer error: ".$mail->ErrorInfo);
     } else {
       return TRUE;
     }
  }
  
  /**
  * Function to send email where the container is specified but not the email template.
  * 
  */
  function simple_pa_mail($to, $subject, $message, $from = DEFAULT_SENDER, $container_html='default_email_container.tpl') {
    if (empty($from)) {
      $from = DEFAULT_SENDER;
    }    
    // patching up message and subject in the email container
    if (!empty($container_html)) {
      $email_container = & new Template('web/config/email_containers/'.$container_html);
      $email_container ->set('subject', $subject);
      $email_container ->set('message', $message);
      // actual message to be sent through the mail
      $message = $email_container->fetch();
    }
    $headers = "MIME-Version: 1.0\r\n".
   "Content-type: text/html; charset=iso-8859-1\r\n".
   "From: $from";
   
    // if you want to test the e-mail system, set global
    // $mail_testing_callback to point to your own function, that
    // behaves like mail().
    global $mail_testing_callback;
    $mail_func = $mail_testing_callback ? $mail_testing_callback : "mail";
    $check = call_user_func($mail_func, $to, $subject, $message, $headers);
    if($check == FALSE) {
       throw new PAException(MAIL_FUNCTION_FAILED, "Mail is not sent due to some internal server problem");
     }
     else {
       return TRUE;
     }
  }
?>
