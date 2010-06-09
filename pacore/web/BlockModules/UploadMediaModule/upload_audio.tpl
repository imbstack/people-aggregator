<?php

// global var $_base_url has been removed - please, use PA::$url static variable


?><div id="image_gallery_upload">
<form enctype="multipart/form-data" action="<?= PA::$url?>/upload_media.php?uid=<?=$_SESSION['user']['id']?>&amp;type=Audios" method="post">
 <fieldset>
    <?= __("You can upload an audio file") ?>. (<?= __("Maximum size") ?> <?=format_file_size($GLOBALS['file_type_info']['audio']['max_file_size'])?>)
         <!--This div is added for handling addmore Button . if user click on addmore button this div is dynamically added -->
    <div id="image_gallery">
    <div id="block" class="block">
          
    
          <div class="field_medium start">
            <h5><label for="select file"><?= __("Select a file to upload, or enter a URL below") ?></label></h5>
            <input  name="userfile_audio_0" type="file" id="select_file" class="text long" value="" />
          </div>
          
          <div class="field">
            <h5><label for="file url"><?= __("Audio URL") ?></label></h5>
            <input name="userfile_audio_url_0" class="text long" id="file_url" type="text" value="" />
          </div>
          
          <div class="field">
            <h5><label for="image title"><?= __("Audio title") ?></label></h5>
            <input type="text" name="caption_audio[0]" value="" class="text long" id="image_title"  />
          </div>
          
            <div class="field_big">
              <h5><label for="description"><?= __("Description") ?></label></h5>
                <span><textarea id="description" name="body_audio[0]" rows="3" cols="28"></textarea></span>
            </div>
            
            <div class="field">
            <h5><label for="tags"><?= __("Tags (separate with commas)") ?></label></h5>
            <input type="text" name="tags_audio[0]" class="text long" id="tag" value="" maxlength="255" />
          </div>
          <div class="field_medium end">
            <h5><label for="select image"><?= __("Select who can listen to this Audio") ?>:</label></h5>
            <div class="right">
              <?php print get_media_access_list('audio_perm[0]'); ?>
            </div>
          </div>
          
          </div>
          
          <div class="field_choose" id="addmore_audiobutton"><?= __("Add More") ?><img src="<?php echo PA::$theme_url;?>/images/plus.gif" alt="<?= __("Add More") ?>"  onclick="javascript:addaudiomedia('block','media_audio')"  />
          
          <div><?= __("Or finish below") ?>.</div>
          </div>
          

          
          <div class="field">
            <h5><label for="send album"><?= __("Send to album") ?>:</label></h5>
             <select name="album_audio" class="select-txt">
              <? if (empty($my_all_album)) {
                   $my_all_album[0]['name']= $default_name;
                 }  ?>
              <?php for ($k=0; $k<count($my_all_album); $k++) { ?>
                <?php if (isset($_GET['album_id']) && $my_all_album[$k]['id'] == $_GET['album_id']) {
                       $selected = " selected=\"selected\" ";
                    } 
                    else { $selected = " "; } ?>
               <option <?=$selected?> value="<?= (!empty($my_all_album[$k]['id'])) ? $my_all_album[$k]['id'] : null ?>"><?=$my_all_album[$k]['name']?></option>
             <?php } ?>
          </select>
         </div>
          
          <div class="field_medium">
            <h5><label for="create album"><?= __("Or create new album") ?>:</label></h5>
            <input class="text longer" id="create_album" type="text" name="new_album_audio" value="<?=@$_POST['new_album_audio'];?>" />
             <input type="hidden" name="content_type" value="media" />
             <input type="hidden" name="group_id" value="<?=@$_GET['gid'];?>" />   
             <input type="hidden" name="media_type" value="audio" />
          </div>
          <input type="submit" name="submit_audio" value="<?= __("Upload audio") ?>" />
        </fieldset>
  
 </form>
</div>
