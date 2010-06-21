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
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/Content/Content.php";
require_once "api/Logger/Logger.php";

/**
 * Implements Audio.
 * @extends Content
 * @author Tekriti Software (www.TekritiSoftware.com)
 */
class Audio extends Content {

    public $file_name;

    public $file_perm;

    /**
     * class Content::__construct
     */
    public function __construct() {
        parent::__construct();
        //TODO: move to constants
        $this->type = AUDIO;
        $this->is_html = 0;
    }

    /**
   * load Audio data in the object
   * @access public
   * @param content id of the Audio.
   * @param $user_id user id of the person who is accessing data
   * @param $my_user_id user id of the person whose data is loading
   */
    public function load($content_id, $user_id = 0, $my_user_id = 0) {
        Logger::log("Enter: Audio::load | Arg: $content_id = $content_id");
        Logger::log("Calling: Content::load | Param: $content_id = $content_id");
        parent::load($content_id);
        $sql = "SELECT * FROM {audios} WHERE content_id = $this->content_id";
        $res = Dal::query($sql);
        if($res->numRows() > 0) {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            if($my_user_id != 0) {
                // getting degree 1 friendlist
                $relations = Relation::get_relations($user_id, APPROVED, PA::$network_info->network_id);
                if($user_id == $my_user_id) {
                    $this->audio_file = $row->audio_file;
                    $this->file_name  = $row->audio_file;
                    $this->file_perm  = $row->audio_perm;
                }
                elseif(in_array($my_user_id, $relations)) {
                    if(($row->audio_perm == WITH_IN_DEGREE_1) || ($row->audio_perm == ANYONE)) {
                        $this->audio_file = $row->audio_file;
                        $this->file_name  = $row->audio_file;
                        $this->file_perm  = $row->audio_perm;
                    }
                }
                elseif($my_user_id == 0) {
                    if(($row->audio_perm == WITH_IN_DEGREE_1) || ($row->audio_perm == ANYONE)) {
                        $this->audio_file = $row->audio_file;
                        $this->file_name  = $row->audio_file;
                        $this->file_perm  = $row->audio_perm;
                    }
                }
                else {
                    if($row->audio_perm == ANYONE) {
                        $this->audio_file = $row->audio_file;
                        $this->file_name  = $row->audio_file;
                        $this->file_perm  = $row->audio_perm;
                    }
                }
            }
            elseif($user_id == $my_user_id) {
                $this->audio_file = $row->audio_file;
                $this->file_name  = $row->audio_file;
                $this->file_perm  = $row->audio_perm;
            }
            else {
                if($row->audio_perm == ANYONE) {
                    $this->audio_file = $row->audio_file;
                    $this->file_name  = $row->audio_file;
                    $this->file_perm  = $row->audio_perm;
                }
            }
        }
        Logger::log("Exit: Audio::load");
        return;
    }

    /**
     * load Audio data
     * @access public
     * @param $array_cids an array of Audio Ids.
     * @param $user_id user id of the person who is accessing data
     * @param $my_user_id user id of the person whose data is loading
     */
    public function load_many($array_cids, $user_id = 0, $my_user_id = 0) {
        Logger::log("Enter: Audio::load_many  | Arg: $content_ids = ".implode(",", $array_cids));
        // TO Do: In the one query (wite query in the one string)
        $i = 0;
        foreach($array_cids as $cid) {
            $sql = "SELECT * FROM {contents} AS C, {audios} as A WHERE C.content_id = A.content_id AND A.content_id = ? AND C.is_active = ?";
            $res = Dal::query($sql, array($cid, ACTIVE));
            if($res->numRows() > 0) {
                $row                        = $res->fetchRow(DB_FETCHMODE_OBJECT);
                $audio[$i]['content_id']    = $row->content_id;
                $audio[$i]['audio_file']    = $row->audio_file;
                $audio[$i]['audio_caption'] = $row->title;
                $audio[$i]['title']         = $row->title;
                $audio[$i]['body']          = $row->body;
                $audio[$i]['created']       = $row->created;
                $audio[$i]['perm']          = $row->audio_perm;
            }
            $i++;
        }
        if(!empty($audio) && ($user_id != 0)) {
            // getting degree 1 friendlist
            $relations = Relation::get_relations($user_id, APPROVED, PA::$network_info->network_id);
            if($user_id == $my_user_id) {
                $user_audio_data = $audio;
            }
            elseif(in_array($my_user_id, $relations)) {
                foreach($audio as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
            elseif($my_user_id == 0) {
                foreach($audio as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
            else {
                foreach($audio as $user_data) {
                    if($user_data['perm'] == ANYONE) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
        }
        elseif($user_id == $my_user_id) {
            $user_audio_data = $audio;
        }
        Logger::log("Exit: Audio::load_many()");
        return $user_audio_data;
    }

    /**
     * Saves Audio in database
     * @access public
     */
    public function save() {
        Logger::log("Enter: Audio::save");
        Logger::log("Calling: Content::save");
        //print '<pre>'; print_r($this); exit;
        if($this->content_id) {
            parent::save();
            // if no permission is set then it is Nobody by default
            $this->file_perm = (empty($this->file_perm)) ? NONE : $this->file_perm;
            $sql = "UPDATE {audios} SET audio_perm = ? WHERE content_id = ?";
            $data = array(
                $this->file_perm,
                $this->content_id,
            );
            $res = Dal::query($sql, $data);
            if($this->file_name) {
                $sql = "UPDATE {audios} SET audio_file = ? WHERE content_id = ?";
                $data = array(
                    $this->file_name,
                    $this->content_id,
                );
                $res = Dal::query($sql, $data);
            }
        }
        else {
            $this->display_on = NULL;
            parent::save();
            if(!$this->file_perm) {
                $this->file_perm = 0;
            }
            $sql = "INSERT INTO {audios} (content_id, audio_file, audio_perm) VALUES (?, ?, ?)";
            $data = array(
                $this->content_id,
                $this->file_name,
                $this->file_perm,
            );
            $res = Dal::query($sql, $data);
        }
        Logger::log("Exit: Audio::save");
        return $this->content_id;
    }

    /**
     * calls Content::delete() to soft delete a content
     * Soft delete
     */
    public function delete() {
        Logger::log("Enter: Audio::delete");
        Logger::log("Calling: Content::delete");
        parent::delete();
        Logger::log("Exit: Audio::delete");
        return;
    }

    /**
   *  Load all Audios with its data for a single user.
   * @param $user_id, the user id of the user whose audios to be loaded.
   * @param $limit, limit how many audios should be loaded. If no value is set then it will load all audios
   * @param $user_id user id of the person who is accessing data
   * @param $my_user_id user id of the person whose data is loading
   * @return $audio_data, an associative array, having content_id, audio_file, title, body in it for each audio.
   */
    public static function load_audio($user_id = 0, $limit = 0, $my_user_id = 0) {
        Logger::log("Enter: Audio::load_audio | Arg: $user_id = $user_id");
        Logger::log("Calling: Content::load | Param: $limit = $limit");
        Logger::log("Calling: Content::load | Param: $my_user_id = $my_user_id");
        $i = 0;
        if($user_id == 0) {
            $sql = "SELECT * FROM {contents} AS C, {audios} as I WHERE C.content_id = I.content_id AND I.audio_perm = ? AND C.is_active = ? ORDER by C.created DESC ";
            if($limit != 0) {
                $sql .= "LIMIT $limit";
            }
            $res = Dal::query($sql, array(ANYONE, 1));
        }
        else {
            $sql = "SELECT * FROM {contents} AS C, {audios} as I WHERE C.content_id = I.content_id AND C.author_id = ? AND C.is_active = ? ORDER by C.created DESC ";
            if($limit != 0) {
                $sql .= "LIMIT $limit";
            }
            $res = Dal::query($sql, array($user_id, 1));
        }
        $audio_data = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $audio_data[$i]['content_id']    = $row->content_id;
                $audio_data[$i]['audio_file']    = $row->audio_file;
                $audio_data[$i]['caption']       = $row->title;
                $audio_data[$i]['title']         = $row->title;
                $audio_data[$i]['body']          = $row->body;
                $audio_data[$i]['created']       = $row->created;
                $audio_data[$i]['collection_id'] = $row->collection_id;
                $audio_data[$i]['perm']          = $row->audio_perm;
                $i++;
            }
        }
        $user_audio_data = array();
        if(!empty($audio_data) && ($my_user_id != 0)) {
            // getting degree 1 friendlist
            $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
            if($user_id == $my_user_id) {
                $user_audio_data = $audio_data;
            }
            elseif(in_array($my_user_id, $relations)) {
                foreach($audio_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
            elseif($my_user_id == 0) {
                foreach($audio_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
            else {
                foreach($audio_data as $user_data) {
                    if($user_data['perm'] == ANYONE) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
        }
        elseif(($user_id == $my_user_id) && ($my_user_id != 0)) {
            $user_audio_data = $audio_data;
        }
        elseif($my_user_id == 0 && (!empty($audio_data))) {
            foreach($audio_data as $user_data) {
                if($user_data['perm'] == ANYONE) {
                    $user_audio_data[] = $user_data;
                }
            }
        }
        Logger::log("Exit: Audio::load_audio");
        return $user_audio_data;
    }

    /**
    *  Load all Audios with its data for a single user.
    * @param $user_id, the user id of the user whose audios to be loaded.
    * @param $limit, limit how many audios should be loaded. If no value is set then it will load all audios
    * @return $audio_data, an associative array, having content_id, audio_file, title, body in it for each audio.
    */
    public static function load_audio_with_collection_name($user_id, $limit = 0) {
        Logger::log("Enter: Audio::load_audio | Arg: $content_id = $content_id");
        Logger::log("Calling: Content::load | Param: $content_id = $content_id");
        $i = 0;
        $sql = "SELECT *, C.title as title, C.body as body, CC.title as c_title FROM {contents} AS C, {audios} as I, {contentcollections} as CC WHERE C.content_id = I.content_id AND C.author_id = ? AND CC.collection_id = C.collection_id AND C.is_active = ? ORDER by rand() ";
        if($limit != 0) {
            $sql .= "LIMIT $limit";
        }
        $res = Dal::query($sql, array($user_id, 1));
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $audio_data[$i]['content_id']       = $row->content_id;
                $audio_data[$i]['audio_file']       = $row->audio_file;
                $audio_data[$i]['caption']          = $row->title;
                $audio_data[$i]['title']            = $row->title;
                $audio_data[$i]['body']             = $row->body;
                $audio_data[$i]['created']          = $row->created;
                $audio_data[$i]['collection_id']    = $row->collection_id;
                $audio_data[$i]['collection_title'] = $row->c_title;
                $i++;
            }
        }
        Logger::log("Exit: Audio::load_audio");
        return $audio_data;
    }

    /**
    *  Load all Audios with its data for a single $collection_id or group id.
    * @param $collection_id, the collection id whose audios to be loaded.
    * @param $limit, limit how many audios should be loaded. If no value is set then it will load all images
    * @return $audio_data, an associative array, having content_id, file, title, body in it for each audio.
    */
    public static function load_audios_for_collection_id($collection_id, $limit = 0, $order = "RAND()") {
        Logger::log("Enter: Audio::load_audios_for_collection_id | Arg: $collection_id = $collection_id");
        Logger::log("Calling: Content::load | Param: $collection_id = $collection_id");
        $i = 0;
        $sql = "SELECT * FROM {contents} AS C, {audios} as I WHERE C.content_id = I.content_id AND C.collection_id  = ? AND C.is_active = ? ORDER BY $order ";
        if($limit != 0) {
            $sql .= "LIMIT $limit";
        }
        $data = array(
            $collection_id,
            1,
        );
        $res = Dal::query($sql, $data);
        $audio_data = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $audio_data[$i]['content_id']    = $row->content_id;
                $audio_data[$i]['audio_file']    = $row->audio_file;
                $audio_data[$i]['caption']       = $row->title;
                $audio_data[$i]['title']         = $row->title;
                $audio_data[$i]['author_id']     = $row->author_id;
                $audio_data[$i]['body']          = $row->body;
                $audio_data[$i]['created']       = $row->created;
                $audio_data[$i]['collection_id'] = $row->collection_id;
                $audio_data[$i]['type']          = $row->type;
                $i++;
            }
        }
        Logger::log("Exit: Audio::load_audios_for_collection_id");
        return $audio_data;
    }

    /**
     * function to delete user audios.
     */
    public static function delete_user_audios($user_id, $collection_id = NULL) {
        Logger::log("Enter: Image::delete_user_audios");
        $sql = 'DELETE FROM C, A, TC USING {contents} AS C, {audios} AS A LEFT JOIN {tags_contents} AS TC ON C.content_id = TC.content_id WHERE C.content_id = A.content_id AND C.author_id = ?';
        $data = array(
            $user_id,
        );
        if($collection_id) {
            $sql .= ' AND C.collection_id = ? ';
            $data[] = $collection_id;
        }
        if(!$res = Dal::query($sql, $data)) {
            Logger::log("Image::delete_user_audios function failed");
            throw new PAException(AUDIO_DELETE_FAILED, "User audios delete failed");
        }
        Logger::log("Exit: Image::delete_user_audios");
        return $res;
    }

    /**
      *  Load all Audios with its data for a single user.
      * @param $user_id, the user id of the user whose audios to be loaded.
      * @param $limit, limit how many audios should be loaded. If no value is set then it will load all audios
      * @param $user_id user id of the person who is accessing data
      * @param $my_user_id user id of the person whose data is loading
      * @return $audio_data, an associative array, having content_id, audio_file, title, body in it for each audio.
      */
    public static function load_recent_media_audio($user_id = 0, $my_user_id = 0) {
        Logger::log("Enter: Audio::load_recent_media_audio | Arg: $user_id = $user_id");
        Logger::log("Calling: Content::load | Param: $my_user_id = $my_user_id");
        $i = 0;
        if($user_id == 0) {
            $sql = "SELECT * FROM {contents} AS C, {audios} as I ,{recent_media_track} as R WHERE C.content_id = I.content_id AND I.content_id=R.cid  AND R.type= ? AND I.audio_perm = ? AND C.is_active = ? ORDER by C.created DESC ";
            $res = Dal::query($sql, array(AUDIO, ANYONE, 1));
        }
        else {
            $sql = "SELECT * FROM {contents} AS C, {audios} as I WHERE C.content_id = I.content_id AND I.content_id=R.cid  AND R.type=? AND C.author_id = ? AND C.is_active = ? ORDER by C.created DESC ";
            $res = Dal::query($sql, array(AUDIO, $user_id, 1));
        }
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $audio_data[$i]['content_id']    = $row->content_id;
                $audio_data[$i]['audio_file']    = $row->audio_file;
                $audio_data[$i]['caption']       = $row->title;
                $audio_data[$i]['title']         = $row->title;
                $audio_data[$i]['body']          = $row->body;
                $audio_data[$i]['created']       = $row->created;
                $audio_data[$i]['collection_id'] = $row->collection_id;
                $audio_data[$i]['perm']          = $row->audio_perm;
                $i++;
            }
        }
        $user_audio_data = array();
        if(!empty($audio_data) && ($my_user_id != 0)) {
            // getting degree 1 friendlist
            $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
            if($user_id == $my_user_id) {
                $user_audio_data = $audio_data;
            }
            elseif(in_array($my_user_id, $relations)) {
                foreach($audio_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
            elseif($my_user_id == 0) {
                foreach($audio_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
            else {
                foreach($audio_data as $user_data) {
                    if($user_data['perm'] == ANYONE) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
        }
        elseif(($user_id == $my_user_id) && ($my_user_id != 0)) {
            $user_audio_data = $audio_data;
        }
        elseif($my_user_id == 0 && (!empty($audio_data))) {
            foreach($audio_data as $user_data) {
                if($user_data['perm'] == ANYONE) {
                    $user_audio_data[] = $user_data;
                }
            }
        }
        Logger::log("Exit: Audio::load_recent_media_audio");
        return $user_audio_data;
    }

    /**
    *  Load all Audios of user gallery with its data for a single user.
    * @param $user_id, the user id of the user whose audios to be loaded.
    * @param $limit, limit how many audios should be loaded. If no value is set
      then it will load all audios
    * @param $user_id user id of the person who is accessing data
    * @param $my_user_id user id of the person whose data is loading
    * @return $audio_data, an associative array, having content_id, audio_file,
      title, body in it for each audio.
    */
    public static function load_user_gallery_audio($user_id = 0, $limit = 0, $my_user_id = 0) {
        Logger::log("Enter: Audio::load_user_gallery_audio");
        $i = 0;
        if($user_id == 0) {
            $sql = "SELECT * FROM {contents} AS C, {audios} as I,{contentcollections}
      as CC WHERE C.content_id = I.content_id AND CC.collection_id =
      C.collection_id AND I.audio_perm = ? AND C.is_active = ? AND CC.type = ?
      ORDER by C.created DESC ";
            if($limit != 0) {
                $sql .= "LIMIT $limit";
            }
            $res = Dal::query($sql, array(ANYONE, ACTIVE, ALBUM_COLLECTION_TYPE));
        }
        else {
            $sql = "SELECT * FROM {contents} AS C, {audios} as I, {contentcollections}
      as CC WHERE C.content_id = I.content_id AND CC.collection_id =
      C.collection_id AND C.author_id = ? AND C.is_active = ? AND CC.type = ?
      ORDER by C.created DESC ";
            if($limit != 0) {
                $sql .= "LIMIT $limit";
            }
            $res = Dal::query($sql, array($user_id, ACTIVE, ALBUM_COLLECTION_TYPE));
        }
        $audio_data = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $audio_data[$i]['content_id']    = $row->content_id;
                $audio_data[$i]['audio_file']    = $row->audio_file;
                $audio_data[$i]['caption']       = $row->title;
                $audio_data[$i]['title']         = $row->title;
                $audio_data[$i]['body']          = $row->body;
                $audio_data[$i]['created']       = $row->created;
                $audio_data[$i]['collection_id'] = $row->collection_id;
                $audio_data[$i]['perm']          = $row->audio_perm;
                $i++;
            }
        }
        $user_audio_data = array();
        if(!empty($audio_data) && ($my_user_id != 0)) {
            // getting degree 1 friendlist
            $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
            if($user_id == $my_user_id) {
                $user_audio_data = $audio_data;
            }
            elseif(in_array($my_user_id, $relations)) {
                foreach($audio_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
            elseif($my_user_id == 0) {
                foreach($audio_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
            else {
                foreach($audio_data as $user_data) {
                    if($user_data['perm'] == ANYONE) {
                        $user_audio_data[] = $user_data;
                    }
                }
            }
        }
        elseif(($user_id == $my_user_id) && ($my_user_id != 0)) {
            $user_audio_data = $audio_data;
        }
        elseif($my_user_id == 0 && (!empty($audio_data))) {
            foreach($audio_data as $user_data) {
                if($user_data['perm'] == ANYONE) {
                    $user_audio_data[] = $user_data;
                }
            }
        }
        Logger::log("Exit: Audio::load_user_gallery_audio");
        return $user_audio_data;
    }
}
?>
