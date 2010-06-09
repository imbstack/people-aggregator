<?php // global var $_base_url has been removed - please, use PA::$url static variable

   
  $size = sizeof($tags_id_name);  
?>
<div class="module_browse_tag">
   <? if ( $size ) {
        $cnt = 1;             
        foreach ( $tags_id_name as $tag ) {
          $url = PA::$url .'/'.FILE_TAG_SEARCH.'?name_string=content_tag&keyword='.$tag['id'];
          $name = _out(stripslashes(chop_string($tag['name'],15))); ?>
            <a href="<?php echo $url;?>"><?php echo $name;?></a>
                <?php if ( $size > $cnt ) { ?>
                  :: 
                <?}?>
              <?php	$cnt++;
            }
      } else {  ?><?= __('Nothing has been tagged yet') ?>. <?}?>				
</div>