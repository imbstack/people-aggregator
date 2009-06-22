<?php global $current_theme_path?>
<div id="image_gallery_upload">
<form enctype="multipart/form-data" action="<?= PA::$url?>/groupmedia_post.php?type=Images<?php
if (!empty($_REQUEST['gid'])) echo '&amp;gid='.$_REQUEST['gid'] ?>" method="POST">
<fieldset>
  <?= __("You can upload a gif, jpg, jpeg, png, xpm or bmp file") ?>. (<?= __("Maximum size") ?> <?=format_file_size($GLOBALS['file_type_info']['image']['max_file_size'])?>). <?= __("Do not upload photos containing cartoons, celebrities, nudity, artwork or copyrighted images") ?>.

  <div id="image_gallery">
    <div id="block" class="block">
      <div class="field_medium start">
        <h5><label for="select file"><?= __("Select a file to upload") ?></label></h5>
          <input name="userfile_0" type="file" id="select_file" class="text long" value="" />
      </div>
          
      <div class="field">
        <h5><label for="image title"><?= __("Image title") ?></label></h5>
        <input type="text" name="caption[0]" value="" class="text long" id="image_title"  />
      </div>

   <input type="hidden" name="media_type" value="image" />
   <input type="hidden" name="content_type" value="media" />
   <input type="hidden" name="image_perm[0]" value="1" />
   
   <?php if (!empty($_REQUEST['gid'])) { ?>
   <input type="hidden" name="group_id" value="<?=$_REQUEST['gid'];?>" />
   <? } ?>
   
   <input type="submit" class="button-submit" name="submit" value="<?= __("Upload image") ?>" />
   </fieldset>
  
 </form>
</div>
