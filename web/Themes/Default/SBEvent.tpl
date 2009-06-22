<div class="events" id="<?php echo $outer_block_id; ?>">
 <?php if (!isset($_GET['gid'])) {?> <h1><?php echo $contents->title; ?></h1>
 <? } else { ?><h2><?php echo $contents->title; ?></h2><? } ?>
  <?=$contents->body; ?> 
  <?php require "common_content_detail.tpl" ?> 
</div>