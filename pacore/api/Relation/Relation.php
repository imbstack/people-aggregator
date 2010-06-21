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
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/User/User.php";

/**
* Class RelationData represents a relation data in object format.
*
* @package Relation
* @author Zoran Hron, March 2009.
*/
class RelationData {

    public $user_id;

    public $relation_id;

    public $relationship_type;

    public $network;

    public $network_uid;

    public $display_name;

    public $thumbnail_url;

    public $profile_url;

    public $in_family;

    public $status;

    public function __construct($rel_record_obj) {
        $obj_vars = get_object_vars($rel_record_obj);
        foreach($obj_vars as $name => $value) {
            $this-> {
                $name
            } = $value;
        }
    }
}

/**
* Class Relation represents user relations.
*
* @package Relation
* @author Tekriti Software
*/
class Relation {

    /**
    * The default constructor for User class.
    */
    public function __construct() {
    }

    /**
    * Add the friend in the database by giving their user_id as relations_id.
    * @param int $relation_id This is user id of the user to whom user is adding as a friend
    * if this relation_id is given as -1, it is a external relation (flickr friend etc)
    * in that case the extra parameters MUST be supplied
    * in_family parameter added  by gurpreet to mark whether the person added is in family.
    */
    public static function getRelationData($user_id, $relation_id, $network_id) {
        $res = Dal::query_one_object("SELECT * FROM {relations} WHERE user_id=? AND relation_id=? AND network_uid=?", array($user_id, $relation_id, $network_id));
        if(!$res) {
            return NULL;
        }
        return new RelationData($res);
    }

    public static function add_relation($user_id, $relation_id, $relation_type_id = 2, $network = NULL, $network_uid = NULL, $display_name = NULL, $thumbnail_url = NULL, $profile_url = NULL, $in_family = null, $status = APPROVED) {
        // status added 04/01/2007
        Logger::log("Enter: function Relation::add_relation\n");
        if(!$relation_id) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required variable relation id is not specified");
        }
        if($relation_id < 0 && !$network_uid) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required variable network_uid is not specified");
        }
        if($relation_id == $user_id) {
            throw new PAException(USER_INVALID, "User is invalid to be added as friend. User can not add himself as a friend");
        }
        // make sure that the user to be added is active
        // but only if it is an internal user
        // relations from external networs have a
        // $relation_id of -1
        if((int) $relation_id > 0) {
            $user_exist = User::user_exist((int) $relation_id);
            if($user_exist == FALSE) {
                Logger::log(" Throwing exception USER_NOT_FOUND | Message: User does not exist", LOGGER_ERROR);
                throw new PAException(USER_NOT_FOUND, 'User does not exist.');
            }
        }
        try {
            // Delete an existing relation
            if(is_null($network_uid)) {
                $sql = 'DELETE FROM {relations} WHERE user_id = ? AND relation_id = ? AND network_uid IS ?';
            }
            else {
                $sql = 'DELETE FROM {relations} WHERE user_id = ? AND relation_id = ? AND network_uid = ?';
            }
            $data = array(
                $user_id,
                $relation_id,
                $network_uid,
            );
            Dal::query($sql, $data);
            // Insert relation_id for corresponding user_id
            $sql = 'INSERT into {relations}
        (user_id, relation_id, relationship_type,
        network, network_uid, display_name,
        thumbnail_url, profile_url, in_family, status)
        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $data = array(
                $user_id,
                $relation_id,
                $relation_type_id,
                $network,
                $network_uid,
                $display_name,
                $thumbnail_url,
                $profile_url,
                $in_family,
                $status,
            );
            Dal::query($sql, $data);
            // Finally - commit our changes to the DB
            Dal::commit();
        }
        catch(PAException$e) {
            // roll back database operations and re-throw the exception
            Dal::rollback();
            throw $e;
        }
        Logger::log("Exit: function Relation::add_relation");
    }

    /* Get all info in the DB on a particular relation.  Fairly
       heavyweight; it will fetch the user record too if required.  Use
       this when you want to display lots of info about one relation.

       We *really* need to tidy up all these get_relation_* functions;
       there's no way anyone will remember what all the different names
       mean ...

    */
    public static function get_relation_record($user_id, $relation_id) {
        $rel = Dal::query_one_assoc("SELECT * FROM {relations} WHERE user_id=? AND ".(is_numeric($relation_id) ? 'relation_id' : 'profile_url')."=?", Array($user_id, $relation_id));
        if(!$rel) {
            return NULL;
        }
        if($rel['relation_id'] !=-1) {
            $u = new User();
            $u->load((int) $rel['relation_id']);
            $rel['display_name']  = "$u->first_name $u->last_name";
            $rel['thumbnail_url'] = $rel['picture'] = $u->picture;
            $rel['user_id']       = (int) $u->user_id;
        }
        else {
            $rel['picture'] = $rel['thumbnail_url'];
            $rel['user_id'] = $rel['profile_url'];
        }
        return $rel;
    }

    /**
    * getting degree 1 relations list for a user.
    * @param $user_id user_id of the user of which degree 1 relations are to be loaded
    * @return $relations_id, an array containing the user_id of all degree 1 relations.
    */
    public static function get_stats() {
        $sql   = "SELECT status, count(*) AS count FROM {relations}
			GROUP BY status";
        $r     = Dal::query($sql);
        $stats = array();
        if($r->numRows()) {
            while($c = Dal::row_assoc($r)) {
                $stats[$c['status']] = $c['count'];
            }
        }
        if(PA::$extra['reciprocated_relationship']) {
            // we will have two paired relations
            $stats['approved'] = (int)((int) $stats['approved']/2);
        }
        return $stats;
    }

    public static function get_relations($user_id, $status = NULL, $network_uid = NULL) {
        Logger::log("Enter: function Relation::get_relations");
        // selecting friend list for a particular user
        // excluding external relations
        $sql = 'SELECT relation_id FROM {relations} WHERE user_id = ? AND relation_id > 0';
        $data = array(
            $user_id,
        );
        $temp_data = array();
        if(!empty($status)) {
            // if status is specified
            $sql .= " AND status = ?";
            $temp_data = array(
                $status,
            );
        }
        $data = array_merge($data, $temp_data);
        $temp_data = array();
        if(!empty($network_uid)) {
            // if network_uid is specified
            $sql .= " AND network_uid = ?";
            $temp_data = array(
                $network_uid,
            );
        }
        $data         = array_merge($data, $temp_data);
        $res          = Dal::query($sql, $data);
        $relations_id = array();
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $relations_id[] = $row->relation_id;
        }
        Logger::log("Exit: function Relation::get_relations");
        return $relations_id;
    }

    public static function get_relations_detail($user_id) {
        Logger::log("Enter: function Relation::get_relations_detail");
        //selecting friend list for a particular user
        $res = Dal::query('SELECT relation_id, relationship_type FROM {relations} WHERE user_id = ?', array($user_id));
        $relations = array();
        while($row = Dal::row($res)) {
            list($friend_id, $rel_type) = $row;
            $relations[] = array(
                intval($friend_id),
                intval($rel_type),
            );
        }
        Logger::log("Exit: function Relation::get_relations_detail");
        return $relations;
    }

    /** method to get a mapping of all relation type ids and relation
     *  strings.
     *
     *  returns an array with keys corresponding to relation_type_id
     *  values and values corresponding to relation_type values from the
     *  relation_classifications table, e.g. array(1 => "havent met", 2
     *  * => "some other classification", ...)
     */
    public static function get_relation_classifications() {
        $sth = Dal::query("SELECT relation_type_id, relation_type FROM {relation_classifications}");
        $ret = array();
        while($r = Dal::row($sth)) {
            list($rid, $rcode) = $r;
            $ret[$rid] = $rcode;
        }
        return $ret;
    }

    public static function lookup_relation_type_id($rel_type) {
        $r = Dal::query_one("SELECT relation_type_id FROM {relation_classifications} WHERE relation_type=?", Array($rel_type));
        if(!$r) {
            throw new PAException(INVALID_RELATION, "Unknown relation type '$rel_type'");
        }
        return intval($r[0]);
    }

    public static function lookup_relation_type($rel_type_id) {
        $r = Dal::query_one("SELECT relation_type FROM {relation_classifications} WHERE relation_type_id=?", Array($rel_type_id));
        if(!$r) {
            throw new PAException(INVALID_RELATION, "Unknown relation type ID $rel_type_id");
        }
        return $r[0];
    }

    /**
  * method for generation of degree of seperation (Implementation of Dijkstra algo) - called in shortest_path()
  * @param $neighbors the array of all immediate relations
  * @param $start the user id of the user from which travelled relations has to be loaded
  * @return $paths the degree and path travelled
  */
    private function dijkstra($neighbors, $start) {
        Logger::log("Enter: Relation::dijkstra");
        $paths = array();
        $closest = $start;
        while(isset($closest)) {
            $marked[$closest] = 1;
            @reset($neighbors[$closest]);
            while(list($user_node, $distance) = @each($neighbors[$closest])) {
                if($marked[$user_node]) {
                    continue;
                }
                $dist = $paths[$closest][0]+$distance;
                if(!isset($paths[$user_node]) || ($dist < $paths[$user_node][0])) {
                    $paths[$user_node]    = $paths[$closest];
                    $paths[$user_node][]  = $closest;
                    $paths[$user_node][0] = $dist;
                }
            }
            unset($closest);
            @reset($paths);
            //here the closest path is selected after checking all paths to rewach that user_id
            while(list($user_node, $path) = @each($paths)) {
                if($marked[$user_node]) {
                    continue;
                }
                $distance = $path[0];
                if(($distance < $min) || !isset($closest)) {
                    $min = $distance;
                    $closest = $user_node;
                }
            }
        }
        Logger::log("Exit: Relation::dijkstra");
        return $paths;
    }

    /**
    * Method to return shortest path with the required degree
    * @param start_id the start id of the path from which path has to be loaded
    * @param end_id the end id of the user till which path has to be loaded
    * @return it returns the array having degree and the path trevelled from start_id to end_id
    */
    public function shortest_path($start_id, $end_id) {
        Logger::log("Enter: Relation::shortest_path");
        $neigh  = $this->set_graph();
        $path   = $this->dijkstra($neigh, $start_id);
        $degree = $path[$end_id][0];
        for($i = 1; $i < count($path[$end_id]); $i++) {
            $nodes_in_path[] = $path[$end_id][$i];
        }
        Logger::log("Exit: Relation::shortest_path");
        return $path[$end_id];
    }

    /**
    * this is the method to get the Graph of the nodes to be travelled for calculatin degree of seperation, this * convert myfriends into associative array -- - called in shortest_path()
    * @return $neighbors the global array having all immediate relations for each user id
    */
    private function set_graph() {
        Logger::log("Enter: Relation::set_graph");
        global $neighbors;
        $sql = 'SELECT user_id FROM {users} WHERE is_active = ?';
        $data = array(
            1,
        );
        $res = Dal::query($sql, $data);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $immediate_relations = Relation::get_relations($row->user_id);
            for($i = 0; $i < count($immediate_relations); $i++) {
                $neighbors[$row->user_id][$immediate_relations[$i]] = 1;
            }
        }
        Logger::log("Exit: Relation::set_graph");
        return $neighbors;
    }

    /**
  * Load relations of a given type and their information
  * @param $user_id user_id of the user
  * @param $relation_id id of the friend
  * @return $user_relation_data an array having user relation's information
  */
    static

    function get_relations_of_type($user_id, $type, $pagesize = 0, $page_no = 1) {
        Logger::log("Enter: Relation::get_relation_type");
        $user_relation_data = array();
        $i                  = 0;
        $count1             = ($page_no-1)*$pagesize;
        $count2             = $pagesize;
        // TO DO: Calculate $count2 = $pagesize-1 or $count2 = $pagesize
        $sql = "SELECT U.login_name AS LN, U.picture AS PT, U.user_id AS UID FROM {relations} AS R, {relation_classifications} AS RC, {users} AS U WHERE R.relationship_type = RC.relation_type_id AND R.relationship_type = ? AND R.user_id = ? AND U.user_id = R.relation_id";
        if($pagesize != 0) {
            // if $pagesize == 0, its load all friends of given type
            $sql .= " LIMIT $count1, $count2";
        }
        $data = array(
            $type,
            $user_id,
        );
        $res = Dal::query($sql, $data);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $user_relation_data[$i]['login_name'] = $row->LN;
            $user_relation_data[$i]['user_id']    = $row->UID;
            $user_relation_data[$i]['picture']    = $row->PT;
            $i++;
        }
        Logger::log("Exit: Relation::get_relation_type");
        return $user_relation_data;
    }

    /**
    * Load EXTERNAL relations of all types and their information
    * @param $user_id user_id of the user
    * @return $user_relation_data an array having user relation's information
    */
    static

    function get_external_relations($user_id, $no_of_relations = 0, $cnt = FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC') {
        // this is just a convenience wrapper
        return Relation::get_all_relations($user_id, $no_of_relations, $cnt, $show, $page, $sort_by, $direction, 'external');
    }

    /**
    * Load relations of all types and their information
    * @param $user_id user_id of the user
    * @param $no_of_relations, no of relations to load at a time
    * @return $user_relation_data an array having user relation's information
    */
    static

    function get_all_relations($user_id, $no_of_relations = 0, $cnt = FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC', $scope = 'internal', $status = NULL, $network_uid = NULL) {
        // status added
        Logger::log("Enter: Relation::get_all_relations");
        $data               = array();
        $user_relation_data = array();
        $i                  = 0;
        $order_by           = $sort_by.' '.$direction;
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        if($scope == 'external') {
            // we get all our main info directly from the relations table in this case
            // and all external relations have the relation_id set to -1
            $sql = "SELECT *,
        R.relationship_type as Rel_id,
        RC.relation_type AS RT,
        R.display_name AS LN,
        R.display_name AS DN,
        R.thumbnail_url AS PT,
        R.profile_url AS UID,
        R.network as NET,
        R.network_uid as NETUID,
        R.status
        FROM {relations} AS R,
        {relation_classifications} AS RC
        WHERE user_id = ?
        AND relation_id = -1
        AND R.relationship_type = RC.relation_type_id";
        }
        else {
            // this is the default for normal
            // relations inside one PA instance
            $sql = "SELECT *, R.relationship_type as Rel_id, RC.relation_type AS RT,
        U.login_name AS LN, U.picture AS PT, U.user_id AS UID, R.status
        FROM {relations} AS R,
        {relation_classifications} AS RC,
        {users} AS U
        WHERE R.relationship_type = RC.relation_type_id
        AND R.user_id = ?
        AND U.user_id = R.relation_id
        AND U.is_active <> ".DELETED;
            // we don't want to see deleted users, do we?, omitting only deleted users (keeping the disabled ones)
        }
        $temp_data = array();
        if(!empty($status)) {
            // if status is specified
            $sql .= " AND R.status = ?";
            $temp_data = array(
                $status,
            );
        }
        $data = array_merge($data, $temp_data);
        $temp_data = array();
        if(!empty($network_uid)) {
            // if status is specified
            $sql .= " AND R.network_uid = ?";
            $temp_data = array(
                $network_uid,
            );
        }
        $data = array_merge($data, $temp_data);
        if($no_of_relations != 0) {
            // if $no_of_friends == 0, its load all friends of given user id
            $sql .= " LIMIT $no_of_relations";
        }
        else {
            $sql .= " $limit";
        }
        $data = array_merge(array($user_id), $data);
        $res = Dal::query($sql, $data);
        if($cnt) {
            return $res->numrows();
        }
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            try {
                $u = new User();
                $u->load((int) $row->UID);
            }
            catch(Exceotion$e) {
                continue;
            }
            $user_relation_data[$i]['login_name']       = $u->login_name;
            $user_relation_data[$i]['user_id']          = $u->user_id;
            $user_relation_data[$i]['network']          = @$row->NET;
            $user_relation_data[$i]['network_uid']      = @$row->NETUID;
            $user_relation_data[$i]['picture']          = $u->picture;
            $user_relation_data[$i]['first_name']       = $u->first_name;
            $user_relation_data[$i]['last_name']        = $u->last_name;
            $user_relation_data[$i]['display_name']     = $u->display_name;
            $user_relation_data[$i]['email']            = $u->email;
            $user_relation_data[$i]['created']          = @$row->created;
            $user_relation_data[$i]['relation_type']    = @$row->RT;
            $user_relation_data[$i]['relation_type_id'] = @$row->Rel_id;
            $user_relation_data[$i]['in_family']        = @$row->in_family;
            $user_relation_data[$i]['status']           = @$row->status;
            $i++;
        }
        Logger::log("Exit: Relation::get_all_relations");
        return $user_relation_data;
    }

    public static function count_relations($user_id, $scope = "internal") {
        if($scope == "external") {
            // we count only relations ourside of this PA instance
            // e.g. imported friends etc
            $sql = "SELECT COUNT(*) FROM {relations}
          WHERE relation_id < 0
          AND user_id = ?";
        }
        else {
            // default behaviour
            // we count relations only in this PA instance
            $sql = "SELECT COUNT(*)
          FROM {relations} AS R, {users} AS U
          WHERE R.user_id = ?
          AND U.user_id = R.relation_id
          AND U.is_active = 1";
        }
        $r = Dal::query_one($sql, Array($user_id));
        return intval($r[0]);
    }

    public static function count_all_user_relations($user_id, $scope, $network_id) {
        if($scope == 'internal') {
            $sql = "SELECT COUNT(*)
          FROM {relations} AS R
          LEFT JOIN {users} AS U
          ON U.user_id = R.user_id
          WHERE R.user_id = ?
          OR R.relation_id = ?
          AND R.network_uid = ?
          AND U.is_active = 1";
            $r = Dal::query_one($sql, array($user_id, $user_id, $network_id));
        }
        else {
            $sql = "SELECT COUNT(*)
          FROM {relations} AS R
          LEFT JOIN {users} AS U
          ON U.user_id = R.user_id
          WHERE R.user_id = ?
          AND R.relation_id = -1
          AND R.network_uid = ?
          AND U.is_active = 1";
            $r = Dal::query_one($sql, array($user_id, $network_id));
        }
        return intval($r[0]);
    }

    /**
  * Load all user relations
  * @param $my_user_id
  * @param $no_of_userids, no of relations to load at a time
  * @return $user_data an array having user relation's information
  */
    static

    function get_all_user_relations($my_user_id, $no_of_userids = 0, $cnt = FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC', $scope = 'internal', $status = NULL, $network_uid = NULL) {
        $rel1      = self::get_all_relations($my_user_id, $no_of_userids, false, $show, $page, $sort_by, $direction, $scope, $status, $network_uid);
        $rel2      = self::get_all_user_ids($my_user_id, $no_of_userids, false, $show, $page, $sort_by, $direction, $status, $network_uid);
        $relations = array_merge($rel1, $rel2);
        $res       = array();
        if(count($relations) > 0) {
            foreach($relations as $curr_rel) {
                $is_dup = false;
                foreach($res as $res_rel) {
                    if($curr_rel['user_id'] == $res_rel['user_id']) {
                        $is_dup = true;
                        break;
                    }
                }
                if(!$is_dup) {
                    $res[] = $curr_rel;
                }
            }
        }
        if($cnt == true) {
            return count($res);
        }
        return $res;
    }
    // find the relation between two given users
    public static function get_relation($user_id, $relation_id, $network_uid = NULL) {
        Logger::log("Enter: Relation::get_relation");
        if(is_null($network_uid)) {
            $sql = 'SELECT relationship_type FROM {relations} WHERE user_id=? AND relation_id=? AND network_uid IS ?';
        }
        else {
            $sql = 'SELECT relationship_type FROM {relations} WHERE user_id=? AND relation_id=? AND network_uid=?';
        }
        $r = Dal::query_one($sql, Array($user_id, $relation_id, $network_uid));
        if(!$r) {
            throw new PAException(RELATION_NOT_EXIST, "There is no relation between $user_id and $relation_id");
        }
        Logger::log("Exit: Relation::get_relation");
        return intval($r[0]);
    }

    /**
  * Delete a friend from the friends list
  * @param $user_id the id of the user who is performing deletion
  * @param $relation_id the id of friend to be deleted
  */
    public static function delete_relation($user_id, $relation_id, $network_uid = NULL, $network = NULL) {
        Logger::log("Enter: Relation::delete_relation");
        // make sure there is a relation to delete
        // this throws an exception if there is no relation.
        $data = array(
            $user_id,
            $relation_id,
        );
        Relation::get_relation($user_id, $relation_id, $network_uid);
        $sql = 'DELETE FROM {relations} WHERE user_id = ? AND relation_id = ?';
        if(!is_null($network_uid)) {
            $sql .= " AND network_uid = ?";
            array_push($data, $network_uid);
        }
        if(!is_null($network)) {
            $sql .= ' AND network = ?';
            array_push($data, $network);
        }
        // delete it!
        Dal::query($sql, $data);
        return true;
        Logger::log("Exit: Relation::delete_relation");
    }

    /**
  * Delete all relations that are from a specific network
  * this only makes sense for external relations
  * @param $user_id the id of the user who is performing deletion
  * @param $network the string name of the network ('flickr','facebook', etc)
  */
    public static function delete_relations_of_network($user_id, $network) {
        Logger::log("Enter: Relation::delete_relations_of_network");
        // delete it!
        Dal::query('DELETE FROM {relations} WHERE user_id = ? AND network = ?', Array($user_id, $network));
        Logger::log("Exit: Relation::delete_relations_of_network");
    }

    /**
    * getting user ids where I am as relations for that user.
    * @param $my_user_id my user_id for which users ids are to be loaded
    * @return $user_id, an array containing the user_ids.
    */
    public static function get_user_ids($my_user_id, $status = NULL, $network_uid = NULL) {
        Logger::log("Enter: function Relation::get_user_ids");
        //selecting friend list for a particular user
        $sql = 'SELECT user_id FROM {relations} WHERE relation_id = ?';
        $data = array(
            $my_user_id,
        );
        if(!empty($status)) {
            $sql .= ' AND status = ?';
            $data = array_merge($data, array($status));
        }
        if(!empty($network_uid)) {
            $sql .= ' AND network_uid = ?';
            $data = array_merge($data, array($network_uid));
        }
        $res = Dal::query($sql, $data);
        $user_ids = array();
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $user_ids[] = $row->user_id;
        }
        Logger::log("Exit: function Relation::get_user_ids");
        return $user_ids;
    }

    /**
    * count user ids where I am as relations for that user.
    * @param my_user_id user_id for which user ids are to be counted
    * @param status : status of the relation; ie approved or denied.
    * @param network_uid : get relations by specific network
    * @return $count
    */
    public static function count_users_in_relations($my_user_id, $status = NULL, $network_uid = NULL) {
        Logger::log("Enter: function Relation::count_users_in_relations");
        //selecting friend list for a particular user
        $sql = 'SELECT count(user_id) AS count FROM {relations} WHERE relation_id = ?';
        $data = array(
            $my_user_id,
        );
        if(!empty($status)) {
            $sql .= ' AND status = ?';
            $data = array_merge($data, array($status));
        }
        if(!empty($network_uid)) {
            $sql .= ' AND network_uid = ?';
            $data = array_merge($data, array($network_uid));
        }
        $res = Dal::query_one_assoc($sql, $data);
        Logger::log("Exit: function Relation::count_users_in_relations");
        return $res['count'];
    }

    /**
    * count all user relations for by status or network_id
    * @param user_id user_id for which user ids are to be counted
    * @param status : status of the relation; ie approved or denied.
    * @param network_uid : get relations by specific network
    * @return $count
    */
    public static function count_user_relations($user_id, $status = NULL, $network_uid = NULL) {
        Logger::log("Enter: function Relation::count_user_relations");
        //selecting friend list for a particular user
        $sql = 'SELECT count(user_id) AS count FROM {relations} WHERE user_id = ?';
        $data = array(
            $user_id,
        );
        if(!empty($status)) {
            $sql .= ' AND status = ?';
            $data = array_merge($data, array($status));
        }
        if(!empty($network_uid)) {
            $sql .= ' AND network_uid = ?';
            $data = array_merge($data, array($network_uid));
        }
        $res = Dal::query_one_assoc($sql, $data);
        Logger::log("Exit: function Relation::count_user_relations");
        return $res['count'];
    }

    /**
  * Load user ids where I am as relations for that user.
  * @param $my_user_id my user_id for which users ids are to be loaded
  * @param $no_of_userids, no of relations to load at a time
  * @return $user_data an array having user relation's information
  */
    static

    function get_all_user_ids($my_user_id, $no_of_userids = 0, $cnt = FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC', $status = NULL, $network_uid = NULL) {
        Logger::log("Enter: Relation::get_all_user_ids");
        $data      = array();
        $user_data = array();
        $i         = 0;
        $order_by  = $sort_by.' '.$direction;
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql = "SELECT *, R.relationship_type as Rel_id, RC.relation_type AS RT, U.login_name AS LN, U.picture AS PT, U.user_id AS UID FROM {relations} AS R, {relation_classifications} AS RC, {users} AS U WHERE R.relationship_type = RC.relation_type_id AND R.relation_id = ? AND U.user_id = R.user_id";
        $temp_data = array();
        if(!empty($status)) {
            $sql .= " AND R.status = ?";
            $temp_data = array(
                $status,
            );
        }
        $data = array_merge($data, $temp_data);
        $temp_data = array();
        if(!empty($network_uid)) {
            $sql .= " AND R.network_uid = ?";
            $temp_data = array(
                $network_uid,
            );
        }
        $data = array_merge($data, $temp_data);
        if($no_of_userids != 0) {
            // if $no_of_friends == 0, its load all friends of given user id
            $sql .= " LIMIT $no_of_userids";
        }
        else {
            $sql .= " $limit";
        }
        $data = array_merge(array($my_user_id), $data);
        $res = Dal::query($sql, $data);
        if($cnt) {
            return $res->numrows();
        }
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $u = new User();
            $u->load((int) $row->UID);
            $user_data[$i]['login_name']       = $u->login_name;
            $user_data[$i]['display_name']     = $u->display_name;
            $user_data[$i]['user_id']          = $row->UID;
            $user_data[$i]['picture']          = $row->PT;
            $user_data[$i]['first_name']       = $row->first_name;
            $user_data[$i]['last_name']        = $row->last_name;
            $user_data[$i]['email']            = $row->email;
            $user_data[$i]['created']          = $row->created;
            $user_data[$i]['relation_type']    = $row->RT;
            $user_data[$i]['relation_type_id'] = $row->Rel_id;
            $user_data[$i]['status']           = $row->status;
            $i++;
        }
        Logger::log("Exit: Relation::get_all_user_ids");
        return $user_data;
    }

    /**
  * function to delete all the relations of a user
  */
    public static function delete_user_relations($user_id) {
        Logger::log("Enter: Relation::delete_user_relations");
        $sql = 'DELETE FROM {relations} WHERE user_id = ? OR relation_id = ?';
        $data = array(
            '0' => $user_id,
            '1' => $user_id,
        );
        if(!$res = Dal::query($sql, $data)) {
            Logger::log("Relation::delete_user_relations failed");
            throw new PAException(RELATIONS_DELETE_FAILED, "Unable to delete the user relations.");
        }
        Logger::log("Exit: Relation::delete_user_relations");
        return;
    }
    // find whether relation between two given users already exists
    public static function relation_exists($user_id, $relation_id) {
        $r = Dal::query_one("SELECT * FROM {relations} WHERE user_id=? AND relation_id=?", Array($user_id, $relation_id));
        if($r) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
    * Static function to check whether a relation of user is in user's family
    * @param user_id of the user whose user whose relation has to be checked
    * @param relation_id : user_id of the relation under investigation
    * @return true if relation is in family, false otherwise
    */
    public static function is_relation_in_family($user_id, $relation_id) {
        Logger::log("Enter: Relation::is_relation_in_family");
        if(empty($user_id) || empty($relation_id)) {
            Logger::log("Exiting: Relation::is_relation_in_family as required parameters userid = $user_id, relation_id = $relation_id are not set");
            throw new PAException(PARAMETERS_NOT_SET, "Required parameters are not set.");
        }
        $sql = 'SELECT * FROM {relations} WHERE in_family = ? AND relation_id = ? AND user_id = ?';
        try {
            $res = Dal::query($sql, array(true, $relation_id, $user_id));
        }
        catch(PAException$e) {
            Logger::log("Exiting: Relation::is_relation_in_family query failed. Associated sql = $sql");
            throw $e;
        }
        $return = false;
        if($res->numRows()) {
            $return = true;
        }
        Logger::log("Exit: Relation::is_relation_in_family");
        return $return;
    }

    /**
  * Static function to update the status of a relationship between user
  * and user's friend
  * @param user_id of the user whose user whose relation has to be checked
  * @param relation_id : user_id of the relation under investigation
  * @param status : status of the relation; ie approved or denied.
  * @return true if relation has been updated, false otherwise
  */
    public static function update_relation_status($user_id, $relation_id, $status, $network_uid = null) {
        $sql = 'UPDATE {relations} set status = ?
            WHERE user_id = ? AND relation_id = ?';
        $data = array(
            $status,
            $user_id,
            $relation_id,
        );
        if($network_uid) {
            $sql .= " AND network_uid = ?";
            array_push($data, $network_uid);
        }
        try {
            $res = Dal::query($sql, $data);
            if($res) {
                $return = true;
            }
        }
        catch(PAException$e) {
            throw $e;
        }
        Logger::log("Exit: Relation::update_relation_status");
        return $return;
    }

    public static function update_relation_network_info($user_id, $relation_id, $network, $network_uid) {
        $sql = 'UPDATE {relations} set network = ?, network_uid = ?
            WHERE relation_id = ? AND user_id = ?';
        $data = array(
            $network,
            $network_uid,
            $relation_id,
            $user_id,
        );
        try {
            $res = Dal::query($sql, $data);
            if($res) {
                $return = true;
            }
        }
        catch(PAException$e) {
            throw $e;
        }
        Logger::log("Exit: Relation::update_relation_network_info");
        return $return;
    }

    /**
    * Static function to get count of relations of given status in a network
    * and user's friend
    * @param status : status of the relation; ie approved or denied.
    * @return count of relations of specified status
    */
    public static function get($params, $conditions) {
        Logger::log("[ Enter: function Relation::get] \n");
        $sql = "SELECT * FROM {relations} WHERE 1 ";
        if($conditions) {
            $sql = $sql.' AND '.$conditions;
        }
        $res = Dal::query($sql);
        if($params['cnt'] == TRUE) {
            // here we just want to know total relations
            Logger::log("[ Exit: function Relation::get and returning count] \n");
            return $res->numRows();
        }
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $relation[] = $row;
        }
        Logger::log("[ Exit: function Relation::get] \n");
        return $relation;
    }

    /**
    * This function returns an array of total relationships of each user
    * @access public
    */
    public function relation_stats() {
        Logger::log("Enter: function Relation::relation_stats\n");
        $sql            = "SELECT count(*) AS cnt FROM {relations} GROUP BY user_id";
        $res            = Dal::query($sql);
        $relation_count = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $relation_count[] = $row->cnt;
            }
        }
        Logger::log("Exit: function Relation::relation_stats\n");
        return $relation_count;
    }

    /**
     * Function added by Himanshu for activites of people module
     * Static function to give us the array of ids of particular user's friends on basis of
      who made recent activities in system or any other basis like popularity
     *@param string $params is for giving order by $params['order'] it must be like         P.'field_name' for popularity table field and R.'field_name' for relation table       field,direction by $params['direction']and limit by $params['limit'] for selection    of friends.
     *@param string $time_interval contains time interval in seconds if we want to select   user activities done in specific period from present date
    */
    public static function get_relation_ids($login_id, $params = NULL, $time_interval = NULL) {
        Logger::log("Enter: function Relation::get_relation_ids");
        if((empty($login_id))) {
            Logger::log("Throwing exception");
            throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required parameter missing");
        }
        $sql = "SELECT P.user_id as user FROM {user_popularity} as P inner join
            {relations} as R WHERE P.user_id=R.relation_id AND R.user_id = ? ";
        if(!empty($time_interval)) {
            $time = (time()-$time_interval);
            $sql .= " AND P.time < ".$time;
        }
        $direction = ($params['direction']) ? $params['direction'] : 'DESC';
        if($params['order']) {
            $order_by = ' ORDER BY '.$params['order'].' '.$direction;
        }
        else {
            $order_by = ' ORDER BY  P.time '.$direction;
        }
        if($params['limit']) {
            $limit = ' LIMIT '.$params['limit'];
        }
        else {
            $limit = "";
        }
        $sql = $sql.$order_by.$limit;
        $data = array(
            $login_id,
        );
        $res = Dal::query($sql, $data);
        $relations_id = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $relations_id[] = $row->user;
            }
        }
        Logger::log("Exit: function Relation::get_relation_ids");
        return $relations_id;
    }
}
?>