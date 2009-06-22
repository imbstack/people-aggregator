<?php

// global var $_base_url has been removed - please, use PA::$url static variable


?><div id="image_gallery_upload">
<form enctype="multipart/form-data" action="<?= PA::$url?>/upload_media.php?uid=<?=$_SESSION['user']['id']?>&amp;type=Images" method="post">
<fieldset>
  <?= __("You can upload a gif, jpg, jpeg, png, xpm or bmp file") ?>. (<?= __("Maximum size") ?> <?=format_file_size($GLOBALS['file_type_info']['image']['max_file_size'])?>). <?= __("Do not upload photos containing cartoons, celebrities, nudity, artwork or copyrighted images") ?>.

  <!--This div is added for handling addmore Button . if user click on addmore button this div is dynamically added -->
  <div id="image_gallery">
    <div id="block" class="block">
      <div class="field_medium start">
        <h5><label for="select file"><?= __("Select a file to upload, or enter a URL below") ?></label></h5>
          <input name="userfile_0" type="file" id="select_file" class="text long" value="" />
      </div>
          
      <div class="field">
        <h5><label for="file url"><?= __("Image URL") ?></label></h5>
        <input name="userfile_url_<?php echo '0';?>" class="text long" id="file_url" type="text" value="" />
      </div>
          
      <div class="field">
        <h5><label for="image title"><?= __("Image title") ?></label></h5>
        <input type="text" name="caption[0]" value="" class="text long" id="image_title"  />
      </div>
          
      <div class="field_big">
        <h5><label for="description"><?= __("Description") ?></label></h5>
          <span><textarea id="description" name="body[0]"></textarea></span>
      </div>
            
      <div class="field">
        <h5><label for="tags"><?= __("Tags (separate with commas)") ?></label></h5>
        <input type="text" name="tags[0]" class="text long" id="tag" value="" maxlength="255" />
      </div>
     
     <div class="field_medium end">
        <h5><label for="select image"><?= __("Select who can see this image") ?>:</label></h5>
	<div class="right">
          <?php print get_media_access_list('image_perm[0]'); ?>
	</div>
     </div>   
    
    </div>
    
    <div class="field_choose" id="addmore_button"><?= __("Add More") ?><img src="<?php echo $current_theme_path;?>/images/plus.gif" alt="<?= __("Add More") ?>" onclick="javascript:addmedia('block','media_gallery')"  />
          
    <div><?= __("Or finish below") ?>.</div>
  </div> 
          

          
   <div class="field">
     <h5><label for="send album"><?= __("Send to album") ?>:</label></h5>
     <select name="album" class="select-txt">
     <? if (empty($my_all_album)) {
             $my_all_album[0]['name']= $default_name;
        }  ?>
        <?php for ($k=0; $k<count($my_all_album); $k++) { ?>
        <?php if (isset($_GET['album_id']) && $my_all_album[$k]['id'] == $_GET['album_id']) {
                   $selected = " selected=\"selected\" ";
              } else { $selected = null; } ?>
        <option <?=$selected?> value="<?= (!empty($my_all_album[$k]['id'])) ? $my_all_album[$k]['id'] : null ?>"><?=$my_all_album[$k]['name']?></option>
        <?php } ?>
     </select>
   </div>
          
   <div class="field_medium">
     <h5><label for="create album"><?= __("Or create new album") ?>:</label></h5>
     <input type="text"  class="text longer" id="create_album" name="new_album" value="<?=@$_POST['new_album'];?>" />
     <input type="hidden" name="media_type" value="image" />
     <input type="hidden" name="content_type" value="media" />
   </div>
   <input type="submit" class="button-submit" name="submit" value="<?= __("Upload image") ?>" />
   </fieldset>
  
 </form>
</div>
