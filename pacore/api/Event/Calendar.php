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
require_once "api/Logger/Logger.php";
require_once "api/User/User.php";
require_once "api/Event/Event.php";
require_once "api/Event/EventAssociation.php";

/*
* The Calendar class
* This is class acts as a convenience to query and manage EventAssociations
* actions like retrieving Events for one month, date conversion, etc
* Author: Martin
*/
class Calendar {
    // the start datetime of this Calendar
    public $range_start;
    // the end datetime of this Calendar
    public $range_end;
    // the target of EventAssociations to include
    public $target_type;

    public $target_id;
    // the 'current timestamp' is what the calendar will be treating as 'today'
    // by explictly setting it, we can have the Calendar behave relative to this date
    public $current_ts;

    public function __construct() {
        Logger::log("Enter: Calendar::__construct");
        Logger::log("Exit: Calendar::__construct");
    }
    // return a date range around a given date
    // possible range types are day|month|year for now
    public static function range($date = NULL, $range_type = 'month') {
        if(!$date) {
            $ts = time();
        }
        elseif(is_int($date)) {
            // it either is int or can be cast to one
            $ts = (int) $date;
        }
        else {
            $ts = strtotime($date);
        }
        $range = array(
            'start_ts' => 0,
            'end_ts'   => 0,
            'date_ts'  => $ts,
        );
        // set proper values
        switch($range_type) {
            case "day":
                // set start and end range to $today and $tomorrow
                $range['start_ts'] = mktime(0, 0, 0, date("m", $ts), date("d", $ts), date("Y", $ts));
                $range['end_ts'] = mktime(0, 0, 0, date("m", $ts), date("d", $ts)+1, date("Y", $ts));
                break;
            case "month":
                $range['start_ts'] = mktime(0, 0, 0, date("m", $ts), 1, date("Y", $ts));
                $range['end_ts'] = mktime(0, 0, 0, date("m", $ts)+1, 1, date("Y", $ts));
                break;
            case "year":
                $range['start_ts'] = mktime(0, 0, 0, 1, 1, date("Y", $ts));
                $range['end_ts'] = mktime(0, 0, 0, 1, 1, date("Y", $ts)+1);
                break;
            default:
                Logger::log(" Throwing exception BAD_PARAMETER | Message: $range_type is not a valid range_type", LOGGER_ERROR);
                throw new PAException(BAD_PARAMETER, $range_type." is not a valid range_type");
                break;
        }
        // add human readable dates
        $range['start'] = Calendar::date_string($range['start_ts']);
        $range['end']   = Calendar::date_string($range['end_ts']);
        $range['date']  = Calendar::date_string($range['date_ts']);
        return $range;
    }
    // build an array of days for a given Calendar::range
    // and sort in the passed event_assocs
    public static function days($range, $event_assocs = NULL) {
        $start               = $range['start_ts'];
        $end                 = $range['end_ts'];
        $days                = array();
        $event_assocs_by_day = NULL;
        if($event_assocs) {
            $event_assocs_by_day = Calendar::sort_assocs_by_day($event_assocs, $range);
        }
        $ts = $start;
        while($ts < $end) {
            $events = array();
            if($event_assocs_by_day) {
                $events = @$event_assocs_by_day[$ts];
            }
            $days[] = array(
                'date_ts' => $ts,
                'date'    => Calendar::date_string($ts),
                'events'  => $events,
            );
            $ts = mktime(0, 0, 0, date("m", $ts), date("d", $ts)+1, date("Y", $ts));
        }
        return $days;
    }
    // takes an array of EventAssociations and
    // builds and array with entries for each day that has EventAssoc(s)
    public static function sort_assocs_by_day($event_assocs, $range = null) {
        $by_day = array();
        $oneday = 60*60*24;
        foreach($event_assocs as $i => $assoc) {
            $s = strtotime($assoc->start_time);
            $e = strtotime($assoc->end_time);
            // deal with multi month stuff
            if(!empty($range['start_ts'])) {
                $s = ($s < (int) $range['start_ts']) ? $range['start_ts'] : $s;
            }
            if(!empty($range['end_ts'])) {
                $e = ($e > (int) $range['end_ts']) ? $range['end_ts'] : $e;
            }

            /*
            print_r($range);
            echo "<hr/>".date("Y-m-d",$s)."<hr/>";
            echo "<hr/>".date("Y-m-d",$e)."<hr/>";
            exit;
            */
            // day grid, convert to start of day
            $sd = strtotime(date("Y-m-d", $s));
            for($ts = $sd; $ts <= $e; $ts += $oneday) {
                $a = clone $assoc;
                if($ts > $sd) {
                    $a->following_day = 1;
                }
                $by_day[$ts][] = $a;
            }
        }
        ksort($by_day);
        return $by_day;
    }
    // convert a timestanp
    public static function date_string($ts) {
        return date(DATE_ATOM, $ts);
    }
}
?>