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

// Test how the system performs when being hit by a spam attack - lots
// of comments and personal messages.

// Author: phil

require_once dirname(__FILE__)."/../lib/common.php";

class SpamTrapPerformanceTest extends PA_TestCase {

    function testSpamTrapPerformance() {
	// global var $_base_url has been removed - please, use PA::$url static variable

       
	while (@ob_end_clean());

	$c = new HTTP_Client;

	echo "Hitting some pages to test how the system performs when hit by spambots\n";

	echo "Spidering content ...\n";

	list($dom, $xp) = $this->get_and_parse(PA::$url . PA_ROUTE_HOME_PAGE);
	$content = array();
	foreach ($xp->query("//a/@href") as $node) {
	    if (preg_match("|/content.php\?|", $node->value)) {
		$content[$node->value] = true;
	    }
	}
	$n_retrieved = 0; $total_time = 0.0;
	foreach ($content as $url => $foo) {
	    echo "Content page to retrieve: $url\n";
	    list(,,$t_taken) = $this->get_and_parse($url);
	    ++ $n_retrieved; $total_time += $t_taken;
	    if ($n_retrieved >= 3) break;
	}
	echo sprintf("avg time per download: %.1f s\n", $total_time / $n_retrieved);
	
	echo "Posting spam...\n";

	echo "Testing that we can't post comments without being logged in...\n";
	foreach ($content as $url => $foo) {
	    echo "Posting anonymous comment to $url\n";
	    if (!preg_match("/cid=(\d+)/", $url, $m)) {
		echo "error determining cid from $url\n";
		continue;
	    }
	    $cid = $m[1];
	    $c = new HTTP_Client;
	    $c->setMaxRedirects(0);
	    $ret = $c->post($url, 
		     //	    list(,,$t_taken, $c) = $this->get_and_parse($url, NULL, "POST", 
		array(
		"addcomment" => "Submit Comment",
		"name" => "automatic test robot",
		"email" => "peepagg-autotest@myelin.co.nz",
		"homepage" => "http://peopleaggregator.net/",
		"cid" => $cid,
		"ccid" => "",
		));
	    var_dump($c);
	    $this->assertEquals($c->_responses[0]['code'], 302);
	    break;
	}

	echo "Posting personal messages...\n";
    }

}


?>