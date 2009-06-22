<?php
  global $settings_new;
  $i=0;
  //$module_settings['left'] = $settings_new[$page_id]['data']['left'];
  //$module_settings['right'] = $settings_new[$page_id]['data']['right'];
?>
<div id="div_epm0">
  <h1>Configure Network <?php echo ucwords(str_replace("_", " ", $settings_new[$page_id]['page_name']));?></h1>
  <form action="" method="post">
    <fieldset>
    <div class="listtable">
      <table id="tablelist" width="100%" cellpadding="3" cellspacing="3"> 
        <tr>
          <th scope="col"><?= __("Available Modules") ?> </th>
          <th scope="col"><?= __("Enable") ?></th>
          <th scope="col" align="center"><?= __("Show in Left or Right") ?> </th>
          <th scope="col"><?= __("Stacking Order") ?> </th>
       </tr>
      <?php 
      $left_array =  $settings_new[$page_id]['data']['left'];
      $left_user_array = $module_settings['left'];
      if (!empty($left_user_array)) {
        $counter = 0;
        foreach ($left_user_array as $left =>$key_val) { 
          if (!empty($key_val)) {
          $alt_class_name = ($i%2) ? '':' class="alternate"';
          $checked = 'checked="checked"';
          ?>
     <tr<?php echo $alt_class_name;?>>
       <td><?php echo $key_val;?></td>
         
       <td align="center">
         <input type="checkbox" name="mod_left[<?php echo $counter;?>]" value="<?php echo $key_val; ?>" <?php echo $checked;?> />
         <br /></td>
         
       <td align="center">
           <input name="left_module[<?php echo $counter;?>]" type="radio" value="left" <?php echo $checked;?> />
       <label for="radiobutton"><?= __("Left") ?> 
       <input name="left_module[<?php echo $counter;?>]" type="radio" value="right" />
        <?= __("Right") ?></label></td>
        
       <td align="center"><input name="textfield_for_left[<?php echo $counter;?>]" type="text" size="3" maxlength="3"  value="<?php echo $left;?>" /></td>
    </tr>
     
    <?php $i++;
         }
         $counter++; 
       } 
    } ?>
   <?php 
      $right_array =  $settings_new[$page_id]['data']['right']; 
      $right_user_array = $module_settings['right'];
      if (!empty($right_user_array)) {
        $counter = 0;
        foreach ($right_user_array as $right =>$key_val) {
          if (!empty($key_val)) {
          $alt_class_name = ($i%2) ? '':' class="alternate"';
          $checked = 'checked="checked"';
          ?>
     <tr<?php echo $alt_class_name;?>>
       <td><?php echo $key_val;?></td>
         
       <td align="center">
         <input type="checkbox" name="mod_right[<?php echo $counter;?>]" value="<?php echo $key_val; ?>" <?php echo $checked;?> />
         <br /></td>
         
       <td align="center">
           <input name="right_module[<?php echo $counter;?>]" type="radio" value="left" />
        <label for="cola"><?= __("Left") ?>
        <input name="right_module[<?php echo $counter;?>]" type="radio" value="right" <?php echo $checked;?> />
        <?= __("Right") ?></label></td>
        
       <td align="center"><input name="textfield_for_right[<?php echo $counter;?>]" type="text" size="3" maxlength="3" value="<?php echo $right;?>" /></td>
     </tr>
     
    <?php $i++;
         }
         $counter++; 
       } 
    } ?>
  
     <?php $l_count = count($left_user_array);
      for ( $counter = 0; $counter < count($left_array); $counter++) {
       $cond_1 = FALSE;
       $cond_2 = FALSE;
        /* Writing Condition */
        if (empty($left_user_array) ) {
          $cond_1 = TRUE;
        } else {
          $cond_1 = (!in_array($left_array[$counter],$left_user_array)) ? TRUE : FALSE;
        }
        if (empty($right_user_array) ) {
          $cond_2 = TRUE;
        } else {
          $cond_2 = (!in_array($left_array[$counter],$right_user_array)) ? TRUE : FALSE;
        }
        if (empty($left_user_array) && empty($right_user_array)) {
          $all_empty = TRUE;
        }
        if (( $cond_1 && $cond_2 ) || ($all_empty)) {
        /* Adding alternate class */
        $alt_class_name = ($i%2) ? '':' class="alternate"';
        $checked = '';
        ?>
      <tr<?php echo $alt_class_name;?>>
         <td><?php echo $left_array[$counter];?></td>
         
         <td align="center">
           <input type="checkbox" name="mod_left[<?php echo ($counter+$l_count);?>]" value="<?php echo $left_array[$counter]; ?>" />
         <br /></td>
         
         <td align="center">
           <input name="left_module[<?php echo ($counter+$l_count);?>]" type="radio" value="left" />
        <label for="radiobutton"><?= __("Left") ?> 
        <input name="left_module[<?php echo ($counter+$l_count);?>]" type="radio" value="right" />
        <?= __("Right") ?></label></td>
        
        <td align="center"><input name="textfield_for_left[<?php echo ($counter+$l_count);?>]" type="text" size="3" maxlength="3"  value="" /></td>
    </tr>
     
    <?php $i++;
         }
       } 
      ?>
    <?php $r_count = count($right_user_array);
      for ( $counter = 0; $counter < count($right_array); $counter++) {
       $cond_1 = FALSE;
       $cond_2 = FALSE;
        /* Make a condition */
        if (empty($left_user_array) ) {
          $cond_1 = TRUE;
        } else {
          $cond_1 = (!in_array($right_array[$counter],$left_user_array)) ? TRUE : FALSE;
        }
        if (empty($right_user_array) ) {
          $cond_2 = TRUE;
        } else {
          $cond_2 = (!in_array($right_array[$counter],$right_user_array)) ? TRUE : FALSE;
        }
        if (($cond_1 && $cond_2 ) || ($all_empty)) {
        /* Adding alternate class */
        $alt_class_name = ($i%2) ? '':' class="alternate"' ;
        $checked = '';
       ?>
      <tr<?php echo $alt_class_name;?>>
        <td><?php echo $right_array[$counter];?></td>
         
         <td align="center">
           <input type="checkbox" name="mod_right[<?php echo ($counter+$r_count);?>]" value="<?php echo $right_array[$counter]; ?>" />
         <br /></td>
         
         <td align="center">
           <input name="right_module[<?php echo ($counter+$r_count);?>]" type="radio" value="left" />
        <label for="cola"><?= __("Left") ?>
        <input name="right_module[<?php echo ($counter+$r_count);?>]" type="radio" value="right" />
        <?= __("Right") ?></label></td>
        
        <td align="center"><input name="textfield_for_right[<?php echo ($counter+$r_count);?>]" type="text" size="3" maxlength="3" value="" /></td>
     </tr>
   <?php $i++;
        }
     } ?>       
</table>
</div>
<?
$middle_column = serialize($settings_new[$page_id]['data']['middle']);
?>
<input type="hidden" value="<?php echo($settings_new[$page_id]['page_id']);?>" name="page_id" />
<input type="hidden" value='<?php echo $middle_column;?>' name="middle_column" />
<center><input type="submit" value="Save Modules Setting" name="save_mod_setting" /></center>
<?php echo $config_navigation_url; ?>
</fieldset>
</form>
</div>