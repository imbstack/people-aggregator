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
require_once "api/Logger/Logger.php";
require_once "api/PAException/PAException.php";

/**
* Class Ranking represents a way to rank users in the system.
*
* @package Ranking
* @author Tekriti Software
*/
class Ranking {

    /**
    * The ranking_id associated with a ranking parameter.
    *
    * @var integer
    */
    public $ranking_id;

    /**
    * The points associated with a ranking parameter.
    *
    * @var integer
    */
    public $point;

    /**
    * Update points of a ranking parameter.
    * Use Point and ranking_id associated with object.
    *
    */
    public function update_parameter() {
        Logger::log("Enter: Ranking::update()");
        if(empty($this->ranking_id)) {
            Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Ranking::ranking_id is empty", LOGGER_ERROR);
            throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Ranking::ranking_id is missing.');
        }
        if(empty($this->point)) {
            Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Ranking::point is empty", LOGGER_ERROR);
            throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Ranking::point is missing.');
        }
        Dal::query("UPDATE {site_ranking_parameters} SET point=? WHERE id=?", array($this->point, $this->ranking_id));
        Logger::log("Exit : Ranking::update()");
    }

    /**
    * Get all ranking parameters
    *
    */
    public static function get_parameters() {
        Logger::log("Enter: Ranking::get_multiple()");
        $res = Dal::query("SELECT * FROM {site_ranking_parameters}");
        if($res->numRows()) {
            $parameter = array();
            $i = 0;
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $parameter[$i]["id"]          = $row->id;
                $parameter[$i]["name"]        = $row->name;
                $parameter[$i]["description"] = $row->description;
                $parameter[$i]["point"]       = $row->point;
                $i++;
            }
            Logger::log("Exit : Ranking::get_multiple() | Returning array");
            return $parameter;
        }
    }

    /**
    * Update ranking of all recently login user.
    *
    */
    public function update_ranking() {
        Logger::log("Enter: Ranking::update_ranking()");
        $arr_parameters = $this->get_parameters();
        $arr_users = $this->recent_login_users();
        foreach($arr_users as $user_id => $user) {
            $point = 0;
            foreach($arr_parameters as $parameter) {
                $this->ranking_id = (int) $parameter["id"];
                $this->point = (int) $parameter["point"];
                switch($this->ranking_id) {
                    case 1:
                        if(!empty($user['picture'])) {
                            $point += $this->point;
                        }
                        break;
                    case 2:
                        if(isset($user["profile_visitor_count"]) && !empty($user['profile_visitor_count'])) {
                            $point += $this->point*$user["profile_visitor_count"];
                        }
                        break;
                    case 3:
                        $point += $this->point*$user['buddies'];
                        break;
                    case 4:
                        $point += $this->point*$user['image_uploaded'];
                        break;
                    case 5:
                        $point += $this->point*$user['group_created'];
                        break;
                    case 6:
                        if(isset($user["time_spent"]) && !empty($user['time_spent'])) {
                            $hour = (int)($user['time_spent']/3600);
                            $point += $this->point*$hour;
                        }
                        break;
                    default:
                        break;
                }
            }
            $this->update_user_points($user_id, $point);
        }
        Logger::log("Exit: Ranking::update_ranking()");
    }

    /**
    * Save ranking of a user into database.
    *
    * @param $user_id - uid of user
    * @param $point   - point user gained
    */
    public function update_user_points($user_id, $point) {
        Logger::log("Enter: Ranking::update_user_points() with user_id = ".$user_id." and point =".$point." as argument");
        $field_name = "site_points";
        $field_type = 5;
        $sql        = 'DELETE FROM {user_profile_data} WHERE user_id = ? AND field_type = ? AND field_name = ?';
        $data = array(
            $user_id,
            $field_type,
            $field_name,
        );
        Dal::query($sql, $data);
        $sql = 'INSERT into {user_profile_data} (user_id, field_name, field_value, field_type, field_perm, seq)  values (?, ?, ?, ?, ?, ?)';
        $data = array(
            $user_id,
            $field_name,
            $point,
            $field_type,
            0,
            NULL,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: Ranking::update_user_points()");
    }

    /**
    * Get array of all users who login after latest updates of ranking.
    *
    */
    public function recent_login_users() {
        Logger::log("Enter: Ranking::recent_login_users()");
        // FIXME: what do we DO with this stupid last cron time?
        $last_cron_timestamp = time();
        // ConfigVariable::get('last_cron_timestamp',0);
        $sql   = "SELECT 
              u.user_id, 
              u.login_name, 
              u.picture,  
              (select count(*) from {contentcollections} cc where cc.author_id = u.user_id AND cc.type =1 AND is_active=1) as group_created, 
              (select count(*) from {contents} c where c.author_id = u.user_id AND c.type = 4 AND is_active=1) as image_uploaded,
              (select count(*) from {relations} r where r.user_id = u.user_id AND r.status = 'approved') as buddies
            FROM {users} u
            WHERE u.is_active = 1 AND u.last_login >= ?";
        $users = array();
        $res   = Dal::query($sql, array($last_cron_timestamp));
        while($user = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $users[$user->user_id]["user_id"]        = $user->user_id;
            $users[$user->user_id]["login_name"]     = $user->login_name;
            $users[$user->user_id]["picture"]        = $user->picture;
            $users[$user->user_id]["group_created"]  = $user->group_created;
            $users[$user->user_id]["image_uploaded"] = $user->image_uploaded;
            $users[$user->user_id]["buddies"]        = $user->buddies;
            $sql                                     = "SELECT field_name, field_value FROM {user_profile_data} WHERE user_id = ? AND field_name IN('time_spent', 'profile_visitor_count')";
            $result                                  = Dal::query($sql, array($user->user_id));
            while($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                $users[$user->user_id][$row->field_name] = $row->field_value;
            }
        }
        Logger::log("Exit: Ranking::recent_login_users()");
        return $users;
    }

    /**
    * Get top ranked users
    *
    */
    public static function get_top_ranked_users($count = FALSE, $params = array()) {
        Logger::log("Enter: Ranking::get_top_ranked_users() with count=".$count);
        $qw = ' WHERE u.is_active = 1';
        if(isset($params["in"])) {
            $qw .= ' AND u.user_id IN ('.$params["in"].')';
        }
        elseif(isset($params["not_in"])) {
            $qw .= ' AND u.user_id NOT IN ('.$params["not_in"].')';
        }
        $qo = ' ORDER BY';
        if(isset($params["order_by"]) && $params["order_by"] == 2) {
            $qo .= ' u.created DESC';
        }
        elseif(isset($params["order_by"]) && $params["order_by"] == 3) {
            $qo .= ' CAST(upd.field_value AS UNSIGNED) ASC';
        }
        else {
            $qo .= ' CAST(upd.field_value AS UNSIGNED) DESC';
        }
        if(!empty($params["page"]) && (!empty($params["show"]))) {
            $start = ($params["page"]-1)*$params["show"];
            $limit = ' LIMIT '.$start.', '.$params["show"];
        }
        else {
            $limit = "";
        }
        $sql    = "SELECT u.user_id, u.login_name, u.picture, u.email, upd.field_value FROM {users} u LEFT JOIN {user_profile_data} upd ON u.user_id = upd.user_id AND upd.field_name = 'site_points' ".$qw." ".$qo." ".$limit;
        $users  = array();
        $result = Dal::query($sql);
        if($count == TRUE) {
            return $result->numRows();
        }
        while($user = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
            $users[$user->user_id]["user_id"]     = $user->user_id;
            $users[$user->user_id]["site_points"] = $user->field_value;
            $users[$user->user_id]["login_name"]  = $user->login_name;
            $users[$user->user_id]["picture"]     = $user->picture;
            $users[$user->user_id]["email"]       = $user->email;
        }
        Logger::log("Exit: Ranking::get_top_ranked_users()");
        return $users;
    }
}
?>