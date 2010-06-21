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
require_once 'api/Content/Content.php';
require_once 'api/Logger/Logger.php';
include_once 'api/TekMedia/TekMedia.php';

/**
 * Implements Tekmedia Video.
 * @extends Content
 * @author Tekriti Software (www.TekritiSoftware.com)
 */
class TekVideo extends Content {

    /**
    * This will have the Tek video it that we get as return value after posting the video to Tek.
    */
    public $video_id;

    /**
    * Status of the video same as on the Tekmidea.
    */
    public $status;

    /**
    * Thubnail url of the video same as returned from Tek for the videos those are on line
    */
    public $external_thumbnail;

    /**
      Thumbnail url of video in local system
    */
    public $internal_thumbnail;

    /**
    * Login name of the user who will be uploading the video.
    */
    public $email_id;

    /**
    * Views will have the number of times the video have been viewed. 
    */
    public $views;

    /**
    * Embed tag
    */
    public $embed_tag;

    /**
    * View url 
    */
    public $view_url;

    /**
    * Duration of the video in seconds
    */
    public $duration;

    /**
     * Category id of video
     */
    public $category_id;

    /**
     * Permission of video
     */
    public $video_perm;

    /**
     * class TekVideo::__construct
     */
    public function __construct() {
        parent::__construct();
        $this->type = TEK_VIDEO;
    }

    /**
    * Function used to save some information into our system
    * @access public 
    */
    public function save() {
        Logger::log("Enter: TekVideo::save()");
        $this->video_id = $this->file_name;
        // so that this content will not display on the recent post module
        $this->is_default_content = FALSE;
        $this->type = TEK_VIDEO;
        parent::save();
        if(empty($this->status)) {
            $this->status =-1;
            Tag::add_tags_to_content($this->content_id, $this->tags);
            $sql = 'INSERT INTO {media_videos} (content_id, video_id, email_id, views, author_id, status, video_perm) values (?, ?, ?, ?, ?, ?, ?)';
            Dal::query($sql, array($this->content_id, $this->video_id, $this->email_id, 0, $this->author_id, $this->status, $this->video_perm));
        }
        else {
            $params['key_value'] = array(
                'video_perm' => $this->video_perm,
            );
            $this->update($this->content_id, $params);
        }
        Logger::log("Exit: TekVideo::save()");
        return;
    }

    public function update($id, $params) {
        Logger::log("Enter: TekVideo::update()");
        $param = $params['key_value'];
        $sql = 'UPDATE {media_videos} SET ';
        if(!empty($param)) {
            foreach($param as $key => $value) {
                $sql .= $key.' = ?, ';
                $data[] = $value;
            }
        }
        $sql    = substr($sql, 0,-2);
        $sql   .= ' WHERE content_id = ?';
        $data[] = $id;
        $res    = Dal::query($sql, $data);
        Logger::log("Exit: TekVideo::update()");
    }

    /**
       This function get all the video for a network
       if we pass cat_id for that than it return all the video for that category
    */
    public function get_all($cnt = FALSE, $show = 'ALL', $page = 1, $sort_by = 'C.created', $direction = 'DESC') {
        Logger::log("Enter: TekVideo::get_all()");
        $order_by = $sort_by.' '.$direction;
        //setting limits for pagination
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql = "SELECT V.*, C.* FROM {media_videos} AS V INNER JOIN {contents} AS C ON V.content_id = C.content_id WHERE V.STATUS = 1 AND C.is_active = 1 ORDER BY $order_by $limit";
        $res = Dal::query($sql);
        if($cnt) {
            return $res->numRows();
        }
        if($res->numRows()) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $result_array[] = $row;
            }
        }
        Logger::log("Exit: TekVideo::get_all()");
        return $result_array;
    }

    /**
      This function for verify status of video 
      if status of that videos changes from -1 to 1 than it update entry on our system
    */
    public function verify_status($video_id = NULL, $content_id, $local_status =-1) {
        Logger::log("Enter: TekVideo::verify_status()");
        if(empty($video_id)) {
            return;
        }
        if($local_status == 1) {
            return;
        }
        $tekmedia_obj = new Tekmedia();
        $data = $tekmedia_obj->get_video($video_id);
        if($data['is_active'] == 1) {
            $image_http_path          = $data['thumbnail_url'];
            $this->external_thumbnail = $image_http_path;
            $this->duration           = $data['length_seconds'];
            $this->view_url           = PA::$url.'/'.FILE_MEDIA_FULL_VIEW.'?cid='.$content_id;
            $this->status             = 1;
            $uf_basename              = preg_replace("[^A-Za-z0-9\-_\.]"quote107"_", basename($image_http_path));
            $uf_basename              = $video_id.'_'.$uf_basename;
            //fetch the image via http and write it where an uploaded file would go
            $uploadfile = PA::$upload_path.$uf_basename;
            $this->download($image_http_path, $uploadfile);
            $this->internal_thumbnail = basename($uploadfile);
            $param = array();
            $param['key_value'] = array(
                'external_thumbnail' => $this->external_thumbnail,
                'duration'           => $this->duration,
                'view_url'           => $this->view_url,
                'status'             => $this->status,
                'internal_thumbnail' => $this->internal_thumbnail,
                'content_id'         => $content_id,
            );
            $this->update($content_id, $param);
        }
        Logger::log("Exit: TekVideo::verify_status()");
    }

    /**
      function for downloading the file and create it locally
    */
    public function download($source_url, $file_target) {
        // Preparations
        $source_url = str_replace(' ', '%20', html_entity_decode($source_url));
        // fix url format
        if(file_exists($file_target)) {
            chmod($file_target, 0777);
        }
        // add write permission
        // Begin transfer
        if(($rh = fopen($source_url, 'rb')) === FALSE) {
            return false;
        }
        // fopen() handles
        if(($wh = fopen($file_target, 'wb')) === FALSE) {
            return false;
        }
        // error messages.
        while(!feof($rh)) {
            // unable to write to file, possibly because the harddrive has filled up
            if(fwrite($wh, fread($rh, 1024)) === FALSE) {
                fclose($rh);
                fclose($wh);
                return false;
            }
        }
        // Finished without errors
        fclose($rh);
        fclose($wh);
        return true;
    }

    /**
      This function get all the video for a network
      if we pass cat_id for that than it return all the video for that category
   */
    public function load($cid = NULL) {
        Logger::log("Enter: TekVideo::load()");
        $sql          = 'SELECT * FROM {media_videos} WHERE content_id = ?';
        $res          = Dal::query($sql, $cid);
        $result_array = array();
        if($res->numRows()) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                if(!empty($row->video_id)) {
                    $this->verify_status($row->video_id, $row->content_id, $row->status);
                }
                $this->video_id           = $row->video_id;
                $this->status             = $row->status;
                $this->external_thumbnail = $row->external_thumbnail;
                $this->internal_thumbnail = $row->internal_thumbnail;
                $this->views              = $row->views;

                /*         $this->embed_tag = get_embed_tag($row->video_id);*/
                $this->view_url    = $row->view_url;
                $this->duration    = $row->duration;
                $this->category_id = $row->category_id;
                $this->content_id  = $row->content_id;
                $this->author_id   = $row->author_id;
                $this->video_perm  = $row->video_perm;
                $this->file_perm   = $row->video_perm;
                parent::load($row->content_id);
                $result_array[] = $row;
            }
        }
        Logger::log("Exit: TekVideo::load()");
        return $result_array;
    }

    /**
      Adding function delete for deleting this 
      We are sending a request to the tekmedia server for deleting the content
      But at present we used Softdelete from our side
    */
    function delete_video($content_id) {
        Logger::log("Enter: TekVideo::delete_video()");
        if(empty($content_id)) {
            return true;
        }
        // for deleting this content, retrive all the information of that content
        $this->load($content_id);
        try {
            $video_id = $this->video_id;
            $sql = 'DELETE FROM {media_videos}
                  WHERE content_id = ?';
            $data = array(
                $this->content_id,
            );
            Dal::query($sql, $data);
            // Content_deleted successfully from video table
            Content::delete_by_id($this->content_id);
            // Content deleted successfully from Content table
            $tekmedia_obj = new Tekmedia();
            $tekmedia_obj->delete_video_from_server($video_id);
            // Video deleted successfully from tekmedia server
            // all done - commit to database
            Dal::commit();
        }
        catch(PAException$e) {
            Dal::rollback();
            throw $e;
        }
        Logger::log("Exit: TekVideo::delete_video()");
        return true;
    }

    function get_most_rated_video() {
        // first find that video_id after that load that video
        Logger::log("Enter: TekVideo::get_most_rated_video()");
        global $path_prefix;
        require_once "$path_prefix/api/Rating/Rating.php";
        $rating = new Rating();
        $rating->set_rating_type('content');
        $param['limit'] = 1;
        $data = $rating->get_max_rated_content($this->type, $param, NULL, time());
        if($data[0]->type_id) {
            $video_data = $this->load($data[0]->type_id);
            Logger::log("Exit: TekVideo::get_most_rated_video()");
            return $video_data;
        }
        Logger::log("Exit: TekVideo::get_most_rated_video()");
        return null;
    }

    function get_most_commented_video() {
        Logger::log("Enter: TekVideo::get_most_commented_video()");
        $sql = 'SELECT COUNT(CM.parent_id) AS total_comments, CM.parent_id AS content_id FROM {comments} AS CM INNER JOIN {contents} AS C ON CM.parent_id = C.Content_id WHERE CM.parent_type = "content" AND C.type = 11 AND C.is_active = 1 GROUP BY CM.parent_type ORDER BY total_comments DESC LIMIT 1';
        $res = Dal::query($sql);
        if($res->numRows()) {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            if($row->content_id) {
                $video_data = $this->load($row->content_id);
                Logger::log("Exit: TekVideo::get_most_commented_video()");
                return $video_data;
            }
        }
        Logger::log("Exit: TekVideo::get_most_commented_video()");
        return null;
    }

    function get_video_order_by_comments($cnt = FALSE, $show = 'ALL', $page = 1) {
        Logger::log("Enter: TekVideo::get_video_order_by_comments()");
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql = 'SELECT COUNT(V.content_id) AS total,V.* FROM {comments} AS CM INNER JOIN {media_videos} AS V ON CM.parent_id = V.content_id GROUP BY V.video_id ORDER BY total DESC  '.$limit;
        $res = Dal::query($sql);
        if($cnt) {
            return $res->numRows();
        }
        if($res->numRows()) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $video_data[] = $row;
            }
        }
        Logger::log("Exit: TekVideo::get_video_order_by_comments()");
        return $video_data;
    }

    function get_video_order_by_rating($cnt = FALSE, $show = 'ALL', $page = 1) {
        Logger::log("Enter: TekVideo::get_video_order_by_comments()");
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql = 'SELECT SUM(R.rating) AS total_rating, V.* FROM {rating} as R INNER JOIN {media_videos} as V ON R.type_id = V.content_id WHERE R.rating_type = "content" GROUP BY R.type_id ORDER BY  total_rating DESC '.$limit;
        $res = Dal::query($sql);
        if($cnt) {
            return $res->numRows();
        }
        if($res->numRows()) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $video_data[] = $row;
            }
        }
        Logger::log("Exit: TekVideo::get_video_order_by_comments()");
        return $video_data;
    }

    public static function update_view_count($content_id) {
        Logger::log("Enter: TekVideo::update_view_count()");
        $sql = 'UPDATE {media_videos} SET views=views+1 WHERE content_id = ?';
        Dal::query($sql, $content_id);
        Logger::log("Exit: TekVideo::update_view_count()");
        return true;
    }

    /**
      function which gives all the data by given user_id
    */
    public static function get($params = NULL, $condition = NULL, $count = FALSE) {
        Logger::log("Enter: TekVideo::get()");
        $sort_by   = !empty($params['sort_by']) ? $params['sort_by'] : 'C.created';
        $direction = !empty($params['direction']) ? $params['direction'] : 'DESC';
        $order_by  = $sort_by.' '.$direction;
        //setting limits for pagination
        if(empty($params['show']) || $params['show'] == 'ALL' || $count == TRUE) {
            $limit = '';
        }
        else {
            $page  = !empty($params['page']) ? $params['page'] : 1;
            $start = ($page-1)*$params['show'];
            $limit = 'LIMIT '.$start.','.$params['show'];
        }
        $sql = "SELECT U.first_name, M.*, C.* FROM  {contentcollections} AS CC, {users} AS U, {media_videos} as M INNER JOIN {contents} AS C ON M.content_id = C.content_id WHERE C.is_active = ? AND C.type = ? AND U.user_id = C.author_id AND C.collection_id = CC.collection_id ";
        $data = array(
            ACTIVE,
            TEK_VIDEO,
        );
        if(!empty($condition) && is_array($condition)) {
            foreach($condition as $key => $val) {
                if(is_array($val)) {
                    $string = implode(', ', $val);
                    $sql .= " AND $key IN ($string)";
                }
                else {
                    $sql .= " AND  $key = ?";
                    array_push($data, $val);
                }
            }
        }
        elseif(!empty($condition)) {
            $sql .= $condition;
        }
        $sql .= " ORDER BY $order_by $limit";
        $res = Dal::query($sql, $data);
        if($count) {
            return $res->numRows();
        }
        $result_array = array();
        if($res->numRows()) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                if(!empty($row->video_id)) {
                    $tekvideo = new TekVideo();
                    $tekvideo->verify_status($row->video_id, $row->content_id, $row->status);
                    $row->tags = get_tag_string($row->content_id);
                    $result_array[] = $row;
                }
            }
        }
        Logger::log("Exit: TekVideo::get()");
        return $result_array;
    }

    public static function get_media_recent_tekvideo($user_id = 0, $my_user_id = 0) {
        Logger::log("Enter: TekVideo::get_media_recent_tekvideo()");
        $i = 0;
        $video_data = array();
        if($user_id == 0 || $user_id != $my_user_id) {
            $sql = "SELECT * FROM {contents} AS C, {media_videos} as MI,{recent_media_track} as R WHERE C.content_id = MI.content_id AND MI.content_id=R.cid  AND R.type = ? AND C.is_active = ? AND MI.video_perm = ?";
            $data = array(
                TEK_VIDEO,
                ACTIVE,
                ANYONE,
            );
            $sql .= " ORDER by C.created DESC ";
        }
        else {
            $sql = "SELECT * FROM {contents} AS C, {media_videos} as MI , {recent_media_track} as R WHERE C.content_id = MI.content_id AND MI.content_id=R.cid  AND R.type = ? AND C.author_id = ? AND C.is_active = ? ORDER by C.created DESC ";
            $data = array(
                TEK_VIDEO,
                $user_id,
                1,
            );
        }
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $video_data[$i]['content_id']         = $row->content_id;
                $video_data[$i]['internal_thumbnail'] = $row->internal_thumbnail;
                $video_data[$i]['caption']            = $row->title;
                $video_data[$i]['title']              = $row->title;
                $video_data[$i]['created']            = $row->created;
                $video_data[$i]['collection_id']      = $row->collection_id;
                $video_data[$i]['perm']               = $row->video_perm;
                $video_data[$i]['author_id']          = $row->author_id;
                $i++;
            }
        }
        //      $user_video_data = check_visibility_for_media($video_data, $user_id, $my_user_id);
        Logger::log("Exit: TekVideo::get_media_recent_tekvideo()");
        return $video_data;
    }

    /**
     * Function to get the previous Video in the album, if exists, from the Video which is currently under view.
     */
    public static function get_previous_video($album_id, $video_id, $condition = '') {
        Logger::log("Enter: TekVideo::get_previous_video");
        if(empty($album_id) || empty($video_id)) {
            throw new PAException(BAD_PARAMETER, 'Invalid arguments');
            Logger::log("Exit: TekVideo::get_previous_video");
        }
        $sql = 'SELECT MAX(MV.content_id) FROM {contents} AS C, {media_videos} AS MV WHERE C.collection_id = ? AND C.content_id = MV.content_id AND MV.content_id < ? AND is_active = 1'.$condition.' ORDER BY created';
        $data = array(
            $album_id,
            $video_id,
        );
        $content_id = Dal::query_first($sql, $data);
        Logger::log("Exit: TekVideo::get_previous_video");
        return $content_id;
    }

    /**
   * Function to get the next Video in the album, if exists, from the Video which is currently under view.
   */
    public static function get_next_video($album_id, $video_id, $condition = '') {
        Logger::log("Enter: TekVideo::get_next_video");
        if(empty($album_id) || empty($video_id)) {
            throw new PAException(BAD_PARAMETER, 'Invalid arguments');
            Logger::log("Exit: TekVideo::get_next_video");
        }
        $sql = 'SELECT MIN(MV.content_id) FROM {contents} AS C, {media_videos} AS MV WHERE C.collection_id = ? AND C.content_id = MV.content_id AND MV.content_id > ? AND is_active = 1'.$condition.' ORDER BY created';
        $data = array(
            $album_id,
            $video_id,
        );
        $content_id = Dal::query_first($sql, $data);
        Logger::log("Exit: TekVideo::get_next_video");
        return $content_id;
    }
}
?>