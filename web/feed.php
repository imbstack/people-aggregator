<?php
require_once dirname(__FILE__)."/../config.inc";
require_once "api/Content/Content.php";
PA::$network_info = Network::get_network_by_address(CURRENT_NETWORK_URL_PREFIX); // hack: do this here to avoid having to include page.php and friends

$uid = (int)@$_GET['uid'];

if ($uid) {
    $feed = Content::get_content_feed_for_user($uid, 5);
}
else if ($_GET['type'] == 'all') {
    $feed = Content::get_content_feed_for_user();
}
else {
    header("HTTP/1.0 404 Not Found");
    ?>No feed available at this address.  Were you looking for <a href="feed.php?type=all">the community blog feed</a>?<?
    exit;
}

header("Content-Type: application/xml");

print($feed); ?>



