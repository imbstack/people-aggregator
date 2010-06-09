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

// base class for storage backends

class StorageBackend {

    // for backends that don't implement downloading, this will download a file to a temp location and call save()
    public function download($current_url, $file_id, $filename, $file_class, $mime_type) {
	// retrieve url
	$temp_path = tempnam(ini_get("upload_tmp_dir"), "dl");
	$ch = curl_init($current_url);
	try {
	    $temp_file = fopen($temp_path, "wb");
	    curl_setopt_array($ch, array(
                CURLOPT_FAILONERROR => TRUE,
		CURLOPT_FOLLOWLOCATION => FALSE,
		CURLOPT_FORBID_REUSE => TRUE,
		CURLOPT_LOW_SPEED_LIMIT => 1024, // kill transfer if slower than 1kB/s for > 5 s
		CURLOPT_LOW_SPEED_TIME => 5,
		CURLOPT_TIMEOUT => 30, // kill transfer if it takes > 30 s
		CURLOPT_FILE => $temp_file,
		));
	    $r = curl_exec($ch);
	    fclose($temp_file); $temp_file = NULL;
	    if (!$r) throw new PAException(FILE_NOT_UPLOADED, sprintf(__("Failed to retrieve %s: %s"), $current_url, curl_error($ch)));
	    curl_close($ch); $ch = NULL;
	    // and throw it in Storage
	    $r = $this->save($temp_path, $file_id, $filename, $file_class, $mime_type);
	    // unlink temp file
	    unlink($temp_path);
	    // and pass the save return code back
	    return $r;
	} catch (PAException $e) {
	    // tidy up
	    if ($temp_file) fclose($temp_file);
	    if ($ch) curl_close($ch);
	    unlink($temp_path);
	    throw $e;
	}
    }

}

?>