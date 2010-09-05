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
		$type = 'content';
		$res = Dal::query("DELETE FROM {moderation_queue} WHERE item_id = ? and type= ?", array($content_id, $type));
		Content::update_content_status($content_id, ACTIVE);
		Logger::log("Exit: ModerationQueue::approve_content()");
	}

	/**
	 * Disapprove contents and remove it from {moderation_queue} database table
	 *
	 * @access public
	 * @param int id of content
	 */
	public static function disapprove_content($content_id) {
		Logger::log("Enter: ModerationQueue::disapprove_content() | Args: \$content_id = $content_id");
		$type = 'content';
		$res = Dal::query("DELETE FROM {moderation_queue} WHERE item_id = ? and type= ?", array($content_id, $type));
		Content::delete_by_id($content_id);
		Logger::log("Exit: ModerationQueue::disapprove_content()");
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
		Logger::log("Enter: ModerationQueue::content_exists() | Args: \$item_id = $content_id, \$collection_id = $collection_id");
		$type = 'content';
		$res = Dal::query("SELECT * FROM {moderation_queue} WHERE item_id = ? AND collection_id = ? AND type = ?", array($collection_id, $content_id, $type));

		if ($res->numRows()) {
			Logger::log("Exit: ModerationQueue::content_exists() | Return: TRUE");
			return TRUE;
		}

		Logger::log("Exit: ModerationQueue::content_exists() | Return: FALSE");
		return FALSE;
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
		$res = Dal::query("INSERT INTO {moderation_queue} (collection_id, item_id, type) VALUES (?, ?, ?)", array($collection_id, $content_id, "content"));
		Content::update_content_status($content_id, MODERATION_WAITING);
		Logger::log("Exit: ModerationQueue::moderate_content()");
	}
}