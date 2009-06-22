<form name="<?php echo $section?>" method="post" enctype="multipart/form-data" action="">
<?php  
  for ($counter = 0; $counter < $showcased_networks; $counter++) {
  
?>
  <fieldset class="center_box"> 
    <div class="field">
      <h4><label for="multiple_select"><?= __("Showcased Network ") ?><?php echo ($counter + 1)?></label></h4>
      <div class="privacy_selected" id="div_select_gen"></div>
    </div>
    
    <div class="field_medium">
      <h4><label for="slogan"><?= __("Showcased Network URL") ?></label></h4>
      <input type="text" class="text longer" id="network_url_<?php echo
$counter?>" name="network_url[]" value="<?php echo
field_value($networks_data[$counter]['network_url'],
field_value(@$_POST['network_url'][$counter], 'http://'))?>"/>
      <div class="field_text"><?= __("Paste the complete URL of the network here") ?>.</div>
    </div>
    
    <div class="field_bigger">
      <h4><label for="curr_desk_image"><?= __("Showcased Network Image") ?></label></h4>
      <div class="curr_image">
        <?php 
          if (!empty($networks_data[$counter]['network_image'])) {
        ?>
        <?php echo uihelper_resize_mk_img($networks_data[$counter]['network_image'], 90, 90, 'images/default.png', 'alt="PeopleAggregator"')?>
        <?php
          } else {
            echo __('No image selected.');
          }
        ?>
      </div>
    </div>
    
    <div class="field_medium">
      <h4><label for="slogan-sub"><?= __("Caption") ?></label></h4>
      <input type="text" class="text longer" id="caption_<?php echo $counter?>"
name="caption[]" value="<?php echo
field_value($networks_data[$counter]['caption'],
field_value(@$_POST['caption'][$counter], ''))?>" />      
    </div>
    
    <div class="field_medium">
      <h4><label for="upload_desk_image"><?= __("Upload Image") ?></label></h4>
      <input type="file" class="text long" id="network_image_<?php echo $counter?>" name="network_image_<?php echo $counter?>" />
      <input type="hidden" name="current_network_image[]" value="<?php echo field_value($networks_data[$counter]['network_image'], '')?>">
      <div class="field_text">
        <?= __("Uploaded images will be displayed at 145 pixels by 145 pixels.") ?>
      </div>
    </div>    
    
    
  </fieldset>
<?php
  }
?>
    <div class="button_position">      
      <input type="submit" name="submit" value="<?= __("Set Showcased Networks") ?>" />
    </div>
<?php echo $config_navigation_url; ?>
</form>