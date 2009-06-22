<?php

// default_config.php:
//
// This file contains default values for
// configuration variables that can be overridden
// in /pa/paproject/web/config/default_config.php
// and /pa/paproject/web/config/local_config.php
// files

// ONLY THE CORE TEAM SHOULD CHANGE THIS FILE.
// Project teams: use project_config.php
// Server administrators: use local_config.php


// also in script/version.sh; please also update that when bumping the version number!
define("PA_VERSION", "v2.0.0_18-May-2009");
define('CHECK_MIME_TYPE', 1);

//set it to 1 if you want to redirect to maintainence page always
define('SITE_UNDER_MAINTAINENCE', 0);
define('DEFAULT_DIRECTORY', 'pa');

define('ALPHA_USERNAME', 'paalpha');
define('ALPHA_PASSWORD', 'paalpha');


/**
*
*   SITE PERSONALIZATION
*
**/

// site name
PA::$site_name = "PeopleAggregator";

// default sender email
PA::$default_sender =  "no-reply@" . PA_DOMAIN_SUFFIX;

// gzip response to user if browser can handle it
// if (!defined("PA_DISABLE_BUFFERING") && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip")) ob_start("ob_gzhandler");

// used to turn on or off the fancy url = now it will display fancy url
define('FANCY_URL',0);


/** ---------------------------------------------------------------------------------------
*
*   DEBUGGING
*
**/

$debug_for_user = TRUE;

// set TRUE to get info about each rendered template added into the output HTML
$debug_annotate_templates = FALSE;

// set TRUE to turn off template caching (good if you are frequently changing templates)
$debug_disable_template_caching = FALSE;

// set TRUE to show the Subversion version on the bottom of each page,
// along with the timing.  This requires some XML parsing, so it's
// best to leave it off on a live site.
$debug_show_svn_version = FALSE;

$comments_disabled = FALSE;

$debug_annotate_templates = true;


/** ---------------------------------------------------------------------------------------
*
*   CLUSTERING
*
**/

// server ID (used in 'files' / 'file_sync_state' tables, for file replication)
PA::$server_id = 'main';



/** ---------------------------------------------------------------------------------------
*
*   INTERNATIONALIZATION
*
**/

// work in progress: try 'japanese' to see the homepage in Japanese.
PA::$language = 'english';
//echo date('Y-M-d H:i:s', 11958473833); die();
/*
echo PA::datetime('01.12.2008 17:01:25', 'long', 'short') . "<br />";
echo PA::datetime('01.12.2008 17:01:25', 'short', 'short'); die();
*/
/** ---------------------------------------------------------------------------------------
*
*   SIMPLE PA SETTINGS
*
**/

// various switches to enable/disable SimplePA related features
// we define a defaut array of switches here so that local_config
// can use and/or override whatever is prefered
// NOTE: to use this, simply set
// $_PA->simple = $default_simplePA_settings;
// in your local_config.php (or in dynamic_config.php for your project)
$default_simplePA_settings = array(
	'use_actionsmodule'   => TRUE, // insert the actionsModuke on every page
	'use_simplenav'       => TRUE, // remove 3rd tier nav, which is mostly handled by the ActionsModule
	'omit_advacedprofile' => TRUE, // don't show external accounts, ID hub, export etc
	'use_simpleblog'      => TRUE, // use the simplyfied Blog post feature
	'use_attachmedia'   => TRUE, // display 'add inage/video' field in simpleblog
	'omit_routing'      => TRUE, // do not let users rout their posts
);

$_PA->simple = $default_simplePA_settings;

/** ---------------------------------------------------------------------------------------
*
*   GROUP NOUN - HOW TO CALL THE GROUP IN THE UI
*
**/
PA::$group_noun = 'Group';
PA::$group_noun_plural = 'Groups';

PA::$people_noun = __('People');
PA::$mypage_noun = __('Me');

/** ---------------------------------------------------------------------------------------
*
*   API KEYS
*
**/

// - Facebook API keys -

// To obtain keys to work at your site, visit http://developers.facebook.com/account.php

//$facebook_api_key = '';
//$facebook_api_secret = '';

// - Flickr API key -

// The following key will work for you, but you should really obtain
// your own from: http://www.flickr.com/services/api/keys/

$flickr_api_key = 'bd94393ae4d7d48e30b6030299673877';
$flickr_api_secret = 'b6345b0bc4cb6ff2';
$flickr_auth_type = 'desktop';

// - AIM Presence and Web AIM keys -

// The following presence key will work for you, but you need to
// obtain a site-specific AIM API key to be able to chat on AIM from
// inside PeopleAggregator.

// Obtain your own presence key at: http://developer.aim.com/presenceReg.jsp
$aim_presence_key = 'dl1N5Swe5BsTc98r';

// Obtain your own AIM key at: http://developer.aim.com/wimReg.jsp
$aim_api_key = NULL;

// - 43(things|people|places).com API key -

// The following key will work for you, but you should really obtain
// your own from: http://www.43things.com/account/webservice_setup

$fortythree_api_key = "9519@Pe3U5HYxrY4rc";



/** ---------------------------------------------------------------------------------------
*
*   FILE UPLOADING
*
**/

//  file upload path
PA::$upload_path = $uploaddir = PA::$project_dir . "/web/files/";


// info about different types of files
$GLOBALS['file_type_info'] = array(
    'audio' => array(
        'type' => 'audio',
        'ext' => 'ac3|aif|aiff|asf|avi|mov|moov|mpa|mpg|mpeg|mp1|mp2|mp3|mp4|ogg|ram|wav|wma|rm',
        'max_file_size' => 10*1024*1024
        ),
    'video' => array(
        'type' => 'video',
        'ext' => 'aac|asf|avi|divx|m1v|m2p|m2v|mov|moov|mpg|mpeg|mpv|ogm|omf|swf|vob|wmv|mpe',
        'max_file_size' => 10*1024*1024
        ),
    'image' => array(
        'type' => 'image',
        'ext' => 'gif|jpg|jpeg|png|xpm|bmp',
        'max_file_size' => 2*1024*1024
        ),
    'doc' => array(
        'type' => 'doc',
        'ext' => 'txt|doc|rtf|pdf',
        'max_file_size' => 3*1024*1024
        ),
    );
// Note that php.ini's upload_max_filesize variable takes precedence
// if lower than any of the max_file_size values.  e.g. if
// upload_max_filesize is 2M, max_file_size values above will only be
// used if under 2M.



/** ---------------------------------------------------------------------------------------
*
*   DEFAULT MEDIA, LINKS, CATEGORIES
*
**/

// variables for default relation, media
// following line has been commented please add following line in your local_config.php
// and specify user id which should be added as friend to every body
// $default_relation_id = 1;

// media type file name, title, album name
$default_image_file_name = "arizona.gif";
$default_image_title = "Arizona";
$default_image_album_name = "My Image Album";

$default_audio_file_name = "sampler.mp3";
$default_audio_title = "sampler";
$default_audio_album_name = "My Audio Album";

$default_video_file_name = "marcPeepAggIntro.mov";
$default_video_title = "the Intro to PeepAgg video";
$default_video_album_name = "My Video Album";

$default_link_categories = array(
                                  0 => array('name' => 'Fav Blogs'),
                                  1 => array('name' => 'Great Sources'),
                                  2 => array('name' => 'Company'),
                                  3 => array('name' => 'Misc. fun places')

                                );

$default_links_array =
array(0 => array('title'=> 'Scripting News','url'=> 'http://scripting.com'),
1 => array('title'=> 'Scobleizer', 'url'=> 'http://scobleizer.wordpress.com/'),
2 => array('title'=> 'Doc Searls', 'url'=> 'http://doc.weblogs.com/'),
3 => array('title'=> 'Read/Write Web', 'url'=> 'http://www.readwriteweb.com/'),
4 => array('title'=> 'Napsterization', 'url'=> 'http://napsterization.org/stories/'),
5 => array('title'=> 'GigaOm (Om Malik)', 'url'=> 'http://gigaom.com/'),
6 => array('title'=> 'PaidContent.org', 'url'=> 'http://paidcontent.org/'),
7 => array('title'=> 'TechCrunch', 'url'=> 'http://techcrunch.com/'),
8 => array('title'=> 'Unmediated', 'url'=> 'http://www.unmediated.org/'),
9 => array('title'=> 'Between the Lines', 'url'=> 'http://blogs.zdnet.com/BTL/'),
10 => array('title'=> 'Broadband Mechanics', 'url'=> 'http://broadbandmechanics.com'),
11=> array('title'=> 'PeopleAggregator.com', 'url'=> 'http://PeopleAggregator.com'),
12 => array('title'=> 'PeopleAggregator.org', 'url'=> ' http://PeopleAggregator.org'),
13 => array('title'=> "Marc's Voice", 'url'=> 'http://marc.blogs.it'),
14 => array('title'=> 'PeepAgg Blog', 'url'=> 'http://peepagg.broadbandmechanics.com/'),
15 => array('title'=> 'Busblog (Tony Pierce)', 'url'=> 'http://www.tonypierce.com/blog/bloggy.htm'),
16 => array('title'=> 'Simply Recipes', 'url'=> 'http://www.elise.com/recipes/'),
17 => array('title'=> 'Pageflakes', 'url'=> 'http://www.pageflakes.com/'),
18 => array('title'=> 'WeeWorld', 'url'=> 'http://weeworld.com/'),
19 => array('title'=> 'WebJay.org', 'url'=> 'http://webjay.org/')
);


/** ---------------------------------------------------------------------------------------
*
*   E-MAIL, FORM & OTHER MESSAGES
*
**/

$outputthis_error_message = "You haven't specified any external blogs. Log on to <a href='http://outputthis.org' target='_blank'>OutputThis.org</a> to specify targets";
define('OUTPUTTHIS_ERROR_MESSAGE', $outputthis_error_message);

/*  Array for displaying messages in Community blog & My page . */
$post_type_message = array(
'all'=>array('message' => 'No content published.'),
'blog'=>array('message' => 'No blog posts published.'),
'event'=>array('message' => 'No events published.', 'queryString' => 'sb_mc_type=event/generic'),
'review'=>array('message' => 'No reviews published.', 'queryString' => 'sb_mc_type=review/localservice'),
'people_showcase'=>array('message' => 'No People Showcases published.', 'queryString' => 'sb_mc_type=showcase/person'),
'video'=>array('message' => 'No video published.', 'queryString' => 'sb_mc_type=media/video'),
'audio'=>array('message' => 'No audio published.', 'queryString' => 'sb_mc_type=media/audio'),
'image'=>array('message' => 'No image published.', 'queryString' => 'sb_mc_type=media/image'),
'group_showcase'=>array('message' => 'No Group Showcase published.', 'queryString' => 'sb_mc_type=showcase/group')
);

/** ---------------------------------------------------------------------------------------
*
*   MISC CONSTANTS & SETTINGS ??  someone know better block name??
*
**/

/* constants */
define('TAG_TYPE_COLLECTION',1);
define('TAG_TYPE_CONTENT',2);
define('DEFAULT_TAG_SOUP_SIZE',1);

define('DESCRIPTION_LENGTH', 300);
define('CHUNK_LENGTH', 26);
define('LONGER_CHUNK_LENGTH', 60);
define('TITLE_LENGTH', 38);
define('GROUP_TITLE_LENGTH', 18);
define('GROUP_TITLE_LENGTH_LONG', 25);
define('COMMENT_LENGTH', 30);
define('NAME_LENGTH', 15);

// Constants for forums
define('PARENT_TYPE_CATEGORY','category');
define('PARENT_TYPE_COLLECTION','collection');
define('PARENT_TYPE_NEWS','news');
define('PARENT_TYPE_MESSAGE','message');



/*  This array has all the fields in which we will allow the basic html tags  */
$allow_html_in_fields = array(
'description'=>'description',
'/event/description'=>'/event/description',
'/media/description'=>'/media/description',
'/review/description'=>'/review/description',
'/review/subject/activities'=>'/review/subject/activities',
'/review/subject/touristspots'=>'/review/subject/touristspots',
'/review/subject/historiclocations'=>'/review/subject/historiclocations',
'/group/description'=>'/group/description',
'/group/quote'=>'/group/quote',
'/group/principles'=>'/group/principles',
'/showcase/description'=>'/showcase/description',
'/showcase/contributions'=>'/showcase/contributions',
'/showcase/quote'=>'/showcase/quote',
'/showcase/wishlist"'=>'/showcase/wishlist',
'/showcase/movies'=>'/showcase/movies',
'/showcase/books'=>'/showcase/books',
'/showcase/musicians'=>'/showcase/musicians',
'/showcase/food'=>'/showcase/food',
'groupdesc'=>'groupdesc',
'comment'=>'comment',
'body'=>'body'
);


// This array has the field names in which HTML tags are allowed.
$tags_allowed_in_fields =
  array(
    'description'=>true,
    'groupdesc'=>true,
    'network_description'=>true,
    'comment'=>true,
    'forum_contents'=>true,
    'ad_script'=>true,
    'page_text'=>true,
    'email_message'=>true,
    'message'=>true,
    'bulletin_body'=>true);


/** ---------------------------------------------------------------------------------------
*
*   NETWORK DEFAULT SETTINGS
*
**/

// Strings that are not allowed to be used as network
// addresses (as in: http://<network address>.peopleaggregator.net/)
$invalid_network_address = array('ftp','mx','mail','pop','pop3','server1','server','smtp','webmail','www','default','blog','categories','configurable','content','content_routing','contentcollection','domains','domains_in','forgot','groups','mc','mc_db','message','moderation','modules','networks','page','page_default','private','relation','routing','routing_destination','spam','svn','tags','trackback','user','user_message','user_profile','users');


// Force beta theme - disable old basic and alpha themes
PA::$blockmodule_path = $current_blockmodule_path = 'web/BlockModules';
PA::$theme_rel  = $current_theme_rel_path = "Themes/Default";
PA::$config_path = "web/config";

// Ping Server URL

$ping_server = PA::$url . "/Ping/ping.php";

//Constant for Ping Server to define the type of activity
define('PA_ACTIVITY_USER_ADDED', 1);
define('PA_ACTIVITY_CONTENT_ADDED', 2);
define('PA_ACTIVITY_GROUP_ADDED', 3);
define('PA_ACTIVITY_NETWORK_ADDED', 4);

/** ---------------------------------------------------------------------------------------
*
*   SB MICROCONTENT SETTINGS
*
**/

$sb_dir_name    = 'sb-files';
$sb_upload_dir  = PA::$project_dir . "/web/$sb_dir_name";
$sb_upload_url  = PA::$url . "/$sb_dir_name";
$sb_mc_location = PA::$core_dir . "/MCdescriptions";

/*
define('SB_DIR_NAME','sb-files');
define('SB_UPLOAD_DIR',  "web/".SB_DIR_NAME);
define('SB_UPLOAD_URL',  PA::$url  . "/".SB_DIR_NAME);
define('SB_MC_LOCATION', "MCdescriptions");
*/

/** ---------------------------------------------------------------------------------------
*
*   CSS and JS optimizers
*
**/

//
$optimizers_use_url_rewrite = false;
$use_css_optimizer = false;
$use_js_optimizer = false;
$use_js_packer = false;
$cssjs_tag = md5(PA_VERSION);


/** ---------------------------------------------------------------------------------------
*
*   OTHER SETTINGS
*
**/

// for query count
$query_count_on_page = 0;
$query_count_array = array();

/** ---------------------------------------------------------------------------------------
*
*   DYNAMIC PAGES SETTINGS
*
**/

define("DYNAMIC_PAGES_DIR", "web/config/pages");
define('MIN_PASSWORD_LENGTH', 5);
define('MAX_PASSWORD_LENGTH', 15);

/** ---------------------------------------------------------------------------------------
*
*   Constant for profile information type.
*
**/
define("BASIC", 4);
define("GENERAL", 1);
define("PERSONAL", 2);
define("PROFESSIONAL", 3);

/** ---------------------------------------------------------------------------------------
*
*   System Roles
*
**/
define("ADMINISTRATOR_ROLE", 1);    // Network Admin Role
define("LOGINUSER_ROLE", 2);        // Default Login User Role
define("ANONYMOUS_ROLE", 3);        // Anonymous User Role
define("GROUP_ADMIN_ROLE", 4);      // Group Administrator Role
define("GROUP_MODERATOR_ROLE", 5);  // Group Moderator Role
define("GROUP_MEMBER_ROLE", 6);     // Group Member Role

/** -----------------------------------------------------------------------------------------
 * Tekmedia keys.
**/
//constants defined for tekmedia
PA::$video_accesskey = 'aab3238922bcc25a6f606eb525ffdc56';
PA::$video_secretkey = '871a67b6d6c4aae206c1b853536d7f4f';

// Request from tekmedia for video upload form

//To get the desired data from Tekmedia, send XML-RPC request to the following URL.
PA::$tekmedia_server = 'http://www.glued.in/client_webservice.php';

//The Tek Media Site url
PA::$tekmedia_site_url = 'http://www.glued.in';

//The iframe path to upload video on Tek media side
PA::$tekmedia_iframe_form_path = PA::$tekmedia_site_url.'/Integration/MyForm.php';
?>
