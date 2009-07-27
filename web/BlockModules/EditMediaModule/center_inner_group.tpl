<?php 
      
      if ($media_data->type == '4') {
        $media_type = "image";
        $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media&gid=" . $media_data->parent_collection_id;
        $image_path = $media_data->image_file;
        if (strstr($image_path, "http://")) {
           $image_show = "<img src=\"$image_path\"  alt=\"PA\" height=\"70\" width=\"50\"/>";
        }
        else {
           $path = uihelper_resize_img($image_path, 70, 50, PA::$theme_rel . "/images/header_image.jpg",'alt="image."');
           $croped_path = $path['url'];
           $image_show = "<img src=\"$croped_path\"  alt=\"PA\" />";
      }  
        $file = $media_data->image_file;
      }
      else if($media_data->type == '5') {
        $media_type = "audio";
        $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/view=groups_media&gid=" . $media_data->parent_collection_id;
        $image_path =  PA::$theme_url . "/images/audio_img.jpg";
        $file = $media_data->file_name;
        
        $path = uihelper_resize_img($image_path, 70, 50, PA::$theme_rel . "/images/audio_img.jpg",'alt="image."');
        
        $croped_path = $path['url'];
        $image_show = "<img src=\"$croped_path\"  alt=\"PA\" />";
      }
      else if ($media_data->type == TEK_VIDEO) {
        $media_type = "video";
        $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . "/view=groups_media&gid=" . $media_data->parent_collection_id;
        $image_path =  "files/".$media_data->internal_thumbnail;
        $file = @$media_data->file_name;
        $path = uihelper_resize_img($image_path, 70, 50, PA::$theme_rel . "/images/video_img.jpg",'alt="image."');
        
        $croped_path = $path['url'];
        $image_show = "<img src=\"$croped_path\"  alt=\"PA\" />";
      }
?>
<h1><?= __("Edit") ?> <?php echo ucfirst($media_type);?></h1>
<div id="edit_media">
    <form action="" method="post">
       <fieldset class="center_box">
         <div class="field_bigger">
          
          <?php
            echo $image_show;
           ?>
           <a href="<?php echo PA::$url;?>/media_full_view.php?cid=<?php echo $_GET['cid'];?>"><?= __("Full view") ?></a>
          </div>
       
               
        <div class="field">
          <h4><label for="title"><?= __("Title") ?>:</label></h4>
          <input type="text" class="text longer" name="caption" value="<?=$media_data->title?>" id="title" />
        </div>
          
          <div class="field_bigger">
            <h4><label for="description"><?= __("Description") ?>:</label></h4>
            <textarea id="description" name="body"><?php print $media_data->body; ?></textarea>
          </div>
          
          <div class="field">
            <h4><label for="tags"><?= __("Tags") ?>:</label></h4>
            <input type="text" class="text longer" id="tags" name="tags" value="<?=$media_data->tags?>" />
          </div> 
          </fieldset>
        <div class="button_position" >
          <input type="submit" name="submit_group" value="<?= __("Apply Changes") ?>">
          
          <label><?= __("Or") ?></label><br />
          <label><a href="<?= $ret_url ?>" ><?= sprintf(__("Return to %s gallery"), PA::$group_noun) ?></a></label>
         <input type="hidden" name="media_type" value="<?=$media_type;?>">
         <input type="hidden" name="file_name" value="<?=$file;?>">
         <input type="hidden" name="file_id" value="<?=$media_data->content_id;?>">
         <input type="hidden" name="group_id"  value="<?=$media_data->parent_collection_id;?>">
     </div>
       
      </form>
</div>
