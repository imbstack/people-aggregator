<?php
//  global $current_route;

  $all_post_params = $_GET;
  unset($all_post_params['post_type']);
  //  unset($all_post_params['page_id']);

  // To display network owner specified title on home page.
  $extra = unserialize(PA::$network_info->extra);
  $block_heading_net= @$extra['network_group_title'];

  $block_heading = (!empty( $block_heading)) ? $block_heading : $block_heading_net;
?>
<div class="wide_content" id="<?php echo $html_block_id;?>">
    <h1><?php echo $block_heading?></h1>
    <div class="date"><?php echo PA::date(time(), 'long') // date("l, F j, Y")?></div>
  <?php echo $inner_HTML;?>
</div>
  <?php if( $page_links ) { ?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php }  ?>
