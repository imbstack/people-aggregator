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
require_once "api/api_constants.php";
require_once "api/Content/Content.php";
require_once "api/DB/Dal/Dal.php";
require_once "api/Logger/Logger.php";

/**
 * Utility class for managing the queuing of content in PeopleAggregator.
 *
 * @author Jon Knapp (http://www.coffeeandcode.com)
 */
class ModerationQueue {

	const TYPE_CONTENT = 'content';

	/**
	 * The ModerationQueue class should not be instantiated.
	 */
	public function __construct() {
		return;
	}

	/**
	 * Approve contents and remove it from {moderation_queue} database table
	 *
	 * @access public
	 * @param int id of content
	 */
	public static function approve_content($content_id) {
		Logger::log("Enter: ModerationQueue::approve_content() | Args: \$content_id = $content_id");
		$sql = 'DELETE FROM {moderation_queue} WHERE item_id = ? and type= ?';
		Dal::query($sql, array($content_id, self::TYPE_CONTENT));
		Content::update_content_status($content_id, ACTIVE);
		Logger::log("Exit: ModerationQueue::approve_content()");
	}

	/**
	 * Check for Content in moderation queue.
	 *
	 * @access public
	 * @param int id of content
	 * @param int collection identifier
	 * if collection_id is not set then it is -1
	 * which indicates that content is not part of any collection
	 */
	public static function content_exists($content_id, $collection_id = -1) {
		Logger::log("Enter: ModerationQueue::content_exists() | Args: \$content_id = $content_id, \$collection_id = $collection_id");
		$sql = 'SELECT * FROM {moderation_queue} WHERE item_id = ? AND collection_id = ? AND type = ?';
		$res = Dal::query($sql, array($collection_id, $content_id, self::TYPE_CONTENT));

		if ($res->numRows()) {
			Logger::log("Exit: ModerationQueue::content_exists() | Return: TRUE");
			return TRUE;
		}

		Logger::log("Exit: ModerationQueue::content_exists() | Return: FALSE");
		return FALSE;
	}

	/**
	 * Disapprove contents and remove it from {moderation_queue} database table
	 *
	 * @access public
	 * @param int id of content
	 */
	public static function disapprove_content($content_id) {
		Logger::log("Enter: ModerationQueue::disapprove_content() | Args: \$content_id = $content_id");
		$sql = 'DELETE FROM {moderation_queue} WHERE item_id = ? and type= ?';
		Dal::query($sql, array($content_id, self::TYPE_CONTENT));
		Content::delete_by_id($content_id);
		Logger::log("Exit: ModerationQueue::disapprove_content()");
	}

	/**
	 * Add Content to ModerationQueue
	 *
	 * @access public
	 * @param int id of content
	 * @param int collection identifier
	 */
	public static function moderate_content($content_id, $collection_id = -1) {
		Logger::log("Enter: ModerationQueue::moderate_content() | Args: \$content_id = $content_id, \$collection_id = $collection_id");
		$sql = 'INSERT INTO {moderation_queue} (collection_id, item_id, type) VALUES (?, ?, ?)';
		Dal::query($sql, array($collection_id, $content_id, self::TYPE_CONTENT));
		Content::update_content_status($content_id, MODERATION_WAITING);
		Logger::log("Exit: ModerationQueue::moderate_content()");
	}

	/**
	 * Remove instance of Content from moderation queue.
	 *
	 * @access public
	 * @param int id of content
	 */
	public static function remove_content($content_id) {
		Logger::log("Enter: ModerationQueue::remove_content() | Args: \$content_id = $content_id");
		$sql = 'DELETE FROM {moderation_queue} WHERE item_id = ? AND type = ?';
		Dal::query($sql, array($content_id, self::TYPE_CONTENT));
		Logger::log("Exit: ModerationQueue::remove_content()");
	}

	/**
	 * Remove all instances of Content from a specific user from the moderation queue.
	 *
	 * @access public
	 * @param int id of content author
	 */
	public static function remove_content_from_user($user_id) {
		Logger::log("Enter: ModerationQueue::remove_content_from_user() | Args: \$user_id = $user_id");
		$sql = 'DELETE FROM MQ USING {moderation_queue} AS MQ INNER JOIN {contents} AS C ON MQ.item_id = C.content_id AND MQ.type = ? AND C.author_id = ?';
		Dal::query($sql, array(self::TYPE_CONTENT, $user_id));
		Logger::log("Exit: ModerationQueue::remove_content_from_user()");
	}
}