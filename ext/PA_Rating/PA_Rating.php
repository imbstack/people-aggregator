<?php
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";


class PA_Rating extends Rating {
  
  /**
  * Generic function to fetch the data from rating table on the basis of key value pairs of field and value.
  */
  public static function get($params=NULL) {
    Logger::log("Enter: PA_Rating::get()");
    
    $sql = 'SELECT * FROM {rating} WHERE 1';
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
        array_push($return, $row);
      }
    }
    
    Logger::log("Exit: PA_Rating::get()");
    return $return;  
  }
  
}
?>