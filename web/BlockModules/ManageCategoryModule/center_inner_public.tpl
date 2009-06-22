<?php  
  // global var $_base_url has been removed - please, use PA::$url static variable

  $class = 'class="display_true"';
  $cnt = count($category);
  foreach ($categories as $catg) {
    $arr_catg[$catg['category_id']] = $catg['name']; 
  }

?>
<div class="description"><?= __("In this page you can manage category on your network") ?></div>
<form enctype="multipart/form-data"  action="" method="POST">
  <fieldset class="center_box">
    <legend><?= __("Manage Category") ?></legend>
      <div class="field_medium">
        <h4><label for="ad url"><?= __("Select Type") ?></label></h4>
        <?php echo uihelper_generate_select_list(array('Default'=>'Default', 'Content'=>'Content'), array("name"=>"type", "onchange"=>"window.location='manage_category.php?type='+this.value"), $type);?>
      </div>
    <div>
    <center>
      <table cellpadding="3" cellspacing="3" border="1px" width="600px">
         <tr>
           <td><b><?= __("Title") ?></b></td>
           <td><b><?= __("Description") ?></b></td>
           <td><b><?= __("Parent") ?></b></td>
           <td><b><?= __("Options") ?></b></td>
         </tr>
        <?php for ($i = 0; $i<$cnt; $i++) { ?> 
          <tr>
           <td><?=$category[$i]->name?></td>
           <td><?=$category[$i]->description?></td>
           <td><?=$category[$i]->parent_name?></td>
           <td><a href="<?= PA::$url?>/manage_category.php?open=1&a=edit&cat_id=<?=$category[$i]->category_id?>"><?= __("Edit") ?></a> / <a href="<?= PA::$url?>/manage_category.php?type=<?=$type?>&a=delete&cat_id=<?=$category[$i]->category_id?>" onclick="return confirm_delete('<?= __("Are you sure you want to delete this category?") ?>')"><?= __("Delete") ?></a></td>
         </tr>
       <?php }?>        
       </table>
      </center>          
    </div>
  </fieldset> 
  <div id="new_cat" <?php echo $class; ?>>
    <fieldset class="center_box">
      <legend><?= __("Create New Category") ?></legend>      
      <div class="field_medium">
        <h4><label for="ad url"><?= __("Parent Category (Optional)") ?></label></h4>
        <?php 
        if (isset($_GET['a']) && $_GET['a']=='edit') {
          echo '<b>'.$parent_name.'</b>';
        }
        else {
        echo uihelper_generate_select_list($arr_catg, array("name"=>"parent_id"), $parent_id);
        }
        ?>
      </div>
      <div class="field_medium">
        <h4><label for="ad url"><?= __("Enter Tilte") ?></label></h4>
        <input type="text" name="cat_title" id="cat_url" class="text longer" value="<?php echo $edit_title;?>" maxlength="200"/>      
      </div>
      
      <div class="field_bigger">
        <h4><label for="ad_description"><?= __("Enter Description") ?></label></h4>
        <textarea name="cat_description" id="cat_description" maxlength="500"><?php echo $edit_desc;?></textarea>      
      </div>
      <div class="button_position">
        <input type="hidden" name="category_id" value="<?=$cat_id?>" />
        <input type="submit" name="submit" value="<?= __("Save") ?>" class="buttonbar" />
      </div>
    </fieldset> 
     <?php echo $config_navigation_url; ?>   
  </div>
</form>