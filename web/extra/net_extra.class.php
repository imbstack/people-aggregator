<?php
/*

This file is used to update network configuration, as network
behaviour and the network API change over time.

This is run at the end of db_update.php, after all other updates.  So
take care not to make anything in db_update.php depend on these
changes!

*/

if (!defined("PA_DISABLE_BUFFERING")) define("PA_DISABLE_BUFFERING", TRUE);
ini_set('max_execution_time', 1200);
ini_set('max_input_time', 1200);

require_once dirname(__FILE__).'/../../config.inc';
require_once 'db/Dal/Dal.php';
require_once "web/includes/network.inc.php";
require_once 'api/User/User.php';
require_once "api/Forum/PaForumBoard.class.php";
require_once "ext/Group/Group.php";

class net_extra {

  function __construct() {
    $this->admin_id = 1;
    // first, add an entry for the 'mother network' (if necessary)
    $this->add_mother_network();

  }

  public function getOldNetworks() {
    $old_networks = array();
    $res = Dal::query("SELECT * FROM {networks}");
    while ( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
      $old_networks[] = $row;
    }
    return $old_networks;
  }

  // Run all updates which should only ever be run ONCE - i.e. things
  // which will destroy existing data if present.
  function once_only_updates() {
    $this->change_extra_field();
  }

  // Run all updates which are safe to run more than once.  These will
  // be run at the end of the update process.
  function safe_updates() {
    global $network_info;

    PA::$network_info = $network_info = Network::get_mothership_info();
    PA::$extra = unserialize(PA::$network_info->extra);
    $this->create_tables();
    $this->add_to_networks_users();
    $this->update_owner_notifications();
    $this->update_personal_notifications();
//    $this->update_personal_notifications_for_owner();
//    $this->update_members(); // DON'T RUN NOW AS member_count AND owner_id WILL NOT EXIST IN THE networks TABLE WHEN THIS SCRIPT IS RUN.

    // run project specific updates and init settings
    global $_PA;
    if(! empty($_PA->project_safe_updates)) {
      // see if the settings file exists
      $file_path = PA::$core_dir . "/web/extra/".$_PA->project_safe_updates."_safe_updates.php";
      if(file_exists($file_path)) {
        include($file_path);
      }
    }
    $this->fix_forum_boards();
//    $this->assign_group_roles();
    $this->updateRelationShips();
//    echo "<pre>Networks updated successfully</pre>\n";
  }

  /* Purpose : Change extra field according to new format.
  This format is defined in network.inc.php
  */
  function change_extra_field() {
    global $network_controls;
//    echo "<pre>Changing the extra field of existing networks (once-only version)</pre>\n";
    if (is_array($this->getOldNetworks())){
      foreach ( $this->getOldNetworks() as $net ) {
        $tmp = $network_controls;
        $header_image = $net->header_image;
        $add = trim($net->address);
        $tmp['basic']['header_image']['name'] = $header_image;
        $tmp['basic']['header_image']['option'] = DESKTOP_IMAGE_ACTION_STRETCH;
        //put extra field data according to new structure
        Dal::query("update {networks} set  extra = ? where  network_id = ? ",array(serialize($tmp),$net->network_id));
      }
    }
  }

  /* Purpose : Delete all personal and group boards,
     add network_id column to pa_forum_boards DB table
  */
  function fix_forum_boards() {
    $is_updated = false;
    if($this->table_exists("pa_forum_board")) {
      $clmns = Dal::query("SHOW COLUMNS FROM `pa_forum_board`");
      while($row = $clmns->fetchRow(DB_FETCHMODE_ASSOC)) {
        if($row['Field'] == 'network_id') {
          $is_updated = true;
        }
      }
      if(!$is_updated) {
        Dal::query("ALTER TABLE `pa_forum_board` ADD `network_id` INT NOT NULL");
        Dal::query("ALTER TABLE `pa_forum_board` ADD INDEX ( `network_id` )");

        $boards = PaForumBoard::listPaForumBoard( "type <> 'network'" );
        foreach($boards as $board) {
          PaForumBoard::deletePaForumBoard($board->get_id());
        }

        $net_boards = PaForumBoard::listPaForumBoard( "type = 'network'" );
        foreach($net_boards as $n_board) {
          $n_board->set_network_id($n_board->get_owner_id());  // - owner is network
          $n_board->save_PaForumBoard();
        }
      }
    }
  }


  /* Purpose : Create new table announcement for each network
  */
  function create_tables() {
    global $network_controls;
//    echo "<pre>Creating tables for existing networks</pre>\n";
    if (is_array($this->getOldNetworks())){
      foreach ( $this->getOldNetworks() as $net ) {
        $add = trim($net->address);
        Dal::query("CREATE TABLE IF NOT EXISTS ".$add."_announcements (
      content_id int(11) NOT NULL,
      announcement_time int(11) NOT NULL default '0',
      position tinyint(1) NOT NULL default '0',
      status tinyint(1) NOT NULL default '0',
      is_active tinyint(1) NOT NULL default '0'
    )");
      }
    }
  }

  function add_mother_network() { // safe to run this more than once
    global $network_controls;
    if (!$network_controls) die("\$network_controls not available");

    $q = Dal::query_one_object('SELECT * FROM {networks} WHERE type=1');
    if (!empty($q)) return; // mother network already exists

//    echo "<pre>Adding mother network</pre>\n";
    $name = "PeopleAggregator";
    $address = 'default';
    $type = 1;
    $tagline='PeopleAggregator';
    $category_id = 0;
    $description = 'This is home network for PeopleAggregator';
    $created = $changed = time();
    $extra = serialize($network_controls);
    $res = Dal::query("INSERT INTO {networks} (name, address, tagline, type,category_id, description,is_active, created, changed, extra) VALUES ( ?, ?, ?,?, ?, ?, ?, ?, ?, ? )", array($name, $address, $tagline, $type, $category_id, $description, 1, $created, $changed, $extra));
    return;
  }

  // add all users to the mother network
  function add_to_networks_users() {
//    echo "<pre>Adding all users to the mother network</pre>\n";
    $res = Dal::query("select network_id from {networks} where type=1");
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $mother_id = $row->network_id;
    $q = Dal::query_one_object("SELECT * FROM {networks_users} WHERE network_id=$mother_id");
    if ($q) {
      return;
    }
    $res = Dal::query("select user_id from {users} where is_active=1");
    $result_data = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      $result_data[] = $row;
    }
    foreach($result_data as $key=>$value) {
      $type = ($value['user_id']==$this->admin_id) ? 'owner' : 'member';
      $sql = "INSERT INTO {networks_users} (network_id,user_id,user_type) values(?,?,?)";
      $res = Dal::query($sql,array($mother_id,$value['user_id'],$type));
    }
  }
  // This function will find out intersect of two arrays
  // and retain the value of first array.
  // it will return an array having intersected keys and value from first array
  function array_intersect_key($first,$second)
  {
    $result = array();
    if (!empty($first)) {
      foreach ($first as $key=>$val) {
  if (@$second[$key]) {
    $result[$key]=$val;
  }
      }
    }
    return $result;
  }

  /* Purpose : Change extra field of network for email notification according to new format.
  This format is defined in network.inc.php
  */
  function update_owner_notifications() {
  $new_notification_owner =
                    array(
                      'some_joins_a_network'=>array(
                                                  'caption'=>'some one joins a network',
                                                  'value'=>NET_MSG
                                                  ),
                      'content_posted'=>array(
                                                  'caption'=>'posts or content is created',
                                                  'value'=>NET_MSG
                                                  ),
                      'group_created'=>array(
                                                  'caption'=>'a group is created on the network',
                                                  'value'=>NET_BOTH
                                                  ),
                      'group_settings_updated'=>array(
                                                  'caption'=>'group settings data changed',
                                                  'value'=>NET_MSG
                                                  ),
                      'media_uploaded'=>array(
                                                  'caption'=>'media is uploaded to a user gallery',
                                                  'value'=>NET_MSG
                                                  ),
                      'group_media_uploaded'=>array(
                                                  'caption'=>'media is uploaded to a group gallery',
                                                  'value'=>NET_MSG
                                                  ),
                      'relation_added'=>array(
                                                  'caption'=>'a new relation is established',
                                                  'value'=>NET_MSG
                                                  ),
                      'reciprocated_relation_estab'=>array(
                                                  'caption'=>'a reciprocated relationship establsihed',
                                                  'value'=>NET_MSG
                                                  ),
                      'content_posted_to_comm_blog'=>array(
                                                  'caption'=>'new content is sent to the home page community blog',
                                                  'value'=>NET_BOTH
                                                  ),
                      'report_abuse_on_content'=>array(
                                                  'caption'=>'report abuse on contents',
                                                  'value'=>NET_BOTH
                                                  ),
                      'report_abuse_on_comment'=>array(
                                                  'caption'=>'report abuse on comments',
                                                  'value'=>NET_BOTH
                                                  ),
                      'content_modified'=>array(
                                                  'caption'=>'content has been modified',
                                                  'value'=>NET_MSG
                                                  ),
                      'new_user_registered'=>array(
                                                  'caption'=>'new user registered on the network',
                                                  'value'=>NET_MSG
                                                  )
                  );
//    echo "<pre>Changing the extra field of existing networks</pre>\n";
    if (is_array($this->getOldNetworks())){
      foreach ( $this->getOldNetworks() as $net ) {
        $extra = unserialize($net->extra);
/*
        $old_notification_owner = @$extra['notify_owner'];
        $res_owner = $this->array_intersect_key($old_notification_owner, $new_notification_owner);
*/
        unset($extra['notify_owner']);
        foreach($new_notification_owner as $key=>$value) {
            $extra['notify_owner'][$key] = $value;
        }
/*
        $extra['notify_owner'] = $res_owner;
*/
        Dal::query("update {networks} set  extra = ? where  network_id = ? ",array(serialize($extra),$net->network_id));
      }
    }
  }

  function update_members() {
//    echo "<pre>About to update the 'networks' table</pre>\n";
    $sql = 'select network_id from {networks} where is_active =1';
    $res = Dal::query($sql);
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      $result[] = $row['network_id'];
    }
    $cnt = count($result);
    for ( $i=0; $i<$cnt; $i++) {
      $nid = $result[$i];
      $update_sql ="UPDATE {networks} SET member_count=(SELECT COUNT(*) FROM {networks_users} WHERE network_id = $nid AND user_type <> 'waiting_member') WHERE network_id = $nid ";
      $res = Dal::query($update_sql);
    }
//    echo "<pre>All networks updated successfully</pre>\n";
  }


  function update_personal_notifications() {
//    echo "<pre>Adding extra notification options to all networks.</pre>\n";
    $new_notification_personal = array(
                      'invitation_accept'=>array(
                                 'caption'=>'join network invitations have been accepted.',
                                 'value'=>NET_BOTH,
                                 'user_settable' => true
                                                  ),
                      'invite_accept_group'=>array(
                                 'caption'=>'join group invitations have been accepted.',
                                 'value'=>NET_BOTH,
                                 'user_settable' => true
                                                  ),
                      'relationship_created_with_other_member'=>array(
                                 'caption'=>'someone has made them a friend or other kind of relation',
                                 'value'=>NET_BOTH,
                                 'user_settable' => true
                                                  ),
                      'someone_join_their_group'=>array(
                                 'caption'=>'someone has joined a group they created',
                                 'value'=>NET_BOTH,
                                 'user_settable' => true
                                                  ),
                      'friend_request_sent'=>array(
                                 'caption'=>'someone has sent them a friend request',
                                 'value'=>NET_BOTH,
                                 'user_settable' => true
                                                  ),
                      'friend_request_approved'=>array(
                                 'caption'=>'someone has approve your friend request',
                                 'value'=>NET_BOTH,
                                 'user_settable' => true
                                                  ),
                      'friend_request_denial'=>array(
                                 'caption'=>'someone has denied to be their friend',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                                    ),
                      'bulletin_sent'=>array(
                                 'caption'=>'network operator has sent a bulletin',
                                 'value'=>NET_BOTH,
                                 'user_settable' => true
                                            ),
                      'welcome_message' => array(
                                 'caption' => 'Welcome message',
                                 'value' => NET_BOTH,
                                 'user_settable' => false
                                            )
                                      );
    if (is_array($this->getOldNetworks())) { // if old network exist
      foreach ( $this->getOldNetworks() as $net ) {
        $extra = unserialize($net->extra);
        // add key and values to the existing array
        foreach($new_notification_personal as $key=>$value) {
            $extra['notify_members'][$key] = $value;
        }
        $extra['msg_waiting_blink'] = false;
        // save array to the data base
        Dal::query("update {networks} set  extra = ? where  network_id = ? ",array(serialize($extra), $net->network_id));
        $extra['notify_members']['msg_waiting_blink'] = false;
      }
      // update notifications settings for all networks - all users
      $new_memb_settings =  serialize($extra['notify_members']);
      Dal::query("UPDATE user_profile_data SET field_value='$new_memb_settings' WHERE field_type='notifications'");
      echo $net->name . " notification settings has been updated.\n\n";
    } // End if old network exist
  } // End of function update_personal_notifications

/*
  protected function updateUserNotifications($new_settings, $net_id) {
    $users = array();
    $users_ids = array();
    $users = Network::get_members(array('network_id' => $net_id));
    if ( $users['total_users'] ) {
       for( $i = 0; $i < $users['total_users']; $i++) {
          $users_ids[] = $users['users_data'][$i]['user_id'];
       }
    }
    foreach($users_ids as $user_id) {
      try{
        $curr_user = new User();
        $curr_user->load((int)$user_id);
        $curr_user->set_profile_field('notifications', 'settings', serialize($new_settings));
        $curr_user->save();
      } catch (PAException $e) {
        $error = TRUE;
        $error_msg = "$e->message";
      }
    }
  }
*/
/*
    function update_personal_notifications_for_owner() {
//    echo "<pre>Adding extra notification options for owner to all networks.</pre>\n";

    // add your field here
    $add_extra_field = array(
                      'report_abuse_on_content'=>array(
                                                 'caption'=>'report abuse',
                                                 'value'=>NET_YES
                                                 )
                       );

    if (is_array($this->getOldNetworks())) { // if old network exist
      foreach ( $this->getOldNetworks() as $net ) {
        $extra = unserialize($net->extra);
        // add key and values to the existing array
        foreach($add_extra_field as $key=>$value) {
          if (!isset($extra['notify_owner'][$key]))
            $extra['notify_owner'][$key] = $value;
        }
        // save array to the data base
        Dal::query("update {networks} set  extra = ? where  network_id = ? ",array(serialize($extra), $net->network_id));
  //        echo $net->name . ' has been updated with extra notifications';
      }
    } // End if old network exist
  }
*/
  function table_exists($tablename){
    //$sql = "DESCRIBE $tablename";
    $sql = "SHOW TABLES LIKE '".Dal::quote($tablename)."'";
    $res = Dal::query($sql);
    while(list($tname) = Dal::row($res)) {
     if ($tname == $tablename) {
        return TRUE;
      }
    }
    return FALSE;

  }


  private function assign_group_roles(){
    $groups_arr = Group::get_all();
    foreach($groups_arr as $_group) {
      $group = new Group();
      $group->load((int)$_group['group_id']);
      $members = $group->get_members();
      foreach($members as $member) {
        $user_roles = array();
        $user = new User();
        $user->load((int)$member['user_id']);
        $role_extra = array('user' => false, 'network' => false, 'groups' => array((int)$group->group_id));
        $user_roles[0] = array('role_id' => null, 'extra' => serialize($role_extra));

        switch($member['user_type']) {
          case OWNER :
              $user_roles[0]['role_id'] = 4;
          break;
          case MODERATOR :
              $user_roles[0]['role_id'] = 5;
          break;
          case MEMBER :
              $user_roles[0]['role_id'] = 6;
          break;
        }
        $user->set_user_role($user_roles);
      }
    }
  }

  private function updateRelationShips() {
    $networks = $this->getOldNetworks();
    if (is_array($networks)) { // if old network exist
      foreach ($networks as $net ) {
       $sql = " UPDATE relations SET network = ?, network_uid = ?
                WHERE relations.user_id = (SELECT user_id FROM networks_users WHERE user_id = relations.user_id AND network_id = ?)
                AND relation_id = (SELECT user_id FROM networks_users WHERE user_id = relations.relation_id AND network_id = ?)";
       $data = array($net->address, $net->network_id, $net->network_id, $net->network_id);
       Dal::query($sql, $data);
   /*
        $members = Network::get_network_members((int)$net->network_id);
        foreach($members['users_data'] as $member) {
          $relations = Relation::get_relations((int)$member['user_id']);
          foreach($relations as $relation) {
            if(Network::member_exists((int)$net->network_id, (int)$relation)) {
              Relation::update_relation_network_info((int)$member['user_id'], (int)$relation, $net->address, $net->network_id);
            }
          }
        }
*/
      }
    }
  }

} // End of class
?>