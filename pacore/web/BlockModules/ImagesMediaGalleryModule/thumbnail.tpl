<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/base/jquery-ui.css" type="text/css" media="all" /> 
<link rel="stylesheet" href="http://static.jquery.com/ui/css/demo-docs-theme/ui.theme.css" type="text/css" media="all" /> 
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script> 
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js" type="text/javascript"></script>
<script src="http://jquery-ui.googlecode.com/svn/tags/latest/external/jquery.bgiframe-2.1.1.js" type="text/javascript"></script> 


<script src="/Themes/Default/javascript/galleria/src/galleria.js"></script>
<script src="/Themes/Default/javascript/galleria/src/themes/classic/galleria.classic.js"></script>
<script>
	window.onload = $('#gallery').galleria();
</script>
<?php global $login_uid; 
  $temp = $links;
  // Handling Group Media gallery
  $gid = (!empty($_GET['gid']))?'&gid='.$_GET['gid']:NULL;
  unset($temp['album_id']);
  unset($temp['album_name']);
 ?>
<div class="media_gallery_thumb" id="image_gallery_thumb">
<br />
<?php if (!empty($temp)) {?>
<div id="gallery">
<?php for ($i=0; $i<count($links)-2; $i++) { ?>  
    <?php  // for Images and their hyperlinks
        if($links[$i]['type'] == 7) {
           $img_tag = '<img src="'.$links[$i]['image_src'].'" style="border:none; width:auto; height:50px;">'; 
	   $image_hyperlink = PA::$url . PA_ROUTE_CONTENT . "/cid=".$links[$i]['content_id'].$gid;
        } 
        else if (strstr($links[$i]['image_file'], "http://")) {
            $image_path = $links[$i]['image_file'];
            //Verify image path as well as Image type
              $image_path = (verify_image_url($image_path)) ? $links[$i]['image_file'] : PA::$theme_url . '/images/no_img_found.gif'; 
              $img_tag = '<img src="'.$image_path.'" style="border:none; width:auto; height:50px;" />';
              $image_hyperlink = PA::$url ."/media_full_view.php?cid=".$links[$i]['content_id'].$gid;
        }
        else {
                $img_tag = "<img src='".'/files'.'/'.$links[$i]['image_file']."'>";
                $image_hyperlink = PA::$url ."/media_full_view.php?cid=".$links[$i]['content_id'].$gid;
        }
    
    ?>
     <a href="<?=$image_hyperlink?>"><?=$img_tag?></a>

  <?} // End of for loop?>  
</div>
<?} else { ?>
  <ul>
    <li>
      <?= __("No Photos.") ?>
    </li>
  </ul>  
<? } ?>
</div>
