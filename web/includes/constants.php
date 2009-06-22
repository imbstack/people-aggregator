<?php

include_once "api/api_constants.php";
define_once ('PAGE_USER_PRIVATE',1);
define_once ('PAGE_USER_PUBLIC',2);
define_once ('PAGE_GROUPS_HOME',3);
define_once ('PAGE_GROUPS_CATEGORY',4);        // ???
define_once ('PAGE_HOMEPAGE',5);
define_once ('PAGE_PEOPLES',6);
define_once ('PAGE_ADDGROUP',7);
define_once ('PAGE_GROUP',8);
define_once ('PAGE_CHOOSECONTENT',9);
define_once ('PAGE_CREATECONTENT',10);
define_once ('PAGE_PERMALINK',12);
define_once ('PAGE_MANAGECONTENT',13);          // ???
define_once ('PAGE_MEDIA_MANAGEMENT',14);
define_once ('PAGE_MESSAGE',15);
define_once ('PAGE_ADDMESSAGE',16);
define_once ('PAGE_SEARCH',17);
// define_once ('PAGE_CREATESBCONTENT',18);        // ???
define_once ('PAGE_SHOWCONTENT',22);
define_once ('PAGE_MEDIA_GALLERY', 19);
define_once ('PAGE_FORUM_MESSAGES',20);
define_once ('PAGE_REGISTER', 21);
define_once ('PAGE_INVITATION', 23);
define_once ('PAGE_EDIT_PROFILE', 24);
// define_once ('PAGE_ALL_PEOPLE', 25);              // ???
define_once ('PAGE_GROUP_MODERATION', 26);
define_once ('PAGE_VIEW_ALL_MEMBERS', 27);
define_once ('PAGE_EDIT_RELATION', 28);
define_once ('PAGE_FORGOT_PASSWORD', 29);
define_once ('PAGE_LOGIN', 30);
define_once ('PAGE_GROUP_INVITE', 31);
define_once ('PAGE_MEDIA_FULL_VIEW', 32);
define_once ('PAGE_POSTCONTENT', 33);
define_once ('PAGE_CREATE_NETWORK', 34);
define_once ('PAGE_NETWORKS_HOME', 35);
define_once ('PAGE_LINKS_MANAGEMENT', 36);
define_once ('PAGE_NETWORKS_CATEGORY', 37);
define_once ('PAGE_NETWORK', 38);
define_once ('PAGE_GROUP_MEDIA_GALLERY', 39);
define_once ('PAGE_EDIT_MEDIA', 40);
define_once ('PAGE_BADGE_CREATE', 41);
define_once ('PAGE_COMMENT_MANAGEMENT', 42);


define_once ('PAGE_NETWORK_STATISTICS', 43);
define_once ('PAGE_EMAIL_NOTIFICATION',44);
define_once ('PAGE_NETWORK_USER_DEFAULTS',45);
define_once ('PAGE_RELATIONSHIP_SETTINGS',47);
define_once ('PAGE_NETWORK_MANAGE_USERS',48);
define_once ('PAGE_NETWORK_MANAGE_CONTENTS',49);
define_once ('PAGE_CONFIGURE_NETWORK',50);
define_once ('PAGE_NETWORK_BULLETINS',51);
define_once ('PAGE_NEW_USER_BY_ADMIN',52);
define_once ('PAGE_NETWORK_LINKS',53);
define_once ('PAGE_CHANGE_PASSWORD',54);
define_once ('PAGE_NETWORK_FEATURE',55);
define_once ('PAGE_MODULE_SELECTOR',56);
define_once ('PAGE_CONFIGURE_SPLASH_PAGE', 58);
define_once ('PAGE_MANAGE_EMBLEM', 59);
define_once ('PAGE_MANAGE_TAKETOUR', 60);
define_once ('PAGE_FORUM_HOME', 61);
define_once ('PAGE_CREATE_FORUM_TOPIC', 62);
define_once ('PAGE_REQUEST', 63);
define_once ('PAGE_NETWORK_MODERATE_USERS', 64);
define_once ('PAGE_MANAGE_GROUP_CONTENT', 65);
define_once ('PAGE_NETWORK_FORUM_MANAGEMENT', 66);
define_once ('PAGE_MANAGE_COMMENTS', 67);
define_once ('PAGE_GROUP_THEME', 68);
define_once ('PAGE_CALENDAR', 69);
define_once('PAGE_ROLE_MANAGE',70);
define_once ('PAGE_MANAGE_AD_CENTER', 71);
define_once ('PAGE_TASK_MANAGE', 72);
define_once ('PAGE_WRITE_TESTIMONIAL', 73);
define_once ('PAGE_MIS_REPORT', 74);
define_once ('PAGE_SITE_RANKING', 75);
// define_once ('PAGE_COOL_PEOPLE', 76);       //???
define_once ('PAGE_USER_COMMENT', 77);
define_once ('PAGE_TERMS', 78);
define_once ('PAGE_NETWORK_MODERATE_CONTENTS',79);
define_once ('PAGE_MANAGE_FOOTERLINKS', 80);
define_once ('PAGE_MANAGE_STATICPAGES', 81);
define_once ('PAGE_STATIC_PAGE_DISPLAY', 82);
define_once ('PAGE_CONFIGURE_EMAIL', 83);
define_once ('PAGE_MANAGE_QUESTIONS', 84);
define_once ('PAGE_ANSWERS', 85);
define_once ('PAGE_TAG_SEARCH', 86);
define_once ('PAGE_NETWORK_THEMES', 87);
define_once ('PAGE_NETWORK_MANAGE_GROUPS', 88);
define_once ('PAGE_MANAGE_CATEGORY', 89);
define_once ('PAGE_MANAGE_PROFANITY', 90);

define_once ('PAGE_GROUP_AD_CENTER', 91);

define_once ('PAGE_VIEW_ALL_MEDIA', 97);
define_once ('PAGE_MANAGE_TEXTPADS', 98);
define_once ('PAGE_POLL', 99);
define_once ('PAGE_MANAGE_DOMAIN', 100);

define_once ('PAGE_USER_CALENDAR', 101);
define_once ('PAGE_GROUP_CALENDAR', 102);
define_once ('PAGE_NETWORK_CALENDAR', 103);
define_once ('PAGE_USER_CUSTOMIZE_UI', 104);
define_once ('PAGE_GROUP_MEDIA_POST', 105);
define_once ('PAGE_MEDIA_GALLERY_UPLOAD', 106);
define_once ('PAGE_VIEW_MESSAGE',107);


define_once ('PAGE_MANAGE_RANKING_POINTS', 200);
define_once ('PAGE_FORUMS', 201);
define_once ('PAGE_LEADER_BOARD', 202);
define_once ('PAGE_SYSTEM_MESSAGE', 205);
define_once ('PAGE_USER_CONTACTS', 206);

define_once ('PAGE_TYPED_DIRECTORY', 300);
define_once ('PAGE_POINTS_DIRECTORY', 310);

define_once ('PAGE_CREATE_DYNAMIC_PAGE', 727);

// used for ajax sorting in BlockModules
define_once ('SORT_BY', 1);
define_once('BLOG_SETTING_STATUS_ALLDISPLAY',3);
define_once('BLOG_SETTING_STATUS_NODISPLAY',0);
define_once('PERSONAL_BLOG_SETTING_STATUS',1);
define_once('EXTERNAL_BLOG_SETTING_STATUS',2);
//$SU is defined in local_config.php
if (!empty($SU)) {
  define_once ('SUPER_USER_ID', $SU);
} else {
  define_once ('SUPER_USER_ID', 1);
}
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

//used in ad center
define_once("AD_WIDTH_LR", 198);//width for left module ad
define_once("AD_HEIGHT_LR", 180);//height for left module ad
define_once("AD_WIDTH_MIDDLE", 577);//width for middle module ad
define_once("AD_HEIGHT_MIDDLE", 80);//height for middle module ad

// use for display the dyanmic row in people page

define_once("FACEWALL_COLUMN_COUNT", 6);
define_once("FACEWALL_ROW_COUNT", 4);

define_once("DEFAULT_NETWORK_ICON",'images/pa_logo_static_pages.gif');


define_once ('CUSTOM_INVITATION_MESSAGE', "Write here personalized message for invitees. It will be appended to email");
?>
