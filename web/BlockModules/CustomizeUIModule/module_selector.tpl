<?php
include "center_inner_private.tpl";
$i=0;
?>
 
 <div class="description">
 </div>
 
 <h1>Configure <?php echo $current_selection->page_name;// $current_page->page_name;?></h1>
  <form action="" method="post">
 <fieldset class="center_box">
  <div class="field_big" style="padding: 4px;">
    <h4><label for="form_data_page_id"><?= __('Select page') . ': ' ?></label></h4>
    <div class="center" style="width: 260px;"><?= $select_tag ?></div>
    <div class="field_text"><br>
      <?= __('Select page you want to customize.') ?>
    </div>
  </div>
  <div class="field_big" style="padding: 4px;">
    <h4><label for="form_data_page_id"><?= __('Select page layout') . ': ' ?></label></h4>
    <div class="center" style="width: 260px"><?= $template_select_tag ?></div>
    <div class="field_text"><br>
      <?= __('Select page layout template.') ?>
    </div>
  </div>
  <?php if($side_dissabled != 'both') : ?>
  <div class="field_bigger" style="padding: 4px;">
    <h4><label for="form_data_page_id"><?= __('Add a side module') . ': ' ?></label></h4>
    <div class="center" style="width: 260px"><?= $side_select_tag ?></div>
    <div class="field_text"><br>
      <?= __('Select a side module to add it to your page, chose module placement column, then click "Save settings". ' .
             'Repeat this to add multiple modules.') ?>
    </div>
  </div>
  <?php endif; ?>
  <div class="field_bigger" style="padding: 4px;">
    <h4><label for="form_data_page_id"><?= __('Add a middle module') . ': ' ?></label></h4>
    <div class="center" style="width: 260px"><?= $middle_select_tag ?></div>
    <div class="field_text"><br>
      <?= __('Select a middle module to add it to your page, chose module placement column, then click "Save settings". ' .
             'Repeat this to add multiple modules.') ?>
    </div>
  </div>
 
    <div class="listtable">
      <table id="tablelist" width="100%" cellpadding="3" cellspacing="3"> 
        <tr>
          <th scope="col"><?= __("Available Modules") ?> </th>
          <th scope="col"><?= __("Enable") ?></th>
          <th scope="col" <?= __("Show in Left or Right") ?>align="center"> </th>
          <th scope="col"><?= __("Stacking Order") ?> </th>
       </tr>
      <?php 
      if (!empty($module_settings)) {
        foreach($show_columns as $column) {
          $counter = 0;
          $settings = $module_settings[$column];
          if($settings) {
          foreach($settings as $index => $module_name) {
            $left_side_disabled  = null;
            $right_side_disabled = null;
            $middle_disabled = null;
            if(!empty($module_name)) {
              $alt_class_name = ($i%2) ? '':' class="alternate"';
              $checked = 'checked="checked"';
              if($column == 'middle') {
                $left_side_disabled  = 'disabled="disabled"';
                $right_side_disabled = 'disabled="disabled"';
                $middle_disabled = null;
              } else {
                $middle_disabled = 'disabled="disabled"';
                if($side_dissabled == 'left') {
                  $left_side_disabled = 'disabled="disabled"';
                }
                if($side_dissabled == 'right') {
                  $right_side_disabled = 'disabled="disabled"';
                }
              }
              include "_module_row.tpl";
              $i++;
              $counter++;
            }
          }  
          }
        }
      } ?>
</table>
</div>

<input type="hidden" name="pid" value="<?= $page_id ?>" />
<input type="hidden" name="uid" value="<?= $uid ?>" />
<input type="hidden" name="gid" value="<?= $gid ?>" />
<input type="hidden" value='<?php echo $settings_type;?>' name="stype" />
<input type="hidden" value='' name="action" id="form_action" />
<center>
  <input type="submit" value="Export Modules Setting" name="submit_setting" onclick="javascript: document.getElementById('form_action').value='exportModuleSettings';"/>
  <input type="submit" value="Save Modules Setting" name="submit_setting" onclick="javascript: document.getElementById('form_action').value='saveModuleSettings';"/>
  <input type="submit" value="Restore Default Setting" name="restore_setting" onclick="javascript: document.getElementById('form_action').value='restoreModuleSettings';"/></center>
<?php echo @$config_navigation_url; ?>
</fieldset>
</form>

