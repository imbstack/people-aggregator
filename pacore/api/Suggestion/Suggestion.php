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
require_once "api/BlogPost/BlogPost.php";
require_once "api/DB/Dal/Dal.php";

/**
 * A special kind of BlogPost, this class is for user Suggestions.
 * @extends BlogPost
 */
class Suggestion extends BlogPost {

	const TYPE_ID = 13;
	const TYPE_NAME = 'Suggestion';
	const TYPE_DESCRIPTION = 'A queued message from users to admins';

	/**
	 * class Content::__construct
	 */
	public function __construct() {
		parent::__construct();
/*
		if (!self::content_type_exists()) {
			self::create_content_type();
		}
		$this->type = self::get_content_type_id();
*/
		// @todo: use better method for assigning ID if it will not always have the same identifier
		$this->type = self::TYPE_ID;
	}

	public static function save_suggestion ($cid, $uid, $title, $body, $track, $tags, $ccid = 0, $is_active = 1, $display_on = 0, $is_default_content = FALSE) {
//		return BlogPost::save_blogpost($cid, $uid, $title, $body, $track, $tags, $ccid, $is_active, $display_on, $is_default_content);
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
			$post = new Suggestion();
			$post->author_id = $uid;
			if ($ccid) {
				$post->parent_collection_id = $ccid;
			}
		}

		if ($ccid && $ccid != -1) {
			$g = ContentCollection::load_collection($ccid, $uid);
			$g->assert_user_access($uid);
		} else {
			$g = NULL;
		}

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

		if (!$cid) {
			// add to suggestion queue automatically if not editing
			ModerationQueue::moderate_suggestion($post->content_id);
		}

		return array(
			"cid" => (int)$post->content_id,
			"moderation_required" => $g ? ($g->is_moderated == 1 && $g->author_id != $uid) : FALSE,
			"errors" => $errors,
		);
	}
/*
	public static function content_type_exists()
	{
		$sql = "SELECT type_id FROM {content_types} WHERE name LIKE ?";
		$res = Dal::query($sql, array(self::TYPE_NAME));

		if ($res->numRows()) {
			return true;
		}
		return false;
	}

	public static function create_content_type() {
		$sql = 'SELECT MAX(type_id) FROM {content_types}';
		$res = Dal::query($sql, array());
		if($res->numRows()) {
			$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$maxID = intval($row['type_id']);
		}
		$sql = "INSERT INTO {content_types} (type_id, name, description) VALUES (?, ?, ?)";
		Dal::query($sql, array($maxID++, self::TYPE_NAME, self::TYPE_DESCRIPTION));
	}

	public static function get_content_type_id() {
		$sql = "SELECT type_id FROM {content_types} WHERE name LIKE ?";
		$res = Dal::query($sql, array(self::TYPE_NAME));
		if($res->numRows()) {
			$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
			return $row['type_id'];
		}
		return 0;
	}
*/
}
?>