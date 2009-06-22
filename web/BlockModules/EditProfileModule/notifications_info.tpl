<?php

global  $network_info;

function find_sum($v1,$v2){
  if( $v1==1 && $v2==1 ) {
     $r=NET_BOTH;
  } else if( $v1==0 && $v2==0 ){
    $r=NET_NONE;
  } else if( $v1==1 && $v2==0 ) {
     $r=NET_EMAIL;
  } else if( $v1==0 && $v2==1 ) {
    $r=NET_MSG;
  }
  return $r;
}

function getDefaults() {
  $network_defaults = PA::$extra['notify_members'];
  $user_defaults = array();
  foreach($network_defaults as $setting => $data) {
   if($data['user_settable']) {
      $user_defaults[$setting]['caption'] =  $data['caption'];
      $user_defaults[$setting]['value']   = $data['value'];
    }
  }
  $user_defaults['msg_waiting_blink'] = PA::$extra['msg_waiting_blink'];
  return $user_defaults;
}

// get current notification settings for this user
$profile = &$this->user->{'notifications'};
if ($profile['settings']) {
  $notification_settings =
    unserialize($profile['settings']['value']);
} else {
  $notification_settings = Array();
}

// get the default notification setting for the betwork
$default_notification_settings = PA::$extra['notify_members'];
// merge defaults into user settings
foreach($default_notification_settings as $setting => $data) {
    if(($data['user_settable'])) {
      if(!isset($notification_settings[$setting])) {                     // only if not already exists in user settings
        $notification_settings[$setting]['caption'] =  $data['caption'];
        $notification_settings[$setting]['value'] = $data['value'];
      }  
    } else {
      if(isset($notification_settings[$setting])) {
        unset($notification_settings[$setting]);
      }   
    } 
}

if(!isset($notification_settings['msg_waiting_blink'])) {                     // only if not already exists in user settings
  $notification_settings['msg_waiting_blink'] = PA::$extra['msg_waiting_blink'];
}

//adjust caption grammar for user level notification
// change "their" to "my", etc.
foreach($notification_settings as $setting=>$data) {
  if (! empty($data['caption'])) {
    $caption = 
      preg_replace(
        Array('/they/','/their/','/themselves/','/them/'),
        Array('I','my','myself','me'),
        $data['caption']
      );
    $notification_settings[$setting]['caption'] = $caption;
    }
}

if (isset($_POST['submit']) && ($_POST['profile_type'] == 'notifications')) {
  if($_POST['submit'] == 'Restore default settings') {
    $notification_settings = getDefaults();
  } else {
    // $this is  DynamicProfile class instance
    // $this->processPOST('notifications');  
    foreach($notification_settings as $k => $v) {
      if($v['value'] <> -1) {
        $emailVal = (empty($_POST[$k.'_email']))?0:1;
        $msgVal = (empty($_POST[$k.'_msg']))?0:1;
        $s = find_sum($emailVal, $msgVal);
        if(isset($notification_settings[$k]['value'])) {
          $notification_settings[$k]['value'] = $s;
        }
      }  
    }
    if(empty($_POST['msg_waiting_blink'])){
      $notification_settings['msg_waiting_blink'] = NET_NO;
    } else if($_POST['msg_waiting_blink'] == NET_YES) {
      $notification_settings['msg_waiting_blink'] = NET_YES;
    } 
  }
  
  // save this to profile
  $profile['settings']['name'] = 'settings';
  $profile['settings']['value'] =
    serialize($notification_settings);

  $this->user->{'notifications'} = $profile;
  // save away!
  $this->user->save_profile_section($profile, 'notifications'); 
	global $error_msg;
	$error_msg = __('Profile updated successfully.');
}


/*
echo "<pre>";
print_r($default_notification_settings);
print_r($notification_settings);
echo "</pre>";
*/

?>
  <h1><?= __("Notifications") ?></h1>
      <form enctype="multipart/form-data" action="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=notifications&action=save" method="post">
      <input type="hidden" name="profile_type" value="notifications" />
        <fieldset>
    <table id="tablelist" width="100%" cellpadding="3" cellspacing="3">
    <tr>
      <th scope="col"><?= __("I want to receive  notifications when") ?>:</th>
      <th scope="col"><?= __("via my registered email") ?> </th>

      <th scope="col"><?= __("via my account inbox") ?> </th>
    </tr>
    <? if ( sizeof($notification_settings) ) {
      $i=0;
      foreach ( $notification_settings as $key => $value ) {
        if(! isset($value['caption'])) {
          continue;
        }
        $class = (( $i++%2 ) == 0) ? ' class="color"': '';
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
      <td><input type="checkbox" name="<?php echo $key.'_email';?>" value="1" <?php echo $chkd_registered;?> />
        <br /></td>
      <td><input type="checkbox" name="<?php echo $key.'_msg';?>" value="1" <?php echo $chkd_msg;?> /></td>
      </tr>

      <? } //..foreach
    } //..if
    ?>
    <tr>
      <th scope="col"><?= __("Notify 'messages waiting'") ?>:</th>
      <th scope="col"><?= __("through my registered mail") ?></th>
      <th scope="col"></th>
    </tr>
  <tr>
    <td><?= __("'messages waiting' notifications in my mail box, when new messages arrive") ?></td>
    <td><input type="checkbox" name="msg_waiting_blink" value="1" <?php
    if(
      $notification_settings['msg_waiting_blink'] == NET_YES
      ) echo ' CHECKED ';
    ?>/>
    </td>
    <td></td>
  </tr>
</table>
        </fieldset>
        
<div class="button_position">
  <input type="submit" class="button-submit" name="submit" value="<?= __("Apply Changes") ?>" />
  <input type="submit" class="button-submit" name="submit" value="<?= __("Restore default settings") ?>" />
</div>
      </form>