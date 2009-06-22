<?php
  session_start();
  require_once dirname(__FILE__).'/../../config.inc';
  require_once "api/PAException/PAException.php";
  require_once "api/Relation/Relation.php";
  require_once "api/Logger/Logger.php";


  if ($_SESSION['user']['id']) {
    $uid = (int)$_SESSION['user']['id'];
  } else {
    // ah-ah no WAY :)
    $msg = "ERROR: User not logged in.";
    echo($msg);
    Logger::log($msg . " in  " . __FILE__);
    exit;
  }
  
  if (isset($_POST['del'])) {
    try {
      Relation::delete_relation($uid, '-1', $_POST['network_uid'], $_POST['network']);
    } catch(PAException $e) {
      $msg = "ERROR: There was a problem with deleting the relation<br>"
        . $e->getMessage();
      echo($msg);
      Logger::log($msg . " in " . __FILE__ );
      exit;
    }
    // if we get here, all is ok
    echo '<a href="javascript://" onclick="addrelation(this);">add to Widget</a>';
  } else {
    // try and add the relation
    try {
      Relation::add_relation(
        $uid, -1, 2, $_POST['network'], 
        $_POST['network_uid'], $_POST['display_name'],
        $_POST['thumbnail_url'], $_POST['profile_url']);
    } catch(PAException $e) {
      $msg = "ERROR: There was a problem with adding the relation<br>"
        . $e->getMessage();
      echo($msg);
      Logger::log($msg . " in " . __FILE__);
      exit;
    }
    // if we get here, all is ok
    echo '<a href="javascript://" onclick="removerelation(this);">remove from Widget</a>';
  }
  
  // default output for debug
  echo "<!-- " . print_r($_POST, true) . " -->";
  
?>