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

// Test various system functions.

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";

class GlobalFunctionsTest extends PHPUnit_Framework_TestCase {
    function testParseFileSizeString() {
        $this->assertEquals(parse_file_size_string("1234"), 1234);
        $this->assertEquals(parse_file_size_string("0b"), 0);
        $this->assertEquals(parse_file_size_string("5b"), 5);
        $this->assertEquals(parse_file_size_string("1k"), 1024);
        $this->assertEquals(parse_file_size_string("16k"), 16*1024);
        $this->assertEquals(parse_file_size_string("2M"), 2*1024*1024);
        $this->assertEquals(parse_file_size_string("123M"), 123*1024*1024);
        $this->assertEquals(parse_file_size_string("2G"), 2*1024*1024*1024);
    }
}

?>