<?php
?>
<div class="blog" id="<?php echo $outer_block_id; ?>">
 <h1><?php echo $contents->title; ?></h1>
    <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $contents->author->user_id?>"><?= uihelper_resize_mk_user_img($contents->author->picture, 32, 32, 'style="margin: 0 11px 0 0;float:left;" alt=".$contents->author_name."'); ?></a>
  <?php echo "<div class='blog_inner'>".$contents->body."</div>"; ?>
  <br style="clear:both" />
  <?php require "common_content_detail.tpl" ?> 
</div>
