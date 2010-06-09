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

include_once "api/api_constants.php";

//Constant for Ping Server to define the type of activity
define_once('PA_ACTIVITY_USER_ADDED', 1);
define_once('PA_ACTIVITY_CONTENT_ADDED', 2);
define_once('PA_ACTIVITY_GROUP_ADDED', 3);
define_once('PA_ACTIVITY_NETWORK_ADDED', 4);


// used for ajax sorting in BlockModules
define_once ('SORT_BY', 1);
define_once('BLOG_SETTING_STATUS_ALLDISPLAY',3);
define_once('BLOG_SETTING_STATUS_NODISPLAY',0);
define_once('PERSONAL_BLOG_SETTING_STATUS',1);
define_once('EXTERNAL_BLOG_SETTING_STATUS',2);

define_once('LONG_EXPIRES', 3600*24*15); // 15 days

// used for nework operator control actions
define_once('NET_NONE',0);
define_once('NET_EMAIL',1);
define_once('NET_MSG',2);
define_once('NET_BOTH',3);
define_once('NET_NO',0);
define_once('NET_YES',1);


define_once("DESKTOP_IMAGE_DISPLAY", 1);
define_once("GROUP_HEADER_IMAGE_DISPLAY", 1);
define_once("HEADER_IMAGE_DISPLAY_BLOCK", 2);

// used to denote announcement status
define_once("ANNOUNCE_LIVE", 1);

?>
