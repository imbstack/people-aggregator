<?php
// Routing rewrites:

  $core_routes = array (

    /**
    *
    *  /api -> /api/doc/index.php
    *
    **/
    "^/api[\/]?$"  => "/api/doc/index.php",

    /**
    *
    *  / -> /index.php
    *
    **/
    "^/$"  => "/index.php",

    /**
    *
    *  /user/username/ -> /dynamic.php?page_id=2&login=username or ID
    *
    **/
    "^/user[\/]+([^\/\?]+)[\/]?(.*)$"  => "/dynamic.php?page_id=2&login=%s&%s", // via login name

    /**
    *
    *  /users/username/ -> user_blog.php?login=username
    *
    **/
    "^/users/([^/]+)/?$"  => "/user_blog.php?login=%s",

    /**
    *
    *  /pages/title/ -> pages.php?caption=title
    *
    **/
    "^/links/([^/]+)/?$" => "/pages.php?caption=%s",

    /**
    *
    *  /tools/CreateDynamicPage/ -> dynamic.php?page_id=727
    *
    **/
    "^/tools/CreateDynamicPage/.*$" => "/dynamic.php?page_id=727",

    /**
    *
    *  /forums/* -> dynamic.php?page_id=201&*
    *
    *
    **/
    "^/forums[\/]?(.*)$" => "/dynamic.php?page_id=201&%s",

    /**
    *
    *  /group/customize/([^/]+)/.* -> /dynamic.php?page_id=87&stype=group&type=%s*
    *
    *  description: route for Configure Group UI Settings
    *
    *
    **/
    "^/group/customize/([^/]+)[\/]?(.*)$" => "/dynamic.php?page_id=68&stype=group&type=%s&%s",

    /**
    *
    *  /group/invitation[\/\?]/.* -> /dynamic.php?page_id=31&%s*
    *
    *  description: route for Group Invitation
    *
    *
    **/
    "^/group/invitation[\/\?](.*)$" => "/dynamic.php?page_id=31&%s",

    /**
    *
    *  /group/ad_center* -> dynamic.php?page_id=91&*
    *
    *
    **/
    "^/group/ad_center[/]?(.*)$" => "/dynamic.php?page_id=91&%s",

    /**
    *
    *  /group/moderation/* -> dynamic.php?page_id=8&*
    *
    *
    **/
    "^/group/moderation[\/\?](.*)$" => "/dynamic.php?page_id=26&%s",

    /**
    *
    *  /group/* -> dynamic.php?page_id=8&*
    *
    *
    **/
    "^/group[\/\?](.*)$" => "/dynamic.php?page_id=8&%s",

    /**
    *
    *  /groups/* -> dynamic.php?page_id=3&*
    *
    *
    **/
    "^/groups[\/]?(.*)$" => "/dynamic.php?page_id=3&%s",

    /**
    *
    *  /relations/edit/* -> dynamic.php?page_id=3&*
    *
    *
    **/
    "^/relations/edit[\/]?(.*)$" => "/dynamic.php?page_id=28&%s",

    /**
    *
    *  /invitation -> /dynamic.php?page_id=23
    *
    **/
    "^/invitation[\/]?(.*)$" => "/dynamic.php?page_id=23&%s",

    /**
    *
    *  /myAccount/editProfile/* -> dynamic.php?page_id=24&*
    *
    *
    **/
    "^/myAccount/editProfile[\/]?(.*)$ HTTPS" => "/dynamic.php?page_id=24&%s",

    /**
    *
    *  /myAccount/messages/* -> dynamic.php?page_id=15&*
    *
    *
    **/
    "^/myAccount/messages[\/]?(.*)$" => "/dynamic.php?page_id=15&%s",

    /**
    *
    *  /myAccount/newMessage/* -> dynamic.php?page_id=16&*
    *
    *
    **/
    "^/myAccount/newMessage[\/]?(.*)$" => "/dynamic.php?page_id=16&%s",

    /**
    *
    *  /media/gallery/([^/]+)/.* -> /dynamic.php?page_id=19&type=%s&%s*
    *
    *  description: route for Media Gallery
    *
    *
    **/
    "^/media/gallery/([^/]+)[\/]?(.*)$" => "/dynamic.php?page_id=19&type=%s&%s",

    /**
    *
    *  /myAccount/ -> dynamic.php?page_id=1
    *
    **/
    "^/me[\/]?(.*)$" => "/dynamic.php?page_id=1&%s",

    /**
    *
    *  /content/permalink/* -> dynamic.php?page_id=12&*
    *
    *
    **/
    "^/content/permalink[\/]?(.*)$" => "/dynamic.php?page_id=12&%s",

    /**
    *
    *  /contents/search/* -> dynamic.php?page_id=17&*
    *
    *
    **/
    "^/contents/search[\/]?(.*)$" => "/dynamic.php?page_id=17&%s",

    /**
    *
    *  /content/cid=1 -> dynamic.php?page_id=12&%s*
    *
    *
    **/
    "^/content[\/\?]?(.*)$" => "/dynamic.php?page_id=12&%s",

    /**
    *
    *  /configure/polls/* -> dynamic.php?page_id=16&*
    *
    *
    **/
    "^/configure/polls[\/]?(.*)$" => "/dynamic.php?page_id=99&%s",

    /**
    *
    *  /configure/ranking_points/ -> dynamic.php?page_id=200
    *
    **/
    "^/configure/ranking_points[\/]?(.*)$" => "/dynamic.php?page_id=200",

    /**
    *
    *  /configure/roles/* -> dynamic.php?page_id=70&*
    *
    *
    **/
    "^/configure/roles[\/]?(.*)$" => "/dynamic.php?page_id=70&%s",

    /**
    *
    *  /configure/ad_center* -> dynamic.php?page_id=71&*
    *
    *
    **/
    "^/configure/ad_center[/]?(.*)$" => "/dynamic.php?page_id=71&%s",

    /**
    *
    *  /configure/system* -> dynamic.php?page_id=737&*
    *
    *
    **/
    "^/configure/system[/]?(.*)$" => "/dynamic.php?page_id=737&%s",

    /**
    *
    *  /configure/defender* -> dynamic.php?page_id=738&*
    *
    *
    **/
    "^/configure/defender[/]?(.*)$" => "/dynamic.php?page_id=738&%s",

    /**
    *
    *  /leaderboard/ -> dynamic.php?page_id=202
    *
    **/
    "^/leaderboard[\/]?(.*)$" => "/dynamic.php?page_id=202",

    /**
    *
    *  /myAccount/customize/([^/]+)/.* -> /dynamic.php?page_id=87&stype=user&type=%s*
    *
    *  description: route for Configure User UI Settings
    *
    *
    **/
    "^/myAccount/customize/([^/]+)[\/]?(.*)$" => "/dynamic.php?page_id=104&stype=user&type=%s&%s",

    /**
    *
    *  /configure/network([^/]+) -> /network_statistics.php?%s*
    *
    *  description: main Network settings page
    *
    *  NOTE: this is temporrary route - network_statistics.php page is not refactored yet!
    **/
    "^/configure/network([^/]*)$" => "/network_statistics.php?%s",

    /**
    *
    *  /configure/network/([^/]+)/.* -> /dynamic.php?page_id=87&stype=network&type=%s*
    *
    *  description: route for Configure Network GUI Settings
    *
    *
    **/
    "^/configure/network/([^/]+)[\/]?(.*)$" => "/dynamic.php?page_id=87&stype=network&type=%s&%s",

    /**
    *
    *  /home/ -> dynamic.php?page_id=5
    *
    **/
    "^/home[\/\?]?(.*)$" => "/dynamic.php?page_id=5&%s",

    /**
    *
    *  /people/ -> dynamic.php?page_id=6
    *
    **/
    "^/people[\/]?(.*)$" => "/dynamic.php?page_id=6&%s",

    /**
    *
    *  /directory -> dynamic.php?page_id=300
    *
    **/
    "^/directory[\/]?$" => "/dynamic.php?page_id=300",

    /**
    *
    *  /directory/someType -> dynamic.php?page_id=300&type=someType
    *
    **/
    "^/directory/(\w*)$" => "/dynamic.php?page_id=300&type=%s",

    /**
    *
    *  /sys_message -> dynamic.php?page_id=205
    *
    **/
    "^/sys_message[\/\?]?(.*)$" => "/dynamic.php?page_id=205&%s",

    /**
    *
    *  /myAccount/contacts/* -> dynamic.php?page_id=206&*
    *
    *
    **/
    "^/myAccount/contacts[\/]?(.*)$" => "/dynamic.php?page_id=206&%s",

    /**
    *
    *  /forgotPassword -> dynamic.php?page_id=29*
    *
    *
    **/
    "^/forgotPassword[\/]?(.*)$" => "/dynamic.php?page_id=29&%s",

    /**
    *
    *  /points -> dynamic.php?page_id=310
    *
    **/
    "^/points[\/]?(.*)$" => "/dynamic.php?page_id=310&%s",

    /**
    *
    *  /families -> dynamic.php?page_id=320
    *
    **/
    "^/families[\/]?(.*)$" => "/dynamic.php?page_id=320&%s",
    /**
    *
    *  /family/edit -> dynamic.php?page_id=322
    *
    **/
    "^/family/edit[\/]?(.*)$" => "/dynamic.php?page_id=322&%s",
    
    
    /**
    *
    *  /family/members -> dynamic.php?page_id=323
    *
    **/
    "^/family/members[\/]?(.*)$" => "/dynamic.php?page_id=323&%s",


    /**
    *
    *  /family -> dynamic.php?page_id=321
    *
    **/
    "^/family[\/]?(.*)$" => "/dynamic.php?page_id=321&%s",
  );

define("PA_ROUTE_FORUMS", "/forums");
define("PA_ROUTE_GROUP", "/group");
define("PA_ROUTE_GROUPS", "/groups");
define("PA_ROUTE_GROUP_MODERATION", "/group/moderation");
define("PA_ROUTE_GROUP_AD_CENTER", "/group/ad_center");
// define("PA_ROUTE_GROUP_THEMES", "/group/customize/themes"); // refactoring for this page not finished!  all links that points to this page should be replaced in whole PA application
define("PA_ROUTE_EDIT_RELATIONS", "/relations/edit");
define("PA_ROUTE_INVITE", "/invitation");
define("PA_ROUTE_USER_PUBLIC", "/user");
define("PA_ROUTE_USER_PRIVATE", "/me");
define("PA_ROUTE_EDIT_PROFILE", "/myAccount/editProfile");  // refactoring for this page not finished!
define("PA_ROUTE_MYMESSAGE", "/myAccount/messages");
define("PA_ROUTE_ADDMESSAGE", "/myAccount/newMessage");
define("PA_ROUTE_PERMALINK", "/content/permalink");         // refactoring for this page not finished!
define("PA_ROUTE_CONTENT", "/content");                     // refactoring for this page not finished!

define("PA_ROUTE_CONFIG_POLL", "/configure/polls");         // refactoring for this page not finished! all links that points to this page should be replaced in whole PA application
define("PA_ROUTE_RANKING_POINTS", "/configure/ranking_points");
define("PA_ROUTE_CONFIG_ROLES", "/configure/roles");
define("PA_ROUTE_MANAGE_AD_CENTER", "/configure/ad_center");

define("PA_ROUTE_CUSTOMIZE_USER_GUI", "/myAccount/customize");
define("PA_ROUTE_CUSTOMIZE_GROUP_GUI", "/group/customize");
define("PA_ROUTE_CUSTOMIZE_NETWORK_GUI", "/configure/network");
define("PA_ROUTE_CONFIGURE_NETWORK", "/configure/network");     // refactoring for this page not finished!
define("PA_ROUTE_CONFIGURE_SYSTEM", "/configure/system");       // ConfigManager page
define("PA_ROUTE_CONFIGURE_DEFENDER", "/configure/defender");   // Configure PADefender page

define("PA_ROUTE_LEADER_BOARD", "/leaderboard");
define("PA_ROUTE_HOME_PAGE", "/home");
define("PA_ROUTE_PEOPLES_PAGE", "/people");
define("PA_ROUTE_SEARCH_HOME", "/contents/search");
define("PA_ROUTE_GROUP_INVITE", "/group/invitation");
define("PA_ROUTE_CREATE_DYN_PAGE", "/tools/CreateDynamicPage/");
define("PA_ROUTE_MEDIA_GALLEY_IMAGES", "/media/gallery/Images");
define("PA_ROUTE_MEDIA_GALLEY_AUDIOS", "/media/gallery/Audios");
define("PA_ROUTE_MEDIA_GALLEY_VIDEOS", "/media/gallery/Videos");
define("PA_ROUTE_TYPED_DIRECTORY", "/directory/");
define("PA_ROUTE_SYSTEM_MESSAGE", "/sys_message");
define("PA_ROUTE_USER_CONTACTS", "/myAccount/contacts");
define("PA_ROUTE_FORGET_PASSWORD", "/forgotPassword");
define("PA_ROUTE_POINTS_DIRECTORY", "/points");
define("PA_ROUTE_FAMILY_DIRECTORY", "/families");
define("PA_ROUTE_FAMILY", "/family");
define("PA_ROUTE_FAMILY_EDIT", "/family/edit");
define("PA_ROUTE_FAMILY_MEMBERS", "/family/members");

?>
