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
  session_start();
  $alphanum  = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
  $rand = substr(str_shuffle($alphanum), 0, 5);
  $image = imagecreatefromgif(dirname(__FILE__)."/Themes/Default/images/comment_verification.gif");
  $textColor = imagecolorallocate ($image,85,119,158);
  imagestring ($image, 5 , 8, 4,  $rand, $textColor);
  $_SESSION['image_random_value'] = md5(strtoupper($rand));
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
  header("Cache-Control: no-store, no-cache, must-revalidate"); 
  header("Cache-Control: post-check=0, pre-check=0", false); 
  header("Pragma: no-cache");   
  header('Content-type: image/gif');
  imagegif($image);
  imagedestroy($image);
?>