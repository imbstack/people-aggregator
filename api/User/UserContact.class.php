<?php

class UserContact {

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
     * Name: user_id
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $user_id;

    /**
     * Name: contact_name
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $contact_name;

    /**
     * Name: contact_email
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $contact_email;

    /**
     * Name: contact_extra
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $contact_extra;

    /**
     * Name: contact_type
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     **/
    protected $contact_type;

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
     * Get value for field: contact_name
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result contact_name
     **/
    public  function get_contact_name( ) {
        // returns the value of contact_name
        return $this->contact_name;
    }


    /**
     * Get value for field: contact_email
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result contact_email
     **/
    public  function get_contact_email( ) {
        // returns the value of contact_email
        return $this->contact_email;
    }


    /**
     * Get value for field: contact_extra
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result contact_extra
     **/
    public  function get_contact_extra( ) {
        // returns the value of contact_extra
        return $this->contact_extra;
    }


    /**
     * Get value for field: contact_type
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result contact_type
     **/
    public  function get_contact_type( ) {
        // returns the value of contact_type
        return $this->contact_type;
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
     * Set value for field: contact_name
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param contact_name
     * @result void
     **/
    public  function set_contact_name( $contact_name ) {
        // sets the value of contact_name
        $this->contact_name = $contact_name;
    }

    /**
     * Set value for field: contact_email
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param contact_email
     * @result void
     **/
    public  function set_contact_email( $contact_email ) {
        // sets the value of contact_email
        $this->contact_email = $contact_email;
    }

    /**
     * Set value for field: contact_extra
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param contact_extra
     * @result void
     **/
    public  function set_contact_extra( $contact_extra ) {
        // sets the value of contact_extra
        $this->contact_extra = $contact_extra;
    }

    /**
     * Set value for field: contact_type
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param contact_type
     * @result void
     **/
    public  function set_contact_type( $contact_type ) {
        // sets the value of contact_type
        $this->contact_type = $contact_type;
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
     * Class Constructor for: UserContact
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
     * Load object from database - dynamic method: load_UserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @result void
     **/
    public  function load_UserContact( $id ) {

        // use get method to load object data
        $this->get_UserContact($id);

    }

    /**
     * Save object to the database - dynamic method: save_UserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @result void
     **/
    public  function save_UserContact( ) {

        // determine is this a new object
        if(!empty($this->id)) {
          $itemsToUpdate = array('user_id' => $this->user_id,
                                 'contact_name' => $this->contact_name,
                                 'contact_email' => $this->contact_email,
                                 'contact_extra' => $this->contact_extra,
                                 'contact_type' => $this->contact_type);
          $this->update_UserContact($this->id, $itemsToUpdate);
        } else {
          $this->insert_UserContact($this->user_id,
                                    $this->contact_name,
                                    $this->contact_email,
                                    $this->contact_extra,
                                    $this->contact_type);
        }
    }

    /**
     * Delete an existing record - dynamic method: delete_UserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @result void
     **/
    public  function delete_UserContact( $id ) {

         // sql query
         $sql = "DELETE FROM { user_contact } WHERE id = ?;";
         $params = array($id);

         // performs deletion of data
         $res = Dal::query($sql, $params);
    }

    /**
     * Delete an existing record - static method: deleteUserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @result void
     **/
    public static function deleteUserContact( $id ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        $instance->delete_UserContact($id);

    }

    /**
     * Insert a new Record - dynamic method: insert_UserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param user_id
     * @param contact_name
     * @param contact_email
     * @param contact_extra
     * @param contact_type
     * @result id
     **/
    public  function insert_UserContact( $user_id, $contact_name, $contact_email, $contact_extra, $contact_type ) {

        // items to be inserted in the database
        $params = array(null,
                      $user_id,
                      $contact_name,
                      $contact_email,
                      $contact_extra,
                      $contact_type);
        $__id = null;

        // insert query
        $sql = "INSERT INTO { user_contact } ( id, user_id, contact_name, contact_email, contact_extra, contact_type ) VALUES ( ?,?,?,?,?,? );";

        // perform insert in the database
        $res = Dal::query($sql, $params);
        if($res) {
          $__id = Dal::insert_id();
        }
        return $__id;

    }

    /**
     * Insert a new Record - static method: insertUserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param params = array()
     * @result id
     **/
    public static function insertUserContact( $params = array() ) {

        // object self instance
        $instance = new self();

        // required fields names
        $db_fields = array("user_id",
                           "contact_name",
                           "contact_email",
                           "contact_extra",
                           "contact_type");

        // build argument list
        foreach($db_fields as $param_name) {
          if(!array_key_exists($param_name, $params)) {
            throw new Exception("UserContact::insertUserContact() - Missing parameter $param_name.");
          }
          $$param_name = $params[$param_name];
        }
        // call dynamic method
        return $instance->insert_UserContact($user_id,
                                             $contact_name,
                                             $contact_email,
                                             $contact_extra,
                                             $contact_type);

    }

    /**
     * Retrieve an existing record - dynamic method: get_UserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: UserContact
     **/
    public  function get_UserContact( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // MySQL query
        $sql = "SELECT * FROM { user_contact } WHERE id = ?;";

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
     * Retrieve an existing record - static method: getUserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: UserContact
     **/
    public static function getUserContact( $id, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        return $instance->get_UserContact($id, $fetchmode);

    }

    /**
     * Update an existing record - dynamic method: update_UserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result bool
     **/
    public  function update_UserContact( $id, $itemsToBeUpdated = array() ) {

         // sql query
         $sql = "UPDATE { user_contact } SET ";

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
     * Update an existing record - static method: updateUserContact()
     *
     *
     * Generated with the DalClassGenerator created by:
     * Zoran Hron <zhron@broadbandmechanics.com>
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result void
     **/
    public static function updateUserContact( $id, $itemsToBeUpdated = array() ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        return $instance->update_UserContact($id, $itemsToBeUpdated);

    }

    /**
     * Retrieved list of objects base on a given parameters - dynamic method: list_UserContact()
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
     * @result array of objects: UserContact
     **/
    public  function list_UserContact( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // build MySQL query
        $sql = "SELECT * FROM { user_contact } ";

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
              $obj = new UserContact();
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
     * Retrieved list of objects base on a given parameters - static method: listUserContact()
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
     * @result array of objects: UserContact
     **/
    public static function listUserContact( $conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        return $instance->list_UserContact($conditionalStatement, $orderby, $sort, $limit, $fetchmode);

    }

    /**
     * Count records based on a given params - dynamic method: count_UserContact()
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
    public  function count_UserContact( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // build MySQL query
        $sql = "SELECT ";
        if(count($selectFields) > 0) {
          $sql .= implode(", ", $selectFields) . ", COUNT(*) AS counter ";
        } else {
          $sql .= "COUNT(*) AS counter ";
        }
        $sql .= "FROM { user_contact } ";
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
     * Count records based on a given params - static method: countUserContact()
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
    public static function countUserContact( $conditionalStatement = null, $selectFields = array(), $groupByFields = array() ) {

        // object self instance
        $instance = new self();

        // call dynamic method
        return $instance->count_UserContact($conditionalStatement, $selectFields, $groupByFields);

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

         //
         return $this->list_UserContact($this->conditional_steatment, $this->order_by_steatment, $this->sort_steatment, $limit_str);
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
                           "user_id",
                           "contact_name",
                           "contact_email",
                           "contact_extra",
                           "contact_type");

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
                           "user_id",
                           "contact_name",
                           "contact_email",
                           "contact_extra",
                           "contact_type");

        // build argument list
        foreach($db_fields as $param_name) {
          if(isset($source->$param_name)) {
            $this->{$param_name} = $source->{$param_name};
          }
        }

    }

}
?>
