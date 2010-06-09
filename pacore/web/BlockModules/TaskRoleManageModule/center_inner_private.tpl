<div class="description"><?= __("In this page you can assign roles to tasks") ?></div>
  <form name = "assigntasks" method="post" >
  <input type = "hidden" name = "totalcount"  value ="<?= count($links); ?>" />
  <input type = "hidden" name = "taskcount"  value ="<?=count($tasklist); ?>" />
  <fieldset class="center_box">
   <?php if ($links) { ?>
     <table cellpadding="3" cellspacing="3" align="center">
      <tr>
        <td><b><?= __("Sr.No.") ?></b></td>
        <td><?= __("Roles") ?></td>
        <? $count =1;
             foreach ($tasklist as $task) {?>
         <td><b><?=$task->name?></b></td>
         <input type = "hidden" name = "taskid<?=$count;?>"  value ="<?=$task->id; ?>" />
        <?$count++; } ?>
        </tr>
        
        <?php $count =1;
           foreach($links as $link ) { ?>
        <?php if($link->id != ANONYMOUS_ROLE) : // TODO: this if() clause should be removed when we implements Anonymous role! ?>   
          <?php echo  '<input type="hidden" name = "link_id'.$count.'"  value = "'.$link->id.'" />' ?>
          <tr class='alternate' style="background:aqua;">
          <tr>
           <td><?=$count;?></td>
           <td><?= $link->name;  ?></td>
           <? 
             foreach ($tasklist as $task) {
               $task_exist =Roles:: is_roletask_exist($link->id, $task->id);     
             ?>
             
           <td><input type="checkbox" name ="<?=$link->id ?>~<?=$task->id?>"  value= "<?=$task->id ?>"  <? if ($task_exist) echo "checked"; ?>/></td>
           <?}?>
           </tr>
        <?php endif; // TODO: this if() clause should be removed when we implements Anonymous role! ?>   
           <?php $count++; }?>
      </table>
      <?php }else {?>
    <div class ="required"><?= __("No roles have been created yet") ?></div>
    <?php }?>
</fieldset>
<div class="button_position"><input type="submit" class="button-submit" name="save" value="<?= __("Save") ?>"  /></div>
<?php echo $config_navigation_url; ?>
</form>