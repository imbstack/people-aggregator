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
require_once dirname(__FILE__)."/../config.inc";
require_once "web/includes/functions/functions.php";
check_session(0);
require_once "api/Content/Content.php";

function trackback_response($error = 0, $error_message = '') {
  header('Content-Type: text/xml;');
  if ($error) {
    echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
    echo "<response>\n";
    echo "<error>1</error>\n";
    echo "<message>$error_message</message>\n";
    echo "</response>";
    die();
  } else {
    echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
    echo "<response>\n";
    echo "<error>0</error>\n";
    echo "</response>";
  }
}


$tb_url    = $_POST['url'];
if (empty($_POST['title'])) {
  $title = $_POST['url'];
}
else {
  $title = $_POST['title'];
}
$excerpt = $_POST['excerpt'];

if (!empty($tb_url)) {
  $result = Content::add_trackbacks($_GET['cid'], $tb_url, $title, $excerpt);
  if ($result == FALSE) {
    trackback_response(1, 'We already have a ping from that URI for this post.');
  }
  else {
    trackback_response(0);
  }
  
}
else {
  trackback_response(1, 'Url required');
}

  
?>