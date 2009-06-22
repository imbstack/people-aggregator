<?php // global var $_base_url has been removed - please, use PA::$url static variable
 ?>
<div id="image_gallery_upload">
<form enctype="multipart/form-data" action="<?= PA::$url?>/groupmedia_post.php?type=Videos&amp;gid=<?=$_GET['gid']?>" method="POST">
<fieldset>
  <?= __("You can upload a video file") ?>. (<?= __("Maximum size") ?> <?=format_file_size($GLOBALS['file_type_info']['video']['max_file_size'])?>).
         <!--This div is added for handling addmore Button . if user click on addmore button this div is dynamically added -->
  <div id="image_gallery">
    <div id="block" class="block">
        <div class="field_medium start">
           <h5><label for="select file"><?= __("Select a file to upload") ?></label></h5>
           <input name="userfile_video_0" type="file" id="select_file" class="text long" value="" />
        </div>
          
        <div class="field">
          <h5><label for="image title"><?= __("Video title") ?></label></h5>
          <input type="text" name="caption_video[0]" value="" class="text long" id="image_title"  />
        </div>
          
        <div class="field_big">
          <h5><label for="description"><?= __("Description") ?></label></h5>
            <span><textarea id="description" name="body_video[0]" rows="3" cols="28"></textarea></span>
         </div>
            
        <div class="field">
          <h5><label for="tags"><?= __("Tags (separate with commas)") ?></label></h5>
          <input type="text" name="tags_video[0]" class="text long" id="tag" value="" maxlength="255" />
        </div>
     </div>
          
     <div class="field_choose" id="addmore_videobutton"><?= __("Add More") ?><img src="<?php echo $current_theme_path;?>/images/plus.gif" alt="<?= __("Add More") ?>"  onclick="javascript:addvideomedia('block','group_video')"  />
          
     <div><?= __("Or finish below") ?>.</div>
   </div>
   <input type="hidden" name="content_type" value="media" />
   <input type="hidden" name="media_type" value="video" />
   <input type="hidden" name="group_id" value="<?=$_GET['gid'];?>" />
   <input type="submit" class="button-submit" name="submit_video" value="<?= __("Upload video") ?>" />
   </fieldset>
  
 </form>
</div>