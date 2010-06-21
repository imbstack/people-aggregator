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

// xspf.php: write an xspf file with one track, given a url on the path
if(@$_GET['single']) {
    $url = $_GET['single'];
    $desc = str_replace("_", " ", basename($url));
    if(preg_match("|^(.*)\.[a-z0-9]+$|", $desc, $m)) {
        $desc = $m[1];
    }
    header("Content-Type: application/xspf+xml");
    echo "<";
    ?>?xml version="1.0" encoding="UTF-8"?<?=">"?>
<playlist version="0" xmlns="http://xspf.org/ns/0/">
  <title><?=htmlspecialchars($desc)?></title>
  <trackList>
    <track>
      <location><?=htmlspecialchars($url)?></location>
      <title><?=htmlspecialchars($desc)?></title>
    </track>
  </trackList>
</playlist>
<?
}
else {
    header("HTTP/1.0 400 Bad Request");
    echo "Syntax: xspf.php/http://server/path/to/mp3/file";
}
?>