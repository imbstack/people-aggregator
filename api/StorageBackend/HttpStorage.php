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

/** HttpStorage: storage backend for remote files.
 * Author: Phillip Pearson
 * Copyright (C) 2007 Broadband Mechanics
 */

require_once "api/Storage/Storage.php";
require_once "api/StorageBackend/StorageBackend.php";

class HttpStorage extends StorageBackend {

    public function __construct() {
    }

    // Copy a file from $current_filename into the storage directory
    public function save($current_path, $file_id, $filename, $file_class, $mime_type) {
	throw new PAException(OPERATION_NOT_PERMITTED, "You can't save into HttpStorage - only links are relevant here");
    }

    // Delete a file from storage
    public function delete($leaf) {
	throw new PAException(OPERATION_NOT_PERMITTED, "You can't delete from HttpStorage");
    }

    // Generate a URL for a file
    public function getURL($leaf) {
	return $leaf;
    }

    // Return full path for a file, or NULL if it doesn't exist
    public function getPath($leaf, $must_exist=TRUE) {
	throw new PAException(OPERATION_NOT_PERMITTED, "You can't fetch files directly from HttpStorage");
	return $this->path . "/$leaf";
    }
}

?>