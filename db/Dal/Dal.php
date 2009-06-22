<?php
require_once 'DB.php';

require_once "api/Logger/Logger.php";
require_once "api/PAException/PAException.php";

// to figure out the table name to get to network data
require_once "db/table_mappings.php";

class Dal {

  static $_connection = 0;

  static $_query_log = NULL;
  static $_query_callbacks = NULL;
  static $_query_start_time = NULL;

  public static function get_connection() {
    if (!Dal::$_connection) {
      global $peepagg_dsn;
      Dal::$_connection = DB::connect($peepagg_dsn, array("autofree" => true));
      if (PEAR::isError(Dal::$_connection)) {
  $msg = Dal::$_connection->getMessage();
  if (strpos($msg, "no such database") != -1) {
    ?>

<h1>Database not found</h1>

<p>Please check your <code>local_config.php</code> file and ensure that the <code>$peepagg_dsn</code> variable is set and points to a valid database.</p>

<p>Currently, either the database doesn't exist, the user doesn't exist, or the password in <code>$peepagg_dsn</code> is incorrect.</p>

   <?php
          exit;
  }
  // otherwise we throw an exception
        throw new PAException(DB_CONNECTION_FAILED, "Database connection failed");
      }
      // connection succeeded - turn off automatic committing
      Dal::$_connection->autoCommit(FALSE);

    }
    return Dal::$_connection;
  }

  public static function disconnect() {
    if(Dal::$_connection) {
      DB::disconnect();
    }
  }


  // Query logging - for performance testing
  public static function start_logging_queries() {
    Dal::$_query_log = array();
  }

  public static function stop_logging_queries() {
    $query_log = Dal::$_query_log;
    Dal::$_query_log = NULL;
    return $query_log;
  }

  public static function register_query_callback($cb) {
    if (!Dal::$_query_callbacks) Dal::$_query_callbacks = array();
    Dal::$_query_callbacks[] = $cb;
  }

  public static function unregister_query_callback($cb) {
    $pos = array_search($cb, Dal::$_query_callbacks);
    if ($pos !== FALSE) {
      unset(Dal::$_query_callbacks[$pos]);
    }
    if (!count(Dal::$_query_callbacks)) Dal::$_query_callbacks = NULL;
  }

  private static function execute_pre_hooks($sql, $args) {
    // start timing query
    Dal::$_query_start_time = microtime(TRUE);
  }

  private static function execute_post_hooks($sql, $args) {
    $query_time = microtime(TRUE) - Dal::$_query_start_time;
    
    // log query, if logging is started
    if (Dal::$_query_log !== NULL) {
      Dal::$_query_log[] = $sql;
    }

    // pass query to callback, if one is registered
    if (Dal::$_query_callbacks) foreach (Dal::$_query_callbacks as $cb) {
      $cb($sql, $args, $query_time);
    }
  }


  // Some convenience functions so we don't have to jump through hoops every time we want
  // to access some data...

  public static function query($sql, $args=NULL) {
    global $query_count_on_page;

    $db = Dal::get_connection();
    $sql = Dal::validate_sql ($sql);
    
    Dal::execute_pre_hooks($sql, $args);
    $sth = $db->query($sql, $args);
    Dal::execute_post_hooks($sql, $args);
    
    if (PEAR::isError($sth)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED | Message: ".$sth->getMessage().
        " | SQL that caused this exception: ".$sql, LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $sth->userinfo);
    }

    // for query count -- this line is added temporarliy for counting queries per page
    $query_count_on_page++;
    //include "query_page.php";
    return $sth;
  }

  /* run a query multiple times and return the actual *data*; this assumes each query only ever returns one row */
  public static function query_multiple($sql, $args=array(NULL)) {
    global $query_count_on_page;

    $db = Dal::get_connection();
    $sql = Dal::validate_sql ($sql);

    $prh = $db->prepare($sql);
    if (PEAR::isError($prh)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED while preparing a query for multiple execution | Message: ".$prh->getMessage().
        " | SQL that caused this exception: ".$sql, LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $prh->getMessage());
    }

    $ret = array();
    foreach ($args as $params) {
      Dal::execute_pre_hooks($sql, $params);
      $sth = $db->execute($prh, $params);
      Dal::execute_post_hooks($sql, $params);
      if (PEAR::isError($sth)) {
	Logger::log(" Throwing exception DB_QUERY_FAILED while in multiple query execution | Message: ".$sth->getMessage().
		    " | SQL that caused this exception: ".$sql, LOGGER_ERROR);
	throw new PAException(DB_QUERY_FAILED, $prh->getMessage());
      }
      $ret[] = $sth->fetchRow(DB_FETCHMODE_ASSOC);
      $query_count_on_page++;
    }

    $db->freePrepared($prh);

    return $ret;
  }

  /* run an UPDATE/etc query multiple times */
  public static function execute_multiple($sql, $args=array(NULL)) {
    global $query_count_on_page;

    $db = Dal::get_connection();
    $sql = Dal::validate_sql ($sql);

    $prh = $db->prepare($sql);
    if (PEAR::isError($prh)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED while preparing a query for multiple execution | Message: ".$prh->getMessage().
        " | SQL that caused this exception: ".$sql, LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $prh->getMessage());
    }

    foreach ($args as $params) {
      Dal::execute_pre_hooks($sql, $params);
      $sth = $db->execute($prh, $params);
      Dal::execute_post_hooks($sql, $params);
      if (PEAR::isError($sth)) {
	Logger::log(" Throwing exception DB_QUERY_FAILED while in multiple query execution | Message: ".$sth->getMessage().
		    " | SQL that caused this exception: ".$sql, LOGGER_ERROR);
	throw new PAException(DB_QUERY_FAILED, $sth->getMessage());
      }
      $query_count_on_page++;
    }

    $db->freePrepared($prh);

    return TRUE;
  }

  public static function all($sth) {
    $ret = array();
    while ($r = Dal::row($sth)) $ret[] = $r;
    return $ret;
  }

  public static function all_assoc($sth) {
    $ret = array();
    while ($r = Dal::row_assoc($sth)) $ret[] = $r;
    return $ret;
  }

  public static function row($sth) {
    return $sth->fetchRow(DB_FETCHMODE_ORDERED);
  }

  public static function row_assoc($sth) {
    return $sth->fetchRow(DB_FETCHMODE_ASSOC);
  }

   public static function row_object($sth) {
    return $sth->fetchRow(DB_FETCHMODE_OBJECT);
  }

  public static function query_one($sql, $args=NULL) {
    $sth = Dal::query($sql, $args);
    $ret = Dal::row($sth);
    return $ret;
  }

  // Run a query and return the first value in the first row of the results.
  // e.g. $ct = Dal::query_first("SELECT COUNT(*) FROM foo");
  public static function query_first($sql, $args=NULL) {
    $r = Dal::query_one($sql, $args);
    if (!$r) return $r;
    return $r[0];
  }

  public static function query_one_assoc($sql, $args=NULL) {
    $sth = Dal::query($sql, $args);
    $ret = Dal::row_assoc($sth);
    return $ret;
  }

  public static function query_one_object($sql, $args=NULL) {
    $sth = Dal::query($sql, $args);
    $ret = Dal::row_object($sth);
    return $ret;
  }

  public static function insert_id() {
    list($id) = Dal::query_one("SELECT LAST_INSERT_ID()");
    return $id;
  }

  public static function next_id($key) {
    $db = Dal::get_connection();
    return $db->nextId($key);
  }

  /* translate_table_name() takes a string like 'contents' and
     translates it into the appropriate 'database.tablename' for the
     current network.

     If you want to override the network name (db_update.php does
     this), specify the new name in the $net_name argument.  Otherwise
     just leave it as NULL and the $network_prefix global will be
     used.

     e.g. 'contents' becomes 'peopleaggregator.foo_contents' if your
     database is 'peopleaggregator' and you are using the 'foo'
     network.

     By default, the mappings in db/table_mappings.php are used.  If
     you want to override a particular table, you can do this creating
     a local_config.php file inside the appropriate network directory,
     and by putting that table in $user_table_mappings like this:

     $user_table_mappings = array(
       'contents' => '/%db%/./%network_name%/_some_new_table_name',
     );

     /%db%/ and /%network_name%/ will expand out to the default
     database name and current network name.  They aren't required.

  */
  public static function translate_table_name($table, $net_name=NULL) {
    global $network_prefix, $db_table_mappings, $user_table_mappings;
    if (!$db_table_mappings) die("Internal error - \$db_table_mappings is not available");

    $table = trim($table);

    if (!$net_name) {
      $net_name = $network_prefix;
    }

    if (!$net_name || $net_name == 'default') {
      $net_name_ = "";
    } else {
      $net_name_ = $net_name."_";
    }

    // look for a user-supplied pattern first, then fall back to defaults.
    $pattern = @$user_table_mappings[$table];
    if (!$pattern) $pattern = @$db_table_mappings[$table];

    // still no pattern?  then don't modify the table name.
    if (!$pattern) {
      $new_table = $table;
    } else {
      // expand /%...%/ strings in pattern to generate the table name.
      $new_table = str_replace("/%db%/", CURRENT_DB,
			       str_replace("/%network_name_%/", $net_name_, $pattern));
    }

    return $new_table;
  }

  public static function validate_sql ($sql, $net_name="") {
    return preg_replace("/\{\s*([a-z_]+)\s*\}/e", 'Dal::translate_table_name("$1", "$net_name")', $sql);
  }

  public static function commit() {
    Dal::get_connection()->commit();
  }

  public static function rollback() {
    Dal::get_connection()->rollback();
  }

  // quote a string for sending it to a db
  public static function quote($s) {
    return Dal::get_connection()->escapeSimple($s);
  }
  
  /**
  * This function will return the enum values available 
  * for a given table and field
  */
  public static function get_enum_values($table, $field) {
    $res = Dal::query('SHOW COLUMNS FROM '.$table);
    $enum_values = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      if ($row->Field == $field) {
        if(ereg(('enum'), $row->Type)) {
          eval(ereg_replace('enum', '$enum_values = array', $row->Type).';');
        }
      }
    }
    return $enum_values;
  }
}

class DbObject {

  // helper to populate a review object from a row
  function load_from_row($row) {
    foreach ($row as $k => $v) {
      $this->$k = $v;
    }
  }

  // helper to turn a query + args into an array of objects
  public static function load_many_from_query($cls, $sql, $args=NULL) {
    $sth = Dal::query($sql, $args);
    $items = array();
    while ($r = Dal::row_assoc($sth)) {
      $obj = new $cls();
      $obj->load_from_row($r);
      $items[] = $obj;
    }
    return $items;
  }

}

?>