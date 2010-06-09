<?php
  
  switch(ucfirst($media_type)) {
    case 'Image': $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=" . $media_data->author_id;  break;
    case 'Audio': $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/uid=" . $media_data->author_id; break;
    case 'Video': $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . "/uid=" . $media_data->author_id; break;
    default:
      $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=" . $media_data->author_id;
  }
?>
<h1> <?= __("Edit") ?> <?php echo ucfirst($media_type);?></h1>
<div id="edit_media">
   <form action="" method="post">
      <fieldset class="center_box">
        <div class="field_bigger">
        <?= uihelper_resize_mk_img($image_path, 70, 50, PA::$theme_rel . "/images/header_image.jpg",'alt="image."') ?>
           <a href="<?php echo PA::$url;?>/media_full_view.php?cid=<?php echo $_GET['cid'];?>">Full view</a>
          
        </div>
        
        <div class="field">
          <h4><label for="Group category"><?= __("Select") ?> <?php echo $media_type;?> <?= __("Access") ?>:</label></h4>
          <?php 
        $perm_name = strtolower($media_type).'_perm';
        echo get_media_access_list($perm_name, $media_data->file_perm); 
          ?>
       </div>
        
        <div class="field">
          <h4><label for="title"><?= __("Title") ?>:</label></h4>
          <input type="text" class="text longer" name="caption" value="<?=$media_data->title?>" id="title" />
        </div>
          
          <div class="field_bigger">
            <h4><label for="description"><?= __("Description") ?>:</label></h4>
            <textarea id="description" name="body"><?php print $media_data->body;?></textarea>
          </div>
          
          <div class="field">
            <h4><label for="tags"><?= __("Tags") ?>:</label></h4>
            <input type="text" class="text longer" id="tags" name="tags" value="<?=$media_data->tags?>" />
          </div>
          
          <div class="field">
            <h4><label for="select"><?= __("Select") ?>:</label></h4>
            <select name="album">
            <?php 
              for ($counter = 0; $counter < count($links); $counter++) { 
              if ($links[$counter]['collection_id'] == $media_data->parent_collection_id) {
                  $selected = " selected=\"selected\" ";
              }
              else {
                  $selected = NULL;
              }
            ?>
              <option <?=$selected?> value="<?=$links[$counter]['collection_id']?>"><?=stripslashes($links[$counter]['description'])?></option>
            <?php
            }
            ?>
            </select>
            
      <label><?= __("in which your") ?> <?php echo $media_type;?> <?= __("file will appear...") ?></label>
          </div>
          <label><?= __("Or") ?></label>
          
          <div class="field">
            <h4><label for="tags"><?= __("Create New Album") ?>:</label></h4>
            <input type="textbox" name="new_album" class="text longer"
            id="text1" value="<?=@$_POST['new_album'];?>" />
          </div>
           </fieldset>
        <div class="button_position" >
          <input type="submit" name="submit" value="Apply Changes">
          <label><?= __("Or") ?></label><br />
          <label><a href="<?= $ret_url ?>"><?= __("Return to media gallery") ?></a></label>
        <input type="hidden" name="media_type" value="<?php echo strtolower($media_type);?>">
        <input type="hidden" name="file_name" value="<?php echo $media_data->file_name;?>">
        <input type="hidden" name="file_id" value="<?php echo $media_data->content_id;?>">
        <?php if( $contentcollection_type == 1 ) { // For Groups ?>
          <input type="hidden" name="group_id"  value="<?=$media_data->parent_collection_id;?>">
        <?php } ?>
       </div>
      </form>
</div>
