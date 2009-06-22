<?php  
  // global var $_base_url has been removed - please, use PA::$url static variable

  $class = ($edit || !empty($form_data)) ? 'class="display_true"' : 'class="display_false"';
  if (@$_GET['open'] == 1) {
    $class = 'class="display_true"';
  }
  //$class = ($edit || !empty($form_data)) ? 'class="display_true"' : 'class="display_false"';
  $legeng_tag = $edit ? __('Edit Link') : __('Create A Link');
  $footer_link_data = NULL;
  if (!empty($links)) {
    $footer_link_data = '<table cellpadding="3" cellspacing="3">
                <tr>
                  <td><b>'.__("Caption").'</b></td>
                  <td><b>'.__("URL").'</b></td>
                  <td><b>'.__("Enable/Disable").'</b></td>
                  <td><b>'.__("Edit").'</b></td>
                  <td><b>'.__("Delete").'</b></td>
                </tr>';
    foreach ($links as $footer_links) {
      $action = $footer_links->is_active ? 'disable' : 'enable';
      $footer_link_data .= '<tr><td>'.$footer_links->caption .'</td>'.'<td>'.$footer_links->url.'</td><td><a href="'.PA::$url .'/manage_footer_links.php?do='.$action.'&id='.$footer_links->id.'">'.ucfirst($action).'</a></td><td><a href="'.PA::$url .'/manage_footer_links.php?do=edit&amp;id='.$footer_links->id.'" onclick="javascript: showhide_ad_block(\'new_ad\', 0, \'manage_footer_links.php\');">'.__("Edit").'</a></td><td><a href="'.PA::$url .'/manage_footer_links.php?action=delete&amp;id='.$footer_links->id.'" onclick="return delete_confirmation_msg(\''.__("Are you sure you want to delete this link?").'\') "><?= __("Delete") ?></a></td></tr>';
    }
    $footer_link_data .= '</table>';
  }
?>
<div class="description"><?= __("In this page you can manage footer links.") ?></div>
<form name="formFooterLinksManagement" id="formFooterLinksManagement" action="" method="POST"> 
  <fieldset class="center_box">
    <legend><?= __("Manage Footer Links") ?></legend>
    <?php if (!empty($page_links)) {?>
    <div class="prev_next">
      <?php if (!empty($page_first)) { echo $page_first; }?>
      <?php echo $page_links?>
      <?php if (!empty($page_last)) { echo $page_last;}?>
    </div>
    <?php
      }
    ?> 
    <?php echo $footer_link_data; ?>
    <?php if (!empty($page_links)) {?>
    <div class="prev_next">
      <?php if (!empty($page_first)) { echo $page_first; }?>
      <?php echo $page_links?>
      <?php if (!empty($page_last)) { echo $page_last;}?>
    </div>
    <?php
      }
    ?> 
    <div class="button_position">
        <input type="button" name="btn_new_ads"  class="buttonbar" value="<?= __("Create New Footer Link") ?>"  onclick="javascript: showhide_ad_block('new_ad','<?php echo @$_GET['open'];?>','manage_footer_links.php');"/>      
    </div>   
  </fieldset> 
  <div id="new_ad" <?php echo $class; ?>>
    <fieldset class="center_box">
      <legend><?php echo $legeng_tag; ?></legend>      
      <div class="field_medium">
        <h4><label for="caption"><?= __("Enter Caption") ?></label></h4>
        <input type="text" name="caption" id="caption" class="text longer" value="<?php echo $form_data['caption'];?>" maxlength="30"/><div class="field_text">
        <?= __("Caption should be less than 30 characters.") ?>
       </div>
      </div>
      <div class="field_medium">
        <h4><label for="ad url"><?= __("Enter URL") ?></label></h4>
        <input type="text" name="url" id="url" class="text longer" value="<?php echo $form_data['url'];?>" maxlength="200"/>      
      </div>
      <div class="field">
        <?php
        	$is_external = FALSE;
        	if (@$form_data['extra']) {
          	$extra_data = unserialize($form_data['extra']);
          	$is_external = $extra_data['is_external'];
        	}
          $checked = "";
          if ($is_external) {
            $checked = " checked=\"checked\"";
          } 
        ?>
        <input type="checkbox" name="is_external" id="is_external" value="1" <?php echo $checked?>>
          <?= __("Check this, if you want to open link in a different window") ?>
    </div>
      <div class="button_position">
        <input type="hidden" name="id" value="<?php echo @$form_data['id']?>" />
        <input type="submit" name="btn_footer_link" value="<?= __("Save") ?>" class="buttonbar" />
      </div>      
    </fieldset>    
  </div>
<?php /*echo $config_navigation_url*/;?>
</form>