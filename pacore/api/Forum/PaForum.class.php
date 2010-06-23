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
require_once "api/Forum/PaForumThread.class.php";
require_once "api/Forum/PaForumPost.class.php";
require_once "api/Forum/PaForumCategory.class.php";
require_once "web/includes/classes/MemoryPagging.class.php";

class PaForum {

    /**
     * Name: id
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $id;

    /**
     * Name: title
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $title;

    /**
     * Name: description
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $description;

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
     * Name: category_id
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $category_id;

    /**
     * Name: sort_order
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $sort_order;

    /**
     * Name: icon
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $icon;

    /**
     * Name: created_at
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $created_at;

    /**
     * Name: updated_at
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $updated_at;

    /**
     * Name: $pagination
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    public $pagination;

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

    /**
     * parent category object
     **/
    public $category;

    /**
     * parent board object
     **/
    public $board;
//--------------- GET METHODS ----------------------------- //
    /**
     * Get value for field: id
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result id
     **/
    public  function get_id( ) {
        // returns the value of id
        return $this->id;
    }


    /**
     * Get value for field: title
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result title
     **/
    public  function get_title($limit = null) {
        // returns the value of title
        if(!$limit) {
          return $this->title;
        } else {
          $title = (strlen($this->title) <= $limit) ? $this->title : substr($this->title, 0, $limit -3) . '...';
          return $title;
        }
    }


    /**
     * Get value for field: description
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result description
     **/
    public  function get_description($limit = null) {
        // returns the value of description
        if(!$limit) {
          return $this->description;
        } else {
          $description = (strlen($this->description) <= $limit) ? $this->description : substr($this->description, 0, $limit -3) . '...';
          return $description;
        }
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
     * Get value for field: category_id
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result category_id
     **/
    public  function get_category_id( ) {
        // returns the value of category_id
        return $this->category_id;
    }


    /**
     * Get value for field: sort_order
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result sort_order
     **/
    public  function get_sort_order( ) {
        // returns the value of sort_order
        return $this->sort_order;
    }


    /**
     * Get value for field: icon
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result icon
     **/
    public  function get_icon($default_icon = null ) {
        // returns the value of icon
        if(!empty($this->icon)) {
          return $this->icon;
        } else {
          return $default_icon;
        }
    }


    /**
     * Get value for field: created_at
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result created_at
     **/
    public  function get_created_at( ) {
        // returns the value of created_at
        return $this->created_at;
    }


    /**
     * Get value for field: updated_at
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result updated_at
     **/
    public  function get_updated_at( ) {
        // returns the value of updated_at
        return $this->updated_at;
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
     * Set value for field: id
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @result void
     **/
    public  function set_id( $id ) {
        // sets the value of id
        $this->id = $id;
    }

    /**
     * Set value for field: title
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param title
     * @result void
     **/
    public  function set_title( $title ) {
        // sets the value of title
        $this->title = $title;
    }

    /**
     * Set value for field: description
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param description
     * @result void
     **/
    public  function set_description( $description ) {
        // sets the value of description
        $this->description = $description;
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
     * Set value for field: category_id
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param category_id
     * @result void
     **/
    public  function set_category_id( $category_id ) {
        // sets the value of category_id
        $this->category_id = $category_id;
    }

    /**
     * Set value for field: sort_order
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param sort_order
     * @result void
     **/
    public  function set_sort_order( $sort_order ) {
        // sets the value of sort_order
        $this->sort_order = $sort_order;
    }

    /**
     * Set value for field: icon
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param icon
     * @result void
     **/
    public  function set_icon( $icon ) {
        // sets the value of icon
        $this->icon = $icon;
    }

    /**
     * Set value for field: created_at
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param created_at
     * @result void
     **/
    public  function set_created_at( $created_at ) {
        // sets the value of created_at
        $this->created_at = $created_at;
    }

    /**
     * Set value for field: updated_at
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param updated_at
     * @result void
     **/
    public  function set_updated_at( $updated_at ) {
        // sets the value of updated_at
        $this->updated_at = $updated_at;
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
     * Class Constructor for: PaForum
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
        $this->initialize($conditionalStatement, $orderby, $sort, $pagesize, $fetchmode);
    }

    public  function initialize( $conditionalStatement = null, $orderby = null, $sort = null, $pagesize = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {
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
     * Load object from database - dynamic method: load_PaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @result void
     **/
    public  function load_PaForum( $id ) {

        // use get method to load object data
        $this->get_PaForum($id);

    }

    /**
     * Save object to the database - dynamic method: save_PaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result void
     **/
    public  function save_PaForum( ) {

        // determine is this a new object
        if(!empty($this->id)) {
          $itemsToUpdate = array('title' => $this->title,
                                 'description' => $this->description,
                                 'is_active' => $this->is_active,
                                 'category_id' => $this->category_id,
                                 'sort_order' => $this->sort_order,
                                 'icon' => $this->icon,
                                 'created_at' => $this->created_at,
                                 'updated_at' => $this->updated_at);
          $this->update_PaForum($this->id, $itemsToUpdate);
        } else {
          $this->insert_PaForum($this->title,
                                $this->description,
                                $this->is_active,
                                $this->category_id,
                                $this->sort_order,
                                $this->icon,
                                $this->created_at,
                                $this->updated_at);
        }
    }

    /**
     * Delete an existing record - dynamic method: delete_PaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @result void
     **/
    public  function delete_PaForum( $id ) {
        $conditionalStatement = "forum_id = $id AND is_active = 1";
        $threads =   PaForumThread::listPaForumThread( $conditionalStatement );
        foreach($threads as $thread) {
          PaForumThread::deletePaForumThread( $thread->get_id() );
        }

         // sql query
         $sql = "UPDATE { pa_forum } SET is_active = 0 WHERE id = ?;";
         $params = array($id);

         // performs deletion of data
         $res = Dal::query($sql, $params);
    }

    /**
     * Delete an existing record - static method: deletePaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @result void
     **/
    public static function deletePaForum( $id ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        $instance->delete_PaForum($id);

    }
    
    /**
     * Insert a new Record - dynamic method: insert_PaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param title
     * @param description
     * @param is_active
     * @param category_id
     * @param sort_order
     * @param icon
     * @param created_at
     * @param updated_at
     * @result id
     **/
    public  function insert_PaForum( $title, $description, $is_active, $category_id, $sort_order, $icon, $created_at, $updated_at ) {

        // items to be inserted in the database
        $params = array(null,
                      $title,
                      $description,
                      $is_active,
                      $category_id,
                      $sort_order,
                      $icon,
                      $created_at,
                      $updated_at);
        $__id = null;

        // insert query
        $sql = "INSERT INTO { pa_forum } ( id, title, description, is_active, category_id, sort_order, icon, created_at, updated_at ) VALUES ( ?,?,?,?,?,?,?,?,? );";

        // perform insert in the database
        $res = Dal::query($sql, $params);
        if($res) {
          $__id = Dal::insert_id();
        }
        return $__id;

    }

    /**
     * Insert a new Record - static method: insertPaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param params = array()
     * @result id
     **/
    public static function insertPaForum( $params = array() ) {

        // object self instance
        $instance = new self();

        // required fields names
        $db_fields = array("title",
                           "description",
                           "is_active",
                           "category_id",
                           "sort_order",
                           "icon",
                           "created_at",
                           "updated_at");

        // build argument list
        foreach($db_fields as $param_name) {
          if(!array_key_exists($param_name, $params)) {
            throw new Exception("PaForum::insertPaForum() - Missing parameter $param_name.");
          }
          $$param_name = $params[$param_name];
        }
        // call dynamic method
        return $instance->insert_PaForum($title,
                                         $description,
                                         $is_active,
                                         $category_id,
                                         $sort_order,
                                         $icon,
                                         $created_at,
                                         $updated_at);

    }

    /**
     * Retrieve an existing record - dynamic method: get_PaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForum
     **/
    public  function get_PaForum( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // MySQL query
        $sql = "SELECT * FROM { pa_forum } WHERE id = ?;";

        // record ID
        $params = array($id);

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
     * Retrieve an existing record - static method: getPaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForum
     **/
    public static function getPaForum( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        return $instance->get_PaForum($id, $fetchmode);

    }

    /**
     * Update an existing record - dynamic method: update_PaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result bool
     **/
    public  function update_PaForum( $id, $itemsToBeUpdated = array() ) {

         // sql query
         $sql = "UPDATE { pa_forum } SET ";

         // where steatment
         $__where = " WHERE id = ?;";

         // array of values
         $params = array();

         // build update paremeters
         foreach($itemsToBeUpdated as $field_name => $field_value) {
              $sql .= "$field_name = ?, ";
              $params[] = $field_value;
         }
         $sql = rtrim($sql, " ,");
         $sql .= $__where;
         $params[] = $id;
         // perform update operation
         $res = Dal::query($sql, $params);
         if($res) {
           $this->populateFromArray($itemsToBeUpdated);
           return true;
         }
         return false;
    }

    /**
     * Update an existing record - static method: updatePaForum()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result void
     **/
    public static function updatePaForum( $id, $itemsToBeUpdated = array() ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        return $instance->update_PaForum($id, $itemsToBeUpdated);

    }

    /**
     * Retrieved list of objects base on a given parameters - dynamic method: list_PaForum()
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
     * @result array of objects: PaForum
     **/
    public  function list_PaForum( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        $this->initialize($conditionalStatement, $orderby, $sort);
        // build MySQL query
        $sql = "SELECT * FROM { pa_forum } ";

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
              $obj = new PaForum();
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
     * Retrieved list of objects base on a given parameters - static method: listPaForum()
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
     * @result array of objects: PaForum
     **/
    public static function listPaForum( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        return $instance->list_PaForum($conditionalStatement, $orderby, $sort, $limit, $fetchmode);

    }

    /**
     * Count records based on a given params - dynamic method: count_PaForum()
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
    public  function count_PaForum( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // build MySQL query
        $sql = "SELECT ";
        if(count($selectFields) > 0) {
          $sql .= implode(", ", $selectFields) . ", COUNT(*) AS counter ";
        } else {
          $sql .= "COUNT(*) AS counter ";
        }
        $sql .= "FROM { pa_forum } ";
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
     * Count records based on a given params - static method: countPaForum()
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
    public static function countPaForum( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        return $instance->count_PaForum($conditionalStatement, $selectFields, $groupByFields);

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
         return $this->list_PaForum($this->conditional_steatment, $this->order_by_steatment, $this->sort_steatment, $limit_str);
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
        $db_fields = array("id",
                           "title",
                           "description",
                           "is_active",
                           "category_id",
                           "sort_order",
                           "icon",
                           "created_at",
                           "updated_at");

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
        $db_fields = array("id",
                           "title",
                           "description",
                           "is_active",
                           "category_id",
                           "sort_order",
                           "icon",
                           "created_at",
                           "updated_at");

        // build argument list
        foreach($db_fields as $param_name) {
          if(isset($source->$param_name)) {
            $this->{$param_name} = $source->{$param_name};
          }
        }

    }

    public function getThreads($pagesize = null, $page = null) {
      $threads = array();
      $conditionalStatement = "forum_id = $this->id AND is_active = 1";
      $orderby = " created_at";
      $sort = "DESC";
      $thrds_obj = new PaForumThread( $conditionalStatement, $orderby, $sort, $pagesize );
      $threads = $thrds_obj->getPage($page);
      return $threads;
    }

    public function getForumStatistics($pagesize = null, $page = null) {
      $statistic = array();
      $threads = array();
      $conditionalStatement = "forum_id = $this->id AND is_active = 1 AND status+0 < 16";
      $conditionalStatement_sticky = "forum_id = $this->id AND is_active = 1 AND status+0 >= 16";
      $orderby = " created_at";
      $sort = "DESC";
      $stickies = PaForumThread::listPaForumThread( $conditionalStatement_sticky, $orderby, $sort );
      $threads  = PaForumThread::listPaForumThread( $conditionalStatement, $orderby, $sort );
      $threads = array_merge($stickies, $threads);
      $statistics['nb_threads'] = count($threads);
      $nb_posts = 0;
      $last_post = null;
      $last_post_date = 0;
      $statistics['last_thread'] = (!empty($threads[0])) ? $threads[0] : null;
      foreach($threads as &$thread) {
       $thread->statistics = $thread->getThreadStatistics();
       $statistics['threads'][] = $thread;
       $nb_posts += $thread->statistics['posts'];
       if(is_object($thread->statistics['last_post'])) {
         $__obj = $thread->statistics['last_post'];
         $__obj->thread = $thread;
         $__post_date = strtotime($__obj->get_created_at());
         if($__post_date > $last_post_date) {
           $last_post = $__obj;
           $last_post_date = $__post_date;
         }
       }
      }
      if(!is_null($page)) {
        $this->pagination = new MemoryPagging(@$statistics['threads'], $pagesize, $page);
        $statistics['threads'] = $this->pagination->getPageItems();
      }
      $statistics['nb_posts'] = $nb_posts;
      $statistics['last_post'] = $last_post;
      return $statistics;
    }

    public function getBoard() {
      $this->category = PaForumCategory::getPaForumCategory($this->category_id);
      $this->board = $this->category->getBoard();
      return $this->board;
    }

    public function getNavigation($url, $css_class, $limit = 24, $separator = ' » ') {
      $navigation = array();
      $navigation[] = $this->category->getNavigation($url, $css_class, $limit, $separator);
      $this_link = add_querystring_var($url, "forum_id", $this->id);
      $text = $this->title;
      if(strlen($text) > $limit) {
        $text = substr($text, 0, $limit - 3) . "...";
      }
      $navigation[] = $this->get_a_tag($this_link, $css_class, $text);
      return implode($separator, $navigation);
    }

    private function get_a_tag($url, $class, $text) {
      return "<a href=\"$url\" class=\"$class\">$text</a>";
    }
}
?>
