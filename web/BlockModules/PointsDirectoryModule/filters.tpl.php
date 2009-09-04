<?php
?>
<ul id="filters">
  <?php foreach($categories as $cat) : ?>
    <li class="<?= ($cat == $category) ? 'active' : ''?>"><a href="<?= PA::$url . PA_ROUTE_POINTS_DIRECTORY . "?category=$cat" . (($user_id) ? "&uid=$user_id" : "") ."&fid=$fid"?>"><?= __(ucfirst($cat)) ?></a></li>
  <?php endforeach; ?>
</ul>
