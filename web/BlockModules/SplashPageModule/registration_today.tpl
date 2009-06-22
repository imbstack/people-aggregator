<form name="<?php echo $section?>" method="post" enctype="multipart/form-data">
  <fieldset class="center_box">
    
    <div class="field_bigger">
      <h4><label for="description"><span class="required"> * </span> <?= __("Description") ?></label></h4>
      <textarea name="description" id="description"><?php echo field_value(@$networks_data['description'], field_value(@$_POST['description'], ''))?></textarea>
    </div>
    
    <div class="field_bigger">
      <h4><label for="curr_desk_image"><?= __("Current Image") ?></label></h4>
      <div class="curr_image">
        <?php 
          if (!empty($networks_data['network_image'])) {
        ?>
        <?php echo uihelper_resize_mk_img($networks_data['network_image'], 193, 67, 'images/default.png', 'alt="Network of Moment"')?>
        <?php
          } else {
            echo __('No image selected.');
          }
        ?>
      </div>
    </div>
    
    <div class="field_medium">
      <h4><label for="upload_desk_image"><span class="required"> * </span> <?= __("Upload Image") ?></label></h4>
      <input type="file" class="text long" id="network_image" name="network_image" />
      <input type="hidden" name="current_network_image" value="<?php echo field_value($networks_data['network_image'], '')?>">
      <div class="field_text">
        <?= __("Uploaded image will be displayed at 193 pixels by 67 pixels.") ?>
      </div>
    </div>
    
  </fieldset>
  <div class="button_position">      
    <input type="submit" name="video_tours_submit" value="<?= __("Save") ?>" />
  </div>
<?php echo $config_navigation_url; ?>
</form>