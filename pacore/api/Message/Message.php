<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
include_once dirname(__FILE__)."/../../config.inc";
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/User/User.php";

/*

DB tables used by messaging system:

message_folder
- fid = PRIMARY KEY
- uid = user id
- name = folder name

user_message_folder (links messages to folders - one row for every message/recipient)
- fid = FOREIGN KEY message_folder.fid
- index_id = PRIMARY KEY
- mid = message id
- new_msg, reply, forward = flags

private_messages (message data)
- message_id = PRIMARY KEY
- sender_id = FOREIGN KEY user.user_id
- sent_time = php time()
- size = message size in kB, rounded up
- all_recipients = text recipient list
- subject
- body
- in_reply_to = set to the mid of the message we are replying to, or to 0 if it is a new message
- conversation_id = mid of the message that started a conversation (series of replies), or to the mid of the message itself it it'S the first

When sending a message (add_mesage()):

- one row in private_messages is inserted to hold the message info,

- for each recipient, their INBOX folder id is found from
  message_folder

  - and a row is inserted into user_message_folder to link message
    (private_messages.message_id) to recipient's inbox
    (message_folder.fid).

XXX: is it correct for add_message to add a row into
user_message_folder (with fid=sender's DRAFT folder) for every
recipient when saving a message as a draft?

*/


/**
* Class message represents messages in the system.
*
* @package Message
* @author Tekriti Software
*/
class Message {

  /**
  * The default constructor for message class.
  */
  public function __construct() {
    return;
  }

  // this method gets and return the conversation_id for a give message
  static function get_conversation_id($message_id) {
    $sql = "SELECT conversation_id from {private_messages} WHERE message_id = ?";
    $data = array($message_id);
    $res = Dal::query($sql, $data);
    if ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      if ($row->conversation_id > 0) return $row->conversation_id;
      else return $message_id; // for all messages that predate the conversation code
    } else {
    	return false; // message does not exist
    }
  }

  static function get_conversations($user_id) {
    $sql = 'SELECT uid, message_id, sender_id, all_recipients, subject, body, sent_time, in_reply_to, conversation_id
    FROM message_folder AS MF,
    user_message_folder AS UMF,
    private_messages AS PM
    WHERE MF.fid = UMF.fid
    AND UMF.mid = PM.message_id
    AND MF.uid = ?
    ORDER BY PM.conversation_id DESC, PM.sent_time ASC
    ';
    $data = array($user_id);
    $res = Dal::query($sql, $data);
    $messages = array();
    $sender_names = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
    	$mid = $row->message_id;
    	$sender_exists = TRUE;
      // get the sender's name
      if (isset($sende_names[(int)$row->sender_id])) {
      	$sender_name = $sende_names[(int)$row->sender_id];
      } else {
	      $u = new User();
  	    try {
					$u->load((int)$row->sender_id);
					$sender_name = $sende_names[(int)$row->sender_id] =
						$u->login_name;
  	    } catch (PAException $e) {
		    	$sender_exists = FALSE;
  	    }
      }
      if ($sender_exists) { // only add this message to display if the sender exists
	      $messages[$mid] = $row;
  	    $messages[$mid]->sender_name = $sender_name;
      }
    }
    return $messages;
  }

  /**
  * Adds the given message for the given user id.
  *
  * @param integer $sender_id The id of the user who sends the message to be added.
  * @param integer $recipients_ids The ids of the recipients of the message to be added.
  * @param string represents all the recipients of a message.
  * @param string subject of message.
  * @param text body of the message.
  */
  static function add_message($sender_id, $recipients_ids=NULL, $all_recipients, $subject, $body, $is_draft = FALSE, $in_reply_to = 0) {

    Logger::log("Enter: function Message::add_message()");
    //if all recipient is an array not a comma separated string
    if(is_array($all_recipients)) {
      $user_names = $all_recipients;
      $all_recipients = implode(",", $user_names);
    } else {//if all recipient is a comma separated string
    // use preg_split here, as the list might include spaces, but the names will not
      $user_names = preg_split("/,\s*/", $all_recipients);
    }

    // If recipients array is not set from the arguments only then it will be set here.
    if (empty($recipients_ids)) {
      foreach ($user_names as $user_name) {
        $user = new User();
        $user->load(trim($user_name));
        $recipients_ids[] = $user->user_id;
      }
    }
    $message_id = Dal::next_id('Message');

    // see if we are part of a conversation
    if ($in_reply_to > 0) {
    	$conversation_id = Message::get_conversation_id($in_reply_to);
    } else {
    	$conversation_id = $message_id; // this would be the parent of any subsequent conversations
    }

    $sql = 'INSERT into {private_messages} (message_id, sender_id, all_recipients, subject, body, sent_time, size, in_reply_to, conversation_id) values (?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $data = array($message_id, $sender_id, $all_recipients, $subject, $body, time(), ceil(strlen($body)/1024), $in_reply_to, $conversation_id);
    Dal::query($sql, $data);
    Logger::log("Saved private message ID $message_id, from $sender_id to recipient(s): $all_recipients", LOGGER_ACTION);

    foreach($recipients_ids as $id) {

      $folder_name = $is_draft ? DRAFT : INBOX;
      $folder_id = Message::get_folder_by_name($id, $folder_name);

      $sql = 'INSERT into {user_message_folder} (mid, fid, new_msg, reply, forward) values (?, ?, ?, ?, ?)';
      $data = array($message_id, $folder_id, 1, 0, 0);
      Dal::query($sql, $data);
      Logger::log("Linked message ID $message_id to folder $folder_id ($folder_name) for user $id", LOGGER_ACTION);
    }

    if ($is_draft == FALSE) {
      $sent_folder_name = SENT;
      $folder_id = Message::get_folder_by_name($sender_id, $sent_folder_name);
      $sql = 'INSERT into {user_message_folder} (mid, fid, new_msg, reply, forward) values (?, ?, ?, ?, ?)';
      $data = array($message_id, $folder_id, 1, 0, 0);
      Dal::query($sql, $data);
      Logger::log("Linked message ID $message_id to folder $folder_id ($sent_folder_name) for sender $sender_id", LOGGER_ACTION);
    }

    Logger::log("Exit: function Message::add_message()");
    return;
  }

  /**
  * Load the messages for the given user_id.
  *
  * @param integer user_id of the user whose messages need to be loaded.
  * @param string name of the folder whose messages need to be loaded.
  * @param integer page number of the folder.
  * @param integer number of messages per page.
  * @param string sort factor.
  * @param int flag indicating sort order.
  * @return array Returns the array for a particular user.Contain the message details.
  */
  static function load_folder_for_user($user_id, $folder_name=INBOX, $cnt=false, $page_no=1, $msg_per_page='ALL', $sortby='sent_time', $sort_flag=0) {
    Logger::log("Enter: function Message::load_folder_for_user()");
    $message = array();

    if ($sort_flag == 0) {
      $sort = "DESC";
    }
    else {
      $sort = "";
    }

    $folder_id = Message::get_folder_by_name($user_id, $folder_name);

    $sql = "SELECT count(mid) AS total from {user_message_folder} WHERE fid = ?";
    $data = array($folder_id);
    $res = Dal::query($sql, $data);

    if ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $total = $row->total;
    }
    if (is_int($msg_per_page)) {
      $count1 = ($page_no - 1) * $msg_per_page;
      $count2 = $msg_per_page;
      $limit = ' LIMIT '. $count1.', '. $count2;
    } else {//if show ALL
      $limit = NULL;
    }
    if ($sortby == 'Sent To') {
      $sortby = 'all_recipients';
    }

    if ($sortby == 'Sender') {
      $sortby = 'Us.login_name';
      $sql = "SELECT * from {private_messages} AS P, {users} AS Us, {user_message_folder} AS U WHERE U.fid = ? AND P.message_id = U.mid AND P.sender_id = Us.user_id AND Us.is_active = ? ORDER BY Us.login_name ".$sort . $limit;
      $data = array($folder_id, ACTIVE);
    }
    else {
      if ($cnt) {
        $sql = "SELECT * from {private_messages} AS P LEFT JOIN {user_message_folder} AS UMF ON P.message_id = UMF.mid LEFT JOIN {users} AS U ON P.sender_id = U.user_id WHERE UMF.fid = ? AND U.is_active = ?";
        $data = array($folder_id, ACTIVE);
        $res = Dal::query($sql, $data);
        Logger::log("Exit: function Message::load_folder_for_user()");
        return $res->numRows();
      } else {
        $sql = "SELECT * from {private_messages} AS P LEFT JOIN {user_message_folder} AS UMF ON P.message_id = UMF.mid LEFT JOIN {users} AS U ON P.sender_id = U.user_id WHERE UMF.fid = ? AND U.is_active = ? ORDER BY ".Message::validate_sortby($sortby)." ".$sort . $limit;
        $data = array($folder_id, ACTIVE);
        $res = Dal::query($sql, $data);
      }

    }

    $i = 0;
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
    	$u = new User();
    	$u->load((int) $row->user_id);
      $message[$i] = array('message_id' => $row->message_id, 'sender_id' => $row->sender_id, 'all_recipients' => stripslashes($row->all_recipients), 'subject' => stripslashes($row->subject), 'body' => stripslashes($row->body), 'sent_time' => $row->sent_time, 'new_msg' => $row->new_msg, 'fid' => $row->fid, 'size' => $row->size, 'index_id' => $row->index_id, 'reply' => $row->reply, 'forward' => $row->forward, 'total' => $total, 'sender_name'=>$u->display_name);
      $i++;
    }
    Logger::log("Exit: function Message::load_folder_for_user()");
    return $message;
  }

  /**
  * Load the message for the given message id.
  *
  * @param integer mid of the message.
  * @param integer fid of the folder.
  * @return array Returns the array for a particular user.Contain the message details.
  */
  static function load_message($folder_id, $message_id, $user_id=null) {
    Logger::log("Enter: function Message::load_message_for_user()");
    $message = array();

    $sql = "SELECT * from {private_messages} WHERE message_id = ?";
    $data = array($message_id);
    $res = Dal::query($sql, $data);

    if ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $message['sender_id']  = $row->sender_id;
      $message['subject']  = stripslashes($row->subject);
      $message['body']  = stripslashes($row->body);
      $message['sent_time']  = $row->sent_time;
      $message['all_recipients'] = stripslashes($row->all_recipients);
      $message['size'] = $row->size;
      $message['in_reply_to'] = $row->in_reply_to;
      $message['conversation_id'] = $row->conversation_id;
    }

    //code for getting the sender name
    if ($message['sender_id']) {
      $User = new User();
      $User->load((int)$message['sender_id']);
      $message['sender_name'] = $User->login_name;
    }

    //Code for getting the index_id for the message.
    $sql = 'SELECT index_id, UMF.fid FROM {user_message_folder} AS UMF LEFT JOIN {message_folder} AS MF ON UMF.fid = MF.fid WHERE UMF.mid = ? AND MF.uid = ?';
    $res = Dal::query($sql, array($message_id, $user_id));
    if ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $message['index_id'] = $row->index_id;
      $folder_id = $row->fid;
    }

    if (!empty($folder_id)) {
      $sql = "UPDATE {user_message_folder} SET new_msg = 0 WHERE mid = ? AND fid = ?";
      $data = array($message_id, $folder_id);
    } else {
      //If folder id is null then this query will execute.
      $sql = 'UPDATE {user_message_folder} SET new_msg = ? WHERE mid = ?';
      $data = array(0, $message_id);
    }
    $res = Dal::query($sql, $data);

    Logger::log("Exit: function Message::load_folder_for_user()");
    return $message;
  }

  /**
  * Deletes the message for the given message ids.
  *
  * @param array ids of the message indexes.
  */
  static function delete_message($message_ids) {
    Logger::log("Enter: function Message::delete_message()");

    $sql = "DELETE FROM {user_message_folder} WHERE index_id = $message_ids[0]";

    for ($i = 1;$i < count($message_ids);$i++) {
      $sql .= " OR index_id = $message_ids[$i]";
    }

    $res = Dal::query($sql, $data);

    Logger::log("Exit: function Message::delete_message()");
    return;
  }


  /**
  * Creates a folder for user.
  *
  * @param string name of the folder to be created.
  * @param int user_id of user for whom the folder is to be created.
  * @param int flag for updation of existing folder
  */
  static function create_folder($user_id, $folder_name, $flag = 0) {
    Logger::log("Enter: function Message::create_folder()");

    $sql = "SELECT * from {message_folder} WHERE uid = ? AND name = ?";
    $data = array($user_id, $folder_name);
    $res = Dal::query($sql, $data);

    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);

    if ($flag == 0) {
      if ($res->numRows() > 0 || strtolower($folder_name) == strtolower(INBOX) || strtolower($folder_name) == strtolower(SENT) || strtolower($folder_name) == strtolower(DRAFT)) {
        $result = false;
      }
      else {
        $sql = "INSERT INTO {message_folder} (uid, name) values (?, ?)";
        $data = array($user_id, $folder_name);
        $res = Dal::query($sql, $data);

        $result = true;
      }
    }
    else {
      if (strtolower($folder_name) == 'inbox' || strtolower($folder_name) == 'sent' || strtolower($folder_name) == 'draft') {
        $result = false;
      }
      else {
        $sql = "UPDATE {message_folder} SET name = ? WHERE fid = ?";
        $data = array($folder_name, $row->fid);
        $res = Dal::query($sql, $data);

        $result = true;
      }
    }
    Logger::log("Exit: function Message::create_folder()");
    return $result;
  }

  /**
  * returns all folders for user.
  *
  * @param int user_id of user for whom the folder is to be created.
  * @param int flag for getting sent folder id.
  */
  static function get_user_folders($user_id, $show_all = FALSE) {
    Logger::log("Enter: function Message::get_user_folders()");

    if ($show_all) {
      $sql = "SELECT * from {message_folder} WHERE uid = ?";
      $data = array($user_id);
    } else {
      $sql = "SELECT * from {message_folder} WHERE uid = ? AND name NOT IN (?, ?, ?)";
      $data = array($user_id, INBOX, SENT, DRAFT);
    }
    $res = Dal::query($sql, $data);

    $folder = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $folder[] = array('fid' => $row->fid, 'name' => stripslashes($row->name));
    }

    Logger::log("Exit: function Message::get_user_folders()");
    return $folder;
  }

  /**
  * Move message to folder.
  *
  * @param array id of the message.
  * @param int new id of the folder.
  */
  static function move_message_to_folder($index_ids, $new_folder_id, $message_id = null) {

   Logger::log("Enter: function Message::move_message_to_folder()");
   $temp_arr =Array();
   $sql = "UPDATE {user_message_folder} SET fid = ? WHERE index_id = $index_ids[0]";
   $sql_mess = "UPDATE {private_messages} SET sent_time = ? WHERE ";
   foreach($message_id as $message_ids){
     $message_value =explode("~" , $message_ids);
      if ($index_ids[0] == $message_value[1]){
        $sql_mess .= "message_id  =  ".$message_value[0];
         $temp_arr[] =  $message_value[0];
      }
   }
    for ($i = 1;$i < count($index_ids);$i++) {
          $sql .= " OR index_id = $index_ids[$i]";
          foreach($message_id as $message_ids){
            $message_value =explode("~",$message_ids);
            if (in_array($message_value[0], $temp_arr)){
             continue;
            }
            if ($index_ids[$i] == $message_value[1]){
              $temp_arr[] = $message_value[0];
              $sql_mess .= " OR message_id  =  ".$message_value[0];
            }
          }
   }


   $data = array(time());
   $res = Dal::query($sql_mess, $data);
   $data = array($new_folder_id);
   $res = Dal::query($sql, $data);

    Logger::log("Exit: function Message::move_message_to_folder()");
    return;
  }

  /**
  * Create basic folders for user.
  *
  * @param int user_id of the user.
  */
  static function create_basic_folders($user_id) {
    Logger::log("Enter: function Message::create_basic_folders()");

    $sql = "INSERT INTO {message_folder} (uid, name) VALUES (?, ?)";
    $data = array($user_id, INBOX);
    $res = Dal::query($sql, $data);

    $sql = "INSERT INTO {message_folder} (uid, name) VALUES (?, ?)";
    $data = array($user_id, SENT);
    $res = Dal::query($sql, $data);

    $sql = "INSERT INTO {message_folder} (uid, name) VALUES (?, ?)";
    $data = array($user_id, DRAFT);
    $res = Dal::query($sql, $data);

    Logger::log("Exit: function Message::create_basic_folders()");
    return;
  }

  /**
  * Search mails for the user.
  *
  * @param int user_id of the user.
  * @param string name to be searched.
  * @param string sort factor.
  */
  static function search_mail($user_id, $search_string, $page_no, $msg_per_page, $sortby, $flag) {
    Logger::log("Enter: function Message::search_mail");

    $search_string = "%$search_string%";

    if ($flag == 0) {
      $sort = 'DESC';
    }
    else {
      $sort = "";
    }
    if ($sortby == 'sender') {
      $sortby = 'login_name';
    }

    $sql = "SELECT COUNT(message_id) as total FROM {private_messages} AS P, {users} AS U, {user_message_folder} as UM, {message_folder} as MF WHERE MF.uid = ? AND UM.fid = MF.fid AND UM.mid = P.message_id AND U.user_id = P.sender_id AND MF.name NOT IN (?) AND (U.login_name like '$search_string' OR P.subject like '$search_string' OR P.body like '$search_string') ORDER BY ".Message::validate_sortby($sortby)." ".$sort."";
    $data = array($user_id, DRAFT);
    $res = Dal::query($sql, $data);

    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $total = $row->total;

    $count1 = ($page_no - 1) * $msg_per_page;
    $count2 = 10;

    $sql = "SELECT * FROM {private_messages} AS P, {users} AS U, {user_message_folder} as UM, {message_folder} as MF WHERE MF.uid = ? AND UM.fid = MF.fid AND UM.mid = P.message_id AND U.user_id = P.sender_id AND MF.name NOT IN (?) AND (U.login_name like '$name' OR P.subject like '$search_string' OR P.body like '$search_string') ORDER BY ".Message::validate_sortby($sortby)." ".$sort." LIMIT ?, ?";
    $data = array($user_id, DRAFT, $count1, $count2);
    $res = Dal::query($sql, $data);

    $i = 0;
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
    	$u = new User();
    	$u->load((int)$row->user_id);
      $selected[] = array(
      	'sender_name' => stripslashes($u->display_name),
      	'all_recipients' => stripslashes($row->all_recipients),
      	'sent_time' => $row->sent_time,
      	'subject' => stripslashes($row->subject),
      	'body' => stripslashes($row->body),
      	'message_id' => $row->message_id,
      	'sender_id' => $row->sender_id,
      	'fid' => $row->fid,
      	'size' => $row->size,
      	'index_id' => $row->index_id,
      	'reply' => $row->reply,
      	'forward' => $row->forward,
      	'total' => $total);
      $i++;
    }
    Logger::log("Exit: function Message::search_mail");
    return $selected;
  }

  /**
  * search a folder for user by name.
  *
  * @param integer id of the user.
  * @param string name of the folder.
  */
  static function get_folder_by_name($user_id, $folder_name) {
    Logger::log("Enter: function Message::get_folder_by_name");

    $sql = "SELECT fid from {message_folder} WHERE uid = ? AND name = ?";
    $data = array($user_id, $folder_name);
    $res = Dal::query($sql, $data);

    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $folder_id = null;
    if ($res->numRows()) {
      $folder_id = $row->fid;
    }

    Logger::log("Exit: function Message::get_folder_by_name");
    return $folder_id;
  }

  /**
  * search a folder for user by id.
  *
  * @param integer id of the folder.
  */
  static function get_folder_by_id($folder_id) {
    Logger::log("Enter: function Message::get_folder_by_id");

    $sql = "SELECT * from {message_folder} WHERE fid = ?";
    $data = array($folder_id);
    $res = Dal::query($sql, $data);

    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $result = array('fid' => $row->fid, 'name' => stripslashes($row->name));

    Logger::log("Exit: function Message::get_folder_by_id");
    return $result;
  }

  /**
  * Edit message in Draft.
  *
  * @param integer id of user.
  * @param string all recipients.
  * @param string subject of message.
  * @param text body of message.
  * @param array id of the message.
  */
  static function edit_message_to_draft($sender_id, $all_recipients, $subject, $body, $message_id) {
    Logger::log("Enter: function Message::edit_message_to_draft");

    $sql = "UPDATE {private_messages} SET message_id = ?, sender_id = ?, all_recipients = ?, subject = ?, body = ?, sent_time = ?, size = ? WHERE message_id = ?";
    $data = array($message_id, $sender_id, $all_recipients, $subject, $body, time(), ceil(strlen($body)/1024), $message_id);
    $res = Dal::query($sql, $data);

    Logger::log("Exit: function Message::edit_message_to_draft");
    return;
  }

  /**
  * change status of message ie reply or forward.
  *
  * @param integer id of the message.
  * @param string action.
  */
  static function change_status($id, $action) {
    Logger::log("Enter: function Message::change_status");

    if ($action == REPLY) {
      $sql = "UPDATE {user_message_folder} SET reply = ? WHERE index_id = ?";
    }
    elseif ($action == FORWARD) {
      $sql = "UPDATE {user_message_folder} SET forward = ? WHERE index_id = ?";
    }
    $data = array(1, $id);
    $res = Dal::query($sql, $data);

    Logger::log("Exit: function Message::change_status");
    return;
  }

  /**
  * returns unread message count.
  *
  * @param integer id of the message.
  * @param string action.
  */
  static function get_new_msg_count($user_id) {
    Logger::log("Enter: function Message::get_new_msg_count");

    $folder_id = Message::get_folder_by_name($user_id, INBOX);

    $sql = "SELECT new_msg from {user_message_folder} WHERE fid = ?";
    $data = array($folder_id);
    $res = Dal::query($sql, $data);

    $new_msg_count = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $new_msg_count[] = $row->new_msg;
    }

    $unread_msg = 0;
    for ($m = 0;$m < count($new_msg_count);$m++) {
      if ($new_msg_count[$m] == 1) {
        $unread_msg++;
      }
    }

    $unread = array('unread_msg' => $unread_msg, 'total' => count($new_msg_count));

    Logger::log("Exit: function Message::get_new_msg_count");
    return $unread;
  }

  /**
  * function used to delete the message for a given user
  */

  public static function delete_user_messages( $user_id ) {
    Logger::log("Enter: function Message::delete_user_messages");

    $sql = 'DELETE FROM MF, UMF, PM USING {message_folder} AS MF, {user_message_folder} AS UMF, {private_messages} AS PM WHERE MF.fid = UMF.fid AND UMF.mid = PM.message_id AND MF.uid = ?';
    $data = array($user_id);
    if( !$res = Dal::query( $sql, $data ) ) {
      Logger::log("Throwing exception in function Message::delete_user_messages while message folders");
      throw new PAException(DELETION_FAILED, "Deletion failed in message_folder.");
    }



    Logger::log("Exit: function Message::delete_user_messages");
  }

  /**
  * This function will get the folder name of the given index id
  * @param index_id
  * @return folder_name
  */
  public static function get_message_folder($index_id) {
    Logger::log("Enter: function Message::get_message_folder");

    $sql = 'SELECT fid FROM {user_message_folder} WHERE index_id = ?';
    $res = Dal::query($sql, array($index_id));
    $folder_name = null;
    if ($res->numRows()) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $fid = $row->fid;

      $sql = 'SELECT name FROM {message_folder} WHERE fid = ?';
      $res = Dal::query($sql, array($fid));

      if ($res->numRows()) {
        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $folder_name = $row->name;
      }
    }

    Logger::log("Exit: function Message::get_message_folder");
    return $folder_name;
  }

  /**
  * Function for searching mails
  * @param $user_id: user id of the user searching for mails.
  * @param $search_string: text to be searched
  * @param $page_no: page number of the search results, default is page 1
  * @param $msg_per_page: number of messages to be shown on one page
  * @param $sort_by: sorting criterion of the searched messages.
  * @param $sort_flag: direction fo sort, i.e DESC of ASC
  * @return array of messages.
  */
  public static function search($user_id, $search_string=null, $page_no = 1, $msg_per_page=MESSAGES_PER_PAGE, $sort_by='sent_time', $sort_flag='DESC') {
    Logger::log("Enter: function Message::search");

    $folders = Message::get_user_folders($user_id, true);
    $sort_by = "SORT BY $sort_by $sort_flag";
    $counter = 0;
    $messages = array();
    if (count($folders)) {
      foreach ($folders as $folder) {
        $sql = 'SELECT * FROM {private_messages} AS PM LEFT JOIN {user_message_folder} AS UMF ON PM.message_id = UMF.mid WHERE UMF.fid =? AND (PM.all_recipients like ? OR PM.subject like ? OR PM.body like ?)';
        $search_string = "%$search_string%";
        $data = array($folder['fid'], $search_string, $search_string, $search_string);
        $res = Dal::query($sql, $data);

        if ($res->numRows()) {
          while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            try {
              $User = new User();
              $User->load((int)$row->sender_id);
              $messages[$counter]['index_id'] = $row->index_id;
              $messages[$counter]['all_recipients'] = $row->all_recipients;
              $messages[$counter]['subject'] = $row->subject;
              $messages[$counter]['body'] = $row->body;
              $messages[$counter]['sender_id'] = $row->sender_id;
              $messages[$counter]['message_id'] = $row->message_id;
              $messages[$counter]['sent_time'] = $row->sent_time;
              $messages[$counter]['folder_name'] = $folder['name'];
              $messages[$counter]['sender_name'] = $User->display_name;
              $counter++;
            } catch (PAException $e) {
             // Caution :When user is deleted
            }
          }
        }
      }
    }

    Logger::log("Exit: function Message::search");
    return $messages;
  }

  /// validate_sortby($sortby)
  // Ensures that a given value is actually a column in the
  // private_messages table.  If true, returns the column name.  If
  // false, returns "NULL", which should hopefully result in an
  // unsorted result.
  private static function validate_sortby($sortby) {

    switch ($sortby) {
    case 'sent_time':
      // Add more valid sort columns here.
      return $sortby;
    }
    return "NULL"; // Note: not NULL, "NULL", as this will be included directly into an SQL string!
  }

}
?>
