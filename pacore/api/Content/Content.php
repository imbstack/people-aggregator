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
 * Base  Class for creating micro-content type.
 *
 * Content class will never be instantiated directly. Content will be inherited
 * by micro-content types like BlogPost/Event/Review.
 *
 * @package Content
 * @author Tekriti Software (http://www.tekritisoftware.com)
 */

// global var $path_prefix has been removed - please, use PA::$path static variable
require_once dirname(__FILE__)."/../../config.inc";
require_once "api/DB/Dal/Dal.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/Tag/Tag.php";
require_once "api/Comment/Comment.php";
require_once "api/Validation/Validation.php";
require_once "api/RssFeedHelper/RssFeedHelper.php";
require_once "api/User/User.php";
require_once "api/api_constants.php";

  /* Flags for displaying content on homepage or not. By default all content will be displayed on the homepage  */
  define_once("DISPLAY_ON_HOMEPAGE", 0);
  define_once("NO_DISPLAY_ON_HOMEPAGE", 1);
  define_once("HOMEPAGE_TYPE_CONTENT", -1);



abstract class Content {

  /**
   * @var int ID for micro-content
   * @access public
   */
  public $content_id;

  /**
   * @var int User who created this micro-content
   * @access public
   */
  public $author_id;

  /**
   * @var int type of micro-content e.g, BlogPost/Event/Review.
   * @access public
   */
  public $type;

  /**
   * @var int ID of Containing Collection for micro-content
   * @access public
   */
  public $parent_collection_id = -1;

  /**
   * @var string title of micro-content
   * @access public
   */
  public $title;

  /**
   * @var string content body text
   * @access public
   */
  public $body;

  /**
   * @var boolean Whether comments are allowed on this micro-content
   * @access public
   */
  public $allow_comments;

  /**
   * @var
   * @access public
   */
  public $trackbacks;

  /**
   * @var boolean Whether content is deleted or not
   * @access public
   */
  public $is_active = 1;

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
   * @var boolean indicating whether the content body is HTML or plain text
   * @access public
   */
  public $is_html = 1;

 /**
  * @var array holds the tags associations for contents.
  * @usage array('id' = tag_id, 'name' => tag_name). tag_is is 0 for tag that has not been saved into DB yet
  * @access public
  */
  public $tags = array();

/**
  * @var boolean indicating whether the content is default content or user posted content
  * @access public
  */
  public $is_default_content = FALSE;

  /**
   * Creates a database connection instances.
   * @access public
   *
   */

   /**
   * @var integer flag for displaying the content on homepage
   * @access public
   */
  public $display_on;

  public function __construct(){
    Logger::log("Enter: Content::__construct");

    Logger::log("Exit: Content::__construct");
  }

  /**
   * Destroys a database connection instances upon deletion on object.
   * @access public
   */
  public function __destruct(){
  }

  /**
   * Loads micro-content data from content,contentcollection,tags tables
   * @access protected
   * @param int content_id ID of micro-content
   */
  public function load ($content_id) {
    Logger::log("Enter: Content::load() | Args: \$content_id = $content_id");
    // get micro-content data from DB

    $sql = "SELECT C.content_id AS content_id, C.author_id AS author_id, C.type AS type, C.title AS title, C.body AS body, C.allow_comments AS allow_comments, C.created AS created, C.changed AS changed, C.trackbacks as trackbacks, C.is_active AS is_active, C.is_html AS is_html FROM {contents} AS C WHERE C.content_id = ? AND C.is_active <> ?";
    $data = array($content_id, DELETED);

    $res = Dal::query($sql, $data);

    // fetch rows
    if ($res->numRows()) {
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
      foreach($row as $key => $value) {
        $this->$key = $value;
      }
      $res = Dal::query("SELECT collection_id FROM {contents} WHERE content_id = ?", array($this->content_id));
      if ($res->numRows()) {
        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $this->parent_collection_id = $row->collection_id;
      }
    }
    else {
      Logger::log("No content found with content_id = $content_id", LOGGER_ERROR);
      // Throw "not found" exception
      throw new PAException(CONTENT_NOT_FOUND, "No such content");
    }
    // get tags associated with micro-content
    $this->tags = Tag::load_tags_for_content($this->content_id);
    $author = new User();
    $author->load( (int) $this->author_id);
    $this->author = $author;
    Logger::log("Exit: Content::load()");
    return;
  }

 /**
   * Saves object to databse.
   * @access protected.
   */
  public function save() {
    if ($this->is_active == 0) {
      Logger::log(CONTENT_HAS_BEEN_DELETED, "Attempt to save a deleted content with content_id = $this->content_id");
      throw new PAException(CONTENT_HAS_BEEN_DELETED,"Object you are trying to save has been deleted");
    }
    Logger::log(" Enter: Content::save()", LOGGER_INFO);

    try {
      if(empty($this->active)) {$this->active = 1;}
      // before saving, check if content already exists or not.
      if ($this->content_id) {
        // UPDATE if exists
        if ($this->parent_collection_id != -1) {
	  //FIXME: do we need to make the distinction here?  Should probably always be able to set collection_id, even if -1.
          $sql = "UPDATE {contents} SET title = ?, is_active = ?, body = ?, allow_comments =?, changed = ?, trackbacks = ?, collection_id = ?, is_html = ? WHERE content_id = ? AND is_active = ?";
          $res = Dal::query($sql, array($this->title, $this->is_active, $this->body, $this->allow_comments, time(), $this->trackbacks, $this->parent_collection_id, $this->is_html, $this->content_id, $this->is_active));
        }
        else {
          $sql = "UPDATE {contents} SET title = ?, is_active = ?, body = ?, allow_comments =?, changed = ?, trackbacks = ?, is_html = ? WHERE content_id = ? AND is_active = ?";
          $res = Dal::query($sql, array($this->title, $this->is_active, $this->body, $this->allow_comments, time(), $this->trackbacks, $this->is_html, $this->content_id, $this->is_active));
        }
      }
      else {
        // get next ID for content.
          $this->content_id = Dal::next_id('Content');
          $this->created = time();
          $this->changed = $this->created;
          if (!$this->allow_comments) {
            $this->allow_comments = 0;
          }
          $sql = "INSERT INTO {contents} (content_id, author_id, type, title, is_active, body, allow_comments, collection_id, created, changed, trackbacks, display_on, is_html) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
          $res = Dal::query($sql, array($this->content_id, $this->author_id, $this->type, $this->title, $this->is_active, $this->body, $this->allow_comments, $this->parent_collection_id, $this->created, $this->changed, $this->trackbacks, $this->display_on, $this->is_html));
      }
      if ($this->is_default_content == FALSE) {
      	// fix the Type of SB media here so they show in Recent Media
      	$type = $this->type;
				if (!empty($this->sb_mc_type)) {
					if (preg_match('/video/', $this->sb_mc_type)) $type = VIDEO;
					if (preg_match('/image/', $this->sb_mc_type)) $type = IMAGE;
					if (preg_match('/audio/', $this->sb_mc_type)) $type = AUDIO;
				}
        Content::save_recent_content($this->content_id,  $type) ;
      }
      // if everything succeeded, commit
      Dal::commit();
    } catch (Exception $e) {
      Logger::log("Exception occurred inside Content::save(); rolling back", LOGGER_INFO);
      Dal::rollback();
      throw $e;
    }

    Logger::log("Exit: Content::save()", LOGGER_INFO);
    return $this->content_id;
  }

  /**
   * deletes an object from database
   * @access protected
   */
  public function delete() {
    Logger::log("Enter: Content::delete()");
    // soft deleting
    Content::delete_by_id($this->content_id);
    Logger::log("Exit: Content::delete()");
    $this->is_active = 0;
    return;
  }

  /**
   * deletes an object from database
   * @param int contentid
   */
  public static function delete_by_id($content_id) {
    Logger::log("Enter: Content::delete_by_id()");

    // soft deleting
    $sql = "UPDATE {contents} SET is_active = ? WHERE content_id = ?";
    $res = Dal::query($sql,array(0, $content_id));
    $sql = "DELETE FROM {moderation_queue} WHERE item_id = ?";
    $res = Dal::query($sql,array($content_id));
    Tag::delete_tags_for_content($content_id);
    Logger::log("Deleting the all comments related to this object");
    Comment::delete_all_comment_of_content($content_id);
    Logger::log("Exit: Content::delete_by_id()");
    return;
  }

  // replaces %BASE_URL% and %PERMALINK% in content body with correct values
  public function replace_percent_strings($url) {
      $this->body = Content::_replace_percent_strings($this->content_id, $this->body, $url);
  }

  // (static version - used by RssFeedHelper, which doesn't go through the Content object to fetch content).
  public static function _replace_percent_strings($cid, $body, $url) {
      $content_url = $url . PA_ROUTE_CONTENT . "/cid=$cid";

      return str_replace(
	      array("%BASE_URL%", "%PERMALINK%"),
	      array($url, $content_url),
	      $body
      );
  }

  // splits a space-separated list of trackback urls into a list
  public static function split_trackbacks($tblist) {
      $track = array();
      foreach (explode(" ", $tblist) as $tb) {
          $t = Validation::validate_url(trim($tb));
          if ($t) $track[] = $t;
      }
      return $track;
  }

  /**
   * adds trackbacks to content object
   * @access public
   * @param int content id.
   * @param string trackback url.
   * @param string title of content.
   * @param string excerpt of content.
   */
  public static function add_trackbacks($content_id, $trackback_url, $title, $excerpt) {
    Logger::log("Enter: function Content::add_trackbacks()");
    $result = TRUE;

    $sql = "SELECT * FROM {trackback_contents} WHERE content_id = ? AND trackback = ?";
    $data = array($content_id, $trackback_url);
    $res = Dal::query($sql, $data);

    if ($res->numRows()) {
      $result = FALSE;
    }
    else {
      $sql = "INSERT INTO {trackback_contents} (trackback, content_id, title, excerpt) VALUES (?, ?, ?, ?)";
      $data = array($trackback_url, $content_id, $title, $excerpt);
      $res = Dal::query($sql, $data);
    }
    Logger::log("Exit: function Content::add_trackbacks()");
    return $result;
  }

  static function count_trackbacks_for_content($content_id) {
    return Dal::query_first("SELECT COUNT(*) FROM {trackback_contents} WHERE content_id=?", array($content_id));
  }

  /**
   * loads all the trackbacks of a content id.
   * @access public.
   * @param content id of the content
   *
   * @return this function return the all trackbacks related to a content.
   */
  static function get_trackbacks_for_content ($content_id) {
    Logger::log("Enter: static function Content::get_trackbacks_for_content()");
    $content_trackbacks = array();
    $i = 0;
    $sql = "SELECT * FROM {trackback_contents} WHERE content_id = ?";
    $data = array($content_id);
    $res = Dal::query($sql,$data);
    if ($res->numRows() > 0) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $content_trackbacks[$i] = array('index' => $row->index, 'trackback' => $row->trackback, 'content_id' => $row->content_id, 'title' => $row->title, 'excerpt' => $row->excerpt);
        $i++;
      }
    }
    Logger::log("Exit: static function Content::get_trackbacks_for_content()");
    return $content_trackbacks;
  }

  /**
   * sends trackbacks to content object
   * @access public
   * @param string trackback url.
   */
  public function send_trackback($trackback_url) {
    Logger::log("Enter: function Content::send_trackbacks()");
    // global var $_base_url has been removed - please, use PA::$url static variable


    $req_title = urlencode($this->title);
    $excerpt = substr($this->body, 0, 20).".....";
    $req_excerpt = urlencode($excerpt);
    $tb_url = $trackback_url;
    $req_url = urlencode(PA::$url . PA_ROUTE_CONTENT . "/cid=$this->content_id");
    $query_string = "title=$req_title&url=$req_url&excerpt=$req_excerpt";
    $trackback_url = parse_url($trackback_url);
    $http_request  = 'POST ' . $trackback_url['path'] . ($trackback_url['query'] ? '?'.$trackback_url['query'] : '') . " HTTP/1.0\r\n";
    $http_request .= 'Host: '.$trackback_url['host']."\r\n";
    $http_request .= 'Content-Type: application/x-www-form-urlencoded;'."\r\n";
    $http_request .= 'Content-Length: '.strlen($query_string)."\r\n";
    $http_request .= "User-Agent: PeopleAggregator/".PA_VERSION;
    $http_request .= "\r\n\r\n";
    $http_request .= $query_string;
    if ( '' == $trackback_url['port'] )
      $trackback_url['port'] = 80;

    $fs = @fsockopen($trackback_url['host'], $trackback_url['port'], $errno, $errstr, 4);
    if (!$fs) return FALSE;
    fputs($fs, $http_request);
    fclose($fs);
    Logger::log("Exit: function Content::send_trackbacks()");

    return TRUE;
  }

  /**
   * sends trackbacks to content object
   * @access public
   */
   public function send_ping() {
    // global var $_base_url has been removed - please, use PA::$url static variable

    Logger::log("Enter: function Content::send_ping()");
    $req_title = urlencode($this->title);
    $tb_url = ("http://rpc.weblogs.com/pingSiteForm");
    $req_url = urlencode(PA::$url . "/createcontent.php?cid=$this->content_id");
    $query_string = "name=$req_title&url=$req_url";
    $trackback_url = parse_url("http://rpc.weblogs.com/pingSiteForm");
    $http_request  = 'POST ' . $trackback_url['path'] . ($trackback_url['query'] ? '?'.$trackback_url['query'] : '') . " HTTP/1.0\r\n";
    $http_request .= 'Host: '.$trackback_url['host']."\r\n";
    $http_request .= 'Content-Type: text/xml;'."\r\n";
    $http_request .= 'Content-Length: '.strlen($query_string)."\r\n";
    $http_request .= "User-Agent: PeopleAggregator/".PA_VERSION;
    $http_request .= "\r\n\r\n";
    $http_request .= $query_string;
    if ( '' == $trackback_url['port'] )
      $trackback_url['port'] = 80;

    $fs = fsockopen($trackback_url['host'], $trackback_url['port'], $errno, $errstr, 4);
    fputs($fs, $http_request);
    while(!@feof($fs)) {
      print @fgets($fs, 4096);
    }
    fclose($fs);
    Logger::log("Exit: function Content::send_ping()");
    return;
  }

  /**
   * It gives the rss feed of contents for a given user and no of items
   *
   * @param $user_id string The user id of the user for getting RssFeed of the content
   * @param $no_of_items string The No of latest items for which Rssfeed is Generated
   * @return $generated_rss_feed string This string contains the content of RssFeed file
   */
  public static function get_content_feed_for_user($user_id = 0, $no_of_items = 10) {
    Logger::log("Enter: Content::get_content_feed_for_user()");

    if ($user_id == 0) {
      // Show all content on the homepage
      $sql = "SELECT * FROM {contents} WHERE is_active=1 AND display_on=? AND collection_id=-1 ORDER BY created DESC LIMIT ?";
      $data = array(DISPLAY_ON_HOMEPAGE, $no_of_items);
    }
    else {
      // Show content from a user's blog
      $sql = "SELECT * FROM {contents} WHERE is_active=1 AND author_id=? AND collection_id=-1 ORDER BY created DESC LIMIT ?";
      $data = array($user_id, $no_of_items);
    }

    $res = Dal::query($sql, $data);
    $new_rss = new RssFeedHelper();
    $generated_rss_feed = $new_rss->get_rss_feed ($res, $user_id);
    Logger::log("Exit: Content::get_content_feed_for_user()");
    return $generated_rss_feed;
  }

  /**
   * It gives the rss feed of contents of given ids.
   *
   * @param $content_id array The content ids of the contents for getting RssFeed
   * @return $generated_rss_feed string This string contains the content of RssFeed file
   */
  public static function get_feed_for_content($content_ids) {
    Logger::log("Enter: Content::get_feed_for_content()");

    $sql = "SELECT * FROM {contents} WHERE content_id IN (".$content_ids[0];
    for ($i = 1;$i < count($content_ids);$i++) {
      $sql .= ", ".$content_ids[$i];
    }
    $sql .= ")";

    $data = array();
    $res = Dal::query($sql, $data);
    $new_rss = new RssFeedHelper();
    $generated_rss_feed = $new_rss->get_rss_feed($res);
    Logger::log("Exit: Content::get_feed_for_content()");
    return $generated_rss_feed;
  }

  /**
   * returns content_id for a user_id
   * @access public
   * @param int user_id ID of user
   */
  public static function get_user_content($user_id) {
    Logger::log("Enter: Content::get_user_content() | Args: \$user+id = $user_id");
    // If error occurs then throw exception

    $res = Dal::query("SELECT content_id, title, body, changed, is_active FROM {contents} WHERE collection_id < 0 AND author_id = ? AND is_active = ? ORDER BY created DESC", array($user_id, 1));

    $output = "";
    if ($res->numRows()) {
      $contents = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $contents[] = array('content_id' => $row->content_id, 'title' => $row->title, 'body' => $row->body, 'author_id' => $user_id, 'changed' => $row->changed, 'is_active' => $row->is_active);
      }
      Logger::log("Exit: Content::get_user_content() | Returning ".$contents);
      return $contents;
    }
    Logger::log("Exit: Content::get_user_content() | Returning nothing");
  }

  public static function is_owner_of($user_id, $content_id) {
    $sql = "SELECT * FROM {contents} WHERE author_id=? AND content_id = ?";
    $res = Dal::query($sql, array($user_id, $content_id));
    if ($res->numRows()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * returns boolean
   * @access public
   * @param int user_id ID of user
   */
  public function check_access ($user_id) {
    Logger::log("Enter: Content::check_access()");
    // TO DO: according to changes in admin role change user id check
    if ($this->parent_collection_id == -1 || ($user_id == 1 )) {
      Logger::log("Exit: Content::check_access()");
      return TRUE;
    }
    else {
      try {
        $collection = ContentCollection::load_collection($this->parent_collection_id, $user_id);
      }
      catch (PAException $e) {
        if ($e->code == USER_ACCESS_DENIED) {
          Logger::log("Exit: Content::check_access()");
          return FALSE;
        }
      }
      Logger::log("Exit: Content::check_access()");
      return TRUE;
    }
  }

  /**
   * loads a micro-content, transparently deciding its type.
   * @access public
   * @param int content_id.
   * @param int user id who is accessing this content.
   */
  public static function load_content ($content_id, $user_id = NULL) {
    Logger::log("Enter: Content::load_content() | Args: \$content_id = $content_id, \$user_id = $user_id");

    $sql = "SELECT CT.name AS content_type_name
            FROM {contents} AS C, {content_types} AS CT
            WHERE C.content_id = ? AND CT.type_id = C.type";

    $res = Dal::query($sql, array($content_id));

    if ($res->numRows()) {
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

      $content_type = $row['content_type_name'];

     /**
      * NOTE:
      *
      * SBMicroContent has been removed from the PA Core
      * so, I've added this fix to prevent crashing on sites
      * which still has posts with micro contents
      */
      if($content_type == 'SBMicroContent') {
        return null;
      }

      require_once "api/".$content_type.'/'.$content_type.".php";
      try {
        $new_content = new $content_type();
        $new_content->load($content_id);
        $new_content->type = $content_type;
      }
      catch (Exception $e) {
        return null;
      }
    }
    else {
      Logger::log("Throwing Exception CONTENT_NOT_FOUND");
      throw new PAException(CONTENT_NOT_FOUND, "No such content");
    }

    Logger::log("Exit: Content::load_content()");
    return $new_content;
  }

  /**
   * loads array of content-id in decreasing order of their edition.
   * @access public
   * @param int user id
   * @param int count
   * @param string sort factor.
   */
  public static function load_content_id_array($user_id = 0, $type=NULL, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC', $only_homepage = true) {
    Logger::log("Enter: Content::load_content_id_array() | Args:  \$user_id = $user_id");


    $order_by = $sort_by.' '.$direction;
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }

    switch ($type) {
    case VIDEO: $sb_pattern = 'media/video%'; break;
    case AUDIO: $sb_pattern = 'media/audio%'; break;
    case IMAGE: $sb_pattern = 'media/image%'; break;
    default: $sb_pattern = NULL; break;
    }
    if ($sb_pattern) {
      if ($user_id == 0) {
	$author = " AND C.display_on = ".DISPLAY_ON_HOMEPAGE;
      } else {
        $author = " AND C.author_id = $user_id";
      }
      $sql = "SELECT C.content_id As content_id, C.collection_id As collection_id, C.title As title, C.body As body, C.author_id As author_id, C.type as type, C.changed As changed, C.created as created FROM {contents} As C, {contents_sbmicrocontents} As CM, {sbmicrocontent_types} As SM WHERE C.collection_id < 0 AND C.is_active = ? $author AND C.type = ? AND CM.content_id = C.content_id AND SM.sbtype_id = CM.microcontent_id AND SM.name LIKE ? ORDER BY $order_by $limit";
      $data = array(1, 7, $sb_pattern);
    }
    else {
      if ($type != NULL) {
        $with_type = " AND type=$type";
      }
      else $with_type = "";

      if ($user_id == 0) {
				if ($cnt) {
					return Dal::query_first("SELECT COUNT(*) FROM {contents} WHERE is_active=1 AND display_on=? $with_type", array(DISPLAY_ON_HOMEPAGE));
				}

        if ($only_homepage) {
        	$homepage = " AND display_on = ".DISPLAY_ON_HOMEPAGE;
        } else {
        	$homepage = " AND display_on IS NOT NULL";
        }
        $sql = "SELECT content_id, collection_id, title, body, type, author_id, changed, created FROM {contents} WHERE is_active = 1 $homepage $with_type ORDER BY $order_by $limit";
        $data = array();
      }
      else {
        $sql = "SELECT content_id, collection_id, title, body, author_id, type, changed, name, created FROM {contents} As C LEFT JOIN {content_types} As CT ON C.type=CT.type_id WHERE C.collection_id = -1 AND C.author_id = ? AND C.is_active = 1 $with_type ORDER BY $order_by $limit";

        //$sql = "SELECT content_id, title, body, author_id, changed FROM {contents} WHERE collection_id < 0 AND author_id = ? AND is_active = ?$with_type ORDER BY $order_by $limit";
        $data = array($user_id);

      }
    }

    $res = Dal::query($sql, $data);

    if ( $cnt ) {
      return $res->numRows();
    }

    $content_id = array();
    if ($res->numRows()) {
      $i = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $content_id[$i] = array('content_id' => $row['content_id'], 'collection_id' => $row['collection_id'], 'title' => $row['title'], 'body' => $row['body'], 'author_id' => $row['author_id'], 'type' => $row['type'], 'changed' => $row['changed'], 'created' => $row['created'] , 'type_name' => (isset($row['name'])) ? $row['name'] : '');
        $i++;
      }
    }
    $contents = array();
    foreach($content_id as &$_content) {
        if(!Content::check_content_privacy($_content)) { // check is content from a  private Group
           $contents[] = $_content;
        }
    }
//    echo '<pre>'.print_r($cont,1).'</pre>';
    Logger::log("Exit: Content::load_content_id_array()");
    return $contents;
  }


  /**
   * check is content published in a private Group
   * @param  $content
   */
  public static function check_content_privacy($content) {
     $result = false;
     if ((int)$content['collection_id'] > 0) { 
			$group = Group::load_group_by_id((int)$content['collection_id']);
			if (is_object($group)) {
				if (($group->access_type == ACCESS_PRIVATE) || ($group->reg_type == REG_INVITE)) {
					// content published in a private Group!
					$result = true;            
				}
			}     }
     return $result;
     /* the following code was commented out because it's 
     obsolete and no longer works as required
     -Martin
     */
     /* if($content['collection_id'] == -1) {
        $sql = "SELECT content_id, collection_id, title, author_id, type FROM {contents} WHERE collection_id <> -1 AND author_id = ? AND is_active = 1 AND title = ? AND type = ?";
        $data = array((int)$content['author_id'], $content['title'], $content['type']);
        $res = Dal::query($sql, $data);
        if ($res->numRows()) {
          $i = 0;
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $group = Group::load_group_by_id((int)$row['collection_id']);
            if(is_object($group)) {
              if(($group->access_type == ACCESS_PRIVATE) || ($group->reg_type == REG_INVITE)) {
                $result = true;            // content published in a private Group!
                break;
              }
            }
          }
        }
     } */
  }

  /**
   * deletes all content related with an user_id
   * @param int user_id
   */
  public static function delete_all_content_of_user($user_id) {
    Logger::log("Enter: Content::delete_all_content_of_user()");
    $res = false;
    // searching for all content related with user id
    $user_content_data = Content::get_user_content ($user_id);
    if(0 < count($user_content_data)) {
      for ($i=0; $i<count($user_content_data); $i++) {
        $content_id[$i] = $user_content_data[$i]['content_id'];
      }

      // soft deleting
      $sql = "UPDATE {contents} SET is_active = 0 WHERE author_id = ?";
      $data = array($user_id);
      $res = Dal::query($sql, $data);

      // delete tags of related user_id's content
      $id = $content_id[0];
      $sql = "DELETE FROM {tags_contents} WHERE content_id = $id";
      for ($i = 1;$i < count($content_id);$i++) {
       $id = $content_id[$i];
       $sql .= " OR content_id = $id";
      }
      $res = Dal::query($sql);

      // Delete comment of related user_id's content
      Logger::log("Deleting the all comments related to this object");
      $id = $content_id[0];
      $sql = "UPDATE {comments} SET is_active = 0 WHERE content_id = $id";
      for ($i = 0;$i < count($content_id);$i++) {
       $id = $content_id[$i];
       $sql .= " OR content_id = $id";
      }
      $res = Dal::query($sql);
    }
    Logger::log("Exit: Content::delete_all_content_of_user()");
    return $res;
  }

  /**
  * Search contents for the user.
  * @access public.
  * @param array name to be searched.
  */
  static function search_content($search_string_array) {
    Logger::log("Enter: function Content::search_content");

    $sql = "SELECT content_id, title, body, author_id, changed FROM {contents} WHERE collection_id < 0 AND is_active = 1 AND (title like '%$search_string_array[0]%' OR body like '%$search_string_array[0]%')";
    for ($i = 1;$i < count($search_string_array);$i++) {
     $name =  "%".$search_string_array[$i]."%";
     $sql .= " AND (title like '$name' OR body like '$name')";
    }

    $res = Dal::query($sql);

    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $selected[] = array('content_id' => $row->content_id, 'title' => $row->title, 'body' => $row->body, 'author_id' => $row->author_id, 'changed' => $row->changed);
    }
    Logger::log("Exit: function Content::search_content");
    return $selected;
  }

  /**
  * Content Search for the user.
  * @access public.
  * @param array name to be searched.
  */
  static function content_search($search_string_array, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC') {
    Logger::log("Enter: function Content::content_search");

    $order_by = $sort_by.' '.$direction;
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }

    $condition = "";
    // Code for all the words search
    if (count(@$search_string_array["allwords"]) > 0) {
        for ($counter = 0; $counter < count($search_string_array["allwords"]); $counter++) {
            $condition .= " AND (title like '%".$search_string_array["allwords"][$counter]."%' OR body like '%".$search_string_array["allwords"][$counter]."%')";
        }
    }

    // Code for exact phrase search
    if (count(@$search_string_array["phrase"]) > 0) {
        $condition .= " AND (title like '%".$search_string_array["phrase"][0]."%' OR body like '%".$search_string_array["phrase"][0]."%')";
    }

    // Code for any words search
    if (count(@$search_string_array["anywords"]) > 0) {
        for ($counter = 0; $counter < count($search_string_array["anywords"]); $counter++) {
            $condition .= " AND (title like '%".$search_string_array["anywords"][$counter]."%' OR body like '%".$search_string_array["anywords"][$counter]."%')";
        }
    }

    // Code for none of words search
    if (count(@$search_string_array["notwords"]) > 0) {
        for ($counter = 0; $counter < count($search_string_array["notwords"]); $counter++) {
            $condition .= " AND (title NOT like '%".$search_string_array["notwords"][$counter]."%' AND body NOT like '%".$search_string_array["notwords"][$counter]."%')";
        }
    }

    if (count(@$search_string_array["date"]) > 0) {
        $condition .= " AND created >= ".$search_string_array["date"]["from"]." AND created <= ".$search_string_array["date"]["to"];
    }
    if (!empty($search_string_array)) { // if search string is supplied
      $sql = "SELECT content_id, title, body, author_id, changed FROM {contents} WHERE collection_id < 0 AND is_active = 1 $condition";

      if (count(@$search_string_array["searchby"]) > 0) {
          $sql .= " order by ".$search_string_array["searchby"][0];
      }

      $sql .= " ORDER BY $order_by $limit";
      $res = Dal::query($sql);

      if ($cnt) {
        return $res->numRows();
      }
      $selected = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $selected[] = array('content_id' => $row->content_id, 'title' => $row->title, 'body' => $row->body, 'author_id' => $row->author_id, 'changed' => $row->changed);
      }
      Logger::log("Exit: function Content::content_search");
      return $selected;
    } else { // else return false
      Logger::log("Exit: function Content::content_search");
      return false;
    }  // end of if search string supplied
  }

  /**
  * updating the status of the content that is altering the is_active field
  */

   static function update_content_status ($content_id, $is_active = 0) {
     Logger::log("Enter: function Content::update_content_status");
     $cid_string = "";
     if(is_array($content_id)) {
        for($counter = 0; $counter < count($content_id); $counter++) {
          $cid_string .= $content_id[$counter].",";
        }
        $cid_string = substr($cid_string, 0, strlen($cid_string) - 1);
        $sql = "UPDATE {contents} SET is_active = ? WHERE content_id IN ( ? )";
        $data = array($is_active, $cid_string);
     } else {
        $sql = 'UPDATE {contents} SET is_active = ? WHERE content_id = ?';
        $data = array($is_active, $content_id);
     }
     $res = Dal::query($sql, $data);
     Logger::log("Deleting the all comments related to this object");
     Comment::delete_all_comment_of_content($content_id, $is_active);

     Logger::log("Exit: function Content::update_content_status");
     return;
   }

   /**
   * returns content_id for a user_id
   * @access public
   * @param int user_id ID of user
   */
  public static function get_user_content_with_paging ($user_id, $page=1, $pagesize=20) {
    Logger::log("Enter: Content::get_user_content_with_paging() | Args: \$user+id = $user_id");
    // If error occurs then throw exception
    if ($page!=1) {
      $page = $page*$pagesize-$pagesize;
    }
    else {
      $page = $page-1;
    }
    $res = Dal::query("SELECT content_id, title, body, changed, is_active FROM {contents} WHERE collection_id < 0 AND author_id = ? ORDER BY created DESC LIMIT $page, $pagesize", array($user_id));

    $output .= "";
    if ($res->numRows()) {
      $contents = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $contents[] = array('content_id' => $row->content_id, 'title' => $row->title, 'body' => $row->body, 'author_id' => $user_id, 'changed' => $row->changed, 'is_active' => $row->is_active);
      }
      Logger::log("Exit: Content::get_user_content_with_paging() | Returning ".$contents);
      return $contents;
    }
    Logger::log("Exit: Content::get_user_content_with_paging() | Returning nothing");
  }

  /**
  * function will return the total number of records in content table
  */

   public function get_record_count ($uid) {
     Logger::log("Enter: function Content::get_record_count");

     $sql = "SELECT count(*) as records from {contents} where collection_id < 0 and author_id = ? ";
     $data = array($uid);
     $res = Dal::query($sql, $data);

     $records = 0;
     if ($res->numRows()) {
          $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
          $records = $row->records;
      }

     Logger::log("Exit: function Content::get_record_count");
     return $records;
   }


  public static function count_all_content() {
    list($ct) = Dal::query_one("SELECT COUNT(*) FROM {contents}");
    return $ct;
  }
   /**
   * Load all content for admin
   */
  public static function load_all_content ($user_id = 0, $type=NULL, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC') {

    $order_by = $sort_by.' '.$direction;
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }

    $sql = "SELECT content_id, title, body, type, author_id, changed, created, is_active FROM {contents} order by created DESC $limit";
    $data = array();
    $res = Dal::query($sql, $data);

    if ( $cnt ) {
      return $res->numRows();
    }

    if ($res->numRows()) {
      $i = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $content_id[$i] = array('content_id' => $row['content_id'], 'title' => $row['title'], 'body' => $row['body'], 'author_id' => $row['author_id'], 'type' => $row['type'], 'changed' => $row['changed'], 'created' => $row['created'], 'type_name' => (isset($row['name'])) ? $row['name'] : '', 'is_active' => $row['is_active']);
        $i++;
      }
    }
    return $content_id;
  }

   /**
   * get custom method for recent post block ; later can be used for other purposes
   */
  public static function get($params = NULL, $conditions = NULL) {
    Logger::log("Enter: function Content::get");
    $sql = " SELECT * FROM {contents} WHERE 1 AND is_active = 1 ";
    if ( $conditions ) {
      $sql = $sql . ' AND ' .$conditions;
    }
    $sort_by = ( @$params['sort_by'] ) ? $params['sort_by'] : 'created';
    $direction = ( @$params['direction'] ) ? $params['direction'] : 'DESC';
    $order_by = ' ORDER BY '.$sort_by.' '.$direction;
    if (!empty($params['page']) && !empty($params['show']) && empty($params['cnt'])) {
      $start = ($params['page'] -1) * $params['show'];
      $limit = ' LIMIT '.$start.','.$params['show'];
    } else {
      $limit = "";
    }
    $sql = $sql . $order_by . $limit;
    $res = Dal::query($sql);
    if (!empty($params['cnt']) && $params['cnt']==TRUE ) {
      return $res->numRows();
    }
    $i=0;
    $content_id = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      $content_id[$i] = array('content_id' => $row['content_id'], 'title' => $row['title'], 'body' => $row['body'], 'author_id' => $row['author_id'], 'type' => $row['type'], 'changed' => $row['changed'], 'created' => $row['created'], 'is_active' => $row['is_active']);
      $i++;
    }
    Logger::log("Exit: function Content::get");
    return $content_id;
  }



  /**
   * loads array of all content-id in decreasing order of their edition,
   *for network operator control, network content management
   * @access public
   * @param int user id
   * @param int count
   * @param string sort factor.
   */

  public static function load_all_content_id_array ($cnt=FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC', $condition = NULL) {
    Logger::log("Enter: Content::load_all_content_id_array()");
      $order_by = $sort_by.' '.$direction;
      //setting limits for pagination
      if ( $show == 'ALL' || $cnt == TRUE) {
        $limit = '';
      } else {
        $start = ($page -1)* $show;
        $limit = 'LIMIT '.$start.','.$show;
      }
    if( $condition['keyword'] || $condition['month']) {
      //setting constraints
      $data ='%'.$condition['keyword'].'%';
      $month = $condition['month'];
      //finding date limits
      $first_day_of_month = strtotime( "-" . ( date( "d", $month)-1 ) . " days", $month );
      $last_day_of_month = strtotime( "+" . ( date("t", $first_day_of_month ) - 1 ) . " days", $first_day_of_month);
      if(date('M',$month) == date('M',time())) {
        $last_day_of_month = time();
      }
     }
      if ( $condition['keyword'] && $condition['month']) {//if something is searched, show respective contents
          $sql = "SELECT CT.name AS content_name, C.content_id, C.collection_id, C.title, C.body, C.type, C.author_id, C.changed, C.created FROM {contents} AS C, {content_types} AS CT WHERE C.title LIKE ? AND C.is_active = ?  AND CT.type_id = C.type  AND C.created BETWEEN $first_day_of_month AND $last_day_of_month ORDER BY $order_by $limit ";
          $data = array( $data, ACTIVE );
      } else if (empty($condition['keyword']) && !empty($condition['month'])) {//if nothing is searched, show all contents
          $sql = "SELECT CT.name AS content_name, C.content_id, C.collection_id, C.title, C.body, C.type, C.author_id, C.changed, C.created FROM {contents} AS C, {content_types} AS CT WHERE  C.is_active = ?  AND C.created BETWEEN $first_day_of_month AND $last_day_of_month AND  CT.type_id = C.type ORDER BY $order_by $limit ";
          $data = array( ACTIVE );
      } else if ( empty($condition['keyword']) && empty($condition['month'])) {
          $sql = "SELECT CT.name AS content_name, C.content_id, C.collection_id, C.title, C.body, C.type, C.author_id, C.changed, C.created FROM {contents} AS C, {content_types} AS CT WHERE  C.is_active = ?  AND  CT.type_id = C.type ORDER BY $order_by $limit ";
          $data = array( ACTIVE );
      }

      $res = Dal::query( $sql, $data );
      if ( $cnt ) {
        if( $res->numRows() > 0 ) {
          return $res->numRows();
        }
      }
       $content_data = array();
      // preparing array, that is to be returned
      if ($res->numRows()) {
        $i = 0;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        try {
          $author = new User();
          $author->load( ( int )$row['author_id'] );
          if ($row['collection_id'] != -1) {
            $var = new ContentCollection();
            $var->load((int)$row['collection_id']);
            $collection['title'] = $var->title;
            $collection['type'] = $var->type;
          } else {
            $collection = array();
          }
          $comment_for_content = Comment::get_comment_for_content($row['content_id']);
          $content_data[$i] = array('content_id' => $row['content_id'],
          	'title' => $row['title'],
          	'body' => $row['body'],
          	'author_id' => $row['author_id'],
          	'type' => $row['content_name'],
          	'changed' => $row['changed'],
          	'created' => PA::datetime($row['created'],
          	'long', 'long'),
          	'type_name' => (isset($row['name'])) ? $row['name'] : '',
          	'comment_count'=>count($comment_for_content),
          	'author_name'=>$author->display_name,
          	'content_type_id' => $row['type'],
          	'parent_info'=>$collection);
          $i++;
        } catch (PAException $e) {
         //
        }
        }
      }


    Logger::log("Exit: Content::load_all_content_id_array()");
    return $content_data;
  }

  static function get_recent_content_for_user($uid, $content_type, $limit) {
    $tables = array(IMAGE => "images",
		    AUDIO => "audios",
		    VIDEO => "videos",
		    );
    $other_table = $tables[$content_type];
    $sth = Dal::query("SELECT * FROM contents C
      LEFT JOIN $other_table I ON C.content_id=I.content_id
      WHERE C.type=? AND C.author_id=? AND C.is_active=1
      ORDER BY C.created DESC
      LIMIT $limit",
		      array($content_type, $uid));
    return Dal::all_assoc($sth);
  }

  /**
  * function to delete user content
  */

  public static function delete_user_content ( $user_id ) {
    Logger::log("Enter: Content::delete_user_content()");

    $sql = 'UPDATE {contents} SET is_active = ? WHERE author_id = ?';
    $data = array(DELETED, $user_id );
    Dal::query( $sql, $data );

    $sql = 'DELETE FROM MQ USING {moderation_queue} AS MQ INNER JOIN {contents} AS C ON MQ.item_id = C.content_id AND MQ.type = ? AND C.author_id = ?';
    $data = array('content', $user_id );//TODO: define the string in constants
    Dal::query( $sql, $data );

    Logger::log("Exit: Content::delete_user_content()");
  }
 /**loads array of all content-id in decreasing order of their edition,
   *for network operator control, network content management
   * @access public
   * @param array params
   * @param int count
   * @param condtition.
  */
  public static function get_recent_posts($params = NULL, $conditions = NULL) {
    Logger::log("Enter: function Content::get");
    $sql = " SELECT * FROM {contents} AS C , {recent_media_track} AS RT WHERE RT.cid=C.content_id AND C.is_active = 1 ";
    if ( $conditions ) {
      $sql = $sql . ' AND ' .$conditions;
    }
    $sort_by = ( $params['sort_by'] ) ? $params['sort_by'] : 'C.created';
    $direction = ( $params['direction'] ) ? $params['direction'] : 'DESC';
    $order_by = ' ORDER BY '.$sort_by.' '.$direction;
    if (!empty($params['page']) && !empty($params['show']) && empty($params['cnt'])) {
      $start = ($params['page'] -1) * $params['show'];
      $limit = ' LIMIT '.$start.','.$params['show'];
    } else {
      $limit = "";
    }
    $sql = $sql . $order_by . $limit;
    $res = Dal::query($sql);
    if (!empty($params['cnt']) && $params['cnt']==TRUE ) {
      return $res->numRows();
    }
    $i=0;
    $content_id = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      $content_id[$i] = array('content_id' => $row['content_id'], 'collection_id' => $row['collection_id'], 'title' => $row['title'], 'body' => $row['body'], 'author_id' => $row['author_id'], 'type' => $row['type'], 'changed' => $row['changed'], 'created' => $row['created'], 'is_active' => $row['is_active']);
      $i++;
    }

    $contents = array();
    foreach($content_id as &$_content) {
        if(!Content::check_content_privacy($_content)) { // check is content from a  private Group
           $contents[] = $_content;
        }
    }
//    echo '<pre>'.print_r($cont,1).'</pre>';
    Logger::log("Exit: function Content::get");
    return $contents;
  }
   /**
   *  save_recent_content in recent media track
   *
   * @param $cid string,$type
   */

  public static function save_recent_content($cid, $type) {
    Logger::log("Enter: Content::save_recent_content");
    $sql = "SELECT  id FROM  {recent_media_track}  WHERE type = ? ORDER BY  created ASC";
    $res = Dal::query($sql,$type);
    if ($res->numRows() < RECENT_MEDIA_LIMIT) {
      $sql_del = "DELETE FROM  {recent_media_track} where cid = ? and type = ?";
      $data = array($cid,$type);
      if( !$res_del = Dal::query($sql_del, $data) ) {
        Logger::log("Content::save_recent_content  function failed");
        throw new PAException(IMAGERECENTMEDIA_FAILED, "Delete Recent Content Failed");
      }
      $sql= "INSERT INTO {recent_media_track}(cid,type,created) values(?,?,?)";
      $data = array($cid,$type,time());
      if( !$res = Dal::query($sql, $data) ) {
        Logger::log("Content::save_recent_content  function failed");
        throw new PAException(IMAGERECENTMEDIA_FAILED, "Save Recent Content Failed");
      }
     } else {
       $sql_del = "DELETE FROM  {recent_media_track} where cid = ? and type = ?";
       $data = array($cid,$type);
       if( !$res_del = Dal::query($sql_del, $data) ) {
         Logger::log("Content::save_recent_content  function failed");
         throw new PAException(IMAGERECENTMEDIA_FAILED, "Delete Recent Content Failed");
       }
         $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
         $sql= "UPDATE {recent_media_track} set cid = ? ,type = ? , created=? where id = ?";
         $data = array($cid,$type,time(),$row->id);
         if( !$res = Dal::query($sql, $data) ) {
           Logger::log("Content::save_recent_content  function failed");
           throw new PAException(IMAGERECENTMEDIA_FAILED, "Save Recent content  Failed");
        }
     }
    Logger::log("Exit Content::save_recent_content");
  }
/**
   * loads array of all content, that are supposed to be moderated
   * for network operator control, network content management
   * @access public
   * @param array params
   * @param array conditions
   * @return array content
   */

  public static function load_all_content_for_moderation ($params = NULL, $conditions = NULL) {
    Logger::log("Enter: Content::load_all_content_for_moderation() | Args:  \$params = $params, \$conditions = $conditions");
      $sql = "SELECT CT.name AS content_name, C.content_id, C.collection_id, C.title, C.body, C.type, C.author_id, C.changed, C.created, C.is_active FROM {contents} AS C, {content_types} AS CT WHERE 1 AND  CT.type_id = C.type";
      if (is_array($conditions)) {
        foreach ($conditions as $field_name => $field_value) {
          $sql = $sql .' AND ' . $field_name .' = '.$field_value;
        }
      }
      //paging variables if set
      $sort_by = (isset($params['sort_by'])) ? $params['sort_by'] : 'created';
      $direction = (isset($params['direction'])) ? $params['direction'] : 'DESC';
      $order_by = ' ORDER BY ' . $sort_by . ' ' . $direction;
      if ((isset($params['page'])) && (isset($params['show'])) && (!isset($params['cnt']))) {
        $start = ($params['page'] -1) * $params['show'];
        $limit = ' LIMIT '.$start.','.$params['show'];
      } else {
        $limit = "";
      }
      $sql = $sql . $order_by . $limit;

      $res = Dal::query($sql);
      if ($params['cnt']) {
        if ($res->numRows() > 0) {
          return $res->numRows();
        }
      }

       $content_data = array();
      // preparing array, that is to be returned
      if ($res->numRows()) {
        $i = 0;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        try {
          $author = new User();
          $author->load((int)$row['author_id']);
          if ($row['collection_id'] != -1) {
            $var = new ContentCollection();
            $var->load((int)$row['collection_id']);
            $collection['title'] = $var->title;
            $collection['type'] = $var->type;
          } else {
            $collection = array();
          }
          $content_data[$i] = array('content_id' => $row['content_id'], 'title' => $row['title'], 'body' => $row['body'], 'author_id' => $row['author_id'], 'type' => $row['content_name'], 'changed' => $row['changed'], 'created' => PA::datetime($row['created'], 'long', 'long'),
          'author_name'=>$author->display_name,
          'content_type_id' => $row['type'], 'parent_info'=>$collection, 'is_active' => $row['is_active'], 'type_name' => (isset($row['name'])) ? $row['name'] : '');
          $i++;
        } catch (PAException $e) {
         //
        }
      }
    }
    Logger::log("Exit: Content::load_all_content_for_moderation()");
    return $content_data;
  }
}

?>
