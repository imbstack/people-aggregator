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

$use_theme = 'Beta';

$login_required = FALSE;
include_once("web/includes/page.php");
require_once "api/Cache/Cache.php";
require_once "web/includes/classes/Delparser.php";

session_write_close(); // close session and release lock, so other scripts can run at the same time as this one

$delicious_id = $_GET['delicious_id'];

// get links from cache if possible, otherwise fetch and store
$cache_key = "delicious_links:$delicious_id";
$links = Cache::getExtCache(0, $cache_key);
if ($links === NULL) {
  $links = delicious_getlinks($delicious_id);

  // if we got something, save it in the cache
  if (!empty($links)) {
    Cache::setExtCache(0, $cache_key, $links);
  }
}

// we have links: now render!
include "web/".PA::$theme_rel."/delicious_links.tpl";

?>