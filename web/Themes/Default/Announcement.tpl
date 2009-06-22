<div class="blog" id="<?php echo $outer_block_id; ?>">
 <?php if (!$_GET['gid']) {?> <h1><?php echo $contents->title; ?></h1>
 <? } else { ?><h2><?php echo $contents->title; ?></h2><? } ?>
  <?php echo $contents->body; ?>
  <br />
  <?php echo 'Announced at '.date('D M d Y h:m a',$contents->created);?>
</div>