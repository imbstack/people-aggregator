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
<ul>
<?php for ($i=0; $i<count($links)-2; $i++) { ?>   
  <li id="image_<?php echo $links[$i]['content_id'];?>">
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
                $img_tag = uihelper_resize_mk_img($links[$i]['image_file'], 70, 50);
                $image_hyperlink = PA::$url ."/media_full_view.php?cid=".$links[$i]['content_id'].$gid;
        }
    
    ?>
     <a href="<?php echo $image_hyperlink; ?>"> <?=$img_tag?> </a>
     
     <p class="name">
        <a href="<?=$image_hyperlink;?>">
          <?php 
          $caption = ucwords(strtolower(_out($links[$i]['image_caption'])));
          if(strlen($caption) > 10) { $ext = substr ($caption, -2); $start =  substr ($caption, 0, 9); print stripslashes($start."....".$ext); } else { print stripslashes($caption); }?>
        </a>
     </p>
     
     <?php if (!empty($uid) && $uid == $login_uid && empty($_GET['gid'])) { 
                 if($links[$i]['type'] == 7) {
                      $link_for_editing = PA::$url ."/post_content.php?cid=".$links[$i]['content_id'];
                      $content_type = 7;
                      
                  } else {
                      $link_for_editing = PA::$url ."/edit_media.php?uid=".$uid."&amp;cid=".$links[$i]['content_id']."&amp;type=image";
                      $content_type = 1;
                      
                  }   ?>         
     <p class="choose">

       <a href="<?php echo $link_for_editing;?>"><img src="<?php echo PA::$theme_url;?>/images/16_edit.gif" alt="" height="16" width="16" border="0" title="Edit" /></a> 
       <a href="#"><img src="<?php echo PA::$theme_url;?>/images/16_delete.gif" alt="" height="16" width="16" border="0"  onclick="edit_delete_media('<?=$links[$i]['content_id']?>','delete','image_upload');" title="Delete" /></a>
    </p>
    
    <? } if (isset($_GET['gid']) && !empty ($_GET['gid']) && ($links[$i]['author_id'] == $login_uid )) {
       if($links[$i]['type'] == 7) {
            $link_for_editing = PA::$url ."/post_content.php?cid=".$links[$i]['content_id'];
       } else {
         $link_for_editing = PA::$url ."/edit_media.php?uid=".$login_uid."&amp;cid=".$links[$i]['content_id']."&amp;type=image";
       }
    ?>
     <p class="choose">

       <a href="<?php echo $link_for_editing;?>"><img src="<?php echo PA::$theme_url;?>/images/16_edit.gif" alt="" height="16" width="16" border="0" /></a> 
       <a href="#"><img src="<?php echo PA::$theme_url;?>/images/16_delete.gif" alt="" height="16" width="16" border="0"  onclick="edit_delete_media('<?=$links[$i]['content_id']?>','delete','image_upload');" /></a>
    </p>
       
    <?} ?>
  </li>
<?} // End of for loop?>  
</ul>
<?} else { ?>
  <ul>
    <li>
      <?= __("No Photos.") ?>
    </li>
  </ul>  
<? } ?>
</div>