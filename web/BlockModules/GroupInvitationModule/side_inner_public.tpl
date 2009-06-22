<?php 
  global $global_form_data;
?>
<div class="description"><?php if (!empty($invite_title)) {echo $invite_title; }?></div>

<form name="groupinvitation" action="" method="post" enctype="multipart/form-data" onsubmit="return validate_form();">

  <fieldset class="center_box">
    <div class="field_medium">
    <h4><label for="select_a_group"><?= __("Select a group to invite into") ?>:</label></h4>
    <select id="groups" name="groups" onchange="javascript: show_pending_invitation('<?php echo PA::$url;?>');" >
      <?php foreach ($user_groups as $group) {
              if (($group['gid'] == @$global_form_data['groups']) ||  $group['gid'] == @$_GET['ccid'] || $group['gid'] == @$_GET['gid']) {
                $selected = 'selected="selected"';
              }
              else {
                $selected = '';
              } ?> 
       <option value="<?php echo $group['gid'];?>" <?php echo $selected;?>><?php echo $group['name'];?></option>
      <?php } ?>
    </select> 
   </div><?= __("Enter Your friend's login name(in People Aggregator) or email address") ?>.<br /><br />
    <div class="field_medium">
      <h4><label for="email"><span class="required"> * </span><?= __("Email") ?>:</label></h4>
      <input type="text" id="email"  class="text longer" name="email_id" value="<?=@$global_form_data['email_id']?>" />
      <div class="field_text">
        <?= __("(separated by comma)") ?>
      </div>
    </div>
    <div class="field_big">
      <h4><label for="email pa_user"><span class="required"> * </span><?php echo PA::$site_name; ?> <?= __("Login Name") ?>:</label></h4>
      <input type="text" id="email_user_name"  class="text longer" name="email_user_name" value="<?=@$global_form_data['email_user_name']?>" />
      <div class="field_text">
        <?= __("(separated by comma)") ?>
      </div>
    </div> 
    <?php
      if ($global_form_data) {
        $message = $global_form_data['message'];
      } else {
        $message = __("Write here personalized message for invitees. It will be appended to email");
      }
    ?>
     <div class="field_bigger">
       <h4><label for="Group description"><?= __("Write your invitation Message") ?>:</label></h4>
       <textarea name="message" id="invitation_message" onfocus='if(this.value == "Write here personalized message for invitees. It will be appended to email"){ this.value="";}'><?php echo $message;?></textarea>
     </div>  
   </fieldset>  
     <div class="button_position">
       <input type="submit" name="submit" value="<?= __("Send Invitation") ?>" />
       <input type='hidden' name='action' value='GroupInvitationSubmit' />
     </div>
</form>