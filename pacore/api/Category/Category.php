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
include_once dirname(__FILE__)."/../../config.inc";
require_once "db/Dal/Dal.php";
//require_once "api/User/User.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/MessageBoard/MessageBoard.php";
require_once "api/Group/Group.php";
define('DEFAULT_PARENT_ID','0');//root id
/**
  * constant for Category class
  */

/**
* Class Category deals with heirarichal category structure
*
* @package Category
* @author Tekriti Software
*/
class Category {

  /**
   * category_id uniquely defines the category.
   * @access public
   * @var int
   */
  public $category_id;
  
  /**
   * parent_id parent of the category
   * @access private
   * @var string
   */
  public $parent_id;
  
  /**
   * name is name of the category.
   * @access public
   * @var string
   */
  public $name;
  
  /**
   * description is description of the category.
   * @access public
   * @var string
   */
  public $description;
  
  /**
   * position is relative position of the category in db
   * @access public
   * @var string
   */
  public $position;
  
  /**
   * type tell to which type category belongs
   * @access public
   * @var string
   */
  public $type;
  
  /**
   * date of creating category
   * @access private
   * @var datetime
   */
  private $created;
  
  /**
   * date of editing category
   * @access private
   * @var datetime
   */
  private $changed; 
  /**
   * Constructor
   */
   
  function __construct() {
    $this->type = 'Default';
  }
   /**
   * saves category data 
   * @access public
   * if category_id is set it updates else inserts
   * return null
  */
  
  public function save() {
    Logger::log("Enter: function Category::save");
    //check for required parameters
    if ( !$this->name ) {
      Logger::log("Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Required parameters missing", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required parameters missing");
    }// ..eof check
    if ( $this->category_id ) {
      //update
      // TODO to move category's position
      $this->changed = time();
      $sql = "UPDATE {categories} SET name = ?, description = ?, type = ?, changed = ? WHERE category_id = ? AND is_active = 1";
      $data = array($this->name, $this->description, $this->type, $this->changed, $this->category_id);
      $res = Dal::query($sql, $data);
    }//.. eof update if 
    else {
    //insert
      $position  = Category::get_position($this->parent_id);
      $this->created = time();
      $this->changed = $this->created;
      $sql = 'INSERT into {categories} ( name, description, type, is_active, created, changed ) values ( ?, ?, ?, ?, ?, ? )';
      $data = array($this->name, $this->description, $this->type, ACTIVE, $this->created, $this->changed);
      $res = Dal::query($sql, $data);
      $insert_id = Dal::insert_id();
      $position .= $insert_id.">";
      $sql = " UPDATE {categories} SET position = ? WHERE category_id = ? ";
      $data = array($position, $insert_id);
      $res = Dal::query($sql, $data);
    }//..eof insert else
    
    Logger::log("Exit: function Category::save");
    return;
  }
  
  /**
   * fetches relative position of category from database 
   * @access public static
   * return position like 1>2>3> etc
  */
  static function get_position( $category_id ) {
    Logger::log("Enter: function Category::get_position");
    
    if($category_id == 0) {
      $position = "";
      Logger::log("Exit: function Category::get_position");
      return $position;  
    }
    $sql = " SELECT position from {categories} WHERE category_id = ? ";
    $data = array( $category_id );
    $res = Dal::query($sql, $data);
    if ( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
      $position = $row->position;
      Logger::log("Exit: function Category::get_position");
      
      return $position;  
    }
  }
  
  /**
   * loads category data 
   * @access public static
   * return null
  */
  public function load() {
    Logger::log("Enter: function Category::load");
    if ( $this->category_id ) {
       
      $sql = "SELECT * from {categories} WHERE category_id = ?";
      $data = array($this->category_id);
      $res = Dal::query($sql, $data);
      if ($res->numRows() > 0) {
        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $this->position    = $row->position;
        $this->name        = $row->name;
        $this->description = $row->description;
        $this->type        = $row->type;
      } else {
        Logger::log("Throwing exception REQUIRED_PARAMETERS_MISSING | Message: id not set in load", LOGGER_ERROR);
        throw new PAException(CATEGORY_DOES_NOT_EXIST, "Category does not exists");
      }
    } else {
      Logger::log("Throwing exception REQUIRED_PARAMETERS_MISSING | Message: id not set in load", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Id not set");
    }
  }
  
  /**
   * sets category_id
   * @access public static
   * return null
  */
  public function set_category_id ($category_id) {
    $this->category_id = $category_id;
  }
  
  /**
   * sets parent_id
   * @access public static
   * return null
  */
  public function set_parent_id ($parent_id) {
    $this->parent_id = $parent_id;
  }
  
  /**
   * deletes category
   * @access public static
   * @param int category_id ID of category
   * return null
  */
  static function delete($category_id) {
    Logger::log("Enter: function Category::delete");
    
    $position = Category::get_position($category_id);
    $sql = "DELETE FROM {categories} WHERE position LIKE '".$position."%'";
    Logger::log("Exit: function Category::delete");
     $res = Dal::query($sql, $data);
    Logger::log("Exit: function Category::delete");
    
    return;
  }
  
   /**
   * builds list of root level categories
   * @access public static
   * array of objects of all categories
  */  
  static function build_root_list( $attached_type=NULL, $type='Default') {
  //FIX ME have to write code for each $attached_type
  
    Logger::log("Enter: function Category::build_root_list");
    $cat_obj = new category_item_list();
    $sql = " SELECT * FROM {categories} WHERE position RLIKE '^[0-9]+>$' AND type = ? AND is_active =1 ORDER BY name ";
    $res = Dal::query($sql, array($type));
    if ($res->numRows() > 0) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        if ($attached_type == 'MessageBoard') {
          $total_threads = MessageBoard::get_threads_count_of_category($row->category_id);
        }
        if ($attached_type == 'Default') {
          $total_threads = Group::get_threads_count_of_category($row->category_id);
        }
        if ($attached_type == 'Network') {
          $total_threads = Network::get_threads_count_of_category($row->category_id);
        }
        $category_item = new category_item($row->category_id, $row->name, $row->description,@$total_threads,$row->position, $row->type);
        $cat_obj->add_cat_item($category_item);
      }
    } 
    Logger::log("Exit: function Category::build_root_list");
    $cat_list=$cat_obj->get_cat_list();
    return $cat_list;
  }
  
  /**
   * builds list of children of a given category
   * @access public static
   * @param int parent_id ID of category
   * array of objects of all children
  */
  
  static function build_children_list($parent_id, $attached_type=NULL) {
    Logger::log("Enter: function Category::build_children_list");
    $cat_obj=new category_item_list();
    $position = Category::get_position($parent_id);
    if ( $position ) {
      $sql = "SELECT * FROM {categories} WHERE position RLIKE  '^".$position."[0-9]+>$'";
      $res = Dal::query($sql);
      $total_threads = NULL;
      if ($res->numRows() > 0) {
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
//         $total_threads = MessageBoard::get_threads_count_of_category($row->category_id);
          if ( $attached_type == 'MessageBoard' ) {
            $total_threads = MessageBoard::get_threads_count_of_category($row->category_id);
          }
          if ( $attached_type == 'Default' ) {
            $total_threads = Group::get_threads_count_of_category($row->category_id);
          }
          if ( $attached_type == 'Network' ) {
            $total_threads = Network::get_threads_count_of_category($row->category_id);
          }
         
          $category_item = new category_item($row->category_id, $row->name, $row->description, $total_threads,$row->position, $row->type);
          $cat_obj->add_cat_item($category_item);
        }
      } 
      
      $cat_list=$cat_obj->get_cat_list();
    } else {
      $cat_list='';
    }
    Logger::log("Exit: function Category::build_children_list");
    return $cat_list;
  } 
  
  
   /**
   * finds parent of given category
   * @access public static
   * @param int category_id ID of category
   * parent_id or false if root level category
  */
  static function find_parent($category_id) {
    $position         = Category::get_position($category_id);
    $position_slices  = explode(">",$position);
    $key              = count($position_slices)-3;
    $parent           = @$position_slices[$key];
    if ($parent=='') {
      return false;
    } else {
      return $parent;
    }
  }
  
  /**
   * Builds a category tree for all cateogories
   * @access public static
   * return array of all categories
  */
  
  static public function build_all_category_list($position = '', $spacing = '',  $category_tree_array = '', $type = 'Default' , $exclude = 1) 
  {
    if (!is_array($category_tree_array)) 
    {
      $category_tree_array = array();
    }
    if ( (sizeof($category_tree_array) < 1) && ($exclude != 0) ) 
    {
      $category_tree_array[] = array('category_id' => '0', 'name' => 'Select Category');
    }
    $sql = "SELECT * FROM {categories} WHERE position RLIKE  '^".$position."[0-9]+>$' AND type = ?";
    $res = Dal::query($sql, array($type));
    if ($res->numRows() > 0) 
    {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) 
      {
        $category_tree_array[] = array('category_id' => $row->category_id, 'name' => $spacing . $row->name);
        $category_tree_array = Category::build_all_category_list($row->position, $spacing . '&nbsp;&nbsp;&nbsp;',  $category_tree_array, $type);
      }
    }
    return $category_tree_array;
  }
   
 
 }//class

/**
* Class category_item deals internally with category objects
*
* @package category_item
* @author Tekriti Software
*/

class category_item {
  /**
   * category_id holds category id
   * @access private
   * @var integer
   */
  public $category_id;
  /**
   * name holds category name
   * @access private
   * @var string
   */
  public $name;
  /**
   * description holds category description
   * @access private
   * @var string
   */
  public $description;
  /**
   * total_threads threads in the category
   * @access private
   * @var integer
   */
   public $total_threads;
   
   /**
   * relative position of category data field
   * @access public
   * @var string
   */
   public $position;
  
  /**
   * type tell to which type category belongs
   * @access public
   * @var string
   */
  public $type;
  /**
   * category_item is used to hold all information of category
   * @access private
   * @var object
   */
  public function category_item($category_id, $name, $description, $total_threads,$position, $type) {
    $this->category_id      =      $category_id;
    $this->name             =      $name;
    $this->description      =      $description;
    $this->total_threads    =      intval($total_threads);
    $this->position         =      $position;
    $this->type             =      $type;
  }
}
/**
* Class category_item_list deals internally with list of category objects
*
* @package category_item_list
* @author Tekriti Software
*/
class category_item_list {
  /**
   * category_list holds objects of category
   * @access private
   * @var array
   */
  public $category_list    = array();
  /**
   * association holds parent child relationship of category
   * @access private
   * @var array
   */
  public $association = array();
  
  public $cat_list;
  /**
   * add_cat_item adds category object to list
   * @param object
  */
  public function add_cat_item($category_item) {
    $this->cat_list[] = $category_item;
  } 
  /**
   * get_cat_list retreives category object list
   * @return list of objects
  */
  public function get_cat_list() {
    return $this->cat_list;
  }
  /**
   * add_association adds relationship
   * @param $category_id,$parent_id
  */
  public function add_association($category_id,$parent_id) {
    if ( !$this->association[$category_id] ) {
      $this->association[$category_id] = $parent_id;
    }
  }
  /**
   * get_association_list retreives relationship array
   * @return list of associations
  */
  public function get_association_list() {
    return $this->association;
  }
}
?>