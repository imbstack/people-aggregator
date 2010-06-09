<?php
$content_privacy = 1;
$display_meta = 'style="display:block";';
$ajax_url = PA::$url.'/ajax/ajax_get_album.php';
?>

<link rel="stylesheet" type="text/css" href="<?php echo PA::$url; ?>/Themes/Default/network.css" />
<script type="text/javascript" language="javascript" src="<?php echo PA::$url; ?>/Themes/Default/javascript/jquery.lite.js"></script>
<script type="text/javascript" src="<?php echo PA::$url; ?>/Themes/Default/javascript/jsr_class.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo PA::$url; ?>/Themes/Default/base_javascript.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo PA::$url; ?>/Themes/Default/skins/zen_columns/zen_columns.css">
<script type="text/javascript" language="javascript">var base_url = '<?php echo PA::$url;?>';var CURRENT_THEME_PATH = '<?php echo PA::$url.'/Themes/Default';?>';</script>

<div id = "image_gal1lery_upload" style="width:600px;margin:20;height:auto; float:left;">
  <fieldset >
    You can upload a video file. (Maximum size 100MB).
         <!--This div is added for handling addmore Button . if user click on addmore button this div is dynamically added -->
    <div id="image_gallery">
     <div id="block" class="block_video_form">

        <div class="upload-vids">
           <span><label for="select file">Select a file to upload</label></span>
           <input name="upload" type="file" id="browse_file" class="text long" value="" style="border:solid 1px #ccc; font-size:11px;"/>
        </div>
          
       <div class="upload-vids">
          <span><label for="image title">Video title</label></span>
          <input type="text" name="title" value="" class="text" id="upload_title" maxlength="90" style="border:solid 1px #ccc; font-size:11px;" />
        </div>
          
        <div class="upload-vids">
          <span><label for="description">Description</label></span>
            <textarea  name="description" rows="3" cols="28" class="text" style="border:solid 1px #ccc; font-size:11px;"></textarea>
         </div>
            
        <div class="upload-vids">
          <span><label for="tags">Tags (separate with commas)</label></span>
          <input type="text" name="tag" class="text long" id="tag" value="" maxlength="90" style="border:solid 1px #ccc; font-size:11px;" />
        </div>
        <input name="output" value="v" type="hidden">
	<input type="hidden" id="ajax_url" value="<?php echo $ajax_url;?>" />
    </div>
          
    </div>
    <?php if(empty($_GET['gid'])) { ?>
     <div class="field_medium end">
        <h5><label for="select image"><?= __("Select who can see this image") ?>:</label></h5>
	<div class="right">
          <?php print get_media_access_list('video_perm[0]'); ?>
	</div>
     </div>   
     <div class="upload-vids">
      <span><label for="send album">Send to album:</label></span>
	<b id="album_change"><select name="album_video" class="select-txt">
	<? if (empty($my_all_album)) {
	      $my_all_album[0]['name']= $default_name;
	    }  ?>
	<?php for ($k=0; $k<count($my_all_album); $k++) { ?>
	  <?php if ($my_all_album[$k]['id'] == $_GET['album_id']) {
		  $selected = " selected=\"selected\" ";
	      } 
	      else { $selected = " "; } ?>
	  <option <?=$selected?> value="<?=$my_all_album[$k]['id']?>"><?=$my_all_album[$k]['name']?></option>
	<?php } ?>
    </select></b>
    </div>
          
    <div class="upload-vids">
      <span><label for="create album">Or create new album:</label></span>
      <b><input class="text longer" style="border:solid 1px #ccc; font-size:11px;" id="create_album" type="text" name="new_album_video"  value="<?=@$_POST['new_album_video'];?>"/></b>
      <input type="hidden" id="type_media" name="media_type" value="video" />
      <input type="hidden" name="content_type" value="media" />
    </div>
    <?php } else { ?>
      <input type="hidden" name="group_id" value="<?php echo $_GET['gid'];?>" />
      <input type="hidden" name="album_video" value="<?php echo $_GET['gid'];?>" />
    <?php } ?>

    <div class="button" style="padding-bottom:10px; float:left; padding-left:250px;">
      <input type="submit" class="button-submit" id="uploadButton" name="submit_video" value="Upload"/>
    </div>

  </fieldset>
</div>
<!-- </body> -->