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

// Test the Storage api class and the StorageBackends.

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";
require_once "api/Storage/Storage.php";
require_once "ext/StorageBackend/LocalStorage.php";

class StorageTest extends PHPUnit_Framework_TestCase {
    function setUp() {
	system("echo 'foo' > test.txt; echo 'second test' > test2.txt");
    }

    function tearDown() {
	system("rm -f test.txt test2.txt");
    }

    function testStorage() {
	// test Storage - public API

	// store test.txt
	echo "saving test.txt with a crazy name\n";
	$file_id = Storage::save('test.txt', 'O*Bc3wukygfsT@#($0876)$!@#*+_][.txt');
	
	echo "resulting file_id = $file_id\n";
	$file = Dal::query_one_object("SELECT * FROM files WHERE file_id=?", array($file_id));
	$this->assertEquals($file->link_count, 0);
	$this->assertEquals($file->last_linked, NULL);

	$file_path = Storage::getPath($file_id);
	$file_url = Storage::getURL($file_id);
	echo "getPath($file_id) -> $file_path\n";
	echo "getURL($file_id) -> $file_url\n";
	$this->assertTrue(strpos($file_path, "web/files/") === 0);
	$this->assertTrue(strpos($file_url, PA::$url) === 0);

	// link it in somewhere
	$link_id = Storage::link($file_id, array(
				     'role' => 'avatar',
				     'user' => 1));
	echo "linked it in as avatar for user 1; link_id = $link_id\n";
	$link = Dal::query_one_object("SELECT * FROM file_links WHERE link_id=? AND file_id=?", array($link_id, $file_id));
	$this->assertEquals($link->file_id, $file_id);
	$file = Dal::query_one_object("SELECT * FROM files WHERE file_id=?", array($file_id));
	$this->assertEquals($file->link_count, 1);
	$this->assertNotEquals($file->last_linked, NULL);

	// another file
	$child_file_id = Storage::save('test2.txt', 'this is the child file.jpg', 'throwaway', 'image/jpeg');
	echo "child file: $child_file_id\n";
	$child_file = Dal::query_one_object("SELECT * FROM files WHERE file_id=?", array($child_file_id));

	$child_file_path = Storage::getPath($child_file_id);
	$child_file_url = Storage::getURL($child_file_id);
	echo "getPath($child_file_id) -> $child_file_path\n";
	echo "getURL($child_file_id) -> $child_file_url\n";
	$this->assertTrue(strpos($child_file_path, "web/files/") === 0);
	$this->assertTrue(strpos($child_file_url, PA::$url) === 0);

	// link child file in as a thumbnail of first file
	$child_link_id = Storage::link($child_file_id, array(
					   'role' => 'thumb',
					   'file' => $file_id,
					   'dim' => '123x123'));
	echo "child link id: $child_link_id\n";
	$child_link = Dal::query_one_object("SELECT * FROM file_links WHERE link_id=? AND file_id=?", array($child_link_id, $child_file_id));
	$this->assertEquals($child_link->file_id, $child_file_id);
	$this->assertEquals($child_link->parent_file_id, $file_id);
	$child_file = Dal::query_one_object("SELECT * FROM files WHERE file_id=?", array($child_file_id));
	$this->assertEquals($child_file->link_count, 1);
	$this->assertNotEquals($child_file->last_linked, NULL);

	// this should fail (missing role)
	try {
	    Storage::link($file_id, array("user" => 1));
	    $this->fail("Expected exception");
	} catch (PAException $e) {
	    $this->assertEquals($e->getCode(), BAD_PARAMETER);
	}
	// this should fail (missing network)
	try {
	    Storage::link($file_id, array("role" => "header", "group" => 42));
	    $this->fail("Expected exception");
	} catch (PAException $e) {
	    $this->assertEquals($e->getCode(), BAD_PARAMETER);
	}
	// this should fail (network not valid)
	try {
	    Storage::link($file_id, array("role" => "thumb", "network" => 1, "file" => $file_id, "dim" => "123x123"));
	    $this->fail("Expected exception");
	} catch (PAException $e) {
	    $this->assertEquals($e->getCode(), BAD_PARAMETER);
	}
	// this should fail (parent_file_id == file_id)
	try {
	    $link_id = Storage::link($file_id, array("role" => "thumb", "file" => $file_id, "dim" => "123x123"));
	    $this->fail("Expected exception");
	} catch (PAException $e) {
	    $this->assertEquals($e->getCode(), BAD_PARAMETER);
	}

	// Now unlink the two files we just created ...

	// unlink the first - but don't delete it
	Storage::unlink($file_id, $link_id, FALSE);
	// make sure it's gone
	$this->assertEquals(Dal::query_one("SELECT * FROM file_links WHERE link_id=? AND file_id=?", array($link_id, $file_id)), NULL);
	// the file should still be there, with zero links, though
	$file = Dal::query_one("SELECT * FROM files WHERE file_id=?", array($file_id));
	$this->assertNotEquals($file, NULL);
	$this->assertEquals($file->link_count, 0);

	// try a bad unlink operation
	try {
	    Storage::unlink($file_id, $child_link_id);
	    $this->fail("Expected exception");
	} catch (PAException $e) {
	    $this->assertEquals($e->getCode(), FILE_NOT_FOUND);
	}

	// unlink and delete the second
	Storage::unlink($child_file_id, $child_link_id);
	// make sure it's gone
	$this->assertEquals(Dal::query_one("SELECT * FROM file_links WHERE link_id=? AND file_id=?", array($child_link_id, $child_file_id)), NULL);
	// and make sure the file is gone too
	$this->assertEquals(Dal::query_one("SELECT * FROM files WHERE file_id=?", array($child_file)), NULL);

	// reap unlinked files (immediately - no grace period)
	Storage::cleanupFiles(-1, -1);
	// make sure the first file is now gone
	$this->assertEquals(Dal::query_one("SELECT * FROM files WHERE file_id=?", array($file_id)), NULL);
    }

}

?>