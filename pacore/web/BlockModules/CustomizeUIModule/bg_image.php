<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
* bg_image.php is a part of PeopleAggregator.
* HTML for displaying the background image
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/
include "center_inner_private.tpl";
?>

<div id="background">
  <h1><?=__("Background Image")?></h1>
  <form action="" enctype="multipart/form-data" method="post">
    <fieldset class="center_box">
      <div class="field_bigger" style="height:200px;overflow:auto;">
        <h4><label for="curr_background_image"><?=__("Current Background Image")?></label></h4>
        <span>(displayed at about half size)</span>
        <img src="<?php echo $image_info['url'];?>" alt="<?=__("Current Background Image")?>" style="width:500px;height:auto;"/>
      </div>
                
      <div class="field_medium">
        <h4><label for="upload_background_image"><?=__("Upload Background Image")?></label></h4>
        <input type="file" class="text long" id="upload_background_image" name="network_image" value="" />

         <div class="field_text">
           <?=__("Image as header background will appear on your Networks Pages")?>
         </div>
       </div>
                

      </fieldset>
     
      
  <div class="button_position">
    <input type="hidden" name="form_data[header_image_name]" value="<?=@$backgr_image_name?>" />
    <input type="hidden" name="type" value="bg_image" />
    <input type="hidden" name="uid" value="<?=$uid?>" />
    <input type="hidden" name="gid" value="<?=$gid?>" />
    <input type="hidden" value='<?php echo $settings_type;?>' name="stype" />
    <input type="hidden" value='' name="action" id="form_action" />
    <input type="submit" name="submit" value="<?=__("Apply background image")?>" onclick="javascript: document.getElementById('form_action').value='applyBackgroundImage';" />
    <input type="submit" name="restore_default" value="<?=__("Restore Default")?>" onclick="javascript: document.getElementById('form_action').value='restoreBackgroundImage';" />
  </div>
  </form>
</div>
