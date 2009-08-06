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

    $this->create_announce_tables();
    $this->add_all_users_to_mother_network();

    // run project specific updates and init settings

    if(! empty(PA::$config->project_safe_updates)) {
      // see if the settings file exists
      $file_path = PA::$core_dir . "/web/extra/".PA::$config->project_safe_updates."_safe_updates.php";
      if(file_exists($file_path)) {
        include($file_path);
      }
    }
  }

  // Purpose : Change extra field according to new format.
  // This format is defined in network.inc.php
  function change_extra_field() {
//    echo "<pre>Changing the extra field of existing networks (once-only version)</pre>\n";
    if (is_array($this->getOldNetworks())){
      foreach ( $this->getOldNetworks() as $net ) {
        $tmp = PA::$network_defaults;
        $header_image = $net->header_image;
        $add = trim($net->address);
        $tmp['basic']['header_image']['name'] = $header_image;
        $tmp['basic']['header_image']['option'] = DESKTOP_IMAGE_ACTION_STRETCH;
        //put extra field data according to new structure
        Dal::query("update {networks} set  extra = ? where  network_id = ? ",array(serialize($tmp),$net->network_id));
      }
    }
  }


  /* Purpose : Create new table announcement for each network
  */
  function create_announce_tables() {
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
    if (!PA::$network_defaults) die("PA::\$network_defaults not available");

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
    $extra = serialize(PA::$network_defaults);
    $res = Dal::query("INSERT INTO {networks} (name, address, tagline, type,category_id, description,is_active, created, changed, extra, owner_id) VALUES ( ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ? )", array($name, $address, $tagline, $type, $category_id, $description, 1, $created, $changed, $extra, SUPER_USER_ID));
    return;
  }

  // add all users to the mother network
  function add_all_users_to_mother_network() {
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
} // End of class
?>