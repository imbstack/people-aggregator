<?php
  $category_selection_list = null;
  $category_list = null;
  if (count($link_categories_array) > 0) {
    $category_selection_list = '<option value="0">- Select List -</option>';
    for ($counter = 0; $counter < count($link_categories_array); $counter++) {
      if ((!empty($_POST['category_id']) && $_POST['category_id'] ==
      $link_categories_array[$counter]->category_id )||
      (!empty($_POST['category_name']) && $_POST['category_name'] ==
      $link_categories_array[$counter]->category_name)) {
        $selected = 'selected="selected"';
        $link_category = $link_categories_array[$counter]->category_id.":".$link_categories_array[$counter]->category_name;
      } else {
        $selected = "";
      }
      
      $category_selection_list .= '<option value="'. $link_categories_array[$counter]->category_id.'" '.$selected.'>'.$link_categories_array[$counter]->category_name.'</option>';
      
      $category_list .= '<div class="field"><h4><label><input type="radio" name="category_id" value="'.$link_categories_array[$counter]->category_id.'" onclick="javascript:ajax_category_links(\'manage_data\', '.$link_categories_array[$counter]->category_id.');"/></label>'.$link_categories_array[$counter]->category_name.'</h4></div>';
    } //end for loop
  } else {
    $category_selection_list = '<option value="0">--<?= __("No link list added") ?>--</option>';
  }
    ?>
    <style>
      .buttonbar {
        height: 20px;
      }
    </style>
<form name="formLinkManagement" id="formLinkManagement">
  <fieldset class="center_box">
    <legend><?= __("Manage your link lists") ?></legend>
    <?php echo $category_list; ?>
    
  
    <div class="button_position">
      <input type="button" name="btn_new_list"  class="buttonbar" value="<?= __("Create new") ?>"  onclick="javascript: list.create(false);" />
      <span id="edit_delete_list_btn" class="display_false">
        <input type="button" name="btn_edit_list" value="<?= __("Edit selected list") ?>"  onclick="javascript: edit_list();" class="buttonbar" />
        <input type="button" name="btn_delete_list" value="<?= __("Delete selected list") ?>"  onclick="javascript: list.remove();" class="buttonbar"  />
      </span>
    </div>
  </fieldset>
  
 
  <center><div class="required" id="error_message"></div></center>
  <div id="edit_list"></div>  
  
  <div id="new_link_category" class="display_false">
    <fieldset class="center_box">
      <legend><?= __("Create a list") ?></legend>      
      <div class="field">
        <h4><label for="category_name"><?= __("New List Name") ?></label></h4>
        <input type="text" name="category_name" id="category_name" class="text longer" />      
      </div>
      
      <div class="button_position">
        <input type="button" name="btn_apply_name" value="<?= __("Save") ?>"  onclick="javascript: list.create(true);" class="buttonbar" />
      </div>    
    </fieldset>
  </div>
  
  <fieldset class="center_box">
    <legend><?= __("Links") ?></legend>
    <input type="hidden" name="form_action" value="" id="form_action" />
   
    <div id="manage_data"><?= __("Links under the selected list will be shown here") ?></div>
  </fieldset>
  
  <center><div class="required" id="error_message_links"></div></center>
  <div id="new_link_in_list" class="display_false">
    <fieldset class="center_box">
      <legend><?= __("Create link") ?></legend> 
      <div class="field">
        <h4><label for="category_name"><?= __("Link caption") ?></label></h4>
        <input type="text" name="title" id="title" class="text longer" />      
      </div>
      
      <div class="field">
        <h4><label for="category_name"><?= __("URL") ?></label></h4>
        <input type="text" name="url" id="url" class="text longer" />
      </div>
      
      <div class="button_position">
        <input type="button" name="btn_apply_name" value="<?= __("Save") ?>" onclick="javascript: list_links.create(true);" class="buttonbar" />
      </div>    
    </fieldset>
  </div>
  
  <div id="edit_list_links"></div>
  
</form>
<form method="post" id="message_form">
  <input type="hidden" value="" name="messages" id="messages" />
  <input type="hidden" value="" name="updated_category_id" id="updated_category_id" />
</form>
<?php
  if(isset($_POST['updated_category_id'])) {
?>
  <script language="javascript" type="text/javascript">
    list.highlight();
  </script>
<?php  
  }
?>
