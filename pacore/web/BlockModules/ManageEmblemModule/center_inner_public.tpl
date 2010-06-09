<?php 
?>
 <script>
    var last_blog = 1;
 </script>
<h1>Manage Emblem </h1>
<form enctype="multipart/form-data" action="manage_emblem.php?uid=<?=$_SESSION['user']['id']?>" method="post" onsubmit="return isValidManageEmbleum('manage_emblem');" name="manage_emblem" >
 <fieldset class="center_box">

 <div id="class_description">
  <?= __("You can upload a gif, jpg, jpeg, png, xpm, bmp, file.") ?> (<?= __("Maximum size") ?> <?=format_file_size($GLOBALS['file_type_info']['image']['max_file_size'])?>). <?= __("Do not upload photos containing cartoons, celebrities, nudity, artwork of copyrighted images.") ?>
<?= __("For better result image dimension  should be 36x191") ?> 
  <!--This div is added for handling addmore Button . if user click on addmore button this div is dynamically added -->
  </div>
  <div id="block" class="block">
  <?php 
    $total=0;
    if (is_array($links)) {
    foreach($links as $k => $v)  {?>
      <div id="my<?PHP echo $total?>Image" >
     <div class="field_big ">
        <h5><label for="select file"><?= __("Select a file to upload") ?>:</label></h5>
          <input name="userfile_<?PHP echo $total;?>" type="file" id="select_file" class="text longer"  />
          <input name="userimage_<?PHP echo $total;?>" type="hidden"  value="<?PHP echo $v['file_name'] ?>"/>
          
          </div>
          <div class="field_big">
           <?php echo uihelper_resize_mk_img($v['file_name'], 191, 36);?>
         </div>
      <div class="field_big" >
        <h5><label for="file url"><?= __("Enter the destination URL of image") ?></label></h5>
        <input name="userfile_url_<?php echo $total;?>" class="text longer" id="file_url" type="text"  value="<?php echo $v['url']; ?>" maxlength="100"/>
      </div>
      <div class="field_big">
        <h5><label for="image title"><?= __("Image title") ?></label></h5>
        <input type="text" name="caption[]"  class="text longer" id="image_title" value="<?php echo htmlspecialchars(@$v['title']); ?>"  maxlength="100"/>
       </div>
      
      </div>
    <?php $total++;} }?>
    </div>
    <input type="hidden" name="total" id="total" value=<?php echo $total ?> />
    </div>
     </fieldset>  
    <div class="field_choose" id="addmore_button">Add More<img src="<?php echo PA::$theme_url;?>/images/plus.gif" alt="Add More" onclick="javascript:addfile('block','image_gallery')"  />
    </div>
  <div class="field_choose" id="addmore_button">Remove<img src="<?php echo PA::$theme_url;?>/images/minus.gif" alt="Add More" onclick="javascript:removefile();"  />
  </div>
 <div class="button_position"><input type="submit" name="submit" value="<?= __("Submit") ?>" /> </div>
<?php echo $config_navigation_url; ?>
 </form>
 