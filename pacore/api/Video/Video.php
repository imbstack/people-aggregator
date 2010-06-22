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
 * Implements Video.
 * @extends Content
 * @author Tekriti Software (www.TekritiSoftware.com)
 */
class Video extends Content {

    public $file_name;

    public $file_perm;

    /**
     * class Content::__construct
     */
    public function __construct() {
        parent::__construct();
        //TODO: move to constants
        $this->type = VIDEO;
        $this->is_html = 0;
    }

    /**
   * load Video data in the object
   * @access public
   * @param content id of the Video.
   * @param $user_id user id of the person who is accessing data
   * @param $my_user_id user id of the person whose data is loading
   */
    public function load($content_id, $user_id = 0, $my_user_id = 0) {
        Logger::log("Enter: Video::load | Arg: $content_id = $content_id");
        Logger::log("Calling: Content::load | Param: $content_id = $content_id");
        parent::load($content_id);
        $sql = "SELECT * FROM {videos} WHERE content_id = $this->content_id";
        $res = Dal::query($sql);
        if($res->numRows() > 0) {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            if($my_user_id != 0) {
                // getting degree 1 friendlist
                $relations = Relation::get_relations($user_id, APPROVED, PA::$network_info->network_id);
                if($user_id == $my_user_id) {
                    $this->video_file = $row->video_file;
                    $this->file_name  = $row->video_file;
                    $this->file_perm  = $row->video_perm;
                }
                elseif(in_array($my_user_id, $relations)) {
                    if(($row->video_perm == WITH_IN_DEGREE_1) || ($row->video_perm == ANYONE)) {
                        $this->video_file = $row->video_file;
                        $this->file_name  = $row->video_file;
                        $this->file_perm  = $row->video_perm;
                    }
                }
                elseif($my_user_id == 0) {
                    if(($row->video_perm == WITH_IN_DEGREE_1) || ($row->video_perm == ANYONE)) {
                        $this->video_file = $row->video_file;
                        $this->file_name  = $row->video_file;
                        $this->file_perm  = $row->video_perm;
                    }
                }
                else {
                    if($row->video_perm == ANYONE) {
                        $this->video_file = $row->video_file;
                        $this->file_name  = $row->video_file;
                        $this->file_perm  = $row->video_perm;
                    }
                }
            }
            elseif($user_id == $my_user_id) {
                $this->video_file = $row->video_file;
                $this->file_name  = $row->video_file;
                $this->file_perm  = $row->video_perm;
            }
            else {
                if($row->video_perm == ANYONE) {
                    $this->video_file = $row->video_file;
                    $this->file_name  = $row->video_file;
                    $this->file_perm  = $row->video_perm;
                }
            }
        }
        Logger::log("Exit: Video::load");
        return;
    }

    /**
     * load Video data
     * @access public
     * @param $array_cids an array of Video Ids.
     * @param $user_id user id of the person who is accessing data
     * @param $my_user_id user id of the person whose data is loading
     * @return $video, an associative array having all data of videos
     */
    public function load_many($array_cids, $user_id = 0, $my_user_id = 0) {
        Logger::log("Enter: Video::load_many | Arg: $content_id = ".implode(",", $array_cids));
        $i = 0;
        foreach($array_cids as $cid) {
            $sql = "SELECT * FROM {contents} AS C, {videos} as V WHERE C.content_id = V.content_id AND V.content_id = ? AND C.is_active = ?";
            $res = Dal::query($sql, array($cid, ACTIVE));
            if($res->numRows() > 0) {
                $row                        = $res->fetchRow(DB_FETCHMODE_OBJECT);
                $video[$i]['content_id']    = $row->content_id;
                $video[$i]['video_file']    = $row->video_file;
                $video[$i]['video_caption'] = $row->title;
                $video[$i]['title']         = $row->title;
                $video[$i]['body']          = $row->body;
                $video[$i]['created']       = $row->created;
                $video[$i]['perm']          = $row->video_perm;
            }
            $i++;
        }
        if(!empty($video) && ($user_id != 0)) {
            // getting degree 1 friendlist
            $relations = Relation::get_relations($user_id, APPROVED, PA::$network_info->network_id);
            if($user_id == $my_user_id) {
                $user_video_data = $video;
            }
            elseif(in_array($my_user_id, $relations)) {
                foreach($video as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
            elseif($my_user_id == 0) {
                foreach($video as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
            else {
                foreach($video as $user_data) {
                    if($user_data['perm'] == ANYONE) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
        }
        elseif($user_id == $my_user_id) {
            $user_video_data = $video;
        }
        Logger::log("Exit: Video::load");
        return $user_video_data;
    }

    /**
     * Saves Video in database
     * @access public
     */
    public function save() {
        Logger::log("Enter: Video::save");
        Logger::log("Calling: Content::save");
        if($this->content_id) {
            parent::save();
            // if no permission is set then it is Nobody by default
            $this->file_perm = (empty($this->file_perm)) ? NONE : $this->file_perm;
            $sql = "UPDATE {videos} SET video_perm = ? WHERE content_id = ?";
            $data = array(
                $this->file_perm,
                $this->content_id,
            );
            $res = Dal::query($sql, $data);
            if($this->file_name) {
                $sql = "UPDATE {videos} SET video_file = ? WHERE content_id = ?";
                $data = array(
                    $this->file_name,
                    $this->content_id,
                );
                $res = Dal::query($sql, $data);
            }
        }
        else {
            $this->display_on = 0;
            parent::save();
            if(!$this->file_perm) {
                $this->file_perm = 0;
            }
            $sql = "INSERT INTO {videos} (content_id, video_file, video_perm) VALUES (?, ?, ?)";
            $data = array(
                $this->content_id,
                $this->file_name,
                $this->file_perm,
            );
            $res = Dal::query($sql, $data);
        }
        Logger::log("Exit: Video::save");
        return $this->content_id;
    }

    /**
     * calls Content::delete() to soft delete a content
     * soft delete
     */
    public function delete() {
        Logger::log("Enter: Video::delete");
        Logger::log("Calling: Content::delete");
        parent::delete();
        Logger::log("Exit: Video::delete");
        return;
    }

    /**
    *  Load all Videos with its data for a single user.
    * @param $user_id, the user id of the user whose videos to be loaded.
    * @param $limit, limit how many videos should be loaded. If no limit is given then it will load all videos.
    * @param $user_id user id of the person who is accessing data
    * @param $my_user_id user id of the person whose data is loading
    * @return $video_data, an associative array, having content_id, video_file, title, body in it for each video.
    */
    public static function load_video($user_id = 0, $limit = 0, $my_user_id = 0) {
        Logger::log("Enter: Video::load_video | Arg: $user_id = $user_id");
        Logger::log("Calling: Content::load | Param: $limit = $limit");
        Logger::log("Calling: Content::load | Param: $my_user_id = $my_user_id");
        $i = 0;
        if($user_id == 0) {
            $sql = "SELECT * FROM {contents} AS C, {videos} as V WHERE C.content_id = V.content_id AND V.video_perm = ? AND C.is_active = ? ORDER by C.created DESC ";
            if($limit != 0) {
                $sql .= "LIMIT $limit";
            }
            $res = Dal::query($sql, array(ANYONE, 1));
        }
        else {
            $sql = "SELECT * FROM {contents} AS C, {videos} as V WHERE C.content_id = V.content_id AND C.author_id = ? AND C.is_active = ? ORDER by C.created DESC ";
            if($limit != 0) {
                $sql .= "LIMIT $limit";
            }
            $res = Dal::query($sql, array($user_id, 1));
        }
        $video_data = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $video_data[$i]['content_id']    = $row->content_id;
                $video_data[$i]['video_file']    = $row->video_file;
                $video_data[$i]['caption']       = $row->title;
                $video_data[$i]['title']         = $row->title;
                $video_data[$i]['body']          = $row->body;
                $video_data[$i]['created']       = $row->created;
                $video_data[$i]['collection_id'] = $row->collection_id;
                $video_data[$i]['perm']          = $row->video_perm;
                $i++;
            }
        }
        $user_video_data = array();
        if(!empty($video_data) && ($my_user_id != 0)) {
            // getting degree 1 friendlist
            $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
            if($user_id == $my_user_id) {
                $user_video_data = $video_data;
            }
            elseif(in_array($my_user_id, $relations)) {
                foreach($video_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
            elseif($my_user_id == 0) {
                foreach($video_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
            else {
                foreach($video_data as $user_data) {
                    if($user_data['perm'] == ANYONE) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
        }
        elseif(($user_id == $my_user_id) && ($my_user_id != 0)) {
            $user_video_data = $video_data;
        }
        elseif($my_user_id == 0 && (!empty($video_data))) {
            foreach($video_data as $user_data) {
                if($user_data['perm'] == ANYONE) {
                    $user_video_data[] = $user_data;
                }
            }
        }
        Logger::log("Exit: Video::load_video");
        return $user_video_data;
    }

    /**
     *  Load all Videos with its data for a single user. with collection title name
     * @param $user_id, the user id of the user whose videos to be loaded.
     * @param $limit, limit how many videos should be loaded. If no limit is given then it will load all videos.
     * @return $video_data, an associative array, having content_id, video_file, title, body in it for each video.
     */
    public static function load_video_with_collection_name($user_id, $limit = 0) {
        Logger::log("Enter: Video::load_video | Arg: $content_id = $content_id");
        Logger::log("Calling: Content::load | Param: $content_id = $content_id");
        $i = 0;
        $sql = "SELECT *, C.title as title, C.body as body, CC.title as c_title FROM {contents} AS C, {videos} as I, {contentcollections} as CC WHERE C.content_id = I.content_id AND C.author_id = ? AND CC.collection_id = C.collection_id AND C.is_active = ? ORDER by rand() ";
        if($limit != 0) {
            $sql .= "LIMIT $limit";
        }
        $res = Dal::query($sql, array($user_id, 1));
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $video_data[$i]['content_id']       = $row->content_id;
                $video_data[$i]['video_file']       = $row->video_file;
                $video_data[$i]['caption']          = $row->title;
                $video_data[$i]['title']            = $row->title;
                $video_data[$i]['body']             = $row->body;
                $video_data[$i]['created']          = $row->created;
                $video_data[$i]['collection_id']    = $row->collection_id;
                $video_data[$i]['collection_title'] = $row->c_title;
                $i++;
            }
        }
        Logger::log("Exit: Video::load_video");
        return $video_data;
    }

    /**
   *  Load all Videos with its data for a single $collection_id or group id.
   * @param $collection_id, the collection id whose videos to be loaded.
   * @param $limit, limit how many videos should be loaded. If no value is set then it will load all images
   * @return $video_data, an associative array, having content_id, file, title, body in it for each video.
   */
    public static function load_videos_for_collection_id($collection_id, $limit = 0, $order = "RAND()") {
        Logger::log("Enter: Video::load_videos_for_collection_id | Arg: $collection_id = $collection_id");
        Logger::log("Calling: Content::load | Param: $collection_id = $collection_id");
        $i = 0;
        $sql = "SELECT * FROM {contents} AS C, {videos} as I WHERE C.content_id = I.content_id AND C.collection_id  = ? AND C.is_active = ? ORDER by $order ";
        if($limit != 0) {
            $sql .= "LIMIT $limit";
        }
        $data = array(
            $collection_id,
            1,
        );
        $res = Dal::query($sql, $data);
        //p($res);
        $video_data = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $video_data[$i]['content_id']    = $row->content_id;
                $video_data[$i]['video_file']    = $row->video_file;
                $video_data[$i]['caption']       = $row->title;
                $video_data[$i]['title']         = $row->title;
                $video_data[$i]['author_id']     = $row->author_id;
                $video_data[$i]['body']          = $row->body;
                $video_data[$i]['created']       = $row->created;
                $video_data[$i]['collection_id'] = $row->collection_id;
                $video_data[$i]['type']          = $row->type;
                $i++;
            }
        }
        Logger::log("Exit: Video::load_videos_for_collection_id");
        return $video_data;
    }

    /**
     * function to delete user videos.
     */
    public static function delete_user_videos($user_id, $collection_id = NULL) {
        Logger::log("Enter: Image::delete_user_videos");
        $sql = 'DELETE FROM C, V, TC USING {contents} AS C, {videos} AS V LEFT JOIN {tags_contents} AS TC ON C.content_id = TC.content_id WHERE C.content_id = V.content_id AND C.author_id = ?';
        $data = array(
            $user_id,
        );
        if($collection_id) {
            $sql .= ' AND C.collection_id = ? ';
            $data[] = $collection_id;
        }
        if(!$res = Dal::query($sql, $data)) {
            Logger::log("Image::delete_user_videos function failed");
            throw new PAException(VIDEO_DELETE_FAILED, "User videos delete failed");
        }
        Logger::log("Exit: Image::delete_user_videos");
        return $res;
    }

    /**
     *  Load all Videos with its data for a single user.
     * @param $user_id, the user id of the user whose videos to be loaded.
     * @param $limit, limit how many videos should be loaded. If no limit is given then it will load all videos.
     * @param $user_id user id of the person who is accessing data
     * @param $my_user_id user id of the person whose data is loading
     * @return $video_data, an associative array, having content_id, video_file, title, body in it for each video.
     */
    public static function load_recent_media_video($user_id = 0, $my_user_id = 0) {
        Logger::log("Enter: Video::load_recent_media_video | Arg: $user_id = $user_id");
        Logger::log("Calling: Content::load | Param: $my_user_id = $my_user_id");
        $i = 0;
        if($user_id == 0) {
            $sql = "SELECT * FROM {contents} AS C, {videos} as V ,{recent_media_track} as R WHERE C.content_id = V.content_id AND V.content_id=R.cid  AND R.type = ? AND V.video_perm = ? AND C.is_active = ? ORDER by C.created DESC ";
            $res = Dal::query($sql, array(VIDEO, ANYONE, 1));
        }
        else {
            $sql = "SELECT * FROM {contents} AS C, {videos} as V ,{recent_media_track} as R WHERE C.content_id = V.content_id AND V.content_id=R.cid  AND R.type=? AND C.author_id = ? AND C.is_active = ? ORDER by C.created DESC ";
            $res = Dal::query($sql, array(VIDEO, $user_id, 1));
        }
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $video_data[$i]['content_id']    = $row->content_id;
                $video_data[$i]['video_file']    = $row->video_file;
                $video_data[$i]['caption']       = $row->title;
                $video_data[$i]['title']         = $row->title;
                $video_data[$i]['body']          = $row->body;
                $video_data[$i]['created']       = $row->created;
                $video_data[$i]['collection_id'] = $row->collection_id;
                $video_data[$i]['perm']          = $row->video_perm;
                $i++;
            }
        }
        $user_video_data = array();
        if(!empty($video_data) && ($my_user_id != 0)) {
            // getting degree 1 friendlist
            $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
            if($user_id == $my_user_id) {
                $user_video_data = $video_data;
            }
            elseif(in_array($my_user_id, $relations)) {
                foreach($video_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
            elseif($my_user_id == 0) {
                foreach($video_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
            else {
                foreach($video_data as $user_data) {
                    if($user_data['perm'] == ANYONE) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
        }
        elseif(($user_id == $my_user_id) && ($my_user_id != 0)) {
            $user_video_data = $video_data;
        }
        elseif($my_user_id == 0 && (!empty($video_data))) {
            foreach($video_data as $user_data) {
                if($user_data['perm'] == ANYONE) {
                    $user_video_data[] = $user_data;
                }
            }
        }
        Logger::log("Exit: Video::load_video");
        return $user_video_data;
    }

    /**
     *  Load all Videos of user gallery with its data for a single user.
     * @param $user_id, the user id of the user whose videos to be loaded.
     * @param $limit, limit how many videos should be loaded. If no limit is given
       then it will load all videos.
     * @param $user_id user id of the person who is accessing data
     * @param $my_user_id user id of the person whose data is loading
     * @return $video_data, an associative array, having content_id, video_file,
       title, body in it for each video.
     */
    public static function load_user_gallery_video($user_id = 0, $limit = 0, $my_user_id = 0) {
        Logger::log("Enter: Video::load_user_gallery_video");
        $i = 0;
        if($user_id == 0) {
            $sql = "SELECT * FROM {contents} AS C, {videos} as V, {contentcollections}
      as CC WHERE C.content_id = V.content_id AND CC.collection_id =
      C.collection_id AND V.video_perm = ? AND C.is_active =? AND CC.type = ?
      ORDER by C.created DESC ";
            if($limit != 0) {
                $sql .= "LIMIT $limit";
            }
            $res = Dal::query($sql, array(ANYONE, ACTIVE, ALBUM_COLLECTION_TYPE));
        }
        else {
            $sql = "SELECT * FROM {contents} AS C, {videos} as V, {contentcollections}
      as CC WHERE C.content_id = V.content_id AND CC.collection_id =
      C.collection_id AND C.author_id = ? AND C.is_active = ? AND CC.type = ?
      ORDER by C.created DESC ";
            if($limit != 0) {
                $sql .= "LIMIT $limit";
            }
            $res = Dal::query($sql, array($user_id, ACTIVE, ALBUM_COLLECTION_TYPE));
        }
        $video_data = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $video_data[$i]['content_id']    = $row->content_id;
                $video_data[$i]['video_file']    = $row->video_file;
                $video_data[$i]['caption']       = $row->title;
                $video_data[$i]['title']         = $row->title;
                $video_data[$i]['body']          = $row->body;
                $video_data[$i]['created']       = $row->created;
                $video_data[$i]['collection_id'] = $row->collection_id;
                $video_data[$i]['perm']          = $row->video_perm;
                $i++;
            }
        }
        $user_video_data = array();
        if(!empty($video_data) && ($my_user_id != 0)) {
            // getting degree 1 friendlist
            $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
            if($user_id == $my_user_id) {
                $user_video_data = $video_data;
            }
            elseif(in_array($my_user_id, $relations)) {
                foreach($video_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
            elseif($my_user_id == 0) {
                foreach($video_data as $user_data) {
                    if(($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
            else {
                foreach($video_data as $user_data) {
                    if($user_data['perm'] == ANYONE) {
                        $user_video_data[] = $user_data;
                    }
                }
            }
        }
        elseif(($user_id == $my_user_id) && ($my_user_id != 0)) {
            $user_video_data = $video_data;
        }
        elseif($my_user_id == 0 && (!empty($video_data))) {
            foreach($video_data as $user_data) {
                if($user_data['perm'] == ANYONE) {
                    $user_video_data[] = $user_data;
                }
            }
        }
        Logger::log("Exit: Video::load_user_gallery_video");
        return $user_video_data;
    }
}
?>
