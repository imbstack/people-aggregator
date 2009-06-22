<?php

// Test url_for()

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";
require_once "web/includes/urls.php";

class UrlGenerationTest extends PHPUnit_Framework_TestCase {
    function setUp() {
	global $_PA;
	$this->old_fancy_url = $_PA->enable_fancy_url;
    }

    function testDown() {
	global $_PA;
	$_PA->enable_fancy_url = $this->old_fancy_url;
    }

    function testUrlFor() {
	global $_PA;

	$user = Test::get_test_user();

	$_PA->enable_fancy_url = TRUE;
	$this->assertEquals(url_for("user", array("login" => $user->login_name)),
			    PA::$url.'/users/'.$user->login_name.'/');
	$this->assertEquals(url_for("user", array("login" => $user->login_name, "one" => 1, "two" => 2)),
			    PA::$url.'/users/'.$user->login_name.'/?one=1&two=2');
	$this->assertEquals(url_for("user", array("login" => $user->login_name, "one" => 1, "two" => 2), array("one" => "asdf", "three" => "foo")),
			    PA::$url.'/users/'.$user->login_name.'/?one=1&two=2&three=foo');

	$_PA->enable_fancy_url = FALSE;
	$this->assertEquals(url_for("user", array("login" => $user->login_name)),
			    PA::$url.'/user.php?login='.$user->login_name);
	$this->assertEquals(url_for("user", array("login" => $user->login_name, "one" => 1, "two" => 2)),
			    PA::$url.'/user.php?login='.$user->login_name.'&one=1&two=2');
	$this->assertEquals(url_for("user", array("login" => $user->login_name, "one" => 1, "two" => 2), array("one" => "asdf", "three" => "foo")),
			    PA::$url.'/user.php?login='.$user->login_name.'&one=1&two=2&three=foo');
    }
}

?>