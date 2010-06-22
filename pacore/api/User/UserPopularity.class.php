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
require_once "web/includes/classes/MemoryPagging.class.php";

class UserPopularity {

    /**
     * Name: user_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $user_id;

    /**
     * Name: popularity
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $popularity;

    /**
     * Name: time
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $time;

    /**
     * Name: page_size
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $page_size = 20;

    /**
     * Name: current_page
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $current_page;

    /**
     * Name: conditional_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $conditional_steatment;

    /**
     * Name: order_by_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $order_by_steatment;

    /**
     * Name: sort_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $sort_steatment;

    /**
     * Name: fetch_mode
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $fetch_mode = DB_FETCHMODE_OBJECT;
    //--------------- GET METHODS ----------------------------- //
    /**
     * Get value for field: user_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result user_id
     **/
    public function get_user_id() {
        // returns the value of user_id
        return $this->user_id;
    }

    /**
     * Get value for field: popularity
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result popularity
     **/
    public function get_popularity() {
        // returns the value of popularity
        return $this->popularity;
    }

    /**
     * Get value for field: time
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result time
     **/
    public function get_time() {
        // returns the value of time
        return $this->time;
    }

    /**
     * Get value for field: page_size
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result page_size
     **/
    public function get_page_size() {
        // returns the value of page_size
        return $this->page_size;
    }

    /**
     * Get value for field: current_page
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result current_page
     **/
    public function get_current_page() {
        // returns the value of current_page
        return $this->current_page;
    }

    /**
     * Get value for field: conditional_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result conditional_steatment
     **/
    public function get_conditional_steatment() {
        // returns the value of conditional_steatment
        return $this->conditional_steatment;
    }

    /**
     * Get value for field: order_by_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result order_by_steatment
     **/
    public function get_order_by_steatment() {
        // returns the value of order_by_steatment
        return $this->order_by_steatment;
    }

    /**
     * Get value for field: sort_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result sort_steatment
     **/
    public function get_sort_steatment() {
        // returns the value of sort_steatment
        return $this->sort_steatment;
    }

    /**
     * Get value for field: fetch_mode
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result fetch_mode
     **/
    public function get_fetch_mode() {
        // returns the value of fetch_mode
        return $this->fetch_mode;
    }
    //--------------- SET METHODS ----------------------------- //
    /**
     * Set value for field: user_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public function set_user_id($user_id) {
        // sets the value of user_id
        $this->user_id = $user_id;
    }

    /**
     * Set value for field: popularity
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param popularity
     * @result void
     **/
    public function set_popularity($popularity) {
        // sets the value of popularity
        $this->popularity = $popularity;
    }

    /**
     * Set value for field: time
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param time
     * @result void
     **/
    public function set_time($time) {
        // sets the value of time
        $this->time = $time;
    }

    /**
     * Set value for field: page_size
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param page_size
     * @result void
     **/
    public function set_page_size($page_size) {
        // sets the value of page_size
        $this->page_size = $page_size;
    }

    /**
     * Set value for field: current_page
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param current_page
     * @result void
     **/
    public function set_current_page($current_page) {
        // sets the value of current_page
        $this->current_page = $current_page;
    }

    /**
     * Set value for field: conditional_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditional_steatment
     * @result void
     **/
    public function set_conditional_steatment($conditional_steatment) {
        // sets the value of conditional_steatment
        $this->conditional_steatment = $conditional_steatment;
    }

    /**
     * Set value for field: order_by_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param order_by_steatment
     * @result void
     **/
    public function set_order_by_steatment($order_by_steatment) {
        // sets the value of order_by_steatment
        $this->order_by_steatment = $order_by_steatment;
    }

    /**
     * Set value for field: sort_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param sort_steatment
     * @result void
     **/
    public function set_sort_steatment($sort_steatment) {
        // sets the value of sort_steatment
        $this->sort_steatment = $sort_steatment;
    }

    /**
     * Set value for field: fetch_mode
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param fetch_mode
     * @result void
     **/
    public function set_fetch_mode($fetch_mode) {
        // sets the value of fetch_mode
        $this->fetch_mode = $fetch_mode;
    }
    //--------------- CRUD METHODS ----------------------------- //
    /**
     * Class Constructor for: UserPopularity
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param orderby = null
     * @param sort = null
     * @param pagesize = 0
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result void
     **/
    public function __construct($conditionalStatement = null, $orderby = null, $sort = null, $pagesize = 0, $fetchmode = DB_FETCHMODE_OBJECT) {
        // set defaults
        if($conditionalStatement) {
            $this->conditional_steatment = $conditionalStatement;
        }
        if($orderby) {
            $this->order_by_steatment = $orderby;
        }
        if($sort) {
            $this->sort_steatment = $sort;
        }
        if($pagesize) {
            $this->page_size = $pagesize;
        }
        if($fetchmode) {
            $this->fetch_mode = $fetchmode;
        }
    }

    /**
     * Load object from database - dynamic method: load_UserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public function load_UserPopularity($user_id) {
        // use get method to load object data
        $this->get_UserPopularity($user_id);
    }

    /**
     * Save object to the database - dynamic method: save_UserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result void
     **/
    public function save_UserPopularity() {
        // determine is this a new object
        if(!empty($this->user_id)) {
            $itemsToUpdate = array(
                'popularity' => $this->popularity,
                'time' => $this->time,
            );
            $this->update_UserPopularity($this->user_id, $itemsToUpdate);
        }
        else {
            $this->insert_UserPopularity($this->popularity, $this->time);
        }
    }

    /**
     * Delete an existing record - dynamic method: delete_UserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public function delete_UserPopularity($user_id) {
        // sql query
        $sql = "DELETE FROM { user_popularity } WHERE user_id = ?;";
        $params = array(
            $user_id,
        );
        // performs deletion of data
        $res = Dal::query($sql, $params);
    }

    /**
     * Delete an existing record - static method: deleteUserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public static function deleteUserPopularity($user_id) {
        // object self instance
        $instance = new self();
        // call dynamic method
        $instance->delete_UserPopularity($user_id);
    }

    /**
     * Insert a new Record - dynamic method: insert_UserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param popularity
     * @param time
     * @result id
     **/
    public function insert_UserPopularity($popularity, $time) {
        // items to be inserted in the database
        $params = array(
            null,
            $popularity,
            $time,
        );
        $__id = null;
        // insert query
        $sql = "INSERT INTO { user_popularity } ( user_id, popularity, time ) VALUES ( ?,?,? );";
        // perform insert in the database
        $res = Dal::query($sql, $params);
        if($res) {
            $__id = Dal::insert_id();
        }
        return $__id;
    }

    /**
     * Insert a new Record - static method: insertUserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param params = array()
     * @result id
     **/
    public static function insertUserPopularity($params = array()) {
        // object self instance
        $instance = new self();
        // required fields names
        $db_fields = array(
            "popularity",
            "time",
        );
        // build argument list
        foreach($db_fields as $param_name) {
            if(!array_key_exists($param_name, $params)) {
                throw new Exception("UserPopularity::insertUserPopularity() - Missing parameter $param_name.");
            }
            $$param_name = $params[$param_name];
        }
        // call dynamic method
        return $instance->insert_UserPopularity($popularity, $time);
    }

    /**
     * Retrieve an existing record - dynamic method: get_UserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: UserPopularity
     **/
    public function get_UserPopularity($user_id, $fetchmode = DB_FETCHMODE_OBJECT) {
        // MySQL query
        $sql = "SELECT * FROM { user_popularity } WHERE user_id = ?;";
        // record ID
        $params = array(
            $user_id,
        );
        // execute query
        $res = Dal::query($sql, $params);
        $row = array();
        // data found?
        if($res->numRows() > 0) {
            // retrieve data object
            $row = $res->fetchRow($fetchmode);
            // populate this object
            if($fetchmode == DB_FETCHMODE_OBJECT) {
                $this->populateFromObject($row);
                return $this;
            }
            else {
                $this->populateFromArray($row);
                return $row;
            }
        }
        return null;
    }

    /**
     * Retrieve an existing record - static method: getUserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: UserPopularity
     **/
    public static function getUserPopularity($user_id, $fetchmode = DB_FETCHMODE_OBJECT) {
        // object self instance
        $instance = new self();
        // call dynamic method
        return $instance->get_UserPopularity($user_id, $fetchmode);
    }

    /**
     * Update an existing record - dynamic method: update_UserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @param itemsToBeUpdated = array()
     * @result bool
     **/
    public function update_UserPopularity($user_id, $itemsToBeUpdated = array()) {
        // sql query
        $sql = "UPDATE { user_popularity } SET ";
        // where steatment
        $__where = " WHERE user_id = ?;";
        // array of values
        $params = array();
        // build update paremeters
        foreach($itemsToBeUpdated as $field_name => $field_value) {
            $sql .= "$field_name = ?, ";
            $params[] = $field_value;
        }
        $sql      = rtrim($sql, " ,");
        $sql     .= $__where;
        $params[] = $user_id;
        // perform update operation
        $res = Dal::query($sql, $params);
        if($res) {
            $this->populateFromArray($itemsToBeUpdated);
            return true;
        }
        return false;
    }

    /**
     * Update an existing record - static method: updateUserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @param itemsToBeUpdated = array()
     * @result void
     **/
    public static function updateUserPopularity($user_id, $itemsToBeUpdated = array()) {
        // object self instance
        $instance = new self();
        // call dynamic method
        return $instance->update_UserPopularity($user_id, $itemsToBeUpdated);
    }

    /**
     * Retrieved list of objects base on a given parameters - dynamic method: list_UserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param orderby = null
     * @param sort = null
     * @param limit = 0
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result array of objects: UserPopularity
     **/
    public function list_UserPopularity($conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT) {
        // build MySQL query
        $sql = "SELECT * FROM { user_popularity } ";
        if($conditionalStatement) {
            $sql .= "WHERE $conditionalStatement";
        }
        if($orderby) {
            $sql .= " ORDER BY $orderby";
        }
        if($sort) {
            $sql .= " $sort";
        }
        if($limit) {
            $sql .= " LIMIT $limit";
        }
        $sql .= ";";
        // execute query
        $res = Dal::query($sql);
        $objects = array();
        // data found?
        if($res->numRows() > 0) {
            // retrieve data objects
            while($row = $res->fetchRow($fetchmode)) {
                if($fetchmode == DB_FETCHMODE_OBJECT) {
                    $obj = new UserPopularity();
                    $obj->populateFromObject($row);
                    $objects[] = $obj;
                }
                else {
                    $objects[] = $row;
                }
            }
        }
        return $objects;
    }

    /**
     * Retrieved list of objects base on a given parameters - static method: listUserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param orderby = null
     * @param sort = null
     * @param limit = 0
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result array of objects: UserPopularity
     **/
    public static function listUserPopularity($conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT) {
        // object self instance
        $instance = new self();
        // call dynamic method
        return $instance->list_UserPopularity($conditionalStatement, $orderby, $sort, $limit, $fetchmode);
    }

    /**
     * Count records based on a given params - dynamic method: count_UserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param selectFields = array()
     * @param groupByFields = array()
     * @result int or array of counted objects
     **/
    public function count_UserPopularity($conditionalStatement = null, $selectFields = array(), $groupByFields = array()) {
        // build MySQL query
        $sql = "SELECT ";
        if(count($selectFields) > 0) {
            $sql .= implode(", ", $selectFields).", COUNT(*) AS counter ";
        }
        else {
            $sql .= "COUNT(*) AS counter ";
        }
        $sql .= "FROM { user_popularity } ";
        if($conditionalStatement) {
            $sql .= "WHERE $conditionalStatement ";
        }
        if(count($groupByFields) > 0) {
            $sql .= "GROUP BY ".implode(", ", $groupByFields);
        }
        $sql .= ";";
        // execute query
        $res = Dal::query($sql);
        $objects = array();
        // data found?
        if($res->numRows() > 1) {
            // retrieve data objects
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $objects[] = $row;
            }
            return $objects;
        }
        elseif($res->numRows() == 1) {
            $data = $res->fetchRow(DB_FETCHMODE_OBJECT);
            return $data->counter;
        }
        else {
            return 0;
        }
    }

    /**
     * Count records based on a given params - static method: countUserPopularity()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param selectFields = array()
     * @param groupByFields = array()
     * @result int or array of counted objects
     **/
    public static function countUserPopularity($conditionalStatement = null, $selectFields = array(), $groupByFields = array()) {
        // object self instance
        $instance = new self();
        // call dynamic method
        return $instance->count_UserPopularity($conditionalStatement, $selectFields, $groupByFields);
    }

    /**
     * Get a page - dynamic method: getPage()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param page = 0
     * @result array of objects
     **/
    public function getPage($page = 0) {
        // calculate limit expression
        $l_start   = $this->page_size*$page;
        $l_end     = $this->page_size;
        $limit_str = "$l_start,$l_end";
        // performs deletion of data
        return $this->list_UserPopularity($this->conditional_steatment, $this->order_by_steatment, $this->sort_steatment, $limit_str);
    }

    /**
     * Populate object from array - dynamic method: populateFromArray()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param params = array()
     * @result void
     **/
    public function populateFromArray($params = array()) {
        // required fields names
        $db_fields = array(
            "user_id",
            "popularity",
            "time",
        );
        // build argument list
        foreach($db_fields as $param_name) {
            if(array_key_exists($param_name, $params)) {
                $this-> {
                    $param_name
                } = $params[$param_name];
            }
        }
    }

    /**
     * Populate object from another object - dynamic method: populateFromObject()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param source = null
     * @result void
     **/
    public function populateFromObject($source = null) {
        // required fields names
        $db_fields = array(
            "user_id",
            "popularity",
            "time",
        );
        // build argument list
        foreach($db_fields as $param_name) {
            if(isset($source->$param_name)) {
                $this-> {
                    $param_name
                } = $source-> {
                    $param_name
                };
            }
        }
    }

    public static function getPagging($items, $page_size, $current_page) {
        if(!is_null($current_page)) {
            $pagination = new MemoryPagging($items, $page_size, $current_page);
            return $pagination;
        }
        return null;
    }
}
?>