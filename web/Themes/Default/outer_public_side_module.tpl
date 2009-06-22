<?php
  global $current_theme_path;
  if ( $html_block_id ) {
     $id = "id=\"$html_block_id\"";
  }
?>
<div class="module" <?php echo $id;?>>
 <?php if($title) {?><h1><img alt="collapse" src="<?php echo $current_theme_path;?>/images/arrow_dn.gif" border="0" height="11" width="11" id="image_<?php echo $html_block_id;?>" /> <?php echo $title;?></h1><? } ?>
 <?php echo $inner_HTML; ?>
  <?php 
    if ($view_all_url) {
  ?>   
      <div class="view_all"><a href="<?php echo $view_all_url?>"><?= __("view all") ?></a></div>
  <?php 
    }
  ?>
</div>