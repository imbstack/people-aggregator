<?php
    if ( $sort_by ) {
?>
<select id="sort_groups" name="sort_groups" size="1" onchange="ajax_call_method_for_sorting ('groups', '', 'ajax_sortgroups.php', 'sort_groups')">
  <?php    
    for($counter = 0; $counter < count($sorting_options); $counter++) {
      if( $sorting_options[$counter]['value'] == $selected_option) {
        $selected = 'selected="selected"';
      }
      else {
        $selected = NULL;
      }
      
      echo '<option value="'.$sorting_options[$counter]['value'].'" '.$selected.'>'.$sorting_options[$counter]['caption'].'</option>';
    }
  ?> 
  </select>
<?php
  }
?>
<ul class="members">
  <?php 
    $counter = 0;
    if( count( $links ) ) {
      foreach($links as $link) {
       
  ?>
  <li>
    <a href="<?= PA::$url . PA_ROUTE_GROUP . "/gid=" . $link['group_id']?>">
      <?php echo uihelper_resize_mk_img($link['picture'], 35, 35, "images/default_group.gif", 'alt="group image"', RESIZE_CROP)?>
    </a>
    <span>
      <b>
        <a href="<?= PA::$url . PA_ROUTE_GROUP . "/gid=" . $link['group_id']?>">
        <?php echo chop_string(stripslashes($link['title']), 17);?>
        </a>
      </b><br/>
    <?php echo uihelper_plural($link['members'], "member") ?>
    </span>
  </li>
  <?php
      $counter++;
    }
  }
  else {
?>
    <li><?= __("No Groups found.") ?><span> <?= __("Click") ?> <a href="<?php echo PA::$url?>/addgroup.php">here</a> <?= __("to create.") ?></span></li>
<?php
  }
?>
</ul>