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

<!-- This script creates a mailbox for every user in the system except for the users those who already have.
It creates three basic folders ie Inbox, Sent, Draft.-->

<?php
require_once '../config.inc';
require_once '../api/User/User.php';
require_once '../api/Message/Message.php';
require_once "db/Dal/Dal.php";

$db = Dal::get_connection();

$sql = 'SELECT user_id FROM users';
$res = $db->query($sql, $data);
if (PEAR::isError($res)) {
  throw new PAException(DB_QUERY_FAILED, $res->getMessage());
}

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {

  $inbox_id = Message::get_folder_by_name($row->user_id, 'Inbox');
  if (!$inbox_id) {
    Message::create_basic_folders($row->user_id);
  }
}
?>