<?php
require_once dirname(__FILE__).'/../../config.inc';
require_once "db/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Network/Network.php";
require_once "api/Logger/Logger.php";
require_once "ext/PingClient/PingClient.php";
require_once "api/api_constants.php";

/**
* Class Testimonials represents Testimonials of the user
* @package Testimonials
* @author Tekriti Software
*/


class Testimonials {
  
 /**
  * Testimonial id of testimonial
  * @var interger
  */
  
  public $testimonial_id;
  
 /**
  * Sender id of Testimonial
  *
  * @var interger
  */
  
  public $sender_id;
  
 /**
  * Recipient id of testimonial 
  *
  * @var interger
  */
  
  public $recipient_id;
  
 /**
  * @var bool
  */
  
  public $is_active;
  
  /**
   * User information modification date/time.
   *
   * @var unix-timestamp
   * @access public
   */
  
  public $created;
  
  /**
   * User information modification date/time.
   *
   * @var unix-timestamp
   * @access public
   */
  
  public $changed;
  
 /**
  * Body of testimonial
  *
  * @var string
  */
  
  public $body;
  
 /**
   function Save()
   Required parameters :- Sender id, recipient id and body 
   @return testimonial id if data is successfully saved.
  */
  
  public function save() {
    Logger::log("Enter: function Testimonials::save");
    
    if (empty($this->sender_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: sender id is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'sender id is missing.');
    }
    
    if (empty($this->recipient_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: recipient id is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'recipient id is missing.');
    }
    
    if (empty($this->body)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: body of testimonial is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Body of testimonial can\'t be empty.');
    }
    
    if($this->sender_id == $this->recipient_id) {
      Logger::log(" Throwing exception INVALID_TESTIMONIAL | Message: sender id and recipient id is same", LOGGER_ERROR);
      throw new PAException(INVALID_ARGUMENTS, 'you can\'t write testimonial for your self');
    }
    
    if (!User::user_exist((int)$this->recipient_id)) {
      Logger::log(" Throwing exception USER_NOT_FOUND | Message: User does not exist", LOGGER_ERROR);
      throw new PAException(USER_NOT_FOUND , 'User does not exist.');
    }
    
    $this->status = PENDING;
    $this->is_active = ACTIVE;
    $this->created = time();
    $this->changed = time();
    
    $sql = "INSERT INTO testimonials 
      (sender_id, recipient_id, body, status, is_active, created, changed) 
      VALUES (?, ?, ?, ?, ?, ?, ?)";

    $data = array($this->sender_id, $this->recipient_id, $this->body, $this->status, $this->is_active, $this->created, $this->changed);
 
    Dal::query($sql, $data);
    $this->testimonial_id = Dal::insert_id();
    
    Logger::log("Exit: function Testimonials::save");
    return $this->testimonial_id;
  }

  //SLOW: should do a query with COUNT(*) rather than retrieving all testimonials.
  public static function count_testimonials($user_id, $status) {
    return Dal::query_first("SELECT COUNT(*) FROM {testimonials} WHERE is_active=1 AND recipient_id=? AND status=? ", array($user_id, $status));
  }

 /**
   function change_status()
   Required parameters :- testimonial_id, status 
   @return testimonial id if data is successfully updated.
  */
      
  public function change_status() {
    Logger::log("Enter: function Testimonials::change_status");
    
    if (empty($this->testimonial_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: testimonial id is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'testimonial id is missing.');
    }
    
    if (empty($this->status)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: status is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'status is missing.');
    }
    
    if (!in_array($this->status, array(APPROVED, DENIED))) {
      Logger::log(" Throwing exception INVALID_PARAMETER | Message: invalid status", LOGGER_ERROR);
      throw new PAException(INVALID_PARAMETER, 'invalid status');
    }
    
    if (!$this->is_testimonial_valid($this->testimonial_id)) {
      Logger::log(" Throwing exception INVALID_PARAMETER | Message: invalid testimonial", LOGGER_ERROR);
      throw new PAException(INVALID_PARAMETER, 'invalid testimonial');
    }
    
    $sql = "UPDATE {testimonials} SET "
        . "status = ? "
        . "WHERE testimonial_id = ?";
    
    $data = array(
        $this->status,
        $this->testimonial_id);
    
    Dal::query($sql, $data);
    
    Logger::log("Exit: function Testimonials::change_status");
    return  $this->testimonials_id;
  }
  
 /**
   function is_testimonial_valid()
   Required parameters :- testimonial_id
   @return true if testimonial exists else false 
  */
      
  public function is_testimonial_valid($testimonial_id) {
    return is_numeric($testimonial_id);
  }
  
  
  /**
   function get()
   Required parameters :- testimonial_id
   @return all the associated value for this method
  */
  
  public function get() {
    Logger::log("Enter: function Testimonials::get");
    
    if (empty($this->testimonial_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: testimonial id is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'testimonial id is missing.');
    }  
    
    if (!$this->is_testimonial_valid($this->testimonial_id)) {
      Logger::log(" Throwing exception INVALID_PARAMETER | Message: invalid testimonial id", LOGGER_ERROR);
      throw new PAException(INVALID_PARAMETER, 'invalid testimonial id ');
    }
    
    $sql = "SELECT testimonial_id, sender_id, recipient_id, body, created, changed, status
            FROM {testimonials} 
            WHERE testimonial_id = ? AND is_active = ?";
    $data = array($this->testimonial_id, ACTIVE);
    $res = Dal::query($sql, $data);
    
    $result = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row;
      }
    }
    
    Logger::log("Enter: function Testimonials::get");
    return $result;
    
  }
  
  
  /**
   function get_multiple_testimonials()
   Required parameters :- testimonial_id, sender_id or recipient_id
   @return all the associated value for this method
  */  
  
  public function get_multiple_testimonials($cnt_paging=FALSE, $show='ALL', $page=1, $sort_by='T.created', $direction='DESC') {
    Logger::log("Enter: function Testimonials::get_multiple_testimonials");
    
    $counter = 0;//finding number of parameters set for the function
    $condition = array($this->testimonial_id, $this->sender_id, $this->recipient_id);
    // avoiding multiple arguments, only 1 of these can be set before calling function 
    
    $cnt = count($condition);
    //error checking only 1 should be selected
    //also checks to see if the testimonial id is valid number
    for ($j = 0; $j < $cnt; $j++) {
      if (!empty($condition[$j])) {
        ++$counter;
        if (!$this->is_testimonial_valid($condition[$j])) {
          Logger::log(" Throwing exception INVALID_PARAMETER | Message: insufficient argument", LOGGER_ERROR);
          throw new PAException(INVALID_PARAMETER, 'insufficient argument '); 
        }  
      }
    }
    //it should be set for only one id for $condtion array elements
    if ($counter != 1) {
      Logger::log(" Throwing exception INVALID_PARAMETER | Message: insufficient argument", LOGGER_ERROR);
      throw new PAException(INVALID_PARAMETER, 'insufficient argument ');
    }
    
    //generating dynamic sql according to parameter set
    if (!empty($this->testimonial_id)) {
      $field_name = 'T.testimonial_id';
      $value = $this->testimonial_id;
    }
    
    if (!empty($this->sender_id)) {
      $field_name = 'T.sender_id';
      $value = $this->sender_id;
    }
    
    if (!empty($this->recipient_id)) {
      $field_name = 'T.recipient_id';
      $value = $this->recipient_id;
    }
    
    $order_by = $sort_by.' '.$direction;
    if ( $show == 'ALL' || $cnt_paging == TRUE) {
      $limit = '';
    }
    else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }
    
    /**
      Here we retrive the user_name of sender because the recipient user Either loged in user Or Page user .. so we take the inner join on the sender_id .. ;)
    */ 
    
    $sql = "SELECT T.testimonial_id, T.sender_id, T.recipient_id, T.body, T.created, T.changed, U.login_name as username, U.picture as user_pic, U.first_name as user_fname, U.last_name as user_lname, U.email as user_email 
            FROM {testimonials} as T
            INNER JOIN {users} as U
            ON T.sender_id = U.user_id
            WHERE $field_name = ? AND T.is_active = ? AND U.is_active = 1 ";
    $data = array($value, ACTIVE);
    
    if (!empty($this->status)) {
      
      switch ($this->status) {
        case PENDING:
          $sql .= ' AND status = ?';
          $data = array($value, ACTIVE, PENDING);
        break;
        
        case APPROVED:
          $sql .= ' AND status = ?';
          $data = array($value, ACTIVE, APPROVED);
        break;
        
        case 'all':
        break;
      }
    }
    else {
      $sql .= ' AND status = ?';
      $data = array($value, ACTIVE, PENDING);
    }
    
    $sql .= " ORDER BY $order_by  $limit";
    
    $res = Dal::query($sql, $data);
    
    if ( $cnt_paging ) {
      Logger::log("Exit: function Testimonials::get_multiple_testimonials");
      return $res->numRows();
    }
    
    $result = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
    }
    
    Logger::log("Exit: function Testimonials::get_multiple_testimonials");
    return $result;
  }
  
  
  /**
   function delete_testimonial()
   Required parameters :- testimonial_id
   @return testimonial id of deleted value
  */
  
  public function delete_testimonial() {
    Logger::log("Enter: function Testimonials::delete_testimonial");
    
    if (empty($this->testimonial_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: testimonial id is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'testimonial id is missing.');
    }  
    
    if (!$this->is_testimonial_valid($this->testimonial_id)) {
      Logger::log(" Throwing exception INVALID_PARAMETER | Message: invalid testimonial id", LOGGER_ERROR);
      throw new PAException(INVALID_PARAMETER, 'invalid testimonial id ');
    }
    
    $sql = "UPDATE {testimonials} SET "
        . "is_active = ? "
        . "WHERE testimonial_id = ?";
    
    $data = array(DELETED, 
                  $this->testimonial_id
                  );
    
    Dal::query($sql, $data);
    
    Logger::log("Exit: function Testimonials::delete_testimonial");
    return  $this->testimonials_id;
    
  }
  
  
  
} ?>