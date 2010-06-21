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
require_once "api/Event/EventAssociation.php";
require_once "api/Content/Content.php";

/*
* The Event class
* this is the basic building block for any Event
* it stores any info that is specific to the Event
* Note: info about to which Claendar this Event 'belongs'
* are stored as EventAssociation(s) (see EventAssociation.php)
* Author: Martin
*/
class Event extends Content {

    public $event_id;
    // (int) the unique ID of this Event
    public $content_id;
    // a reference to the actual Event post
    // This can also be a reference to outside content (point-to URL)
    public $user_id;
    // (int) the ID of the User who created this Event
    public $event_title;
    // (text) the title for this Event
    public $start_time;
    // (date)
    public $end_time;
    // (date)
    public function __construct() {
        Logger::log("Enter: Event::__construct");
        parent::__construct();
        // Content
        $this->type = EVENT;
        $this->trackbacks = NULL;
        Logger::log("Exit: Event::__construct");
    }

    /* scrap an Event, no questions asked */
    public static function delete_by_id($event_id) {
        $e = new Event();
        $e->event_id = $event_id;
        $e->delete();
    }

    public function delete() {
        Logger::log("Enter: Event::delete");
        $event_id = $this->event_id;
        $content_id = $this->get_cid_from_eid($event_id);
        Content::delete_by_id($content_id);
        // remove all EventAssociations for this Event
        EventAssociation::delete_for_event($event_id);
        $sql = "DELETE FROM {events} WHERE event_id = ?";
        $data = array(
            $event_id,
        );
        try {
            Dal::query($sql, $data);
        }
        catch(PAException$e) {
            throw $e;
        }
        Logger::log("Exit: Event::delete");
    }

    /* update or create an Event
    *  If the Event instance has an event_id, we update
    *  Else we create a new one
    **  Note: only event_title, start/end_time and event_data 
    **  are editable! content_id, user_id are considered unchangeable
    **  to 'move' and event, delete and re-create it
    */
    public function save() {
        Logger::log("Enter: Event::save");
        // check for complete info
        if(empty($this->event_title)) {
            Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: event_title is empty", LOGGER_ERROR);
            throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Please supply an Event Title.');
        }
        $this->title = $this->event_title;
        if(empty($this->start_time)) {
            Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: start_time is empty", LOGGER_ERROR);
            throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Please specify the Start Time.');
        }
        if(empty($this->end_time)) {
            Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: end_time is empty", LOGGER_ERROR);
            throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Please specify the End Time.');
        }
        // serialize event_data for storage
        $event_data = "";
        if(!empty($this->event_data)) {
            $event_data = serialize($this->event_data);
        }
        // make end_time sane (can only be same or after start_time)
        if(strtotime($this->end_time) <= strtotime($this->start_time)) {
            $this->end_time = $this->start_time;
        }
        $this->author_id = $this->user_id;
        $this->body = $this->event_data['description'];
        // are we creating a new one?
        if(!$this->event_id) {

            /*
            if (empty($this->content_id)) {
                Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: content_id is empty", LOGGER_ERROR);
                throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Content id is missing.');
            }
            */
            // do we have a real User set as owner?
            if(!User::user_exist((int) $this->user_id)) {
                Logger::log(" Throwing exception USER_NOT_FOUND | Message: User does not exist", LOGGER_ERROR);
                throw new PAException(USER_NOT_FOUND, 'User does not exist.');
            }
            // save a Content
            parent::save();
            $sql = "INSERT INTO events 
      (content_id, user_id, event_title, start_time, end_time, event_data) 
      VALUES (?, ?, ?, ?, ?, ?)";
            $data = array(
                $this->content_id,
                $this->user_id,
                $this->event_title,
                $this->start_time,
                $this->end_time,
                $event_data,
            );
        }
        else {
            // save as Content
            parent::save();
            $sql = "UPDATE {events} SET "."event_title = ?, start_time = ?, end_time = ?, event_data = ? "."WHERE event_id = ?";
            $data = array(
                $this->event_title,
                $this->start_time,
                $this->end_time,
                $event_data,
                $this->event_id,
            );
        }
        // write to DB
        try {
            Dal::query($sql, $data);
            if(!$this->event_id) {
                // newly created
                $this->event_id = Dal::insert_id();
            }
            else {
                // update any existing EventAssociations
                EventAssociation::update_assocs_for_event($this);
            }
            // Finally - commit our changes to the DB
            Dal::commit();
        }
        catch(PAException$e) {
            // roll back database operations and re-throw the exception
            Dal::rollback();
            throw $e;
        }
        Logger::log("Exit: Event::save");
    }

    public static function exists($event_id) {
        $sql = "SELECT count(*) FROM {events} WHERE event_id = ? LIMIT 1";
        $data = array(
            $event_id,
        );
        $res = Dal::query($sql, $data);
        if(!$res->numRows()) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function owner($event_id) {
        $sql = "SELECT user_id FROM {events} WHERE event_id = ? LIMIT 1";
        $data = array(
            $event_id,
        );
        $res = Dal::query($sql, $data);
        if(!$res->numRows()) {
            return false;
        }
        else {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            return (int) $row->user_id;
        }
    }

    public static function get_eid_from_cid($cid) {
        $sql = "SELECT * FROM {events} WHERE content_id = ? LIMIT 1";
        $data = array(
            $cid,
        );
        $res = Dal::query($sql, $data);
        if(!$res->numRows()) {
            Logger::log(" Message: Event with content_id($cid) does not exist.", LOGGER_ERROR);
            return NULL;
        }
        if($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            return $row->event_id;
        }
    }

    public static function get_cid_from_eid($eid) {
        $sql = "SELECT * FROM {events} WHERE event_id = ? LIMIT 1";
        $data = array(
            $eid,
        );
        $res = Dal::query($sql, $data);
        if(!$res->numRows()) {
            Logger::log(" Message: Event with content_id($cid) does not exist.", LOGGER_ERROR);
            return NULL;
        }
        if($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            return $row->content_id;
        }
    }

    public function load($cid) {
        parent::load($cid);
        // the content part
        $this->load_by_event_id(Event::get_eid_from_cid($cid));
        // the event part
    }

    public function load_by_event_id($event_id) {
        Logger::log("Enter: Event::load");
        $sql = "SELECT * FROM {events} WHERE event_id = ? LIMIT 1";
        $data = array(
            $event_id,
        );
        $res = Dal::query($sql, $data);
        if(!$res->numRows()) {
            Logger::log(" Message: Event with event_id($event_id) does not exist.", LOGGER_ERROR);
            Logger::log("Exit: Event::load");
            return NULL;
        }
        if($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $this->event_id    = $event_id;
            $this->content_id  = $row->content_id;
            $this->user_id     = (int) $row->user_id;
            $this->event_title = $row->event_title;
            $this->start_time  = $row->start_time;
            $this->end_time    = $row->end_time;
            $this->event_data  = unserialize($row->event_data);
        }
        Logger::log("Exit: Event::load");
    }
}
?>