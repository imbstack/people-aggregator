<?php 

require_once "db/Dal/Dal.php";
require_once "api/Forum/PaForum.class.php";
require_once "api/Forum/PaForumBoard.class.php";

class PaForumCategory { 

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
     * Name: name
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $name;

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
     * Name: sort_order
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $sort_order;

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
     * Get value for field: name
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result name
     **/
    public  function get_name($limit = null) {
        // returns the value of title
        if(!$limit) {
          return $this->name;
        } else {
          $title = (strlen($this->name) <= $limit) ? $this->name : substr($this->name, 0, $limit -3) . '...';
          return $title;
        }  
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
     * Get value for field: description
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result description
     **/
    public  function get_description( ) {
        // returns the value of description
        return $this->description;
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
     * Set value for field: name
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param name
     * @result void
     **/
    public  function set_name( $name ) {
        // sets the value of name
        $this->name = $name;
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
     * Class Constructor for: PaForumCategory
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
     * Load object from database - dynamic method: load_PaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public  function load_PaForumCategory( $id ) {

        // use get method to load object data
        $this->get_PaForumCategory($id);
        
    }

    /**
     * Save object to the database - dynamic method: save_PaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result void
     **/
    public  function save_PaForumCategory( ) {

        // determine is this a new object
        if(!empty($this->id)) { 
          $itemsToUpdate = array('name' => $this->name,
                                 'board_id' => $this->board_id,
                                 'description' => $this->description,
                                 'is_active' => $this->is_active,
                                 'sort_order' => $this->sort_order,
                                 'created_at' => $this->created_at,
                                 'updated_at' => $this->updated_at);
          $this->update_PaForumCategory($this->id, $itemsToUpdate); 
        } else { 
          $this->insert_PaForumCategory($this->name,
                                        $this->board_id,
                                        $this->description,
                                        $this->is_active,
                                        $this->sort_order,
                                        $this->created_at,
                                        $this->updated_at); 
        } 
    }

    /**
     * Delete an existing record - dynamic method: delete_PaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public  function delete_PaForumCategory( $id ) {
        $conditionalStatement = "category_id = $id AND is_active = 1";
        $forums =   PaForum::listPaForum( $conditionalStatement );
        foreach($forums as $forum) {
          PaForum::deletePaForum( $forum->get_id() );
        }

         // sql query
         $sql = "UPDATE { pa_forum_category } SET is_active = 0 WHERE id = ?;";
         $params = array($id);

         // performs deletion of data
         $res = Dal::query($sql, $params);
    }

    /**
     * Delete an existing record - static method: deletePaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public static function deletePaForumCategory( $id ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        $instance->delete_PaForumCategory($id);
        
    }

    /**
     * Insert a new Record - dynamic method: insert_PaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param name
     * @param board_id
     * @param description
     * @param is_active
     * @param sort_order
     * @param created_at
     * @param updated_at
     * @result id
     **/
    public  function insert_PaForumCategory( $name, $board_id, $description, $is_active, $sort_order, $created_at, $updated_at ) {

        // items to be inserted in the database 
        $params = array(null,
                      $name,
                      $board_id,
                      $description,
                      $is_active,
                      $sort_order,
                      $created_at,
                      $updated_at);
        $__id = null;

        // insert query
        $sql = "INSERT INTO { pa_forum_category } ( id, name, board_id, description, is_active, sort_order, created_at, updated_at ) VALUES ( ?,?,?,?,?,?,?,? );";

        // perform insert in the database
        $res = Dal::query($sql, $params);
        if($res) { 
          $__id = Dal::insert_id();
        }
        return $__id;

    }

    /**
     * Insert a new Record - static method: insertPaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param params = array()
     * @result id
     **/
    public static function insertPaForumCategory( $params = array() ) {

        // object self instance 
        $instance = new self();

        // required fields names
        $db_fields = array("name",
                           "board_id",
                           "description",
                           "is_active",
                           "sort_order",
                           "created_at",
                           "updated_at");

        // build argument list 
        foreach($db_fields as $param_name) { 
          if(!array_key_exists($param_name, $params)) { 
            throw new Exception("PaForumCategory::insertPaForumCategory() - Missing parameter $param_name.");
          }
          $$param_name = $params[$param_name];
        }
        // call dynamic method 
        return $instance->insert_PaForumCategory($name,
                                                 $board_id,
                                                 $description,
                                                 $is_active,
                                                 $sort_order,
                                                 $created_at,
                                                 $updated_at);
        
    }

    /**
     * Retrieve an existing record - dynamic method: get_PaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumCategory
     **/
    public  function get_PaForumCategory( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // MySQL query
        $sql = "SELECT * FROM { pa_forum_category } WHERE id = ?;";

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
     * Retrieve an existing record - static method: getPaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumCategory
     **/
    public static function getPaForumCategory( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->get_PaForumCategory($id, $fetchmode);
        
    }

    /**
     * Update an existing record - dynamic method: update_PaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result bool
     **/
    public  function update_PaForumCategory( $id, $itemsToBeUpdated = array() ) {

         // sql query
         $sql = "UPDATE { pa_forum_category } SET ";

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
     * Update an existing record - static method: updatePaForumCategory()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result void
     **/
    public static function updatePaForumCategory( $id, $itemsToBeUpdated = array() ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->update_PaForumCategory($id, $itemsToBeUpdated);
        
    }

    /**
     * Retrieved list of objects base on a given parameters - dynamic method: list_PaForumCategory()
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
     * @result array of objects: PaForumCategory
     **/
    public  function list_PaForumCategory( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        $this->initialize($conditionalStatement, $orderby, $sort);
        // build MySQL query
        $sql = "SELECT * FROM { pa_forum_category } ";

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
              $obj = new PaForumCategory(); 
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
     * Retrieved list of objects base on a given parameters - static method: listPaForumCategory()
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
     * @result array of objects: PaForumCategory
     **/
    public static function listPaForumCategory( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->list_PaForumCategory($conditionalStatement, $orderby, $sort, $limit, $fetchmode);
        
    }

    /**
     * Count records based on a given params - dynamic method: count_PaForumCategory()
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
    public  function count_PaForumCategory( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // build MySQL query
        $sql = "SELECT ";
        if(count($selectFields) > 0) {
          $sql .= implode(", ", $selectFields) . ", COUNT(*) AS counter "; 
        } else { 
          $sql .= "COUNT(*) AS counter "; 
        }
        $sql .= "FROM { pa_forum_category } ";
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
     * Count records based on a given params - static method: countPaForumCategory()
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
    public static function countPaForumCategory( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->count_PaForumCategory($conditionalStatement, $selectFields, $groupByFields);
        
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
         return $this->list_PaForumCategory($this->conditional_steatment, $this->order_by_steatment, $this->sort_steatment, $limit_str); 
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
                           "name",
                           "board_id",
                           "description",
                           "is_active",
                           "sort_order",
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
                           "name",
                           "board_id",
                           "description",
                           "is_active",
                           "sort_order",
                           "created_at",
                           "updated_at");

        // build argument list 
        foreach($db_fields as $param_name) { 
          if(isset($source->$param_name)) { 
            $this->{$param_name} = $source->{$param_name};
          }
        } 
        
    }
    
    public function getForums() {
      $forums = array();
      $conditionalStatement = "category_id = $this->id AND is_active = 1";
      $orderby = "created_at";
      $sort = "DESC";
      $forums = PaForum::listPaForum( $conditionalStatement, $orderby, $sort );
      return $forums;
    }
    
    public function getCategoryStatistics() {
      $statistic = array();
      $statistics['forums'] = array();
      $forums = $this->getForums();
      $statistics['nb_forums'] = count($forums);
      foreach($forums as &$forum) {
       $forum->statistics = $forum->getForumStatistics();
       $statistics['forums'][] = $forum;
      }
      return $statistics;
    }
    
    public function getBoard() {
      $this->board = PaForumBoard::getPaForumBoard($this->board_id);
      return $this->board;
    }
    
    public function getNavigation($url, $css_class, $limit = 24, $separator = ' » ') {
      $navigation = array();
      $navigation[] = $this->board->getNavigation($url, $css_class, $limit, $separator);
      $this_link = add_querystring_var($url, "category_id", $this->id);
      $text = $this->name;
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