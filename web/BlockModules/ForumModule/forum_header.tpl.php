<?php
?>
<div class="forum_header">
  <?php if($message) : ?>
    <div class="<?= $message['class']?>">
      <?= $message['message'] ?>
    </div>
  <?php elseif(@$description) : ?>
    <?= $description ?>
  <?php endif ?> 
</div>