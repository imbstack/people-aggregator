<?php

/**
 * @class SimpleWebClient
 *
 * The SimpleWebClient class provides simple 
 * methods for Web connection and data streaming.
 * 
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 *
 */

require_once("HTTP/Client.php");
require_once "web/includes/classes/BaseClient.class.php";

class SimpleWebClient extends BaseClient {

  private $error;
	private $agent;
  private $url;
  private $response_data;
 
	public function __construct($url) {
    $this->error = null;
    $this->url = $url;
    $this->response_data = null;
    $this->agent = new HTTP_Client();
	}
  

	public function connect() {
		return true;
	}

	public function disconnect() {
  
	}


	/**
   * Send WEB request
   * 
   * 
   *   @param $data :      output data string
   *
   * @return               nothing
   *
   */
	public function send($data, $encoded = true) {
    $code = $this->agent->post($this->url, $data, $encoded);
    if (PEAR::isError($code)) {
      $this->error = $code->getMessage();
      return false;
    } else {
      $responseArray = $this->agent->currentResponse();
      $this->response_data  = $responseArray['body'];
    }
    return $responseArray['code'];
	}

	/**
   * Get response data
   * 
   * 
   * @return       (\p string)    received data
   *
   */
	public function getResponse() {
		return $this->response_data;
	}


	public function getError() {
		return $this->error;
	}

}


?>
