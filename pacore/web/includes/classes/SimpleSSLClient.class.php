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

/**
 * @class SimpleSSLClient
 *
 * The SimpleSSLClient class provides simple methods for SSL connection and data streaming.
 * 
 * 
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 *
 */

require_once "web/includes/classes/BaseClient.class.php";

class SimpleSSLClient extends BaseClient {

	private $connection;
	private $error_text;
  private $server;
  private $port;
  private $timeout;
  
	public function __construct($server, $port, $timeout) {
    $sslAvailable = false;
    $xportlist = stream_get_transports();
		
    if(in_array('ssl', $xportlist)) {
        $sslAvailable = true;
    } else if(in_array('ss', $xportlist)) {    // Debian linux fix: missing first letter in transports list
        $sslAvailable = true;
    }
    
    if(!$sslAvailable) {
        throw new Exception("Error: This server does not support SSL connections.");
    }

    $this->server  = $server;
    $this->port    = $port;
    $this->timeout = $timeout;
    $this->connection = false;

	}
  

	/**
   * Open the socket connection.
   * 
   * 
   * @return                (\p bool)      is success
   *
   */
	public function connect() {

		$this->connection = @fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
		if($this->connection === false) {
			$this->setError("Unable to contact \"$this->server\" server! Reason: $errstr");
			return false;
		}
		return true;
	}

	
  /**
   * Close the current socket connection.
   * 
   */
	public function disconnect() {
		fclose($this->connection);
	}


	/**
   * Output data to socket stream
   * 
   * 
   *   @param $data :     (\p string)    output data string
   *
   * @return               nothing
   *
   */
	public function send($data, $encoded = true) {
    try {
    fwrite($this->connection, $data);
    } catch (Exception $e) {
       $this->setError($e->getMessage());
       return false;
    }
    return true;
	}

	/**
   * Read the socket stream incoming data
   * 
   * 
   *   @param $length :     (\p int)       data length
   *
   * if success:
   *   
   * @return                (\p string)    received data
   * else:
   * @return                (\p bool)      false
   * 
   */
	public function getResponse($length = 8192){
    $data = '';
    while($this->waiting()){
      $data .= $this->get($length);
    }
		return $data;
	}

  private function get($length = NULL) {
    if($length) {
      $data = fread($this->connection, $length);
    } else {
      $data = fgets($this->connection,4096);
    }
    return $data;
  }
  
  private function waiting($timeout = 1) {
    $R = array($this->connection);
    $W = array();
    $X = array();
    $sel = @stream_select($R,$W,$X,intval($timeout),intval(($timeout*1000000)%1000000));
    if($sel === false) return false;
    return (($sel > 0) && !feof($this->connection));
  }

	public function getError() {
		return $this->error_text;
	}

	public function setError($text) {
		$this->error_text = $text;
	}
  
}


?>
