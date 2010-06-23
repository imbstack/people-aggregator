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
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/User/User.php";

/**
* Class FooterLink for managing footer links of application
*
* @package FooterLink
* @author Tekriti Software
*/
class FooterLink {

  /**
  * id of the footer link.
  * @access public
  * @var int
  */
  public $id;

  /**
  * caption for footer link
  * @access public
  * @var string
  */
  public $caption; 

  /**
  * url associated with link
  * @access public
  * @var string
  */
  public $url;
  /**
  * status of link, 1 for enabled, 0 for disabled
  * @access public
  * @var int
  */
  public $is_active;

  /**
  * extra field associated with footer link
  * @access public
  * @var string
  */
  public $extra;
  
  /**
  * The default constructor for FooterLink class.
  */
  public function __construct() {

  }
 
  /**
  * This function saves an entry in footer_links table
  * input type: Values are set for object eg links = new FooterLink; $links->caption= 'sys';
  * return type: id
  */

  public function save() {
      Logger::log("Enter: function FooterLink::save()");
      if (!empty($this->id)) {
        $sql = "UPDATE {footer_links} SET caption = ?, url = ?, is_active = ?, extra = ? WHERE id = ".$this->id;
      } else {
        $sql = "INSERT INTO {footer_links} (caption, url, is_active, extra) VALUES 
        (?, ?, ?, ?) ";
      }
      $data = array($this->caption, $this->url, $this->is_active, $this->extra);
      $res = Dal::query($sql, $data);
      $id = Dal::insert_id();
      Logger::log("Enter: function FooterLink::save");
      return ($id);
  }

  /**
  * This function will retrieve data from footer links table
  * @param $params specify WHERE clause for query e.g.params['id'] = 5, 
  */

  static function get($params = NULL, $cnt=false, $page=1, $show='ALL', $order_by = 'caption ASC') {
    Logger::log("Enter: function FooterLink::get");
      $args = array();
      if (!empty($params['id'])) {
        $sql = " SELECT * FROM {footer_links} WHERE id = ? ";
        $args[] = $params['id'];
      } else {
      $sql = " SELECT * FROM {footer_links} WHERE 1 ";
      if (is_array($params)) {
        foreach ($params as $field_name => $field_value) {
          $sql = $sql .' AND ' . $field_name .' = '.$field_value;
        }
      }
      //paging variables if set
     if (!empty($order_by)) {
       $order_by = ' ORDER BY '.$order_by;
       $sql .= $order_by;
     }
     if ($show == 'ALL' || $cnt == TRUE) {
       $limit = '';
     } else {
       $start = ($page -1)* $show;
       $limit = ' LIMIT '.$start.','.$show;
     }
     $sql .= $limit;
    }
    if ($res = Dal::query($sql, $args)) {
    } else {   
      return FALSE;
    } 
    if ($cnt == TRUE) {
      // here we just want to know total footer links
      Logger::log("[ Exit: function FooterLink::get and returning count] \n");
      return $res->numRows();
    }
    $links = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $links[] = $row;
    }
    Logger::log("Exit: function FooterLink::get");
    return $links;
  }
  
  /**
  * This function delete an entry from footer_links table
  * @param $id specifies the footer link to be deleted
  */

  static function delete($id) {
    Logger::log("Enter: function FooterLink::delete");
    $sql = " DELETE FROM {footer_links} WHERE id = ? ";
    $data = array($id);
    $res = Dal::query($sql, $data);
    Logger::log("Exit: function FooterLink::delete");
    return;
  }
/**
  *This function updates an entry in footer_links table
  *@param $update_fields is an array that contains fields_name and respective value
  *@param $condition is an array that forms the WHERE clause for update query
  */

  static function update($update_fields, $condition) {
    Logger::log("Enter: function FooterLink::update");
    if (!empty($update_fields)) { 
      $sql = 'UPDATE {footer_links} SET ';     
      foreach ($update_fields as $key => $value) {
        $sql .= $key .' = '.$value .', ';
      }
      $sql = substr($sql, 0, -2);  
      if (!empty($condition)) {
        $sql .= ' WHERE 1';
        foreach ($condition as $k => $v) { 
          $sql .= ' AND '.$k.' = '.$v;
        }
      }
    }
    $res = Dal::query($sql);
    Logger::log("Exit: function FooterLink::update");
  }
}
?>