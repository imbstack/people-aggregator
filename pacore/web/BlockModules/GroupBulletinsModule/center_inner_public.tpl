<?php
	require_once "web/includes/tinymce.php";
  install_tinymce('full');?>

<div class="description"><?= __("Use this tool to send announcements to all registered network members") ?>. </div>
<div id="div_epm1">
  <form method="post" action="<?php echo PA::$url;?>/network_bulletins.php">
  <fieldset class="center_box">
  <div class="field">
    <h4><label><?= __("Title") ?>:</label></h4>
    <input name="title" type="text" class="text longer" size="60" value="<?php echo htmlspecialchars(@$_POST['title']);?>" />
  </div>
  
  <div class="field_bigger">
    <h4><label><?= __("Message/post") ?>:</label></h4>
     <textarea name="bulletin_body" cols="58" rows="10" class="text longer"><?php echo htmlspecialchars(@$_POST['bulletin_body']);?></textarea>
  </div>
  
  <div class="field">
    <h4><label><?= __("Tags") ?>:</label></h4>
     <input name="tags" type="text" class="text longer" size="60" value="<?php echo htmlspecialchars(@$_POST['tags']);?>"/>
  </div>
  
  
  <h4><label><?= __("Destination") ?>:</label></h4>
    <?php $checked = '';if(!empty($_POST['inbox'])) { $checked = 'CHECKED';} else { $checked = ''; }?>
    <div class="field">
      <input type="checkbox" name="inbox" value="1" <?php echo $checked ;?> />
      &nbsp;<?= __("Deliver message to all registered members by internal mail") ?>
    </div>
      
    <?php $checked = '';if(!empty($_POST['mail'])) { $checked = 'CHECKED';} else { $checked = ''; }?>
    <div class="field">
      <input type="checkbox" name="mail" value="1" <?php echo $checked ;?> />
      &nbsp;<?= __("Email message to all network members") ?>
    </div>
    <?php $checked = '';if(!empty($_POST['network_home'])) { $checked = 'CHECKED';} else {
    $checked = ''; }?>
    <div class="field">
      <input type="checkbox" name="network_home" value="1" <?php echo $checked ;?>/>
      &nbsp;<?= __("Post message to network home page community blog") ?> 
    </div>
    
    <div class="button_position"> 
      <input type="submit" name="preview" value="<?= __("Preview") ?>"/>
      <input type="submit" name="send_to_me_only" value="<?= __("Send to me only") ?>"/>
      <input type="submit" name="bulletins" value="<?= __("Send") ?>"/>
    </div>  
   </fieldset>
   <div><?php echo $preview_msg;?></div>
  </form>
  <?php echo $config_navigation_url; ?>
</div>