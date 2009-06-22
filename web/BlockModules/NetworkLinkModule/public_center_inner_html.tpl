<form name="formLinkManagement" action="" method="POST" >  
<fieldset class="center_box">
      <p>Select the content that will populate the network link module.</p>      
      <p>Manage Link Lists </p>      
      <?php if (count( $link_categories_array) > 0 ) {
        for ($i = 0; $i < count($link_categories_array); $i++){
          $temp_category_id_array[] = $link_categories_array[$i]->category_id;
        }
        $comma_seperated_cat_id = '';
        if (sizeof($temp_category_id_array)) {
          $comma_seperated_cat_id = implode( ',', $temp_category_id_array);
        }
      }?>
      <div class="field">
       <select name="link_categories" id="link_categories" onchange="JavaScript: ajax_category_default_links('manage_data', document.getElementById('link_categories').value); ">
      <?php 
          if (count($link_categories_array) > 0) {
              echo "<option value='0'>- Select List -</option>";
              for ($counter = 0; $counter < count($link_categories_array); $counter++) {
               if (!empty($_POST)) { 
               if ($_POST['category_id'] ==
                $link_categories_array[$counter]->category_id ||
                $_POST['category_name'] ==
                $link_categories_array[$counter]->category_name) {
                  $selected = "selected";
                  $link_category =       
                  $link_categories_array[$counter]->category_id.":"   
                  .$link_categories_array [ $counter]->category_name;
                }  
                } else {
                    $selected = '';
                }
      ?>
      <option value="<?php echo
      $link_categories_array[$counter]->category_id.":".$link_categories_array[
      $counter]->category_name; ?>" <?php echo @$selected; ?>><?php echo
      $link_categories_array[$counter]->category_name; ?></option>
        <?php        
                }
            } else { ?>
               <option value="0">--<?= __("No Link Categories") ?>--</option>
        <?php  } ?>                  
       </select>
       </div>
       <div class="button_position">
     <ul id="manage_link_button">     
     <input type="button" name="btn_new_list" value="New List"  onclick="JavaScript: div_expand_collapse ('new_link_category', 0);" />
     <input type="button" name="btn_edit_list" value="Edit List"  onclick="JavaScript: div_expand_collapse ('new_link_category', 1);" />
     <input type="button" name="btn_delete_list" value="Delete List"  onclick="JavaScript: delete_links (1)" />
    </ul>
    </div>
     <br /><br /><hr /> 
      <div id ="new_link_category" <?php if (!@$_POST['btn_apply_name']) {?> style="display:none;"<?php } ?>>
        <div class="field">
          <input type="text" name="category_name" id="category_name" maxlength="50"/>
        </div>
        <div class="field">
          <input type="submit" name="btn_apply_name" value="<?= __("Save") ?>" onclick="JavaScript: return links_validation (document.formLinkManagement, 1);" />
        </div>
      </div>
    
 
  <div id="manage_link">
  
    <p><?= __("Manage Links") ?></p>
    <div class="button_position">
    <input type="button" name="btn_add_new_link" value="Add Link"  onclick="JavaScript: add_link_expand_collapse ('link_control');" /><input type="button" name="btn_edit_link" value="Edit Link" onclick="JavaScript: edit_links('link_control'); " /><input type="button" name="btn_delete_link" value="Delete Links" onclick="JavaScript: delete_links(0);" /></div>
  
  
  <br /><br /><hr />
  <div id="link_control" <?php if (!@$_POST['btn_save_link']) {?> style="display:none;"<?php } ?>>
    
    <div class="field">
       <?= __("Link Name") ?>:<input type="text" size="61" name="title" value="<?php echo htmlspecialchars(@$_POST['title']); ?>" id='title' />
    </div>   
    <div class="field">
      <?= __("Link") ?>:<input type="text" size="65" name="url" value="<?php if (@$_POST['url']) {echo htmlspecialchars($_POST['url']);} else { echo "http://";}; ?>" id='url' />
    </div>
    
    <div class="button_position">
      <input type="submit" name="btn_save_link"  value="<?= __("Save") ?>" id="btn_save_link" onclick="JavaScript: return links_validation (document.formLinkManagement, 2);" />
    </div>
    
  </div>
  <div id='manage_data'></div> <!-- for ajax implementation-->
  
  <input type="hidden" name="form_action" value="" id="form_action" />
  <input type="hidden" name="category_ids_str" value="<?php echo @$comma_seperated_cat_id; ?>" />
    <?php if (@$link_category) { ?>
      <script language="JavaScript">ajax_category_default_links ('manage_data','<?php echo $link_category?>');</script>
    <?php } ?>
  </div>
  </fieldset>
  <?php echo $config_navigation_url; ?>
</form>