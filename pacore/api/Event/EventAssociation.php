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
require_once dirname(__FILE__).'/../../config.inc';

require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/User/User.php";
require_once "api/Event/Event.php";

/*
* The EventAssociation class
* This is the workhorse of the Event architecture.  An EventAssociation maps single Events
* to any number of Users/Groups/Networks (Calendars). There can be multiple Associations
* for any Event (1-n).
* Author: Martin
*/

class EventAssociation {
  
  public $assoc_id;
  // (int) The unique ID for this EventAssociation
  public $event = NULL;
  
  // some read-only 
  public $event_id;
  // (int) the reference to the ID of this Event 
  public $user_id;
  // (int) the User ID for the 'owner' of this EventAssociation
  public $assoc_target_type;
  // (network|group|user) - what level of assosciation this row describes
  public $assoc_target_id;
  // (int) - depending on assoc_target_type this is the ID of the Network, Group or User
  public $assoc_target_name;
  // (string) - for easy retrieval this is either the name of the Network, Group or User 
  public $event_title;
  // the title given in the Event class instance
  public $start_time;
  // (date) as in the Event class instance
  public $end_time;
  // (date) as in the Event class instance

  public function __construct() {
    Logger::log("Enter: EventAssociation::__construct");
    Logger::log("Exit: EventAssociation::__construct");
  }
  
  /* scrap an EventAssociation, no questions asked */
  public static function delete($assoc_id) {
    Logger::log("Enter: EventAssociation::delete");
    $sql = "DELETE FROM {events_associations} WHERE assoc_id = ?";
    $data = array($assoc_id);
    try {
      Dal::query($sql, $data);
    } catch (PAException $e) {
      throw $e;
    }
    Logger::log("Exit: EventAssociation::delete");
  }

  /* scrap all EventAssociations for an Event, no questions asked */
  public static function delete_for_event($event_id) {
    Logger::log("Enter: EventAssociation::delete_for_event");
    $sql = "DELETE FROM {events_associations} WHERE event_id = ?";
    $data = array($event_id);
    try {
      Dal::query($sql, $data);
    } catch (PAException $e) {
      throw $e;
    }
    Logger::log("Exit: EventAssociation::delete_for_event");
  }
  
  /*
  * Update all existing EventAssociations for an Event
  */
  /*
  * This should be called each time an Event is updated to keep
  * all associated EventAssociation in sync
  * pass in the class Event instance
  */
  public static function update_assocs_for_event($event) {
    Logger::log("Enter: EventAssociation::update_assocs_for_event");
    $sql = "UPDATE {events_associations} SET "
        . "event_title = ?, start_time = ?, end_time = ? "
        . "WHERE event_id = ?";
      $data = array(
        $event->event_title,
        $event->start_time,
        $event->end_time,
        $event->event_id);
    try {
      Dal::query($sql, $data);
    } catch (PAException $e) {
      throw $e;
    }
    Logger::log("Exit: EventAssociation::update_assocs_for_event");
  }


  /*
  * check if EventAssociations for a given Target and Event exists
  * a Target is a unique combination of assoc_target_type and assoc_target_id
  * returns true or false
  */
  public static function assoc_exists($target_type, $target_id, $event_id) {
    Logger::log("Enter: EventAssociation::assoc_exists");
    $sql = "SELECT * FROM {events_associations} 
      WHERE assoc_target_type = ? AND assoc_target_id = ?" 
      . " AND event_id = ?";
    $data = array($target_type, $target_id, $event_id);
    $res = Dal::query($sql, $data);
    if ( $res->numRows() ) {
      Logger::log("Exit: EventAssociation::assoc_exists");    
      return true;
    }
    return false;
  }

  /*
  * find all EventAssociations for a given Target
  * a Target is a unique combination of assoc_target_type and assoc_target_id
  * returns an Array of assoc_ids
  */
  public static function find_for_target_and_delta($target_type, $target_id, $range_start = NULL, $range_end = NULL) {
    Logger::log("Enter: EventAssociation::find_for_target");
    $assoc_ids = array();
    $sql = "SELECT assoc_id FROM {events_associations} 
      WHERE assoc_target_type = ? AND assoc_target_id = ?";
      if ($range_start && $range_end) {
        // find events that either start or end between the given datetimes
        $sql .= "
        AND (start_time BETWEEN CAST(? AS DATETIME) AND CAST(? AS DATETIME)
        OR end_time BETWEEN CAST(? AS DATETIME) AND CAST(? AS DATETIME)
        OR (start_time <= CAST(? AS DATETIME) AND end_time >= CAST(? AS DATETIME)))";
        
      $data = array($target_type, $target_id, $range_start, $range_end, $range_start, $range_end, $range_start, $range_end);
    } else {
      $data = array($target_type, $target_id);
    }
    $sql .= " ORDER BY start_time ASC";
    $res = Dal::query($sql, $data);
    while ( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
      $assoc_ids[] = $row->assoc_id;
    }
    Logger::log("Exit: EventAssociation::find_for_target");    
    return $assoc_ids;
  }

  public static function find_for_event($event_id) {
    Logger::log("Enter: EventAssociation::find_for_event");
    $assoc_ids = array();
    $sql = "SELECT assoc_id FROM {events_associations} 
      WHERE event_id = ?";
    $data = array($event_id);
    $res = Dal::query($sql, $data);
    while ( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
      $assoc_ids[] = $row->assoc_id;
    }
    Logger::log("Exit: EventAssociation::find_for_event");
    return $assoc_ids;
  }
  
  /*
  * actually load and return a list of EventAssociation objects
  * pass in an array of assoc_ids as obtained 
  * by EventAssociation::find_for_target
  */
  public static function load_in_list($assoc_list) {
    Logger::log("Enter: EventAssociation::load_in_list");
    $assocs = array();
    for ($i=0;$i<count($assoc_list);$i++) {
      $a = new EventAssociation();
      $a->load($assoc_list[$i]);
      $assocs[$i] = $a;
    }
    Logger::log("Exit: EventAssociation::load_in_list");
    return $assocs;
  }

  
  /* update or create an EventAssociation
  */
  public function save() {
    Logger::log("Enter: EventAssociation::save");
    // check for complete info
    if (empty($this->event_id)) {
        Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: event_id is empty", LOGGER_ERROR);
        throw new PAException(REQUIRED_PARAMETERS_MISSING, 'event_id is missing.');
    }

    if (empty($this->assoc_target_type)) {
        Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: assoc_target_type is empty", LOGGER_ERROR);
        throw new PAException(REQUIRED_PARAMETERS_MISSING, 'assoc_target_type is missing.');
    }
    if (empty($this->assoc_target_id)) {
        Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: assoc_target_id is empty", LOGGER_ERROR);
        throw new PAException(REQUIRED_PARAMETERS_MISSING, 'assoc_target_id is missing.');
    }
    if (empty($this->assoc_target_name)) {
        Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: assoc_target_name is empty", LOGGER_ERROR);
        throw new PAException(REQUIRED_PARAMETERS_MISSING, 'assoc_target_name is missing.');
    }
    // depending on assoc_target_type check if network|group|user exists
    switch ($this->assoc_target_type) {
      case "network":
        // network of assoc_target_id exists?
        // this check should maybe be part of the Network class?
        $res = Dal::query("SELECT COUNT(*) FROM {networks} 
          WHERE network_id=? AND is_active=1", 
          array($this->assoc_target_id));
        if (!$res->numRows()) {
          Logger::log(" Throwing exception NETWORK_NOT_FOUND | Message: Network does not exist", LOGGER_ERROR);
          throw new PAException(NETWORK_NOT_FOUND , 'Network does not exist.');
        }
        break;
      case "group":
        // group of assoc_target_id exists?
        $res = Dal::query("SELECT COUNT(*) FROM {groups} 
          WHERE group_id=?", 
          array($this->assoc_target_id));
        if (!$res->numRows()) {
          Logger::log(" Throwing exception GROUP_NAME_NOT_EXIST | Message: Group does not exist", LOGGER_ERROR);
          throw new PAException(GROUP_NAME_NOT_EXIST , 'Group does not exist.');
        }
        break;
      case "user":
        // user of assoc_target_id exists?
        if (! User::user_exist($this->assoc_target_id)) {
          Logger::log(" Throwing exception USER_NOT_FOUND | Message: User does not exist", LOGGER_ERROR);
          throw new PAException(USER_NOT_FOUND , 'User does not exist.');
        }
        break;
      default: // oh-oh, not a valid assoc_target_type!!
        Logger::log(" Throwing exception BAD_PARAMETER | Message: " 
        . $this->assoc_target_type . " is not a valid assoc_target_type", LOGGER_ERROR);
        throw new PAException(BAD_PARAMETER, $this->assoc_target_type . " is not a valid assoc_target_type");
        break;        
    }
    
    // check to prevent duplicate associations 
    if (EventAssociation::assoc_exists($this->assoc_target_type, $this->assoc_target_id, $this->event_id)) {
        Logger::log(" Throwing exception BAD_PARAMETER | Message: " 
        . "There already is an EventAsssociation for this network, group or user.", LOGGER_ERROR);
        throw new PAException(BAD_PARAMETER, "The Event is already associated to this " 
        . $this->assoc_target_type . ".");
    }
    
    if (! Event::exists($this->event_id) ) {
      Logger::log(" Throwing exception EVENT_NOT_EXIST | Message: Event does not exist", LOGGER_ERROR);
      throw new PAException(EVENT_NOT_EXIST , 'Event does not exist.');
    }
    // load the Event if not already loaded
    if (! $this->event) {
      $this->load_event($this->event_id);
    }
    
    // serialize assoc_data for storage
    $assoc_data = "";
    if (! empty($this->assoc_data)) {
      $assoc_data = serialize($this->assoc_data);
    }
    
    // are we creating a new one?
    if (! $this->assoc_id) {
      // do we have a real User set as owner?
      if (! User::user_exist($this->user_id) ) {
        Logger::log(" Throwing exception USER_NOT_FOUND | Message: User does not exist", LOGGER_ERROR);
        throw new PAException(USER_NOT_FOUND , 'User does not exist.');
      }
      // do we have an Event?
      if (! Event::exists($this->event->event_id) ) {
        Logger::log(" Throwing exception EVENT_NOT_EXIST | Message: Event does not exist", LOGGER_ERROR);
        throw new PAException(EVENT_NOT_EXIST , 'Event does not exist.');
      }
      $sql = "INSERT INTO events_associations 
      (event_id, user_id, assoc_target_type, assoc_target_id, assoc_target_name, event_title, start_time, end_time, assoc_data) 
      VALUES (?,?,?,?,?,?,?,?,?)";
      $data = array(
        $this->event->event_id, 
        $this->user_id, 
        $this->assoc_target_type, 
        $this->assoc_target_id, 
        $this->assoc_target_name, 
        $this->event->event_title, 
        $this->event->start_time, 
        $this->event->end_time, 
        $assoc_data
        );
    } else {
      $sql = "UPDATE {events_associations} SET "
        . "event_id = ?, user_id = ?, assoc_target_type = ?, assoc_target_id = ?,
           assoc_target_name = ?, event_title = ?, start_time = ?, end_time = ?,
           assoc_data = ?"
        . "WHERE assoc_id = ?";
      $data = array(
        $this->event->event_id, 
        $this->user_id, 
        $this->assoc_target_type, 
        $this->assoc_target_id, 
        $this->assoc_target_name, 
        $this->event->event_title, 
        $this->event->start_time, 
        $this->event->end_time, 
        $assoc_data,
        $this->assoc_id);
    }
    // write to DB
    try {
      Dal::query($sql, $data);
      if (! $this->assoc_id) {
        $this->assoc_id = Dal::insert_id();
      }
      // Finally - commit our changes to the DB
      Dal::commit();
    } catch (PAException $e) {
      // roll back database operations and re-throw the exception
      Dal::rollback();
      throw $e;
    }

    Logger::log("Exit: EventAssociation::save");
  }
  
  /*
  * load the actual Event object
  */
  public function load_event() {
    Logger::log("Enter: EventAssociation::load_event");
    // check that it's a real live Event
    if (! Event::exists($this->event_id) ) {
      Logger::log(" Throwing exception EVENT_NOT_EXIST | Message: Event does not exist", LOGGER_ERROR);
      throw new PAException(EVENT_NOT_EXIST , 'Event does not exist.');
    }
    $this->event = new Event();
    $this->event->load_by_event_id($this->event_id);
    Logger::log("Exit: EventAssociation::load_event");
  }
  
  public function load($assoc_id) {
    Logger::log("Enter: EventAssociation::load");
    $sql = "SELECT * FROM {events_associations} WHERE assoc_id = ? LIMIT 1";
    $data = array($assoc_id);
    $res = Dal::query($sql, $data);
    if (!$res->numRows()) {
      Logger::log(" Throwing exception EVENT_NOT_EXIST | Message: EventAssociation with assoc_id($assoc_id) does not exist.", LOGGER_ERROR);
      throw new PAException(EVENT_NOT_EXIST, "EventAssociation with assoc_id($assoc_id) does not exist.");
    }
    if ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $this->assoc_id = $row->assoc_id;
      $this->event_id = $row->event_id;
      $this->user_id = $row->user_id;
      $this->assoc_target_type = $row->assoc_target_type; 
      $this->assoc_target_id = $row->assoc_target_id;
      $this->assoc_target_name = $row->assoc_target_name;
      $this->event_title = $row->event_title;
      $this->start_time = $row->start_time;
      $this->end_time = $row->end_time;
      $this->assoc_data = unserialize($row->assoc_data);
    }
    Logger::log("Exit: EventAssociation::load");    
  }
  

}
?>