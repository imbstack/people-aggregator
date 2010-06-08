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

/** LocalStorage: default storage backend, just stores files on the local hard disk.
 * Author: Phillip Pearson
 * Copyright (C) 2007 Broadband Mechanics
 */

require_once "api/Storage/Storage.php";
require_once "ext/StorageBackend/StorageBackend.php";

// used by LocalStorage::delete
function not_this_server($server_id) {
    return ($server_id != PA::$server_id);
}

class LocalStorage extends StorageBackend {

    private $path = NULL,
	$url = NULL;

    public function __construct() {
	 
	// path to files (default from default_config.php: PA::$path/web/files)
	$this->path = PA::$config->local_storage_path;
	$this->url = PA::$url . '/' . PA::$config->local_storage_rel_url;
	$this->rel_url = PA::$local_url . '/' . PA::$config->local_storage_rel_url;
    }

    // Copy a file from $current_filename into the storage directory
    public function save($current_path, $file_id, $filename, $file_class, $mime_type) {
	if (!file_exists($current_path)) throw new PAException(FILE_NOT_FOUND, "File to store not found (at $current_path)");
	$filename = Storage::validateFileType($current_path, $filename, $mime_type); //TODO: maybe move this inside Storage::save?
	$leaf = $this->makeFilename($file_id, $filename);
	$path = $this->getPath($leaf, FALSE);
	if (!@copy($current_path, $path)) throw new PAException(STORAGE_ERROR, "Error copying $current_path to $path");
	// make a note that we have a copy, so it will get replicated onto other servers
	Dal::query("INSERT INTO local_files SET file_id=?, timestamp=NOW(), filename=?, servers=?",
		   array($file_id, $leaf, PA::$server_id));
	//echo "copied $current_path to $path\n";
	return $leaf;
    }

    // Delete a file from storage
    public function delete($leaf) {
	//TODO: use innodb so this actually matters
	list($file_id, $servers) = Dal::query_one("SELECT file_id, servers FROM local_files WHERE filename=? FOR UPDATE", array($leaf));
	try {
	    if (!$file_id) throw new PAException(FILE_NOT_FOUND, "Unable to find file $leaf in local_files table:");
	    $path = $this->getPath($leaf);
	    $server_ids = explode(",", $servers);
	    if (in_array(PA::$server_id, $server_ids)) {
		if (empty($path)) throw new PAException(FILE_NOT_FOUND, "Unable to delete nonexistent file $path");
		if (!@unlink($path)) throw new PAException(STORAGE_ERROR, "Error deleting $path");
		$server_ids = array_filter($server_ids, "not_this_server");
		$servers = implode(",", $server_ids);
	    }
	    Dal::query("UPDATE local_files SET is_deletion=1, timestamp=NOW(), servers=? WHERE file_id=?", array($file_id, $servers));
	} catch (PAException $e) {
	    Dal::rollback();
	    throw $e;
	}
	return TRUE;
    }

    // Generate a URL for a file
    public function getURL($leaf) {
	$path = $this->getPath($leaf);
	if (!file_exists($path)) throw new PAException(FILE_NOT_FOUND, "Unable to find file to generate url: $path");
	return $this->url . "/$leaf";
    }

    // Return full path for a file, or NULL if it doesn't exist
    public function getPath($leaf, $must_exist=TRUE) {
	$path = $this->path . "/$leaf";
	if ($must_exist && !file_exists($path)) throw new PAException(FILE_NOT_FOUND, "File not found: $path");
	return $path;
    }

    // FOR INTERNAL USE ONLY (but declared 'public' so we can test it from a unit test)
    public function makeFilename($file_id, $filename) {
	// 1000 files per directory
	$bucket = (int)($file_id / 1000);
	$leaf = $file_id % 1000;
	
	// make bucket dir if required
	$bucket_path = $this->path . "/$bucket";
	if (!is_dir($bucket_path)) mkdir($bucket_path);

	// work out full filename
	$munged_filename = preg_replace("/[^A-Za-z0-9\.\-\_]/", '', $filename);
	$path = $bucket . "/" . $leaf . "-" . $munged_filename;
	return $path;
    }

}

?>