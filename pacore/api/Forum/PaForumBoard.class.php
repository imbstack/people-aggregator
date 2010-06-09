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

require_once "api/Forum/PaForumCategory.class.php";

class PaForumBoard {

    const network_board   = 'network';
    const group_board     = 'group';
    const personal_board  = 'personal';
    const hidden_board    = 'hidden';

    const default_avatar_size = '95x110';
    
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
     * Name: network_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $network_id;
    
    /**
     * Name: owner_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $owner_id;

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
     * Name: type
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $type;

    /**
     * Name: theme
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $theme;

    /**
     * Name: settings
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $settings;

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
     * Get value for field: network_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result network_id
     **/
    public  function get_network_id( ) {
        // returns the value of network_id
        return $this->network_id;
    }


    /**
     * Get value for field: owner_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result owner_id
     **/
    public  function get_owner_id( ) {
        // returns the value of owner_id
        return $this->owner_id;
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
    public  function get_title( ) {
        // returns the value of title
        return $this->title;
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
     * Get value for field: type
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result type
     **/
    public  function get_type( ) {
        // returns the value of type
        return $this->type;
    }


    /**
     * Get value for field: theme
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result theme
     **/
    public  function get_theme( ) {
        // returns the value of theme
        return $this->theme;
    }


    /**
     * Get value for field: settings
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result settings
     **/
    public  function get_settings( ) {
        // returns the value of settings
        return unserialize($this->settings);
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
     * Set value for field: network_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param network_id
     * @result void
     **/
    public  function set_network_id( $network_id ) {
        // sets the value of network_id
        $this->network_id = $network_id;
    }
    

    /**
     * Set value for field: owner_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param owner_id
     * @result void
     **/
    public  function set_owner_id( $owner_id ) {
        // sets the value of owner_id
        $this->owner_id = $owner_id;
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
     * Set value for field: type
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param type
     * @result void
     **/
    public  function set_type( $type ) {
        // sets the value of type
        $this->type = $type;
    }

    /**
     * Set value for field: theme
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param theme
     * @result void
     **/
    public  function set_theme( $theme ) {
        // sets the value of theme
        $this->theme = $theme;
    }

    /**
     * Set value for field: settings
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param settings
     * @result void
     **/
    public  function set_settings( $settings ) {
        // sets the value of settings
        $this->settings = serialize($settings);
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
     * Class Constructor for: PaForumBoard
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
     * Load object from database - dynamic method: load_PaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public  function load_PaForumBoard( $id ) {

        // use get method to load object data
        $this->get_PaForumBoard($id);
        
    }

    /**
     * Save object to the database - dynamic method: save_PaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result void
     **/
    public  function save_PaForumBoard( ) {

        // determine is this a new object
        if(!empty($this->id)) { 
          $itemsToUpdate = array('owner_id'   => $this->owner_id,
                                 'network_id' => $this->network_id,
                                 'title' => $this->title,
                                 'description' => $this->description,
                                 'type' => $this->type,
                                 'theme' => $this->theme,
                                 'settings' => $this->settings,
                                 'is_active' => $this->is_active,
                                 'created_at' => $this->created_at);
          $this->update_PaForumBoard($this->id, $itemsToUpdate); 
        } else { 
          $this->insert_PaForumBoard($this->owner_id,
                                     $this->network_id,
                                     $this->title,
                                     $this->description,
                                     $this->type,
                                     $this->theme,
                                     $this->settings,
                                     $this->is_active,
                                     $this->created_at); 
        } 
    }

    /**
     * Delete an existing record - dynamic method: delete_PaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public  function delete_PaForumBoard( $id ) {
        $conditionalStatement = "board_id = $id AND is_active = 1";
        $categories = PaForumCategory::listPaForumCategory( $conditionalStatement );
        foreach($categories as $category) {
          PaForumCategory::deletePaForumCategory( $category->get_id() );
        }

         // sql query
         $sql = "DELETE FROM { pa_forum_board } WHERE id = ?;";
         $params = array($id);

         // performs deletion of data
         $res = Dal::query($sql, $params);

         $del_users_sql = "DELETE FROM { pa_forums_users } WHERE board_id = ?;";
         $params = array($id);
         // performs deletion of members
         $res = Dal::query($del_users_sql, $params);
    }

    /**
     * Delete an existing record - static method: deletePaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public static function deletePaForumBoard( $id ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        $instance->delete_PaForumBoard($id);
        
    }

    /**
     * Delete all user Boards - static method: delete_UserBoards()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public static function delete_UserBoards( $user_id ) {
        $boards = self::listPaForumBoard("owner_id = $user_id AND type <> 'group' AND type <> 'network' AND is_active = 1");
        foreach($boards as $board) {
          $board->delete_PaForumBoard($board->get_id());
        }
    }


    /**
     * Insert a new Record - dynamic method: insert_PaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param owner_id
     * @param title
     * @param description
     * @param type
     * @param theme
     * @param settings
     * @param is_active
     * @param created_at
     * @result id
     **/
    public  function insert_PaForumBoard( $owner_id, $network_id, $title, $description, $type, $theme, $settings, $is_active, $created_at ) {

        // items to be inserted in the database 
        $params = array(null,
                      $owner_id,
                      $network_id,
                      $title,
                      $description,
                      $type,
                      $theme,
                      $settings,
                      $is_active,
                      $created_at);
        $__id = null;

        // insert query
        $sql = "INSERT INTO { pa_forum_board } ( id, owner_id, network_id, title, description, type, theme, settings, is_active, created_at ) VALUES ( ?,?,?,?,?,?,?,?,?,? );";

        // perform insert in the database
        $res = Dal::query($sql, $params);
        if($res) { 
          $__id = Dal::insert_id();
        }
        return $__id;

    }

    /**
     * Insert a new Record - static method: insertPaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param params = array()
     * @result id
     **/
    public static function insertPaForumBoard( $params = array() ) {

        // object self instance 
        $instance = new self();

        // required fields names
        $db_fields = array("owner_id",
                           "network_id",
                           "title",
                           "description",
                           "type",
                           "theme",
                           "settings",
                           "is_active",
                           "created_at");

        // build argument list 
        foreach($db_fields as $param_name) { 
          if(!array_key_exists($param_name, $params)) { 
            throw new Exception("PaForumBoard::insertPaForumBoard() - Missing parameter $param_name.");
          }
          $$param_name = $params[$param_name];
        }
        // call dynamic method 
        return $instance->insert_PaForumBoard($owner_id,
                                              $network_id,
                                              $title,
                                              $description,
                                              $type,
                                              $theme,
                                              $settings,
                                              $is_active,
                                              $created_at);
        
    }

    /**
     * Retrieve an existing record - dynamic method: get_PaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumBoard
     **/
    public  function get_PaForumBoard( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // MySQL query
        $sql = "SELECT * FROM { pa_forum_board } WHERE id = ?;";

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
     * Retrieve an existing record - static method: getPaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumBoard
     **/
    public static function getPaForumBoard( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->get_PaForumBoard($id, $fetchmode);
        
    }

    /**
     * Update an existing record - dynamic method: update_PaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result bool
     **/
    public  function update_PaForumBoard( $id, $itemsToBeUpdated = array() ) {

         // sql query
         $sql = "UPDATE { pa_forum_board } SET ";

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
     * Update an existing record - static method: updatePaForumBoard()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result void
     **/
    public static function updatePaForumBoard( $id, $itemsToBeUpdated = array() ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->update_PaForumBoard($id, $itemsToBeUpdated);
        
    }

    /**
     * Retrieved list of objects base on a given parameters - dynamic method: list_PaForumBoard()
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
     * @result array of objects: PaForumBoard
     **/
    public  function list_PaForumBoard( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        $this->initialize($conditionalStatement, $orderby, $sort);
        // build MySQL query
        $sql = "SELECT * FROM { pa_forum_board } ";

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
              $obj = new PaForumBoard(); 
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
     * Retrieved list of objects base on a given parameters - static method: listPaForumBoard()
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
     * @result array of objects: PaForumBoard
     **/
    public static function listPaForumBoard( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->list_PaForumBoard($conditionalStatement, $orderby, $sort, $limit, $fetchmode);
        
    }

    /**
     * Count records based on a given params - dynamic method: count_PaForumBoard()
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
    public  function count_PaForumBoard( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // build MySQL query
        $sql = "SELECT ";
        if(count($selectFields) > 0) {
          $sql .= implode(", ", $selectFields) . ", COUNT(*) AS counter "; 
        } else { 
          $sql .= "COUNT(*) AS counter "; 
        }
        $sql .= "FROM { pa_forum_board } ";
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
     * Count records based on a given params - static method: countPaForumBoard()
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
    public static function countPaForumBoard( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // object self instance 
        $instance = new self();

        // call dynamic method 
        return $instance->count_PaForumBoard($conditionalStatement, $selectFields, $groupByFields);
        
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
         return $this->list_PaForumBoard($this->conditional_steatment, $this->order_by_steatment, $this->sort_steatment, $limit_str); 
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
                           "owner_id",
                           "network_id",
                           "title",
                           "description",
                           "type",
                           "theme",
                           "settings",
                           "is_active",
                           "created_at");

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
                           "owner_id",
                           "network_id",
                           "title",
                           "description",
                           "type",
                           "theme",
                           "settings",
                           "is_active",
                           "created_at");

        // build argument list 
        foreach($db_fields as $param_name) { 
          if(isset($source->$param_name)) { 
            $this->{$param_name} = $source->{$param_name};
          }
        } 
        
    }
    
    public function getCategories($pagesize = null, $page = null) {
      $categories = array();
      $conditionalStatement = "board_id = $this->id AND is_active = 1";
      $orderby = "created_at";
      $sort = "DESC";
      $categories = PaForumCategory::listPaForumCategory( $conditionalStatement, $orderby, $sort );
      return $categories;
    }
    
    public function getBoardStatistics() {
      $categories = array();
      $statistics['categories'] = array();
      $categories = $this->getCategories();
      $statistics['nb_categories'] = count($categories);
      foreach($categories as &$category) {
       $category->statistics = $category->getCategoryStatistics();
       $statistics['categories'][] = $category;
      }
      return $statistics;
    }
    
    public function getNavigation($url, $css_class, $limit = 24, $separator = ' > ') {
/*      $this_link = add_querystring_var($url, "board_id", $this->id);
      $navigation = $this->get_a_tag($this_link, $css_class, __("Home"));
*/      
      $navigation = $this->get_a_tag($url, $css_class, __("Home"));
      return $navigation;
    }
  
    private function get_a_tag($url, $class, $text) {
      return "<a href=\"$url\" class=\"$class\">$text</a>";
    }

    public function getAvatarSize() {
      $size = array();
      $settings = $this->get_settings();
      $_sz = explode('x', $settings['avatar_size']);
      if(!empty($_sz[0]) && !empty($_sz[1])) {
        $size['width']  = $_sz[0];
        $size['height'] = $_sz[1];
      } else {
        $_sz = explode('x', PaForumBoard::default_avatar_size);
        $size['width']  = $_sz[0];
        $size['height'] = $_sz[1];
      }
      return $size;
    }
    
}
?>