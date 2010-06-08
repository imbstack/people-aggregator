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
require_once "api/Content/Content.php";
PA::$network_info = Network::get_network_by_address(CURRENT_NETWORK_URL_PREFIX); // hack: do this here to avoid having to include page.php and friends

$uid = (int)@$_GET['uid'];
$gid = (int)@$_GET['gid'];
$type = @$_GET['type'];


if ($uid) {
    $feed = Content::get_content_feed_for_user($uid, 5);
} else if ($gid) {
	$group = ContentCollection::load_collection((int)$gid);
	$contents = $group->get_contents_for_collection($type = 'all', $cnt=FALSE, 5, 1,'created','DESC');
	$content_ids = array();
	foreach($contents as $i=>$con) {
		$content_ids[] = $con['content_id'];
	}
	$feed = Content::get_feed_for_content($content_ids);
} else if ($type == 'all') {
    $feed = Content::get_content_feed_for_user();
} else {
    header("HTTP/1.0 404 Not Found");
    ?>No feed available at this address.  Were you looking for <a href="/feed.php?type=all">the community blog feed</a>?<?
    exit;
}

header("Content-Type: application/xml");

print($feed); ?>
