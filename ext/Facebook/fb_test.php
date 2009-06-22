<?
ob_start();
include "../../default_config.php"; // for api keys
include "facebookapi_php5_restlib.php";

session_start();

if ($_GET['newsession_req']) {
  $_SESSION['session_key'] = NULL;
}
$session_key = @$_SESSION['session_key'];
$uid = @$_SESSION['uid'];

$client = new FacebookRestClient($facebok_api_key, $facebook_api_secret);
//$client->server_addr = 'http://api.facebook.com/restserver.php';
$client->server_addr = 'http://pokemon.broadbandmechanics.com/fb_api/restserver.php';
//$client->debug = 1;
if ($session_key) $client->session_key = $session_key;

if (!$session_key) {
  $token = @$_GET['auth_token'];

  if ($token) {
    echo "<p>ok, i have an auth token: $token</p>";
    
      $r = $client->auth_getSession($token);
    
    $session_key = $_SESSION['session_key'] = $r['session_key'];
    $uid = $_SESSION['uid'] = $r['uid'];
  }
  else {

?>

<p><a href="http://api.facebook.com/login.php?api_key=<?=$facebook_api_key?>" target="_blank">log in to facebook</a></p>


<form method="GET"><p>then paste the token from the url here: <input type="text" name="auth_token" size="20"><input type="submit" value="finish login"></p></form>

<?
   }
}

if ($session_key) {
  echo "<p>logged in as $uid, with sess key $session_key</p>";
  echo "<p>Trying to getInfo</p>";

  try {
  $r = $client->users_getInfo(array($uid), array("about_me", "affiliations", "birthday", "books", "clubs", "current_location", "first_name", "gender", "hometown_location", "hs_info", "interests", "last_name", "meeting_for", "meeting_sex", "movies", "music", "name", "political", "pic", "relationship_status", 'quote', "school_info", "tv", "work_history"));
    } catch (Exception $e) {
      echo "<script>location.href='?newsession_req=1'</script>";
      exit;
    }

  echo "<div clear='all'><p>user info:</p><pre>".htmlspecialchars(var_export($r, TRUE))."\n"
  .htmlspecialchars($client->last_xml)
  ."</pre>";

}

?>