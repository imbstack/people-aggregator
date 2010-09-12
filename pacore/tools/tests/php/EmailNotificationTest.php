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

// Test that the e-mail notification code is working properly.

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";
require_once "web/includes/functions/auto_email_notify.php";
ini_set('xdebug.collect_vars', '1');

$this_test_requires_globals = "test";

class EmailNotificationTest extends PHPUnit_Framework_TestCase {

    static $messages = array();
    
    public static function mail_hook($recip, $subject, $body, $headers) {
      	if (0) {
	    echo "mail() called!
recipient: $recip
subject: $subject
--- headers ---
$headers
--- body ---
$body
--- end mail ---
";
	}
	EmailNotificationTest::$messages[] = array($recip, $subject, $body, $headers);
	return TRUE;
    }

    function testEmailNotification() {

	// test requires xdebug and xdebug_get_declared_vars().
	if (!extension_loaded('xdebug')) {
	    echo ($msg = "Need xdebug extension for email notification test")."\n";
	    $this->markTestIncomplete($msg);
	}
	if (ini_get('xdebug.collect_vars') != 1) {
	    echo ($msg = "need to set xdebug.collect_vars = 1")."\n";
	    $this->markTestIncomplete($msg);
	}

	$this->assertEquals($GLOBALS['this_test_requires_globals'], "test", "xdebug not working - or need to update phpunit?  see http://pear.php.net/bugs/bug.php?id=5053 for more info.");

	// hook mail()
	global $mail_testing_callback;
	$mail_testing_callback = array("EmailNotificationTest", "mail_hook");

	// load main network and get a fake owner user
	global $network_info, $owner;
	$network_info = Network::get_mothership_info();
	$owner = Test::get_test_user();
	
	// override destination so we get an e-mail
	$extra = unserialize($network_info->extra);
	$extra['notify_owner']['announcement']['value'] = NET_EMAIL;
	$extra['notify_owner']['content_to_homepage']['value'] = NET_EMAIL;
	$network_info->extra = serialize($extra);

	$this->assertEquals(count(EmailNotificationTest::$messages), 0);

	// now trigger a fake network announcement
        $owner_name = 'John Q. NetworkOwner';
	announcement(array(
	    'params' => array(
		'aid' => "whatever",
		),
	    'owner_name' => $owner_name,
	    ));

	$this->assertEquals(count(EmailNotificationTest::$messages), 1);
	$msg = EmailNotificationTest::$messages[0];
	$this->assertContains("nnouncement", $msg[1]);
	$this->assertContains("network", $msg[1]);
	$this->assertContains($owner_name, $msg[2]);
	$this->assertContains("nnouncement", $msg[2]);

	// now trigger a fake content posting to community blog
	$comm_blog_post = array(
	    'owner_name' => $owner_name,
	    'params' => array(
		'first_name' => "Firstname",
		'network_name' => "Network Name",
		'user_id' => $owner->user_id,
		'user_image' => $owner->picture,
		'cid' => '1234',
		'content_title' => 'Fake content title',
		));

	content_posted_to_comm_blog($comm_blog_post);
	$this->assertEquals(count(EmailNotificationTest::$messages), 2);
	$msg = EmailNotificationTest::$messages[1];
	$this->assertContains("Community Blog", $msg[1]);
	$this->assertContains("posted", $msg[1]);
	$this->assertContains($owner_name, $msg[2]);
	$this->assertContains("Firstname has posted", $msg[2]);
	echo "The site name is ". PA::$site_name ."\n";
	$this->assertContains(PA::$site_name, $msg[2]);

	/* The following test won't work yet -- as the site name is
           inserted in the messages early, so changing it now won't
           have any effect.

	// now change the site name and make sure it shows up properly
	$old_config_site_name = PA::$site_name;
	$new_config_site_name = PA::$site_name = "Test site name - for EmailNotificationTest";
	content_posted_to_comm_blog($comm_blog_post);
	$this->assertEquals(count(EmailNotificationTest::$messages), 3);
	$msg = EmailNotificationTest::$messages[2];
	$this->assertContains("Community Blog", $msg[1]);
	$this->assertContains("posted", $msg[1]);
	$this->assertContains($owner_name, $msg[2]);
	$this->assertContains("Firstname has posted", $msg[2]);
	echo "The site name is ". PA::$site_name ."\n";
	$this->assertContains(PA::$site_name, $msg[2]);
	$this->assertNotContains($old_config_site_name, $msg[2]);
	$this->assertNotContains("PeopleAggregator", $msg[2]);
	*/

    }
    
}

?>