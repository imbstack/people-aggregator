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
require_once "api/Cache/Cache.php";
require_once "api/Network/Network.php";
require_once "api/Logger/Logger.php";
require_once "api/api_constants.php";

/**
* Class Tasks represents different tasks that user can take
* Purpose - this class is used to get the tasks in the system
* @package Tasks
* @author Tekriti Software
*/
//this is singleton class used for many things
Class Tasks {

    /**
    * The array of all the system tasks
    * @var integer
    */
    public $tasks;

    private static $instance;

    /**
    * The default constructor for Roles class.
    * the constructor is private so that it is instantiated only once
    */
    private function __construct() {
        Logger::log("Enter: function Tasks::__construct");
        Logger::log("Exit: function Tasks::__construct");
    }

    public static function get_instance() {
        if(!isset(self::$instance)) {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    public function get_tasks($fetch_mode = DB_FETCHMODE_OBJECT) {
        Logger::log("Enter: function Tasks::get_tasks");
        //get tasks from db but its ok to define it in array for now
        // this should go in Cache
        $tasks = array();
        $res = Dal::query('SELECT * FROM {tasks} ');
        while($r = $res->fetchRow($fetch_mode)) {
            $tasks[] = $r;
        }
        Logger::log("Exit: function Tasks::get_tasks");
        return $tasks;
    }

    public static function get_id_from_task_value($task_value) {
        return Dal::query_first("SELECT id FROM {tasks} WHERE task_value=?", array($task_value));
    }
}
?>