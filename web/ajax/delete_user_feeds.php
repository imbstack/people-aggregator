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
$login_required = TRUE;
require_once dirname(__FILE__).'/../../config.inc';
include_once("web/includes/page.php");
require_once "ext/UserProfileFeed/UserProfileFeed.php";

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) and ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
   $msg = __("Blog Feed sucessfully deleted.");
   if(!empty($_REQUEST['feed_url']) && is_object(PA::$login_user)) {
     try {
       PA::$login_user->delete_profile_field('blogs_rss', 'blog_feed', (int)$_REQUEST['section_id']);
       PA::$login_user->delete_profile_field('blogs_rss', 'blog_title', (int)$_REQUEST['section_id']);
       PA::$login_user->delete_profile_field('blogs_rss', 'blog_url', (int)$_REQUEST['section_id']);

       $feeds = new UserProfileFeed();
       $feeds->user_id = PA::$login_uid;
       $feeds->set_feed_type(USER_PROFILE_FEED);
       $feed_data = $feeds->get_user_feeds();
       $feed_id = null;
       foreach($feed_data as $user_feed) {
         if($user_feed->import_url == $_REQUEST['feed_url']) {
           $feed_id = $user_feed->feed_id;
         }
       }
       if($feed_id) {
         $feeds->set_feed_id((int)$feed_id);
         $feeds->delete_user_feed_data();
         ExternalFeed::delete_user_feed($feed_id, PA::$login_uid );
         ExternalFeed::deleteByID($feed_id);
       }
     } catch(Exception $e) {
       $msg = "Error deleting feed: <pre>" . $e->getMessge() . "</pre>";
     }
   }
}
echo $msg;
?>