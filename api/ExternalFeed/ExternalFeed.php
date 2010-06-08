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
  * This file is used to add new rss feeds link for a user.
  * This file uses the domit rss feed parser which is a LGPL code.
  * @author : tekritisoftware
  * DB tables involved external_feed, feed_data
  */


  include_once dirname(__FILE__)."/../../config.inc";
  // global var $path_prefix has been removed - please, use PA::$path static variable
  require_once "api/Logger/Logger.php";
  require_once 'ext/Domit/xml_domit_rss_lite.php';


  class ExternalFeed {

    /**
    * @param $feed_id integer
    * Unique identifier for every feed added by the user
    */
    protected $feed_id;

    /**
    * @param $user_id integer
    * Unique identifier for the user who added the feed
    */
    public $user_id;

    /**
    * @param $import_url string
    * URL from RSS feed will be fetched is import_url
    * example: http://radio.weblogs.com/0105058/rss.xml
    */
    protected $import_url;

    /**
    * @param $is_active integer
    * is_active will determine the current status of the feed.
    * is_active = 1 ( default for active feeds ), is_active = 0 ( deleted feeds )
    */
    protected $is_active;

    /**
    * @param $max_posts integer
    * Maximum number of posts per feed to be fetched for a given import url
    */
    protected $max_posts;

    /**
    * @param $feed_type string
    * Type of feed will be determined by type
    * Feed type can be a group, user, network etc.
    */
    protected $feed_type;

    /**
    * @param $last_build_date integer
    * Date when the feed was last updated.
    */
    protected $last_build_date;

    /**
    * @param $do_refresh integer
    * When this variable is set the feed is valid for refreshing.
    * This will be set when user want its feed to be refreshed or when FEED_REFRESH_TIME has passed since
    * this feed is added.
    */
    protected $do_refresh;

    /**
    *@param $original_url string
    * Original permalink of the feed data
    */
    protected $original_url;

    /**
    * @param $publish_date integer
    * Unix time of the time when the post has been published
    */
    protected $publish_date;


    /**
    * Setter methods for feed_id
    */
    public function set_feed_id( $feed_id ) {
      if( !is_int( $feed_id ) ) {
        throw new PAException(INVALID_FIELD_VALUE, 'Feed id should have an integral value');
      }
      $this->feed_id = $feed_id;
    }

    /**
    * Setter method for import_url
    */
    public function set_import_url( $import_url ) {
      if( !Validation::isValidURL( $import_url ) ) {
        throw new PAException(INVALID_FIELD_VALUE, 'Given import URL is not valid.');
      }
      $this->import_url = $import_url;
    }

    /**
    * Setter method for import_url
    */
    public function set_do_refresh($do_refresh) {
      if( !is_bool($do_refresh) ) {
        throw new PAException(INVALID_FIELD_VALUE, 'do_refresh should have a boolean value.');
      }
      $this->do_refresh = $do_refresh;
    }

     /**
    * Setter method for feed_type
    */
    public function set_feed_type($feed_type) {
      //TODO: Check for the type of feed allowed
      $this->feed_type = $feed_type;
    }


    /**
    * Constructor for this class
    */
    function __construct() {
      Logger::log("Enter: ExternalFeed::__construct");

      $this->feed_type = USER_FEED;
      $this->max_posts = MAX_POSTS_PER_FEED;
      $this->is_active = ACTIVE;
      $this->do_refresh = false;

      Logger::log("Exit: ExternalFeed::__construct");
    }


    /**
    * Public function to create a new feed for a user.
    * @param import_url, user_id
    * @return true on success, on failure will throw an exception
    */
    public function save() {
      Logger::log("Enter: ExternalFeed::save");

      //check for feed, whether it exists in the system or not
      $sql = 'SELECT feed_id FROM {external_feed} WHERE import_url = ?';
      $res = Dal::query($sql, array($this->import_url));

      if($res->numRows()) { //feed url already exists in the system
        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $this->feed_id = $row->feed_id;
        $sql = 'SELECT * FROM {user_feed} WHERE user_id = ? AND feed_id = ?';
        $res = Dal::query($sql, array($this->user_id, $this->feed_id));

        if( $res->numRows() ) { // Import url already exists for given user.
          Logger::log("Feed url = $this->import_url already exists for user_id = $this->user_id");
          throw new PAException(IMPORT_URL_ALREADY_EXISTS, 'Import URL exists already for user.');
        }
        else {
          //saving feed for the user
          $this->save_user_feed();
          //refreshing the data for the feed
          $this->do_refresh = true;
          $this->refresh_feed_data();
        }
      }
      else {
        //This is a new feed and will be added to the existing feeds in the system
        $this->feed_id = Dal::next_id('ExternalFeed');
        $sql = 'INSERT INTO {external_feed} ( feed_id, import_url, max_posts, is_active, feed_type, last_build_date ) VALUES ( ?, ?, ?, ?, ?, ? )';
        $data = array( $this->feed_id, $this->import_url, $this->max_posts, $this->is_active, $this->feed_type, time() );

        try {
          //Inserting the feed to external_feed table
          $res = Dal::query( $sql, $data );
        }
        catch ( PAException $e ) {
          Logger::log("ExternalFeed::save failed for user_id = $this->user_id. Associated sql = $sql");
          throw $e;
        }

        //saving feed for the user
        $this->save_user_feed();


        try {
            $this->import_posts();//importing the posts for given feed
        }
        catch( PAException $e ) {
          Logger::log("$e->message");
          ExternalFeed::delete_user_feed($this->feed_id, $this->user_id);// deleting the inserted feed if import_posts fails.
          throw $e;
        }
      }

      Logger::log("Exit: ExternalFeed::save");
      return true;
    }

    public static function deleteByID($feed_id) {
      Logger::log("Enter: ExternalFeed::deleteByID");

      //check for feed, whether it exists in the system or not
      $sql = 'DELETE FROM {external_feed} WHERE feed_id = ?';
      $res = Dal::query($sql, array($feed_id));
      Logger::log("Exit: ExternalFeed::deleteByID");
    }

    /**
    * Function to fetch feeds for a user.
    * @return array of objects for feed.
    */
    public function get_feeds() {
      Logger::log("Enter: ExternalFeed::get");

      $sql = 'SELECT * FROM {external_feed} WHERE is_active = ?';
      $data = array(ACTIVE);

      if($this->feed_id) {
        $sql .= ' AND feed_id = ?';
        $data[] = $this->feed_id;
      }

      try {
        $res = Dal::query( $sql, $data );
      }
      catch ( PAException $e ) {
        Logger::log("ExternalFeed::get failed for user_id = $this->user_id. Associated sql = $sql");
        throw $e;
      }

      $external_feed = array();
      if( $res->numRows() ) {
        while ( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
          $external_feed[] = $row;
        }
      }

      Logger::log("Exit: ExternalFeed::get");
      return $external_feed;
    }


    /**
    * Deleting a user feed.
    * @param user_id, feed_id
    * @return true on success, on failure will throw an exception
    */
    public static function delete_user_feed( $feed_id, $user_id ) {
      Logger::log("Enter: ExternalFeed::delete_user_feed");

      $sql = 'DELETE FROM {user_feed} WHERE feed_id = ? AND user_id = ?';
      try {
        $res = Dal::query( $sql, array( $feed_id, $user_id ) );
      }
      catch ( PAException $e ) {
        Logger::log("ExternalFeed::delete failed for feed_id = $feed_id and user_id = $user_id. Associated sql = $sql");
        throw $e;
      }

      Logger::log("Exit: ExternalFeed::delete_user_feed");
      return true;
    }


    /**
    * Function used to import posts from the given import_url. This function will import maximum = max_posts for any import_url
    * This function will use the Domit RSS parser to parse the feed.
    *  @param feed_id import_url
    * @return true on success, on failure will throw exception
    */
    public function import_posts() {
      Logger::log("Enter: ExternalFeed::import_posts");

      $feed_data = array();

      //$this->import_url and $this->max_posts should be set in order to exceute this function correctly.
      $feed_data = $this->import_feed();

      if($feed_data) {
        $total_posts = count($feed_data);
        for ($counter = 0; $counter < $total_posts; $counter++) {
          $sql = 'INSERT INTO {feed_data} ( feed_id, title, description, original_url, publish_date ) VALUES ( ?, ?, ?, ?, ?)';
          $data = array($this->feed_id, $feed_data[$counter]['title'], $feed_data[$counter]['description'], $feed_data[$counter]['original_url'], $feed_data[$counter]['publish_date']);

          try {
            $res = Dal::query($sql, $data);
          }
          catch( PAException $e ) {
            Logger::log("Entry in feed_data table failed for feed_id = $this->feed_id, import_url = $this->import_url");
            throw $e;
          }
        }//end for

      } else {
        Logger::log("No posts are found for Import Url = $this->import_url");
        throw new PAException(IMPORT_FAILED, 'No posts are being imported. Given import url is incorrect or feed has yielded zero posts.');
        return false;
      }

      Logger::log("Exit: ExternalFeed::import_posts");
      return true;
    }

    /**
    * Function to update the lastBuildDate for the feed
    * @param feed_id
    */
    public function update_last_build_date() {
      Logger::log("Enter: ExternalFeed::update_last_build_date");

      $sql = 'UPDATE {external_feed} SET last_build_date = ? WHERE feed_id = ?';

      try {
        $res = Dal::query($sql, array($this->last_build_date, $this->feed_id));
      }
      catch(PAException $e) {
        Logger::log("Update last build date failed for feed_id = $this->feed_id and last_build_date = $this->last_build_date");
        throw $e;
      }

      Logger::log("Exit: ExternalFeed::update_last_build_date");
      return true;
    }

    /**
    * Function to delete feed related data from feed_data table
    * @param feed_id
    */
    public function delete_user_feed_data() {
      Logger::log("Enter: ExternalFeed::delete_user_feed_data");

      $sql = 'DELETE FROM {feed_data} WHERE feed_id = ?';

      try {
        Dal::query($sql, array($this->feed_id));
      }
      catch(PAException $e) {
        Logger::log("Update ExternalFeed::delete_user_feed_data failed for feed_id = $this->feed_id");
        throw $e;
      }

      Logger::log("Exit: ExternalFeed::delete_user_feed_data");
    }

    /**
    * Function to fetch the latest data from the import url.
    * @param feed_id
    */
    public function refresh_feed_data() {
      Logger::log("Enter: ExternalFeed::refresh_feed_data");

      $sql = 'SELECT last_build_date FROM {external_feed} WHERE feed_id = ?';

      try{
        $res = Dal::query($sql, array($this->feed_id));
        if( $res->numRows() ) {
          $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
          $seconds_since_update = time() - $row->last_build_date;

          if($seconds_since_update - FEED_REFRESH_TIME > 0) {
            $this->do_refresh = true;//will be set when last_build_date is expired by more than FEED_REFRESH_TIME seconds
          }

          if($this->do_refresh) {
            $feed_details = $this->get_feeds();
            $this->import_url = $feed_details[0]->import_url;//setting import url from feeds will be fetched.
            if($this->import_url) {
              $this->delete_user_feed_data();//old feeds deleted for the import_url
              $this->import_posts();//fresh posts are being imported.
              $this->last_build_date = time();
              $this->update_last_build_date();
            }
            $this->do_refresh = false;
          }

        }
      }
      catch(PAException $e) {
        Logger::log("ExternalFeed::refresh_feed_data failed for feed_id = $this->feed_id");
        throw $e;
      }

      Logger::log("Exit: ExternalFeed::refresh_feed_data");
      return true;
    }

    /**
    * Function to save a feed for the user in user_feed table
    * @param feed_id, user_id
    * @return on success true, exception otherwise
    */
    private function save_user_feed() {
      Logger::log("Enter: ExternalFeed::save_user_feed");

      $sql = 'INSERT INTO {user_feed} (user_id, feed_id) VALUES (?, ?)';

      try {
        Dal::query($sql, array($this->user_id, $this->feed_id));
      }
      catch(PAException $e) {
        Logger::log("ExternalFeed::save_user_feed failed for user_id = $this->user_id and feed_id = $this->feed_id");
        throw $e;
      }

      Logger::log("Exit: ExternalFeed::save_user_feed");
      return true;
    }

    /**
    * Function to get the user feeds
    * @param user_id
    * @return array of feeds
    */
    public function get_user_feeds() {
      Logger::log("Enter: ExternalFeed::get_user_feeds");

      $sql = 'SELECT EF.feed_id, EF.import_url FROM {user_feed} AS UF LEFT JOIN {external_feed} AS EF ON UF.feed_id = EF.feed_id WHERE EF.is_active = ? AND UF.user_id = ? AND EF.feed_type = ?';

      try {
        $res = Dal::query($sql, array(ACTIVE, $this->user_id, $this->feed_type));
      } catch(PAException $e) {
        Logger::log("ExternalFeed::get_user_feeds failed for user_id = $this->user_id.");
        throw $e;
      }

      $user_feeds = array();

       if($res->numRows()) {
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
          $user_feeds[] = $row;
        }
      }

      Logger::log("Exit: ExternalFeed::get_user_feeds");
      return $user_feeds;
    }

    /**
    * Function to get the feed data for the user
    * @param user_id
    * @return array of object having feed related data.
    */
    public function get_user_feed_data() {
      Logger::log("Enter: ExternalFeed::get_user_feed_data");

      $user_feeds = array();
      $user_feeds = $this->get_user_feeds();

      $user_feed_data = array();
      if ($user_feeds) {
        foreach ($user_feeds as $feed) {
          $sql = 'SELECT * FROM {feed_data} WHERE feed_id = ? ORDER BY feed_id DESC';
          try {
            $res = Dal::query($sql, array($feed->feed_id));

            //refreshing the data for the feed
            $this->feed_id = $feed->feed_id;

            $this->refresh_feed_data();
            if ($res->numRows()) {
              while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $this->feed_id = $row->feed_id;

                $user_feed_data[] = $row;
              }
            }

          } catch (PAException $e) {
            Logger::log("ExternalFeed::get_user_feed_data failed for user_id = $this->user_id and feed_id = $feed->feed_id.");
            throw $e;
          }
        }//end try-catch block
      }//end if

      Logger::log("Exit: ExternalFeed::get_user_feed_data");
      return $user_feed_data;
    }

    /**
    * Function to import post from a given import url
    * @param $import_url : Feed url from where posts will be imported
    * @param $max_posts : Max number of posts to be imported from the feed.
    * @param array of posts.
    */
    public function import_feed() {
      Logger::log("Enter: ExternalFeed::import_feed");
      //initializing the object of the Domit Rss feed parser.

      $domit_obj = new xml_domit_rss_document_lite($this->import_url);
      $channel_count = $domit_obj->getChannelCount();
      $feed_data = array();
      if ($channel_count) {
        $post_count = 0;
        for ($counter = 0; $counter < $channel_count; $counter++) {

          $channel =& $domit_obj->getChannel($counter);
          $item_count = $channel->getItemCount($channel);

          for ($item = 0; $item < $item_count; $item++) {
            $item_obj =& $channel->getItem($item);
            $feed_data[$item]['title'] = $item_obj->getTitle($item);
            $feed_data[$item]['description'] = $item_obj->getDescription($item);
            $feed_data[$item]['original_url'] = $item_obj->getLink($item);
            $feed_data[$item]['publish_date'] = @strtotime($item_obj->getPubDate($item));


            if (++$post_count == $this->max_posts) {// if posts = max_posts allowed, futher import will be halted.
              return $feed_data;
            }

          }
        }
      }
      else {
        Logger::log("Exit: ExternalFeed::import_feed No posts are found for Import Url = $this->import_url");
        throw new PAException(IMPORT_FAILED, 'No posts are being imported. Given import url is incorrect or feed has yielded zero posts.');
        return $feed_data;
      }

      Logger::log("Exit: ExternalFeed::import_feed");
      return $feed_data;
    }

    /**
    * Generic function to get the data from feed_data table
    * @param array of parameters as key value pairs eg. array('feed_id'=>1, ''is_active'=>1)
    * @return data object
    */
    public function get_feed_data($params) {
      Logger::log("Enter: ExternalFeed::get_feed_data");

      $sql = 'SELECT * FROM {feed_data}';
      $data = array();
      if (count($params)) {
        $sql .= ' WHERE 1';
        foreach ($params as $key => $value) {
          $sql .= ' AND '.$key.' = ?';
          $data[] = $value;
        }
      }

      $sql .= ' ORDER BY publish_date DESC';

      //code for refreshing the feed data if params have feed_id
      if (!empty($params['feed_id'])) {
        $this->feed_id = $params['feed_id'];
        $this->refresh_feed_data();
      }

      try {
        $res = Dal::query($sql, $data);
      } catch (PAException $e) {
        Logger::log("Exit: ExternalFeed::get_feed_data. Query failed, associated sql = $sql");
        throw $e;
      }

      $feed_data_obj = array();
      if ($res->numRows()) {
        while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
          $feed_data_obj[] = $row;
        }
      }

      Logger::log("Enter: ExternalFeed::get_feed_data");
      return $feed_data_obj;
    }

  }


?>