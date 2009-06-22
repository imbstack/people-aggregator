<?php

// Put constants used in the API code in here.
// Constants only used by the web code go in web/includes/constants.php.

/* constants for active, disabled and deleted users  */
define_once("ACTIVE", 1);
define_once("DISABLED", -1);
define_once("DELETED", 0);
define_once('INACTIVE', -1);
/*login_name = gurpreet, login_id = 17 will become #17#gurpreet after deleting the user in order to make login_name resusable.Earlier entry in user's table has been made unique by string manipulation(making it #17#gurpreet) as login_name is a unique key in users table.*/
define_once('MARK_DELETED_USER', '#');
define_once('UNVERIFIED', -2);
// is_active will be 2 for content subjected to moderation.
define_once('MODERATION_WAITING', 2); 

//-------------------------------------- defined for edit profile page 
//---------------------------------------when user uploads his desktop image
//-------------------following actions in form of radio buttons are given
define_once('DESKTOP_IMAGE_ACTION_STRETCH',1);
define_once('DESKTOP_IMAGE_ACTION_CROP',2);
define_once('DESKTOP_IMAGE_ACTION_TILE',3);
define_once('DESKTOP_IMAGE_ACTION_LEAVE',4);
define_once('DESKTOP_MAX_WIDTH',1000);
define_once('DESKTOP_MAX_HEIGHT',191);
//--------------------------------------------------
//--------------------------------------------------

//content type defined for external feeds
define_once('USER_FEED', 'user');
define_once('GROUP_FEED', 'group');
define_once('NETWORK_FEED', 'network');
define_once('USER_PROFILE_FEED', 'user_profile');
//

define_once("USER_ACCESS_READ", 1);
define_once("USER_ACCESS_WRITE", 2);

// Values for ContentCollection.type
define_once("GROUP_COLLECTION_TYPE",1);
define_once("ALBUM_COLLECTION_TYPE",2);

// Values for Album.album_type
define_once("IMAGE_ALBUM", 1);
define_once("AUDIO_ALBUM", 2);
define_once("VIDEO_ALBUM", 3);

//-------------------------------------- defined for content types
//-------------------------------------- used in Content and inherited apis
  define_once("BLOGPOST", 1);
  define_once("REVIEW", 2);
  define_once("EVENT", 3);
  define_once("IMAGE", 4);
  define_once("AUDIO", 5);
  define_once("VIDEO", 6);
  define_once("SBMICROCONTENT_EVENT", 7);
  define_once("SBMICROCONTENT_REVIEW", 8);
  define_once("SBMICROCONTENT_GROUP", 9);
  define_once("SBMICROCONTENT_PEOPLE", 10);
  define_once("QUESTION", 8);
  define_once("POLL", 11);
  define_once("TEK_VIDEO", 12);

//--------------------------------------------------
//--------------------------------------------------

//-------------------------------------- defined for constants used in api
//-------------------------------------- used in Content's inherited apis Image, Audio, Video
  define_once("NONE", 0);
  define_once("ANYONE", 1);
  define_once("WITH_IN_DEGREE_1", 2);
//--------------------------------------------------
//--------------------------------------------------



//constants for Relations table
define_once("IN_FAMILY",3);


//constants for ExternalFeeds
define_once("MAX_POSTS_PER_FEED", 5); //maximum number of posts allowed per feed.
define_once("FEED_REFRESH_TIME", 900);//after FEED_REFRESH_TIME seconds feed is ready for lastest data.

//constants for Network api
define_once('NETWORK_MAXIMUM_MEMBERS','128');
define_once('NETWORK_OWNER', 'owner');
define_once('NETWORK_MODERATOR','moderator');
define_once('NETWORK_MEMBER', 'member');
define_once('REGULAR_NETWORK_TYPE', 0);
define_once('MOTHER_NETWORK_TYPE', 1);
define_once('DISABLED_MEMBER', 'disabled');
define_once('ALL_NETWORKS', 'ALL');
// for private networks
define_once('PRIVATE_NETWORK_TYPE', 2);
// for approve deny of network membership
define_once('NETWORK_WAITING_MEMBER', 'waiting_member');


//constants for Message api
define_once("INBOX", 'Inbox');
define_once("SENT", 'Sent');
define_once("DRAFT", 'Draft');
define_once("REPLY", "reply");
define_once("FORWARD", "forward");
define_once("MESSAGES_PER_PAGE", 20);


//It is used while counting current online users
// 1800 seconds for now (half hour)
define_once("MAX_TIME_ONLINE_USER", 1800);

//maximum length allowed for the message in mymessage section
define_once("MAX_MESSAGE_LENGTH", 15000);

// constants for Reciprocated Relation
define_once ("DENIED", 'denied');
define_once ("APPROVED" , 'approved');
define_once ("PENDING", 'pending');

//constants for the operators
define_once("LIKE_SEARCH", 'LIKE');
define_once("GLOBAL_SEARCH", '*');
define_once("EXACT_SEARCH", '=');
define_once("RANGE_SEARCH", 'BETWEEN');
define_once("IN_SEARCH", 'IN');
define_once("GREATER_THAN", '>');
define_once("LESS_THAN", '<');
define_once("GREATER_THAN_EQUAL_TO", '>=');
define_once("LESS_THAN_EQUAL_TO", '<=');
define_once("NOT_EQUAL_TO", '<>');


// constants for the MessageBoard api These constant are defined in config.inc
//define_once("PARENT_TYPE_MESSAGE", 'message');
//define_once("PARENT_TYPE_COLLECTION", 'collection');

// constants for the Group api
define_once("MEMBER", 'member');
define_once("MODERATOR", 'moderator');
define_once("OWNER", 'owner');
define_once("NOT_A_MEMBER", 'not_a_member');

// constants for recent media display
define_once("RECENT_MEDIA_LIMIT",6);
  
define_once('ALLOW_ANONYMOUS',1);
define_once('ANONYMOUS_USER_ID',-1);

define_once("AGE_SEARCH", 'dob_search');//age search has been handled separately using the DATE_ADD mysyl function.
define_once("ALL_REGISTERED_USERS", 'All Registered Users');

// constants for abuse reporting
define_once("TYPE_COMMENT", 'comment');
define_once("TYPE_CONTENT", 'content');
define_once("TYPE_USER", 'user');
define_once("TYPE_ANSWER", 'answer');

// constant for Items
define_once('USER_CONTRIBUTED', 0);
define_once('ADMIN_CONTRIBUTED', 1);
define_once('ITEM_MESSAGE', 1);
define_once('ITEM_REVIEW', 2);
define_once('CELEBRITIY_TYPE', 1);
// contant for celeberities
define_once('FEATURE_FIELD_NAME', 'feature');
define_once('CELEBRITY_MESSAGE', 1);
define_once('CELEBRITY_REVIEW', 2);
define_once('CELEBRITY_HEADLINE', 3);
define_once('CELEBRITY_SONG', 4);
define_once('CELEBRITY_ALBUM', 5);
// constant for Rivers of people
define_once("POPULARITY",1);
define_once("TIME_INTERVAL", 2*24*60*60);//its number of seconds in two days 
?>