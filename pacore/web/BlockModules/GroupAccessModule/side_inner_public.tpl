<div class="module_html">
  <a href="<?php echo PA::$url.PA_ROUTE_GROUP?>/gid=<?php echo $group_details->collection_id?>">

      <?php echo uihelper_resize_mk_img($group_details->picture, 170, 170, "images/default_group.gif", 'alt="group image"', RESIZE_FIT)?>
</a>
<br />
<?php echo $group_details->description;?>
<br />
<?php 
  if (!$is_admin && $is_member) {
?>
  <a href="<?php echo PA::$url.PA_ROUTE_GROUP?>/action=leave&amp;gid=<?php echo $group_details->collection_id?>">
  <?php echo __('Leave This Group') ?>
  </a>
<?php 
  }
?>
<? if (!empty($join_this_group_string)) { ?>
  <a href="<?php echo PA::$url.PA_ROUTE_GROUP?>/action=join&amp;gid=<?php echo $group_details->collection_id?>">
   <?php echo $join_this_group_string;?>
  </a>
<?php 
  }
?>
<? if ($is_admin) { ?>
  <a href="<?php echo PA::$url.'/'.FILE_ADDGROUP?>?gid=<?php echo $group_details->collection_id?>">
   <?php echo __('Group Settings')?>    
  </a>
<?php 
  }
?>
</div>