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
require_once "api/Forum/PaForumBoard.class.php";
require_once "api/Forum/PaForumThread.class.php";
require_once "api/Forum/PaForumPost.class.php";

class PaForumsUsers {

    const _owner    = 1;
    const _admin    = 2;
    const _allowed  = 4;
    const _waiting  = 8;
    const _limited  = 16;
    const _banned   = 32;
    const _anonymous = 64;
    const _notmember = 128;

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
     * Name: board_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $board_id;

    /**
     * Name: user_status
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $user_status;

    /**
     * Name: is_active
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $is_active;

    /**
     * Name: date_join
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $date_join;

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
    public  function get_user_id( ) {
        // returns the value of user_id
        return $this->user_id;
    }


    /**
     * Get value for field: board_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result board_id
     **/
    public  function get_board_id( ) {
        // returns the value of board_id
        return $this->board_id;
    }


    /**
     * Get value for field: user_status
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result user_status
     **/
    public  function get_user_status( ) {
        // returns the value of user_status
        $this->user_status = $this->getUserStatus();
        return $this->user_status;
    }


    /**
     * Get value for field: is_active
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result is_active
     **/
    public  function get_is_active( ) {
        // returns the value of is_active
        return $this->is_active;
    }


    /**
     * Get value for field: date_join
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result date_join
     **/
    public  function get_date_join( ) {
        // returns the value of date_join
        return $this->date_join;
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
    public  function get_page_size( ) {
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
    public  function get_current_page( ) {
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
    public  function get_conditional_steatment( ) {
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
    public  function get_order_by_steatment( ) {
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
    public  function get_sort_steatment( ) {
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
    public  function get_fetch_mode( ) {
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
    public  function set_user_id( $user_id ) {
        // sets the value of user_id
        $this->user_id = $user_id;
    }

    /**
     * Set value for field: board_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param board_id
     * @result void
     **/
    public  function set_board_id( $board_id ) {
        // sets the value of board_id
        $this->board_id = $board_id;
    }

    /**
     * Set value for field: user_status
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_status
     * @result void
     **/
    public  function set_user_status( $user_status ) {
        // sets the value of user_status
        $this->user_status = $user_status;
    }

    /**
     * Set value for field: is_active
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param is_active
     * @result void
     **/
    public  function set_is_active( $is_active ) {
        // sets the value of is_active
        $this->is_active = $is_active;
    }

    /**
     * Set value for field: date_join
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param date_join
     * @result void
     **/
    public  function set_date_join( $date_join ) {
        // sets the value of date_join
        $this->date_join = $date_join;
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
    public  function set_page_size( $page_size ) {
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
    public  function set_current_page( $current_page ) {
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
    public  function set_conditional_steatment( $conditional_steatment ) {
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
    public  function set_order_by_steatment( $order_by_steatment ) {
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
    public  function set_sort_steatment( $sort_steatment ) {
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
    public  function set_fetch_mode( $fetch_mode ) {
        // sets the value of fetch_mode
        $this->fetch_mode = $fetch_mode;
    }

//--------------- CRUD METHODS ----------------------------- //
    /**
     * Class Constructor for: PaForumsUsers
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
    public  function __construct( $conditionalStatement = null, $orderby = null, $sort = null, $pagesize = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // set defaults
        if($conditionalStatement) { 
          $this->conditional_steatment = $conditionalStatement;
        }
        if($orderby) $this->order_by_steatment = $orderby;
        if($sort) $this->sort_steatment = $sort;
        if($pagesize) $this->page_size = $pagesize;
        if($fetchmode) $this->fetch_mode = $fetchmode;
        
    }

    /**
     * Load object from database - dynamic method: load_PaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public  function load_PaForumsUsers( $user_id ) {

        // use get method to load object data
        $this->get_PaForumsUsers($user_id);
        
    }

    /**
     * Save object to the database - dynamic method: save_PaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result void
     **/
    public  function save_PaForumsUsers( ) {

        // determine is this a new object
        if(!empty($this->user_id)) { 
          $itemsToUpdate = array('board_id' => $this->board_id,
                                 'user_status' => $this->user_status,
                                 'is_active' => $this->is_active,
                                 'date_join' => $this->date_join);
          $this->update_PaForumsUsers($this->user_id, $itemsToUpdate); 
        } else { 
          $this->insert_PaForumsUsers($this->board_id,
                                      $this->user_status,
                                      $this->is_active,
                                      $this->date_join); 
        } 
    }

    /**
     * Delete an existing record - dynamic method: delete_PaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public  function delete_PaForumsUsers( $user_id ) { 
         PaForumBoard::delete_UserBoards($user_id);
         PaForumThread::delete_ThreadsForUser($user_id);
         PaForumPost::delete_PostsForUser($user_id);

         // sql query
         $sql = "UPDATE { pa_forums_users } SET is_active = 0 WHERE user_id = ?;";
         $params = array($user_id);

         // performs deletion of data
         $res = Dal::query($sql, $params);
    }

    /**
     * Delete an existing record - static method: deletePaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public static function deletePaForumsUsers( $user_id ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        $instance->delete_PaForumsUsers($user_id);
        
    }

    /**
     * Insert a new Record - dynamic method: insert_PaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param board_id
     * @param user_status
     * @param is_active
     * @param date_join
     * @result id
     **/
    public  function insert_PaForumsUsers($user_id = null, $board_id, $user_status, $is_active, $date_join ) {

        // items to be inserted in the database 
        $params = array($user_id,
                      $board_id,
                      $user_status,
                      $is_active,
                      $date_join);
        $__id = null;

        // insert query
        $sql = "INSERT INTO { pa_forums_users } ( user_id, board_id, user_status, is_active, date_join ) VALUES ( ?,?,?,?,? );";

        // perform insert in the database
        $res = Dal::query($sql, $params);
        if($res) { 
          $__id = Dal::insert_id();
        }
        return $__id;

    }

    /**
     * Insert a new Record - static method: insertPaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param params = array()
     * @result id
     **/
    public static function insertPaForumsUsers( $params = array() ) {

        // object self instance 
        $instance = new self();

        // required fields names
        $db_fields = array("user_id",
                           "board_id",
                           "user_status",
                           "is_active",
                           "date_join");

        // build argument list 
        foreach($db_fields as $param_name) { 
          if(!array_key_exists($param_name, $params)) { 
            throw new Exception("PaForumsUsers::insertPaForumsUsers() - Missing parameter $param_name.");
          }
          $$param_name = $params[$param_name]; 
        } 
        // call dynamic method 
        return $instance->insert_PaForumsUsers($user_id,
                                               $board_id,
                                               $user_status,
                                               $is_active,
                                               $date_join);
        
    }

    /**
     * Retrieve an existing record - dynamic method: get_PaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumsUsers
     **/
    public  function get_PaForumsUsers( $user_id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // MySQL query
        $sql = "SELECT * FROM { pa_forums_users } WHERE user_id = ?;";

        // record ID
        $params = array($user_id);

        // execute query
        $res = Dal::query($sql, $params);

        $row = array();
        // data found? 
        if ($res->numRows() > 0) {
          // retrieve data object
          $row = $res->fetchRow($fetchmode);
          // populate this object
          if($fetchmode == DB_FETCHMODE_OBJECT) { 
            $this->populateFromObject($row);
            return $this;
          } else { 
            $this->populateFromArray($row);
            return $row;
          } 
        }

        return null;
    }

    /**
     * Retrieve an existing record - static method: getPaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumsUsers
     **/
    public static function getPaForumsUsers( $user_id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->get_PaForumsUsers($user_id, $fetchmode);
        
    }

    /**
     * Update an existing record - dynamic method: update_PaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @param itemsToBeUpdated = array()
     * @result bool
     **/
    public  function update_PaForumsUsers( $user_id, $itemsToBeUpdated = array() ) {

         // sql query
         $sql = "UPDATE { pa_forums_users } SET ";

         // where steatment
         $__where = " WHERE user_id = ?;";

         // array of values
         $params = array();

         // build update paremeters 
         foreach($itemsToBeUpdated as $field_name => $field_value) { 
              $sql .= "$field_name = ?, ";
              $params[] = $field_value;
         }
         $sql = rtrim($sql, " ,");
         $sql .= $__where;
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
     * Update an existing record - static method: updatePaForumsUsers()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @param itemsToBeUpdated = array()
     * @result void
     **/
    public static function updatePaForumsUsers( $user_id, $itemsToBeUpdated = array() ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->update_PaForumsUsers($user_id, $itemsToBeUpdated);
        
    }

    /**
     * Retrieved list of objects base on a given parameters - dynamic method: list_PaForumsUsers()
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
     * @result array of objects: PaForumsUsers
     **/
    public  function list_PaForumsUsers( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // build MySQL query
        $sql = "SELECT * FROM { pa_forums_users } ";

        if($conditionalStatement) $sql .= "WHERE $conditionalStatement";
        if($orderby) $sql .= " ORDER BY $orderby";
        if($sort) $sql .= " $sort";
        if($limit) $sql .= " LIMIT $limit";
        $sql .= ";";

        // execute query
        $res = Dal::query($sql);

        $objects = array(); 
        // data found? 
        if ($res->numRows() > 0) {
          // retrieve data objects
          while($row = $res->fetchRow($fetchmode)) {
            if($fetchmode == DB_FETCHMODE_OBJECT) { 
              $obj = new PaForumsUsers(); 
              $obj->populateFromObject($row);
              $objects[] = $obj; 
            } else { 
              $objects[] = $row; 
            } 
          }
        }

        return $objects;
    }

    /**
     * Retrieved list of objects base on a given parameters - static method: listPaForumsUsers()
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
     * @result array of objects: PaForumsUsers
     **/
    public static function listPaForumsUsers( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->list_PaForumsUsers($conditionalStatement, $orderby, $sort, $limit, $fetchmode);
        
    }

    /**
     * Count records based on a given params - dynamic method: count_PaForumsUsers()
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
    public  function count_PaForumsUsers( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // build MySQL query
        $sql = "SELECT ";
        if(count($selectFields) > 0) {
          $sql .= implode(", ", $selectFields) . ", COUNT(*) AS counter "; 
        } else { 
          $sql .= "COUNT(*) AS counter "; 
        }
        $sql .= "FROM { pa_forums_users } ";
        if($conditionalStatement) { 
          $sql .= "WHERE $conditionalStatement "; 
        }
        if(count($groupByFields) > 0) { 
          $sql .= "GROUP BY " . implode(", ", $groupByFields);
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
        } else if($res->numRows() == 1) { 
          $data = $res->fetchRow(DB_FETCHMODE_OBJECT); 
          return $data->counter; 
        } else { 
          return 0; 
        }
    }

    /**
     * Count records based on a given params - static method: countPaForumsUsers()
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
    public static function countPaForumsUsers( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->count_PaForumsUsers($conditionalStatement, $selectFields, $groupByFields);
        
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
    public  function getPage( $page = 0 ) {

         // calculate limit expression
         $l_start = $this->page_size * $page;
         $l_end   = $this->page_size;
         $limit_str = "$l_start,$l_end";

         // performs deletion of data
         return $this->list_PaForumsUsers($this->conditional_steatment, $this->order_by_steatment, $this->sort_steatment, $limit_str); 
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
    public  function populateFromArray( $params = array() ) {

        // required fields names
        $db_fields = array("user_id",
                           "board_id",
                           "user_status",
                           "is_active",
                           "date_join");

        // build argument list 
        foreach($db_fields as $param_name) { 
          if(array_key_exists($param_name, $params)) { 
            $this->{$param_name} = $params[$param_name];
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
    public  function populateFromObject( $source = null ) {

        // required fields names
        $db_fields = array("user_id",
                           "board_id",
                           "user_status",
                           "is_active",
                           "date_join");

        // build argument list 
        foreach($db_fields as $param_name) { 
          if(isset($source->$param_name)) { 
            $this->{$param_name} = $source->{$param_name};
          }
        } 
        
    }

    
    private function getUserStatus() {
        // MySQL query
        $sql = "SELECT user_status+0 AS status FROM { pa_forums_users } WHERE user_id = ? AND board_id = ?;";

        $res = null;
        // record ID
        $params = array($this->user_id, $this->board_id);
         
        // execute query
        $res = Dal::query($sql, $params);
        if($res->numRows() > 0) {
          $_objarr = $res->fetchRow(DB_FETCHMODE_OBJECT);
          $res = $_objarr->status;
        }
        return $res;
    }

    public function userStatusString() {
      $status = $this->get_user_status();
      if($status & self::_owner)   return __("owner");  
      if($status & self::_admin)   return __("admin");    
      if($status & self::_banned ) return __("banned");
      if($status & self::_limited) return __("limited");
      if($status & self::_allowed) return __("member");
    }

    public static function getStatusString($user_id) {
      $user = self::getPaForumsUsers($user_id);
      return $user->userStatusString();
    }

    
    public function updateUserStatus($new_status, $type = 'set') {
      $status = $this->get_user_status();
      switch($type) {
        case 'set':   $status = $status | $new_status; break;
        case 'reset': $status = $status ^ $new_status; break;
        break;
      }
      $this->set_user_status($status);
    }

}
?>