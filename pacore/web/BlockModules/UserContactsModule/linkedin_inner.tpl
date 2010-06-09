  <?php if($contacts) : ?>
    <form name="plaxo_contacts_form" id="plaxo_contacts_form" action="" method="POST">
      <div class="profile_conteiner">
        <h4><?= __("LinkedIn Contacts: ") ?></h4>
        <?php $cnt=0; foreach($contacts as $k => $contact) : $cnt++ ?>
          <div class="profile_content">
            <div class="profile_image">
              <a href="<?= PA::$url.PA_ROUTE_USER_CONTACTS . "?type=$type&stype=$stype&action=contactDetails&contact_id=" . $contact['cont_id'] ?>" id="contact_<?= $contact['cont_id'] ?>" class="jTip">
               <img src="<?php echo $contact['picture']?>" alt ="<?php echo $contact['name']?>" />
              </a>
            </div>
            <div class="profile_text">
               <?php echo wordwrap($contact['name'], 12, "<br />\n", true) //$contact['email']?>
            </div>
            <div class="profile_select">
               <label class="label_profile" for="inv_selected_<?= $k ?>">Select </label>
               <input type="checkbox" name="invite_selected[<?= $k ?>]" id="inv_selected_<?= $k ?>" value="<?php echo $contact['cont_id'] ?>" />
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="profile_conteiner" id="invite_msg_container">
        <h4><?= __("Write your invitation Message:") ?></h4>
        <div>
          <div style="float: left; width: 80%;">
            <textarea name="message" id="invitation_message" style="width: 100%; height: 128px"onfocus='javascript: if(this.value == "<?=CUSTOM_INVITATION_MESSAGE?>" ){ this.value="";}'><?php echo $message;?></textarea>
          </div>
          <div style="float: left; margin-left: 8px">
            <input type="submit" name="submit" id="submit_invite" value="Send Invitation" />
          </div>
        </div>
      </div>
      <div class="button_position" style="float:left">
        <input type="button" name="invite" id="invite_show" value="Invite Selected" />
        <input type="submit" name="submit" id="submit_delete" value="Delete Selected" onclick="javascript: if(confirm('Are you sure?')) { document.getElementById('action').value = 'deleteSelected'; return true; } else {return false;}" />
        <input type="button" name="select_all" id="select_all" value="Select All" />
        <input type="reset" name="reset" id="reset_sel" value="Reset Selection" />
        <input type="hidden" name="action" id="action" value="inviteSelected" />
      </div>
    </form>
  <?php else: ?>
      <?= __("No contacts")  ?>
  <?php endif; ?>
