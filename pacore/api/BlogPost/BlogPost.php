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
require_once "api/Content/Content.php";
require_once "api/Logger/Logger.php";
require_once "api/Tag/Tag.php";
require_once "web/includes/functions/validations.php";



/**
 * Implements Blog Posts.
 * @extends Content
 */
class BlogPost extends Content {

  /**
   * class Content::__construct
   */
  public function __construct(){
    parent::__construct();
    //TODO: move to constants
    $this->type = 1;
  }
  
  /**
   * load blog post data in the object
   * @access public
   */
  public function load ($content_id) {
    Logger::log("Enter: BlogPost::load | Arg: \$content_id = $content_id");
    Logger::log("Calling: Content::load | Param: \$content_id = $content_id");
    parent::load($content_id);
    Logger::log("Exit: BlogPost::load");
    return;
  }
   
  /**
   * Saves blog post in db
   * @access public
   */
  public function save () {
    Logger::log("Enter: BlogPost::save");
    Logger::log("Calling: Content::save");

    parent::save();
    
    Logger::log("Exit: BlogPost::save");
    return;
  }

  // check post parameters for validity - return NULL if OK, or an error message if bad
  public static function validate_post_data($data_array) {
      if (empty($data_array['blog_title'])) {
          return "Title is empty or contains illegal chracters. Please try again.";    
      }
      
      if (empty($data_array['description']) ) {
          return "Description cannot be empty.";
      }

      // if we got this far, all is OK
      return NULL;
  }

  // called from createcontent.php to save a blog post
  public static function save_blogpost ($cid, $uid, $title, $body, $track, $tags, $ccid = 0, $is_active = 1, $display_on = 0, $is_default_content = FALSE) {
    // global var $path_prefix has been removed - please, use PA::$path static variable

    $errors = array();

    // ensure integers here
    $cid = (int)$cid;
    $uid = (int)$uid;
    $ccid = (int)$ccid;

    // if a new post, make one, otherwise load the existing one
    if ($cid) {
        $post = Content::load_content($cid, $uid);
        // ignore $ccid passed to function if the post already exists
        // - we don't allow users to move posts between
        // ContentCollections.
        $ccid = (int)$post->parent_collection_id;
    } else {
        $post = new BlogPost();
        $post->author_id = $uid;
        if ($ccid) {
            $post->parent_collection_id = $ccid;
        }
    }

    if ($ccid && $ccid != -1) {
        $g = ContentCollection::load_collection($ccid, $uid);
        $g->assert_user_access($uid);
    } else $g = NULL;

    $post->title = $title;
    $post->body = $body;
    $post->allow_comments = 1;
    $post->is_active = $is_active;
    $post->display_on = $display_on;
    $post->trackbacks = '';
    if ($track) {
      $post->trackbacks = implode(",", $track);
    }
    $post->is_default_content = $is_default_content;
    $post->save();
    //if ($tags) {
      Tag::add_tags_to_content($post->content_id, $tags);
    //}

    if ($track) {
      foreach($track as $t) {
        if (!$post->send_trackback($t)) {
          $errors[] = array(
            "code" => "trackback_failed",
            "msg" => "Failed to send trackback",
            "url" => $t,
            );
        }
      }
    }

    if ($g && !$cid) {
        // new post - post it to the group as well
        $g->post_content($post->content_id, $uid);
    }
   
    return array(
        "cid" => (int)$post->content_id,
        "moderation_required" => $g ? ($g->is_moderated == 1 && $g->author_id != $uid) : FALSE,
        "errors" => $errors,
        );
  }
  
  /**
   *  get rss feed of blogs for a user.
   *
   * @param $user_id string The user id of the user for getting RssFeed of the blogpost
   * @param $no_of_items string The No of latest items for which Rssfeed is Generated
   * @return $generated_rss_feed string This string contains the content of RssFeed file
   */
  public static function get_feed_for_user ($user_id, $no_of_items = 10) {
    global $TB_CONTENTS;
    Logger::log("Enter: BlogPost::get_feed_for_user()");

    // Check if user id given
    if (!$user_id ) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Required variable not specified", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required variable not specified");
    }

    //Query for selecting all title, description from table cotents for generation of RssFeed
    $sql = "SELECT * FROM {contents} WHERE author_id = ? AND type = 1 AND is_active = 1 ORDER BY changed DESC LIMIT ?";

    $data = array($user_id, $no_of_items);
    $res = Dal::query($sql, $data);
    $new_rss = new RssFeedHelper();
    $generated_rss_feed = $new_rss->get_rss_feed ($res, $user_id);
    Logger::log("Exit: BlogPost::get_feed_for_user()");
    return $generated_rss_feed;

  }

  /**
   *  get all blogs for a user.
   *
   * @param $user_id string The user id of the user for getting RssFeed of the blogpost
   */
  public static function get_user_blog ($user_id) {
    global $TB_CONTENTS;
    Logger::log("Enter: BlogPost::get_user_blog() | Args: \$user+id = $user_id");

    $res = Dal::query("SELECT * FROM {contents} WHERE author_id = ? AND type = ? AND is_active = ? ORDER BY changed DESC", array($user_id, 1, 1));
    $output .= "";
    if ($res->numRows()) {
      $contents = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $contents[] = array('content_id' => $row->content_id, 'title' => $row->title, 'body' => $row->body, 'author_id' => $row->author_id, 'created' => $row->created, 'changed' => $row->changed);
      }
      Logger::log("Exit: BlogPost::get_user_blog() | Returning ".implode($contents));
      return $contents;
    }
    Logger::log("Exit: BlogPost::get_user_blog() | Returning nothing");
  }
   
}
?>