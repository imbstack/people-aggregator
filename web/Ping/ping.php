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
  
  require_once dirname(__FILE__)."/../../config.inc";
  require_once "db/Dal/Dal.php";
  require_once "api/PingServer/PingServer.php";
  
  if($_POST) {
      $PingServer = new PingServer();
      $PingServer->set_params($_POST);
      $PingServer->save ();
  }
?>