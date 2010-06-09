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
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";

/**
* This class will be used for rating an entity in PeopleAggregator.
* This entity can be a content or a collection or a user or a tag etc.
* This class will also help to rate a individual attributes of the entity.
* For example, a user can be rated on various parameters which 
* constitutes its over all rating
* User will act as the entity that will do the rating, i.e a user can rate a content, 
* collection, another user, comment etc.
* 
* @author Tekriti Software (http://www.tekritisoftware.com)
*/

class Rating {
  
  /**
  * This variable will act as the unique index for every rating made
  * @var int
  *@access private
  */
  private $index_id;
  
  /**
  * Rating type defines the type entity that we will rate. 
  * For example, If we are rating it will be content, for user it will be user
  * @var enum
  *@access private
  */
  private $rating_type;
  
  /**
  * Type id defines the actual id of the entity defined by the rating_type
  * For example if we are rating a user then rating_type=> user and rating_id=>user id of the user that is going to get rated
  * @var int
  *@access private
  */
  private $type_id;
  
  /**
  * Attribute id will come into play when we have to rate a entity of  some rating_type having a type_id on some parameters
  * For example if we have to rate a entity say content, on the basis of such paramters quality of content, originality 
  * of content, vocabulary used then this attribute id will come into display. One can treat these parameters as attributes and
  * can rate the entity on the basis of these parameters
  * @var int
  *@access private
  */
  private $attribute_id = -1;
  
  /**
  * Rating is the actual rating that is being given to the entity.
  * For example, rating can ve 5 for a content on 10 point scale for user and can be 2 on 5 point scale for a content
  * @var int
  *@access private
  */
  private $rating;
  
  /**
  * Max rating has the maximum possible value of rating when we are going to rate an entity.
  * For example Max rating can be 100 for content or may be 10 for the collection
  * @var int
  *@access private
  */
  private $max_rating;
  
  /**
  * User id is the user who is going to rate an enitity. For most of the cases it will be the user_id of the 
  * logged in user who is participating in rating some entity.
  * @var int
  *@access private
  */
  private $user_id;
  
  /**
  * DEFINING THE SETTER METHODS FOR ALL THE CLASS VARIABLES
  * Defining setter method for index_id
  */
  public function set_index_id($index_id) {
    Logger::log("Enter: Rating::set_index_id()");   
    if (!is_int($index_id) || $index_id < 1) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Rating::index_id should have a postive value');
    }
    $this->index_id = $index_id;
    Logger::log("Exit: Rating::set_index_id()");
  }
  
  /**
  * Defining setter method for rating_type
  */
  public function set_rating_type($rating_type) {
    Logger::log("Enter: Rating::set_rating_type()");   
    $rating_type = trim($rating_type);
    if (empty($rating_type)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Rating::rating_type can not have empty value');
    }
    $sql = 'SHOW COLUMNS FROM {rating}';
    $res = Dal::query($sql);
    while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      if(ereg(('enum'), $row->Type)) {
        eval(ereg_replace('enum', '$valid_rating_types = array', $row->Type).';');
      }
    }
    if (!in_array($rating_type, $valid_rating_types)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Rating::rating_type='.$rating_type.' is not a valid rating_type');
    }
    $this->rating_type = $rating_type;
    Logger::log("Exit: Rating::set_rating_type()");
  }
  
  /**  
  * Defining setter method for type_id
  */
  public function set_type_id($type_id) {
    Logger::log("Enter: Rating::set_type_id()");   
    if (!is_int($type_id) || $type_id < 1) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Rating::type_id should have a postive value');
    }
    $this->type_id = $type_id;
    Logger::log("Exit: Rating::set_type_id()");
  }
  
  /**
  * Defining setter method for attribute_id
  */
  public function set_attribute_id($attribute_id) {
    Logger::log("Enter: Rating::set_attribute_id()");   
    if ($attribute_id != -1 && $attribute_id < 1) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Rating::attribute_id can be have positive value or it can be -1');
    }
    $this->attribute_id = $attribute_id;
    Logger::log("Exit: Rating::set_attribute_id()");
  }
  
   /**  
  * Defining setter method for rating
  */
  public function set_rating($rating) {
    Logger::log("Enter: Rating::set_rating()");   
    if (!is_int($rating)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Rating::rating should have a postive value');
    }
    $this->rating = $rating;
    Logger::log("Exit: Rating::set_rating()");
  }
  
  /**  
  * Defining setter method for max_rating
  */
  public function set_max_rating($max_rating) {
    Logger::log("Enter: Rating::set_max_rating()");   
    if (!is_int($max_rating) || $max_rating < 1) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Rating::max_rating should have a postive value');
    }
    $this->max_rating = $max_rating;
    Logger::log("Exit: Rating::set_max_rating()");
  }
  
  /**  
  * Defining setter method for user_id
  */
  public function set_user_id($user_id) {
    Logger::log("Enter: Rating::set_user_id()");   
    if (!is_int($user_id) || $user_id < 1) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Rating::user_id should have a postive value');
    }
    $this->user_id = $user_id;
    Logger::log("Exit: Rating::set_user_id()");
  }
  
  
  
  /**
  * Default constructor for the rating class
  */
  function __construct() {
    Logger::log("Enter: Rating::__construct()");
    Logger::log("Exit: Rating::__construct()");
  }
  
  /**
  * Function to rate an entity. If their is no existing record for the entity rating a new record will be inserted,
  * otherwise the existing entry will be updated  
  */
  public function rate() {
    Logger::log("Enter: Rating::rate()");   
    
    try {
      $sql = 'SELECT index_id FROM {rating} WHERE rating_type = ? AND type_id = ? AND attribute_id = ? AND user_id = ?';
      $data = array($this->rating_type, $this->type_id, $this->attribute_id, $this->user_id);
      $res = Dal::query($sql, $data);
      
      if ($res->numRows()) {
        //update the existing rating
        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $this->index_id = $row->index_id;
        $sql = 'UPDATE {rating} SET rating = ?, max_rating = ? WHERE index_id = ?';
        $data = array($this->rating, $this->max_rating, $this->index_id);
        Dal::query($sql, $data);
      } else {
        //creating a new rating
        $sql = 'INSERT INTO {rating} (rating_type, type_id, attribute_id, rating, max_rating, user_id) values (?, ?, ?, ?, ?, ?)';
        $data = array($this->rating_type, $this->type_id, $this->attribute_id, $this->rating, $this->max_rating, $this->user_id);
        Dal::query($sql, $data);
      }
    } catch (PAException $e) {
      throw $e;
    }
    
    Logger::log("Exit: Rating::rate()");
  }
  
  /**
  * Function to get the rating of a particular entity made by the user on the basis of its rating_type, type_id and attribute_id  
  */
  public function get_rating() {
    Logger::log("Enter: Rating::get_rating()");
    $sql = 'SELECT count(*) AS ratings, SUM(rating) AS total_rating, SUM(max_rating) AS total_max_rating, attribute_id FROM {rating} WHERE rating_type = ? AND type_id = ? GROUP BY attribute_id';
    $data = array($this->rating_type, $this->type_id);
    $res = Dal::query($sql, $data);
    
    $return = null;
    if ($res->numRows()) {      
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $return[] = $row;
      }
    } else {
      $return = false;
    }
    Logger::log("Exit: Rating::get_rating()");
    return $return;
  }
  /**
   * Generic function get.
   **/
  public static function get($conditions = NULL, $params = NULL){
    Logger::log("Enter: Rating::get()");
    $sql = " SELECT * FROM {rating} WHERE 1 ";
    if ($conditions) {
      $sql = $sql . ' AND ' .$conditions;
    }
    $data = array();
    if (!empty($params)) {
      foreach ($params as $key => $value) {
        $sql .= ' AND '.$key.' = ?';
        array_push($data, $value);
      }
    }
    $res = Dal::query($sql, $data);
    $return = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $return[] = $row;
      }
    }
    Logger::log("Exit: Rating::get()");
    return $return;
  }
 
}
?>