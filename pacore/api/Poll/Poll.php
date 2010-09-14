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
/**
 * Class for creating dealing with Poll.
 *
 * Poll class inherites Content class.
 * @package Poll
 * @author Tekriti Software (http://www.tekritisoftware.com)
 */

require_once "api/Logger/Logger.php";
require_once "api/Content/Content.php";

class Poll extends Content {

 /**
  * @var int ID for content
  * @access public
  */
 public $content_id;
 
 /**
  *@var title for Poll
  *@access public
 */ 
 
 public $title;
 
 /**
  *@var options for storing options of Poll
  *@access public
 */
 public $options;
 
 /**
  *@var user_id for storing user_id
  *@access public
 */
 
 public $user_id; 
 
 /**
  * @var unix-timestamp content creation date/time
 */ 
 public $created;
  
 /**
  * @var unix-timestamp micro-content modification date/time
  * @access public
 */
 public $changed;
 
 /**
  *@var vote to store user vote
  *@access public
 */  
 public $vote;   
 
  /**
   * contructor, creates database handle
   * @access public
  */
  public function __construct() {
    parent::__construct();
    //TODO: move to constants
    $this->type = POLL;
  }
  
  /**
    * Destroys a database connection instances upon deletion on object.
    * @access public
  */
  public function __destruct() {
    parent::__destruct();
  }
  
  /**
  * saves poll to  database.
  * @access public
  */
  public function save_poll() {
    Logger::log("Enter: Poll::save_poll");
    Logger::log("calling: parent::save");
    if($this->content_id) {
      // UPDATE if exists
      $this->changed = time();
      $sql = "UPDATE {polls} SET title = ?, options = ?, changed = ? WHERE content_id = ? ";
      try {
        $data = array($this->title, $this->options,$this->changed,                             $this->content_id);
        $res = Dal::query($sql, $data);
        parent::save();
      } catch (Exception $e) {
         Dal::rollback();
         throw $e;
        }
    } else {
        parent::save();  
        try {
          $this->created = time();
          $this->changed = $this->created;
          $sql = "INSERT INTO {polls} (content_id, title, user_id, group_id, options, created,                changed, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		  $data = array($this->content_id, $this->title, $this->user_id, $this->group_id, $this->options, $this->created,                               $this->changed,$this->is_active);
          $res = Dal::query($sql, $data);
        } catch (Exception $e) {
            Logger::log("Exception occurred inside Poll::save_poll(); rolling back",                    LOGGER_INFO);
            Dal::rollback();
            throw $e;
          }
      }
    Logger::log("Exit: Poll::save_poll()");
  }
  
  /**
  * save vote to poll_vote table
  * @access public
  */
  public function save_vote() {
    Logger::log("Enter: Poll::save_vote");
    
    try {
      $sql = "INSERT INTO {poll_vote} (poll_id, user_id, vote, is_active) VALUES (?, ?, ?,          ?)";
      $data = array($this->poll_id, $this->user_id, $this->vote, $this->is_active);
      $res = Dal::query($sql, $data);
    } catch (Exception $e) {
      Logger::log("Exception occurred inside Poll::save_vote(); rolling back",                    LOGGER_INFO);
      Dal::rollback();
      throw $e;
    }
    
    Logger::log("Exit: Poll::save_vote()");    
  }
  
  /**
  * save the current poll topic to database
  * @access public
  */
  public function save_current(){
    $sql_prev = "UPDATE {polls} set is_active = 1, created = ?, changed = ? WHERE poll_id = ?";
    $changed2 = time() - 25;
    $res = Dal::query($sql_prev, array($this->prev_changed,$changed2,$this->prev_poll_id));
    
    $sql = "UPDATE {polls} set is_active = 1, changed = ? WHERE poll_id = ?";
    $changed1 = time();
    $res1 = Dal::query($sql, array($changed1,$this->poll_id));
  }
  
  /**
  * Loads current poll topic
  *@access public
  */
  public function load_current($group_id=0) {
    $sql = "SELECT * FROM {polls} WHERE is_active = 1 and group_id='".$group_id."' ORDER BY changed DESC LIMIT 0,1";
    $res = Dal::query($sql);
    $data = array();
    if ($res->numRows()) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $data[] = $row;
      }
    }
    return $data;  
  }

  /** 
  *loads previous poll topic
  *@access public
  */
  public function load_prev_polls($group_id = 0) {
    $sql = "SELECT * FROM {polls} WHERE is_active = 1 and group_id='".$group_id."' ORDER BY changed DESC LIMIT 1,18446744073709551615"; // this excludes the first result, but gives all others
    
    $res = Dal::query($sql);
    $data = array();
    if ($res->numRows()) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $data[] = $row;
      }
    }
    return $data;   
  }

  /**
  * Loads poll for database
  * @access public
  */
  public function load_poll($poll_id = 0, $group_id = 0) {
	  Logger::log("Enter: Poll::load_poll()");
    $data = array();
    if ($poll_id == 0) {
      $sql = "SELECT * FROM {polls} WHERE is_active and group_id='".$group_id."' <> 0 ORDER BY changed DESC";
      $res = Dal::query($sql);
      if ($res->numRows()) {
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
					$data[] = $row;
				}
			}
    } else {
        $sql = "SELECT poll_id,content_id, title, user_id, options, created, changed, is_active             FROM {polls} WHERE poll_id = ? AND is_active<>0";
        $res = Dal::query($sql, $poll_id);
        if ($res->numRows()) {
          while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $data[] = $row;
          }
        }
     } 
    Logger::log("Exit: Poll::load_poll");
    return $data;
  }
  /**
   * Loads vote from database
   * @access public
  */
  public function load_vote($poll_id,$uid = 0) {
    Logger::log("Enter: Poll::load_vote");
    $data = array();
    if($uid != 0) { 
      $sql = "SELECT user_id, vote FROM {poll_vote} WHERE poll_id = ? AND user_id = ? AND is_active = 1";
      $res = Dal::query($sql, array($poll_id, $uid));
      if ($res->numRows()) {
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
          $data[] = $row;
        }
      } 
      }else {
     $sql = "SELECT * FROM {poll_vote} WHERE poll_id = ? AND is_active = 1";
     $res = Dal::query($sql,$poll_id);
     if ($res->numRows()) {
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
          $data[] = $row;
        }
      } 
    }
    return $data;  
  } 
  /**
  *Loads total votes acoording to the option
  * @access public
  */
  public function load_vote_option($poll_id,$option) {
    Logger::log("Enter: Poll::load_vote_option");
    $sql = "SELECT count(user_id) AS counter FROM {poll_vote} WHERE poll_id = ? AND is_active = 1 AND vote = ?";
    $data = array($poll_id, $option);
    $res = Dal::query($sql, $data);//p($res->query);
    if ($res->numRows()) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $data[] = $row;
      }
    } 
    return $data;  
  }
  
  
  /**
   *Delete poll from database.
   *access public
  */
  public function delete_poll($poll_id, $content_id) {
     Logger::log("Enter: Poll::delete_poll");
     //soft deletion
     $sql = "UPDATE {polls} SET is_active = 0 WHERE poll_id = ?";
     $res = Dal::query($sql, $poll_id);
     Logger::log("Calling: parent::delete()");
     parent::delete_by_id($content_id);
     Logger::log("Exit: Poll::delete()");
  }
  
  /**
   * Delete vote from database
   * @access public
  */
  public function delete_vote($poll_id) {
    Logger::log("Enter:Poll::delete_vote()");
    //soft deletion 
    $sql = "UPDATE {poll_vote} SET is_active = 0 WHERE poll_id = ?";
    $res = Dal::query($sql, $poll_id);
    Logger::log("Exit:Poll::delete_vote()");  
  }  
} 
?>
