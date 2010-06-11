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

// Test the ConfigVariable ext api class.

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";

class ConfigVariableTest extends PHPUnit_Framework_TestCase {
    function testConfigVar() {
	// setup
	ConfigVariable::remove("test_config_var");
	$this->assertEquals(ConfigVariable::get("test_config_var", "1234"), "1234");

	// see if we can save a new value and serialize an array
	ConfigVariable::set("test_config_var", array(1, 2, 3));
	$this->assertEquals(ConfigVariable::get("test_config_var", "1234"), array(1, 2, 3));

	// see if we can change an existing value, and store a nasty
	// string full of punctuation
	$ridiculous_punctuation = "'&@^%$*(&^@%#\"$&*^%@#$+_)}{}{{?><,./,";
	ConfigVariable::set("test_config_var", $ridiculous_punctuation);
	$this->assertEquals(ConfigVariable::get("test_config_var", "1234"), $ridiculous_punctuation);

	// make sure we can remove the value
	ConfigVariable::remove("test_config_var");
	$this->assertEquals(ConfigVariable::get("test_config_var", "1234"), "1234");
    }
}

?>