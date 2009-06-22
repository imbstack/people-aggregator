<?php
  
  require_once dirname(__FILE__)."/../../config.inc";
  require_once "db/Dal/Dal.php";
  require_once "ext/PingServer/PingServer.php";
  
  if($_POST) {
      $PingServer = new PingServer();
      $PingServer->set_params($_POST);
      $PingServer->save ();
  }
?>