<?php
  // global var $_base_url has been removed - please, use PA::$url static variable

?>
<?php include 'folders_list.tpl' ?>
<div id="message_col_b">
  <div id="buttonbar">
    <form name="messageList" method="post" action="<?php echo PA::$url.PA_ROUTE_MYMESSAGE?>">
      <ul>
        <?php
          if (!empty($search_string)) {
        ?>
        <li><a href="<?php echo PA::$url.PA_ROUTE_MYMESSAGE?>/q=<?php echo $search_string?>"><?= __("Back to search results") ?></a></li>
        <?php
          }
        ?>
        <li><a href="<?php echo PA::$url .PA_ROUTE_ADDMESSAGE ;?>"><?= __("Compose message") ?></a></li>
        <li><a href="#" onclick="javascript: user_messages('delete', 1);"><?= __("Delete") ?></a></li>
        <li><a href="#" onclick="javascript: action_decide('reply');"><?= __("Reply") ?></a></li>
        <li><a href="#" onclick="javascript: action_decide('forward');"><?= __("Forward") ?></a></li>
        <?php if (!empty($prev_nxt_msg['previous'])) { ?><li><a href="<?php echo PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$folder_name&action=view_message&mid=" . $prev_nxt_msg['previous'];?>"><?= __("Previous") ?></a></li>
        <?php } ?>
        <?php if (!empty($prev_nxt_msg['next'])) { ?><li><a href="<?php echo PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$folder_name&action=view_message&mid=" . $prev_nxt_msg['next'];?>"><?= __("Next") ?></a></li>
        <?php } ?>
        <li><a href="#" onclick="javascript: user_messages('move', 1);"><?= __("Move to:") ?></a></li>
        <li class="buttonbar_select">
          <select name="sel_folder" size="1" id="sel_folder">
          <?php
            if (count($folders)) {
          ?>
            <option value=""><?= __("- Select Folder -") ?></option>
          <?php
              foreach ($folders as $folder) {
          ?>
            <option value="<?php echo $folder['name']?>"><?php echo $folder['name']?></option>
          <?php
              }
            } else {
          ?>
            <option value="-1"><?= __("(no folders available)") ?></option>
          <?php
            }
          ?>
          </select>
        </li>
      </ul>
      <input type="hidden" name="action" value="">
      <input type="hidden" name="mid" value="<?php echo $mid?>">
      <input type="hidden" name="folder_name" value="<?php echo $folder_name?>">
      <input type="hidden" name="index_id[]" value="<?php echo $message_details['index_id']?>">
    </form>
  </div>
  <div id="message_body">
    <ul>
      <li><b><?= __("Date:") ?> </b><?php echo PA::datetime($message_details['sent_time'], 'long', 'short'); // date("d M Y h:i:s ",$message_details['sent_time'])?></li>
      <li><b><?= __("From:") ?> </b><?php echo $message_details['sender_name']?></li>
      <li><b><?= __("To:") ?> </b>
      <?=uihelper_lookupnames($message_details['all_recipients'])?>
      </li>
      <li><b><?= __("Subject:") ?> </b><?php echo $message_details['subject']?></li>
      <li class="view_message">
         <?php if(preg_match("#\<[^\>]+\>#", $message_details['body'])) : ?>
           <?php echo trim($message_details['body'])?>
         <?php else: ?>
           <?php echo nl2br(trim($message_details['body']))?>
         <?php endif; ?> 
      </li>
    </ul>
    <form name="message_form" action="" method="post">
      <input type="hidden" name="form_handler" value="MessageModule">
      <input type="hidden" name="mssg_id" id="mssg_id" value="<?php echo $mid?>">
      <input type="hidden" name="do_action" value="">
    </form>
  </div>
</div>
