<?php
?>

<div class="module_moderator_info">
  <?= __('Members') ?> <?php echo !empty($group_details->members) ? $group_details->members : '' ;?> <br />
  <?= __('Category') ?>: <?php echo $group_details->category_name;?><br />
  <?= __('Founded') ?>: <?php echo PA::datetime($group_details->created, 'long', 'short'); // date("Y-m-d H:i:s", $group_details->created); ?><br />
  <?= __('Type') ?>: <?php echo !empty($group_details->access_type) ? $group_details->access_type : ''; ;?><br />
  <?php echo !empty($group_details->tag_entry) ? $group_details->tag_entry : '';?><br /> <p></p>
  <div class="box center">
  <?= __('Owner') ?><br />
    <a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $group_details->author_id?>">
    <?php echo !empty($group_details->author_picture) ? uihelper_resize_mk_img($group_details->author_picture, 90, 68, "images/default.png", 'alt="Picture of the group moderator."') : ''; ?></a><br />
    <a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $group_details->author_id?>">         <?php echo !empty($group_details->author_name) ? $group_details->author_name : '' ;?> </a>
  </div>
</div>
