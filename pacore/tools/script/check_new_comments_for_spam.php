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

include dirname(__FILE__)."/../config.inc";
require_once "api/Comment/Comment.php";
while(@ob_end_clean());
//echo "Comment spam stats:\n";
//Dal::query("SELECT akismet_spam,COUNT(comment_id) ct FROM comments GROUP BY akismet_spam ORDER BY ct DESC");
function get_remaining() {
    list($remaining) = Dal::query_one("SELECT COUNT(*) FROM comments WHERE is_active=1 AND akismet_spam IS NULL");
    return (int) $remaining;
}
echo "Looking for comments which have not been checked for spam yet.\n";
$ct = get_remaining();
echo "... $ct comments to check.  Checking them...\n";
$checked = 0;
while($checked < $ct) {
    $remaining = get_remaining();
    echo "Progress: $checked checked out of total $ct (remaining: $remaining)\n";
    if(!$remaining) {
        echo "No comments left to classify!\n";
        break;
    }
    $sth = Dal::query("SELECT comment_id FROM comments WHERE is_active=1 AND akismet_spam IS NULL LIMIT 10");
    while($r = Dal::row($sth)) {
        list($comment_id) = $r;
        echo "- checking comment $comment_id... ";
        flush();
        $c = new Comment();
        $c->load((int) $comment_id);
        if(!$c->homepage) {
            $c->homepage = "";
        }
        $c->akismet_check();
        echo var_export($c->akismet_spam, TRUE)."\n";
        ++$checked;
        sleep(1);
    }
}
?>