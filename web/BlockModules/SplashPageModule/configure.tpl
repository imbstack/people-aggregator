<?php
  $checked = null;
  if (!empty($networks_data['show_splash_page']) && $networks_data['show_splash_page'] == ACTIVE) {
    $checked = ' checked="checked"';
  }
?>
<form name="<?php echo $section?>" method="post">
  <fieldset class="center_box">
    
    <div class="field_bigger">
      <h4><label for="description"><?= __("Show Splash Page") ?></label></h4>
      <input type="checkbox" name="show_splash_page" id="show_splash_page" value="1"<?php echo $checked?>>
      <div class="field_text">
        <?= __("If this checkbox is checked then Splash Page will be the first page to be shown to the anonymous users otherwise Homepage will be the first page") ?>.
      </div>
    </div>
        
  </fieldset>
  <div class="button_position">      
    <input type="submit" name="video_tours_submit" value="<?= __("Save") ?>" />
  </div>
<?php echo $config_navigation_url; ?>
</form>