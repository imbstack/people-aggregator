<?php
    if (!empty($sort_by)) {
  ?>
  <div class="module_icon_list" id="list_groups">
    <select id="sort_groups" name="sort_groups" size="1" onchange="ajax_call_method_for_sorting ('groups', '', 'ajax_sortgroups.php', 'sort_groups')">
          <option value="created" selected="selected"><?= __("Recently Created") ?></option>
          <option value="changed" selected="selected"><?= __("Recently Modified") ?></option>
          <option value="members" selected="selected"><?= __("Largest Group") ?></option>
          <option value="0" selected="selected">--  <?= __("Sort By") ?> --</option>
    </select>
  <?php
    } else {
  ?>
  <div class="module_icon_list">
  <?php  
    }
  ?>
  
  <ul class="members">
    <?php 
      $counter = 0;
      if( count($links) ) {
        foreach($links as $link) {
         
    ?>
    <li>
      <a href="<?= PA::$url  . PA_ROUTE_GROUP .  "/gid=" . $link['group_id'] ?>">
      <?= uihelper_resize_mk_img($link['picture'], 35, 35, "images/default_group.gif", 'alt="group image"', RESIZE_CROP)?>
      </a>
      <span>
        <b>
          <a href="<?= PA::$url  . PA_ROUTE_GROUP . "/gid=" . $link['group_id']?>">
          <?= chop_string(stripslashes($link['title']), 17);?>
          </a>
        </b>
	      <br/>
	      <?= sprintf(__("%d members"), $link['members']) ?>
    </span>
    </li>
   <?php
         $counter++;
       }
     }
     else {
     ?>
           <li><?= __("No Groups found.") ?><span> 
    <?= sprintf(__('Click <a href="%s">here</a> to create one.'), PA::$url.'/addgroup.php') ?></span></li>
     <?php
     }
   ?>    
  </ul>  
</div>