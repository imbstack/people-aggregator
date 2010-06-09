<div class="post_info">
  <?php  if( $contents->tag_entry ) { echo $contents->tag_entry.'<br />'; }?>
  <?= sprintf(__('posted by %s on %s'),
        chop_string($contents->author_name),
        $contents->create_time
      ) ?>
  <a href="<?php echo $permalink;?>"><?= __("permalink") ?></a> | 
  <a href="<?php echo $permalink;?>"><?= __("comments") ?> (<?php echo $contents->no_of_comments?>)</a>
  <div class="col_end"></div>
</div>