<?php  
  require_once "web/includes/tinymce.php";
  install_tinymce('full');
  $class = ($edit || !empty($form_data)) ? 'class="display_true"' : 'class="display_false"';
  if (@$_GET['open'] == 1) {
    $class = 'class="display_true"';
  }
  //$class = ($edit || !empty($form_data)) ? 'class="display_true"' : 'class="display_false"';
  $legend_tag = $edit ? __('Edit Static Page') : __('Create Static Page');
  $static_page_data = NULL;
  if (!empty($links)) {
    $static_page_data = '<table cellpadding="3" cellspacing="3">
                <tr>
                  <td><b>'.__("Caption").'</b></td>
                  <td><b>'.__("URL").'</b></td>
                  <td><b>'.__("Page Text").'</b></td>
                  <td><b>'.__("Edit").'</b></td>
                  <td><b>'.__("Delete").'</b></td>
                </tr>';
    foreach ($links as $static_pages) {
      $url = link_to(NULL,
		    "pages_links", array("caption" => $static_pages->url));
      $static_page_data .= '<tr><td>'.$static_pages->caption .'</td>'.'<td>'.$url.'</td><td>'.chop_string($static_pages->page_text, 50).'</td><td><a href="'.PA::$url .'/manage_static_pages.php?do=edit&amp;id='.$static_pages->id.'" onclick="javascript: showhide_ad_block(\'new_ad\', 0, \'manage_static_pages.php\');">'. __("Edit").'</a></td><td><a href="'.PA::$url .'/manage_static_pages.php?action=delete&amp;id='.$static_pages->id.'" onclick="return delete_confirmation_msg(\''.__("Are you sure you want to delete this page?").'\') ">'.__("Delete").'</a></td></tr>';
    }
    $static_page_data .= '</table>';
  }
?>
<form name="formStaticPagesManagement" id="formStaticPagesManagement" action="" method="POST"> 
  <fieldset class="center_box">
    <legend><?= __("Manage Static Pages") ?></legend>
    <?php if (!empty($page_links)) {?>
    <div class="prev_next">
      <?php if (!empty($page_first)) { echo $page_first; }?>
      <?php echo $page_links?>
      <?php if (!empty($page_last)) { echo $page_last;}?>
    </div>
    <?php
      }
    ?> 
    <?php echo $static_page_data; ?>
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
        <input type="button" name="btn_new_ads" class="buttonbar" value="<?= __("Create New Static Page") ?>" onclick="javascript: showhide_ad_block('new_ad','<?php echo @$_GET['open'];?>','manage_static_pages.php');"/>      
    </div>   
  </fieldset> 
  <div id="new_ad" <?php echo $class; ?>>
    <fieldset class="center_box">
      <legend><?php echo $legend_tag; ?></legend>      
      <div class="field_medium">
        <h4><label for="caption"><?= __("Enter Caption") ?></label></h4>
        <input type="text" name="caption" id="caption" class="text longer" value="<?php echo $form_data['caption'];?>" maxlength="30" onKeyUp="javascript:preview_url(this.value, 'preview_url','<?php echo PA::$url;?>')"/><div class="field_text">
        <?= __("Caption should be less than 30 characters.") ?>
       </div><?php if (!$edit) { ?><div id="preview_url" class="required"></div><?php } ?>
      </div>    
      <div class="field_bigger">
        <h4><label for="page_text"><?= __("Enter Page Text") ?></label></h4>
        <textarea name="page_text" id="page_text"><?php echo $form_data['page_text'];?></textarea>      
      </div>
      <div class="button_position">
        <input type="hidden" name="id" value="<?php echo @$form_data['id']?>" />
        <input type="hidden" id="preferred_caption" name="preferred_caption" value="<?php echo @$form_data['preferred_caption']?>"/>
        <input type="submit" name="btn_static_pages" value="<?= __("Save") ?>" class="buttonbar" />
      </div>        
    </fieldset>    
  </div>
</form>