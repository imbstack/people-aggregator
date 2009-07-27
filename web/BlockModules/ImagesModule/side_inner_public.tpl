<?php
?>
<div class="module_tabbed_image_list" id='module_tabbed_image_list'>
 <ul>
   <li id="images" class="active"><a href="javascript:show_side_gallery_module('gallery-images');"><?= __("Images") ?></a></li>
   <li id="videos"><a href="javascript:show_side_gallery_module('gallery-videos'); "><?= __("Video") ?></a></li>
   <li id="audios"><a href="javascript:show_side_gallery_module('gallery-audios'); "><?= __("Audio") ?></a></li>
 </ul>
 </div>
 <div id="module_gallery_list">
 <div id="gallery-images" class="image_list display_true">
   <ul>
     <?php
       if($links_count = count($links['images'])) {
         for( $counter = 0; $counter < $links_count; $counter++) {
           $image = $links['images'][$counter]['image_file'];
           $cid = $links['images'][$counter]['content_id'];
           $permalink = (!empty($gid)) ? PA::$url .'/media_full_view.php?gid='.$gid.'&amp;type=image&amp;cid='.$cid : PA::$url .'/media_full_view.php?cid='.$cid.'&amp;type=image&amp;media';
//            $permalink = PA::$url .'/media_full_view.php?cid='.$cid.'&amp;type=image&media';
           if(strstr($image, 'http://')) {
            $image= (verify_image_url($image)) ? $image:PA::$theme_url . '/images/no_img_found.gif';
     ?>
    <li>
        <a href="<?php echo $permalink; ?>">
         <img alt="PA" border="0" src="<?php echo $image; ?>" width="70" height="65" />
         </a>
       </li>
       <?php } else { ?>
    <li>
         <a href="<?php echo $permalink; ?>">
         <?php  echo uihelper_resize_mk_img($image, 70, 65); ?>  
         </a>
    </li>
       <?php
              }
            }
         } else {
             echo '<li>'.__("No images have been published yet.").'</li>';
         }
       ?>
     </ul>
    </div>
            
<div id="gallery-videos" class="image_list_video display_false">
      <ul>
        <?php
          if( $links_count = count($links['videos'])) {
            for( $counter = 0; $counter < $links_count; $counter++) {                      
              $cid = $links['videos'][$counter]['content_id'];
              $permalink = (!empty($gid)) ? PA::$url .'/media_full_view.php?gid='.$gid.'&amp;type=video&amp;cid='.$cid : PA::$url .'/media_full_view.php?cid='.$cid.'&amp;type=video&amp;media';
              $image = uihelper_resize_mk_img("files/".$links['videos'][$counter]['internal_thumbnail'], 70, 65, "images/video_avail_soon.gif", 'alt="'.$links['videos'][$counter]['title'].'"', RESIZE_FIT);
              //$title = chop_string($links['videos'][$counter]['title'], 19);
        ?>
        <li>
                <a href="<?php echo $permalink;?>">
                <?php echo $image; ?>
                <span><b><?php echo _out($links['videos'][$counter]['title']); ?></b></span><br /></a></li>
         <?php
                      }                    
                    }
                    else {
                      echo '<li>'.__("No videos have been published yet.").'</li>';
                    }
                  ?>                  
       </ul>
</div>
            
<div id="gallery-audios" class="image_list_audio display_false">
  <ul>
    <?php if( $links_count = count($links['audios'])) {
        for( $counter = 0; $counter < $links_count; $counter++) {                      
          $cid = $links['audios'][$counter]['content_id'];
          $permalink = PA::$url . PA_ROUTE_CONTENT . '/cid='.$cid;
          //$title = chop_string($links['audios'][$counter]['title'], 9); ?>
    <li>
      <a href="<?php echo $permalink; ?>">
      <img width="21" height="16" src="<?php echo PA::$theme_url."/images/li_audio.gif";?>" alt="PA" border="0" />
     <span><b><?php echo _out($links['audios'][$counter]['title']); ?></b><br /></span></a></li>
    <?php   }                    
          } else { echo '<li>'.__("No audio has been published yet.").'</li>'; } ?>                  
  </ul>
</div>
</div>
    