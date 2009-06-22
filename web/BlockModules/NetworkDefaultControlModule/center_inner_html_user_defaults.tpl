<?php
?>
<form enctype="multipart/form-data" method="POST" action="" >
<?php global $current_theme_path;
global $network_info; 
$option = $form_data['extra']['user_defaults']['desktop_image']['option'];
?>
<fieldset class="center_box">
<div class="wrapper">
  <div class="section">
  <div id="class_description">
  <h2><?= __("New User Account Defaults") ?> </h2>
  <p>Select the content that will populate all new user accounts when they are
    set-up. </p>
  <h3><?= __("Network friends") ?>: </h3>
  <?php 
        if( $ack_message ) { 
          echo   '<li>'.$ack_message .'</li>';
        }
     ?>
    <?php
      $data_array = null;
      $checked = '';
      if(!empty($form_data['extra']['user_defaults']['user_friends'])) {
        $data_array = explode(',',$form_data['extra']['user_defaults']['user_friends'],2);
        if( $data_array[0] == $_SESSION['user']['name'] ) {
          $data_array = @$data_array[1];
          $checked = 'CHECKED';
        } else {
          $data_array = $form_data['extra']['user_defaults']['user_friends'];
          $checked ='';
        }
      }  
    ?>
    </div>
    <div class="field">
      <input type="checkbox" name="relate_me" value="1" <?php echo $checked;?>/>
      <?= __("I appear as a friend in all new accounts") ?><br />
      <br />
    </div>
    <div class="field_bigger">
    <?= __("These users appear as friends in all new accounts.") ?><br />
    <input name="user_friends" type="text" size="60" value="<?php echo  $data_array;?>"/><br />
      <?= __("(enter screen names of registered network users, separated by commas)") ?> 
    </div>
    <div id="class_description">
  <h3><?= __("Desktop Image") ?>:</h3><?
      list($rsz_opts) = uihelper_explain_desktop_image_action($option);
    ?>
      <?= uihelper_resize_mk_img(@$form_data['extra']['user_defaults']['desktop_image']['name'], 550, 100, PA::$theme_rel."/images/default_desktop_image.jpg", 'alt="desktop image"', $rsz_opts); ?>
        <br/>
      <?= __("Current default desktop image") ?><br />
        <?= __("(Images 1016 pixels wide by 191 pixels tall will have the best results)") ?><br />
        <br />
       </div>
      <div class="field_medium">
      <?= __("Upload default desktop image") ?>
      
        <input type="file" name="desktop_image"/>
        <br />
           
      </div>
      <div class="field">
        <input name="header_image_option" type="radio" value="<?php echo DESKTOP_IMAGE_ACTION_STRETCH;?>" <?php if($option==DESKTOP_IMAGE_ACTION_STRETCH)  echo 'checked="checked"';  ?> />Stretch to fit
       
        <input name="header_image_option" type="radio" value="<?php echo DESKTOP_IMAGE_ACTION_CROP;?>" <?php if($option==DESKTOP_IMAGE_ACTION_CROP)  echo 'checked="checked"';  ?> />Crop
       
        <input name="header_image_option" type="radio" value="<?php echo DESKTOP_IMAGE_ACTION_TILE;?>" <?php if($option==DESKTOP_IMAGE_ACTION_TILE)  echo 'checked="checked"';  ?> />Tile 
       
        <input name="header_image_option" type="radio" value="<?php echo DESKTOP_IMAGE_ACTION_LEAVE;?>" <?php if($option==DESKTOP_IMAGE_ACTION_LEAVE)  echo 'checked="checked"';  ?> />Leave it alone<br />
        
        </div>
        <div class="field_medium">
          <h4><label for="set_image"><?= __("Set desktop image turn on/off") ?></label></h4>
          <?php 
             $dia =
             @$form_data['extra']['user_defaults']['desktop_image']['display'];
             if ($dia == DESKTOP_IMAGE_DISPLAY) {
                  echo '<input type="radio" name="desktop_image_display" value="1" checked="checked"/>'.__("Turn on").' ';
                  echo '<input type="radio" name="desktop_image_display" value="2"/>'.__("Turn off").' ';
                  } else {
                  echo '<input type="radio" name="desktop_image_display" value="1" />'.__("Turn on").' ';
                  echo '<input type="radio" name="desktop_image_display" value="2" checked="checked"/> '.__("Turn off").' ';
                }               
               ?>
         <div class="field_text">
           <?= __("Here to set the Desktop image is display turn on or off.") ?>
          </div>
      </div>
      
    <div id="class_description">
  <h3><?= __("Media Gallery Images") ?>:</h3>
  
    <?= __("Select an image album(s) from your gallery to include in new user default galleries. (An album in your gallery may contain one or more images.)") ?><br />
      <a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . '/uid=' . PA::$login_uid ?>" target="_blank"><?= __("Upload to gallery now") ?></a> <br />
      <br />

</div>
&nbsp;
<div id="field_bigger">
         <select name="multiple_images[]" multiple="multiple" style='width:335px;height:75px'>
       <?php if ( count($user_albums['images']) >= 1 ) {
         $collection_id_array = explode(',',$form_data['extra']['user_defaults']['default_image_gallery']);
         for($counter = 0; $counter < count($user_albums['images']); $counter++) {  
           $selected = "";
           if( in_array($user_albums['images'][$counter]['collection_id'], $collection_id_array)) { //if the current album is set as a network settings
             $selected = "selected=\"selected\"";
           } else {
             $selected = "";
           }
         ?>
           
           <option value="<?php echo $user_albums['images'][$counter]['collection_id']; ?>" <?php echo $selected;?>><?php echo chop_string($user_albums['images'][$counter]['description'], 20);?></option>
         <?php }//for end
              } //if end
         else {?>
        <option value="">----- No image in default user galleries ------</option>
       <?php } ?>
     </select>
    
    <br/>
     <?= __("For multiple selections hold down the &lt;Ctrl&gt;key PC or the &lt;Command&gt; key(Mac) while clicking the desired selections.") ?>
    </div>
    
    </ul>
  <div id="class_description">
  <h3><?= __("Media Gallery Audio") ?>:</h3>
  <?= __("Select an audio album(s) from your gallery to include in new user default galleries. (An album in your gallery may contain one or more audio clips.)") ?><br />
          <a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . '/uid=' . PA::$login_uid ?>" target="_blank"><?= __("Upload to gallery now") ?></a> <br />
          <br />
   
    </div>
    &nbsp;
    <div id="field_bigger">
    
     <select name="multiple_audios[]" multiple="multiple" style='width:335px;height:75px'>
       <?php if ( count($user_albums['audios']) >= 1 ) {
         $collection_id_array = explode(',',$form_data['extra']['user_defaults']['default_audio_gallery']);
         for($counter = 0; $counter < count($user_albums['audios']); $counter++) { 
         $selected = "";
           if( in_array($user_albums['audios'][$counter]['collection_id'], $collection_id_array)) {//if the current album is set as a network settings
             $selected = "selected=\"selected\"";
           } else {
             $selected = "";
           } ?>
           <option value="<?php echo $user_albums['audios'][$counter]['collection_id']; ?>" <?php echo $selected;?> ><?php echo chop_string($user_albums['audios'][$counter]['description'], 20);?></option>
         <?php }//for end
              } //if end
         else {?>
        <option value="">----- No audio in default user galleries ------</option>
       <?php } ?>
     </select>
     <br/>
        <?= __("For multiple selections hold down the &lt;Ctrl&gt;key PC or the &lt;Command&gt; key(Mac) while clicking the desired selections.") ?>
      </div>
   <div id="class_description">
  <h3><?= __("Media Gallery Video") ?>:</h3>
  Select a video album(s) from your gallery to include in new user default
      galleries. (An album in your gallery may contain one or more video clips.)<br />
          <a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . '/uid=' . PA::$login_uid ?>" target="_blank">Upload to gallery now</a> <br />
         <br />
      </div>
    &nbsp;
    <div id="field_bigger">
    
     <select name="multiple_videos[]" multiple="multiple" style='width:335px;height:75px'>
       <?php if ( count($user_albums['videos']) >= 1 ) {
         $collection_id_array = explode(',',$form_data['extra']['user_defaults']['default_video_gallery']);
         for($counter = 0; $counter < count($user_albums['videos']); $counter++) { 
         $selected = "";
           if( in_array($user_albums['videos'][$counter]['collection_id'], $collection_id_array)) {//if the current album is set as a network settings
             $selected = "selected=\"selected\"";
           } else {
             $selected = "";
           } ?>
           <option value="<?php echo $user_albums['videos'][$counter]['collection_id']; ?>" <?php echo $selected;?> ><?php echo chop_string($user_albums['videos'][$counter]['description'], 20);?></option>
         <?php }//for end
              } //if end
         else {?>
        <option value="">----- No video in default user galleries ------</option>
       <?php } ?>
     </select>
       <br/>
       <?= __("For multiple selections hold down the &lt;Ctrl&gt;key PC or the &lt;Command&gt; key(Mac) while clicking the desired selections.") ?>
      </div>
  </ul>
  &nbsp;
  <div id="field_bigger">
  <h3><?= __("Default Blog") ?>:</h3>
  <?= __("Select a post to include in new user default.") ?> <br />
         <select name="default_blog">
        <option value="0"> Select a Blog</option>
        <?php $count_content = count($content);
          for ($counter = 0; $counter < $count_content; $counter++) {
          $selected = '';
          if ($content[$counter]['content_id'] 
               == (int)$form_data['extra']['user_defaults']['default_blog']) { 
           $selected = "selected=\"selected\"";
         } else {
            $selected ='';
         }?> 
        <option value="<?php echo $content[$counter]['content_id']?>" <?php echo $selected;?>><?php 
        echo $content[$counter]['title']?></option>
        <?php } ?>
      </select>
    
    </div><br />
   
 <!-- <h3><?= __("Links") ?>:</h3>
  <ul>
    <li><?= __("Manage link lists") ?><br />
      <br />
    </li>
    <li>
      <select name="select">
        <option>-- <?= __("Select category") ?> --</option>
        <option><?= __("Search Engines") ?></option>
        <option><?= __("Ect...") ?></option>
      </select>
      <br />
      <br />
    </li>
    <li>
      <input type="submit" name="Submit2" value="<?= __("New List") ?>" />
      &nbsp;
      <input type="submit" name="Submit2" value="<?= __("Edit List") ?>" />
      &nbsp;
      <input type="submit" name="Submit2" value="<?= __("Delete List") ?>" />
    </li>
  </ul>
  <ul>
    <li>Manage links<br />
      <br />
      <br />
    </li>
    <li>
      <input type="submit" name="Submit2" value="<?= __("Add Link") ?>" />
      &nbsp;
      <input type="submit" name="Submit2" value="<?= __("Edit Link") ?>" />
      &nbsp;
      <input type="submit" name="Submit2" value="<?= __("Delete Link") ?>" />
    </li>
    <li><dl><dt>LinkName.com</dt><dd>http://www.linkname.com</dd>
      <dt>LinkName.com</dt><dd>http://www.linkname.com</dd>
      <dt>LinkName.com</dt><dd>http://www.linkname.com</dd></dl></li>
  </ul>
  
  <br>
  <br>-->
  
  </fieldset>
  <div class="button_position">    
    <input type="hidden" name="config_action" id="config_action_1" value="save" />
    <input type="submit" name="submit" value="<?= __("Save Settings") ?>"/>
    <input name="submit" type="submit" value="<?= __("Restore Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='restore_defaults';"/>
    <input name="submit" type="submit" value="<?= __("Store as Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='store_as_defaults';" />
  </div> 
 </form>
  <form method="post" action="" enctype="multipart/form-data">
  <fieldset class="center_box">  
   <div class="field_biger" style="float:left; width:50%; border-right:1px solid silver">
    <div style="float:left;">
     <h4><label for="slogan-sub"><?= __("Load / restore User Account Defaults from local disk") ?></label></h4>
     <input name="local_file" id="local_file" type="file" value="" />
    </div>
    <div class="field_text"  style="width:50%">
      <p><?= __('Select XML configuration file.') ?></p>
    </div>
   </div>
   <div class="field_biger" style="float:left; margin-left:12px">
     <h4><label><?= __("Select action") . ": " ?></label></h4>
     <input type="hidden" name="config_action" id="config_action_2" value="load_general_settings" />
     <input name="submit" type="submit" value="<?= __("Load User Account Defaults") ?>" /><br />
   </div>
  </fieldset>
  </form>
</div>
 