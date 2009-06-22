<?php

// StoredFile: class to represent a file in Storage (see Storage.php)
// Instantiate with Storage::get($file_info)
//   where $file_info can be an integer file ID, a pa:// URL as returned by FileUploader, or an array from Dal::query_one_assoc if the file has already been loaded from the DB

class StoredFile {
  public function __construct($file_info) {
    if (is_array($file_info)) {
      $r = $file_info;
    } else {
      if (preg_match("|pa://(\d+)|", $file_info, $m)) {
        $file_id = (int)$m[1];
      } else {
        $file_id = (int)$file_info;
      }
      if (!$file_id) throw new PAException(INVALID_ID, "Invalid file ID: $file_info");

      $r = Dal::query_one_assoc("SELECT * FROM files WHERE file_id=?", array($file_id));
      if (empty($r)) throw new PAException(FILE_NOT_FOUND, "File $file_id not found");
    }

    if ($r['incomplete']) throw new PAException(FILE_NOT_FOUND, "File $file_id is incomplete and should not be accessed");

    foreach ($r as $k => $v) $this->$k = $v;
  }

  public function getPath() {
    $ctx = Storage::connect($this->storage_backend);
    $path = $ctx->getPath($this->local_id);
    if (!$path) throw new PAException(INVALID_ID, "Missing filename in StoredFile with ID ".$this->file_id);
    if (!file_exists($path)) throw new PAException(FILE_NOT_FOUND, "StoredFile with ID ".$this->file_id." ($path) does not exist");
    return $path;
  }

  public function getURL() {
    $ctx = Storage::connect($this->storage_backend);
    return $ctx->getURL($this->local_id);
  }

  public function getDownloadURL() {
    return PA::$url."/download.php?f=".$this->file_id."&file=".urlencode($this->filename);
  }

}

?>