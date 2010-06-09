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
require_once "api/api_constants.php";
require_once "db/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/Activities/ActivityType.class.php";

/**
 * Class Activities saves,Update,deletes and returns series of activities performed by a user in the system.
  */
class Activities {

/**
 *Static Function to save activity performed by user in activity_log and in user_popularity his popularity according to his activities weightage, this function has a single query for insert and update if same login_id exists in table user_popularity
 *@param string $login_id to have present login user id
 *@param string $type is activity performed by user it should be present in array in api_constant
 *@param string $object is id of Group,Network,Album or Friend where ever is activity performed
 *@param string $extra is serialized description about activity performed

*/
  public static function save($login_id, $type, $object, $extra = NULL, $datetime = NULL, $points = 0) {
    Logger::log("Enter: function Activities::save");
      if ((empty($type)) || (empty($object)) || (empty($login_id))) {
        Logger::log("Throwing exception");
        throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required parameter missing");
      }
      $time = ($datetime) ? $datetime : time();
      $activity_weightage = ($points > 0) ? $points : ActivityType::get_points_for_activity($type);
      if ($activity_weightage !== false) {
        $sql = "INSERT INTO {activity_log} (type, subject, object, extra, status, time) VALUES(?, ?, ?, ?, ?, ?)";
        $data = array($type, $login_id, $object, $extra, 'new', $time);
        $res = Dal::query($sql, $data);
      } else {
        Logger::log("Throwing exception");
        throw new PAException(ACTIVITY_TYPE_NOT_EXIST, "Activity type not exist");
      }
      $sql = "INSERT INTO {user_popularity} (user_id, popularity, time) VALUES (".$login_id.", ".$activity_weightage.", ".$time.") ON DUPLICATE KEY UPDATE  popularity = (popularity + ".$activity_weightage." ), time = ".$time;
      $res = Dal::query($sql);
    Logger::log("Exit: function Activities::save");
  }

/**
 *Static Function to get activity list.
 *@param string $params = array('relation_ids'=array(),'direction'=>,'order'=>)
  for giving order by $params['order'],direction by $params['direction'] and limit by $params['limit'] for selection  of activity.It can also contain id if we want to select for particular subject id like group id,network id,users id as $param['relations_ids']
 *@param string $condition is condition if we want to add for getting activity
  $condition must starts with AND,OR followed by condition to check
*/
  public static function get_activities($params = NULL , $conditions = NULL) {
    Logger::log("Enter: function Activities::get_activities");
     $data = array();
     $sql = "SELECT * FROM {activity_log}  WHERE 1 ";
     if ( (!empty($params['relation_ids'])) && (is_array($params['relation_ids'])) ) {
        $sql .= " AND subject IN ( ";
        foreach($params['relation_ids'] as $key) {
          $sql .= " ?, ";
        }
        $sql = substr($sql, 0, -2);
        $sql .= ") ";
        $data = array_merge($data, $params['relation_ids']);
      } else if(!empty($params['relation_ids'])) {
        $sql .= " AND subject = ? ";
        array_push($data, $params['relation_ids']);
      }

      //appending the list of required activity types if any there
      if (!empty($params['activity_type'])) {
        if (is_array($params['activity_type'])) {
          $sql .= ' AND type IN(';
          foreach ($params['activity_type'] as $type) {
            $sql .= '?, ';
            array_push($data, $type);
          }
          $sql = substr($sql, 0, -2);//removing the extra comma and single space
          $sql .= ')';
        } else {
          $sql .= ' AND type = ?';
          array_push($data, $params['activity_type']);
        }
      }

      if (!empty($conditions)) {
        foreach ($conditions as $key => $value) {
          $sql .= " AND $key = ?";
          array_push($data, $value);
        }
      }

      $direction = (!empty($params['direction'])) ? $params['direction'] : 'DESC';
      if(!empty($params['order'])) {
        $order_by = ' ORDER BY  ' .$params['order'].' '. $direction;
      } else {
        $order_by = ' ORDER BY  time ' . $direction;
      }
      if (!empty($params['limit'])) {
        $limit = ' LIMIT '.$params['limit'];
      } else {
        $limit = "";
      }

      $sql = $sql . $order_by . $limit;
      $res = Dal::query($sql, $data);
      $river = array();
      if ($res->numRows() > 0) {
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
          $river[]= $row;
        }
      }
    Logger::log("Exit: function Activities::get_activities");
      return $river;
  }

/**
 *Static function to updates activities status to viewed from new when they are viewed.
 *@param string $params can contain key value pair when we want to have selective update
*/
  public static function update($params = NULL, $status = 'viewed') {
    Logger::log("Enter: function Activities::update");
      $sql = "UPDATE {activity_log} SET status = ?  WHERE 1 ";
      $data = array($status);
      if (!empty($params) && is_array($params)) {
        foreach ($params as $field_name => $field_value) {
          $sql = $sql .' AND ' . $field_name .' = ?';
          array_push($data, $field_value);
        }
      }

      Dal::query($sql, $data);
    Logger::log("Exit: function Activities::update");
  }


/**
 *Static function to delete activities older than particular time interval
 *@param string $params can contain key value pair if we want to have selective delete
 *@param string $time_interval contains time interval in seconds to delete activity     list by default its set equal to number of seconds in 2 days in api_constant
*/
  public static function delete($params = NULL, $time_interval = TIME_INTERVAL ) {
    Logger::log("Enter: function Activities::delete");
      $sql = "DELETE FROM {activity_log} WHERE  status = ? ";
      $data = array('viewed');
      if (!empty($params) && is_array($params)) {
        foreach ($params as $field_name => $field_value) {
          $sql = $sql .' AND ' . $field_name .' = ?';
          array_push($data, $field_value);
        }
      }
      $time = (time() - $time_interval);
      $sql .= "AND time < ". $time ;
      Dal::query($sql, $data);
    Logger::log("Exit: function Activities::delete");
  }

/**
 *Static function to delete activities for a group
*/
  public static function delete_for_group($gid) {
    Logger::log("Enter: function Activities::delete_for_group");
      if (empty($gid)) {
        Logger::log("Throwing exception");
        throw new PAException(GROUP_ID_NOT_DEFINED, "Group id can't be empty");
      }
      $sql = "DELETE FROM {activity_log} WHERE type like 'group_%' AND object = ?";
      $data = array($gid);
      Dal::query($sql, $data);
    Logger::log("Exit: function Activities::delete_for_group");
  }

/**
 *Static function to delete activities for a Network
*/
  public static function delete_for_network($nid) {
    Logger::log("Enter: function Activities::delete_for_network");
      if (empty($nid)) {
        Logger::log("Throwing exception");
        throw new PAException(NETWORK_ID_NOT_DEFINED, "Network id can't be empty");
      } else if($nid == MOTHER_NETWORK_TYPE) {
        Logger::log("Throwing exception");
        throw new PAException(MOTHER_NETWORK_ID_PASSED, "Mother Network activities can't be deleted");
      }
      $sql = "DELETE FROM {activity_log} WHERE type like 'network_%' AND object = ?";
      $data = array($nid);
      Dal::query($sql, $data);
    Logger::log("Exit: function Activities::delete_for_network");
  }

/**
 *Static function to delete activities for a User
*/
  public static function delete_for_user($uid) {
    Logger::log("Enter: function Activities::delete_for_user");
      if (empty($uid)) {
        Logger::log("Throwing exception");
        throw new PAException(USER_ID_NOT_DEFINED, "User id can't be empty");
      } else if($uid == SUPER_USER_ID) {
        Logger::log("Throwing exception");
        throw new PAException(ADMIN_ID_PASSED, "Admin can't be deleted");
      }

//      $sql = "DELETE FROM {activity_log} WHERE subject = ? OR (type like 'user_%' AND object = ?)";
      $sql = "UPDATE {activity_log} SET status = ?  WHERE subject = ? ";
      $data = array('deleted', $uid);
//      $data = array($uid, $uid);
      Dal::query($sql, $data);

    Logger::log("Exit: function Activities::delete_for_user");
  }


  public static function get_activity($type, $subject_id, $object_id) {
    Logger::log("Enter: function Activities::get_activity");
      $result = null;
      $sql = "SELECT * FROM {activity_log} WHERE type = ? AND subject = ? AND object = ?";
      $data = array($type, $subject_id, $object_id);
      $res = Dal::query($sql, $data);
      if($res->numRows() > 0) {
        if($res->numRows() == 1) {
          $result = $res->fetchRow(DB_FETCHMODE_OBJECT);
        } else {
          Logger::log("Throwing exception");
          throw new PAException(DUPLICATED_RECORD_FOUND, "Duplicated Activity record found in activity_log DB table. Please, contact system admin and report this error.");
        }
      }
    Logger::log("Exit: function Activities::get_activity");
    return $result;
  }

}
?>
