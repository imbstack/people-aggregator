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

//require_once "../config.inc";
$login_required = TRUE;
include_once("web/includes/page.php");
require_once "api/DB/Dal/Dal.php";
require_once "api/Storage/Storage.php";

$filename_raw = $_GET['file'];

// sanity check
$root = realpath(PA::$project_dir . "/web/files");
if (!$root) {
    header("404 Not Found");
    echo "In fact, the entire upload directory (".PA::$project_dir."/web/files) doesn't seem to exist, so there's no chance of finding your file!";
    exit;
}

// see if we're getting it from Storage
if (isset($_GET['f'])) {
    try {
  $file = Storage::get((int)$_GET['f']);
    } catch (PAException $e) {
  if ($e->getCode() != FILE_NOT_FOUND) throw $e;
  header("404 Not Found");
  echo "File does not exist";
  exit;
    }
    if ($file->filename != $filename_raw) {
  header("403 Forbidden");
  echo "File ID and name do not match";
  exit;
    }
    $filename = $file->getPath();
} else {
    // protect against directory traversal
    if (strstr($filename_raw, "/../")) {
  header("403 Forbidden");
  echo "Directory traversal not allowed";
  exit;
    }
    
    $filename = realpath("$root/$filename_raw");
    
    // second security check
    if (strpos($filename, $root) !== 0) {
  header("403 Forbidden");
  echo "Directory traversal forbidden.";
  exit;
    }
}
    
// not found?
if (!$filename) {
    header("404 Not Found");
    echo "I looked, but couldn't find $filename_raw (realpath $root/$filename_raw)";
    exit;
}

// if we got this far, send it out.

$ctype = NULL;

if (preg_match("/(\.[A-Za-z]+)$/", $filename, $m)) {

    $map = array(
        '.wmv' => 'video/x-ms-wmv',
        '.qt' => 'video/quicktime',
        '.mov' => 'video/quicktime',
        '.avi' => 'video/x-msvideo',
        );

    $ctype = @$map[$m[1]];

}

if (!$ctype) $ctype = 'application/octet-stream';

 header("Pragma: public"); // required
 header("Expires: 0");
 header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
 header("Cache-Control: private",false); // required for certain browsers
 header("Content-Type: $ctype");
 header("Content-Disposition: attachment; filename=".basename($filename).";" );
 header("Content-Transfer-Encoding: binary");
 header("Content-Length: ".@filesize($filename));

 @readfile("$filename") or die("file not found.");
 exit;
?>