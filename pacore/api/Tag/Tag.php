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
require_once "api/User/User.php";
require_once "api/Content/Content.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/PAException/PAException.php";

/**
* Class tags represents the tags in the system.
*
* @package Tags
* @author Tekriti Software
*/
class Tag {

    /**
    * The default constructor for tags class.
    */
    public function __construct() {
        return;
    }

    /**
    * Adds the given tags for the given user id.
    *
    * @param integer $uid The uid of the user for whom tags are to be added.
    * @param array $tags contains the tags to be added for the given user.
    */
    static

    function add_tags_to_user($uid, $tags) {
        Logger::log("Enter: function Tag::add_tags_to_user");
        if(!User::user_exist((int) $uid)) {
            throw new PAException(USER_NOT_FOUND, "The user does not exist");
        }
        //Load the updated_tags_id by inserting the given tags to database
        //If the tag is already present in database then it will retrieve that value
        //Else this function will enter the tag into the database and then retrieve the value of tag_id and tag_name.
        if($tags) {
            $tags_id = Tag::load_tag_ids($tags);
        }
        $sql = 'DELETE FROM {tags_users} WHERE user_id = ?';
        $data = array(
            $uid,
        );
        Dal::query($sql, $data);
        if($tags_id) {
            foreach($tags_id as &$value) {
                $sql = 'INSERT into {tags_users} (tag_id, user_id) values (?, ?)';
                $data = array(
                    $value,
                    $uid,
                );
                Dal::query($sql, $data);
            }
        }
        Logger::log("Exit: function Tag::add_tags_to_user");
        return;
    }

    /**
    * Adds the given tags for the given content id.
    *
    * @param integer $cid The cid of the content for which tags are to be added.
    * @param array $tags contain the tags to be added to the given content.
    */
    static

    function add_tags_to_content($cid, $tags) {
        Logger::log("Enter: function Tag::add_tags_to_content");
        // TODO: Uncomment the following lines when the Content class implements the function below
        //Load the updated_tags_id by inserting the given tags to database
        //If the tag is already present in database then it will retrieve that value
        //Else this function will enter the tag into the database and then retrieve the value of tag_id and tag_name.
        $tags_id = Tag::load_tag_ids($tags);
        $sql = 'DELETE FROM {tags_contents} WHERE content_id = ?';
        $data = array(
            $cid,
        );
        Dal::query($sql, $data);
        if($tags_id) {
            foreach($tags_id as &$value) {
                $sql = 'INSERT into {tags_contents} (tag_id,content_id) values (?, ?)';
                $data = array(
                    $value,
                    $cid,
                );
                Dal::query($sql, $data);
            }
        }
        Logger::log("Exit: function Tag::add_tags_to_content");
        return;
    }

    /**
    * Adds the given tags for the given contentcollection.
    *
    * @param integer $cc The contentcollection id of the contentcollection for which tags to be loaded.
    * @param array $tags contain the tags added to the given contentcollection.
    */
    static

    function add_tags_to_content_collection($cc, $tags = NULL) {
        Logger::log("Enter: function Tag::add_tags_to_content_collection");
        //Load the updated_tags_id by inserting the given tags to database
        //If the tag is already present in database then it will retrieve that value
        //Else this function will enter the tag into the database and then retrieve the value of tag_id and tag_name.
        $tags_id = NULL;
        if($tags) {
            $tags_id = Tag::load_tag_ids($tags);
        }
        $sql = 'DELETE FROM {tags_contentcollections} WHERE collection_id = ?';
        $data = array(
            $cc,
        );
        Dal::query($sql, $data);
        if($tags_id) {
            foreach($tags_id as &$value) {
                $sql = 'INSERT into {tags_contentcollections} (tag_id,collection_id) values (?, ?)';
                $data = array(
                    $value,
                    $cc,
                );
                Dal::query($sql, $data);
            }
        }
        Logger::log("Exit: function Tag::add_tags_to_content_collection");
        return;
    }

    /**
    * Adds the given tags for the given network.
    *
    * @param integer $network_id The network id 
    * @param array $tags contain the tags added to the given network.
    */
    static

    function add_tags_to_network($network_id, $tags) {
        Logger::log("Enter: function Tag::add_tags_to_network");
        //Load the updated_tags_id by inserting the given tags to database
        //If the tag is already present in database then it will retrieve that value
        //Else this function will enter the tag into the database and then retrieve the value of tag_id and tag_name.
        if($tags) {
            $tags_id = Tag::load_tag_ids($tags);
        }
        $sql = 'DELETE FROM {tags_networks} WHERE network_id = ?';
        $data = array(
            $network_id,
        );
        Dal::query($sql, $data);
        if($tags_id) {
            foreach($tags_id as $value) {
                $sql = 'INSERT into {tags_networks} (tag_id,network_id) values (?, ?)';
                $data = array(
                    $value,
                    $network_id,
                );
                Dal::query($sql, $data);
            }
        }
        Logger::log("Exit: function Tag::add_tags_to_network");
        return;
    }

    /**
    * Load the tags id for the given user_id.
    *
    * @param integer uid of the user whose tags need to be loaded.
    * @return array Returns the array tags_id_name for a particular user.Contain the tag id and tag name.
    * like $tag_ids_name[0]['id'] contain tag id
    * like $tag_ids_name[0]['name'] contain tag name
    */
    static

    function load_tags_for_user($uid) {
        Logger::log("Enter: function Tag::load_tags_for_user with user id :: $uid");
        $tags_id_name = array();
        $i            = 0;
        $sql          = "SELECT T.tag_id AS tag_id, T.tag_name AS tag_name FROM {tags} AS T, {tags_users} AS TS WHERE
     TS.user_id = ? AND T.tag_id = TS.tag_id";
        $data = array(
            $uid,
        );
        $res = Dal::query($sql, $data);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $tags_id_name[$i] = array(
                'id' => $row->tag_id,
                'name' => stripslashes($row->tag_name),
            );
            $i++;
        }
        Logger::log("Exit: function Tag::load_tags_for_user");
        return $tags_id_name;
    }

    /**
    * Load the tags_id for the given cid
    *
    * @param integer cid of the content which tags needs to be loaded
    * @return array Returns the array tags_id_name for a particular content.Contain the tag id and tag name.
    * like $tag_ids_name[0]['id'] contain tag id
    * like $tag_ids_name[0]['name'] contain tag name
    */
    static

    function load_tags_for_content($cid) {
        Logger::log("Enter: function Tag::load_tags_for_content with content id :: $cid");
        $tags_id_name = array();
        $i            = 0;
        $sql          = "SELECT T.tag_id AS tag_id, T.tag_name AS tag_name FROM {tags} AS T, {tags_contents} AS TC WHERE
     TC.content_id = ? AND T.tag_id = TC.tag_id";
        $data = array(
            $cid,
        );
        $res = Dal::query($sql, $data);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $tags_id_name[$i] = array(
                'id' => $row->tag_id,
                'name' => stripslashes($row->tag_name),
            );
            $i++;
        }
        Logger::log("Exit: function Tag::load_tags_for_content");
        return $tags_id_name;
    }

    /**
    * Load the tags_id for the given content collection
    *
    * @param integer contentcollection id of the contentcollection whose tags needs to be loaded
    * @return array Returns the array tags_id_name for a particular content collection.Contain the tag id and tag
    * name.
    * like $tag_ids_name[0]['id'] contain tag id
    * like $tag_ids_name[0]['name'] contain tag name
    */
    static

    function load_tags_for_content_collection($cc) {
        Logger::log("Enter: function Tag::load_tags_for_content_collection with content collection :: $cc");
        $tags_id_name = array();
        $i            = 0;
        $sql          = "SELECT T.tag_id AS tag_id, T.tag_name AS tag_name FROM {tags} AS T, {tags_contentcollections} AS TC WHERE
     TC.collection_id = ? AND T.tag_id = TC.tag_id";
        $data = array(
            $cc,
        );
        $res = Dal::query($sql, $data);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $tags_id_name[$i] = array(
                'id' => $row->tag_id,
                'name' => stripslashes($row->tag_name),
            );
            $i++;
        }
        Logger::log("Exit: function Tag::load_tags_for_content_collection ");
        return $tags_id_name;
    }

    /**
    * Load the tags_id for the given network
    *
    * @param integer nid of the network which tags needs to be loaded
    * @return array Returns the array tags_id_name for a particular network.Contain the tag id and tag name.
    * like $tag_ids_name[0]['id'] contain tag id
    * like $tag_ids_name[0]['name'] contain tag name
    */
    static

    function load_tags_for_network($nid) {
        Logger::log("Enter: function Tag::load_tags_for_network with network id :: $nid");
        $tags_id_name = array();
        $i            = 0;
        $sql          = "SELECT T.tag_id AS tag_id, T.tag_name AS tag_name FROM {tags} AS T, {tags_networks} AS TN WHERE
     TN.network_id = ? AND T.tag_id = TN.tag_id";
        $data = array(
            $nid,
        );
        $res = Dal::query($sql, $data);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $tags_id_name[$i] = array(
                'id' => $row->tag_id,
                'name' => stripslashes($row->tag_name),
            );
            $i++;
        }
        Logger::log("Exit: function Tag::load_tags_for_network");
        return $tags_id_name;
    }

    /**
    * Load the updated_tags_id by inserting the given tags to database
    * If the tag is already present in database then it will retrieve that value
    * Else this function will enter the tag into the database and then retrieve the value of tag_id and tag_name.
    *
    * @param array tags contain the tags whose tag_id needs to be returned
    *
    * @return array
    */
    static

    function load_tag_ids($tags) {
        Logger::log("Enter: function Tag::load_tag_ids");
        $updated_tags_id = array();
        $num = count($tags);
        for($i = 0; $i <= ($num-1); $i++) {
            $sql = 'SELECT tag_id FROM {tags} WHERE tag_name= ?';
            $data = array(
                $tags[$i],
            );
            $res = Dal::query($sql, $data);
            if($res->numRows() > 0) {
                $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
                $updated_tags_id[$i] = $row->tag_id;
            }
            else {
                $sql = 'INSERT into {tags} (tag_name) values (?)';
                $data = array(
                    strtolower($tags[$i]),
                );
                Dal::query($sql, $data);
                //find last insert id
                $updated_tags_id[$i] = Dal::insert_id();
            }
        }
        Logger::log("Exit: function Tag::load_tag_ids");
        return $updated_tags_id;
    }

    /**
    * Load the all users, content and contentcollections associated to a given tag.
    *
    * @param string $tag_name the name of the tag.
    *
    * @return array $all_values contains all the items associated to a given tag
    * like $all_values["user"][0]['id'] contains user id.
    * like $all_values["user"][0]['name'] contains user login_name.
    * like $all_values["microcontent"][0]['id'] contains microcontent id.
    * like $all_values["microcontent"][0]['name'] contains microcontent title.
    * like $all_values["contentcollection"][0]['id'] contains collection id.
    * like $all_values["contentcollection"][0]['name'] contains collection name.
    */
    //static function all_related_type($tag_name) {
    //changed on 20 dec 05 by Arvind
    static

    function get_all_related_types($tag_name) {
        Logger::log("Enter: function Tag::all_related_type with tag name :: $tag_name");
        $user_ids               = array();
        $content_ids            = array();
        $content_collection_ids = array();
        $i                      = 0;
        // Retrieving the tag id of given tag.
        Logger::log("Retrieving the tag id of givin tag with tag name :: $tag_name");

        # TODO here we can use query_one
        $sql = "SELECT * FROM {tags} WHERE tag_name = ?";
        $data = array(
            $tag_name,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            $tag_id = $row->tag_id;
        }
        else {
            throw new PAException(TAG_NOT_EXIST, 'The tag you have given is not present in database');
        }
        // Retrieving user ids associated with this tag.
        Logger::log("Retrieving the user ids associated with tag id :: $tag_id");
        $sql = "SELECT U.user_id as user_id,U.login_name as login_name,U.is_active as is_active FROM {tags_users} as TU, {users} as U WHERE tag_id = ? AND TU.user_id = U.user_id AND U.is_active = 1";
        $data = array(
            $tag_id,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            // Inserting the user id and login name in the array.
            Logger::log("Inserting the user id and login name in the array");
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $all_values["user"][$i] = array(
                    'id' => $row->user_id,
                    'name' => $row->login_name,
                );
                $i++;
            }
        }
        // Retrieving content ids associated with this tag.
        Logger::log("Retrieving the content ids associated with tag id :: $tag_id");
        $i = 0;
        $sql = "SELECT C.content_id as content_id,C.title as title,C.is_active as is_active FROM {tags_contents} as TC, {contents} as C WHERE tag_id = ? AND TC.content_id = C.content_id AND C.is_active = 1";
        $data = array(
            $tag_id,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            // Inserting the content id and title in the array.
            Logger::log("Inserting the content id and title in the array");
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $all_values["microcontent"][$i] = array(
                    'id' => $row->content_id,
                    'name' => $row->title,
                );
                $i++;
            }
        }
        // Retrieving content collection ids associated with this tag.
        Logger::log("Retrieving the content cllection ids associated with tag id :: $tag_id");
        $i = 0;
        $sql = "SELECT CC.collection_id as collection_id, CC.title as name, CC.is_active as is_active FROM {tags_contentcollections} as TCC, {contentcollections} as CC WHERE tag_id = ? AND TCC.collection_id = CC.collection_id";
        $data = array(
            $tag_id,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            // Inserting the content collection id and name in array.
            Logger::log("Inserting the content collection id and name in array");
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $all_values["contentcollection"][$i] = array(
                    'id' => $row->collection_id,
                    'title' => $row->title,
                );
                $i++;
            }
        }
        Logger::log("Exit: function Tag::all_related_type");
        return $all_values;
    }

    /**
    * Load the content_id by searching the given tags
    *
    * @param String tag contain the tag by which the search is to be made
    *
    * @return array
    */
    //static function get_content_ids($tag) {
    //changed on 20 dec 05 by Arvind
    // Modified by Saurabh on 30 August 2007
    static

    function get_associated_content_ids($tag, $cnt = FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC') {
        Logger::log("Enter: function Tag::get_associated_content_ids");
        if(is_numeric($tag)) {
            $cond_array = 'T.tag_id=?
              AND TC.tag_id=T.tag_id
              AND TC.content_id=C.content_id
              AND C.is_active=1';
            $data = array(
                $tag,
            );
        }
        else {
            $cond_array = 'T.tag_name LIKE ?
              AND TC.tag_id=T.tag_id
              AND TC.content_id=C.content_id
              AND C.is_active=1 GROUP BY TC.content_id';
            $tag_name = '%'.$tag.'%';
            $data = array(
                $tag_name,
            );
        }
        $sql      = " SELECT TC.content_id,T.tag_name,C.title
              FROM {tags} AS T,
              {tags_contents} AS TC,
              {contents} AS C
              WHERE 1";
        $sql      = (!empty($cond_array)) ? $sql.' AND '.$cond_array : $sql;
        $order_by = $sort_by.' '.$direction;
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql .= " $limit";
        $res = Dal::query($sql, $data);
        if($cnt) {
            return $res->numRows();
        }
        $contents = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $contents[] = array(
                    'id'         => $row->content_id,
                    'name'       => $row->title,
                    'content_id' => $row->content_id,
                );
            }
        }
        Logger::log("Exit: function Tag::get_associated_content_ids");
        return $contents;
    }

    /**
    * Load the content_collection_id by searching the given tags
    *
    * @param String tag contain the tag by which the search is to be made
    *
    * @return array
    */
    //static function get_contentcollection_ids($tag) {
    //changed on 20 dec 05 by Arvind
    static

    function get_associated_contentcollectionids($tag, $cnt = FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC') {
        Logger::log("Enter: function Tag::get_contentcollection_ids");
        if(is_numeric($tag)) {
            $cond_array = 'T.tag_id=?
              AND TCC.tag_id=T.tag_id
              AND TCC.collection_id=CC.collection_id
              AND CC.is_active=1';
            $data = array(
                $tag,
            );
        }
        else {
            $cond_array = 'T.tag_name LIKE ?
              AND TCC.tag_id=T.tag_id
              AND TCC.collection_id=CC.collection_id
              AND CC.is_active=1 GROUP BY TCC.collection_id';
            $tag_name = '%'.$tag.'%';
            $data = array(
                $tag_name,
            );
        }
        $sql      = " SELECT TCC.collection_id,T.tag_name,CC.title
              FROM {tags} AS T,
              {tags_contentcollections} AS TCC,
              {contentcollections} AS CC
              WHERE 1";
        $sql      = (!empty($cond_array)) ? $sql.' AND '.$cond_array : $sql;
        $order_by = $sort_by.' '.$direction;
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql .= " $limit";
        $res = Dal::query($sql, $data);
        if($cnt) {
            return $res->numRows();
        }
        $contentcollections = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $contentcollections[] = array(
                    'id' => $row->collection_id,
                    'name' => $row->title,
                );
            }
        }
        Logger::log("Exit: function Tag::get_contentcollection_ids");
        return $contentcollections;
    }

    /**
    * Load the user_id by searching the given tags
    *
    * @param String tag contain the tag by which the search is to be made
    *
    * @return array
    */
    //static function get_tag_user_ids($tag) {
    //changed on 20 dec 05 by Arvind
    // Modified by saurabh on 30 Aug 07
    static

    function get_associated_userids($tag, $cnt = FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC') {
        Logger::log("Enter: function Tag::get_associated_userids");
        if(is_numeric($tag)) {
            $cond_array = 'T.tag_id = ?
                AND TU.tag_id=T.tag_id
                AND TU.user_id=U.user_id
                AND U.is_active=1 GROUP BY TU.user_id';
            $data = array(
                $tag,
            );
        }
        else {
            $cond_array = 'T.tag_name LIKE ?
                AND TU.tag_id=T.tag_id
                AND TU.user_id=U.user_id
                AND U.is_active=1 GROUP BY TU.user_id';
            $tag_name = '%'.$tag.'%';
            $data = array(
                $tag_name,
            );
        }
        $sql      = "SELECT T.tag_id,TU.user_id,U.login_name,T.tag_name
              FROM {tags} AS T,
              {tags_users} AS TU,
              {users} AS U
              WHERE 1";
        $sql      = (!empty($cond_array)) ? $sql.' AND '.$cond_array : $sql;
        $order_by = $sort_by.' '.$direction;
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $res = Dal::query($sql, $data);
        if($cnt) {
            return $res->numRows();
        }
        $users = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $users[] = array(
                    'id' => $row->user_id,
                    'name' => $row->tag_name,
                );
            }
        }
        Logger::log("Exit: function Tag::get_associated_userids");
        return $users;
    }

    /**
    * Delete the tags of a given content.
    *
    * @param integer id of the content
    */
    //static function delete_tags_for_contents($cid) {
    //changed on 20 dec 05 by Arvind
    static

    function delete_tags_for_content($cid) {
        Logger::log("Enter: function Tag::delete_tags_for_contents");
        $sql = 'DELETE FROM {tags_contents} WHERE content_id = ?';
        $data = array(
            $cid,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: function Tag::delete_tags_for_contents");
        return;
    }

    /**
    * Delete the tags of a given content collection.
    *
    * @param integer id of the content
    */
    //static function delete_tags_for_contentcollections($ccid) {
    //changed on 20 dec 05 by Arvind
    static

    function delete_tags_for_contentcollection($ccid) {
        Logger::log("Enter: function Tag::delete_tags_for_contentcollections");
        $sql = 'DELETE FROM {tags_contentcollections} WHERE collection_id = ?';
        $data = array(
            $ccid,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: function Tag::delete_tags_for_contentcollections");
        return;
    }

    /**
    * Delete the tags of a given user.
    *
    * @param integer id of the content
    */
    static

    function delete_tags_for_user($uid) {
        Logger::log("Enter: function Tag::delete_tags_for_user");
        $sql = 'DELETE FROM {tags_users} WHERE user_id = ?';
        $data = array(
            $uid,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: function Tag::delete_tags_for_user");
        return;
    }
    static

    function delete_tags_for_network($nid) {
        Logger::log("Enter: function Tag::delete_tags_for_network");
        $sql = 'DELETE FROM {tags_networks} WHERE network_id = ?';
        $data = array(
            $nid,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: function Tag::delete_tags_for_network");
        return;
    }

    /**
    * get tag name from tag_id
    *
    * @param integer id of the tag
    * @return tag name
    */
    static

    function get_tag_name($tag_id) {
        Logger::log("Enter: function Tag::get_tag_name with tag_id :: $tag_id");
        $sql = "SELECT tag_name FROM {tags}  WHERE tag_id = ?";
        $data = array(
            $tag_id,
        );
        $res      = Dal::query($sql, $data);
        $row      = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $tag_name = stripslashes($row->tag_name);
        Logger::log("Exit: function Tag::get_tag_name");
        return $tag_name;
    }

    /**
  * Show the tags of a content or content collection.
  *
  * @param integer id of the content type
  * @param integer id of the type of content
  * @return array of tag soup
  */
    static

    function load_tag_soup_old($cnt = NULL, $content_or_collection = '', $content_or_collection_type_id = '') {
        //TODO make more generic for message board threads
        Logger::log("Enter: function Tag::load_tag_soup");
        $output    = '';
        $occurence = 0;
        $max       = 0;
        $sql       = "SELECT distinct T.tag_id AS tag_id, T.tag_name AS tag_name FROM {tags} AS T";
        $where     = ' where 1 ';
        if($content_or_collection != '' && $content_or_collection_type_id != '') {
            if($content_or_collection == TAG_TYPE_COLLECTION) {
                $sql .= ' , {tags_contentcollections} AS TCC,{contentcollections} AS CC';
                $where .= " AND T.tag_id=TCC.tag_id
                   AND TCC.collection_id=CC.collection_id
                   AND CC.type='$content_or_collection_type_id'
                 ";
            }
            elseif($content_or_collection == TAG_TYPE_CONTENT) {
                $sql .= ' ,{tags_contents} AS TC,{contents} AS C';
                $where .= " AND T.tag_id=TC.tag_id
                   AND TC.content_id=C.content_id
                   AND  C.type='$content_or_collection_type_id'";
            }
        }
        $sql = $sql.$where.' order by tag_name';
        if($cnt) {
            $sql = $sql." LIMIT $cnt";
        }
        $res = Dal::query($sql);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $occurence = Tag::get_tag_occurence($row->tag_id);
            if($max < $occurence) {
                $max = $occurence;
            }
            $tags_id_name[] = array(
                'occurence' => $occurence,
                'id'        => $row->tag_id,
                'name'      => stripslashes($row->tag_name),
                'size'      => '',
            );
        }
        for($i = 0; $i < count($tags_id_name); $i++) {
            $size = Tag::get_font_size($max, $tags_id_name[$i]['occurence']);
            $tags_id_name[$i]['size'] = $size;
        }
        Logger::log("Exit: function Tag::load_tag_soup");
        return $tags_id_name;
    }
    // this is temporary solution for alpha release only tags from contents are coming
    static

    function load_tag_soup($cnt = NULL) {
        //TODO make more generic for message board threads
        Logger::log("Enter: function Tag::load_tag_soup");
        $output    = '';
        $occurence = 0;
        $max       = 0;
        //query altered by Gurpreet on 28-Nov-2006 for getting tags of active content only.
        $sql = "SELECT count(TC.tag_id) as cnt, TC.tag_id,T.tag_name FROM {tags_contents} AS TC, {tags} AS T, {contents} AS C WHERE T.tag_id=TC.tag_id AND TC.content_id = C.content_id AND C.is_active = ? GROUP BY TC.tag_id ORDER BY cnt DESC";
        //select count(TC.tag_id) as cnt, TC.tag_id,T.tag_name from pa_poc.gama_tags_contents  AS TC,pa_poc.tags AS T WHERE T.tag_id=TC.tag_id group by TC.tag_id order by cnt desc
        if($cnt) {
            $sql = $sql." LIMIT $cnt";
        }
        $res = Dal::query($sql, array(ACTIVE));
        $tags_id_name = array();
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $occurence = $row->cnt;
            if($max < $occurence) {
                $max = $occurence;
            }
            $name = $row->tag_name;
            //$name = Tag::get_tag_name($row->tag_id);
            $tags_id_name[] = array(
                'occurence' => $occurence,
                'id'        => $row->tag_id,
                'name'      => stripslashes($name),
                'size'      => '',
            );
        }
        if(!empty($tags_id_name)) {
            for($i = 0; $i < count($tags_id_name); $i++) {
                $size = Tag::get_font_size($max, $tags_id_name[$i]['occurence']);
                $tags_id_name[$i]['size'] = $size;
            }
        }
        Logger::log("Exit: function Tag::load_tag_soup");
        return $tags_id_name;
    }

    /**
  * Find the occurence of the tag.
  *
  * @param integer id tag_id
  * @return total occurence of tag
  */
    private function get_tag_occurence($tag_id) {
        $total = 0;

        /* commented because older versions dont support UNION
        
        $sql = " SELECT count(*) as cnt FROM {tags_contents} WHERE tag_id='$tag_id'
                 UNION
                 SELECT count(*) as cnt FROM {tags_contentcollections} WHERE tag_id='$tag_id'
                 UNION
                 SELECT count(*)  as cnt FROM {tags_users} WHERE tag_id='$tag_id'
               ";

        $res = Dal::query($sql);
        while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
          $total += $row->cnt;
        }
        */
        //to do find a better way to avoid three queries
        $sql    = " SELECT count(*) as cnt FROM {tags_contents} WHERE tag_id='$tag_id'";
        $res    = Dal::query($sql);
        $row    = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $total += $row->cnt;
        $sql    = " SELECT count(*) as cnt FROM {tags_contentcollections} WHERE tag_id='$tag_id'";
        $res    = Dal::query($sql);
        $row    = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $total += $row->cnt;
        $sql    = "SELECT count(*)  as cnt FROM {tags_users} WHERE tag_id='$tag_id'";
        $res    = Dal::query($sql);
        $row    = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $total += $row->cnt;
        return $total;
    }

    /**
    * Find the font size of the tag based on popularity.
    *
    * @param integer $max maximum occurence of any tag
    * @param integer $occurence occurence of the tag
    * @return integer $font_size
    */
    private function get_font_size($max, $occurence) {
        $default_size = DEFAULT_TAG_SOUP_SIZE;
        if($max == 0) {
            $size = $default_size;
        }
        if($occurence == 0) {
            $size = $default_size;
        }
        if($max != 0 && $occurence != 0) {
            $span = ceil(($occurence/$max)*100);
            if($span > 0 && $span <= 20) {
                $size = $default_size+1;
            }
            if($span > 20 && $span <= 40) {
                $size = $default_size+2;
            }
            if($span > 40 && $span <= 60) {
                $size = $default_size+3;
            }
            if($span > 60 && $span <= 80) {
                $size = $default_size+4;
            }
            if($span > 80 && $span <= 100) {
                $size = $default_size+5;
            }
        }
        return $size;
    }

    /**
     * Load the recently added tags
     *
     * @param integer contentcollection id of the contentcollection whose tags needs to be loaded
     * @return array Returns the array tags_id_name.Contain the tag id and tag
     * name.
     * like $tag_ids_name[0]['id'] contain tag id
     * like $tag_ids_name[0]['name'] contain tag name
     */
    static

    function load_recent_tags($limit) {
        Logger::log("Enter: function Tag::load_recent_tags with tag_id upto limit :: $limit");
        $tags_id_name = array();
        $i            = 0;
        $sql          = "SELECT T.tag_id AS tag_id, T.tag_name AS tag_name FROM {tags} AS T LIMIT 0, ?";
        $data = array(
            $limit,
        );
        $res = Dal::query($sql, $data);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $tags_id_name[$i] = array(
                'id' => $row->tag_id,
                'name' => stripslashes($row->tag_name),
            );
            $i++;
        }
        Logger::log("Exit: function Tag::load_recent_tags ");
        return $tags_id_name;
    }
    static

    function get_all_content_on_tag_basis($tag_name) {
        Logger::log("Enter: function Tag::get_all_content_on_tag_basis with tag name :: $tag_name");
        $user_ids               = array();
        $content_ids            = array();
        $content_collection_ids = array();
        $i                      = 0;
        // Retrieving the tag id of given tag.
        Logger::log("Retrieving the tag id of givin tag with tag name :: $tag_name");

        # TODO here we can use query_one
        $sql = "SELECT * FROM {tags} WHERE tag_name = ?";
        $data = array(
            $tag_name,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            $tag_id = $row->tag_id;
        }
        else {
            throw new PAException(TAG_NOT_EXIST, 'The tag you have given is not present in database');
        }
        // Retrieving content ids associated with this tag.
        Logger::log("Retrieving the content ids associated with tag id :: $tag_id");
        $i = 0;
        $sql = "SELECT DISTINCT C.content_id as content_id,C.title as title, C.body as body, C.author_id as author_id, C.changed as changed FROM {tags_contents} as TC, {contents} as C WHERE tag_id = ? AND TC.content_id = C.content_id AND C.is_active = 1";
        $data = array(
            $tag_id,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            // Inserting the content id and title in the array.
            Logger::log("Inserting the content id and title in the array");
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $all_values[$i] = array(
                    'content_id' => $row->content_id,
                    'title'      => $row->title,
                    'body'       => stripslashes($row->body),
                    'author_id'  => $row->author_id,
                    'changed'    => $row->changed,
                );
                $i++;
            }
        }
        Logger::log("Exit: function Tag::get_all_content_on_tag_basis");
        return $all_values;
    }
    // Split a string containing tags into an array of tags
    // eg: split_tags(" one, two  , one two three four,five") -> array("one", "two", "one two three four", "five");
    static

    function split_tags($tag_input) {
        $tags = explode(',', $tag_input);
        $terms = array();
        foreach($tags as $term) {
            $tr = trim($term);
            if($tr) {
                $terms[] = $tr;
            }
        }
        return $terms;
    }

    public static function tag_array_to_html($tag_array, $is_group = NULL) {
        //$is_group is added because now we dont show groups according to tags
        if(!$tag_array) {
            return "";
        }
        $href = NULL;
        $t = array();
        foreach($tag_array as $tag) {
            if($is_group) {
                $href = PA::$url.'/'.FILE_TAG_SEARCH.'?name_string=group_tag&keyword='.$tag['name'];
                $t[] = '<a href="'.$href.'">'.$tag['name'].'</a>';
            }
            else {
                $href = PA::$url.'/'.FILE_TAG_SEARCH.'?name_string=content_tag&keyword='.$tag['name'];
                $t[] = '<a href="'.$href.'">'.$tag['name'].'</a>';
            }
        }
        return "<b>Tags:</b> ".implode(", ", $t);
    }
}
?>
