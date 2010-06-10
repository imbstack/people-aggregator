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
 * @author Tekriti Software (www.TekritiSoftware.com)
 */
include_once dirname(__FILE__)."/../../config.inc";
require_once "db/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/User/User.php";
require_once "ext/Akismet/Akismet.php";
require_once "api/Comment/SpamDomain.php";

/* How the spam protection code works.

* submit_comment.php: comment submission from a user

- submit_comment.php calls $comment->spam_check(), and returns an
  error if it returns TRUE.  If it returns FALSE, the comment is
  posted, then $this->spam_analyze() is called by $this->save().

* tools/script/analyze_all_comments.php

- For each comment, $comment->index_spam_domains() is called.

* spam_check(): pre-save spam classification

- Reads all spam terms from the spam_terms table and returns TRUE if
  any of them match the comment, subject or homepage.  (Heavyweight -
  don't have too many spam terms or this will slow down the system).

- Calls $this->get_link_hosts() and checks each with new
  SpamDomain($domain).  If (new SpamDomain($domain))->blacklisted !=
  0, returns TRUE.

* spam_analyze(): post-save spam classification

- Calls $this->akismet_check()

  - akismet_check() checks the comment with akismet.com.  If Akismet
    classifies the comment as spam, the comment row in the DB is
    updated to set akismet_spam=1, spam_state=SPAM_STATE_AKISMET,
    is_active=0.

- Calls $this->index_spam_domains()

  - index_spam_domains calls SpamDomain::clear_domains_for_comment,
    then instantiates a SpamDomain object for each domain returned by
    $this->get_link_hosts().

  - If any of the domains are blacklisted, Comment::set_spam_state is
    set to delete the current comment and set
    spam_state=SPAM_STATE_DOMAIN_BLACKLIST.

In progress: index_words() indexes all the words in comments and puts
them in spam_terms with blacklisted=0 (unless already blacklisted).

*/

// spam states (for comments.spam_state column)
define("SPAM_STATE_OK", 0); // not spam
define("SPAM_STATE_MANUALLY_DELETED", 1); // deleted manually in edit interface
define("SPAM_STATE_SPAM_WORDS", 2); // deleted automatically due to presence of spam words
define("SPAM_STATE_AKISMET", 3); // deleted automatically due to a postitive check-comment response from akismet.com
define("SPAM_STATE_DOMAIN_BLACKLIST", 4); // deleted automatically due to a blacklisted domain (automatic or manual)
define("SPAM_STATE_TOO_MANY_LINKS", 5); // deleted automatically due to excessive # of links

/**
 * Class Comment represents a Comment item in the system.
 * Comments are related to the content.
 */

class Comment {

  /**
   * database handle
   * @access protected
   */
  protected $db;

  /**
   * comment_id is uniquely define the comment.
   * @access public
   * @var int
   */
  public $comment_id;

  /**
   * content_id relate the comment with content.
   * @access public
   * @var int.
   */
  public $content_id;

  /**
   * id of the user who has created the comment.
   * @access public
   * @var int.
   */
  public $user_id;

  /**
   * subject of comment.
   * @access public
   * @var string.
   */
  public $subject;

  /**
   * Actual body of the comment.
   * @access public
   * @var string.
   */
  public $comment;

  /**
   * time of creation of comment.
   * @access public
   * @var date
   */
  public $created;

  /**
   * time at which the comment is modified.
   * @access public
   * @var date
   */
  public $changed;

  /**
   * difine the status of comment mean deleted or not.
   * @access public
   * @var int
   */
  public $is_active=1;

  /**
   * name of the user who created the comment.
   * @access public
   * @var string
   */
  public $name;

  /**
   * email of the user who created the comment.
   * @access public
   * @var string
   */
  public $email;

  /**
   * homepage of the user who created the comment.
   * @access public
   * @var string
   */
  public $homepage;

  /**
   * for type in which comment has been posted eg 'user', 'content', 'group', 'contentcollection'
   * @access public
   * @var string
   */
   public $parent_type;

  /**
   * for id of parent type
   * @access public
   * @var integer
   */
   public $parent_id;

  /**
   * for state of spam.
   * @access public
   * @var integer
   */
   public $spam_state;

  /**
   * Saves comment to databse.
   * @access public.
   */
  public function save() {
    Logger::log("Enter: function Comment::save");

    $sql = "SELECT * FROM {contents} WHERE content_id = ?";
    $data = array($this->content_id);
    $res = Dal::query($sql, $data);

    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      if ($row->is_active == 0) {
        Logger::log("Throwing exception CONTENT_NOT_EXIST | The content on which you are posting a comment does not exist", LOGGER_ERROR);
        throw new PAException(COMMENT_NOT_EXIST, "The content on which you are posting a comment does not exist");
      }
    }

    // is this a new comment?
    $is_new_comment = !$this->comment_id;
    if ($is_new_comment) {
	if (!isset($this->ip_addr)) $this->populate_server_vars();
        $this->created = $this->changed = time();
    }

    if(!$this->user_id) {
      $this->user_id = -1;
      // TODO see if name is not validated earlier in executing page
      if(!$this->name) {
        throw new PAException(AUTHOR_NAME_NOT_PRESENT,"Name of the Comment author is not present");
      }

      if($this->comment_id) {
        // Checking the authenticity of the publishing user.
        $sql = "SELECT content_id, name, email, homepage FROM {contents} WHERE comment_id = ?";
        $res = Dal::query($sql, array($this->comment_id));

        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);

        if($row->content_id != $this->content_id) {
          Logger::log("Objects content_id is not matched with the comments content id although we are using comments content id for further processing that is",$row->content_id );
        }
        if($row->name != $this->name) {
          throw new PAException(USER_NOT_FOUND, "Objects name is not matched with the Comments name");
        }
        if($row->email != $this->email) {
          throw new PAException(USER_NOT_FOUND, "Objects email is not matched with the Comments email");
        }
        if($row->homepage != $this->homepage) {
          throw new PAException(USER_NOT_FOUND, "Objects homepage is not matched with the Comments homepage");
        }

        $this->changed = time();
        $sql = "UPDATE {comments} SET subject = ?, comment = ?, changed = ? WHERE comment_id = ?";
        $data = array($this->subject, $this->comment, $this->changed, $this->comment_id);
        $res = Dal::query($sql, $data);
      }
      else {

        $sql = "INSERT INTO {comments} ( content_id, user_id, subject, comment, created, changed, is_active, name, email, homepage, ip_addr, referrer, user_agent, parent_type, parent_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $data = array( $this->content_id, $this->user_id, $this->subject, $this->comment, $this->created, $this->changed, $this->is_active, $this->name, $this->email, $this->homepage, $this->ip_addr, $this->referrer, $this->user_agent, $this->parent_type, $this->parent_id);
        $res = Dal::query($sql, $data);
	$this->comment_id = Dal::insert_id();
	Logger::log("Saved anonymous comment $this->comment_id on content ID $this->content_id", LOGGER_ACTION);
      }
    }//... !$this->user_id
    else {
      if($this->comment_id) {
        // Checking the authenticity of the publishing user.
        $sql = "SELECT content_id, user_id FROM {comments} WHERE comment_id = ?";
        $res = Dal::query($sql, $this->comment_id);
        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);

        if($row->content_id != $this->content_id) {
          Logger::log("Objects content_id is not matched with the comments content id although we are using comments content id for further processing that is",$row->content_id );
        }
        if($row->user_id != $this->user_id) {
          throw new PAException(USER_NOT_FOUND, "Objects user_id is not matched with the Comments user_id");
        }

        $this->changed = time();
        $sql = "UPDATE {comments} SET subject = ?, comment = ?, changed = ? WHERE comment_id = ?";
        $data = array($this->subject, $this->comment, $this->changed, $this->comment_id);
        $res = Dal::query($sql, $data);
      }
      else {

        $sql = "INSERT INTO {comments} ( content_id, subject, comment, created, changed, is_active, user_id, name, email, homepage, ip_addr, referrer, user_agent, parent_type, parent_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $data = array( $this->content_id, $this->subject, $this->comment, $this->created, $this->changed, $this->is_active, $this->user_id, $this->name, $this->email, $this->homepage, $this->ip_addr, $this->referrer, $this->user_agent, $this->parent_type, $this->parent_id);
        $res = Dal::query($sql, $data);
	$this->comment_id = Dal::insert_id();
	Logger::log("Saved comment $this->comment_id by user $this->user_id on content ID $this->content_id", LOGGER_ACTION);
      }
    }
    $this->spam_analyze();
    Logger::log("Exit: function Comment::save \n", LOGGER_INFO);
    return ;
  }

  public function populate_server_vars() {
    $this->ip_addr = @$_SERVER['REMOTE_ADDR'];
    $this->referrer = @$_SERVER['HTTP_REFERER'];
    $this->user_agent = @$_SERVER['HTTP_USER_AGENT'];
  }

  /**
   * Uploads comment related to a comment id from database.
   * @access public.
   * @param comment id of the comment.
   */
  public function load($comment_id) {
    //log message
    Logger::log("[ Enter: function Comment::load | Args: \$comment_id = $comment_id ]\n");

    $sql = "SELECT * FROM {comments} WHERE comment_id = ?";
    $data = array($comment_id);
    $res = Dal::query($sql, $data);

    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      if ($row->is_active == 0) {
        Logger::log("Throwing exception COMMENT_NOT_EXIST | The Comment you are trying to load, is already deleted from database", LOGGER_ERROR);
        throw new PAException(COMMENT_NOT_EXIST, "Comment you are trying to load is deleted from database");
      }
      $this->content_id = $row->content_id;
      $this->comment_id = $row->comment_id;
      $this->user_id = $row->user_id;
      $this->subject = $row->subject;
      $this->comment = $row->comment;
      $this->created = $row->created;
      $this->changed = $row->changed;
      $this->is_active = $row->is_active;
      $this->name = $row->name;
      $this->email = $row->email;
      $this->homepage = $row->homepage;
      $this->ip_addr = $row->ip_addr;
      $this->user_agent = $row->user_agent;
      $this->referrer = $row->referrer;
      $this->akismet_spam = $row->akismet_spam;
      $this->spam_state = $row->spam_state;
      $this->parent_type = $row->parent_type;
      $this->parent_id = $row->parent_id;
    }
    Logger::log("[ Exit: function Comment::load ]\n", LOGGER_INFO);
    return;
  }

  /* initialize a comment from a row from the db - to save an extra query calling load($comment_id) above */
  public function load_from_row($row) {
    foreach ($row as $k => $v) {
      $this->$k = $v;
    }
  }

  /**
   * deletes a comment from database.
   * @access public.
   */
  public function delete() {
    Logger::log("[ Enter: function Comment::delete | Args: \$comment_id = $this->comment_id ]\n");

    $sql = "SELECT * FROM {comments} WHERE comment_id = ?";
    $data = array($this->comment_id);
    $res = Dal::query($sql, $data);

    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      if ($row->is_active == 0) {
        Logger::log("Throwing exception COMMENT_NOT_EXIST | The Comment you are trying to delete, is already deleted from database", LOGGER_ERROR);
        throw new PAException(COMMENT_NOT_EXIST, "The Comment you are trying to delete, is already deleted from database");
      }
    }
    $sql = "UPDATE {comments} SET is_active = 0 WHERE comment_id = ?";
    $data = array($this->comment_id);
    $res = Dal::query($sql,$data);

    $this->is_active = 0;
    Logger::log("Exit: function Comment::delete\n");
    return;
  }

  static function count_comments_for_content($content_id) {
    return Dal::query_first("SELECT COUNT(*) FROM {comments} WHERE is_active=1 AND content_id=? AND parent_type <> ?", array($content_id, TYPE_USER));
  }

  /**
   * loads all the comments of a content id.
   * @access public.
   * @param content id of the content
   *
   * @return this function return the all comments related to a content.
   */
  static function get_comment_for_content($content_id = NULL, $count = '', $order_by = 'DESC', $ignore_collection = NULL ) {
    Logger::log("Enter: static function Comment::get_comment_for_content id=$content_id, count=$count, order_by=$order_by");
    $content_comments = array();
    $i = 0;
    if($count) {
        $limit = 'LIMIT '.$count;
    } else $limit = "";

    $sql = "SELECT COM.comment_id, COM.content_id, COM.user_id, COM.subject,
COM.comment, COM.created, COM.changed, COM.name, COM.email, COM.homepage FROM
{comments} AS COM, {contents} AS CON WHERE 1 AND COM.is_active = ? AND
COM.content_id=CON.content_id AND CON.is_active = ? AND COM.parent_type <> ? AND COM.parent_type <> ?";
    $data = array(ACTIVE, ACTIVE, TYPE_ANSWER, TYPE_USER);
    if ( $content_id ) {
      $sql .=" AND COM.content_id = ?";
      $data[] = $content_id;
    }
    if ( $ignore_collection ) {
      $sql .=" AND CON.collection_id = ? ";
      $data[] = INACTIVE;
    }

   $sql .=" ORDER BY COM.created $order_by $limit";
    $res = Dal::query($sql, $data);

    if ($res->numRows() > 0) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $content_comments[$i] = array('comment_id' => $row->comment_id, 'content_id'=>$row->content_id, 'user_id' => $row->user_id, 'comment' => stripslashes($row->comment), 'created' => $row->created, 'changed' => $row->changed, 'name' => stripslashes($row->name), 'email' => stripslashes($row->email), 'homepage' => stripslashes($row->homepage));
        if ( $row->user_id != -1 ) {
          $user = new User();
          $user->load((int)$row->user_id);
          $content_comments[$i]['name'] = $user->display_name;
          $content_comments[$i]['email'] = $user->email;
          $content_comments[$i]['picture']=$user->picture;
        }
        $i++;
      }
    }

    Logger::log("Exit: static function Comment::get_comment_for_content");

    return $content_comments;
  }

  /**
   * Delete all the comments of a content id.
   * @access public.
   * @param content id of the content
   *
   */
  static function delete_all_comment_of_content($content_id, $is_active = 0) {
    Logger::log("Enter: static function Comment::delete_all_comment_of_content content_id = $content_id");

    $sql = "UPDATE {comments} SET is_active = ? WHERE content_id = ?";
    $data = array($is_active, $content_id);
    $res = Dal::query($sql,$data);
    Logger::log("Exit: static function Comment::delete_all_comment_of_content content_id = $content_id");

    return;
  }

  /**
   * loads all the comments of a user.
   * @access public.
   * @param user_id id of the user
   *
   * @return this function return the all comments posted by a User.
   */
  static function get_comment_for_user($user_id = NULL, $count = 10) {
    Logger::log("Enter: static function Comment::get_comment_for_content");

    $content_comments = array();
    $i = 0;

    $limit = 'LIMIT '.$count;


    $sql = "SELECT * FROM {comments} WHERE user_id = ? AND is_active = ?
AND parent_type <> ? ORDER BY created DESC $limit";
    $data = array($user_id, 1, TYPE_ANSWER);

    $res = Dal::query($sql,$data);

    $user_comments = array();
    if ($res->numRows() > 0) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $user_comments[$i] = array('comment_id' => $row->comment_id, 'content_id'=>$row->content_id, 'user_id' => $row->user_id, 'comment' => stripslashes($row->comment), 'created' => $row->created, 'changed' => $row->changed, 'is_active' => $row->is_active, 'name' => stripslashes($row->name), 'email' => stripslashes($row->email), 'homepage' => stripslashes($row->homepage));
        if ( $row->user_id != -1 ) {
          $user = new User();
          $user->load((int)$row->user_id);
          $user_comments[$i]['name'] = $user->display_name;
          $user_comments[$i]['email'] = $user->email;
        }
        $i++;
      }
    }
    Logger::log("Exit: static function Comment::get_comment_for_content");

    return $user_comments;
  }


  // comment spam functions

  /* Count all comments that a user is able to administer.

     $uid: pass a user ID to count all comments that user $uid can
     administer.  Set to 0 to count all active comments in the
     system. */
  static function count_all_comments($uid = 0) {
    if (!$uid) {
      // simple case - count all active comments in the system
      list($ct) = Dal::query_one("SELECT COUNT(*) FROM {comments} WHERE is_active = 1");
      return (int)$ct;
    }

    $total = 0;

    // user's blog
    // indices needed:
    // contents: content_for_user UNIQUE (is_active, collection_id, author_id, content_id)
    // comments: comments_for_content UNIQUE (is_active, content_id, comment_id)
    list($ct) = Dal::query_one("SELECT COUNT(cmt.comment_id)
        FROM {contents} ct /*FORCE KEY(content_for_user)*/
            LEFT JOIN {comments} cmt ON ct.content_id = cmt.content_id /*FORCE KEY(comments_for_content)*/
        WHERE ct.collection_id = -1
            AND ct.author_id = ?
            AND ct.is_active = 1
            AND cmt.is_active = 1", array($uid));
    $total += (int)$ct;

    // all groups created by the user
    // indices needed:
    // groups: groups_for_user UNIQUE (is_active, author_id, group_id)
    // contents: content_for_collection UNIQUE (collection_id, is_active, content_id)
    // comments: comments_for_content UNIQUE (is_active, content_id, comment_id)
    list($ct) = Dal::query_one("SELECT COUNT(cmt.comment_id)
        FROM {groups_users} gu /*FORCE KEY(groups_for_user)*/
            LEFT JOIN {contents} ct ON gu.group_id = ct.collection_id /*FORCE KEY(content_for_collection)*/
            LEFT JOIN {comments} cmt ON cmt.content_id = ct.content_id /*FORCE KEY(comments_for_content)*/
        WHERE gu.user_id = ?
            AND gu.user_type = 'owner'
            AND ct.is_active = 1
            AND cmt.is_active = 1", array($uid));
    $total += (int)$ct;

    return $total;
  }

  /* get_all_comments(): Fetch all comments that a user is able to
     administer.

     $uid: pass a user ID to limit results to those that the given uid
     can administer.  Set to 0 if running as an administrator.

     $show: pass 'ALL' to retrieve all comments (dangerous!) or pass
     an integer to set the number of comments to return per page.

     $page: number of the page of comments to return; from 1 to
     ceil(total comments / $show).

  */
  static function get_all_comments($uid = 0, $show = 'ALL', $page = 1, $sort_by = 'created', $direction = 'DESC') {

    if ($show == 'ALL') {
      $limit = "";
    } else {
      $limit = "LIMIT ".(($page - 1) * $show).", $show";
    }

    $select_fields = "ct.collection_id ct_collection_id, ct.author_id ct_author_id, ct.title comment_title";
    foreach (array("comment_id", "content_id", "created", "ip_addr", "name", "email", "homepage", "subject", "comment", "akismet_spam", "spam_state", "user_id") as $field) {
      $select_fields .= ", cmt.$field $field";
    }

    if (!$uid) {
      // selecting from all active comments in the system
      $sth = Dal::query(
        "SELECT $select_fields
            FROM {comments} cmt
              LEFT JOIN {contents} ct ON ct.content_id = cmt.content_id
            WHERE cmt.is_active = 1 AND ct.is_active = 1
         ORDER BY $sort_by $direction
         $limit");
    } else {
      // selecting comments that are administerable by the given user
      $sth = Dal::query(
        "(SELECT $select_fields
            FROM {contents} ct /*FORCE KEY(content_for_user)*/
              LEFT JOIN {comments} cmt ON ct.content_id = cmt.content_id /*FORCE KEY(comments_for_content)*/
            WHERE ct.collection_id = -1
              AND ct.author_id = ?
              AND ct.is_active = 1
              AND cmt.is_active = 1)
         UNION
         (SELECT $select_fields
            FROM {groups_users} gu /*FORCE KEY(groups_for_user)*/
              LEFT JOIN {contents} ct ON gu.group_id = ct.collection_id /*FORCE KEY(content_for_collection)*/
              LEFT JOIN {comments} cmt ON cmt.content_id = ct.content_id /*FORCE KEY(comments_for_content)*/
            WHERE gu.user_id = ?
              AND gu.user_type = 'owner'
              AND ct.is_active = 1
              AND cmt.is_active = 1)
         ORDER BY $sort_by $direction
         $limit", array($uid, $uid));
    }

    $cmts = array();
    while ($r = Dal::row_assoc($sth)) {
      $cmts[] = $r;
    }
    $sth->free();

    return $cmts;
  }

  /* full-text search on comments

     currently only searches the ENTIRE set of comments, so it should
     only be usable by administrators and will be very slow.
  */
  static function search($text, $limit=0) {
    $text = Dal::quote($text);
    $limit_sql = $limit ? " LIMIT $limit" : "";
    $sth = Dal::query("SELECT content_id, comment_id FROM {comments} WHERE is_active = 1 AND (name LIKE '%$text%' OR subject LIKE '%$text%' OR homepage LIKE '%$text%' OR comment LIKE '%$text%') $limit_sql");
    return Dal::all_assoc($sth);
  }

  /* fetch a specified set of comments.

     $limit_set: array(123, 456, ...) <-- array of comment IDs

  */
  static function get_selected($comment_ids) {
    return Dal::query_multiple(
      "SELECT * FROM {comments} WHERE comment_id = ?",
      array_map(create_function('$id', 'return array($id);'), $comment_ids));
  }

  /* add a term into the spam_terms table */
  static function add_spam_term($term, $blacklist=1) {
    $r = Dal::query_one("SELECT id FROM spam_terms WHERE term=?", array($term));
    if ($r[0]) return; // we already have this term

    Dal::query("INSERT INTO spam_terms SET term=?, blacklist=?", array($term, $blacklist));
  }

  /* set the spam state of a bunch of comments */
  static function set_spam_state($comment_ids, $new_state) {
    $is_active = $new_state ? 0 : 1; // setting spam_state=0 will undelete a comment
    $new_state = (int)$new_state; // just in case
    Dal::execute_multiple(
      "UPDATE {comments} SET is_active=$is_active, spam_state=$new_state WHERE comment_id=?",
      array_map(create_function('$id', 'return array($id);'), $comment_ids));
  }

  /* Extracts all links from a post.

     Example return:
     array(
       'http://foobar.typepad.com/myblog/' => // link urls are keys
         array('my blog', 'link to my blog'), // array of link text
       'http://asdf.typepad.com/spamblog' => // link url
         array('generic viagra', 'phentermine'), // link text
       'http://viagra.spammy-domain.com/cheap-viagra-online.html' =>
         array("viagra", "cheap viagra"),
     )

  */
  function get_links() {
    $links = array();
    if ($this->homepage && $this->name) $links[$this->homepage] = array($this->name);
    if ($this->comment) {
      // look for forum-style links
      if (preg_match_all("|\[url=(.*?)\](.*?)\[/url\]|", $this->comment, $matches, PREG_SET_ORDER)) {
	foreach ($matches as $m) {
	  list(, $url, $linktext) = $m;
	  $url = trim($url); $linktext = trim($linktext);
	  if (isset($links[$url])) {
	    $links[$url][] = $linktext;
	  } else {
	    $links[$url] = array($linktext);
	  }
	}
      }

      // and html links
      $dom = new DOMDocument();
      $dom->loadHTML($this->comment);
      $xp = new DOMXPath($dom);
      //    echo "comment: ".htmlspecialchars($this->comment)."<br>";
      foreach ($xp->query("//a[@href]") as $node) {
	$url = $node->getAttribute("href");
	//echo "<li>link: ".htmlspecialchars($url)."</li>";
	$linktext = $xp->query("text()", $node)->item(0)->data;
	if (isset($links[$url])) {
	  $links[$url][] = $linktext;
	} else {
	  $links[$url] = array($linktext);
	}
      }

      // and plain text urls
      if (preg_match_all("|(http://[A-Za-z0-9\:/\-\.]+)|", strip_tags($this->comment), $matches, PREG_SET_ORDER)) {
	foreach ($matches as $url) {
	  list($url, $linktext) = $url;
	  if (isset($links[$url])) {
	    $links[$url][] = $linktext;
	  } else {
	    $links[$url] = array($linktext);
	  }
	}
      }
    }
    return $links;
  }

  /* Extracts all links from the comment, and groups them by 'top
     level host'.

     Example return:
     array(
       'typepad.com' => array( // top level hostnames are keys
         'http://foobar.typepad.com/myblog/' => // link urls are keys
           array('my blog', 'link to my blog'), // array of link text
         'http://asdf.typepad.com/spamblog' => // link url
           array('generic viagra', 'phentermine'), // link text
       ),
       'spammy-domain.com' => array( // another domain
         'http://viagra.spammy-domain.com/cheap-viagra-online.html' =>
           array("viagra", "cheap viagra"),
       ),
     )

   */
  public function get_link_hosts() {
    // global var $path_prefix has been removed - please, use PA::$path static variable
    if(!$content = @file_get_contents(PA::$project_dir. "/api/Comment/two-level-tlds.txt")) {
        $content = file_get_contents(PA::$core_dir. "/api/Comment/two-level-tlds.txt");
    }
    $two_level_tlds = array_flip(explode("\n", $content));

    $hosts = array();
    foreach ($this->get_links() as $url => $linktexts) {
      $hostname_parsed = parse_url($url);
      $hostname_bits = explode(".", $hostname_parsed['host']);
      if (count($hostname_bits) < 2) {
	//$msg .= "<li>invalid hostname: ".htmlspecialchars($url)."</li>";
	continue;
      }
      // chop off the last two
      $last_two = implode(".", array_slice($hostname_bits, -2));
      // $top_domain_length = 3 for country domains like coffee.gen.nz, or = 2 for intl domains like topicexchange.com
      $top_domain_length = (isset($two_level_tlds[$last_two])) ? 3 : 2;
      if (count($hostname_bits) < $top_domain_length) {
	//$msg .= "<li>invalid hostname (too short for CCTLD): ".htmlspecialchars($url)."</li>";
	continue;
      }
      // extract the domain name (TLD + next host part)
      $domain = strtolower(implode(".", array_slice($hostname_bits, -$top_domain_length)));
      if (!isset($hosts[$domain])) $hosts[$domain] = array();
      $hosts[$domain][] = array($url, $linktexts);
    }
    return $hosts;
  }


  /* [run this after saving the comment] processes the comment and
     processes any links found, putting the domains into spam_domains
     and domains_in_comments tables */
  private function spam_analyze() {
    if (!$this->comment_id) throw new PAException(INVALID_ID, "Can't spam-analyze a comment without an ID");

    $this->akismet_check();
    $this->index_spam_domains();
  }

  /* experimental - not used in the normal course of operation */
  public function analyze_text() {
    // text : html ratio
    $comment_len = strlen($this->comment);

    $stripped = preg_replace("|\s*<[^>]*>\s*|", "", $this->comment);
    $text_len = strlen($stripped);

    $linktext_len = 0;
    $link_ct = 0;
    foreach ($this->get_links() as $url => $linktexts) {
      foreach ($linktexts as $linktext) {
	$linktext_len += strlen($linktext);
      }
      $link_ct += count($linktexts);
    }

    //    list($link_ct) = Dal::query_one("SELECT SUM(occurrences) FROM domains_in_comments WHERE comment_id=?", array($this->comment_id));

    /*    if ($comment_len) {
      echo "$this->comment_id: text len $text_len : total len $comment_len = ".((float)$text_len/$comment_len)
	.(!$linktext_len ? "" : ("; text len : linktext len $linktext_len = ".((float)$text_len/$linktext_len)))
	."; $link_ct links; active: $this->is_active\n";
    }*/
  }

  public function index_spam_domains($noisy=FALSE) {
    SpamDomain::clear_domains_for_comment($this->comment_id);

    $blacklisted = 0;

    $hosts = $this->get_link_hosts();
    $link_ct = 0;
    foreach ($hosts as $domain => $links) {
      foreach ($links as $url => $linktexts) {
	$link_ct += count($linktexts);
      }
      $domain = new SpamDomain($domain, $noisy);
      try {
	$domain->link_to_comment($this->comment_id, count($links));
      } catch (Exception $e) {
	echo "Exception occurred processing comment $this->comment_id, domain $domain\n";
	echo "hosts: "; var_dump($hosts);
	throw $e;
      }
      if ($domain->blacklisted) $blacklisted = 1;
    }

    if ($blacklisted) {
      Comment::set_spam_state(array($this->comment_id), SPAM_STATE_DOMAIN_BLACKLIST);
      //      echo "setting spam (blacklist) flag for $this->comment_id due to a bad domain.\n";
      return TRUE;
    }

    if ($link_ct >= 10) {
      //      echo "deleting $this->comment_id due to too many links.\n";
      Comment::set_spam_state(array($this->comment_id), SPAM_STATE_TOO_MANY_LINKS);
      return TRUE;
    }
  }

  // experimental - index the words in the comment, see if we can do some
  // spam detection that way.
  public function index_words() {
    echo "Comment $this->comment_id: $this->comment\n-----\n";
  }

  /* check against current blacklist(s) and return TRUE if the comment contains blacklisted text/links */
  function spam_check() {
    // check terms first
    $sth = Dal::query("SELECT term FROM spam_terms WHERE blacklist=1");
    while ($r = Dal::row($sth)) {
      $term = $r[0];
      if (strpos($this->comment, $term) !== FALSE
	  || strpos($this->subject, $term) !== FALSE
	  || strpos($this->homepage, $term) !== FALSE) {
	return TRUE; // spam!
      }
    }

    // now check domains and count up links
    $link_ct = 0;
    foreach ($this->get_link_hosts() as $domain => $links) {
      $domain = new SpamDomain($domain);
      if ($domain->blacklisted) {
	return TRUE; // spam!
      }
      foreach ($links as $url => $linktexts) {
	$link_ct += count($linktexts);
      }
    }

    // check number of links
    if ($link_ct >= 10) {
      return TRUE; // spam!
    }

    // not spam
    return FALSE;
  }


  /* ask akismet whether this is spam, and store the result in the db */
  function akismet_check() {
    global  $akismet_key;

    if (!@$akismet_key) return;

    $ak = new Akismet($akismet_key);
    $params = array(
		    "user_ip" => $this->ip_addr,
		    "user_agent" => $this->user_agent,
		    "referrer" => $this->referrer,
		    "permalink" => PA::$url . PA_ROUTE_CONTENT . "/cid=".$this->content_id,
		    "comment_type" => "comment",
		    "comment_author" => $this->name,
		    "comment_author_email" => $this->email,
		    "comment_author_url" => $this->homepage,
		    "comment_content" => $this->comment,
		    );

    if ($this->user_id > 0) {
      $user = new User();
      $user->load((int)$this->user_id);
      $params['comment_author'] = $user->get_name();
      $params['comment_author_email'] = $user->email;
      $params['comment_author_url'] = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $this->user_id;
    }

    $akismet_decision = $ak->check_spam(PA::$url, $params);
    switch ($akismet_decision) {
    case 'true': $this->akismet_spam = 1; break;
    case 'false': $this->akismet_spam = 0; break;
    default:
      // akismet couldn't make up its mind - probably we didn't give it enough data.
      return;
    }

    $sql = "";
    if ($this->akismet_spam) {
      $this->spam_state = SPAM_STATE_AKISMET;
      $this->is_active = 0;
      $sql .= ", spam_state=$this->spam_state, is_active=$this->is_active";
    }

    Dal::query("UPDATE comments SET akismet_spam=? $sql WHERE comment_id=?",
	       array($this->akismet_spam, $this->comment_id));
  }

  /**
  * function will update the status of all the comments of the specified user.
  */
  public static function delete_user_comments( $user_id ) {
    Logger::log("Enter: function Comment::delete_user_comments");

      $sql = 'UPDATE {comments} SET is_active = ? WHERE user_id = ?'  ;
      $data = array( DELETED, $user_id );

      Dal::query( $sql, $data );

    Logger::log("Exit: function Comment::delete_user_comments");
    return;
  }

  /**
    this function required parent type and parent id for submit any comment
  */
  public function save_comment() {
    Logger::log("Enter: function Comment::save_comment");

    // setting the variable for the spam
    $is_new_comment = !$this->comment_id;
    if ($is_new_comment) {
      if (!isset($this->ip_addr)) $this->populate_server_vars();
        $this->created = $this->changed = time();
      }

    // inserting the data into database
    $sql = "INSERT INTO {comments} ( content_id, subject, comment, created, changed, is_active, user_id, name, email, homepage, ip_addr, referrer, user_agent, parent_type, parent_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $data = array( $this->content_id, $this->subject, $this->comment, $this->created, $this->changed, $this->is_active, $this->user_id, $this->name, $this->email, $this->homepage, $this->ip_addr, $this->referrer, $this->user_agent, $this->parent_type, $this->parent_id);

    $res = Dal::query($sql, $data);

    $this->comment_id = Dal::insert_id();

    Logger::log("Saved comment $this->comment_id by user $this->user_id on content ID $this->content_id", LOGGER_ACTION);

    $this->spam_analyze();
    Logger::log("Exit: function Comment::save_comment \n", LOGGER_INFO);
  }

 /**
  * function will return all the comment on the content
  */

  public function get_multiples_comment($cnt = FALSE, $show = 'ALL', $page = 1, $sort_by = 'created', $direction = 'DESC') {
    Logger::log("Enter: function Comment::get_multiples_comment");

    if (empty($this->parent_id)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: parent id of comment is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Parent id of comment is empty');
    }

    if (empty($this->parent_type)) {
      Logger::log(" Throwing exception REQUIRED_PARAMETERS_MISSING | Message: parent type of comment is empty", LOGGER_ERROR);
      throw new PAException(REQUIRED_PARAMETERS_MISSING, 'Parent type of comment is empty');
    }

    $order_by = $sort_by.' '.$direction;
    if ($show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }

    $sql = "SELECT comment_id, user_id, comment, created, name, parent_id, parent_type
            FROM {comments}
            WHERE parent_type = ? AND parent_id = ? AND is_active = ?
            ORDER BY $order_by  $limit";

    $data = array($this->parent_type, $this->parent_id, ACTIVE);
    $res = Dal::query($sql, $data);

    if ($cnt) {
      Logger::log("Exit: Comment::get_multiples_comment()");
      return $res->numRows();
    }

    $result = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
    }

    Logger::log("Exit: function Comment::get_multiples_comment");
    return $result;
  }

  public static function count_comment($parent_id, $parent_type) {
    return Dal::query_first("SELECT COUNT(*) FROM {comments} WHERE is_active=1 AND parent_id=? AND parent_type=? ", array($parent_id, $parent_type));

  }

}

?>
