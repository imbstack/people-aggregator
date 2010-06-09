<?php
// global var $_base_url has been removed - please, use PA::$url static variable

if (is_array($links) && sizeof($links) > 0) {?>
<div class="module_icon_list">
  <ul class="members">
  <?php foreach ($links as $link) { ?>
    <li>
      <a href="<?= PA::$url . PA_ROUTE_GROUP . "/gid=" . $link['id']?>">
        <?php echo uihelper_resize_mk_img($link['picture'], 35, 35, "images/default_group.gif", 'alt="group image"', RESIZE_CROP)?>
      </a>
      <span>
        <b>
          <a href="<?= PA::$url  . PA_ROUTE_GROUP . "/gid=" . $link['id']?>">
            <?php echo chop_string(stripslashes($link['title']), 17);?>
          </a>
       </b><br/>
       <?php echo uihelper_plural($link['members'], ''.__('member').'') ?>
     </span>
   </li>
   <?php } // End of For ?>
  </ul>
</div>
<? } else { //End of If condition ?>
<div class="module_browse_groups">
  <ul>
    <?if ($mode == 'private' || !empty($user_name)) { ?>
      <li><?= sprintf(__('You haven\'t <a href="%s">created</a> or <a href="%s">joined</a> any groups yet.'), PA::$url . "/addgroup.php", PA::$url . PA_ROUTE_GROUPS) ?>
      </li>
      <?php } else { ?>
      <li><span><?= __('User has not joined any groups yet.'); ?></span></li>
    <?php } ?>
  </ul>
</div>
<? } ?>
