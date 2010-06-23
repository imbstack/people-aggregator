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

/** FixedStorage: storage backend for files distributed with PeopleAggregator
 * Author: Phillip Pearson
 * Copyright (C) 2007 Broadband Mechanics
 */

require_once "api/Storage/Storage.php";
require_once "api/StorageBackend/StorageBackend.php";

class FixedStorage extends StorageBackend {

    private $path = NULL,
	$url = NULL;

    public function __construct() {
	$this->path = "web";
	$this->url = PA::$url;
	$this->rel_url = PA::$local_url;
    }

    // Copy a file from $current_filename into the storage directory
    public function save($current_path, $file_id, $filename, $file_class, $mime_type) {
	throw new PAException(OPERATION_NOT_PERMITTED, "You can't save into FixedStorage - only files distributed with PA are accessible here");
    }

    // Delete a file from storage
    public function delete($leaf) {
	throw new PAException(OPERATION_NOT_PERMITTED, "You can't delete from FixedStorage - only files distributed with PA are accessible here");
    }

    // Generate a URL for a file
    public function getURL($leaf) {
	return $this->url . "/$leaf";
    }

    // Return full path for a file, or NULL if it doesn't exist
    public function getPath($leaf, $must_exist=TRUE) {
	return $this->path . "/$leaf";
    }
}

?>