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
  * This file fetches feeds for the feed url in the user profile  
  * This file uses the domit rss feed parser which is a LGPL code.
  * @author : tekritisoftware
  * DB tables involved external_feed, feed_data, user_profile_data
  */
require_once 'api/ExternalFeed/ExternalFeed.php';

/**
This class has been employed to get the data for the feeds added by the
user in its profile. This extends the External Feed class in api's to parse the
rss feed.
*/
class UserProfileFeed extends ExternalFeed {

    public $feed_data_obj;

    /**
     * Function to process the user feed.
     * This function will check for the feed url added by the user in 
     * user_profile in external feeds. If it exists then it will return the associated 
     * feed_id otherwise it will add 
     */
    private function import_user_feed() {
        Logger::log("Enter: UserProfileFeed::import_user_feed");
        $sql = 'SELECT feed_id FROM {external_feed} WHERE import_url = ? AND is_active = ?';
        try {
            $res = Dal::query($sql, array($this->import_url, ACTIVE));
        }
        catch(PAException$e) {
            Logger::log("Exit UserProfileFeed::import_user_feed.Not able to get feed details for given import_url. Associated sql = $sql, import_url = $this->import_url");
            throw $e;
        }
        if($res->numRows()) {
            //given import url exists already in the external feed list
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            //TODO: refresh the feed data
            $this->feed_id = $row->feed_id;
        }
        else {
            // Given feed does not exists in the external feeds. So add it first and then return the associated feed_id.
            //setting the feed_type to user_profile feed
            $this->feed_type = USER_PROFILE_FEED;
            $this->save();
        }
        Logger::log("Exit: UserProfileFeed::import_user_feed");
        return $this->feed_id;
    }

    /**
    * Function to process a user profile feed.
    * It will update the user_profile_data for status of the feed.
    */
    public function process_user_feeds() {
        Logger::log("Enter: UserProfileFeed::process_user_feeds");
        //Getting user's blog_feeds from user_profile_data
        $profile_data = array();
        $params = array(
            'field_name' => 'blog_feed',
            'user_id' => $this->user_id,
        );
        $profile_data = User::get_profile_data($params);
        if(count($profile_data)) {
            foreach($profile_data as $profile_data_obj) {
                $this->import_url = $profile_data_obj->field_value;
                //importing the feed data from respective feed_url
                $this->import_user_feed();
                $this->feed_data_obj = $profile_data_obj;
                $this->update_feed_status();
            }
            //end for
        }
        //end if
        Logger::log("Exit: UserProfileFeed::process_user_feeds");
        return true;
    }

    /**
    * Function to update the status of particular feed for the user
    */
    private function update_feed_status() {
        Logger::log("Enter: UserProfileFeed::update_feed_status");
        $User = new User();
        $User->user_id = $this->user_id;
        $user_profile_data[] = array(
            'user_id'     => $this->user_id,
            'field_name'  => 'feed_id',
            'field_value' => $this->feed_id,
            'field_type'  => $this->feed_data_obj->field_type,
            'field_perm'  => $this->feed_data_obj->field_perm,
            'seq'         => $this->feed_data_obj->seq,
        );
        $params = array(
            'seq' => $this->feed_data_obj->seq,
        );
        //Saving data in user_profile table
        $User->save_user_profile_fields($user_profile_data, 'blogs_rss', 'feed_id', $params);
        Logger::log("Exit: UserProfileFeed::update_feed_status");
        return;
    }

    /**
    * Function for sanitizing the profile_feed_data to array like array('feed_id', 'title', 'blog_url', 'blog_feed')
    * TODO Add access permissions
    * $viewer_uid : User id of the user who is viewing the user's profile. It will be empty for anonymous user.
    */
    public static function get_user_profile_feeds($user_id, $viewer_uid = null) {
        Logger::log("Enter: UserProfileFeed::get_user_profile_feeds");
        $profile_data = User::load_user_profile($user_id, $viewer_uid, 'blogs_rss', 'seq DESC');
        $user_profile_feeds = array();
        // code for sanitizing the data to array('feed_id', 'title', 'blog_url', 'blog_feed')
        if(count($profile_data)) {
            $pervious_seq = null;
            $counter =-1;
            foreach($profile_data as $key => $value) {
                if($pervious_seq != $value['seq']) {
                    $user_profile_feeds[++$counter][$value['name']] = $value['value'];
                    $pervious_seq = $value['seq'];
                }
                else {
                    $user_profile_feeds[$counter][$value['name']] = $value['value'];
                }
            }
        }
        Logger::log("Exit: UserProfileFeed::get_user_profile_feeds");
        return $user_profile_feeds;
    }
}
?>