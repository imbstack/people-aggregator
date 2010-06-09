<?php
  global $settings_new;
  $i=0;  
?>
     <tr<?php echo $alt_class_name;?>>
       <td><?php echo $module_name;?></td>
         
       <td align="center">
          <input type="checkbox" name="form_data[<?=$column?>][<?php echo $counter;?>][name]" value="<?php echo $module_name; ?>" <?php echo $checked;?> />
          <br />
       </td>
         
       <td align="center">
          <input name="form_data[<?=$column?>][<?php echo $counter;?>][column]" type="radio" value="left" <?php echo ($column == 'left') ? $checked : null;?> <?= $left_side_disabled ?>/>
          <?= __("Left") ?>
          <input name="form_data[<?=$column?>][<?php echo $counter;?>][column]" type="radio" value="right" <?php echo ($column == 'right') ? $checked : null;?>  <?= $right_side_disabled ?>/>
          <?= __("Right") ?>
          <input name="form_data[<?=$column?>][<?php echo $counter;?>][column]" type="radio" value="middle" <?php echo ($column == 'middle') ? $checked : null;?>  <?= $middle_disabled ?>/>
          <?= __("Middle") ?>
       </td>
        
       <td align="center"><input name="form_data[<?=$column?>][<?php echo $counter;?>][position]" type="text" size="3" maxlength="3"  value="<?php echo $counter;?>" /></td>
    </tr>
