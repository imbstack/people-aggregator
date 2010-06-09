<form method="post" enctype="multipart/form-data">
<fieldset class="center_box">
<?php 
  if (is_array($parameters)) {
    foreach ($parameters as $param) {
      $post_name = "param_".$param["id"];
      if (!empty($error)) {
        $param["point"] = htmlentities($_POST[$post_name]);
      }
      echo '<div class="field_medium">
              <h4><label class="text longer" for="'.$post_name.'"><span class="required"> * </span>'.$param["name"].'</label></h4>
              <input type="text" class="text" name="'.$post_name.'" id="'.$post_name.'" value="'.$param["point"].'" maxlength="10"/>
              <div>'.$param["description"].'</div>
            </div>';
    }
    echo '<div class="button_position">
            <input type="submit" name="submit_ranking" value="Submit" />
          </div>';
  }
  else {
    echo '<div class="field_text">
            <?= __("No ranking parameter defined.") ?>
          </div>';
  }
?>
</fieldset>
<?php echo $config_navigation_url;?>
</form>