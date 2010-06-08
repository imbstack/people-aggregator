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
  $login_required = true;
  include '../includes/page.php';
  
  include 'ext/ExternalFeed/ExternalFeed.php';
  
 $ExternalFeed = new ExternalFeed();
 $import_url = 'http://blog.broadbandmechanics.com/feed/';
 try {
   $user = get_login_user();
   $ExternalFeed->user_id = SUPER_USER_ID;
   //$feed_data = $ExternalFeed->get_feed_data();
   $ExternalFeed->set_import_url($import_url);
   $ExternalFeed->save();
   //$ExternalFeed->set_feed_id(1);
   //$ExternalFeed->set_do_refresh(false);
   //$ExternalFeed->refresh_feed_data();
 }
 catch( PAException $e ) {
   echo $e->message;
 }
?>