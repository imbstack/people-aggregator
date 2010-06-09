<div>
<?
if (!empty($links)) {
  if (!empty($links->ad_script)) {
    echo $links->ad_script;
  } else if (!empty($links->ad_image)) {
    ?><div align="center"><a href="<?php echo $links->url?>" target="_blank" onclick="javascript:track_this_ad('<?php echo $links->ad_id;?>');">
      <?= uihelper_resize_mk_img($links->ad_image, $width, $height, NULL, 'alt="advertisement"', RESIZE_FIT_NO_EXPAND) ?></a></div>
    </a><?
  }
  if (!empty($links->description)) {
  	echo '<div style="margin:5px 2px;">'.$links->description.'</div>';
  }
}
?>
</div>