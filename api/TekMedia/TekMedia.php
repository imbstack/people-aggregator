<?php
require_once 'api/Logger/Logger.php';
require_once 'web/api/lib/ixr_xmlrpc.php';
/**
  Class to handle operation with TekMedia server
*/
class TekMedia {
  public $access_key;
  public $secret_key;
  
  public $redirection_url;

  function __construct() {
    $this->access_key = PA::$video_accesskey; //Set your accessKey here.
    $this->secret_key = PA::$video_secretkey; //Set your secretKey here.
  }


   public function generate_form_key($gid = NULL) {
     $client = new IXR_Client(PA::$tekmedia_server);
     $params = array();
     global $domain_suffix;
     $params['accessKey'] = $this->access_key; //Set your accessKey here.
     $params['secretKey'] = $this->secret_key; //Set your secretKey here.
     $params['userid'] = $domain_suffix.'_'.PA::$network_info->network_id.'_'.PA::$login_uid; //Set your userid here.
     if (!$client->query('tekmClient.video.get_form_key', $params)) {
       Logger::log('tekmClient.video.generate_form : Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage(), LOGGER_WARNING);
       print_r();
     } 

     $response = $client->getResponse();
     if ($response['status']) {
        return $response['response']['form_key'];
     }
     return NULL;
   }

   /**
      This function generate a form for the video uplaoding
      
   */
   public function generate_form($gid = NULL) {
     if ($form_key = $this->generate_form_key($gid)) {
        $redirection_path = PA::$url.'/save_tekmedia.php';
        $get_form_url = 	PA::$url."/upload_video_form.php"; 
        // need this to live on a public access server!

        if(!empty($gid)) {
          $form_url = $get_form_url.'?gid='.$gid;
        } else {
          $form_url = $get_form_url.'?uid='.PA::$login_uid;
        }
        $query_string = 'form_key='.$form_key.'&redirection_path='.$redirection_path.'&get_form='.$form_url;
     }
     return $query_string;
   }
  
  /**
    Code for getting video from tekmedia
  */
  public function get_video($video_id) {
    
    $client = new IXR_Client(PA::$tekmedia_server);
    $params = array();
    $params['accessKey'] = $this->access_key; //Set your accessKey here.
    $params['secretKey'] = $this->secret_key; //Set your secretKey here.
    $params['video_id'] = $video_id; //Set video id here.
    if (!$client->query('tekmClient.video.get_details', $params)) {
       Logger::log('tekmClient.video.get_details : Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage(), LOGGER_WARNING);
    }  
    $response = $client->getResponse();
    if(empty($response['response']['videos'])) return;
    $tekmedia_video = $response['response']['videos'];
    return $tekmedia_video;
  }

  /**
    Set video_id for this function 
  */

  public function delete_video_from_server($video_id) {
    
    $client = new IXR_Client(PA::$tekmedia_server);
    $params = array();
    $params['accessKey'] = $this->access_key; //Set your accessKey here.
    $params['secretKey'] = $this->secret_key; //Set your secretKey here.
    $params['video_id'] = $video_id; //Set video id here.
    
    if (!$client->query('tekmClient.video.delete', $params)) {
       Logger::log('tekmClient.video.delete : Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage(), LOGGER_WARNING);
    }  
      
    $response = $client->getResponse();
    
    return true;

  }

}

?>