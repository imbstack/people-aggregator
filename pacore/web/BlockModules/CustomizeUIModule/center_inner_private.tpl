  <?php
    $active = ' class="active"';
    $query_str = null;
    if($uid) {
      $query_str = "uid=$uid";
    } else if($gid) {
      $query_str = "gid=$gid";
    }
  ?>
  <ul id="filters">
    <li<?php echo ($type == 'theme') ?$active:''; ?>><a href="<?= "$base_url/theme/$query_str" ?>" ><?= __("Theme Selector") ?></a></li>
    <?php if($settings_type == 'network') : ?>
      <li<?php echo ($type == 'bg_image') ?$active:''; ?>><a href="<?= "$base_url/bg_image/$query_str" ?>" ><?= __("Background Image") ?></a></li>
    <?php endif; ?>
    <li<?php echo ($type == 'module') ?$active:''; ?>><a href="<?= "$base_url/module/$query_str" ?>" ><?= __("Module Selector") ?></a></li>
    <li<?php echo ($type == 'desktop_image') ?$active:''; ?>><a href="<?= "$base_url/desktop_image/$query_str" ?>" ><?= __("Desktop image") ?></a></li>
    <li<?php echo ($type == 'style') ?$active:''; ?>><a href="<?= "$base_url/style/$query_str" ?>" ><?= __("Customize Theme") ?></a></li>
  </ul>
