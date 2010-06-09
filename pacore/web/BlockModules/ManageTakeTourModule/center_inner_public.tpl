<?php
  
 $has_links = !empty($links);
?>

<script>
 var last_blog = 1;
</script>

<form enctype="multipart/form-data" action="manage_taketour.php?uid=<?=$_SESSION['user']['id']?>" method="post" onsubmit="return isValidManageEmbleum('manage_taketour');" name="manage_taketour">
 <fieldset class="center_box">
  <h1><?= __("Manage TakeATour") ?></h1>
  <div id="class_description">
   <?= __("You can upload a gif, jpg, jpeg, png, xpm, bmp, file.") ?> (<?= __("Maximum size") ?> 500KB). <?= __("Do not upload photos containing cartoons, celebrities, nudity, artwork of copyrighted images.") ?><br /><br />
  </div>
  <!-- this div is added to handle the addmore button; if the user clicks the addmore button, this div is dynamically added -->
  <div id="image_gallery">
   <div id="block" class="block">
    <div id="my<?php echo @$total ?>Image" >
     <div class="field_big">
      <h4><label ><?= __("Select a file to upload") ?>:</label></h4>
      <input name="userfile_0" type="file" id="select_file" class="text longer" value="" />
      <input name="userimage_0" type="hidden" value="<?= $has_links ? htmlspecialchars($links[0]['file_name']) : ''; ?>" />
     </div>
     <?php if ($has_links) { ?>
      <div class="field_big" style="height: 135px">
       <h4>Current image</h4><?= uihelper_resize_mk_img($links[0]['file_name'], 198, 135) ?>
      </div>
     <?php }?>
     <div class="field_big">
      <h4><label><?= __("Video URL") ?></label></h4>
      <input name="userfile_url_0" class="text longer" id="file_url" type="text"  value="<?php echo ($has_links) ? $links[0]['url'] : ''; ?>" />
     </div>
     <div class="field_bigger">
      <h4><label><?= __("Video description") ?></label></h4>
      <textarea  name="caption[0]" cols="55" rows="5" id="Content"><?php echo ($has_links) ? htmlspecialchars(@$links[0]['title']) : ''; ?></textarea></div>
     </div>
    </div>
   </div>  
  </div>  
 </fieldset>
 <input type="hidden" name="total" id="total" value=0 />
 <div class="button_position">
  <input type="submit" name="submit" value="<?= __("Submit") ?>" /> 
  <?php echo $config_navigation_url; ?>
 </div>
</form>