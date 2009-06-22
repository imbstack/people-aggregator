<?php
session_start();
// print_r($_REQUEST); exit;
require_once dirname(__FILE__).'/../config.inc';
$s = PA::$url;
if ($_SESSION['flickr_return_url']) {
  $s = $_SESSION['flickr_return_url'];
  if ($_REQUEST['frob']) {
    $s .= (preg_match('/\?/',$s)) ? '&' : '?';
    $s .= "frob=".$_REQUEST['frob'];
  }
}
header("Location: ".$s);
?>