<?php if ($display)  {
       $style = "";
     } else {
       $style ='class="display_false"';
    } 
 ?>
<div class="description"><?= __("In this page you can manage different admin roles") ?></div>
<form action=""  class="inputrow" method = "post">
<div id="role" <?=$style?> >
</div>
</form>
<form action=""  class="inputrow" method = "post">
<fieldset class="center_box" style="margin-left: 20px;">

   <?php if ($links) { ?>
     <table cellpadding="3" cellspacing="3" width ="100%">
      <tr>
        <td><b><?= __("Sr.No.") ?></b></td>
        <td><b><?= __("Name") ?></b></td>
        <td><b><?= __("Description") ?></b></td>
        <td><b><?= __("Type") ?></b></td>
        <td><b><?= __("Action") ?></b></td>
      </tr>
      
      <?php $count =1;
          foreach($links as $link ) {?>
       <?php // if($link->id != ANONYMOUS_ROLE) : // TODO: this if() clause should be removed when we implements Anonymous role! ?>
          <tr class='alternate' style="background:aqua;">
          <tr>
           <td><?=$count;?></td>
           <td><?= $link->name;  ?></td>
           <td><?=chop_string($link->description,100); ?></td>
           <td><?= $link->type;  ?></td>
           <?php if(!$link->read_only) : ?> 
             <td id="role_<?=$link->id ?>">
                <a href="#" onclick="javascript: roles_edit.editrole('<?=$link->id ?>');">Edit</a> |
                <a href="#" onclick="javascript: if(confirm('<?= __("Are you sure?") ?>')) {roles_edit.delrole('<?=$link->id ?>');}; return false;"><?= __("Delete") ?></a>
             </td>
           <?php else:?>
             <td>
                <a href="#" onclick="javascript: roles_edit.editrole('<?=$link->id ?>');">Edit</a>
             </td>
           <?php endif;?>
           </tr>
        <?php // endif; // TODO: this if() clause should be removed when we implements Anonymous role! ?>   
           <?php $count++; }?>
      </table>
      <?php }else {?>
    <div class ="required"><?= __("No roles have been created yet.") ?></div>
    <?php }?>
</fieldset>
<div class="button_position">
  <input type="button" class="button-submit" name="submit" value="<?= __("Add New Role") ?>"  onclick="roles_edit.showrole();" />
  <input type="hidden" name="action" id="config_action_1" value="" />
  <input name="submit" type="submit" value="<?= __("Restore Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='restoreRoleToDefaults';"/>
  <input name="submit" type="submit" value="<?= __("Store as Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='storeRolesAsDefaults';" />
</div>
</form>
  </form>
  <form method="post" action="" enctype="multipart/form-data">
  <fieldset class="center_box">  
   <div class="field_biger" style="float:left; width:50%; border-right:1px solid silver">
    <div style="float:left;">
     <h4><label for="slogan-sub"><?= __("Load / restore Roles Settings from local disk") ?></label></h4>
     <input name="local_file" id="local_file" type="file" value="" />
    </div>
    <div class="field_text"  style="width:50%">
      <p><?= __('Select XML configuration file.') ?></p>
    </div>
   </div>
   <div class="field_biger" style="float:left; margin-left:12px">
     <h4><label><?= __("Select action") . ": " ?></label></h4>
     <input type="hidden" name="action" id="config_action_2" value="loadRolesSettings" />
     <input name="submit" type="submit" value="<?= __("Load Roles Settings") ?>" /><br />
   </div>
  </fieldset>
  </form>
<?php echo $config_navigation_url; ?>
