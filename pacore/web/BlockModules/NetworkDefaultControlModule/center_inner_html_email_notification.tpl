<?php
// echo "<pre>".print_r($form_data,1)."</pre>";
?>
<h1><?= __("Email Notifications") ?></h1>
<form method="post" action="" >
<fieldset class="center_box">

<table cellpadding="0" cellspacing="0" class="email_notification_table">
  <tr>
    <td class="email_notification_decript"><h4><?= __("Notify network operator (me) when") ?>:</h4></td>
    <td class="email_notification_box"><?= __("via my registered email") ?> </td>
    <td class="email_notification_box"><?= __("via my account inbox") ?> </td>
  </tr>
  <? if ( sizeof($form_data['extra']['notify_owner']) ) { 
    $i=0;
    foreach ( $form_data['extra']['notify_owner'] as $key => $value ) :
      $class = (( $i++%2 ) == 0) ? ' class="color"': NULL;
      switch ( $value['value'] ) {
        case NET_NONE: $chkd_registered = '';  $chkd_msg = '';  break;
        case NET_EMAIL: $chkd_registered = 'CHECKED';  $chkd_msg = '';    break;
        case NET_MSG: $chkd_registered = '';  $chkd_msg = 'CHECKED';    break;
        case NET_BOTH: $chkd_registered = 'CHECKED';  $chkd_msg = 'CHECKED';  break;
        default: $chkd_registered = '';  $chkd_msg = '';  
      }
  ?>
  <tr<?php echo $class;?>>
    <td><?php echo $value['caption'];?></td>
    <td><input type="checkbox" name="<?php echo $key.'_email';?>" value="1" <?php echo $chkd_registered;?> /><br /></td>
    <td><input type="checkbox" name="<?php echo $key.'_msg';?>" value="1" <?php echo $chkd_msg;?> /></td>
  </tr>
  <? endforeach;//..foreach
  }//..if ?>   
</table>
<table cellpadding="0" cellspacing="0" class="email_notification_table">
  <tr>
    <td class="email_notification_decript"><h4><?= __("Registered members receive notifications when") ?>:</h4></td>
    <td class="email_notification_box"><?= __("via their registered email") ?> </td>
    <td class="email_notification_box"><?= __("via their account inbox") ?> </td>
    <td class="email_notification_box"><?= __("User settable") ?> </td>
  </tr>
  <? if ( !empty($form_data['extra']['notify_members']) ) {
    $i=0;
    foreach ( $form_data['extra']['notify_members'] as $key => $value ) :
      $class = (( $i++%2 ) == 0) ? ' class="color"': NULL;
      switch ( $value['value'] ) {
        case NET_NONE: $chkd_registered = '';  $chkd_msg = '';  break;
        case NET_EMAIL: $chkd_registered = ' CHECKED ';  $chkd_msg = '';  break;
        case NET_MSG: $chkd_registered = '';  $chkd_msg = ' CHECKED ';  break;
        case NET_BOTH: $chkd_registered = ' CHECKED ';  $chkd_msg = ' CHECKED ';  break;
        default: $chkd_registered = '';  $chkd_msg = '';  
      }
    ?>
    <?php $style = ($value['value'] == -1) ? " style=\"display: none\"" : ""; ?>
    <tr<?php echo $class . $style;?>>
      <td><?php echo $value['caption'];?></td>
      <td><input type="checkbox" name="<?php echo $key.'_email';?>" value="1" <?php echo $chkd_registered;?> /><br /></td>
      <td><input type="checkbox" name="<?php echo $key.'_msg';?>" value="1" <?php echo $chkd_msg;?> /></td>
      <td><input type="checkbox" name="<?php echo $key.'_settable';?>" value="1" <?= (@$value['user_settable']) ? "CHECKED=\"CHECKED\"" : ""?> /></td>
    </tr>
    <? endforeach;//..foreach
  } //..if ?>  
</table> 
<table cellpadding="0" cellspacing="0">
  <tr>
    <td class="email_side_title"><h4><?= __("Blink Messages Waiting:") ?></h4></td>
    <td class="email_notification_box"><?= __("via their registered email") ?></td>
  <tr> 
  <tr>
    <td><?= __("messages  waiting blinks in network member's toolbar when new messages arrive") ?></td>
    <td><input type="checkbox" name="msg_waiting_blink" value="1" <?php if(!empty($form_data['extra']['msg_waiting_blink'])) echo 'CHECKED';?> /></td>
  </tr>  
</table>    
<!-- Last modified by Zoran Hron -->
<div class="button_position">
  <input type="hidden" name="config_action" id="config_action_1" value="save" />
  <input name="submit" type="submit" value="<?= __("Update All Current Users") ?>" style="color:red"  onclick="javascript: document.getElementById('config_action_1').value='update_user_defaults';"/>
  <input type="submit" name="submit" value="<?= __("Save changes") ?>" />
  <input name="submit" type="submit" value="<?= __("Restore Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='restore_defaults';"/>
  <input name="submit" type="submit" value="<?= __("Store as Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='store_as_defaults';" />
</div>
</fieldset>
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
<?php echo $config_navigation_url; ?>
