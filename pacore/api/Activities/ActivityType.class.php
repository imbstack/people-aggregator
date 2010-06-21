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

require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";

/**
*
* @author - Zoran Hron
* @desc -
* @version - 0.1.0
*
**/
class ActivityType {

    public $id;

    private $title;

    private $type;

    private $description;

    private $points;

    public function __construct($title = null, $description = null, $activity_type = null, $points = 0) {
        $this->title       = $title;
        $this->description = $description;
        $this->type        = $activity_type;
        $this->points      = $points;
    }

    public function save() {
        Logger::log("Enter: function ActivityType::save()");
        if((empty($this->title)) || (empty($this->points)) || (empty($this->type))) {
            Logger::log("Throwing exception in ActivityType::save(). Required parameter missing.", LOGGER_ERROR);
            throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required parameter missing");
        }
        $sql      = "INSERT INTO {activity_types} (id, title, description, type, points) "."VALUES ('".$this->id."', '".$this->title."', '".$this->description."', '".$this->type."', ".$this->points.") "."ON DUPLICATE KEY UPDATE title = '".$this->title."', description = '".$this->description."', type = '".$this->type."', points = ".$this->points;
        $res      = Dal::query($sql);
        $this->id = Dal::insert_id();
        Logger::log("Exit: function ActivityType::save()");
    }

    /**
    * Get all activity types records
    *
    */
    public static function get_activity_types() {
        Logger::log("Enter: ActivityType::get_activity_types()");
        $parameter = array();
        $res = Dal::query("SELECT * FROM {activity_types}");
        if($res->numRows()) {
            $i = 0;
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $parameter[$i]["id"]          = $row->id;
                $parameter[$i]["title"]       = $row->title;
                $parameter[$i]["description"] = $row->description;
                $parameter[$i]["type"]        = $row->type;
                $parameter[$i]["points"]      = $row->points;
                $i++;
            }
            Logger::log("Exit : ActivityType::get_activity_types() | Returning array");
        }
        return $parameter;
    }

    /**
    * Get all activity types array
    *
    */
    public static function get_activities_array() {
        Logger::log("Enter: ActivityType::get_activities_array()");
        $parameter = array();
        $res = Dal::query("SELECT type FROM {activity_types}");
        if($res->numRows()) {
            $i = 0;
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $parameter[$i] = $row->type;
                $i++;
            }
            Logger::log("Exit : ActivityType::get_activities_array() | Returning array");
        }
        return $parameter;
    }

    public static function get_points_for_activity($activity_type) {
        $sql = "SELECT points FROM {activity_types} WHERE type = '$activity_type' ";
        $res = Dal::query($sql);
        if($res->numRows()) {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            return $row->points;
        }
        return false;
    }

    public function delete($id = null) {
        if(!$id) {
            $id = $this->id;
        }
        Logger::log("Enter: function ActivityType::delete");
        $sql = "DELETE FROM {activity_types} WHERE id = ?";
        $data = array(
            $id,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: function ActivityType::delete");
    }
}
// end class ActivityTypes
?>