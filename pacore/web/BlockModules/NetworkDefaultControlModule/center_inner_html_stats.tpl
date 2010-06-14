<?php 

$image_info =  uihelper_resize_img(@$form_data['inner_logo_image'], 70, 60, PA::$theme_rel . "/images/default-network-image.gif");
    
$mother_network_info = Network::get_mothership_info();
$extra = unserialize($mother_network_info->extra);
?>
<div id="div_epm1">  
  <form method="post" action="" enctype="multipart/form-data">
  <fieldset class="center_box">  
    <?php if ($is_edit) { ?>
      <div class="very_bigger">
        <h4><label for="multiple_select">Network Statistics</label></h4>
        <div class="field_text">
          <ul>
            <li><?php echo PA::$url ;?></li>
            <li><?php echo _n(';%d registered users
1;1 registered user', $network_stats['registered_members_count']);?></li>
            <li><?php echo _n(';%d groups
1;1 group', $network_stats['groups_count']);?></li>
            <li><?php echo _n(';%d posts
1;1 post', $network_stats['contents_count']);?></li>
            <li><?php echo _n(';%d registered online users
1;1 registered online user', $network_stats['online_members_count']);?></li>
          </ul>
         </div>
       </div>
  <?php }  ?>
  <?php
    if ( !$is_edit ) { ?>
    <div class="field_big">
      <h4><label for="slogan"><?= __("Network Address") ?>: <span class="required">*</span></label></h4>
      <input type="text" id="address" name="address" value="<?php echo @$form_data['address'];?>" maxlength="10" class="text longer" />
      <div class="field_text">
        <?= sprintf(__("Network address will be part of the URL of your network, e.g. <code>http://<b>abc</b>.%s/</code>. It should be less than 10 characters."), PA::$domain_suffix) ?>
       </div>
    </div>
    
    <div class="field">
      <h4><label for="slogan"><a href='javascript: return void();' onclick="javascript: return  ajax_check_network_availability('availability', 'address'); "><?= __("Check Availability") ?></a> </label></h4>
     <div id="availability"></div>
   </div> 
    <input type="hidden" name="form_handler" value="NetworkDefaultControlModule" />
    <input type="hidden" name="operation" value="create_network" />
  <? } ?>
  <div class="field_big">
    <h4><label for="slogan"><?= __("Network Title/Heading") ?><span class="required"> *</span></label></h4>
    <input type="text"  size="60" name="name" value="<?php echo htmlspecialchars(@$form_data['name']);?>" maxlength="100" class="text longer" />
    <div class="field_text">
      <?= __("Title appears on network pages.  For best results, keep below 60 characters (max 100 characters).") ?>
    </div>
  </div>
  <div class="field_big">
    <h4><label for="slogan"><?= __("Network Sub Title") ?>:<span class="required"> *</span></label></h4>
    <input type="text" size="60" name="tagline" value="<?php echo htmlspecialchars(@$form_data['tagline']);?>" maxlength="150" class="text longer" />
    <div class="field_text">
      <?= __("The sub-title will be displayed below the Network Title.") ?>
    </div>
  </div>
  
	<div class="field_big">
    <h4><label for="category"><?= __("Network Category") ?><span class="required"> *</span></label></h4>
      <select name="category">
        <option value=""> -- Select Category --</option>
          <?php if(count($categories) > 0) { 
            for($counter = 0; $counter < count($categories); $counter++) {
              $selected = "";
              if($categories[$counter]['category_id'] == @$form_data['category']) {
                $selected = 'selected="selected"';
              }
          ?>
          <option value="<?php echo $categories[$counter]['category_id']?>" <?php echo $selected; ?>><?php echo $categories[$counter]['name']?></option>
          <?php   } // End of For loop 
          } //end of If condition ?>
      </select>
    <div class="field_text">
      <?= __("This category is used to organise the Network Directory.") ?>
    </div>
  </div>
                
  <div class="field_bigger">
    <h4><label><?= __("Network Description") ?> <span class="required">*</span></label></h4>    
    <textarea name="desc" id="textarea"><?php echo htmlspecialchars(@$form_data['desc']);?></textarea>   
    <div class="field_text">
      <?= __("A short paragraph or two describing your network.") ?>
    </div>
  </div>
  
  <div class="field_big">
    <h4><label><?= __("Default Network Language") ?></label></h4>
    <select name="default_language" class="text">
      <?php foreach($available_languages as $value => $name) { ?>
        <option value="<?=$value ?>" <?=(($value == @$form_data['default_language']) ? "selected=\"selected\"" : null)?> ><?=$name?></option>
      <?php } ?>
    </select>
   <div class="field_text">
      <?= __("Select the Network default language") ?>
    </div>
  </div>
  
  <div class="field_big">
    <h4><label for="slogan-sub"><?= __("Community Blog Title") ?></label></h4>
    <input type="text" name="network_group_title" value="<?php echo htmlspecialchars(@$form_data['network_group_title']);?>" maxlength="50" class="text longer" />
    <div class="field_text">
      <?= __("The Network group title will be displayed on the homepage.") ?>
    </div>
  </div>
  
  <div class="field_big">
    <h4><label for="slogan-sub"><?= __("Network Icon") ?></label></h4>
    <input type="file" name="inner_logo_image"/>
    <div class="field_text">
      <?= __("The icon will appear in the network listing.") ?>
    </div>
  </div>
  <?php if ( $is_edit ) { ?>
   <div class="field_bigger">
    <h4><label for="slogan-sub"><?= __("Current Icon") ?></label></h4>
    <img src="<?php echo $image_info['url'];?>" width="70" height="60" alt="PA" />
    
    <div class="field_text">
      <?= __("Ideal size 70 x 60 pixels") ?>
    </div>
  </div>
  <? }?>
  <div class="field">
    <input type="checkbox" name="network_content_moderation" value="<?php echo NET_YES;?>"
    <?php if (!empty($form_data) && @$form_data['network_content_moderation'] == NET_YES) {
    echo 'checked="checked"'; }?> /> <?= __("Content moderation is required.") ?>
  </div>
  <?php if (@$form_data['type'] != MOTHER_NETWORK_TYPE) { ?>
  <div class="field_big">
    <h4><label for="slogan-sub">Network Type</label></h4>
    Public <input type="radio" name="type" value="<?php echo REGULAR_NETWORK_TYPE;?>" <?php if (@$form_data['type']==REGULAR_NETWORK_TYPE){echo 'checked="checked"';}?> />
    Private <input type="radio" name="type" value="<?php echo PRIVATE_NETWORK_TYPE;?>" <?php if (@$form_data['type']==PRIVATE_NETWORK_TYPE){echo 'checked="checked"';}?> />
    
    <div class="field_text">
      Public network can be joined by any member. For private networks member has to seek permission of network owner.
    </div>
  </div>
  <?php }?>
  <div class="field">
    <input type="checkbox" name="show_people_with_photo" value="<?php echo NET_YES;?>"
    <?php if (@$form_data['show_people_with_photo'] == NET_YES) {
    echo 'checked="checked"'; }?> /> <?= __("People page should only show users with a profile photo.") ?>
  </div>
  <div class="field">
    <input type="checkbox" name="language_bar_enabled" value="<?= NET_YES ?>" <?php if (@$form_data['language_bar_enabled'] == NET_YES) { echo 'checked="checked"'; }?> />
    <?= __("You want to enable the languages menu bar.") ?>
  </div>
 <?php if ($meta_network_reci_relation) {?>    
  <div class="field">
    <input type="checkbox" name="top_navigation_bar" value="<?php echo NET_YES;?>"
    <?php if ($form_data['top_navigation_bar'] == NET_NO) {
    echo 'checked="checked"'; }?> /> <?= __("Top menu bar is not required.") ?>
  </div>
  <div class="field">
    <input type="checkbox" name="reciprocated_relationship" value="<?php echo NET_YES;?>"
    <?php if ($form_data['reciprocated_relationship'] == NET_YES) {
    echo 'checked="checked"'; }?> /> <?= __("Reciprocated relationships are required.") ?>
  </div>
  <div class="field">
    <input type="checkbox" name="email_validation" value="<?php echo NET_YES;?>"
    <?php if ($form_data['email_validation'] == NET_YES) {
    echo 'checked="checked"'; }?> /> <?= __("Email address validation is required.") ?>
  </div>
  <div class="field">
    <input type="checkbox" name="captcha_required" value="<?php echo NET_YES;?>"
    <?php if (@$form_data['captcha_required'] == NET_YES) {
    echo 'checked="checked"'; }?> /> <?= __("CAPTCHA is required during registration.") ?>
  </div>
  <? } else { ?>
  <div class="field">
    <?php echo (@$extra['reciprocated_relationship'] == NET_YES) ? __("Reciprocated relationships are required.")
                                                                 : __("Reciprocated relationships are not required."); ?>
  </div>
  <div class="field">
    <?php echo (@$extra['email_validation'] == NET_YES) ? __("Email address validation is required.")
                                                        : __("Email address validation is not required."); ?>
  </div>
  <div class="field">
    <?php echo (@$extra['captcha_required'] == NET_YES) ? __("CAPTCHA is required during registration.")
                                                        : __("CAPTCHA is not required during registration."); ?>
  </div>
  <? } ?>
   <div class="button_position">
     <input type="hidden" name="action" id="action_1" value="<? echo @$form_data['action'];?>" />
     <input type="hidden" name="nid" value="<? echo PA::$network_info->network_id; ?>" />
     <input type="hidden" name="config_action" id="config_action_1" value="" />
     <input name="submit" type="submit" value="<?= __("Save") ?>" />
    <?php if($page_id == PAGE_NETWORK_STATISTICS) : ?>
     <input name="submit" type="submit" value="<?= __("Restore Defaults") ?>" onclick="javascript: document.getElementById('action_1').value=' '; document.getElementById('config_action_1').value='restore_defaults';"/>
     <input name="submit" type="submit" value="<?= __("Store as Defaults") ?>" onclick="javascript:  document.getElementById('config_action_1').value='store_as_defaults';" />
    <?php endif; ?> 
   </div>
  <?php if ($is_edit) { ?>
           <?php echo $config_navigation_url;?>
   <?php
         }
   ?>
   <div class="field_text">&nbsp;</div>
   </fieldset>
 </form>
 <?php if($page_id == PAGE_NETWORK_STATISTICS) : ?>
  <form method="post" action="" enctype="multipart/form-data">
  <fieldset class="center_box">  
   <div class="field_biger" style="float:left; width:50%; border-right:1px solid silver">
    <div style="float:left;">
     <h4><label for="slogan-sub"><?= __("Load / restore General Network Settings from local disk") ?></label></h4>
     <input name="local_file" id="local_file" type="file" value="" />
    </div>
    <div class="field_text"  style="width:50%">
      <p><?= __('Select XML configuration file.') ?></p>
    </div>
   </div>
   <div class="field_biger" style="float:left; margin-left:12px">
     <h4><label><?= __("Select action") . ": " ?></label></h4>
     <input type="hidden" name="config_action" id="config_action_2" value="load_general_settings" />
     <input name="submit" type="submit" value="<?= __("Load General Settings") ?>" /><br />
   </div>
  </fieldset>
  </form>
  <form method="post" action="" enctype="multipart/form-data">
  <fieldset class="center_box">  
   <div class="field_biger" style="float:left; width:50%; border-right:1px solid silver">
    <div style="float:left;">
     <h4><label><?= __("Upload / Download network default settings for this network") ?></label></h4>
     <input name="config_file" id="config_file" type="file" value="" />
    </div>
    <div class="field_text"  style="width:50%">
      <p><?= __('Select XML configuration file for upload.') ?></p>
    </div>
   </div>
   <div class="field_biger" style="float:left; margin-left:12px">
     <h4><label><?= __("Select action") . ": " ?></label></h4>
     <input type="hidden" name="config_action" id="config_action_3" value="download_settings_file" />
     <input name="submit" type="submit" value="<?= __("Download default settings file") ?>" /><br />
     <input name="submit" type="submit" value="<?= __("Upload new default settings file") ?>"  onclick="javascript: document.getElementById('config_action_3').value='upload_settings_file';" />
   </div>
  </fieldset>
  </form>
 <?php endif; ?>
<?php if ($is_edit && $form_data['type'] != MOTHER_NETWORK_TYPE) { ?>
  <form name="delete_form" method="post" action="" >
    <fieldset class="center_box">
      <div class="very_bigger">
        <h4><label for="multiple_select">Delete Network</label></h4>
        <div class="privacy_selected" id="div_select_gen">
        </div>
        <div class="field_text">
          <p>You may remove your network from the PeopleAggregator system. </p>
          <p>Please know this step is final though. There is no backup.</p>
        </div>
      </div>
      <div class="field_bigger">
        <h4><label for="slogan-sub">Confirm Deletion</label></h4>
        <div class="field_text">
         <input class="confirm" type="checkbox" name="delete_network" value="1" /> Check to confirm that you want to delete this entire network including all of it's users, groups, posts and media.
        </div>
        
      </div>
      <div class="field_text">
        Yes, clicking this will really delete the entire network! Are you sure? Have you backed up this network just in case...?
      </div>
      <div class="button_position">
        <input type="submit" name="submit" value="Delete Network" />
        <input type="hidden" name="nid" value="<? echo PA::$network_info->network_id; ?>" />
        <input type="hidden" name="action" value="delete" />
      </div>
    </fieldset>
  </form>
<?php } ?>
</div>
