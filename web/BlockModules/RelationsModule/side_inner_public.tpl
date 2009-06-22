<?php

$lower_rel = strtolower($rel_term);

?><div class="module_icon_list">
<ul class="members">
  <?php 
  if (!empty($links)) { 
    $i = 0;
    foreach($links as $link) {
      $class = (( $i%2 ) == 0) ? ' class="color"': NULL;
      ++$i;
   ?>
  <li<?php echo $class?>><a href="<?=User::url_from_id($link['user_id']);?>">
  <?= uihelper_resize_mk_user_img($link['picture'], 35, 35, 'alt="facewall"') ?>
	<span> <b>
   <?=$link['display_name']; ?>
  </b><br />
  <?= _n(";%d $lower_rel"."s
0;No $lower_rel"."s
1;One $lower_rel", $link['no_of_relations']) ?></span></a></li>
	<?} } else { ?>
  <li><span><?= __("No $lower_rel"."s made yet."); ?></span></li>
  <?php } ?>
</ul>
</div>
