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
<form name="groupform" action="<?=PA::$url.'/'.FILE_ADDGROUP?>" method="post" enctype="multipart/form-data" onsubmit="return validate_form();">

    <fieldset class="center_box">
        <div class="field_medium">
          <h4><label for="Group name"><span class="required"> * </span><?= __("Group Name") ?>:</label></h4>
          <input type="text" class="text longer" name="groupname" value="<?php echo $groupname;?>" id="group_name" maxlength="254" />
         </div>
			<?php
			if (!empty($_PA->useTypedGroups)) {
				include("edit_typedgroup_profile.tpl.php");
			}
			?>
        
        <div class="field_medium">
          <h4><label for="Group category"><span class="required"> * </span><?= __("Group Category") ?>:</label></h4>
          <select id="group_category" name="group_category">
          <option value="">Select a category</option>
             <?php
                 foreach ($categories as $category) {
              ?>
              <option value="<?= $category['category_id']?>"
              <?php if ($group_category == $category['category_id']) {print 'selected';}?>>
               <?= $category['name']?>  </option>
               <?php  } ?>
            </select>     
        </div>
        <div class="field_medium">
          <h4><label for="tags"><?= __("Tags") ?>: </label></h4>
          <input class="text longer" type="text" id="tags" name="group_tags" value="<?=$tag_entry?>"/>
          <div class="field_text">
     <?= __("separated tags with commas") ?>
    </div>
          </div>
          
          <div class="field_medium">
          <h4><label for="Group Photo"><?= __("Group Photo") ?>: </label></h4>
          <input type="file" class="text longer" id="Group_photo" name = "groupphoto"/>
          <div class="field_text">
     <?= __("Choose File") ?>
    </div>
          </div>
           
          <?php
            if (!empty($group_photo)) {
          ?>
          <div class="field_bigger">
            <h4><label for="Current_Photo"><?= __("Current Photo") ?></label></h4>
            <?php echo  uihelper_resize_mk_img($group_photo, 100, 100, "images/default.png", 'alt="Group photo."', RESIZE_FIT) ?>
          </div>
          <?php
            }
          ?>
        
          <div class="field_bigger">
            <h4><label for="Group description"><?= __("Group Description") ?>:</label></h4>
            <textarea id="group_description" name="groupdesc" cols="64" rows="5"><?= $body ?></textarea>
          </div>
          <div class="field">
            <h4><label for="registration_type"><?= __("Registration Type") ?>:</label></h4>
            <?php
                if($reg_type == 0) {
                  $open_checked = 'checked="checked"';
                  $moderated_checked = '';
                  $invite_only_checked = '';
                }
                else if($reg_type == 1){
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
         <input type="hidden" name="gid" value="<?= $collection_id?>"/>
         <input type="hidden" name="ccid" value="<?= $collection_id?>"/>
         <input type="hidden" name="file" value="<?= $upfile?>"/>
         <input type="hidden" name="header_file" value="<?= $header_file?>"/>
<!--         
         <input type="hidden" name="is_super_groups_available" value="<?php /* echo $super_groups_available */?>"/>
-->         
         <input type="hidden" name="form_handler" value="AddGroupModule" />
      </fieldset>
    </form>
