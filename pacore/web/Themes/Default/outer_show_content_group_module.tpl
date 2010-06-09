<?php
  $link = PA::$url . PA_ROUTE_GROUP;
  if( !empty( $_GET['uid'] ) ) {
    $query_string = '&amp;uid='.$_GET['uid'];
  }
  else if( !empty( $_GET['gid'] ) ) {
    $query_string = '&amp;gid='.$_GET['gid'];
  }

  $block_heading = ( !empty( $block_heading )) ? $block_heading : __('Community Blog');
?>
<div class="wide_content" id="<?php echo $html_block_id;?>">
<?= $inner_HTML;?>
</div>
  <?php if( $page_links ) { ?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php }  ?>
