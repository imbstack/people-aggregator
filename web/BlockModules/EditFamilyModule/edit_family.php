<?php
global $_PA;
$image_actions[0] = 
    array('caption'=>'Stretch to fit', 'value'=>DESKTOP_IMAGE_ACTION_STRETCH);
  $image_actions[1] = 
    array('caption'=>'Crop', 'value'=>DESKTOP_IMAGE_ACTION_CROP);
  $image_actions[2] = 
    array('caption'=>'Tile', 'value'=>DESKTOP_IMAGE_ACTION_TILE);
  $image_actions[3] = 
    array('caption'=>'Leave it alone', 'value'=>DESKTOP_IMAGE_ACTION_LEAVE);

?>
<form name="groupform" action="" method="post" enctype="multipart/form-data" onsubmit="return validate_form();">

    <fieldset class="center_box">
        <div class="field_medium">
          <h4><label for="Group name"><span class="required"> * </span><?= __("Family Name") ?>:</label></h4>
          <input type="text" class="text longer" name="groupname" value="<?= @$mod->groupname;?>" id="group_name" maxlength="254" />
         </div>


<style>
.field {
	clear: both;
}
.field .center {
	width: 320px !important;
}
</style>

<?php
$op = (empty($mod->entity)) ? "create_entity" : "edit_entity";
?>
<input type="hidden" name="op" value="<?=$op?>" />
<input type="hidden" name="group_id" value="<?=$mod->gid?>"/>
<?= $mod->dynFields->hidden("name");?>

<?php
	if (@$mod->err) {
		echo '<div class="error">';
		echo _out($mod->err);
		echo '</div>';
	}
?>
<?php
$type = $mod->dynFields->getVal('type');
	$mod->dynFields->hidden("type");
  ?>
  <?php
	foreach ($mod->profilefields as $i=>$field) {
		switch ($field['type']) {
			case 'stateselect':
				$mod->dynFields->select($field['label'], $field['name'], PA::getStatesList());
			break;
			case 'industryselect':
				$mod->dynFields->select($field['label'], $field['name'], $_PA->industries);
			break;
			case 'religionselect':
				$mod->dynFields->select($field['label'], $field['name'], $_PA->religions);
			break;
			case 'countryselect':
				$mod->dynFields->select($field['label'], $field['name'], PA::getCountryList());
			break;
			case 'urltextfield':
				$mod->dynFields->textfield($field['label'], $field['name']);
			break;
			case 'textfield':
				$mod->dynFields->textfield($field['label'], $field['name']);
			break;
			case 'image':
				$mod->dynFields->image($field['label'], $field['name']);
			break;
			case 'dateselect':
				$mod->dynFields->dateselect($field['label'], $field['name']);
			break;
			default:
				echo print_r($field);
			break;
		}
	}


?>


        <div class="field_medium">
          <h4><label for="tags"><?= __("Tags") ?>: </label></h4>
          <input class="text longer" type="text" id="tags" name="group_tags" value="<?=@$mod->tag_entry?>"/>
          <div class="field_text">
     <?= __("separated tags with commas") ?>
    </div>
          </div>
          
          <div class="field_medium">
          <h4><label for="Group Photo"><?= __("Family Photo") ?>: </label></h4>
          <input type="file" class="text longer" id="Group_photo" name="groupphoto"/>
          <div class="field_text">
     <?= __("Choose File") ?>
    </div>
          </div>
           
          <?php
            if (!empty($mod->group_photo)) {
          ?>
          <div class="field_bigger">
            <h4><label for="Current_Photo"><?= __("Current Photo") ?></label></h4>
            <?php echo  uihelper_resize_mk_img($mod->group_photo, 100, 100, "images/default.png", 'alt="Group photo."', RESIZE_FIT) ?>
          </div>
          <?php
            }
          ?>
        
          <div class="field_bigger">
            <h4><label for="About this Family"><?= __("About this Family") ?>:</label></h4>
            <textarea id="group_description" name="groupdesc" cols="64" rows="5"><?= @$mod->body ?></textarea>
          </div>
          <div class="field">
            <h4><label for="registration_type"><?= __("Registration Type") ?>:</label></h4>
            <?php
                if(@$mod->reg_type == 0) {
                  $open_checked = 'checked="checked"';
                  $moderated_checked = '';
                  $invite_only_checked = '';
                }
                else if($mod->reg_type == 1){
                  $open_checked = '';
                  $moderated_checked = 'checked="checked"';
                  $invite_only_checked = '';
                } else {
                   $open_checked = '';
                   $moderated_checked = '';
                   $invite_only_checked = 'checked="checked"';
                }
               ?>
            <input type="radio" id="open" name="reg_type" value='0' <?php echo $open_checked;?> /> <?= __("Open") ?> 
            
            <input  id="moderated" type="radio" name="reg_type" value='1' <?php echo $moderated_checked;?> /> <?= __("Moderated") ?> 
            
            <input id="invite_only" type="radio" name="reg_type" value='2' <?php echo $invite_only_checked;?> /> <?= __("Invite Only") ?>
          </div>        
          
          <br /><div class="button_position">
           <input type="submit" name="addgroup" value="<?= __("Submit") ?>" />
          </div>
         <input type="hidden" name="gid" value="<?= @$mod->collection_id?>"/>
         <input type="hidden" name="ccid" value="<?= @$mod->collection_id?>"/>
         <input type="hidden" name="file" value="<?= @$upfile?>"/>
         <input type="hidden" name="header_file" value="<?= @$mod->header_file?>"/>
         <input type="hidden" name="form_handler" value="AddGroupModule" />
      </fieldset>
    </form>
