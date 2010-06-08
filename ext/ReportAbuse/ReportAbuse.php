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
require_once dirname(__FILE__).'/../../config.inc';
require_once "db/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Network/Network.php";
require_once "api/Logger/Logger.php";
require_once "ext/PingClient/PingClient.php";
require_once "api/api_constants.php";

/**
* Class RepostAbuse represents Abuse report on the content, comment, ablum  
* @package RepostAbuse
* @author Tekriti Software
*/  
  

class ReportAbuse {
 
 /**
  * Report id 
  * @var interger
  */
  
  public $report_id;
  
 /**
  * Parent type .. such as 'content', 'group', 'album' , 'comment'
  * @var String
  */
  
  public $parent_type;

 /**
  * Parent id .. 
  * @var interger
  */
  
  public $parent_id;    

 /**
  * Reporter id .. 
  * @var interger
  */
  
  public $reporter_id;     
  
 /**
  * Body of report
  * @var String
  */
  
  public $body;
  
  /**
   function Save()
   Required parameters :- Parent type, Parent id, Body of report and Reporter id
   @return Report id if data is successfully saved.
  */
  
  public function save() {
    Logger::log("Enter: function ReportAbuse::save");
        
    if (empty($this->parent_type)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Parent type is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'parent type is Empty.');
    }
    
    if (empty($this->parent_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Parent id is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Parent id is missing.');
    }
    
    if (empty($this->body)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: body of Report abuse is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Body of Report can\'t be empty.');
    }
    
   if (empty($this->reporter_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Reporter id is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Log into People Aggregator before sending report');
    }
    
    if (!User::user_exist((int)$this->reporter_id)) {
      Logger::log(" Throwing exception USER_NOT_FOUND | Message: User does not exist", LOGGER_ERROR);
      throw new PAException(USER_NOT_FOUND , 'User does not exist.');
    }
    
    if (!($this->is_valid_type($this->parent_type))) {
      Logger::log(" Throwing exception INVALID_ARGUMENTS | Message: Not a valid parent type", LOGGER_ERROR);
      throw new PAException(INVALID_ARGUMENTS, 'parent type is invalid');
    }
    
    $sql = "INSERT INTO {report_abuse}
      (parent_type, parent_id, reporter_id, body, created)
      VALUES (?, ?, ?, ?, ?)";

    $this->created = time();
      
    $data = array($this->parent_type, $this->parent_id, $this->reporter_id, $this->body, $this->created);
 
    Dal::query($sql, $data);
    $this->report_id = Dal::insert_id();
    
    Logger::log("Exit: function ReportAbuse::save");
    return $this->report_id;
  
  }
  
  public function get() {
    Logger::log("Enter: function ReportAbuse::get");
      
    if (empty($this->report_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: report id is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'report id is missing.');
    }  
    
    if (!$this->is_valid_report($this->report_id)) {
      Logger::log(" Throwing exception INVALID_PARAMETER | Message: invalid report id", LOGGER_ERROR);
      throw new PAException(INVALID_PARAMETER, 'invalid report id ');
    }
    
    $sql = "SELECT report_id, parent_type, parent_id, body, created, reporter_id
            FROM {report_abuse} 
            WHERE report_id = ? ";
    $data = array($this->report_id);
    $res = Dal::query($sql, $data);
    
    $result = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row;
      }
    }
    
    Logger::log("Exit: function ReportAbuse::get");
    return $result;
  }
  
  
  public function get_multiples() {
    Logger::log("Enter: function ReportAbuse::get_multiples");
    
    $temp_array = array($this->report_id, $this->parent_type, $this->parent_id);
    $empty_count = count($temp_array);
    
    $j = 0;
    for ($i = 0; $i < $empty_count; $i++) {
      if (!empty($temp_array[$i])) {
        ++$j;
      }
    }
    
    // None variable are set for this function 
    if ($j < 1) {
      Logger::log(" Throwing exception INVALID_PARAMETER | Message: insufficient argument", LOGGER_ERROR);
      throw new PAException(INVALID_PARAMETER, 'insufficient argument '); 
    }
    
    if (!empty($this->parent_type) || !empty($this->parent_id)) {
    
      if (!$this->is_valid_type($this->parent_type)) {
        Logger::log(" Throwing exception INVALID_ARGUMENTS | Message: Not a valid parent type", LOGGER_ERROR);
        throw new PAException(INVALID_ARGUMENTS, 'parent type is invalid');
      }
      
      if (empty($this->parent_id)) {
        Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Parent id is empty", LOGGER_ERROR);
        throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Parent id is missing.');
      }
      
      if (empty($this->parent_type)) {
        Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Parent type is empty", LOGGER_ERROR);
        throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Parent type is missing.');
      }
      $field = 'parent_type = ? AND parent_id = ?';
      $data = array($this->parent_type, $this->parent_id);
    }
    
    if (!empty($this->report_id)) {
      $field = 'report_id = ?';
      $data = array($this->report_id);
    }
    
    $sql = "SELECT report_id, parent_type, parent_id, body, created, reporter_id
            FROM {report_abuse} 
            WHERE $field";
    
    $res = Dal::query($sql, $data);
    
    $result = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
    }
    
    Logger::log("Enter: function ReportAbuse::get_multiples");
    return $result;
  }
  
 
  /**
   function is_valid_report()
   Required parameters :- $report_id
   @return true if Report exists else false 
  */
      
  public function is_valid_report($report_id) {
    return is_numeric($report_id);
  }
  
  /**
   function is_valid_type()
   Required parameters :- Type
   @return true if type exists else false 
  */
      
  public function is_valid_type($type) {
    $type_array = array(TYPE_COMMENT, TYPE_CONTENT);
    return in_array($type, $type_array);
  }
  
  
  
}

?>
