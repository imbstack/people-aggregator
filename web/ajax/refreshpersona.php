<?php
// refreshpersona
// trigger the scoop to refresh the data for one persona
  require_once dirname(__FILE__).'/../../config.inc';
  require_once "api/User/User.php";

  session_start();

  $persona_id = $_REQUEST['p'];
  if ($_SESSION['user']['id']) {
    $uid = (int)$_SESSION['user']['id'];
  }
  if (!$uid || !$persona_id) {
    print("ERROR: User not logged in.");
    exit;
  }
  $user = new User();
  $user->load($uid);
  trigger_scraper($user, $persona_id);

function trigger_scraper($user, $persona_id) {
  global $scoop_ui_url; // should be defined in local_config.php
  if(! isset($scoop_ui_url)) {
    $scoop_ui_url = "http://localhost:7070";
  }
  $params = Array(
    'userName'=>$user->login_name,
    'password'=>$user->password,
    'pwtype'=>'md5'
    );

  $params['todo'] = $persona_id . ',';

  // http://localhost:7070/sessionStart?userName=userName&password=xxxxx
  // we would like to do POST
  $retun = http('POST', $scoop_ui_url . '/sessionStart', $params);  
  // echo "<hr>$return<hr>"; // debug!!
}

function http($method="GET", $url, $argArray=null) {
  require_once("HTTP/Client.php");
  $agent = new HTTP_Client();
  if ($method == "POST") {
    $code = $agent->post($url, $argArray);
  } else {
    if($argArray) {
      // build query string from $argArray
      if(strpos("?",$url)) {
        $query = "&";
      } else {
        $query = "?";
      }
      $url .= $query . http_build_query($argArray);
    }
    $code = $agent->get($url);
  }
  if (PEAR::isError($code)) {
    $error = $code->getMessage();
    Logger::log(basename(__FILE__) . " $method $url failed: $error");
    return false;
  } else {
    $responseArray = $agent->currentResponse();
    return $responseArray['body'];
  }
}
?>