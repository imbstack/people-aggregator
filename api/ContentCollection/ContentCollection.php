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
 * Base  Class for creating micro-content collection type.
 *
 * Content class will never be instantiated directly. Content will be inherited
 * by micro-content types like BlogPost/Event/Review.
 *
 * @package ContentCollection
 * @author Tekriti Software (http://www.tekritisoftware.com)
 */

/*
 * FIXME: check for is_active flag in every method.
 */
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once dirname(__FILE__)."/../../config.inc";
require_once "api/api_constants.php";
require_once "api/Logger/Logger.php";
require_once "api/PAException/PAException.php";
//require_once "api/Tag/Tag.php";
require_once "api/Content/Content.php";
require_once "api/Logger/Logger.php";

/**
 * ContentCollections are containers of micro-content like groups/forum etc.
 */
class ContentCollection {

  /**
   * @var int collection Id
   */
  public $collection_id;

  /**
   * @var int id of collection type
   */
  public $type;

  /**
   * @var int user_id who created the collection
   */
  public $author_id;


  /**
   * @var string title of collection
   */
  public $title;

  /**
   * @var string description of collection
   */
  public $description;

  /**
   * @var boolean if contented is deleted
   */
  public $is_active;

  public $picture;
  /**
   * @var array tag asociations for collection
   */
  public $tags = array();

  public function __construct() {
    Logger::log("Enter: ContentCollection::__construct");

    Logger::log("Exit: ContentCollection::__construct");
  }

  public function __destruct() {
  }

  /**
   * loads data from database for given content collection ID
   * @access protected
   * @param int $collection_id The content collection Id of a content collection
   */
  public function load($collection_id) {
    Logger::log("Enter: ContentCollection::load() | Args: \$ccid = $collection_id");
    $res = Dal::query("SELECT * FROM {contentcollections} WHERE collection_id = ? AND is_active = ?", array($collection_id, 1));

    if ($res->numRows()) {
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
      foreach ($row as $key => $value) {
        $this->$key = $value;
      }
    }
    else {
      Logger::log("Throwing Exception CONTENT_NOT_FOUND", LOGGER_ERROR);
      throw new PAException(CONTENT_NOT_FOUND, "No such content collection");
    }
    Logger::log("Exit: ContentCollection::load()");
    return;
  }

  public static function findByTitle($title) {
    $enc_title = Dal::quote($title);
    $groups = array();
    $sth = Dal::query("SELECT collection_id,title FROM {contentcollections} WHERE title LIKE '%$enc_title%' AND is_active=1");
    while ($r = Dal::row($sth)) {
      list($ccid, $title) = $r;
      $groups[] = array(
  'ccid' => (int)$ccid,
  'title' => $title,
  );
    }
    return $groups;
  }

  /**
   * saves data to database
   * @access protected
   */
  public function save() {
    Logger::log("Enter: ContentCollection::save()");
    // If same TITLE value exist for same author

    if (empty($this->author_id)) {
      Logger::log('Exit: ContentCollection::save(). Author of the collection is not specified.');
      throw new PAException(BAD_PARAMETER, 'Author of the collection is not specified.');
    }

    $sql = NULL;
    $data = array($this->author_id, $this->title, $this->is_active);
    if (!empty($this->collection_id)) {
      array_push($data, $this->collection_id);
      $sql = 'SELECT collection_id FROM {contentcollections} WHERE author_id = ? AND title = ? AND is_active = ? AND collection_id <> ?';
    } else if (empty($this->type)) {
      Logger::log('Exit: ContentCollection::save(). Collection type is not specified.');
      throw new PAException(BAD_PARAMETER, 'Collection type is not specified.');
    } else if ($this->type == 2) {
      //TODO: Code refining is pending for the album contentcollection.
      $sql = "SELECT collection_id, album_type_id FROM {contentcollections} AS CC, {contentcollections_albumtype} AS CCA WHERE CC.author_id = ? AND CC.title = ? AND CC.collection_id = CCA.contentcollection_id AND CCA.album_type_id = $this->album_type AND is_active = ?";
    } else {
      array_push($data, $this->type);
      $sql = 'SELECT collection_id FROM {contentcollections} WHERE author_id = ? AND title = ? AND is_active = ? AND type = ?';
    }

    $res = Dal::query($sql, $data);

    //TODO: Need to remove the following code. Base call should not dependent on the child classes.
    if ($res->numRows() > 0) {
      if ($this->type == 1) {
        $colletion_name = "group";
      }
      else {
        $colletion_name = "album";
      }

      Logger::log("Throwing Exception CONTENT_COLLECTION_TITLE_ALREADY_EXIST",LOGGER_ERROR);
      throw new PAException(CONTENT_COLLECTION_TITLE_ALREADY_EXIST, "Error: This $colletion_name name already exist");
    }


    //If exists then update else insert
    if ($this->collection_id) {
      $sql = "UPDATE {contentcollections} SET type = ?, author_id = ?, title = ?, description = ?, changed =?, picture = ? WHERE collection_id = ?";
      $data = array($this->type, $this->author_id, $this->title, $this->description, time(), $this->picture, $this->collection_id);
      $res = Dal::query($sql, $data);
    }
    else {
      // get id
      $this->collection_id = Dal::next_id("ContentCollection");
      $this->created = time();
      $this->changed = time();
      $this->is_active = 1;
      $sql = "INSERT INTO {contentcollections} (collection_id, author_id, type, title, description, is_active, created, changed, picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $data = array($this->collection_id, $this->author_id, $this->type, $this->title, $this->description, $this->is_active, $this->created, $this->changed, $this->picture);
      $res = Dal::query($sql, $data);
    }
    Logger::log("Exit: ContentCollection::save()");
    return;
  }

  /**
   * softdeletes content collection
   * @access protected
   */
  protected function delete() {
    Logger::log("Enter: ContentCollection::delete()");
    $sql = "UPDATE {contentcollections} SET is_active = ? WHERE collection_id = ?";
    $data = array(DELETED, $this->collection_id);
    $res = Dal::query($sql, $data);

    $sql = "UPDATE {contents} SET is_active = ? WHERE collection_id = ?";
    $res = Dal::query($sql, array(DELETED, $this->collection_id));

    /*$content = $this->get_contents_for_collection($type = 'all',$cnt=FALSE, $show='ALL');
    foreach ($content as $con) {
      Content::delete_by_id($con['content_id']);
    }*/
    //TODO: need to get discuss about this block of commented code

    Logger::log("Exit: ContentCollection::delete()");
    return;
  }

  public function get_contents_for_collection($type = 'all',$cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC',$is_group=NULL) {
    Logger::log("Enter: ContentCollection::get_contents_for_collection()");

    $sql = "SELECT C.* FROM {contents} AS C ";
    $where = " WHERE 1 AND C.is_active = 1 AND C.collection_id='".$this->collection_id."'";
    if ( $type == 'all' && $is_group) {
      $arr = array(AUDIO,VIDEO,IMAGE, TEK_VIDEO);
      $media_content = implode(',',$arr);
      $where .= ' AND C.type NOT IN ('.$media_content.')';
    }
    if ($type != 'all') {
      $where.=" AND type = $type ";
    }


    $order_by = ' ORDER BY C.'.$sort_by.' '.$direction;

    if ( $show == 'ALL' || $cnt == TRUE) {
       $limit = '';
     } else {
       $start = ($page -1)* $show;
       $limit = 'LIMIT '.$start.','.$show;
     }


    if (!empty($like)) {
      $sql.=" , {contents_sbmicrocontents} As CM
              , {sbmicrocontent_types} As SM ";
      $where.=" AND C.type = 7
                AND CM.content_id = C.content_id
                AND SM.sbtype_id = CM.microcontent_id
                AND SM.name $like ";
    }

    $sql = $sql.$where;
    $sql .= "  $order_by $limit";

    $res = Dal::query($sql);
    if ($cnt) {
      return $res->numRows();
    }
    $contents = array();
    if ( $res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $contents[] = array('content_id' => $row->content_id, 'title' => ($row->title), 'body' => ($row->body), 'author_id' => $row->author_id, 'type' => $row->type, 'created' => $row->created, 'changed' => $row->changed);
      }
    }
    Logger::log("Exit: ContentCollection::get_contents_for_collection()");
    return $contents;
  }

  /**
   * returns collection object, transparently deciding its type
   * @access public
   * @param int collection_id of collection type to be returned
   * @param int user id of user who is accessing this collection.
   */
  public static function load_collection($collection_id, $user_id = NULL) {
    Logger::log("Enter: ContentCollection::load_collection() | Args: \$collection_id = $collection_id, \$user_id = $user_id");
    // global var $path_prefix has been removed - please, use PA::$path static variable

    $sql = "SELECT CCT.name AS collection_type_name FROM {contentcollections} AS CC, {contentcollection_types} AS CCT WHERE CC.collection_id = ? AND CC.type = CCT.type_id";

    $res = Dal::query($sql, array($collection_id));

    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $collection_type = $row['collection_type_name'];

      require_once 'ext/'.$collection_type.'/'.$collection_type.'.php';
      $new_collection = new $collection_type();

      $new_collection->load($collection_id);
//       if (!$new_collection->check_access($user_id)) {
//         throw new PAException(USER_ACCESS_DENIED, "You are not authorized to access this collection");
//       }
    }
    else {
      print $res->numRows();
      throw new PAException(CONTENT_NOT_FOUND, "No such content collection");
    }
    Logger::log("Exit: ContentCollection::load_collecton");
    return $new_collection;
  }

  /**
   * check does requested collection exists
   * @access public
   * @param int collection_id of collection type to be returned
   */
  public static function collection_exists($collection_id) {
    Logger::log("Enter: ContentCollection::collection_exists() | Args: \$collection_id = $collection_id");

    $sql = "SELECT CCT.name AS collection_type_name FROM {contentcollections} AS CC, {contentcollection_types} AS CCT WHERE CC.collection_id = ? AND CC.type = CCT.type_id";
    $res = Dal::query($sql, array($collection_id));

    Logger::log("Exit: ContentCollection::collection_exists()");
    return ($res->numRows() > 0);
  }

  /**
   *  Add a new item to rss_helper object.
   *
   * @param $content_collection_id string The id of the content collection for which Rssfeed is Generated
   * @param $no_of_items string The No of latest items for which Rssfeed is Generated
   * @return $generated_rss_feed string This string contains the content of RssFeed file
   */
  public static function get_feed_for_content_collection($collection_id, $no_of_items = 10) {
    Logger::log("Enter: ContentCollection::get__feed_for_content_collection()");

    if (!$collection_id) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Required variable not specified", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required variable not specified");
    }

    //Query for selecting all items from table cotents for generation of RssFeed

    $sql = 'SELECT C.content_id AS content_id, C.author_id AS author_id, C.type AS type, C.body AS body, C.created AS created, C.changed AS changed, C.collection_id AS ccid FROM {contents} AS C WHERE C.collection_id = ? ORDER BY C.created DESC LIMIT ?';

    $data = array($collection_id, $no_of_items);
    $res = Dal::query($sql, $data);

    // Collecting all $no_of_items rows in an array $row
    while($row[] = $res->fetchRow(DB_FETCHMODE_OBJECT)) {}

    $new_rss = new RssFeedHelper();
    $generated_rss_feed = $new_rss->generate_rss($row);

    Logger::log("Exit: ContentCollection::get__feed_for_content_collection()");
    return $generated_rss_feed;
  }

  /**
   * returns collection details for a user
   * @access public
   * @param int user_id of user.
   */
  public static function get_user_collection ($user_id) {
    Logger::log("Enter: ContentCollection::get_user_collection() | Args: \$user+id = $user_id");

    $res = Dal::query("SELECT collection_id, title, description FROM {contentcollections} WHERE author_id = ? AND is_active = ?", array($user_id, 1));
    $output .= "";
    if ($res->numRows()) {
      $contents = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $collections[] = array('collection_id' => $row->collection_id, 'title' => $row->title, 'description' => $row->des);
      }
      Logger::log("Exit: Content::get_user_content() | Returning ".implode($collections));
      return $collections;
    }
    Logger::log("Exit: ContentCollection::get_user_content() | Returning nothing");
  }

  /**
   * returns type of the collection
   * @access public static
   * @param int collection_id of collection type to be returned
   * usage : this function can be used to check whether it is group or album or anyother type of collection type
   */
  public static function get_collection_type($collection_id) {
    Logger::log("Enter: ContentCollection::get_collection_type() | Args: \$collection_id = $collection_id, ");
    $sql = "SELECT CCT.name AS name ,CCT.type_id AS type FROM {contentcollections} AS CC, {contentcollection_types} AS CCT WHERE CC.collection_id = ? AND CC.type = CCT.type_id";
    $res = Dal::query($sql, array($collection_id));
    $collection_type = array();
    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $collection_type['name'] = $row['name'];
      $collection_type['type'] = $row['type'];
    }
    Logger::log("Exit: ContentCollection::get_collection_type");
    return $collection_type;
  }

  /**
  * function to soft delete the collections for a given user.
  */


  protected static function delete_user_collection( $user_id ) {
    Logger::log("Enter: ContentCollection::delete_user_collection");

    $sql = 'UPDATE {contentcollections} SET is_active = ? WHERE author_id = ?';
    $data = array( DELETED, $user_id );

    Dal::query( $sql, $data );

    Logger::log("Exit: ContentCollection::delete_user_collection");
    return;
  }

}
?>