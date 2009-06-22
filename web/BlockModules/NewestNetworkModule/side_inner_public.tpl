<?php
    if (!empty($sort_by)) {
  ?>
  <div class="module_icon_list" id="list_groups">
    <select id="sort_groups" name="sort_groups" size="1" onchange="ajax_call_method_for_sorting ('groups', '', 'ajax_sortnetworks.php', 'sort_groups')">
          <option value="created" <?php if($selected_option == 'created') { ?> selected="selected" <? } ?>><?= __("Recently Created") ?></option>
          <option value="changed" <?php if($selected_option == 'changed') { ?> selected="selected" <? } ?>><?= __("Recently Modified") ?></option>
          <option value="members" <?php if($selected_option == 'members') { ?> selected="selected" <? } ?>><?= __("Largest Network") ?></option>
          <option value="0" <?php if(!$selected_option) { ?> selected="selected" <? } ?>>--  <?= __("Sort By") ?> --</option>
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
      if (!empty($links)) {
        $cnt = count($links);
        for ($counter = 0; $counter < $cnt; $counter++) {
    ?>
    <li>
    <a href="http://<? echo $links[$counter]->address .'.' . PA::$domain_suffix . BASE_URL_REL . PA_ROUTE_HOME_PAGE?>" ><?= uihelper_resize_mk_img(trim($links[$counter]->inner_logo_image), 35, 35, PA::$theme_rel."/images/default-network-image.gif") ?></a>
      <span>
        <b>
          <a href="http://<? echo $links[$counter]->address . '.' . PA::$domain_suffix . BASE_URL_REL . PA_ROUTE_HOME_PAGE?>" ><?php echo chop_string(stripslashes($links[$counter]->name), 16);?></a>
        </b>
      <?php echo uihelper_plural($links[$counter]->member_count, "member") ?>
    </span>
    </li>
   <?php
       }
     }
     else {
     ?>
       <li><?php echo __('No network created yet.'); ?></li>
     <?php
     }
   ?>    
  </ul>  
</div>