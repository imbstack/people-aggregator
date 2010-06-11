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
  /* API for Links management. The methods in the API will be used for creating, editing and deleting link categories & links under the categories.*/
  
include_once dirname(__FILE__)."/../../config.inc";
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";





class NetworkLinks {
    
    /**
    *   Creater of the Link and Category will be identified by the user_id
    *   category_id
    *   @var integer
    *   @access public
    */
    public $user_id;
    
    /**
    *   Links can be added under some category. Each link category is identified by a unique *   category_id
    *   @var integer
    *   @access public
    */
    public $category_id;
    
    /**
    *   Name of the category.      
    *   @var varchar
    *   @access public
    */
    public $category_name;
    
    /**
    *   Links added under categories will be identified by a unique identifier called link_id
    *   @var integer
    *   @access public
    */
    public $link_id;
    
    /**
    *   title of the link is referenced by title
    *   @var varchar
    *   @access public
    */
    public $title;
    
    /**
    *   Each link is associated with a linking url.
    *   @var varchar
    *   @access public
    */
    public $url;
    
    /**
    *   Status of the categories and link will be determined by is_active class variable. For *   deleted category or link is_active is -1 and 1 otherwise.
    *   @var integer
    *   @access public
    */
    public $is_active = 1;
    
    /**
    *   Creation time of the category or link.
    *   @var integer
    *   @access public
    */
    public $created;
    
    /**
    *   Last updation of link & category will be tracked by changed class variable
    *   @var integer
    *   @access public
    */
    public $changed;
    
    
    /**
    *   Public function to set the values of the class variables.
    *   @param $param_array associative array of the class variables.
    *  @access public
    */
    
    function set_params ($param_array) {
        Logger::log("Enter: function NetworkLinks::set_params");
        foreach ($param_array as $key => $value) {
            $this->$key = $value;
        }
        Logger::log("Exit: function NetworkLinks::set_params");
        return;
    }
    
    
    /**
    *   Method to save a category. Parameters for the function are class variables which are *   set by a setter function. 
    *   @param no parameters
    *   @access public
    */
    
    public function save_category () {
        Logger::log("Enter: function NetworkLinks::save_category");
        /*  Check for category with existing name */
        $condition = array('category_name'=> $this->category_name, 'user_id'=> $this->user_id,  'is_active'=> $this->is_active);
        $data_array = $this->load_category($condition);
        
        if(count($data_array) == 0) {
            $sql = "INSERT INTO {network_linkcategories} (category_name, user_id, created, changed, is_active) values (?, ?, ?, ?, ?)";
            $data = array($this->category_name, $this->user_id, $this->created, $this->changed, $this->is_active);
        
            $res = Dal::query($sql, $data);
        } else {
            /*  User has already created a category with the given name */
            
            Logger::log("Throwing exception LINK_CATEGORY_EXISTS | Link category already exists", LOGGER_ERROR);
            throw new PAException(LINK_CATEGORY_EXISTS, "Link category already exists.");
        }
        
        $this->category_id = Dal::insert_id();
        Logger::log("Exit: function NetworkLinks::save_category");
        return $this->category_id;
    }
    
    
    /**
    *   Conditional Load function for category.
    *   @param $condition_array associative array of the class variables with their values.
    *   @access public
    */
    
    public function load_category ($condition = NULL, $limit = NULL) {
        Logger::log("Enter: function NetworkLinks::load_category");
        $sql = "SELECT * FROM {network_linkcategories} WHERE 1 ";
        
        $data = array();
        if(count($condition) > 0) {              
            foreach ($condition as $key => $value) {
                $sql .= " AND $key = ?"; 
                $data[] = $value;   
            }             
        }
        $sql .= " ORDER BY created";
        if(!empty($limit)) {
            $sql .= " LIMIT $limit";
        }
        
        $res = Dal::query($sql, $data);
        $return = array(); 
        if ($res->numRows() > 0) { 
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $return[] = $row;                  
            }
        } 
                  
        Logger::log("Exit: function NetworkLinks::load_category");
        return $return;
    }
    
    
    /**
    *   Method to add a new link under a category for a user
    *   @param no parameters
    *   @access public
    */
    
    public function save_link () {
        Logger::log("Enter: function NetworkLinks::save_link");
        /*  Check for existing title of the link  */
        $condition = array('title'=> $this->title, 'category_id'=> $this->category_id, 'is_active'=> $this->is_active);
        $data_array = $this->load_link($condition);
        
        if(count($data_array) == 0) {
            $sql = "INSERT INTO {network_links} (title, url, category_id, created, changed, is_active) values (?, ?, ?, ?, ?, ?)";
            $data = array($this->title, $this->url, $this->category_id, $this->created,  $this->changed, $this->is_active);
        
            $res = Dal::query($sql, $data);
        } else {
            /*  User has already created a link with the given name */
            
            Logger::log("Throwing exception LINK_EXISTS | Link with the given title already exists", LOGGER_ERROR);
            throw new PAException(LINK_EXISTS, "Link with the given title already exists");
        }
        
        Logger::log("Exit: function NetworkLinks::save_link");
    }
    
    
    /**
    *   Method to load a links or links.
    *   @param $condition_array associative array of the class variables with their values.
    *   @access public
    */
    
    public function load_link ($condition = NULL, $limit = NULL) {
        Logger::log("Enter: function NetworkLinks::load_link");
        $sql_modified = "";
        if(is_array($this->link_id)) {
            $link_id_string = implode(',', $this->link_id);
            unset($condition['link_id']);
            $sql_modified = " AND L.link_id IN (".$link_id_string.")";
        }
        $sql = "SELECT L.* FROM {network_links} AS L INNER JOIN {network_linkcategories} AS LC ON L.category_id = LC.category_id WHERE LC.user_id = ?";
        
        $sql .= $sql_modified;          
        
        $data = array($this->user_id);
        if(count($condition) > 0) {              
            foreach ($condition as $key => $value) {
                $sql .= " AND L.$key = ?"; 
                $data[] = $value;   
            }             
        }
        
        $sql .= " ORDER BY L.created";
        
        if(!empty($limit)) {
          $sql .= " LIMIT $limit";
        }          
        
        $res = Dal::query($sql, $data);
        $return = array(); 
        if ($res->numRows() > 0) { 
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $return[] = $row;                  
            }
        }
        
        Logger::log("Exit: function NetworkLinks::load_link");
        return $return;
    }
    
    
    /**
    *   Method to update a link category
    *   @param no parameters
    *   @access public
    */
    
    public function update_category () {
        Logger::log("Enter: function NetworkLinks::update_category");
        
        $sql = "SELECT * FROM {network_linkcategories} WHERE category_name = ? AND user_id = ? AND category_id <> ?";
        $data = array($this->category_name, $this->user_id, $this->category_id);
        
        $res = Dal::query($sql, $data);
        
        if($res->numRows() == 0) {
            $sql = "UPDATE {network_linkcategories} SET category_name = ?, changed = ? WHERE category_id = ?";
            $data = array($this->category_name, $this->changed, $this->category_id);
            
            $res = Dal::query($sql, $data);
        } else {
            /* Category exist for user  */
            Logger::log("Throwing exception LINK_CATEGORY_EXISTS | Link category already exists", LOGGER_ERROR);
            throw new PAException(LINK_CATEGORY_EXISTS, "Link category already exists");
        }
        
        Logger::log("Exit: function NetworkLinks::update_category");
    }
    
    
    /**
    *   Method to update a link
    *   @param no parameters
    *   @access public
    */
    
    public function update_link () {
        Logger::log("Enter: function NetworkLinks::update_link");
        
        $sql = "SELECT L.* FROM {network_links} AS L INNER JOIN {network_linkcategories} AS LC ON L.category_id = LC.category_id WHERE L.title = ? AND L.category_id = ? AND LC.user_id = ? AND L.link_id <> ?";
        $data = array($this->title, $this->category_id, $this->user_id, $this->link_id);
        
        $res = Dal::query($sql, $data);
        
        if($res->numRows() == 0) {              
            $sql = "UPDATE {network_links} SET title = ?, url = ?, category_id = ?, changed = ? WHERE link_id = ?";
            $data = array($this->title, $this->url, $this->category_id, $this->changed, $this->link_id);
            $res = Dal::query($sql, $data);
        } else {
            /* Link with given title already exist for user  */
            Logger::log("Throwing exception LINK_EXISTS | Link with the given title already exists", LOGGER_ERROR);
            throw new PAException(LINK_EXISTS, "Link with the given title already exists");
        }
        Logger::log("Exit: function NetworkLinks::update_link");
    }
    
    
    /**
    *   Method for deleting a link category.
    *   @param no parameters
    *   @access public
    */
    
    public function delete_category () {
        Logger::log("Enter: function NetworkLinks::delete_category");
        
        /*  Check whether link category exists or not */
        
        $condition = array('category_id'=>$this->category_id, 'user_id'=> $this->user_id, 'is_active'=> $this->is_active);
        $data_array = $this->load_category ($condition);
        
        if(count($data_array) > 0) {                          // Category exists for user
            $condition = array('category_id'=>$this->category_id, 'is_active'=> $this->is_active);
            $data_array = $this->load_link ($condition);
            
            if(count($data_array) > 0) {      // Active Links still exists for category.
                Logger::log("Throwing exception LINKS_EXISTS_UNDER_CATEGORY | Links exists for category you are trying to delete. Please delete the links for category before deleting the category.", LOGGER_ERROR);
                throw new PAException(LINKS_EXISTS_UNDER_CATEGORY, "Links exists for category you are trying to delete. Please delete the links for category before deleting the category.");
                
            } else {
                $sql = "UPDATE {network_linkcategories} SET is_active = ?, changed = ? WHERE category_id = ?";                  
                $data = array(INACTIVE, $this->changed, $this->category_id);
                $res = Dal::query ($sql, $data);
            }
            
        } else {
            Logger::log("Throwing exception LINK_CATEGORY_DOES_NOT_EXISTS | Link category you are trying to delete does not exists.", LOGGER_ERROR);
            throw new PAException(LINK_CATEGORY_DOES_NOT_EXISTS, "Link category you are trying to delete does not exists.");
        }
        
        $condition = array('category_id'=>$this->category_id);
        $data_array = $this->load_link ($condition);
        
        Logger::log("Exit: function NetworkLinks::delete_category");
        return TRUE;
    }
    
    /**
    *   Method to delete the links.
    *   @param no parameters
    *   @access public
    */
    
    public function delete_link () {
        Logger::log("Enter: function NetworkLinks::delete_link");
        
        $condition = array('link_id'=> $this->link_id, 'is_active'=> $this->is_active);
        $data_array = $this->load_link ($condition);
        
        if(count($data_array) > 0) {
            if(is_int($this->link_id)) {
                $sql = "UPDATE {network_links} SET is_active = ?, changed = ? WHERE link_id = ?";
                $data = array(INACTIVE, $this->changed, $this->link_id);
                
            } else {
                $sql = "UPDATE {network_links} SET is_active = ?, changed = ? WHERE link_id IN (".implode(',', $this->link_id).")";
                $data = array(INACTIVE, $this->changed);
            }             
            
            $res = Dal::query ($sql, $data);
            
        } else {
            Logger::log("Throwing exception LINK_DOES_NOT_EXISTS | Link you are trying to delete does not exists.", LOGGER_ERROR);
            throw new PAException(LINK_DOES_NOT_EXISTS, "Link you are trying to delete does not exists.");
        }
        
        
        Logger::log("Exit: function NetworkLinks::delete_link");
        return TRUE;
    }
    
      
    /**
    *   Method to load a links or links.
    *   @param $condition_array associative array of the class variables with their values.
    *   @access public
    */
    
    public function network_owner_link ($condition = NULL, $limit = NULL) {
        Logger::log("Enter: function NetworkLinks::network_owner_link");
        
        $sql = "SELECT L.* FROM {network_links} AS L INNER JOIN {network_linkcategories} AS LC ON L.category_id = LC.category_id";
        if(count($condition) > 0) {              
            foreach ($condition as $key => $value) {
                $sql .= " AND L.$key = ?"; 
                $data[] = $value;   
            }             
        }
        $sql .= " ORDER BY L.created";
        if(!empty($limit)) {
          $sql .= " LIMIT $limit";
        }
        $res = Dal::query($sql, $data);
        $return = array(); 
        if ($res->numRows() > 0) { 
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $return[] = $row;                  
            }
        }
        Logger::log("Exit: function NetworkLinks::load_link");
        return $return;
    }
    
    
    
    
}
?>