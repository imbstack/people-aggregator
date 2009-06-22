<?php

require_once "db/Dal/Dal.php";
require_once "api/User/User.php";
class PaForumPost { 

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
     * Name: content
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $content;

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
     * Name: parent_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $parent_id;

    /**
     * Name: thread_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $thread_id;

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
     * Name: modified_by
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $modified_by = null;
    
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
     * Get value for field: content
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result content
     **/
    public  function get_content( ) {
        // returns the value of content
        return $this->content;
    }


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
     * Get value for field: parent_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result parent_id
     **/
    public  function get_parent_id( ) {
        // returns the value of parent_id
        return $this->parent_id;
    }


    /**
     * Get value for field: thread_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result thread_id
     **/
    public  function get_thread_id( ) {
        // returns the value of thread_id
        return $this->thread_id;
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
     * Get value for field: modified_by
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result modified_by
     **/
    public  function get_modified_by( ) {
        // returns the value of modified_by
        return $this->modified_by;
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
     * Set value for field: content
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param content
     * @result void
     **/
    public  function set_content( $content ) {
        // sets the value of content
        $this->content = $content;
    }

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
     * Set value for field: parent_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param parent_id
     * @result void
     **/
    public  function set_parent_id( $parent_id ) {
        // sets the value of parent_id
        $this->parent_id = $parent_id;
    }

    /**
     * Set value for field: thread_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param thread_id
     * @result void
     **/
    public  function set_thread_id( $thread_id ) {
        // sets the value of thread_id
        $this->thread_id = $thread_id;
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
     * Class Constructor for: PaForumPost
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
     * Load object from database - dynamic method: load_PaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public  function load_PaForumPost( $id ) {

        // use get method to load object data
        $this->get_PaForumPost($id);
        
    }

    /**
     * Save object to the database - dynamic method: save_PaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result void
     **/
    public  function save_PaForumPost( ) {

        // determine is this a new object
        if(!empty($this->id)) { 
          $itemsToUpdate = array('title' => $this->title,
                                 'content' => $this->content,
                                 'user_id' => $this->user_id,
                                 'parent_id' => $this->parent_id,
                                 'thread_id' => $this->thread_id,
                                 'is_active' => $this->is_active,
                                 'created_at' => $this->created_at,
                                 'updated_at' => $this->updated_at,
                                 'modified_by'=> $this->modified_by); 
          $this->update_PaForumPost($this->id, $itemsToUpdate); 
        } else { 
          $this->insert_PaForumPost($this->title,
                                    $this->content,
                                    $this->user_id,
                                    $this->parent_id,
                                    $this->thread_id,
                                    $this->is_active,
                                    $this->created_at,
                                    $this->updated_at,
                                    $this->modified_by); 
        } 
    }

    /**
     * Delete an existing record - dynamic method: delete_PaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public  function delete_PaForumPost( $id ) {

         // sql query
         $sql = "UPDATE { pa_forum_post } SET is_active = 0 WHERE id = ?;";
         $params = array($id);

         // performs deletion of data
         $res = Dal::query($sql, $params);
    }

    /**
     * Delete an existing record - static method: deletePaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public static function deletePaForumPost( $id ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        $instance->delete_PaForumPost($id);
        
    }

    /**
     * Delete all user posts - static method: delete_PostsForUser()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public static function delete_PostsForUser( $user_id ) {

         // sql query
         $sql = "UPDATE { pa_forum_post } SET is_active = 0 WHERE user_id = ?;";
         $params = array($user_id);

         // performs deletion of data
         $res = Dal::query($sql, $params);
    }


    /**
     * Insert a new Record - dynamic method: insert_PaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param title
     * @param content
     * @param user_id
     * @param parent_id
     * @param thread_id
     * @param is_active
     * @param created_at
     * @param updated_at
     * @param modified_by
     * @result id
     **/
    public  function insert_PaForumPost( $title, $content, $user_id, $parent_id, $thread_id, $is_active, $created_at, $updated_at, $modified_by ) {

        // items to be inserted in the database 
        $params = array(null,
                      $title,
                      $content,
                      $user_id,
                      $parent_id,
                      $thread_id,
                      $is_active,
                      $created_at,
                      $updated_at,
                      $modified_by);
        $__id = null;

        // insert query
        $sql = "INSERT INTO { pa_forum_post } ( id, title, content, user_id, parent_id, thread_id, is_active, created_at, updated_at, modified_by ) VALUES ( ?,?,?,?,?,?,?,?,?,? );";

        // perform insert in the database
        $res = Dal::query($sql, $params);
        if($res) { 
          $__id = Dal::insert_id();
        }
        return $__id;

    }

    /**
     * Insert a new Record - static method: insertPaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param params = array()
     * @result id
     **/
    public static function insertPaForumPost( $params = array() ) {

        // object self instance 
        $instance = new self();

        // required fields names
        $db_fields = array("title",
                           "content",
                           "user_id",
                           "parent_id",
                           "thread_id",
                           "is_active",
                           "created_at",
                           "updated_at",
                           "modified_by");
        // build argument list
        foreach($db_fields as $param_name) { 
          if(!array_key_exists($param_name, $params)) { 
            throw new Exception("PaForumPost::insertPaForumPost() - Missing parameter $param_name.");
          }
          $$param_name = $params[$param_name];
        }
        // call dynamic method
        return $instance->insert_PaForumPost($title,
                                             $content,
                                             $user_id,
                                             $parent_id,
                                             $thread_id,
                                             $is_active,
                                             $created_at,
                                             $updated_at,
                                             $modified_by);
        
    }

    /**
     * Retrieve an existing record - dynamic method: get_PaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumPost
     **/
    public  function get_PaForumPost( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // MySQL query
        $sql = "SELECT * FROM { pa_forum_post } WHERE id = ?;";

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
     * Retrieve an existing record - static method: getPaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumPost
     **/
    public static function getPaForumPost( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->get_PaForumPost($id, $fetchmode);
        
    }

    /**
     * Update an existing record - dynamic method: update_PaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result bool
     **/
    public  function update_PaForumPost( $id, $itemsToBeUpdated = array() ) {

         // sql query
         $sql = "UPDATE { pa_forum_post } SET ";

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
     * Update an existing record - static method: updatePaForumPost()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result void
     **/
    public static function updatePaForumPost( $id, $itemsToBeUpdated = array() ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->update_PaForumPost($id, $itemsToBeUpdated);
        
    }

    /**
     * Retrieved list of objects base on a given parameters - dynamic method: list_PaForumPost()
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
     * @result array of objects: PaForumPost
     **/
    public  function list_PaForumPost( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        $this->initialize($conditionalStatement, $orderby, $sort);
        // build MySQL query
        $sql = "SELECT * FROM { pa_forum_post } ";

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
              $obj = new PaForumPost(); 
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
     * Retrieved list of objects base on a given parameters - static method: listPaForumPost()
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
     * @result array of objects: PaForumPost
     **/
    public static function listPaForumPost( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->list_PaForumPost($conditionalStatement, $orderby, $sort, $limit, $fetchmode);
        
    }

    /**
     * Count records based on a given params - dynamic method: count_PaForumPost()
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
    public  function count_PaForumPost( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // build MySQL query
        $sql = "SELECT ";
        if(count($selectFields) > 0) {
          $sql .= implode(", ", $selectFields) . ", COUNT(*) AS counter "; 
        } else { 
          $sql .= "COUNT(*) AS counter "; 
        }
        $sql .= "FROM { pa_forum_post } ";
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
     * Count records based on a given params - static method: countPaForumPost()
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
    public static function countPaForumPost( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->count_PaForumPost($conditionalStatement, $selectFields, $groupByFields);
        
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
         return $this->list_PaForumPost($this->conditional_steatment, $this->order_by_steatment, $this->sort_steatment, $limit_str); 
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
                           "content",
                           "user_id",
                           "parent_id",
                           "thread_id",
                           "is_active",
                           "created_at",
                           "updated_at",
                           "modified_by");

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
                           "content",
                           "user_id",
                           "parent_id",
                           "thread_id",
                           "is_active",
                           "created_at",
                           "updated_at",
                           "modified_by");

        // build argument list 
        foreach($db_fields as $param_name) { 
          if(isset($source->$param_name)) { 
            $this->{$param_name} = $source->{$param_name};
          }
        } 
        
    }

    public function getAuthor() {
      $user  = new User();
      if($this->user_id == -1) {
        $user->user_id    = -1;
        $user->login_name = 'anonymous';
        $user->picture    = 'anonymous.gif';
      } else {
        $user->load((int)$this->user_id);
      }
      return $user;
    }

}
?>