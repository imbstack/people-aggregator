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
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Logger/Logger.php";
require_once "api/PAException/PAException.php";
require_once "api/DB/Dal/Dal.php";

/**
*  This class is used to create different type of albums like image, audio, video albums.
* At the instantiation of this class we will instantiate this like new Album('IMAGE')
*/
class Album extends ContentCollection {

    /**
    * @var string album type
    * @access public
    */
    public $album_type;

    /**
     * contructor, creates database handle
     * @access public
     */
    public function __construct($album_type = 0) {
        Logger::log("Enter: Album::__construct");
        parent::__construct();
        // For album type id is 2
        $this->type = 2;
        $this->album_type = $album_type;
        Logger::log("Exit: Album::__construct");
    }

    /**
     * Destroys a database connection instances upon deletion on object.
     * @access public
     */
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Load a single album from database. This is having all data in $this object.
     * @access private
     */
    public function load($collection_id) {
        Logger::log("Enter: Album::load() ");
        if($this->album_type == 0) {
            $sql = "SELECT album_type_id FROM {contentcollections_albumtype} WHERE contentcollection_id = ? ";
            $data = array(
                $collection_id,
            );
            $res = Dal::query($sql, $data);
            if($res->numRows()) {
                $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
                $this->album_type = $row->album_type_id;
            }
            else {
                Logger::log("Throwing Exception CONTENT_COLLECTION_NOT_FOUND", LOGGER_ERROR);
                throw new PAException(CONTENT_COLLECTION_NOT_FOUND, "This album does not exist");
            }
        }
        parent::load($collection_id);
        Logger::log("Exit: Album::load() ");
        return;
    }

    public function load_first($user_id, $album_type) {
        $this->album_type = $album_type;
        $sql = "SELECT CC.collection_id FROM {contentcollections} as CC, {contentcollections_albumtype} AS CCA WHERE CC.collection_id = CCA.contentcollection_id AND CCA.album_type_id = ? AND CC.is_active = ? AND CC.author_id = ? AND CC.type = ? order by CC.collection_id";
        $data = array(
            $album_type,
            1,
            $user_id,
            2,
        );
        $r = Dal::query_one($sql, $data);
        if(empty($r)) {
            throw new PAException(CONTENT_COLLECTION_NOT_FOUND, "No albums of type $album_type found");
        }
        list($collection_id) = $r;
        $this->load($collection_id);
    }

    /**
    * Load all albums of given type for an user from database.
    * @access private
    * @param int user_id
    * @param int album_type An album type, or NULL to retrieve albums of all types.
    */
    public static function load_all($user_id, $album_type = NULL) {
        Logger::log("Enter: Album::load_all()");
        $data_for_collection = array();
        $i = 0;
        // for album type collection id its type is 2
        if($album_type) {
            $sql = "SELECT * FROM {contentcollections} as CC, {contentcollections_albumtype} AS CCA WHERE CC.collection_id = CCA.contentcollection_id AND CCA.album_type_id = ? AND CC.is_active = ? AND CC.author_id = ? AND CC.type = ? order by collection_id desc";
            $data = array(
                $album_type,
                1,
                $user_id,
                2,
            );
        }
        else {
            $sql = "SELECT * FROM {contentcollections} as CC, {contentcollections_albumtype} AS CCA WHERE CC.collection_id = CCA.contentcollection_id AND CC.is_active = ? AND CC.author_id = ? AND CC.type = ? order by collection_id desc";
            $data = array(
                1,
                $user_id,
                2,
            );
        }
        $res = Dal::query($sql, $data);
        if($res->numRows()) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $data_for_collection[$i]['collection_id'] = $row->collection_id;
                $data_for_collection[$i]['title']         = $row->title;
                $data_for_collection[$i]['type']          = $row->type;
                $data_for_collection[$i]['description']   = stripslashes($row->description);
                $data_for_collection[$i]['created']       = $row->created;
                $data_for_collection[$i]['album_type_id'] = $row->album_type_id;
                $i++;
            }
        }
        Logger::log("Exit: Album::load_all()");
        return $data_for_collection;
    }

    /**
     * Saves Album data to database
     * @access public
     */
    public function save() {
        Logger::log("Enter: Album::save()");
        parent::save();
        // Saving data in contentcollections_albumtype table
        $sql = "INSERT INTO {contentcollections_albumtype} (contentcollection_id, album_type_id) VALUES (?, ?)";
        $data = array(
            (int) $this->collection_id,
            $this->album_type,
        );
        $res = Dal::query($sql, $data);
        Logger::log("Exit: Album::save()");
    }

    /**
     * access user permissions on a Album
     * @access public
     * @param int user_id ID of user trying to perform some operation to group
     * @param constant $access access permission to be checked
     */
    public function check_access($user_id, $access = USER_ACCESS_READ, $content_id = 0) {
        Logger::log("Enter: Album::check_access() | Args: $user_id = $user_id");
        // TO DO: Access permission
        return TRUE;
    }

    /**
    * Delete album
    */
    public function delete() {
        Logger::log("Enter: Album::delete");
        parent::delete();
        // delete from  contentcollections_albumtype table
        /*$sql = "DELETE FROM {contentcollections_albumtype} WHERE contentcollection_id = ? AND album_type_id = ?";
        $data = array($this->collection_id, $this->album_type);
        $res = Dal::query($sql, $data);*/
        Logger::log("Exit: Album::delete");
        return;
    }

    /**
     * Function to check whether the Album name already exists or not. Image, Audio and Video Album    type can have album of the same name.
        @param int $author_id.
        @param int $album_type.
        @param varchar $album_title.
     */
    static

    function album_exists($author_id, $album_title, $album_type) {
        Logger::log("Enter: Album::album_exists");
        $sql = "SELECT C.collection_id FROM {contentcollections} AS C LEFT JOIN {contentcollections_albumtype} AS CA ON C.collection_id = CA.contentcollection_id WHERE C.author_id = ? AND C.title = ? AND CA.album_type_id = ? AND C.is_active = ?";
        $data = array(
            $author_id,
            $album_title,
            $album_type,
            1,
        );
        $res = Dal::query($sql, $data);
        Logger::log("Exit: Album::album_exists");
        if($res->numRows() > 0) {
            throw new PAException(CONTENT_COLLECTION_TITLE_ALREADY_EXIST, "Error: This Album name already exists");
        }
        return;
    }

    /**
     * Function to find an Album by name. 
     Image, Audio and Video Album    type can have album of the same name.
        @param int $author_id.
        @param int $album_type.
        @param varchar $album_title.
     */
    static

    function find_album($author_id, $album_title, $album_type) {
        Logger::log("Enter: Album::find_album");
        $sql = "SELECT C.collection_id FROM {contentcollections} AS C LEFT JOIN {contentcollections_albumtype} AS CA ON C.collection_id = CA.contentcollection_id WHERE C.author_id = ? AND C.title = ? AND CA.album_type_id = ? AND C.is_active = ?";
        $data = array(
            $author_id,
            $album_title,
            $album_type,
            1,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            // it exists
            Logger::log("Exit: Album::find_album found $album_id");
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            return $row->collection_id;
        }
        return false;
    }

    /**
    * function to delete user albums and all data in the albums
    */
    public static function delete_user_albums($user_id) {
        Logger::log("Enter: Album::delete_user_albums");
        $sql = 'UPDATE {contentcollections} SET is_active = ? WHERE type = ? AND author_id = ?';
        $data = array(
            DELETED,
            ALBUM_COLLECTION_TYPE,
            $user_id,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: Album::delete_user_albums");
    }

    public static function get_or_create_default($author_id, $alb_type) {
        if(empty($author_id)) {
            throw new PAException(INVALID_ID, "Empty author_id");
        }
        if(!in_array($alb_type, array(IMAGE_ALBUM, AUDIO_ALBUM, VIDEO_ALBUM))) {
            throw new PAException(INVALID_ID, "Invalid album type: $alb_type");
        }
        // Check for existing album; the default will be the first one.
        $album = new Album();
        try {
            $album->load_first($author_id, $alb_type);
            // If no exception fired, the album already exists.
            Logger::Log("Album::get_or_create_default($alb_type, $author_id) found an existing album: #".$album->collection_id.")");
        }
        catch(PAException$e) {
            if($e->getCode() != CONTENT_COLLECTION_NOT_FOUND) {
                throw $e;
            }
            // Looks like it doesn't exist, so create it.
            $album            = new Album($alb_type);
            $album->author_id = $author_id;
            $album->title     = $album->name = $album->description = PA::$config->default_album_titles[$alb_type];
            $album->save();
            Logger::Log("Album::get_or_create_default($alb_type, $author_id) created an album: ".$album->collection_id);
        }
        return $album;
    }
}
?>
