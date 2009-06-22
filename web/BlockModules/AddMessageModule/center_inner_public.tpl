<?php include 'folders_list.tpl' ?>
<div id="message_col_b">  
    <div id="buttonbar">
      <ul>
        <li><a href="#" onclick="javascript: add_message.check();"><?= __("Send") ?></a></li>
        <li><a href="<?php echo PA::$url.PA_ROUTE_MYMESSAGE ?>" onclick ="return delete_confirmation_msg('<?= __("Message composed will be discarded. Do you want to continue?") ?>') ">Cancel</a></li>
      </ul>    
    </div>
  <?php $mod->post_url =  PA::$url.PA_ROUTE_ADDMESSAGE.'/action=AddMessagesSubmit'; ?>
  <?= $mod->start_form("compose_form", "post") ?>
  <fieldset class="center_box">  
  <div id="message_body">
    <div class="field">
      <h4><label for="to"><span class="required"> * </span> <?= __("To") ?></label></h4>
      <input type="hidden" name="to" id="to_box" value="<?php echo field_value($to, field_value(@$to, null))?>"/>

      <span class="text" id="to_display_box"><?php echo field_value(@$to_display, field_value(@$to_display, null))?></span>

      <select name="friend" id="sel_friend" onchange="javascript: add_message.add_recipient();">
        <option value="select friend"><?= __("select friend") ?></option>
        <?php
          if ($friends) {
            foreach ($friends as $display_name => $login_name) {
              $display_name=chop_string($display_name,19);
        ?>
        <option id="<?php echo $login_name?>" value="<?php echo $login_name?>"  > <?php echo $display_name?></option>
        <?php
            }
          }
        ?>
      </select>
      <div class="field_text"><?= __("Note: to address a message to a user who is not your friend, please visit their public page and click 'send message'.")?></div>
    </div>


    <div class="field_medium">
      <h4><label for="description"><?= __("Subject") ?></label></h4>
      <input class='text longer' id="subject" name='subject' type='text' value="<?php echo field_value($subject, field_value(@$subject, null))?>" />      
    </div>
    
    <div class="field_bigger">
      <h4><label><?= __("Message") ?></label></h4>
      <textarea class="text longer" name="body" id="body" rows="" cols=""><?php echo field_value($body, field_value(@$body, null))?></textarea>
    </div>
  </div>
  <input type="hidden" name="send" value="send" />

  <input type="hidden" name="in_reply_to" value="<?php echo field_value($in_reply_to, field_value(@$in_reply_to, null)) ?>" />
  
  </fieldset>
  </form>
  <div id="buttonbar">
    <ul>
      <li><a href="#" onclick="javascript: add_message.check();"><?= __("Send") ?></a></li>
      <li><a href="<?php echo PA::$url.PA_ROUTE_MYMESSAGE?>" onclick ="return delete_confirmation_msg('<?php echo __('Message composed will be discarded. Do you want to continue?')?>') "><?= __("Cancel") ?></a></li>
    </ul>    
  </div>
</div>