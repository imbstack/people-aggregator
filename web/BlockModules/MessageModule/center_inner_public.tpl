<?php
  // global var $_base_url has been removed - please, use PA::$url static variable

  //todo - remove <style> from here and put it into css
  $message = __('There are no messages in this folder.');//message to be shown when no messages are found in the folder.
  if (!empty($search_string)) {
    $message = __('No messages matched your search');// message to be displayed when search yields no results.
  }
?>
<?php include 'folders_list.tpl' ?>
<div id="message_col_b">
  <div id="buttonbar">
    <ul>
      <li><a href="<?php echo PA::$url .PA_ROUTE_ADDMESSAGE ;?>"><?= __("Compose message") ?></a></li>
      <li><a href="#" onclick="javascript: user_messages('delete', 0);"><?= __("Delete") ?></a></li>
      <li><a href="javascript: message_get_folder_name('createFolderForm');"><?= __("Create new folder") ?></a></li>
      <form name="createFolderForm" method="post" action="<?php echo PA::$url .PA_ROUTE_MYMESSAGE ;?>">
        <input type="hidden" name="new_folder" value="" id="new_folder">
        <input type="hidden" name="action" value="new_folder">
      </form>
      
      <li><a href="#" onclick="javascript: user_messages('move', 0);"><?= __("Move to:") ?></a></li>
      
      <form name="messageList" method="post" action="">
      <li class="buttonbar_select">

        <select name="sel_folder" size="1" id="sel_folder">
          <?php
            if (count($folders)) {
          ?>
          <option value=""><?= __("- Select Folder -") ?></option>
          <?php  
              foreach ($folders as $folder) {
                $selected = null;
                if ($folder['name'] == $folder_name) {
                  $selected = ' selected="selected"';
                }
          ?>                  
          <option value="<?php echo $folder['name']?>"<?php echo $selected?>><?php echo $folder['name']?></option>
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

  </div>          
    <table border="0" cellspacing="0" cellpadding="0">
      <input type="hidden" name="action" value="">
      <?php
        if (count($messages)) {
          if ($folder_name == SENT) {
          ?>
          <tr class="no_read">
            <td width="30"><input type="checkbox"  name="select_all" id="select_all"></td>
            <td width="222"><?= __("Sent To") ?></td>
            <td width="400"><?= __("Subject") ?></td>
            <td width="30">&nbsp;</td>
            <td width="100"><?= __("Date") ?></td>  
          </tr>
          <?php
          //If we are viewing messages in Sent folder
            foreach ($messages as $message) {
              if ($message['new_msg'] == ACTIVE) {
                //message is still unread
                $class = 'no_read';
              } else {
                // message has been read at least once
                $class = 'read';
              }
              
              //If message is sent today, then time will be displayed like 11.03 am otherwise date like Dec 20
              if (date("Mdy") == date("Mdy", $message['sent_time'])) {
                $date_time = date("h:i a", $message['sent_time']);
              } else {
                $date_time = date("M  d", $message['sent_time']);
              }
            
      ?>
      <tr class="<?php echo $class?>">
      <td width="30"><input type="checkbox" name="index_id[]" value="<?php echo $message['index_id']?>"><input type="hidden" name="msgid[]" value="<?php echo $message['message_id']?>~<?php echo $message['index_id']?>"></td>
        <td width="222"><?= __("To:") ?> <?php echo chop_string(uihelper_lookupnames($message['all_recipients']), 35)?></td>                
        <td width="400"><a href="<?php echo PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$folder_name&action=view_message&mid=" . $message['message_id']?>"><?php echo chop_string($message['subject'], 80);?></a></td>
        <td width="30">&nbsp;</td>
        <td width="100"><?php echo $date_time?></td>

      </tr>
      <?php
            }
          } else if (!empty($search_string)) {
          ?>
          <tr class="no_read">
            <td width="30"><input type="checkbox"  name="select_all" id="select_all"></td>
            <td width="222">Sender</td>
            <td width="400">Folder: Subject</td>
            <td width="30">&nbsp;</td>
            <td width="100">Date</td>  
          </tr>
          <?php
            //If we are viewing messages after search
            foreach ($messages as $message) {
            	// FIXME: why is the 'new_msg' index not set in search results?
              if (@$message['new_msg'] == ACTIVE) {
                //message is still unread
                $class = 'no_read';
              } else {
                // message has been read at least once
                $class = 'read';
              }
              
              //If message is sent today, then time will be displayed like 11.03 am otherwise date like Dec 20
              if (date("Mdy") == date("Mdy", $message['sent_time'])) {
                $date_time = date("h:i a", $message['sent_time']);
              } else {
                $date_time = date("M  d", $message['sent_time']);
              }
              
              if ($message['folder_name'] == SENT) {
                $sender = 'To:'.$message['all_recipients'];
              } else {
                
                $login = User::get_login_name_from_id($message['sender_id']);
/*                
                $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$message['sender_id'];
                $url_perms = array('current_url' => $current_url,
                                          'login' => $login                  
                                        );
                $url = get_url(FILE_USER_BLOG, $url_perms);
*/                
                $url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login;
                $sender = '<a href="'.$url.'">'.$message['sender_name'].'</a>';
              }
      ?>
      <tr class="<?php echo $class?>">
        <td width="30">
          <input type="checkbox" name="index_id[]" value="<?php echo $message['index_id']?>">
          <input type="hidden" name="msgid[]" value="<?php echo $message['message_id']?>~<?php echo $message['index_id']?>">
        </td>
        <td width="222"><?php echo $sender?></td>
        <td width="400"><b><?php echo ucfirst($message['folder_name'])?>:</b>
          <a href="<?php echo PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$folder_name&action=view_message&mid=" . $message['message_id']?>&amp;q=<?php echo $search_string?>"><?php echo chop_string($message['subject'], 80);?></a>
        </td>
        <td width="30">&nbsp;</td>
        <td width="100"><?php echo $date_time?></td>  
      </tr>
      <?php
            }
          } else {
          ?>
          <tr class="no_read">
            <td width="30"><input type="checkbox" name="select_all" id="select_all"></td>
            <td width="222"><?= __("Sender") ?></td>
            <td width="400"><?= __("Subject") ?></td>
            <td width="30">&nbsp;</td>
            <td width="100"><?= __("Date") ?></td>  
          </tr>
          <?php
            //If we are viewing messages in any other folder than Sent
            foreach ($messages as $message) {
              if ($message['new_msg'] == ACTIVE) {
                //message is still unread
                $class = 'no_read';
              } else {
                // message has been read at least once
                $class = 'read';
              }
              
              //If message is sent today, then time will be displayed like 11.03 am otherwise date like Dec 20
              if (date("Mdy") == date("Mdy", $message['sent_time'])) {
                $date_time = date("h:i a", $message['sent_time']);
              } else {
                $date_time = date("M  d", $message['sent_time']);
              }
              
              $login = User::get_login_name_from_id($message['sender_id']);
/*              
              $current_url = PA::$url . '/' .FILE_USER_BLOG .'?uid='.$message['sender_id'];
              $url_perms = array('current_url' => $current_url,
                                        'login' => $login                  
                                      );
              $url = get_url(FILE_USER_BLOG, $url_perms);
*/
              $url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login;
                        
      ?>
      <tr class="<?php echo $class?>">
        <td width="30"><input type="checkbox" name="index_id[]" value="<?php echo $message['index_id']?>"><input type="hidden" name="msgid[]" value="<?php echo $message['message_id']?>~<?php echo $message['index_id']?>"></td>
        <td width="222"><a href="<?php echo $url;?>"><?php echo $message['sender_name']?></a></td>
        <td width="400"><a href="<?php echo PA::$url . PA_ROUTE_MYMESSAGE . "/folder=$folder_name&action=view_message&mid=" . $message['message_id'] ?>"><?php echo chop_string($message['subject'], 80);?></a></td>
        <td width="30">&nbsp;</td>
        <td width="100"><?php echo $date_time?></td>  
      </tr>
      <?php
            }
          }
      ?>
      <tr class="no_read">
        <td colspan="5" id="paging">
          <?php                     
            if (!empty($page_links)) {
              echo $page_links;
            }
          ?>
        </td>
      </tr>
      <?php
        } else {
      ?>
      <tr class="no_read">
        <td colspan="5"><?php echo $message?></td>
      </tr>
      <?php
        }
      ?>
    </table>
  </form>
</div>