<?php
$login_required = FALSE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.

include_once("web/includes/page.php");
require_once "ext/Image/Image.php";

global $login_uid, $current_theme_path;
// Now we are trying to get the data from the given id
if ( !empty($_GET['img_id']) ) {
  $data_array = explode('_', $_GET['img_id']);
  
  $show_media = new Image();
  $show_media->load($data_array[1]);
  
  if (strstr($show_media->file_name, 'http://')) {
    $image_val['url'] = $show_media->file_name;
  }
  else if (!empty($show_media->file_name)) {
    // Now we fatching all the data related to the image and display it 
    $image_val = uihelper_resize_img($show_media->file_name, 220, 200, PA::$theme_rel."/images/no_preview.gif",'alt="Media gallery"');
  }
  else { // handling the SB image 
    $var = $show_media->body;
    $start = strpos($var, '<image>') + 7;
    $end = strpos($var, '</image>');
    $image_val['url'] = substr($var, $start, $end-$start);
    $show_media->body = $show_media->title;
  }
 
  echo '<h2 style="overflow:hidden"><center>'.$show_media->title.'</center></h2>';
  echo '<img src="'.$image_val['url'].'" style="border:none; width:auto; height:200px;" />';
 
  echo '<br /><center>'.$show_media->body.'</center>';
}
?>