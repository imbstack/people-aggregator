<?php
    if( $sort_by ) {
      if(isset($_REQUEST['gid'])) {
         $_gid = $_REQUEST['gid'];
         $ajax_call = "javascript: ajax_call_method_for_sorting_group_members('$block_name', '$_gid', '/ajax/ajax_sortby.php', 'sort_users')";
      } else {
         $ajax_call = "javascript: ajax_call_method_for_sorting('$block_name', '', '/ajax/ajax_sortby.php', 'sort_users')";
      }
  ?>        
<select id="sort_users" name="sort_users" onchange="<?=$ajax_call?>">
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
    for ($counter = 0; $counter < count($links['users_data']); $counter++) {
      $class = (( $counter%2 ) == 0) ? 'class="color"': NULL;
  ?>  
  
  <li <?php echo $class?>>
    <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . urlencode($links['users_data'][$counter]['login_name']) ?>">
      <?php echo uihelper_resize_mk_user_img($links['users_data'][$counter]['picture'], 35, 35, 'alt="PA"') ?>
    </a>
        <span>
          <b><?= link_to($links['users_data'][$counter]['display_name'],
			 "user_blog", array("login" => urlencode($links['users_data'][$counter]['login_name']))) ?></b><br/>
          <?= sprintf(__("(%d %ss)"), $links['users_data'][$counter]['no_of_relations'],
          $rel_term) ?>
        </span>
  </li>
  <?php 
    }
  ?>          
</ul>         