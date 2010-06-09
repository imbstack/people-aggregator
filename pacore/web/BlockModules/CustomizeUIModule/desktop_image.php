<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
include "center_inner_private.tpl";
?>

<div id="desktop">        
  <h1><?= __("Desktop Image") ?></h1>
  <form action="" enctype="multipart/form-data" method = "post">

    <fieldset class="center_box">
      <div class="field_bigger">
        <h4><label for="curr_desk_image"><?= __("Current Desktop Image") ?></label></h4>
        <img src="<?php echo $image_info['url'];?>" width="422" height="71" alt="PA" />
      </div>
                
      <div class="field_medium">
        <h4><label for="upload_desk_image"><?= __("Upload Desktop Image") ?></label></h4>
        <input type="file" class="text long" id="upload_desk_image" name="header_image" value="" />

         <div class="field_text">
           <?php if($settings_type == 'user') : ?>
             <?= __("Image will appear on your Personal Pages") ?>
           <?php elseif($settings_type == 'group') : ?>
             <?= __("Image will appear on your Groups Pages") ?>
           <?php else : ?>
             <?= __("Image will appear on your Networks Pages") ?>
           <?php endif; ?>
         </div>
       </div>
                
       <div class="field_medium">
       <h4><label for="set_image"><?= __("Set image appearance") ?></label></h4>
          <input name="form_data[header_image_option]" type="radio" value="<?php echo DESKTOP_IMAGE_ACTION_STRETCH;?>" <?php if($option==DESKTOP_IMAGE_ACTION_STRETCH)  echo 'checked="checked"';  ?> /><?= __("Stretch to fit") ?>
          <input name="form_data[header_image_option]" type="radio" value="<?php echo DESKTOP_IMAGE_ACTION_CROP;?>" <?php if($option==DESKTOP_IMAGE_ACTION_CROP)  echo 'checked="checked"';  ?> /><?= __("Crop") ?>
          <input name="form_data[header_image_option]" type="radio" value="<?php echo DESKTOP_IMAGE_ACTION_TILE;?>" <?php if($option==DESKTOP_IMAGE_ACTION_TILE)  echo 'checked="checked"';  ?> /><?= __("Tile") ?>
          <input name="form_data[header_image_option]" type="radio" value="<?php echo DESKTOP_IMAGE_ACTION_LEAVE;?>" <?php if($option==DESKTOP_IMAGE_ACTION_LEAVE)  echo 'checked="checked"';  ?> /><?= __("Leave it alone") ?><br />

          <div class="field_text">
            <?= __("Images 1016 pixels wide by 191 pixels tall will have the best results") ?>
          </div>
        </div>
        <div class="field_medium">
            <h4><label for="set_image"><?= __("Set Desktop image Turn on/off") ?></label></h4>
             <?php 
             if ($dia == DESKTOP_IMAGE_DISPLAY) {
                  echo '<input type="radio" name="form_data[desktop_image_display]" value="1" checked="checked"/>'.__("Turn on").' ';
                  echo '<input type="radio" name="form_data[desktop_image_display]" value="2"/>'.__("Turn off").' ';
                  } else {
                  echo '<input type="radio" name="form_data[desktop_image_display]" value="1" />'.__("Turn on").' ';
                  echo '<input type="radio" name="form_data[desktop_image_display]" value="2" checked="checked"/> '.__("Turn off").' ';
                }
                
                                
               ?>
         <div class="field_text">
           <?= __("Here to set the Desktop image is display turn on or off.") ?>
          </div>
      </div>
      </fieldset>
     
      
  <div class="button_position">
    <input type="hidden" name="form_data[header_image_name]" value="<?= @$header_image_name ?>" />
    <input type="hidden" name="type" value="desktop_image" />
    <input type="hidden" name="uid" value="<?= $uid ?>" />
    <input type="hidden" name="gid" value="<?= $gid ?>" />
    <input type="hidden" value='<?php echo $settings_type;?>' name="stype" />
    <input type="hidden" value='' name="action" id="form_action" />
    <input type="submit" name="submit" value="<?= __("Apply desktop image") ?>" onclick="javascript: document.getElementById('form_action').value='applyDesktopImage';" />
    <input type="submit" name="restore_default" value="<?= __("Restore Default") ?>" onclick="javascript: document.getElementById('form_action').value='restoreDesktopImage';"/>
  </div>
  </form>
</div>