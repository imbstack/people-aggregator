<form name="<?php echo $section?>" method="post" enctype="multipart/form-data">
 
  <fieldset class="center_box">
    <div class="field_medium">
      <h4><label for="slogan-sub"><span class="required"> * </span> <?= __("Featured User") ?></label></h4>
      <input type="text" class="text longer" id="featured_user_name" name="featured_user_name" value="<?php echo field_value(@$networks_data['featured_user_name'], field_value(@$_POST['featured_user_name'],          ''))?>" />
      <br> The username of the featured user
    </div>
    
    <div class="field_medium">
      <h4><label for="slogan-sub"><span class="required"> * </span> <?= __("Featured Group ID") ?></label></h4>
      <input type="text" class="text longer" id="featured_group_id" name="featured_group_id" value="<?php echo field_value(@$networks_data['featured_group_id'], field_value(@$_POST['featured_group_id'], ''))         ?>" />
      <br> The ID of the featured group
    </div>
    
    <div class="field_medium">
      <h4><label for="description"><span class="required"> * </span> <?= __("Featured Video Link") ?></label></h4>
      <input type="text" class"text longer" id="featured_video_url" name="featured_video_url" value="<?php echo field_value(@$networks_data['featured_video_url'], field_value(@$_POST['featured_video_url'], ''           )) ?>" />
      <br> The URL for the featured video
    </div>

   <div class="field_medium">
      <h4><label for="description"><span class="required"> * </span> <?= __("Featured Business ID") ?></label></h4>
      <input type="text" class"text longer" id="featured_business_id" name="featured_business_id" value="<?php echo field_value(@$networks_data['featured_business_id'], field_value(@$_POST['featured_business_id'], '' )) ?>" />
      <br> The ID for the featured business
    </div>
    
  </fieldset>

  <div class="button_position">
    <input type="submit" name="video_tours_submit" value="<?= __("Save Showcase Module") ?>" />
  </div>
<?php echo $config_navigation_url; ?>
</form>

