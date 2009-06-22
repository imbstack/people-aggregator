<?php
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
      <div class="view_all"><a href="<?php echo $view_all_url?>">view all</a></div>
  <?php 
    }
  ?>
  <?php 
    if ($manage_links_url) {
  ?>   
      <div class="view_all"><a href="<?php echo $manage_links_url?>">Manage Links</a></div>
  <?php 
    }
  ?>
    
</div>

