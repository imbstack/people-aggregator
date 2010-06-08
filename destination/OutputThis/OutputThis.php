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
 * Class for routing content.
 *
 * @package OutputThis
 * @author Tekriti Software (http://www.tekritisoftware.com)
 */

require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "web/api/lib/ixr_xmlrpc.php";


class OutputThis {

  /**
  * The default constructor for OutputThis class.
  */
  public function __construct() {
    return;
  }
  
  /**
  * send a content to a destination.
  *
  * @param array content details.
  * @param array ids of destinations.
  * @param string username of outputthis.
  * @param string password of outputthis.
  */
  static function send($blog_post, $destination_ids, $username, $password) {
    
    Logger::log("Enter: function OutputThis::send()");
    $client = new IXR_Client('http://outputthis.org/xmlrpc.php');
    $params = array('title' => $blog_post['title'], 'description' => $blog_post['body']);
    $i = 0;
    for ($i=0;$i<count($destination_ids);$i++) {
      $id = $destination_ids[$i];
      $requests[$i] = array('ID'=>$id, 'status' => 'publish');
    }
    if (!$client->query('outputthis.publishPost', $username, $password, $requests, $params)) {
      $error[0] = false;
      $error[1] = 'Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage();   
      Logger::log("Exit: function OutputThis::send()-something went wrong");
      return $error;
    } else {
     Logger::log("Exit: function OutputThis::send() - success true");
     return true;
    }
  } 
  
  /**
  * sends targets from OutputThis.
  *
  * @param integer id of destination.
  */
  static function get_targets ($username,$password) {
    Logger::log("Enter: function OutputThis::get_targets()");
    $client = new IXR_Client('http://outputthis.org/xmlrpc.php');
    if (!$client->query('outputthis.getPublishedTargets', $username, $password)) {
      $error[0] = 'error';
      $error[1] = 'Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage();
      Logger::log("Exit: function OutputThis::get_targets()-Error in getting information from output this");  
      return $error;
    } else { 
      $targets = $client->getResponse();
      Logger::log("Exit: function OutputThis::get_targets()-getting information from outputthis successful");
      return $targets;
    }
  }
}
?>