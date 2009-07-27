<?php
?>
  <div class="wrapper">
    <div class="message">
      <?php if(!empty($message)) : ?>
        <?= $message ?>
      <?php endif ?>
    </div>
    <div class="section">
      <?php echo $data->getHtml(); ?>
    </div>
  </div>