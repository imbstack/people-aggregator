<?php
  if (!empty($_REQUEST['gid'])) {
  	$ads_pages = Advertisement::get_pages('group');
  } else {
  	$ads_pages = Advertisement::get_pages();
  }
  
  if (!empty($_REQUEST['gid'])) {
  	$thisurl = PA::$url.PA_ROUTE_GROUP_AD_CENTER.'?gid='.$_REQUEST['gid'].'&';
  } else {
  	$thisurl = PA::$url.PA_ROUTE_MANAGE_AD_CENTER.'?';
  }
  

  $class = ($edit || !empty($form_data)) ? 'class="display_true"' : 'class="display_false"';
  if (@$_GET['open'] == 1) {
    $class = 'class="display_true"';
  }
  //$class = ($edit || !empty($form_data)) ? 'class="display_true"' : 'class="display_false"';
  $legend_tag = $edit ? __("Edit Ad") : __("Create An Ad");
  $ads_list = NULL;
  if (!empty($links)) {
    $ads_list = '<table cellpadding="3" cellspacing="3">
                <tr>
                  <td><b>'.__("Title").'</b></td>
                  <td><b>'.__("Image").'</b></td>
                  <td><b>'.__("Page").'</b></td>
                  <td><b>'.__("Orientation").'</b></td>
                  <td><b>'.__("Display count").'</b></td>
                  <td><b>'.__("Hit count").'</b></td>
                  <td><b>'.__("Enable/Disable").'</b></td>
                  <td><b>'.__("Edit").'</b></td>
                  <td><b>'.__("Delete").'</b></td>
                </tr>';
    foreach ($links as $ad_data) {
      $action = $ad_data->is_active ? 'disable' : 'enable';
      foreach( $ads_pages as $key => $value) {
        if ($value['value'] == $ad_data->page_id) {
          $page_name = $value['caption'];
          break;
        }
      }
    $ad_orientation = '('.$ad_data->orientation.')';

      $ads_list .= '<tr><td>'.$ad_data->title .'</td>'.'<td>'. uihelper_resize_mk_img($ad_data->ad_image, 40, 40,'images/default.jpg','alt="No Image Selected" align="left" style="padding: 0px 12px 12px 0px;"', RESIZE_FIT).'</td><td>'.$page_name.'</td><td>'.$ad_orientation.'</td>
      <td>'.$ad_data->display_count.'</td>
      <td>'.$ad_data->hit_count.'</td>
      <td><a href="'.$thisurl.'action='.$action.'&ad_id='.$ad_data->ad_id.'">'.($action == "enable" ? __("Enable") : __("Disable")).'</a></td>
      <td><a href="'.$thisurl.'action=edit&amp;ad_id='.$ad_data->ad_id.'" onclick="javascript: showhide_ad_block(\'new_ad\', 0, \'manage_ad_center.php\');">'.__("Edit").'</a></td>
      <td><a href="'.$thisurl.'action=delete&amp;ad_id='.$ad_data->ad_id.'" onclick="return delete_confirmation_msg(\''.__("Are you sure you want to delete this Ad?").'\') ">'.__("Delete").'</a></td>
      </tr>';
    }
    $ads_list .= '</table>';
  }
?>
<div class="description"><?= __("In this page you can manage ads on your network") ?></div>
<form enctype="multipart/form-data" name="formAdCenterManagement" id="formAdCenterManagement" action="<?=$thisurl."action=save"?>" method="POST">
  <fieldset class="center_box">
    <legend><?= __("Manage Ads") ?></legend>
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
        <input type="button" name="btn_new_ads"  class="buttonbar" value="<?= __("Create New Ad") ?>"  onclick="javascript: showhide_ad_block('new_ad','<?php echo @$_GET['open'];?>', '<?=$thisurl?>');"/>
    </div>
  </fieldset>
  <div id="new_ad" <?php echo $class; ?>>
    <fieldset class="center_box">

      <legend><?php echo $legend_tag; ?></legend>
      <div class="field_medium">
        <h4><label for="ad_title"><?= __("Enter Title") ?></label></h4>
        <input type="text" name="ad_title" id="ad_title" class="text longer" value="<?php echo $form_data['ad_title'];?>" maxlength="30"/>
        <div class="field_text"><?= __("Title should not be greater than 30 characters") ?>.</div>
      </div>
      <div class="field_medium">
        <h4><label for="ad name"><?= __("Select An Image") ?></label></h4>
        <input type="file" name="ad_image" class="text" id="ad_image"/>
        <div class="field_text"><?= __("Preferred size") ?> <?php echo AD_WIDTH_LR ;?>x<?php echo AD_HEIGHT_LR ;?> <?= __("for side modules and") ?>
        <?php echo AD_WIDTH_MIDDLE; ?>x<?php echo AD_HEIGHT_MIDDLE;?> <?= __("for center modules") ?></div>
      </div>
      <?php if ($edit) {?>
      <div class="field_bigger">
        <h4><label for="ad pre image">
        <input type="hidden" name="edit_image"  value="<?php echo $form_data['ad_image'];?>"><?= __("Previously Selected Image") ?></label></h4>
        <?php echo uihelper_resize_mk_img($form_data['ad_image'], 400, 80,'images/default.jpg','alt="No Image Selected" align="left"', RESIZE_FIT)?>
      </div>
      <?php } ?>
      <div class="field_medium">
        <h4><label for="ad url"><?= __("Enter URL") ?></label></h4>
        <input type="text" name="ad_url" id="ad_url" class="text longer" value="<?php echo $form_data['ad_url'];?>" maxlength="200"/>
      </div>
      <div class="field_bigger">
        <h4><label for="ad script"><?= __("Or Enter JavaScript(.js code)/ Html code") ?></label></h4>
        <textarea name="ad_script" id="ad_script"><?php echo $form_data['ad_script'];?></textarea>
      </div>
      <div class="field_bigger">
        <h4><label for="ad_description"><?= __("Enter Description") ?></label></h4>
        <textarea name="ad_description" id="ad_description" maxlength="500"><?php echo $form_data['ad_description'];?></textarea>
      </div>
      <div class="field_medium">
        <h4><label for="ad_page_id"><?= __("Select Page") ?></label></h4>
        <select name="ad_page_id" id="page_id" class="text longer">
          <option value="0"><?= __("Select a page") ?></option>
          <?php foreach ($ads_pages as $pages) { ?>
          <?php if ($pages['value'] == $form_data['ad_page_id']) {
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
      <div class="field_medium"><?php $x_options = $mod->get_ad_options('horizontal', $x);?>
        <h4><label for="orientation"><?= __("Select Horizontal position") ?></label></h4>
        <select name="x_loc" id="x_loc" class="text">
          <?php echo $x_options;?>
        </select>
      </div>
      <div class="field_medium"><?php $y_options = $mod->get_ad_options('vertical', $y);?>
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
<?php if (empty($_REQUEST['gid'])) echo $config_navigation_url;?>
</form>