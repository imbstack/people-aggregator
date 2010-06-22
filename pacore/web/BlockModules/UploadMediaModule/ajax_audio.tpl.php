<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
  if(!empty($_REQUEST['gid'])) {
    $query_str = '&amp;gid='.$_REQUEST['gid'];
}
elseif(!empty($_REQUEST['uid'])) {
    $query_str = '&amp;uid='.$_REQUEST['uid'];
}
else {
    $query_str = null;
}
?>
<div id="image_gallery_upload">
<form enctype="multipart/form-data" action="<?=PA::$url."/ajax/upload_media.php?type=Audios$query_str"?>" method="POST">
<fieldset>
  <?=__("You can upload an audio file")?>. (<?=__("Maximum size")?> <?=format_file_size($GLOBALS['file_type_info']['audio']['max_file_size'])?>

  <div id="image_gallery">
    <div id="block" class="block">
      <div class="field_medium start">
        <h5><label for="select file"><?=__("Select a file to upload")?></label></h5>
          <input name="userfile_audio_0" type="file" id="select_file" class="text long" value="" />
      </div>

      <div class="field">
        <h5><label for="audio_title"><?=__("Audio title")?></label></h5>
        <input type="text" name="caption_audio[0]" value="" class="text long" id="audio_title"  />
      </div>

   <input type="hidden" name="media_type" value="audio" />
   <input type="hidden" name="content_type" value="media" />
   <input type="hidden" name="audio_perm[0]" value="1" />

   <?php if(!empty($_REQUEST['gid'])) {?>
   <input type="hidden" name="group_id" value="<?=$_REQUEST['gid'];?>" />
   <?
}?>

   <input type="submit" class="button-submit" name="submit_audio" value="Upload audio" />
   </fieldset>

 </form>
</div>
