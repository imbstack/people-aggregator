<?php
/**
 * Created by: Z.Hron, 02-06-2007
 *
 * @name Resize image
 * @author Zoran Hron
 *
 *
 * @example
 *
 *  <code>
 *
 *   <img src="http://www.myserver.com/resize_img.php?src=http://www.something.com/files/lion2.jpg&height=98&width=98" />
 *
 *  </code>
 *
 **/
require_once dirname(__FILE__).'/../config.inc';

    $img_src        = $_GET['src'];
    $new_img_width  = $_GET['width'];
    $new_img_height = $_GET['height'];
    $img            = null;
    $img_remote     = null;
    if(function_exists("gd_info") && function_exists("imagecreatefromjpeg") && function_exists("imagecreatefromgif")) {
      $img_path = parse_url($img_src);
      $img_ext  = pathinfo($img_path['path'], PATHINFO_EXTENSION );
      if(empty($img_ext)) {  // dynamic image URL
        if (preg_match("|http://(.*?)/(.*)|", $img_src, $m)) {
          try {
            list(, $uf_server, $uf_path) = $m;
            $image_file = Storage::save($img_src, basename($uf_path), "critical", "image");
            if(!empty($image_file)) {
              $stored_img = new StoredFile($image_file);
              $img_src = $stored_img->getURL();
              $img_path = parse_url($img_src);
              $img_ext  = pathinfo($img_path['path'], PATHINFO_EXTENSION );
            } else {
              echo "invalid IMG url"; exit;
            }
          } catch (Exception $e) {
            $img = null;
          }
        }
      }
      if($img_path['host'] == $_SERVER['SERVER_NAME']) {                         // image from local server
        $img_src = PA::resolveRelativePath('web' . $img_path['path']);
      }
      if ($img_ext == 'jpg' || $img_ext == 'jpeg') {
        $img = @imagecreatefromjpeg($img_src);
      }
      elseif ($img_ext == 'png') {
        $img = @imagecreatefrompng($img_src);
      }
      elseif ($img_ext == 'gif') {
        $img = @imagecreatefromgif($img_src);
      } else {
        exit;
      }
      if ($img) {
        $x_offset = $y_offset = 0;
        $img_width = imagesx($img);
        $img_height = imagesy($img);
        $x_scale = floatval($new_img_width)/floatval($img_width);
        $y_scale = floatval($new_img_height)/floatval($img_height);
        $x_scale = $y_scale = max($x_scale, $y_scale);
        $new_width = intval($img_width * $x_scale + 0.5);
        $new_height = intval($img_height * $y_scale + 0.5);
        if ($new_width > $new_img_width) {
          $x_offset = (int)((floatval($new_width - $new_img_width) / 2.0) / $x_scale);
          $img_width = intval(floatval($new_img_width) / $x_scale + 0.5);
          $new_width = $new_img_width;
        }
        if ($new_height > $new_img_height) {
          $y_offset = (int)((floatval($new_height - $new_img_height) / 2.0) / $y_scale);
          $img_height = intval(floatval($new_img_height) / $y_scale + 0.5);
          $new_height = $new_img_height;
        }
        $tmp_img = @imagecreatetruecolor($new_width, $new_height);
        @imagecopyresampled($tmp_img, $img, 0, 0, $x_offset, $y_offset, $new_width, $new_height, $img_width, $img_height);
/*
        $scale = min($new_img_width/$img_width, $new_img_height/$img_height);
        $new_width  = floor($scale * $img_width);
        $new_height = floor($scale * $img_height);
        $tmp_img = @imagecreatetruecolor($new_width, $new_height);
        @imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);
*/
        header("Content-Type: image/png");
        imagePNG($tmp_img);
        imageDestroy($tmp_img);
      }
 }
?>
