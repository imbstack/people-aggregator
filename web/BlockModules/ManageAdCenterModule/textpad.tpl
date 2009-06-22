<?php
  // global var $_base_url has been removed - please, use PA::$url static variable

  $ads_pages = Advertisement::get_pages();

  $class = ($edit || !empty($form_data)) ? 'class="display_true"' : 'class="display_false"';
  if (@$_GET['open'] == 1) {
    $class = 'class="display_true"';
  }
  //$class = ($edit || !empty($form_data)) ? 'class="display_true"' : 'class="display_false"';
  $legeng_tag = $edit ? __('Edit Textpad') : __('Create Textpad');
  $ads_list = NULL;
  if (!empty($links)) {
    $ads_list = '<table cellpadding="3" cellspacing="3">
                <tr>
                  <td><b>'.__("Title").'</b></td>
                  <td><b>'.__("Page").'</b></td>
                  <td><b>'.__("Orientation").'</b></td>
                  <td><b>'.__("Enable").'/'.__("Disable").'</b></td>
                  <td><b>'.__("Edit").'</b></td>
                  <td><b>'.__("Delete").'</b></td>
                </tr>';
    foreach ($links as $ad_data) {
      $action = $ad_data->is_active ? 'disable' : 'enable';
      $page_name = NULL;
      foreach( $ads_pages as $key => $value) {
        if ($value['value'] == $ad_data->page_id) {
          $page_name = $value['caption'];
          break;
        }
      }
    $ad_orientation = '('.$ad_data->orientation.')';

      $ads_list .= '<tr><td>'.$ad_data->title .'</td><td>'.$page_name.'</td><td>'.$ad_orientation.'</td><td><a href="'.PA::$url.'/'.FILE_MANAGE_TEXTPADS.'?do='.$action.'&ad_id='.$ad_data->ad_id.'">'.($action == "enable" ? __("Enable") : __("Disable")).'</a></td><td><a href="'.PA::$url.'/'.FILE_MANAGE_TEXTPADS.'?do=edit&amp;ad_id='.$ad_data->ad_id.'" onclick="javascript: showhide_ad_block(\'new_ad\', 0, \''.FILE_MANAGE_TEXTPADS.'\');">'.__('Edit').'</a></td><td><a href="'.PA::$url.'/'.FILE_MANAGE_TEXTPADS.'?action=delete&amp;ad_id='.$ad_data->ad_id.'" onclick="return delete_confirmation_msg(\''.__('Are you sure you want to delete this Textpad').'?\') ">'.__('Delete').'</a></td></tr>';
    }
    $ads_list .= '</table>';
  }
?>
<div class="description">&nbsp;</div>
<form enctype="multipart/form-data" name="formAdCenterManagement" id="formAdCenterManagement" action="" method="POST">
  <fieldset class="center_box">
    <legend><?= __("Available Textpads") ?></legend>
    <?php if ($page_links) {?>
    <div class="prev_next">
      <?php if ($page_first) { echo $page_first; }?>
      <?php echo $page_links?>
      <?php if ($page_last) { echo $page_last;}?>
    </div>
    <?php
      }
    ?>
    <?php echo $ads_list; ?>
    <div class="button_position">
        <input type="button" name="btn_new_ads"  class="buttonbar" value="<?= __("New Textpad") ?>"  onclick="javascript: showhide_ad_block('new_ad','<?php echo @$_GET['open'];?>', '<?php echo FILE_MANAGE_TEXTPADS?>');"/>
    </div>
  </fieldset>
  <div id="new_ad" <?php echo $class; ?>>
    <fieldset class="center_box">
      <legend><?php echo $legeng_tag;echo (!empty($form_data['ad_title'])) ? ': '.$form_data['ad_title'] : NULL ?></legend>
      <div class="field_medium">
        <h4><label for="ad_title"><?= __("Enter Title") ?></label></h4>
        <input type="text" name="ad_title" id="ad_title" class="text longer" value="<?php echo @$form_data['ad_title'];?>" maxlength="30"/>
        <div class="field_text"><?= __("Title should not be greater than 30 characters") ?>.</div>
      </div>
      <div class="field_bigger">
        <h4><label for="ad_description"><?= __("Enter Description") ?></label></h4>
        <textarea name="ad_description" id="ad_description" maxlength="500"><?php echo @$form_data['ad_description'];?></textarea>
      </div>
      <div class="field_medium">
        <h4><label for="ad_page_id"><?= __("Select Page") ?></label></h4>
        <select name="page_id" id="page_id" class="text longer">
          <option value="0"><?= __("Select a page") ?></option>
          <?php foreach ($ads_pages as $pages) { ?>
          <?php if (!empty($form_data['page_id']) && $pages['value'] == $form_data['page_id']) {
                  $selected = "selected=\"selected\"";
                } else {
                  $selected = NULL;
                }
          ?>
          <option value="<?php echo $pages['value'];?>" <?php echo $selected;?>><?php echo $pages['caption'];?></option>
          <?php } ?>
        </select>
      </div>
      <?php
        $x = $y = NULL;
        if (!empty($form_data['orientation'])) {
          list($x, $y) = explode(',', $form_data['orientation']);
        }
      ?>
      <div class="field_medium"><?php $x_options = get_ad_options('horizontal', $x);?>
        <h4><label for="orientation"><?= __("Select Horizontal position") ?></label></h4>
        <select name="x_loc" id="x_loc" class="text">
          <?php echo $x_options;?>
        </select>
      </div>
      <div class="field_medium"><?php $y_options = get_ad_options('vertical', $y);?>
        <h4><label for="orientation"><?= __("Select vertical position") ?></label></h4>
        <select name="y_loc" id="y_loc" class="text">
          <?php echo $y_options;?>
        </select>
      </div>
      <div class="button_position">
      <?php if (!empty($form_data['ad_id'])) { ?>
        <input type="hidden" name="ad_id" value="<?php echo $form_data['ad_id']?>" />
      <?php }?>
        <input type="hidden" name="created" value="<?php echo @$form_data['created']?>" />
        <input type="submit" name="btn_apply_name" value="<?= __("Save") ?>" class="buttonbar" />
      </div>
    </fieldset>
  </div>
<?php echo $config_navigation_url;?>
</form>
