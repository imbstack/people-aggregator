<?php
ini_set('max_execution_time', 1200);
ini_set('max_input_time', 1200);

if (!defined("PA_DISABLE_BUFFERING")) define("PA_DISABLE_BUFFERING", TRUE);
$here = dirname(__FILE__);
require_once "$here/../../config.inc";
require_once "web/includes/functions/functions.php";
require_once "api/Content/Content.php";
require_once "db/Dal/Dal.php";
require_once "db/Dal/DbUpdate.php";
require_once "web/extra/net_extra.php";

class db_update_page
{
  public static function check_quiet() {
    return defined("PEEPAGG_UPDATING");
  }

  function __construct() {
    // set to false to only update a single network (see: $this->update_single_network($network_name))
    $this->full_update = TRUE;

    // set to true to run update without any output
    $this->quiet = db_update_page::check_quiet();

    $this->running_on_cli = !isset($_SERVER['REQUEST_METHOD']);
  }

  function do_updates()
  {

    // ALL DATABASE UPDATES GO IN HERE!
    // FOR EACH SQL STATEMENT YOU WANT TO EXECUTE, GIVE IT A 'KEY', THEN CALL:
    // $this->qup("key", "sql statement");
    // eg. $this->qup("new foobar table", "create table foobar (id int not null, primary key(id))");
    // YOU SHOULD NORMALLY PUT YOUR UPDATES AT THE *END* OF THIS FUNCTION.

    /** NOTE: KEY must be unique for each update query */

    /** EXAMPLE ADD NEW TABLE */

    /*$this->qup("new mc_feeds table",
      "CREATE TABLE mc_feeds (
      user_id int not null,
      id int not null auto_increment,
        primary key(user_id,id),
      feed_url text not null,
      feed_name varchar(255)
      )"); */

   /** EXAMPLE ALTER TABLE */

   // $this->qup("add feed_description to mc_feeds", "ALTER TABLE mc_feeds ADD COLUMN feed_description TEXT");



   /** EXAMPLE INSERT INTO TABLE */
   // $this->qup("insert default data 1 for relation classifications", "INSERT INTO `relation_classifications` (`relation_type`, `relation_type_id`) VALUES ('acquaintance', '1');");


   /** EXAMPLE UPDATE TABLE */

  // $this->qup("changed id field in review-type movie", "UPDATE review_type SET review_id = 1 WHERE review_name = 'Movie'");





  if (!$this->column_exists('users','zipcode')) {
    $this->qup("add zipcode column to users", "ALTER TABLE `users` ADD `zipcode` INT( 11 ) AFTER `last_login`");
  }
  $this->qup("changed forgot_password_id type", "ALTER TABLE `forgot_password` CHANGE `forgot_password_id` `forgot_password_id` VARCHAR( 255 ) NOT NULL DEFAULT '0'");

  if (!$this->column_exists('contents','display_on')) {
    $this->qup("display_on added to contents table", "ALTER TABLE `contents` ADD `display_on` TINYINT( 1 ) DEFAULT '0' NOT NULL");
  }





  if (!$this->table_exists('tags_networks')) {
    $this->qup("tags_networks table",
      "CREATE TABLE `tags_networks` (
    `tag_id` int(11) NOT NULL default '0',
    `network_id` int(11) NOT NULL default '0'
    )");
  }

if (!$this->table_exists('networks')) {
    $this->qup("networks table",
      "CREATE TABLE `networks` (
      `network_id` int(11) NOT NULL auto_increment,
      `name` varchar(50) default NULL,
      `address` varchar(50) default NULL,
      `tagline` varchar(255) default NULL,
      `type` int(1) default '0',
      `maximum_members` int(11) default '0',
      `category_id` int(11) default '0',
      `description` text,
      `header_image` varchar(255) default NULL,
      `inner_logo_image` varchar(255) default NULL,
      `network_alt_text` varchar(255) default NULL,
      `is_active` int(1) default '0',
      `created` int(11) default '0',
      `changed` int(11) default '0',
      `stop_after_limit` int(1) default '0',
      `extra` text,
      PRIMARY KEY  (`network_id`)
    )");

  }


if (!$this->table_exists('networks_users')) {
  $this->qup("networks_users table",
      "CREATE TABLE `networks_users` (
      `network_id` int(11) NOT NULL default '0',
      `user_id` int(11) NOT NULL default '0',
      `user_type` varchar(50) NOT NULL default ''
    )");
  }

  if (!$this->table_exists('linkcategories')) {
  $this->qup("linkcategories table",
      "CREATE TABLE `linkcategories` (
    `category_id` int(11) NOT NULL auto_increment,
    `category_name` varchar(255) default NULL,
    `user_id` int(11) default NULL,
    `created` int(11) default NULL,
    `changed` int(11) default NULL,
    `is_active` tinyint(1) default NULL,
    PRIMARY KEY  (`category_id`)
  )");
 }

 if (!$this->table_exists('links')) {
 $this->qup("links table",
      "CREATE TABLE `links` (
    `link_id` int(11) NOT NULL auto_increment,
    `title` varchar(255) default NULL,
    `url` varchar(255) default NULL,
    `category_id` int(11) default NULL,
    `created` int(11) default NULL,
    `changed` int(11) default NULL,
    `is_active` tinyint(1) default NULL,
    PRIMARY KEY  (`link_id`)
  )");
}


//  $this->qup_all_networks("announcements table",
//       "CREATE TABLE IF NOT EXISTS {announcements} (
//     `content_id` int(11) NOT NULL default 0,
//     `announcement_time` int(11) NOT NULL default 0,
//     `position` tinyint(1) NOT NULL default 0,
//     `status` tinyint(1) NOT NULL default 0,
//     `is_active` tinyint(1) NOT NULL default 0
//
//   )");

if (!$this->table_exists('external_feed')) {
  $this->qup("creating table external_feed", "CREATE TABLE `external_feed` (  `feed_id` int(11) NOT NULL auto_increment, `import_url` varchar(255) NOT NULL default '',  `max_posts` smallint(4) default '5',   `is_active` int(2) default NULL,  `feed_type` varchar(100) NOT NULL default 'user',  `last_build_date` int(11) default NULL,   PRIMARY KEY  (`feed_id`) )");
}

if (!$this->table_exists('feed_data')) {
  $this->qup("creating table feed_data", "CREATE TABLE `feed_data` (   `feed_data_id` int(11) NOT NULL auto_increment,   `feed_id` int(11) NOT NULL default '0',   `title` varchar(255) default NULL,   `description` text,   `original_url` varchar(255) default NULL,   PRIMARY KEY  (`feed_data_id`) )");
}

if (!$this->table_exists('user_feed')) {
  $this->qup("creating table user_feed", "CREATE TABLE `user_feed` (  `user_id` int(11) NOT NULL default '0',   `feed_id` int(11) NOT NULL default '0' )");
}

if (!$this->table_exists('users_online')) {
  $this->qup("create users_online table", "CREATE TABLE `users_online` (`user_id` INT(11) NOT NULL, `timestamp` INT(11) NOT NULL)");
}
  //TO DO::: ALTER TABLE `forgot_password` CHANGE `forgot_password_id` `forgot_password_id` VARCHAR( 255 ) NOT NULL DEFAULT '0'

  if (!$this->column_exists('networks','inner_logo_image')) {
    $this->qup("added one column named inner_logo_image in networks table", "ALTER TABLE `networks` ADD `inner_logo_image` VARCHAR( 255 ) AFTER `header_image`");
    }
  if (!$this->column_exists('networks','network_alt_text')) {
    $this->qup("added one column named network_alt_text in networks table", "ALTER TABLE `networks` ADD `network_alt_text` VARCHAR( 255 ) AFTER `description`");
    }



    if (!$this->column_exists('images','image_perm')) {
    $this->qup("added one more column in images table", "ALTER TABLE `images` ADD `image_perm` INT( 2 ) DEFAULT '0' NOT NULL AFTER `image_file`");
    }

    if (!$this->column_exists('audios','audio_perm')) {
      $this->qup("added one more column in audios table", "ALTER TABLE `audios` ADD `audio_perm` INT( 2 ) DEFAULT '0' NOT NULL AFTER `audio_file`");
    }

    if (!$this->column_exists('videos','video_perm')) {
      $this->qup("added one more column in videos table", "ALTER TABLE `videos` ADD `video_perm` INT( 2 ) DEFAULT '0' NOT NULL AFTER `video_file`");
    }

    if ($this->column_exists('users','zipcode')) {
      $this->qup("delete zipcode field from users table", "ALTER TABLE `users` DROP `zipcode`");
    }

    if (!$this->table_exists('configurable_text')) {
      $this->qup("configurable text table",
            "CREATE TABLE `configurable_text` (
              `id` int(11) NOT NULL auto_increment,
              `caption` varchar(255) default NULL,
              `caption_value` varchar(255) default NULL,
              PRIMARY KEY  (`id`)
            )");
      $this->qup("record1",
            "INSERT INTO `configurable_text` VALUES (1, 'News', 'News')");
      $this->qup("record2",
            "INSERT INTO `configurable_text` VALUES (2, 'NewsHeading3', 'Google Buys Quebec')");
      $this->qup("record3",
            "INSERT INTO `configurable_text` VALUES (3, 'NewsHeading2', 'AOL Releases Virginia')");
      $this->qup("record4",
            "INSERT INTO `configurable_text` VALUES (4, 'NewsHeading1', 'EMI Launches')");
      $this->qup("record5",
            "INSERT INTO `configurable_text` VALUES (5, 'Standards', ' Standards')");
      $this->qup("record6",
            "INSERT INTO `configurable_text` VALUES (6, 'Standard1', 'iTags v3.0 Ships')");
      $this->qup("record7",
            "INSERT INTO `configurable_text` VALUES (7, 'Standard2', 'AOL supports SB.org')");
      $this->qup("record8",
            "INSERT INTO `configurable_text` VALUES (8, 'Standard3', 'Apple Adheres to mRSS')");
      $this->qup("record9",
            "INSERT INTO `configurable_text` VALUES (9, 'Scene1', 'Marc Canter speaking at current conferences and The Scene...')");
    }



    // 2006-06: auto-update
    $this->qup("create svn_objects table", "CREATE TABLE svn_objects (path VARCHAR(255) NOT NULL, kind VARCHAR(32) NOT NULL, hash VARCHAR(32), revision INT NOT NULL, is_active BOOL NOT NULL DEFAULT 1, PRIMARY KEY(is_active, path, revision), UNIQUE(revision, path))");
    $this->qup("create svn_meta table", "CREATE TABLE svn_meta (revision INT NOT NULL)");
    $this->qup("add repos_root to svn_meta", "ALTER TABLE svn_meta ADD COLUMN repos_root TEXT NOT NULL");
    $this->qup("add repos_path to svn_meta", "ALTER TABLE svn_meta ADD COLUMN repos_path TEXT NOT NULL");
    $this->qup("add held_revision to svn_objecs", "ALTER TABLE svn_objects ADD COLUMN held_revision INT");

    // 2006-09-05: blog badge
    $this->qup_all_networks("create blog_badges table", "CREATE TABLE {blog_badges} (user_id INT NOT NULL, badge_tag VARCHAR(32) NOT NULL, PRIMARY KEY(user_id, badge_tag), badge_config TEXT NOT NULL)");
    // 2007-01-15: friendly name and the ability to delete badges
    $this->qup_all_networks("add blog_badges.title, blog_badges.is_active and blog_badges.badge_id", "ALTER TABLE {blog_badges}
        DROP PRIMARY KEY,
        ADD COLUMN badge_id INT NOT NULL AUTO_INCREMENT,
        ADD PRIMARY KEY(badge_id),
        ADD COLUMN title VARCHAR(255) NOT NULL DEFAULT '',
        ADD COLUMN is_active INT NOT NULL DEFAULT 1,
        ADD KEY user_badges (is_active, user_id, badge_tag)");


    // 2006-11-13: run net_extra so $this->qup_all_networks will properly update the home network.
    $this->qup("run net_extra to put the home network into the database - try 2", "run_net_extra_once_only");

    // 2006-11-10: network_links
    $this->qup_all_networks("create network_links table - try 2", "CREATE TABLE IF NOT EXISTS {network_links} (
  link_id int(11) NOT NULL auto_increment,
  title varchar(255) default NULL,
  url varchar(255) default NULL,
  category_id int(11) default NULL,
  created int(11) default NULL,
  changed int(11) default NULL,
  is_active tinyint(1) default NULL,
  PRIMARY KEY  (link_id)
  )");
  // 2006-11-10: network_linkcategories
   $this->qup_all_networks("create network_linkcategories table - try 2", "CREATE TABLE IF NOT EXISTS {network_linkcategories} (
  category_id int(11) NOT NULL auto_increment,
  category_name varchar(255) default NULL,
  user_id int(11) default NULL,
  created int(11) default NULL,
  changed int(11) default NULL,
  is_active tinyint(1) default NULL,
  PRIMARY KEY  (category_id)
) ");



    // 2006-09-07: comment spam tracking
    $this->qup_all_networks("comments.ip_addr column", "ALTER TABLE {comments} ADD COLUMN ip_addr VARCHAR(32) NOT NULL");
    $this->qup_all_networks("comments.referrer column", "ALTER TABLE {comments} ADD COLUMN referrer TEXT NOT NULL");
    $this->qup_all_networks("comments.user_agent column", "ALTER TABLE {comments} ADD COLUMN user_agent VARCHAR(255) NOT NULL");
    $this->qup("create spam_terms table", "CREATE TABLE spam_terms (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), term VARCHAR(255) NOT NULL)");
    // spam_state; 0: not spam; 1: manually deleted; 2: bulk-deleted by spam terms; 3: flagged by akismet; 4: deleted by domain blacklist
    $this->qup_all_networks("comments.spam_state column 2", "ALTER TABLE {comments} ADD COLUMN spam_state INT NOT NULL DEFAULT 0");
    // 1 if akismet has said this is spam, 0 if not spam, null if we haven't asked akismet
    $this->qup_all_networks("comments.akismet_spam column", "ALTER TABLE {comments} ADD COLUMN akismet_spam BOOL DEFAULT NULL");


    // index of domain names present in links in comments (for spam detection) (domain = top part of domain, e.g. "bmw07.com" for "phentermine-online.bmw07.com"; count = total number of times this domain name has been seen in comments, ever; blacklisted = {0: unknown, 1: blacklisted manually, 2: on a dns blacklist}; whitelisted = {0: unknown, 1: whitelisted manually})
    $this->qup("create spam_domains table", "CREATE TABLE spam_domains (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), domain VARCHAR(255) NOT NULL, UNIQUE(domain), count INT NOT NULL, blacklisted INT NOT NULL DEFAULT 0, whitelisted INT NOT NULL DEFAULT 0)");
    $this->qup("index blacklist flags on spam_domains", "ALTER TABLE spam_domains ADD KEY blacklisted (blacklisted)");
    $this->qup("spam_domains.active_count", "ALTER TABLE spam_domains ADD COLUMN active_count INT NOT NULL"); // active_count counts how many times the domain appears in *active* comments.
    // linking domain names to comments - so we can go back later on and delete spammy comments *after* a domain gets blacklisted.
    $this->qup("create domains_in_comments table", "CREATE TABLE domains_in_comments (domain_id INT NOT NULL, comment_id INT NOT NULL, PRIMARY KEY(comment_id, domain_id), KEY(domain_id), occurrences INT NOT NULL)");

    // 2005-10-28 Martin
    $this->qup("alter relation table for external relations",
      "ALTER TABLE relations ADD `network` VARCHAR( 255 ) NULL,
        ADD `network_uid` VARCHAR( 255 ) NULL, ADD `display_name` VARCHAR( 255 ) NULL,
        ADD `thumbnail_url` TINYTEXT NULL, ADD `profile_url` TINYTEXT NULL");

    // 2006-11-20 Phil
    $this->qup("change length of mc_db_status.network to 50",
      "ALTER TABLE mc_db_status CHANGE COLUMN network network VARCHAR(50) NOT NULL DEFAULT '',
         DROP PRIMARY KEY,
         ADD PRIMARY KEY(stmt_key, network)");

    // 2006-11-27 Martin
    $this->qup("move profile data: blog_url ",
    "UPDATE `user_profile_data`
  SET `field_name` = 'blog_url',
  `field_type` = 'blogs_rss'
  WHERE `field_name` = 'blog_url'"
    );

    // 2006-11-27 Martin
    $this->qup("move profile data: blog_title ",
    "UPDATE `user_profile_data`
  SET `field_name` = 'blog_title',
  `field_type` = 'blogs_rss'
  WHERE `field_name` = 'blog_title'");

    // 2006-11-27 Martin
    $this->qup("move profile data: flickr id",
    "UPDATE `user_profile_data`
  SET `field_name` = 'flickr_email',
  `field_type` = 'external'
  WHERE `field_name` = 'flickr'");

    // 2006-11-27 Martin
    $this->qup("move profile data: delicious id",
    "UPDATE `user_profile_data`
  SET `field_name` = 'delicious',
  `field_type` = 'external'
  WHERE `field_name` = 'delicious'");

    //2006-11-28 Gurpreet
    if ($this->column_exists("external_feed", "user_id")) {
  $this->qup("deleting user_id column from external_feed",
       "ALTER TABLE `external_feed` DROP `user_id`");
    }

    // 2006-11-28 Martin
    $this->qup("add sequence field to profile: multiple values of one name",
    "ALTER TABLE `user_profile_data` ADD `seq` INT( 3 ) UNSIGNED ZEROFILL NULL");

    // 2006-11-30 Martin
    $this->qup("ensure sequence is set for blog_url fields",
    "UPDATE `user_profile_data`
  SET `seq` = 1
  WHERE `field_name` = 'blog_url'
  AND seq = NULL");

    // 2006-11-30 Martin
    $this->qup("ensure sequence is set for blog_title fields",
    "UPDATE `user_profile_data`
  SET `seq` = 1
  WHERE `field_name` = 'blog_title'
  AND seq = NULL");

    // 2006-11-30 Martin
    $this->qup("ensure sequence is set for blog_feed fields",
    "UPDATE `user_profile_data`
  SET `seq` = 1
  WHERE `field_name` = 'blog_feed'
  AND seq = NULL");

  // 2006-12-08 Gurpreet
  if (!$this->column_exists("relations", "in_family")) {
    $this->qup("adding in_family field to relations table",
    "ALTER TABLE `relations` ADD `in_family` INT( 2 ) ");
  }
  // 2007-01-03 Ekta
  if (!$this->column_exists('relations','status')) {
    $this->qup("status added to relations table", "ALTER TABLE `relations` ADD
    `status` ENUM('denied', 'pending', 'approved') DEFAULT 'approved' NOT NULL");
  }
  // 2007-01-04 Kuldeep - creating table moduledata
  // This table is used to hold the values for various modules e.g.
  // Personalised video, emblem modules
  if (!$this->table_exists("moduledata")) {
    $this->qup("creating table moduledata",
                "CREATE TABLE IF NOT EXISTS `moduledata` (
                `id` int(11) NOT NULL auto_increment,
                `modulename` varchar(255) default NULL,
                `caption` varchar(255) default NULL,
                `data` text,
                `created` int(11) default NULL,
                `changed` int(11) default NULL,
                PRIMARY KEY  (`id`),
                UNIQUE KEY `modulename` (`modulename`)
                ) ");

    $this->qup("inserting for LogoModule",
                  'INSERT INTO `moduledata` (`id`, `modulename`, `caption`, `data`, `created`, `changed`) VALUES (1, \'LogoModule\', NULL, NULL, NULL, 1167849000)');
    $this->qup("inserting for TakeATourModule",
                  'INSERT INTO `moduledata` (`id`, `modulename`, `caption`, `data`, `created`, `changed`) VALUES (2, \'TakeTour\', NULL, NULL, NULL, 1167849000)');
  }
  // 2007-04-17 Ekta - creating table advertisements
  // This table will be used for manage ad-center at PA
  $this->qup_all_networks("creating table advertisements",
                "CREATE TABLE IF NOT EXISTS {advertisements} (
                `ad_id` int(11) NOT NULL auto_increment,
                `user_id` int(11) NOT NULL,
                `ad_image` varchar(255) default NULL,
                `url` varchar(255) default NULL,
                `ad_script` text default NULL,
                `title` varchar(255) default NULL,
                `description` text default NULL,
                `page_id` int(11) default NULL,
                `orientation` int(11) default NULL,
                `is_active` tinyint(1) default NULL,
                `created` int(11) default NULL,
                `changed` int(11) default NULL,
                `display_count` int(11) default 0,
                `hit_count` int(11) default 0,
                PRIMARY KEY  (`ad_id`)
                ) ");

  // Added by Saurabh for the Testimonial
  if (!$this->table_exists('testimonials')) {
    $this->qup("Creating table Testimonials ","CREATE TABLE `testimonials` (
    `testimonial_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `sender_id` INT( 11 ) NOT NULL ,
    `recipient_id` INT( 11 ) NOT NULL ,
    `body` TEXT NOT NULL ,
    `status` VARCHAR( 50 ) NULL ,
    `is_active` TINYINT( 1 ) NULL ,
    `created` INT( 11 ) NOT NULL ,
    `changed` INT( 11 ) NOT NULL
     )");
  }

  // added by saurabh for the Report Abuse
    $this->qup_all_networks("Creating table Report abuse ","CREATE TABLE IF NOT EXISTS {report_abuse} (
      `report_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
      `parent_type` VARCHAR(100) NULL ,
      `parent_id` INT(11) NOT NULL ,
      `reporter_id` INT(11) NULL ,
      `body` TEXT NULL ,
      `created` INT(11) NOT NULL
      )");

   // added by saurabh for the comment on everything

    $this->qup_all_networks("adding parent_type and parent_id into comments table", "ALTER TABLE {comments} ADD `parent_id` INT( 11 ) NULL , ADD `parent_type` VARCHAR( 100 ) NULL ;");


  // Added by saurabh on 11th may
   $this->qup_all_networks("adding created into groups users table", "ALTER TABLE {groups_users} ADD `created` INT( 11 ) NULL; ");


  if (!$this->column_exists('feed_data','publish_date')) {
    $this->qup("add publish_date column to feed_data", "ALTER TABLE `feed_data` ADD `publish_date` INT( 11 ) ");
  }

  $this->qup_all_networks("header_image column in groups", "ALTER TABLE {groups} ADD COLUMN header_image TEXT NULL");
  $this->qup_all_networks("header_image_action column in groups", "ALTER TABLE {groups} ADD COLUMN header_image_action INT(2) NULL");

  $this->qup_all_networks("display_header_image column in groups", "ALTER TABLE {groups} ADD COLUMN display_header_image  varchar(40)  DEFAULT 1");
  // 2007-04-11 Saurabh
  $this->qup_all_networks("extra column in groups", "ALTER TABLE {groups} ADD COLUMN extra TEXT NULL");
    // 2007-01-26 Phil - adding lots of indices
    // First, indices that don't require any extra columns to be added:
    $this->qup("2007-01-25 indices for homepage.php queries (home only)", array(
      'ALTER TABLE relations ADD KEY user_rel (user_id, relation_id)',
      'ALTER TABLE users_online ADD KEY login_order (timestamp),
         ADD UNIQUE user_id (user_id),
         ADD KEY timestamp (timestamp)',
      'ALTER TABLE users ADD KEY count_active (is_active)',
    ));
    $this->qup_all_networks("2007-01-26 indices for homepage.php queries (all nets)", array(
      'ALTER TABLE {users_roles} ADD KEY user_id (user_id)',
      'ALTER TABLE {page_settings} ADD KEY user_page (user_id, page_id)',
      'ALTER TABLE {contents} ADD KEY homepage_content (is_active, display_on, collection_id, created),
         ADD KEY homepage_typed_content (is_active, display_on, collection_id, type, created),
         ADD KEY user_content (is_active, collection_id, author_id, created),
         ADD KEY user_typed_content (is_active, collection_id, author_id, type, created)',
    ));

    // 2007-01-29 Phil - more scaling
    // Copying created column from users to networks_users to remove a join
    $this->qup("2007-01-29 add created to networks_users; copy from users table", array(
      "ALTER TABLE networks_users ADD COLUMN created INT NOT NULL,
         ADD KEY recent_users (network_id, created)",
      "UPDATE networks_users NU LEFT JOIN users U ON NU.user_id=U.user_id
         SET NU.created=U.created",
      ));
    $this->qup("2007-01-30 add (network_id,user_id) key to networks_users",
      "ALTER TABLE networks_users ADD KEY network_user_id (network_id, user_id)");
    $this->qup("2007-01-30 add (network_id,user_type) key to networks_users",
      "ALTER TABLE networks_users ADD KEY network_user_type (network_id, user_type)");

    $this->qup("2007-01-30 copy member count and owner id into networks table from networks_users", array(
      "ALTER TABLE networks ADD COLUMN member_count INT NOT NULL,
         ADD COLUMN owner_id INT NOT NULL",
      "UPDATE networks N SET N.member_count=(SELECT COUNT(NU.user_id) FROM networks_users NU where NU.network_id=N.network_id)",
      "UPDATE networks N SET N.owner_id=(SELECT NU.user_id FROM networks_users NU where NU.network_id=N.network_id AND NU.user_type='owner') WHERE N.member_count > 0",
      "UPDATE networks set owner_id = ".SUPER_USER_ID." WHERE network_id = ".MOTHER_NETWORK_TYPE
      )); //update network query added on 26-10-2007 to set the owner_id as SUPER_USER_ID for mother network.
    $this->qup("2007-01-30 add indices on count, name and created to networks table",
      "ALTER TABLE networks ADD KEY network_member_counts (is_active, type, member_count),
         ADD KEY network_name_alpha (is_active, type, name),
         ADD KEY network_created (is_active, type, created)");
    $this->qup("2007-01-30 add address index on networks",
      "ALTER TABLE networks ADD KEY network_address (address, is_active)");

    $this->qup_all_networks("2007-01-30 add (user_id, group_id) index on groups",
      "ALTER TABLE {groups_users} ADD KEY users_groups (user_id, group_id)");

    // 2007-02-13 Phil - line break conversion option for content, now that we have an html editor for blog posts
    $this->qup_all_networks("2007-02-13 add contents.is_html", array(
      "ALTER TABLE {contents} ADD COLUMN is_html BOOLEAN DEFAULT 1",
      "UPDATE {contents} SET is_html=0 WHERE type < 7",
      ));

    // 2007-02-13 Phil - updated persona table defs
    $this->qup("remove old persona tables and add new ones", array(
      "DROP TABLE IF EXISTS {personas}, {persona_properties}, {persona_services}, {persona_service_paths}",

      "CREATE TABLE {personas} (
  `persona_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `persona_service_id` int(10) unsigned NOT NULL default '0',
  `sequence` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `configuration` text NOT NULL,
  PRIMARY KEY  (`persona_id`)
) DEFAULT CHARSET=UTF8",

      "CREATE TABLE {persona_properties} (
  `persona_property_id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '0',
  `persona_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `content_type` varchar(255) NOT NULL default '',
  `content_hash` varchar(255) NOT NULL default '',
  `serial_number` int(10) unsigned NOT NULL default '0',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `category` varchar(255) NOT NULL default '',
  `viewer` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`persona_property_id`),
  KEY `key_index` USING BTREE (`name`)
) DEFAULT CHARSET=UTF8",

      "CREATE TABLE {persona_services} (
  `persona_service_id` int(10) unsigned NOT NULL auto_increment,
  `sequence` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `symbol` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `category` varchar(255) NOT NULL default '',
  `logo` varchar(255) NOT NULL default '',
  `configuration` text NOT NULL,
  `enabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`persona_service_id`)
) DEFAULT CHARSET=UTF8",

      "INSERT INTO {persona_services} VALUES
(1,1,'MySpace','MySpace','This is the MySpace service.','default','','{\"category\": \"default\", \"name\": \"MySpace\", \"sequence\": 1, \"enabled\": 1, \"persona_service_id\": 1, \"logo\": \"\"}',1),
(2,2,'Facebook','Facebook','This is the Facebook service.','default','','{\"category\": \"default\", \"name\": \"Facebook\", \"sequence\": 2, \"enabled\": 1, \"persona_service_id\": 2, \"logo\": \"\"}',1),
(3,3,'Google','Google','This is the Google service.','default','','{\"category\": \"default\", \"name\": \"Google\", \"sequence\": 3, \"enabled\": 1, \"persona_service_id\": 3, \"logo\": \"\"}',1),
(4,4,'YouTube','YouTube','This is the YouTube service.','default','','{\"category\": \"default\", \"name\": \"YouTube\", \"sequence\": 4, \"enabled\": 1, \"persona_service_id\": 4, \"logo\": \"\"}',1),
(5,5,'AIM','AIM','This is the AIM service.','default','','{\"category\": \"default\", \"name\": \"AIM\", \"sequence\": 5, \"enabled\": 1, \"persona_service_id\": 5, \"logo\": \"\"}',1),
(6,6,'Yahoo','Yahoo','This is the Yahoo service.','default','','{\"category\": \"default\", \"name\": \"Yahoo\", \"sequence\": 6, \"enabled\": 1, \"persona_service_id\": 6, \"logo\": \"\"}',0),
(7,7,'Flickr','Flickr','This is the Flickr service.','default','','{\"category\": \"default\", \"name\": \"Flickr\", \"sequence\": 7, \"enabled\": 1, \"persona_service_id\": 7, \"logo\": \"\"}',1),
(8,8,'Blip TV','BlipTV','This is the Blip TV service.','default','','{\"category\": \"default\", \"name\": \"Blip TV\", \"sequence\": 8, \"enabled\": 1, \"persona_service_id\": 8, \"logo\": \"\"}',0),
(9,9,'LiveJournal','LiveJournal','This is the LiveJournal service.','default','','{\"category\": \"default\", \"name\": \"LiveJournal\", \"sequence\": 9, \"enabled\": 1, \"persona_service_id\": 9, \"logo\": \"\"}',0),
(10,10,'VOX','VOX','This is the VOX service.','default','','{\"category\": \"default\", \"name\": \"VOX\", \"sequence\": 10, \"enabled\": 1, \"persona_service_id\": 10, \"logo\": \"\"}',0),
(11,11,'Multiply','Multiply','This is the Multiply service.','default','','{\"category\": \"default\", \"name\": \"Multiply\", \"sequence\": 11, \"enabled\": 1, \"persona_service_id\": 11, \"logo\": \"\"}',0),
(12,12,'Dabble','Dabble','This is the Dabble service.','default','','{\"category\": \"default\", \"name\": \"Dabble\", \"sequence\": 12, \"enabled\": 1, \"persona_service_id\": 12, \"logo\": \"\"}',0),
(13,13,'Del.icio.us','Delicious','This is the Del.icio.us service.','default','','{\"category\": \"default\", \"name\": \"Del.icio.us\", \"sequence\": 13, \"enabled\": 1, \"persona_service_id\": 13, \"logo\": \"\"}',0),
(14,14,'Amazon','Amazon','This is the Amazon service.','default','','{\"category\": \"default\", \"name\": \"Amazon\", \"sequence\": 14, \"enabled\": 1, \"persona_service_id\": 14, \"logo\": \"\"}',0),
(15,15,'eBay','eBay','This is the eBay service.','default','','{\"category\": \"default\", \"name\": \"eBay\", \"sequence\": 15, \"enabled\": 1, \"persona_service_id\": 15, \"logo\": \"\"}',0),
(16,16,'RSS','RSS','This is the RSS service.','default','','{\"category\": \"default\", \"name\": \"RSS\", \"sequence\": 16, \"enabled\": 1, \"persona_service_id\": 16, \"logo\": \"\"}',0),
(17,17,'OPML','OPML','This is the OPML service.','default','','{\"category\": \"default\", \"name\": \"OPML\", \"sequence\": 17, \"enabled\": 1, \"persona_service_id\": 17, \"logo\": \"\"}',0),
(18,18,'Blogger','Blogger','This is the Blogger service.','default','','{\"category\": \"default\", \"name\": \"Blogger\", \"sequence\": 18, \"enabled\": 1, \"persona_service_id\": 18, \"logo\": \"\"}',0),
(19,19,'Meta-Weblog','MetaWeblog','This is the Meta-Weblog service.','default','','{\"category\": \"default\", \"name\": \"Meta-Weblog\", \"sequence\": 19, \"enabled\": 1, \"persona_service_id\": 19, \"logo\": \"\"}',0),
(20,20,'Atom','Atom','This is the Atom service.','default','','{\"category\": \"default\", \"name\": \"Atom\", \"sequence\": 20, \"enabled\": 1, \"persona_service_id\": 20, \"logo\": \"\"}',0),
(21,21,'XML','XML','This is the XML service.','default','','{\"category\": \"default\", \"name\": \"XML\", \"sequence\": 21, \"enabled\": 1, \"persona_service_id\": 21, \"logo\": \"\"}',0),
(22,22,'JSON','JSON','This is the JSON service.','default','','{\"category\": \"default\", \"name\": \"JSON\", \"sequence\": 22, \"enabled\": 1, \"persona_service_id\": 22, \"logo\": \"\"}',0),
(23,23,'Text','Text','This is the Text service.','default','','{\"category\": \"default\", \"name\": \"Text\", \"sequence\": 23, \"enabled\": 1, \"persona_service_id\": 23, \"logo\": \"\"}',0)",

      "CREATE TABLE {persona_service_paths} (
  `persona_service_path_id` int(10) unsigned NOT NULL auto_increment,
  `persona_service_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `category` varchar(255) NOT NULL default '',
  `configuration` text NOT NULL,
  `sequence` int(10) unsigned NOT NULL default '0',
  `enabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`persona_service_path_id`)
) DEFAULT CHARSET=UTF8",

      "INSERT INTO {persona_service_paths} VALUES

(1,1,'MyUploadedVideos','My Uploaded Videos','video','{\"category\": \"video\", \"name\": \"MyUploadedVideos\", \"title\": \"My Uploaded Videos\", \"url\": \"http:\\/\\/www.MySpace.com\\/index.cfm?fuseaction=vids.myvideos\", \"gui\": \"button\", \"sequence\": 1, \"viewer\": \"VideoList\", \"persona_service_id\": 1, \"persona_service_path_id\": 1, \"content_type\": \"json\", \"path\": \"Video_MyUploadedVideos\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',1,1),

(2,1,'MyFavoriteVideos','My Favorite Videos','video','{\"category\": \"video\", \"name\": \"MyFavoriteVideos\", \"title\": \"My Favorite Videos\", \"url\": \"http:\\/\\/www.MySpace.com\\/index.cfm?fuseaction=vids.myfavorites\", \"gui\": \"button\", \"sequence\": 2, \"viewer\": \"VideoList\", \"persona_service_id\": 1, \"persona_service_path_id\": 2, \"content_type\": \"json\", \"path\": \"Video_MyFavoriteVideos\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',2,1),

(3,1,'MyFriends','My Friends','user','{\"category\": \"user\", \"name\": \"MyFriends\", \"title\": \"My Friends\", \"url\": null, \"gui\": \"button\", \"sequence\": 3, \"viewer\": \"FriendList\", \"persona_service_id\": 1, \"persona_service_path_id\": 3, \"content_type\": \"json\", \"path\": \"User_MyFriends\", \"login\": true, \"type\": \"user\", \"method\": \"FetchFriendList\"}',3,1),

(4,1,'SearchVideos','Search MySpace Videos','video','{\"category\": \"video\", \"name\": \"SearchVideos\", \"sequence\": 4, \"url\": \"http:\\/\\/vidsearch.myspace.com\\/index.cfm?fuseaction=vids.fullsearch&fullSearch=Search+Video&t=\", \"gui\": \"search\", \"title\": \"Search MySpace Videos\", \"viewer\": \"VideoList\", \"persona_service_id\": 1, \"persona_service_path_id\": 4, \"content_type\": \"json\", \"path\": \"Video_Search\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',4,1),

(5,1,'InboxMessages','Inbox','message','{\"category\": \"message\", \"name\": \"InboxMessages\", \"title\": \"Inbox\", \"url\": null, \"gui\": \"button\", \"sequence\": 5, \"viewer\": \"MessageList\", \"persona_service_id\": 1, \"persona_service_path_id\": 5, \"content_type\": \"json\", \"path\": \"User_Inbox\", \"login\": true, \"type\": \"user\", \"method\": \"FetchInbox\"}',5,1),

(6,1,'FriendMessages','Friend Requests','message','{\"category\": \"message\", \"name\": \"FriendMessages\", \"title\": \"Friend Requests\", \"url\": null, \"gui\": \"button\", \"sequence\": 6, \"viewer\": \"MessageList\", \"persona_service_id\": 1, \"persona_service_path_id\": 6, \"content_type\": \"json\", \"path\": \"User_FriendRequests\", \"login\": true, \"type\": \"user\", \"method\": \"FetchFriendRequests\"}',6,1),

(7,1,'BlogMessages','Blog Comments','message','{\"category\": \"message\", \"name\": \"BlogMessages\", \"title\": \"Blog Comments\", \"url\": null, \"gui\": \"button\", \"sequence\": 7, \"viewer\": \"MessageList\", \"persona_service_id\": 1, \"persona_service_path_id\": 7, \"content_type\": \"json\", \"path\": \"User_BlogComments\", \"login\": true, \"type\": \"user\", \"method\": \"FetchBlogComments\"}',7,1),

(8,1,'PictureMessages','Picture Comments','message','{\"category\": \"message\", \"name\": \"PictureMessages\", \"title\": \"Picture Comments\", \"url\": null, \"gui\": \"button\", \"sequence\": 8, \"viewer\": \"MessageList\", \"persona_service_id\": 1, \"persona_service_path_id\": 8, \"content_type\": \"json\", \"path\": \"User_PictureComments\", \"login\": true, \"type\": \"user\", \"method\": \"FetchPictureComments\"}',8,1),

(9,1,'VideoMessages','Video Comments','message','{\"category\": \"message\", \"name\": \"VideoMessages\", \"title\": \"Video Comments\", \"url\": null, \"gui\": \"button\", \"sequence\": 9, \"viewer\": \"MessageList\", \"persona_service_id\": 1, \"persona_service_path_id\": 9, \"content_type\": \"json\", \"path\": \"User_VideoComments\", \"login\": true, \"type\": \"user\", \"method\": \"FetchVideoComments\"}',9,1),

(10,3,'MyLiveVideos','My Live Videos','video','{\"category\": \"video\", \"name\": \"MyLiveVideos\", \"title\": \"My Live Videos\", \"url\": \"https:\\/\\/upload.video.google.com\\/Status?f=l&hl=en\", \"gui\": \"button\", \"sequence\": 1, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 10, \"content_type\": \"json\", \"path\": \"Video_Live\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',1,1),

(11,3,'Top100Videos','Top 100','video','{\"category\": \"video\", \"name\": \"Top100Videos\", \"title\": \"Top 100\", \"url\": \"http:\\/\\/video.google.com\\/videoranking\", \"gui\": \"button\", \"sequence\": 2, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 11, \"content_type\": \"json\", \"path\": \"Video_Top\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',2,1),

(12,3,'ComedyVideos','Comedy','video','{\"category\": \"video\", \"name\": \"ComedyVideos\", \"title\": \"Comedy\", \"url\": \"http:\\/\\/video.google.com\\/videosearch?q=genre:comedy\", \"gui\": \"button\", \"sequence\": 3, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 12, \"content_type\": \"json\", \"path\": \"Video_Comedy\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',3,1),

(13,3,'MusicVideos','Music Videos','video','{\"category\": \"video\", \"name\": \"MusicVideos\", \"title\": \"Music Videos\", \"url\": \"http:\\/\\/video.google.com\\/videosearch?q=type:music_video\", \"gui\": \"button\", \"sequence\": 4, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 13, \"content_type\": \"json\", \"path\": \"Video_Music\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',4,1),

(14,3,'MovieVidoes','Movies','video','{\"category\": \"video\", \"name\": \"MovieVidoes\", \"title\": \"Movies\", \"url\": \"http:\\/\\/video.google.com\\/movietrailers.html\", \"gui\": \"button\", \"sequence\": 5, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 14, \"content_type\": \"json\", \"path\": \"Video_Movies\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',5,1),

(15,3,'SportsVideos','Sports','video','{\"category\": \"video\", \"name\": \"SportsVideos\", \"title\": \"Sports\", \"url\": \"http:\\/\\/video.google.com\\/videosearch?q=type%3Asports%20OR%20genre%3Asports\", \"gui\": \"button\", \"sequence\": 6, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 15, \"content_type\": \"json\", \"path\": \"Video_Sports\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',6,1),

(16,3,'AnimationVideos','Animation','video','{\"category\": \"video\", \"name\": \"AnimationVideos\", \"title\": \"Animation\", \"url\": \"http:\\/\\/video.google.com\\/videosearch?q=genre:animation\", \"gui\": \"button\", \"sequence\": 7, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 16, \"content_type\": \"json\", \"path\": \"Video_Animation\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',7,1),

(17,3,'TVVideos','TV Shows','video','{\"category\": \"video\", \"name\": \"TVVideos\", \"title\": \"TV Shows\", \"url\": \"http:\\/\\/video.google.com\\/videosearch?q=type%3Atvshow\", \"gui\": \"button\", \"sequence\": 8, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 17, \"content_type\": \"json\", \"path\": \"Video_TV\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',8,1),

(18,3,'PicksVideos','Google Picks','video','{\"category\": \"video\", \"name\": \"PicksVideos\", \"title\": \"Google Picks\", \"url\": \"http:\\/\\/video.google.com\\/videosearch?q=type%3Agpick&so=1\", \"gui\": \"button\", \"sequence\": 9, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 18, \"content_type\": \"json\", \"path\": \"Video_Picks\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',9,1),

(19,3,'SearchVideos','Search Google Videos','video','{\"category\": \"video\", \"name\": \"SearchVideos\", \"title\": \"Search Google Videos\", \"url\": \"http:\\/\\/video.google.com\\/videosearch?q=\", \"gui\": \"search\", \"sequence\": 10, \"viewer\": \"VideoList\", \"persona_service_id\": 3, \"persona_service_path_id\": 19, \"content_type\": \"json\", \"path\": \"Video_Search\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',10,1),

(20,4,'MyVideos','My Videos','video','{\"category\": \"video\", \"name\": \"MyVideos\", \"title\": \"My Videos\", \"url\": \"http:\\/\\/www.youtube.com\\/my_videos\", \"gui\": \"button\", \"sequence\": 1, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 20, \"content_type\": \"json\", \"path\": \"Video_MyVideos\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',1,1),

(21,4,'FavoriteVideos','My Favorites','video','{\"category\": \"video\", \"name\": \"FavoriteVideos\", \"title\": \"My Favorites\", \"url\": \"http:\\/\\/www.youtube.com\\/my_favorites\", \"gui\": \"button\", \"sequence\": 2, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 21, \"content_type\": \"json\", \"path\": \"Video_Favorites\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',2,1),

(22,4,'SubscriptionVideos','My Subscriptions','video','{\"category\": \"video\", \"name\": \"SubscriptionVideos\", \"title\": \"My Subscriptions\", \"url\": \"http:\\/\\/www.youtube.com\\/subscription_center\", \"gui\": \"button\", \"sequence\": 3, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 22, \"content_type\": \"json\", \"path\": \"Video_Subscriptions\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',3,1),

(23,4,'MostRecentVideos','Most Recent','video','{\"category\": \"video\", \"name\": \"MostRecentVideos\", \"title\": \"Most Recent\", \"url\": \"http:\\/\\/www.youtube.com\\/browse?s=mr\", \"gui\": \"button\", \"sequence\": 4, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 23, \"content_type\": \"json\", \"path\": \"Video_MostRecent\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',4,1),

(24,4,'MostViewedVideos','Most Viewed','video','{\"category\": \"video\", \"name\": \"MostViewedVideos\", \"title\": \"Most Viewed\", \"url\": \"http:\\/\\/www.youtube.com\\/browse?s=mp\", \"gui\": \"button\", \"sequence\": 5, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 24, \"content_type\": \"json\", \"path\": \"Video_MostViewed\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',5,1),

(25,4,'TopRatedVideos','Top Rated','video','{\"category\": \"video\", \"name\": \"TopRatedVideos\", \"title\": \"Top Rated\", \"url\": \"http:\\/\\/www.youtube.com\\/browse?s=tr\", \"gui\": \"button\", \"sequence\": 6, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 25, \"content_type\": \"json\", \"path\": \"Video_TopRated\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',6,1),

(26,4,'MostDiscussedVideos','Most Discussed','video','{\"category\": \"video\", \"name\": \"MostDiscussedVideos\", \"title\": \"Most Discussed\", \"url\": \"http:\\/\\/www.youtube.com\\/browse?s=md\", \"gui\": \"button\", \"sequence\": 7, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 26, \"content_type\": \"json\", \"path\": \"Video_MostDiscussed\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',7,1),

(27,4,'TopFavoriteVideos','Top Favorites','video','{\"category\": \"video\", \"name\": \"TopFavoriteVideos\", \"title\": \"Top Favorites\", \"url\": \"http:\\/\\/www.youtube.com\\/browse?s=mf\", \"gui\": \"button\", \"sequence\": 8, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 27, \"content_type\": \"json\", \"path\": \"Video_TopFavorites\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',8,1),

(28,4,'MostLinkedVideos','Most Linked','video','{\"category\": \"video\", \"name\": \"MostLinkedVideos\", \"title\": \"Most Linked\", \"url\": \"http:\\/\\/www.youtube.com\\/browse?s=mrd\", \"gui\": \"button\", \"sequence\": 9, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 28, \"content_type\": \"json\", \"path\": \"Video_MostLinked\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',9,1),

(29,4,'RecentlyFeaturedVideos','Recently Featured','video','{\"category\": \"video\", \"name\": \"RecentlyFeaturedVideos\", \"title\": \"Recently Featured\", \"url\": \"http:\\/\\/www.youtube.com\\/browse?s=rf\", \"gui\": \"button\", \"sequence\": 10, \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 29, \"content_type\": \"json\", \"path\": \"Video_RecentlyFeatured\", \"login\": false, \"type\": \"video\", \"method\": \"FetchVideoList\"}',10,1),

(30,4,'SearchVideos','Search YouTube Videos','video','{\"category\": \"video\", \"name\": \"SearchVideos\", \"sequence\": 11, \"url\": \"http:\\/\\/www.youtube.com\\/results?search=Search&search_query=\", \"gui\": \"search\", \"title\": \"Search YouTube Videos\", \"viewer\": \"VideoList\", \"persona_service_id\": 4, \"persona_service_path_id\": 30, \"content_type\": \"json\", \"path\": \"Video_Search\", \"login\": true, \"type\": \"video\", \"method\": \"FetchVideoList\"}',11,1),

(31,2,'Profile','My Profile','user','{\"category\": \"user\", \"name\": \"Profile\", \"sequence\": 1, \"url\": \"\", \"gui\": \"button\", \"title\": \"My Profile\", \"viewer\": \"Profile\", \"persona_service_id\": 2, \"persona_service_path_id\": 31, \"content_type\": \"json\", \"path\": \"User_Profile\", \"login\": true, \"type\": \"profile\", \"method\": \"FetchUserProfile\"}',1,1),

(32,2,'Friends','My Friends','friends','{\"category\": \"user\", \"name\": \"Friends\", \"sequence\": 2, \"url\": \"\", \"gui\": \"button\", \"title\": \"My Friends\", \"viewer\": \"Friends\", \"persona_service_id\": 2, \"persona_service_path_id\": 32, \"content_type\": \"json\", \"path\": \"User_Friends\", \"login\": true, \"type\": \"friends\", \"method\": \"FetchFriends\"}',2,1),

(33,2,'Messages','My Messages','messages','{\"category\": \"messages\", \"name\": \"Messages\", \"sequence\": 3, \"url\": \"\", \"gui\": \"button\", \"title\": \"My Messages\", \"viewer\": \"Messages\", \"persona_service_id\": 2, \"persona_service_path_id\": 33, \"content_type\": \"json\", \"path\": \"User_Messages\", \"login\": true, \"type\": \"messages\", \"method\": \"FetchMessages\"}',3,1)",
    ));

    $this->qup_all_networks("recent_media_track table added to handle recent media on homepage", "CREATE TABLE {recent_media_track} (`id` INT( 11 ) NOT NULL AUTO_INCREMENT , `cid` INT( 11 ) , `type` INT( 1 ) , `created` INT( 11 ) , PRIMARY KEY ( `id` ) )");

    $this->qup("adding Flickr Friends to persona_service_paths",
   "INSERT INTO {persona_service_paths} VALUES
(36,7,'Friends','My Friends','friends','{\"category\": \"user\", \"name\": \"Friends\", \"sequence\": 2, \"url\": \"\", \"gui\": \"button\", \"title\": \"My Friends\", \"viewer\": \"Friends\", \"persona_service_id\": 7, \"persona_service_path_id\": 36, \"content_type\": \"json\", \"path\": \"User_Friends\", \"login\": true, \"type\": \"friends\", \"method\": \"FetchFriends\"}',3,1)"
    );
//2007-02-17, david disabling services that aren't complete
    $this->qup("disabling incomplete Flickr for pre1 release",
  "UPDATE persona_services
  SET enabled = 0
  WHERE name = 'Flickr'");
    $this->qup("disabling MySpace features not now supported in scraper, e.g. reflecting MySpace site changes",
  "UPDATE persona_service_paths
  SET enabled = 0
  WHERE persona_service_id = 1");
//2007-02-17, david enabling updated MySpace My Friends
    $this->qup("enable newly re-supported MySpace My Friends feature",
  "UPDATE persona_service_paths
  SET enabled = 1
  WHERE persona_service_path_id = 3");

    // 2007-02-20 Phil - create cache for things like recently fetched delicious and flickr data.
    // This sort of thing could go in the profile space, as with everything from the scraper, but
    // for the moment let's just throw it in here.
    $this->qup("create ext_cache table 2",
       "CREATE TABLE ext_cache (
          id VARCHAR(255) NOT NULL, PRIMARY KEY (id),
          created DATETIME NOT NULL, KEY (created),
          user_id INT NOT NULL, KEY (user_id, id, created),
          data TEXT NOT NULL)");

    // 2007-02-22 Phil - moved from Ekta's web/refine_group_access.php
    $this->qup_all_networks("get rid of group moderation", array(
      // remove access type and is_moderated entry from existing groups
      "UPDATE {groups} SET access_type = 0, is_moderated = 0",
      // to remove contents, waiting for moderation
      create_function("", '
        $res = Dal::query("SELECT content_id FROM {contents} WHERE is_active = 2");
        global $network_prefix;
        while($row = Dal::row_object($res)) {
          Content::delete_by_id($row->content_id);
        }
      ')));

    // 2007-02-22, martin re enabling Flickr
    $this->qup("re enabling Flickr Service",
  "UPDATE persona_services
  SET enabled = 1
  WHERE name = 'Flickr'");

    // 2007-02-26 Phil - speeding things up
    // queries seen on badge_create.php
    $this->qup("2007-02-26 optimization", array(
      // replace (user_id) key with (user_id, field_type, field_name)
      "ALTER TABLE {user_profile_data} DROP KEY user_id,
         ADD KEY profile_fields (user_id, field_type(32), field_name(32))", // Phil: modified 2007-05-30 to avoid crash on UTF-8 MySQL
      // remove duplicate (user_id) key on relations
      "ALTER TABLE {relations} DROP KEY user_id",
      // message folder lookup
      "ALTER TABLE {message_folder} ADD KEY name_to_fid (uid, name)",
      // fid index on user_message_folder
      "ALTER TABLE {user_message_folder} ADD KEY fid (fid)",
      ));

    // 2007-02-27 Phil - speeding up ContentCommentsTest
    $this->qup_all_networks("2007-02-27 content and comments", array(
      // fetch all comments for a content item
      "ALTER TABLE {comments} ADD KEY comments_for_content (is_active, content_id, created)",
      // fetch all trackbacks for a content item
      "ALTER TABLE {trackback_contents} ADD KEY trackbacks_for_content (content_id)",
      // fetch tags for a content item, or content tagged with a tag
      "ALTER TABLE {tags_contents} ADD KEY tag_to_content (tag_id, content_id),
         ADD KEY content_to_tags (content_id, tag_id)",
    ));
//2007-03-09, david enabling updated Flickr My Pictures and My Profile
/* NOTE: this couldn't work, as those entries were never in the DB to begin with
*  adding them below (Martin)
$this->qup("enable Flickr My Friends and User Profile features",
 "UPDATE persona_service_paths
  SET enabled = 1
  WHERE persona_service_path_id = 35
  OR persona_service_path_id = 34");
*/

    $this->qup("2007-03-12 Martin: adding Flickr Profile and Photos",
   "INSERT INTO {persona_service_paths} VALUES
   (34,7,'UserProfile','My Profile','user','{\"category\": \"user\", \"name\": \"MyProfile\", \"sequence\": 1, \"url\": \"\", \"gui\": \"button\", \"title\": \"My Profile\", \"viewer\": \"Profile\", \"persona_service_id\": 2, \"persona_service_path_id\": 34, \"content_type\": \"json\", \"path\": \"User_Profile\", \"login\": true, \"type\": \"profile\", \"method\": \"FetchUserProfile\"}',1,1),
   (35,7,'MyPictures','My Pictures','pictures','{\"category\": \"pictures\", \"name\": \"MyPictures\", \"sequence\": 1, \"url\": \"\", \"gui\": \"button\", \"title\": \"My Pictures\", \"viewer\": \"Pictures\", \"persona_service_id\": 2, \"persona_service_path_id\": 35, \"content_type\": \"json\", \"path\": \"MyPictures\", \"login\": true, \"type\": \"pictures\", \"method\": \"FetchUserPictures\"}',2,1)");

    $this->qup("replace profile_fields key with profile_fields_seq", "ALTER TABLE {user_profile_data}
      DROP KEY profile_fields,
      ADD KEY profile_fields_seq (user_id, field_type(32), field_name(32), seq)"); // Phil: modified 2007-05-30 to avoid crash on UTF-8 MySQL

    $this->qup("add expires column to ext_cache table", array(
      "TRUNCATE TABLE {ext_cache}",
      "ALTER TABLE {ext_cache}
        ADD COLUMN expires DATETIME NOT NULL,
        DROP KEY created,
        DROP KEY user_id,
        ADD KEY item (user_id, id, expires)",
      ));

    // so we can completely index comment content in spam_terms
    $this->qup("add blacklist+frequency columns to spam_terms table", "ALTER TABLE {spam_terms}
      ADD COLUMN frequency INT NOT NULL DEFAULT 0,
      ADD KEY by_word (term),
      ADD KEY by_freq (blacklist, frequency, term),
      ADD COLUMN blacklist BOOLEAN DEFAULT 1,
      ADD KEY bad_words (blacklist, term)");

    // {{{ 2007-03-19 Marek + Phil - Adding indexes all over the place
    $this->qup("2007-03-19 database optimization - global tables", array(
/*
      // default_announcements no longer used
      "ALTER TABLE {default_announcements}
        ADD INDEX content_id ( `content_id` ),
        ADD INDEX is_active ( `is_active` )",
*/
      "ALTER TABLE {external_feed} ADD INDEX is_active ( `is_active` )",
      "ALTER TABLE {feed_data} ADD INDEX feed_content ( `feed_id`, `publish_date` )",
      "ALTER TABLE {forgot_password}
        ADD PRIMARY KEY (forgot_password_id),
        ADD INDEX user_status_id ( `user_id` , `status` , `forgot_password_id` )",
      "ALTER TABLE {linkcategories} ADD INDEX user_categories ( `user_id` , `is_active` , `created`)",
      "ALTER TABLE {links} ADD INDEX cat_active_created ( `category_id`, `is_active`, `created` )",
      "ALTER TABLE {moduledata} ADD INDEX created ( `created` )",
      "ALTER TABLE {networks_users}
        DROP KEY network_user_id,
        ADD INDEX network_user_id_type ( `network_id`, `user_id`, `user_type` )",
      "ALTER TABLE {persona_properties}
        ADD INDEX parent_id ( `parent_id` ),
        ADD INDEX persona_id ( `persona_id` )",
      "ALTER TABLE {persona_service_paths} ADD INDEX persona_service_id ( `persona_service_id` )",
      "ALTER TABLE {personas}
        ADD INDEX persona_service_id ( `persona_service_id` ),
        ADD INDEX user_id ( `user_id` )",
      "ALTER TABLE {private_messages} ADD INDEX sender_when ( `sender_id`, `sent_time` )",
      "ALTER TABLE {relation_classifications}
        ADD INDEX relation_type_id ( `relation_type_id` ),
        ADD INDEX relation_type ( `relation_type` )",
      "ALTER TABLE {relations} ADD INDEX network_uid ( `network_uid` )",
      "ALTER TABLE {roles} ADD PRIMARY KEY ( `role_id` )",
      "ALTER TABLE {spam_terms} ADD INDEX term ( `term` )",
      "ALTER TABLE {svn_meta} ADD INDEX revision ( `revision` )",
      "ALTER TABLE {tags_networks}
        ADD INDEX `network_to_tag` ( `network_id`, `tag_id` ),
        ADD INDEX `tag_to_network` ( `tag_id`, `network_id` )",
      "ALTER TABLE {tags_users}
        ADD INDEX `tag_to_user` ( `tag_id`, `user_id` ),
        ADD INDEX `user_to_tag` ( `user_id`, `tag_id` )",
      "ALTER TABLE {user_feed}
        ADD INDEX `feed_to_user` ( `feed_id`, `user_id` ),
        ADD INDEX `user_to_feed` ( `user_id`, `feed_id` )",
      "ALTER TABLE {user_message_folder} ADD INDEX mid ( `mid` )",
      "ALTER TABLE {user_profile_data} ADD INDEX user_id ( `user_id` )",
      "ALTER TABLE {users} ADD INDEX email ( `email` )",
    ));
    $this->qup_all_networks("2007-03-19 database optimization", array(
      "ALTER TABLE {audios} ADD PRIMARY KEY ( `content_id` )",
      "ALTER TABLE {boardmessages}
        ADD INDEX parent_id ( `parent_id` ),
        ADD INDEX parent_type ( `parent_type` ),
        ADD INDEX user_id ( `user_id` )",
      "ALTER TABLE {categories_boardmessages}
        ADD INDEX `boardmessage_to_category` ( `boardmessage_id` , `category_id` ),
        ADD INDEX `category_to_boardmessage` ( `category_id`, `boardmessage_id` )",
      "ALTER TABLE {categories} ADD INDEX active_position ( `is_active`, `position` )",
      "ALTER TABLE {comments} ADD INDEX comments_by_user ( `is_active`, `user_id`, `created` )",
      "ALTER TABLE {content_routing_destinations} ADD INDEX user_id ( `user_id` )",
      "ALTER TABLE {content_types} ADD INDEX name ( `name` )",
      "ALTER TABLE {contentcollections_albumtype}
        ADD INDEX `album_type_to_contentcollection` ( `album_type_id`, `contentcollection_id` ),
        ADD INDEX `contentcollections_to_album_type` ( `contentcollection_id`, `album_type_id` )",
      "ALTER TABLE {contentcollections} ADD INDEX is_active ( `is_active` )",
      "ALTER TABLE {contents_sbmicrocontents}
        ADD INDEX `content_to_microcontent` ( `content_id`, `microcontent_id` ),
        ADD INDEX `microcontent_to_content` ( `microcontent_id`, `content_id` )",
      "ALTER TABLE {groups} ADD INDEX category_id ( `category_id` )",
      "ALTER TABLE {images} ADD PRIMARY KEY ( `content_id` )",
      "ALTER TABLE {invitations}
        ADD INDEX inv_collection_id ( `inv_collection_id` ),
        ADD INDEX inv_status ( `inv_status` ),
        ADD INDEX user_id ( `user_id` )",
      "ALTER TABLE {moderation_queue} ADD INDEX collection_id ( `collection_id` )",
      // modules_settings not used any more?
      "ALTER TABLE {modules_settings}
        ADD INDEX is_active ( `is_active` ),
        ADD INDEX module_id ( `module_id` )",
      "ALTER TABLE {network_linkcategories}
        ADD INDEX user_cat ( `user_id`, `category_name`, `category_id` ),
        ADD INDEX user_active ( `user_id` , `is_active` )",
      "ALTER TABLE {network_links}
        ADD INDEX cat_title ( `category_id`, `title` ),
        ADD INDEX is_active ( `is_active` )",
      "ALTER TABLE {recent_media_track} ADD INDEX cid ( `cid` )",
      "ALTER TABLE {tags_contentcollections}
        ADD INDEX `contentcollection_to_tag` ( `collection_id`, `tag_id` ),
        ADD INDEX `tag_to_contentcollection` ( `tag_id`, `collection_id` )",
      "ALTER TABLE {users_roles}
        ADD INDEX `role_to_user` ( `role_id`, `user_id` ),
        ADD INDEX `user_to_role` ( `user_id`, `role_id` )",
      "ALTER TABLE {videos} ADD PRIMARY KEY ( `content_id` )",
    ));
    // }}}

    $this->qup("2007-03-31 Martin adding event table for class Event",
  "CREATE TABLE `events` (
    eid INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (eid),
    cid TEXT NOT NULL,
    uid INT NOT NULL, KEY (uid),
    title TEXT NOT NULL,
    start_time DATETIME NOT NULL, KEY (start_time),
    end_time DATETIME NOT NULL, KEY (end_time),
    event_data TEXT NULL,
    KEY delta (start_time, end_time)
    ) DEFAULT CHARSET=UTF8");

    $this->qup("2007-04-02 Martin renaming fields to be inline with PA naming conventions",
  "ALTER TABLE `events`
    CHANGE eid event_id INT( 11 ) NOT NULL AUTO_INCREMENT,
    CHANGE cid content_id TEXT NOT NULL,
    CHANGE uid user_id INT( 11 ) NOT NULL,
    CHANGE title event_title TEXT NOT NULL");


    $this->qup("2007-04-03 Martin adding event_assosciations table for class EventAssociations",
  "CREATE TABLE `events_associations` (
    assoc_id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (assoc_id),
    event_id INT NOT NULL, KEY (event_id),
    user_id INT NOT NULL, KEY (user_id),
    assoc_target_type VARCHAR(30) NOT NULL, KEY (assoc_target_type),
    assoc_target_id INT NOT NULL, KEY (assoc_target_id),
    assoc_target_name TEXT NOT NULL,
    event_title TEXT NOT NULL,
    start_time DATETIME NOT NULL, KEY (start_time),
    end_time DATETIME NOT NULL, KEY (end_time),
    event_data TEXT NULL,
    KEY target (assoc_target_type, assoc_target_id)
    ) DEFAULT CHARSET=UTF8");

    $this->qup("2007-04-07 Martin correcting name for assoc_data",
  "ALTER TABLE `events_associations`
    CHANGE event_data assoc_data TEXT NULL");



    $this->qup("2007-04-23 Arvind added a table for admin_roles",
      "CREATE TABLE `admin_roles` (
      `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
      `name` VARCHAR( 100 ) ,
      `description` TINYTEXT,
      `created` INT( 11 ) ,
      `changed` INT( 11 ) ,
      PRIMARY KEY ( `id` )
      ) TYPE = MYISAM ");

    $this->qup("2007-04-17 Phil fixing ext_cache",
      "ALTER TABLE ext_cache DROP PRIMARY KEY,
        CHANGE COLUMN id cache_key VARCHAR(255) NOT NULL");

    $this->qup("2007-04-23 Arvind added a table for tasks_roles",
      "CREATE TABLE `tasks_roles` (
      `task_id` INT( 11 ) NOT NULL  ,
      `role_id` INT( 11 ) NOT NULL
      ) TYPE = MYISAM ");

    $this->qup("2007-04-23 Arvind added a table for tasks",
      "CREATE TABLE `tasks` (
      `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
      `name` VARCHAR( 255 ) NOT NULL,
      `description` TINYTEXT,
       PRIMARY KEY (`id`)
      ) TYPE = MYISAM ");

    //adding a column task_value. We will put the checks according to this value
    //as it is more readable
    $this->qup("2007-05-02 Arvind altered table for tasks to have task_value",
         " ALTER TABLE `tasks` ADD `task_value` VARCHAR( 255 ) NOT NULL ;");

    $this->qup("2007-04-26 Arvind added a task - Manage settings in tasks",
      " INSERT INTO `tasks` (id, name, description) VALUES (1, 'Manage Settings', 'Manage settings of your network.'); ");



    $this->qup("2007-04-26 Arvind added a task - meta network in tasks",
      "INSERT INTO `tasks` (id, name, description) VALUES (2, 'Meta network', 'Manage meta network controls here.'); ");


    $this->qup("2007-04-26 Arvind added a task - manage ads in tasks",
      "INSERT INTO `tasks` (id, name, description) VALUES (3, 'Manage Ads', 'Manage Ads which will appear on the pages of networks.'); ");


    $this->qup("2007-04-26 Arvind added a task - notifications in tasks",
      "INSERT INTO `tasks` (id, name, description) VALUES (4, 'Notifications', 'Email and personal message box notifications.'); ");


    $this->qup("2007-04-26 Arvind added a task - links of network in tasks",
      "INSERT INTO `tasks` (id, name, description) VALUES (5, 'Manage Links', 'Manage the links of the network the user will get at the time of registration.');");


    $this->qup("2007-04-26 Arvind  added a task - manage content in tasks",
      "INSERT INTO `tasks` (id, name, description) VALUES (6, 'Manage content', 'Manage you content, comments, forums.');");


    $this->qup("2007-04-26 Arvind added a task - default settings in task",
      "INSERT INTO `tasks` (id, name, description) VALUES (7, 'User defaults', 'Manage the default settings that user wil get.'); ");

    $this->qup("2007-04-26 Arvind added a task - manage themes",
      "INSERT INTO `tasks` (id, name, description) VALUES (8, 'Themes', 'Manage themes and customize them.');");

    //updating the table entries to contain the task values
    $tasks = array(1=>'manage_settings', 2=>'meta_networks', 3=>'manage_ads',4=>'notifications', 5=>'manage_links', 6=>'manage_content', 7=>'user_defaults', 8=>'manage_themes');
    foreach($tasks as $key=>$val){
      $this->qup("2007-05-02 Arvind updated the row of task table having id = $key",
     "UPDATE tasks SET task_value = '$val' WHERE id = $key");
    }
   // db entry for configurable email : Ekta 30/07/2007
/*
   NOTE: this is removed - new Email messages system implemented!
   Z. Hron, Jan. 2009.
*/
/*
    $this->qup_all_networks("create email_messages table email_messages",
           "CREATE TABLE IF NOT EXISTS {email_messages} (
            id INT( 11 ) NOT NULL ,
            type VARCHAR( 255 ) NOT NULL ,
            description VARCHAR( 255 ) NOT NULL ,
            subject VARCHAR( 255 ) NOT NULL ,
            message TEXT NOT NULL ,
            configurable_variables TEXT NOT NULL ,
            PRIMARY KEY ( id , type )
            )");
   global $email_messages;
   $this->qup_all_networks("2008-01-14 truncate the rows of email_messages having", "TRUNCATE TABLE {email_messages}");
    foreach ($email_messages as $type_id=>$data) {
      $type = $data['type'];// need to insert description
      $description = mysql_escape_string($data['description']);
      $subject = mysql_escape_string($data['subject']);
      $message_file = $data['message'];
      $EmailMessageFile = "web/includes/email_msg_text/$message_file";
      if(file_exists(PA::$project_dir . '/' . $EmailMessageFile)) {
        $EmailMessageFile = PA::$project_dir . '/' . $EmailMessageFile;
      } else {
        $EmailMessageFile = PA::$core_dir . '/' . $EmailMessageFile;
      }
      $fh = fopen($EmailMessageFile, 'r');
      if (filesize($EmailMessageFile)) {
        $theData = fread($fh, filesize($EmailMessageFile));
        $theData = mysql_escape_string($theData);
        fclose($fh);
      }
      $configurable_data = mysql_escape_string(serialize($data['configurable_variables']));
      $this->qup_all_networks("2008-01-14 Ekta Inserted the rows of email_messages having type = $type", "INSERT INTO {email_messages} (id, type, description, subject, message, configurable_variables) VALUES ($type_id, '$type', '$description', '$subject', '$theData', '$configurable_data')");
    }
*/
    $this->qup("2007-04-23 Arvind added a table for users_adminroles",
      "CREATE TABLE `users_adminroles` (
      `user_id` INT( 11 ) NOT NULL  ,
      `role_id` INT( 11 ) NOT NULL
      ) TYPE = MYISAM ");


//Fixed a bug in ad center for all the networks
$this->qup_all_networks("2007-06-21 Arvind altered table for advertisements on all networks",
      "ALTER TABLE {advertisements} CHANGE `orientation` `orientation` VARCHAR( 255 ) NULL DEFAULT NULL ");

  if (!$this->table_exists('site_ranking_parameters')) {
      $this->qup("site_ranking_parameters table",
        "CREATE TABLE `site_ranking_parameters` (
        `id` int(11) NOT NULL auto_increment,
        `name` varchar(50) NOT NULL,
        `description` text NOT NULL,
        `point` int(11) NOT NULL,
        PRIMARY KEY  (`id`)
        ) ");
    }
   if ($this->table_exists('site_ranking_parameters')) {
     $this->qup("insert default data for site_ranking_parameters1", "INSERT INTO `site_ranking_parameters` (`id`, `name`, `description`, `point`) VALUES
     (1, 'Uploading a Picture', 'Points will be give to user who has uploaded his picture. ', 1)");
     $this->qup("insert default data for site_ranking_parameters2", "INSERT INTO `site_ranking_parameters` (`id`, `name`, `description`, `point`) VALUES
     (2, 'Profile Views', 'Whenever user provile is viewed by anyone he get points.', 1)");
     $this->qup("insert default data for site_ranking_parameters3", "INSERT INTO `site_ranking_parameters` (`id`, `name`, `description`, `point`) VALUES
     (3, 'Number of friends in buddy list', 'More number of friends a user have, more points he get.', 1)");
     $this->qup("insert default data for site_ranking_parameters4", "INSERT INTO `site_ranking_parameters` (`id`, `name`, `description`, `point`) VALUES
     (4, 'Albums Uploaded', 'More images a user upload, more points he get.', 1)");
     $this->qup("insert default data for site_ranking_parameters5", "INSERT INTO `site_ranking_parameters` (`id`, `name`, `description`, `point`) VALUES
     (5, 'Number of group created by user', 'More group a user create, more points he get.', 1)");
     $this->qup("insert default data for site_ranking_parameters6", "INSERT INTO `site_ranking_parameters` (`id`, `name`, `description`, `point`) VALUES
     (6, 'Number of hour spent by user on the site', 'More time a user spent on the site, more points he get.', 1)");
    }
   if (!$this->table_exists('config_variables')) {
      $this->qup("config_variables table",
        "CREATE TABLE `config_variables` (
        `variable` varchar(100) NOT NULL,
        `value` tinytext NOT NULL,
        PRIMARY KEY  (`variable`)
        ) ");
    }

    // adding manage_events
    $this->qup("2007-05-27 Martin added a task - manage events",
      "INSERT INTO `tasks` (task_value, name, description) VALUES ('manage_events', 'Events', 'Create events and manage them.');");


    // 2007-05-30 Phil: preparing for utf-8
    $this->qup("2007-05-30 Phil utf-8 preparation", array(
      // Key 'user_id' reappeared accidentally in the 2007-03-19
      // change, and key 'profile_fields_seq' needs shortening to work
      // with UTF-8.
      "ALTER TABLE user_profile_data DROP KEY user_id,
       DROP KEY profile_fields_seq,
       ADD KEY profile_fields_seq (user_id, field_type(32), field_name(32), seq)",
      ));

    // 2007-06-12 Phil: last minute bugfix for 1.2pre5: clear out old custom settings so all users get new modules and the external feed selector
    $this->qup_all_networks("2007-06-12 blow away page_settings", "DELETE FROM {page_settings}");

    // 2007-06-15 Phil: proper persistent logins
    $this->qup("2007-06-15 login_cookies table 2", "CREATE TABLE login_cookies (
      user_id INT NOT NULL,
      series VARCHAR(32) NOT NULL,
      token VARCHAR(32) NOT NULL,
      PRIMARY KEY(user_id, series),
      expires DATETIME NOT NULL,
      KEY expires (expires),
      user_agent VARCHAR(255) NOT NULL,
      ip_addr VARCHAR(16) NOT NULL
    )");

    // 2007-07-04 Gurpreet: is_configurable field to page_default_settings
    $this->qup_all_networks("adding is_configurable field to page_default_settings", "ALTER TABLE {page_default_settings}
    ADD `is_configurable` BINARY( 1 ) DEFAULT '0' ");

// 2007-07-10 Arvind: long network names
    $this->qup("2007-07-10 Altering network table for long title and subtitle", "ALTER TABLE `networks` CHANGE `name` `name` VARCHAR( 255 )  DEFAULT NULL");
   // 2007-07-04 Ekta - creating table footer_links
  // This table will be used for managing footer links at PA
  $this->qup_all_networks("creating table footer_links",
                "CREATE TABLE IF NOT EXISTS {footer_links} (
                `id` int(11) NOT NULL auto_increment,
                `caption` varchar(255) default NULL,
                `url` varchar(255) default NULL,
                `is_active` tinyint(1) default NULL,
                `extra` text,
                PRIMARY KEY  (`id`)
                ) ");
 // This table will be used for managing static pages at PA
  $this->qup_all_networks("create static_pages table",
           "CREATE TABLE IF NOT EXISTS {static_pages}
             (`id` int(11) NOT NULL auto_increment,
              `caption` varchar(255),
              `url` varchar(255),
              `page_text` text,
              PRIMARY KEY (`id`)
            )");

    $this->qup("create files table", "CREATE TABLE files (
      file_id INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(file_id),
      filename VARCHAR(255) NOT NULL,
      file_class VARCHAR(32) NOT NULL,
      mime_type VARCHAR(255) NOT NULL,
      incomplete BOOL NOT NULL DEFAULT 1,
        KEY(incomplete),
      created DATETIME NOT NULL,
      link_count INT NOT NULL DEFAULT 0,
        KEY(link_count),
      storage_backend VARCHAR(32),
      local_id VARCHAR(255)
      )");
    $this->qup("modify files and add file_links table", array(
     "ALTER TABLE files
        ADD COLUMN last_linked DATETIME,
        ADD COLUMN last_unlinked DATETIME",
     "CREATE TABLE file_links (
      link_id INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(link_id),
      file_id INT NOT NULL,
        KEY(file_id),
      role VARCHAR(32) NOT NULL,
      user_id INT,
      network_id INT,
      group_id INT,
      ad_id INT,
      content_id INT,
      parent_file_id INT,
      dim VARCHAR(32),
        KEY thumbnail_lookup (role, parent_file_id, dim),
        KEY user_files (user_id, role)
     )",
    ));
    $this->qup("add index for cleanupFiles reaping query",
     "ALTER TABLE files
        DROP KEY link_count,
        ADD KEY cleanup_search (link_count, last_unlinked, created)");
    $this->qup("changing index on file_links to include file_id",
     "ALTER TABLE file_links
        DROP KEY file_id,
        DROP PRIMARY KEY,
        ADD PRIMARY KEY (file_id, link_id)");

    // 2007-08-03 Saurabh - creating table for Rating
    $this->qup_all_networks("creating rating table for rating various entities in the system",
      "CREATE TABLE {rating} (
      `index_id` int(11) NOT NULL auto_increment,
      `rating_type` enum('user','content','collection','tag','comment','network') NOT NULL default 'content',
      `type_id` int(11) NOT NULL default '0',
      `attribute_id` smallint(3) NOT NULL default '-1',
      `rating` mediumint(5) NOT NULL default '0',
      `max_rating` mediumint(5) NOT NULL default '0',
      `user_id` int(11) NOT NULL default '0',
      PRIMARY KEY  (`index_id`) )");

   // This line is added for new content
    $this->qup("adding new content type question on 3-aug", "INSERT INTO {content_types} VALUES (8, 'Question', 'Question')");

  // 2007-08-03 Himanshu - Creating table for Activity Log and User Popularity
   $this->qup("creating activity_log table for storing list of all the activities performed by all the users on the system",
              "CREATE TABLE {activity_log} (
              `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
              `type` VARCHAR( 255 ) NOT NULL,
              `subject` VARCHAR( 255 ) NOT NULL,
              `object` VARCHAR( 255 ) NOT NULL,
              `extra` TEXT NULL,
              `time` INT( 11 ) NOT NULL,
              `status` VARCHAR( 10 ) NOT NULL)");
    $this->qup("Inserting index as pair of subject and time ",
               "ALTER TABLE `activity_log` ADD INDEX `chrono_user_activity` ( `subject` , `time` )");

    $this->qup("creating table user_popularity for having popularity of each user according to the point alloted to each activity performed by user",
                "CREATE TABLE {user_popularity} (
                `user_id` INT NOT NULL ,
                `popularity` INT NOT NULL ,
                `time` INT( 11 ) NOT NULL,
                PRIMARY KEY ( `user_id` )
                ) ");

    $this->qup/*_all_networks*/("adding reviews table",
      "CREATE TABLE {reviews} (
        review_id INT NOT NULL AUTO_INCREMENT, -- id of this review
          PRIMARY KEY (review_id),
        is_active BOOL NOT NULL DEFAULT 1, -- set to 0 to delete this review
        subject_type VARCHAR(32) NOT NULL, -- class being reviewed ('content', 'ext_movie', 'ext_tvshow', etc).
        subject_id INT NOT NULL, -- actual id of thing being reviews (item id, content id, external movie id, etc)
        author_id INT NOT NULL, -- user_id of the reviewer
          KEY by_subject (is_active, subject_type, subject_id),
        title VARCHAR(255), -- title of review
        body TEXT NOT NULL, -- review content
        created DATETIME NOT NULL, -- when written
        updated DATETIME NOT NULL, -- when edited
          KEY by_freshness (is_active, subject_type, created)
      )");

    $this->qup("2007-10-23 Martin adding 'in_reply_to' field to keep track of threads",
      "ALTER TABLE `private_messages` ADD `in_reply_to` INT(11) NOT NULL DEFAULT '0'");
    $this->qup("Inserting index for threading",
               "ALTER TABLE `private_messages` ADD INDEX `threading` ( `message_id`, `in_reply_to` )");

    $this->qup("2007-10-24 Martin adding 'conversation_id' field to keep track of conversations",
      "ALTER TABLE `private_messages` ADD `conversation_id` INT(11) NOT NULL DEFAULT '0'");
    $this->qup("Inserting index for conversations",
               "ALTER TABLE `private_messages` ADD INDEX `conversations` ( `conversation_id` )");

    $this->qup("2007-11-12 Vinod added a task - Post content",
      "INSERT INTO `tasks` (id, name, description, task_value) VALUES (10, 'Post Contnet', 'Post content to community blog', 'post_to_community');");
      //added by santosh on 14-nov-07
    $this->qup_all_networks("Alter Categories table add type on 14-nov-07","ALTER TABLE {categories} ADD `type` enum('Default','Content')  NOT NULL default 'Default' AFTER `description`");
    //Dec-4, 2007
    $this->qup_all_networks("Adding group_type field to groups table","ALTER TABLE {groups} ADD `group_type` ENUM( 'regular', 'supergroup' ) DEFAULT 'regular' NOT NULL");
    //Dec-4, 2007
    $this->qup_all_networks("Adding super group settings table to all networks","CREATE TABLE {supergroup_settings} (
      `group_id` INT( 11 ) NOT NULL ,
      `ecommerce` BINARY( 1 ) DEFAULT '0' NOT NULL ,
      `mailing_list` BINARY( 1 ) DEFAULT '0' NOT NULL ,
      `playlist` BINARY( 1 ) DEFAULT '0' NOT NULL ,
      `showcasing` BINARY( 1 ) DEFAULT '0' NOT NULL ,
      `image` VARCHAR( 255 ) ,
      `flash_script` TEXT,
      PRIMARY KEY ( `group_id` )
      )");
      //Dec-4, 2007
      $this->qup_all_networks("Adding super group data table to all networks","CREATE TABLE {supergroup_data} (
      `group_id` INT( 11 ) NOT NULL ,
      `field_name` VARCHAR( 255 ) NOT NULL ,
      `field_value` TEXT,
      `setting` ENUM( 'ecommerce', 'mailing_list', 'playlist', 'showcasing' ) NOT NULL ,
      `seq` INT( 11 ) DEFAULT '0'
      )");
      //Dec-10, 2007
      $this->qup("Adding new task for for super group related features","INSERT INTO {tasks} VALUES (11, 'Super Group Creation', 'This task will allow the users having the assigned roles to create super groups', 'super_groups')");

     //added by santosh for items on 9-jan-2008
     $this->qup_all_networks("Adding table items on 9-jan","CREATE TABLE {items} (
      `item_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
      `type` TINYINT(1) NOT NULL ,
      `title` TEXT NOT NULL ,
      `description` TEXT NULL ,
      `url` TEXT NULL ,
      `status` TINYINT(1) NOT NULL ,
      `author_id` INT( 11 ) NOT NULL ,
      `created` INT( 11 ) NOT NULL ,
      `picture` TEXT NULL ,
      `changed` INT( 11 ) NOT NULL ,
      `changed_by` INT( 11 ) NOT NULL
      )");
    $this->qup_all_networks("Adding table items_data on 9-jan","CREATE TABLE {items_data} (
    `item_id` INT( 11 ) NOT NULL ,
    `field_name` TEXT NOT NULL ,
    `field_value` TEXT NOT NULL ,
    `seq` TINYINT(1) NOT NULL
    )");
   $this->qup_all_networks("Adding table items_user on 9-jan","CREATE TABLE {items_user} (
    `item_id` INT( 11 ) NOT NULL ,
    `user_id` INT( 11 ) NOT NULL ,
    `joined` INT( 11 ) NOT NULL ,
    `is_active` TINYINT(1) NOT NULL
    )");
    $this->qup_all_networks("Adding table items_collection on 9-jan","CREATE TABLE {items_collection} (
    `item_id` INT( 11 ) NOT NULL ,
    `collection_id` INT( 11 ) NOT NULL ,
    `is_default` TINYINT(1) NOT NULL
    )");
    $this->qup_all_networks("Adding table items_content on 9-jan","CREATE TABLE {items_content} (
    `item_id` INT( 11 ) NOT NULL ,
    `content_id` INT( 11 ) NOT NULL ,
    `is_default` TINYINT(1) NOT NULL
    )");
   $this->qup_all_networks("Adding table tags_item on 9-jan"," CREATE TABLE {tags_item} (
   `tag_id` INT( 11 ) NOT NULL,
   `item_id` INT( 11 ) NOT NULL
   )");
   $this->qup_all_networks("Adding table item_messages on 9-jan"," CREATE TABLE {item_messages} (
   `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
   `item_id` INT( 11 ) NOT NULL ,
   `user_id` INT( 11 ) NOT NULL ,
   `message` TEXT NOT NULL ,
   `created` INT( 11 ) NOT NULL ,
   `is_active` TINYINT(1) NOT NULL ,
   `type` TINYINT(1) NOT NULL ,
   PRIMARY KEY ( `id` )
   )");
   // updating rating table for celebrity type.
   if ($this->table_exists('rating')) {
    $this->qup_all_networks("alter table rating on 15-jan"," ALTER TABLE {rating} CHANGE `rating_type` `rating_type` ENUM( 'user', 'content', 'collection', 'tag', 'comment', 'network', 'celebrity' )  NOT NULL DEFAULT 'content'");
    }
    //updating for celebrity songs, headlines etc
    $this->qup_all_networks("alter table item_messages on 16-jan"," ALTER TABLE {item_messages} ADD `url` VARCHAR( 255 ) NULL ");
/*
    $this->qup("Add Login User as default Role",
         "INSERT INTO `admin_roles` (`id`, `name`, `description`, `created`, `changed`) VALUES (NULL, 'Login User', 'ordinary logged in users can do these tasks', NULL, NULL);");
    $this->qup("Put default Admin role Login User first, in id/position 0",
         "UPDATE admin_roles SET id = 0  WHERE name = 'Login User'"
         );
*/
    $this->qup("Fix spelling error 'Post Contnet' in Tasks list",
        "UPDATE tasks SET name = 'Post Content' WHERE task_value = 'post_to_community' "
        );
       //adding channel_comment table to be used in the comment widget.
    $this->qup("adding table channel_comment on 14-feb",
              "CREATE TABLE channel_comment (`comment_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`channel_id` INT( 11 ) NOT NULL ,`id` INT( 11 ) NOT NULL ,`comment` TEXT NOT NULL ,`user_id` INT( 11 ) NOT NULL ,`created` INT( 11 ) NOT NULL ,`is_active` TINYINT( 1 ) NOT NULL)"
              );

    // Role Management reorganization
    $this->rebuild_role_tables();

    //adding the type column to advertisment table so as to support textpad also
    $this->qup_all_networks('Adding type enum field to the advertisments table', "ALTER TABLE {advertisements} ADD `type` ENUM( 'ad', 'textpad' ) NOT NULL DEFAULT 'ad'");

    //adding the script to update all the entries in group table where caetgory-id is entered null
    $this->qup_all_networks('Altering group table entries where category_id is entered 0', "UPDATE {groups} SET category_id = 1 WHERE category_id = 0 ");
    //adding poll module.
    $this->qup("polls table created on 25-feb-08",
      "CREATE TABLE `polls` (`poll_id` int(11) NOT NULL auto_increment,`content_id` int(11) NOT NULL default '0', `title` varchar(255) NOT NULL default '', `user_id` int(11) NOT NULL default '0', `options` longtext, `created` int(11) NOT NULL default '0',  `changed` int(11) NOT NULL default '0', `is_active` int(11) NOT NULL default '0',  PRIMARY KEY  (`poll_id`))");

    $this->qup("poll_vote  table created on 25-feb-08",
      "CREATE TABLE `poll_vote` ( `vote_id` int(11) NOT NULL auto_increment, `poll_id` int(11) NOT NULL default '0', `user_id` int(11) NOT NULL default '0', `vote` longtext,  `is_active` int(11) NOT NULL default '0', PRIMARY KEY  (`vote_id`))");

    // 'files' table, to track files and how they are replicated across servers.
    // When a file is uploaded, it goes into the
    $this->qup("2008-02-28 PP local_files table",
    "CREATE TABLE `local_files` (
       file_id INT NOT NULL, -- provided by Storage, matches file_id in files table
         PRIMARY KEY(file_id),
       created DATETIME NOT NULL, -- when created
       user_id INT NOT NULL, -- who uploaded the file
         KEY when_who (created, user_id),
       filename VARCHAR(255) NOT NULL, -- the on-disk filename.  not unique; id+filename is used to identify a file, so we can have lots of different me.jpg files, etc
         KEY(filename),
       servers VARCHAR(255) NOT NULL -- format: 'sf1,la5,foo', where each server has a short id; these are all the servers with copies of the file
     )");

    // bringing that in sync with the data we get in StorageBackend and enabling deletion
    $this->qup("2008-02-28 PP local_files table 2",
    "ALTER TABLE `local_files`
       DROP KEY when_who,
       DROP COLUMN user_id,
       CHANGE COLUMN created timestamp DATETIME NOT NULL, -- last changed timestamp rather than creation date; if the file is deleted this will get updated so the deletion propagates
       ADD KEY when_changed (timestamp),
       ADD COLUMN is_deletion BOOL DEFAULT '0' -- set to 1 if this isn't a file but rather a deletion notification, so the deletion gets replicated
    ");

    // adding width/height columns to files
    $this->qup("2008-03-11 PP adding files.w/h", "ALTER TABLE files ADD COLUMN width INT NOT NULL, ADD COLUMN height INT NOT NULL");

    // 'local_file_sync_state' table, to indicate the replication state of each server.
    $this->qup("2008-02-28 PP local_file_sync_state table",
    "CREATE TABLE `local_file_sync_state` (
       server_id VARCHAR(16) NOT NULL, -- short id of this server ('sf1', 'la5', etc, specified in local_config.php, defaults to 'main')
       timestamp DATETIME NOT NULL -- timestamp of the last file handled.  we can assume that the server has copies of all files before this date.
     )");
    // 03-02-2008 Zoran Hron, temporrary disabling services that aren't complete
    $this->qup("disabling incomplete MySpace service",
                "UPDATE persona_services
                 SET enabled = 0
                 WHERE name = 'MySpace'");
    $this->qup("disabling incomplete Google service",
                "UPDATE persona_services
                 SET enabled = 0
                 WHERE name = 'Google'");
    $this->qup("disabling incomplete YouTube service",
                "UPDATE persona_services
                 SET enabled = 0
                 WHERE name = 'YouTube'");


     //page_views table is added to tracking hit count on the pages of radio-one.
    $this->qup("2008-03-04 page_views table added ",
    "CREATE TABLE `page_views` (`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`type` ENUM( 'article', 'video' ) NOT NULL ,`title` TEXT NULL ,`url` TEXT NOT NULL ,`view` TINYINT NOT NULL ,`time_stamp` INT( 11 ) NOT NULL)");
    //adding radio1_channel rating type for rating the channel content.
    if ($this->table_exists('rating')) {
      $this->qup("2008-03-14 radio1_channel rating type added",
    "ALTER TABLE {rating} CHANGE `rating_type` `rating_type` ENUM( 'user', 'content', 'collection', 'tag', 'comment', 'network', 'celebrity', 'radio1_channel' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'content'");
    }

    $this->qup("2008-03-26 PP user_email_history table added",
      "CREATE TABLE user_email_history (
        user_id INT NOT NULL,
        timestamp DATETIME NOT NULL,
          PRIMARY KEY(user_id, timestamp),
        email VARCHAR(255) NOT NULL,
        remote_ip CHAR(15),
        all_remote_ips VARCHAR(255))");

     $this->qup("2008-04-01 removing id from channel comment table" ,
      "ALTER TABLE channel_comment DROP id ");

      $this->qup("2008-04-01 adding slug to channel comment table" ,
      "ALTER TABLE channel_comment ADD slug TEXT NOT NULL ");

// ------- ActivityTypes -------------------------------------------------------------------------

  if (!$this->table_exists('activity_types')) {
      $this->qup("activity_types table",
        "CREATE TABLE `activity_types` (
        `id` int(11) NOT NULL auto_increment,
        `title` varchar(50) NOT NULL,
        `type` varchar(50) NOT NULL,
        `description` text NOT NULL default '',
        `points` int(11) NOT NULL,
        PRIMARY KEY  (`id`)
        ) ");
    }
   if ($this->table_exists('activity_types')) {
     $this->qup("insert default data for activity_types_1", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (1, 'User image upload', 'user_image_upload', 'Points will be give to user who has uploaded an image. ', 1)");
     $this->qup("insert default data for activity_types_2", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (2, 'User video upload', 'user_video_upload', 'Points will be give to user who has uploaded a video. ', 1)");
     $this->qup("insert default data for activity_types_3", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (3, 'User audio upload', 'user_audio_upload', 'Points will be give to user who has uploaded an audio. ', 1)");
     $this->qup("insert default data for activity_types_4", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (4, 'User post a blog', 'user_post_a_blog', 'Points will be give to user who has posted a blog. ', 1)");
     $this->qup("insert default data for activity_types_5", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (5, 'User post a comment', 'user_post_a_comment', 'Points will be give to user who has posted a comment. ', 1)");
     $this->qup("insert default data for activity_types_6", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (6, 'User friend requested', 'user_friend_requested', 'Points will be give to user who has friend requested. ', 1)");
     $this->qup("insert default data for activity_types_7", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (7, 'User friend added', 'user_friend_added', 'Points will be give to user who has friend added. ', 1)");
     $this->qup("insert default data for activity_types_8", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (8, 'User friend send a message', 'user_friend_send_a_message', 'Points will be give to user who has received a friend message. ', 1)");
     $this->qup("insert default data for activity_types_9", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (9, 'Group created', 'group_created', 'Points will be give to user who has a group created. ', 1)");
     $this->qup("insert default data for activity_types_10", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (10, 'Group joined', 'group_joined', 'Points will be give to user who has joined to a group. ', 1)");
     $this->qup("insert default data for activity_types_11", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (11, 'Group image upload', 'group_image_upload', 'Points will be give to user who has a group image uploaded. ', 1)");
     $this->qup("insert default data for activity_types_12", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (12, 'Group video upload', 'group_video_upload', 'Points will be give to user who has a group video uploaded. ', 1)");
     $this->qup("insert default data for activity_types_13", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (13, 'Group audio upload', 'group_audio_upload', 'Points will be give to user who has a group audio uploaded. ', 1)");
     $this->qup("insert default data for activity_types_14", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (14, 'Group post a blog', 'group_post_a_blog', 'Points will be give to user who has posted a group blog. ', 1)");
     $this->qup("insert default data for activity_types_15", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (15, 'Network joined', 'network_joined', 'Points will be give to user who has joined to a network. ', 1)");
     $this->qup("insert default data for activity_types_16", "INSERT INTO `activity_types` (`id`, `title`, `type`, `description`, `points`) VALUES
     (16, 'Network created', 'network_created', 'Points will be give to user who has created a network. ', 1)");
    }

    foreach ($this->networks as $network_prefix) {
//      set_time_limit(30);
      if ($network_prefix == 'default') continue;  // leave this table untouched for mother network
      if (!$this->table_exists($network_prefix . '_activity_types')) {

        $this->qup($network_prefix . "_activity_types table",
          "CREATE TABLE $network_prefix" . "_activity_types (
          `id` int(11) NOT NULL auto_increment,
          `title` varchar(50) NOT NULL,
          `type` varchar(50) NOT NULL,
          `description` text NOT NULL default '',
          `points` int(11) NOT NULL,
          PRIMARY KEY  (`id`)
         ) ");

         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_1", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (1, 'User image upload', 'user_image_upload', 'Points will be give to user who has uploaded an image. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_2", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (2, 'User video upload', 'user_video_upload', 'Points will be give to user who has uploaded a video. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_3", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (3, 'User audio upload', 'user_audio_upload', 'Points will be give to user who has uploaded an audio. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_4", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (4, 'User post a blog', 'user_post_a_blog', 'Points will be give to user who has posted a blog. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_5", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (5, 'User post a comment', 'user_post_a_comment', 'Points will be give to user who has posted a comment. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_6", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (6, 'User friend requested', 'user_friend_requested', 'Points will be give to user who has friend requested. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_7", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (7, 'User friend added', 'user_friend_added', 'Points will be give to user who has friend added. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_8", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (8, 'User friend send a message', 'user_friend_send_a_message', 'Points will be give to user who has received a friend message. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_9", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (9, 'Group created', 'group_created', 'Points will be give to user who has a group created. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_10", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (10, 'Group joined', 'group_joined', 'Points will be give to user who has joined to a group. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_11", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (11, 'Group image upload', 'group_image_upload', 'Points will be give to user who has a group image uploaded. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_12", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (12, 'Group video upload', 'group_video_upload', 'Points will be give to user who has a group video uploaded. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_13", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (13, 'Group audio upload', 'group_audio_upload', 'Points will be give to user who has a group audio uploaded. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_14", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (14, 'Group post a blog', 'group_post_a_blog', 'Points will be give to user who has posted a group blog. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_15", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (15, 'Network joined', 'network_joined', 'Points will be give to user who has joined to a network. ', 1)");
         $this->qup("$network_prefix" . "_activity_types, insert default data for activity_types_16", "INSERT INTO $network_prefix" . "_activity_types (`id`, `title`, `type`, `description`, `points`) VALUES
           (16, 'Network created', 'network_created', 'Points will be give to user who has created a network. ', 1)");
      }
    }
// ------- New PaForums -------------------------------------------------------------------------

    if (!$this->table_exists('pa_forums_users')) {
      $this->qup("2008-06-10, PA Forums by: Zoran Hron - creating pa_forums_users table",
        "CREATE TABLE `pa_forums_users` (
        `user_id` int(11) NOT NULL,
        `board_id` int(11) NOT NULL,
        `user_status` set('owner','admin','allowed','waiting','limited','banned') NOT NULL default 'allowed',
        `is_active` tinyint(1) NOT NULL,
        `date_join` date NOT NULL,
        KEY `board_id` (`board_id`),
        KEY `user_id` (`user_id`)
      )");
    }

    if (!$this->table_exists('pa_forum_board')) {
      $this->qup("2008-06-10, PA Forums by: Zoran Hron - creating pa_forum_board table",
        "CREATE TABLE `pa_forum_board` (
        `id` int(11) NOT NULL auto_increment,
        `owner_id` int(11) NOT NULL,
        `title` varchar(128) NOT NULL,
        `description` varchar(256) default NULL,
        `type` set('network','group','personal','hidden') default NULL,
        `theme` varchar(48) NOT NULL default 'default',
        `settings` text,
        `is_active` tinyint(1) NOT NULL default '1',
        `created_at` date NOT NULL,
        PRIMARY KEY  (`id`),
        KEY `owner_id` (`owner_id`)
      )");
    }

    if (!$this->table_exists('pa_forum')) {
      $this->qup("2008-06-10, PA Forums by: Zoran Hron - creating pa_forum table",
        "CREATE TABLE `pa_forum` (
        `id` int(11) NOT NULL auto_increment,
        `title` varchar(255) default NULL,
        `description` text,
        `is_active` tinyint(1) NOT NULL default '1',
        `category_id` int(11) default NULL,
        `sort_order` int(11) default NULL,
        `icon` varchar(128) default NULL,
        `created_at` datetime default NULL,
        `updated_at` datetime default NULL,
        PRIMARY KEY  (`id`),
        KEY `category_id` (`category_id`)
      )");
    }

    if (!$this->table_exists('pa_forum_category')) {
      $this->qup("2008-06-10, PA Forums by: Zoran Hron - creating pa_forum_category table",
        "CREATE TABLE `pa_forum_category` (
        `id` int(11) NOT NULL auto_increment,
        `name` varchar(255) default NULL,
        `board_id` int(11) NOT NULL,
        `description` text,
        `is_active` tinyint(1) NOT NULL default '1',
        `sort_order` int(11) default NULL,
        `created_at` datetime default NULL,
        `updated_at` datetime default NULL,
        PRIMARY KEY  (`id`),
        KEY `board_id` (`board_id`)
      )");
    }

    if (!$this->table_exists('pa_forum_post')) {
      $this->qup("2008-06-10, PA Forums by: Zoran Hron - creating pa_forum_post table",
        "CREATE TABLE `pa_forum_post` (
        `id` int(11) NOT NULL auto_increment,
        `title` varchar(255) default NULL,
        `content` text,
        `user_id` int(11) default NULL,
        `parent_id` int(11) default NULL,
        `thread_id` int(11) default NULL,
        `is_active` tinyint(1) NOT NULL default '1',
        `created_at` datetime default NULL,
        `updated_at` datetime default NULL,
        PRIMARY KEY  (`id`),
        KEY `user_id` (`user_id`),
        KEY `parent_id` (`parent_id`),
        KEY `thread_id` (`thread_id`),
        KEY `created_at` (`created_at`)
      )");
    }

    if (!$this->table_exists('pa_forum_thread')) {
      $this->qup("2008-06-10, PA Forums by: Zoran Hron - creating pa_forum_thread table",
        "CREATE TABLE `pa_forum_thread` (
        `id` int(11) NOT NULL auto_increment,
        `related_content_id` int(11) default NULL,
        `title` varchar(255) default NULL,
        `content` text,
        `status` set('public','private','hidden','locked','sticky') NOT NULL default 'public',
        `forum_id` int(11) default NULL,
        `user_id` int(11) default NULL,
        `viewed` int(11) default NULL,
        `is_active` tinyint(1) NOT NULL default '1',
        `created_at` datetime default NULL,
        `updated_at` datetime default NULL,
        PRIMARY KEY  (`id`),
        KEY `forum_id` (`forum_id`),
        KEY `user_id` (`user_id`)
      )");
    }

    foreach ($this->networks as $network_prefix) {
//      set_time_limit(30);
      if ($network_prefix == 'default') continue;  // leave this table untouched for mother network
      if (!$this->table_exists($network_prefix . '_user_popularity')) {
         $this->qup("2008-07-26, by: Zoran Hron - creating table $network_prefix" . "_user_popularity",
                "CREATE TABLE $network_prefix"."_user_popularity (
                `user_id` INT NOT NULL ,
                `popularity` INT NOT NULL ,
                `time` INT( 11 ) NOT NULL,
                PRIMARY KEY ( `user_id` )
               ) ");
      }
    }

    // adding Entity related tables
    $this->qup_all_networks("2008-07-31 martin: adding Entity table",
    	"CREATE TABLE {entities} (
         id INT NOT NULL AUTO_INCREMENT,
           PRIMARY KEY (id),
         entity_service VARCHAR(32) NOT NULL,
         entity_type VARCHAR(32) NOT NULL,
         entity_id VARCHAR(64) NOT NULL,
           UNIQUE KEY (entity_service, entity_type, entity_id),
         entity_name VARCHAR(255) NOT NULL
         )"
    );
    $this->qup_all_networks("2008-07-31 martin: adding EntityAttributes table",
    	"CREATE TABLE {entityattributes} (
         id INT NOT NULL,
         attribute_name VARCHAR(32) NOT NULL,
         attribute_value TEXT NOT NULL,
         attribute_permission INT NULL,
           KEY (id, attribute_name)
         )"
    );

    $this->qup_all_networks("2008-08-06 martin: adding EntityRelations table",
    "CREATE TABLE {entityrelations} (
         subject_service VARCHAR(32) NOT NULL,
         subject_type VARCHAR(32) NOT NULL,
         subject_id VARCHAR(64) NOT NULL,
         relation_type VARCHAR(32) NOT NULL,
         object_service VARCHAR(32) NOT NULL,
         object_type VARCHAR(32) NOT NULL,
         object_id VARCHAR(64) NOT NULL,
         start_time INT(11) NOT NULL,
         end_time INT(11) NULL,
         attributes TEXT,
           KEY (object_service, object_type, object_id, relation_type, end_time),
           KEY (subject_service, subject_type, subject_id, relation_type, end_time)
         )"
    );

    $this->qup_all_networks("2008-08-09 martin: adding EntityRelations auto increment ID",
    	"ALTER TABLE {entityrelations} ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST"
    );

    $this->qup_all_networks("2008-08-09 martin: dropping EntityRelations attributes",
    	"ALTER TABLE {entityrelations} DROP attributes"
    );
    $this->qup_all_networks("2008-08-09 martin: flushing EntityRelations",
    	"TRUNCATE TABLE {entityrelations}"
    );

    $this->qup_all_networks("2008-08-09 martin: adding EntityRelationAttributes table",
    	"CREATE TABLE {entityrelationattributes} (
         id INT NOT NULL,
         attribute_name VARCHAR(32) NOT NULL,
         attribute_value TEXT NOT NULL,
         attribute_permission INT NULL,
           KEY (id, attribute_name)
         )"
    );



    $this->qup_all_networks("2008-08-15 ZHron: `user_id` to `assoc_id` - changing structure of page_settings table",
      "ALTER TABLE {page_settings} CHANGE `user_id` `assoc_id` INT( 11 ) NOT NULL DEFAULT '0'");

    $this->qup_all_networks("2008-08-15 ZHron: `lft` to `assoc_type` - changing structure of page_settings table",
      "ALTER TABLE {page_settings} CHANGE `lft` `assoc_type` VARCHAR( 12 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'user'");

    $this->qup("2008-10-06 martin: disabling facebook for pre8 release",
      "UPDATE persona_services
      SET enabled = 0
      WHERE name = 'facebook'");


    // adding roles type info column
    if (!$this->column_exists('roles', 'type')) {
      $this->qup("2008-10-12, by: Zoran Hron - adding roles type info column",
                 "ALTER TABLE `roles` ADD `type` SET( 'network', 'group' ) NOT NULL DEFAULT 'network';"
                );
    }
    // adding primary key to tasks_roles table
    if (!$this->column_exists('tasks_roles', 'id')) {
      $this->qup("2008-10-08 ZHron: - adding primary key to tasks_roles table",
        "ALTER TABLE `tasks_roles` ADD `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;");
    }
    // -------------------------------------------------------------


    $new_core_tasks = array(
        array('id' => 12, 'name' => 'Post to Forum',         'description' => 'User can post to Forum',            'task_value' => 'post_to_forum'),
        array('id' => 13, 'name' => 'Send Messages',         'description' => 'User can send Private Messages',    'task_value' => 'send_messages'),
        array('id' => 14, 'name' => 'Post Blog Comments',    'description' => 'User can post Blog comments',       'task_value' => 'post_comments'),
        array('id' => 15, 'name' => 'Upload Images',         'description' => 'User can upload Images to Gallery', 'task_value' => 'upload_images'),
        array('id' => 16, 'name' => 'Upload Videos',         'description' => 'User can upload Videos to Gallery', 'task_value' => 'upload_videos'),
        array('id' => 17, 'name' => 'Manage Members',        'description' => 'User can manage members',           'task_value' => 'manage_members'),
        array('id' => 18, 'name' => 'Manage Media',          'description' => 'User can manage Media contents',    'task_value' => 'manage_media'),
        array('id' => 19, 'name' => 'Manage Announcements',  'description' => 'User can manage Announcements',     'task_value' => 'manage_announcements'),
        array('id' => 20, 'name' => 'Create Groups',         'description' => 'User can create Groups',            'task_value' => 'create_groups'),
        array('id' => 21, 'name' => 'Manage Groups',         'description' => 'User can manage Groups',            'task_value' => 'manage_groups'),
        array('id' => 22, 'name' => 'View Contents',         'description' => 'User can view contents',            'task_value' => 'view_content'),
        array('id' => 23, 'name' => 'Edit Contents',         'description' => 'User can edit contents',            'task_value' => 'edit_content'),
        array('id' => 24, 'name' => 'View Splash Pages',     'description' => 'User can view Splash pages',        'task_value' => 'view_splash_pages'),
        array('id' => 25, 'name' => 'Manage Roles',          'description' => 'User can manage User Roles',        'task_value' => 'manage_roles'),
        array('id' => 26, 'name' => 'Manage Textpads',       'description' => 'User can manage Textpads',          'task_value' => 'manage_textpads'),
        array('id' => 27, 'name' => 'Delete Network',        'description' => 'User can delete Network',           'task_value' => 'delete_network'),
        array('id' => 28, 'name' => 'Delete Group',          'description' => 'User can Delete Group',             'task_value' => 'delete_group'),
        array('id' => 29, 'name' => 'Create Networks',       'description' => 'User can create Networks',          'task_value' => 'create_networks'),
    );

    $new_login_user_tasks = array(
              12,      // 'Post to Forum'
              13,      // 'Send Messages'
              14,      // 'Post Blog Comments'
              15,      // 'Upload Images'
              16,      // 'Upload Videos'
              20,      // 'Create Groups'
              22,      // 'View Contents'
              24,      // 'View Splash Pages'
              29,      // 'Create Networks'
    );

    $new_anonym_user_tasks = array(
              22,      // 'View Contents'
              24,      // 'View Splash Pages'
    );

    // adding new core tasks
    foreach($new_core_tasks as $task) {
      $this->qup("2008-10-11, by: Zoran Hron; adding new core task - " . $task['name'],
                 "INSERT INTO `tasks` (id, name, description, task_value)
                  VALUES (".$task['id'].", '".$task['name']."', '".$task['description']."', '".$task['task_value']."'); "
                );
      $this->qup("2008-10-19, by: Zoran Hron; assign new core task - " . $task['name'] . "to AdminRole",
                              "INSERT INTO `tasks_roles` (role_id, task_id)
                               VALUES (1, ".$task['id']."); "
                             );
    }

    // adding new permissions to LoginUSer
    foreach($new_login_user_tasks as $tid) {
      $this->qup("2008-10-20, by: Zoran Hron; assign new core task ID - " . $tid . "to LoginUser Role",
                              "INSERT INTO `tasks_roles` (role_id, task_id)
                              VALUES (2, ".$tid."); "
                             );
    }

    // adding new permissions to Anonymous
    foreach($new_anonym_user_tasks as $aid) {
      $this->qup("2008-10-20, by: Zoran Hron; assign new core task ID - " . $aid . "to Anonymous Role",
                              "INSERT INTO `tasks_roles` (role_id, task_id)
                              VALUES (3, ".$aid."); "
                             );
    }


    // creating 'roles' and 'tasks_roles' tables for each network
    $res =  Dal::query("SELECT COUNT(*) AS count FROM `roles` WHERE 1");
    $res = $res->fetchRow(DB_FETCHMODE_ASSOC);
    $roles_count = $res['count'] +1;

    $res =  Dal::query("SELECT COUNT(*) AS count FROM `tasks_roles` WHERE 1");
    $res = $res->fetchRow(DB_FETCHMODE_ASSOC);
    $tasks_count = $res['count'] +1;

    foreach ($this->networks as $network_prefix) {
//      set_time_limit(30);
      if ($network_prefix == 'default') continue;  // leave this table untouched for mother network (already created)
      if (!$this->table_exists($network_prefix . '_roles')) {
         Dal::query("CREATE TABLE IF NOT EXISTS `$network_prefix"."_roles` SELECT * FROM `roles` WHERE 1 ");
         Dal::query("ALTER TABLE `$network_prefix"."_roles` ADD PRIMARY KEY(`id`)");
         Dal::query("ALTER TABLE `$network_prefix"."_roles` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT");
         Dal::query("ALTER TABLE `$network_prefix"."_roles` AUTO_increment = $roles_count" );
      }
      if (!$this->table_exists($network_prefix . '_tasks_roles')) {
         Dal::query("CREATE TABLE IF NOT EXISTS `$network_prefix"."_tasks_roles` SELECT * FROM `tasks_roles` WHERE 1 ");
         Dal::query("ALTER TABLE `$network_prefix"."_tasks_roles` ADD PRIMARY KEY(`id`)");
         Dal::query("ALTER TABLE `$network_prefix"."_tasks_roles` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT");
         Dal::query("ALTER TABLE `$network_prefix"."_tasks_roles` AUTO_increment = $tasks_count" );
         echo "Adding primary key for DB table $network_prefix"."_tasks_roles <br />";
      }
    }

    $this->qup_all_networks("2008-10-11, by: Zoran Hron - adding extra info column to users_roles table",
                            "ALTER TABLE {users_roles} ADD `extra` VARCHAR( 180 ) NOT NULL DEFAULT 'a:3:{s:4:\"user\";b:0;s:7:\"network\";b:1;s:6:\"groups\";a:0:{}}';"
                           );


    $task_renaming_list = array(
        'Meta Network'            => 'Manage Meta Networks',
        'Manage Settings'         => 'Manage Network Settings',
        'Notifications'           => 'Manage Notifications',
        'User defaults'           => 'Configure User defaults',
        'Themes'                  => 'Configure Themes',
        'Events'                  => 'Manage Events',
        'Post Content'            => 'Post to Blog',
        'Super Group Creation'    => 'Create Super Groups',
    );

    foreach($task_renaming_list as $old_name => $new_name) {
      $this->qup("2008-10-13, by: Zoran Hron; changing task name from `$old_name` to `$new_name`",
                 "UPDATE `tasks` SET `name` = '$new_name' WHERE `name` = '$old_name' ;"
                );
    }
    $this->qup_all_networks("2008-10-27, by: Zoran Hron - adding new role type to set of types",
                            "ALTER TABLE {roles} CHANGE `type` `type` SET( 'user', 'network', 'group' ) NOT NULL DEFAULT 'network'");

   $this->qup("2008-10-27, by: Zoran Hron; adding new core task - post_to_group",
              "INSERT INTO `tasks` (id, name, description, task_value)
              VALUES (NULL, 'Post to Group Blog', 'User can Post to Group Blog', 'post_to_group'); "
             );
   $this->qup("2008-10-27, by: Zoran Hron; adding new core task - post_to_user_blog",
              "INSERT INTO `tasks` (id, name, description, task_value)
               VALUES (NULL, 'Post to Personal Blog', 'User can Post to Personal Blog', 'post_to_user_blog'); "
             );

   $this->qup("2008-10-27, by: Zoran Hron; changing task name from `Post to Blog` to `Post to Community`",
                 "UPDATE `tasks` SET `name` = 'Post to Community' WHERE `task_value` = 'post_to_community' ;"
             );



   $new_login_user_tasks = array(
            31,      // 'Post to Personal Blog'
    );

   $new_admin_tasks = array(
            30,      // 'Post to Group Blog'
            31,      // 'Post to Personal Blog'
   );

   // adding new permissions to LoginUSer
   foreach($new_login_user_tasks as $tid) {
     $this->qup_all_networks("2008-10-27, by: Zoran Hron; assign new core task ID - " . $tid . "to LoginUser Role",
                             "INSERT INTO {tasks_roles} (role_id, task_id)
                             VALUES (2, ".$tid."); "
                            );
   }

   // adding new permissions to Admin
   foreach($new_admin_tasks as $aid) {
     $this->qup_all_networks("2008-10-27, by: Zoran Hron; assign new core task ID - " . $aid . "to Admin Role",
                             "INSERT INTO {tasks_roles} (role_id, task_id)
                             VALUES (1, ".$aid."); "
                            );
   }

   // adding new Group roles to the Core

  $new_group_roles = array(
          array('id' => 4, 'name' => 'Group Administrator', 'description' => 'User can manage Group settings, members and contents', 'read_only' => 1, 'type' => 'group', 'tasks' => array(6, 9, 10, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 24, 25, 26)),
          array('id' => 5, 'name' => 'Group Moderator', 'description' => 'The user has the same permissions as well as the Group Admin but can not assign Roles to other users', 'read_only' => 1, 'type' => 'group', 'tasks' => array(6, 9, 10, 12, 13, 14, 15, 16, 18, 19, 21, 22, 23, 24, 26)),
          array('id' => 6, 'name' => 'Group Member', 'description' => 'User can create Events and post to the Group, Forum and their Personal Blog', 'read_only' => 1, 'type' => 'group', 'tasks' => array(9, 10, 12, 13, 14, 22)),
  );
  foreach($new_group_roles as $role) {
     $this->qup_all_networks("2008-10-31, by: Zoran Hron - adding new Group role " . $role['id'],
                             "INSERT INTO {roles} (id, name, description, created, changed, read_only, type)
                              VALUES (".$role['id'].", '".$role['name']."', '".$role['description']."', ".time().", ".time().", ".$role['read_only'].", '".$role['type']."')
                              ON DUPLICATE KEY UPDATE name = '".$role['name']."', description = '".$role['description']."', read_only = ".$role['read_only'].", type = '".$role['type']."'"
                            );
     foreach($role['tasks'] as $task_id) {
       $this->qup_all_networks("2008-10-31, by: Zoran Hron - adding new tasks permissions to new role, role ID=".$role['id'].", task ID=" . $task_id,
                               "INSERT IGNORE INTO {tasks_roles} (`task_id`, `role_id`) VALUES (".$task_id.", ".$role['id'].");"
                              );
     }
  }


// BOF ----------------- fix tasks_role DB table ---------------------------------------------------------------------------------------

    $this->qup_all_networks("2008-11-05, by: Zoran Hron; truncate table tasks_roles",
                            "TRUNCATE TABLE {tasks_roles}; "
                           );

    // reassign all permissions for Admin role
   $all_admin_perms = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31);
   foreach($all_admin_perms as $task_id) {
      $this->qup_all_networks("2008-11-05, by: Zoran Hron; re-assign all admin permissions, permID: - " . $task_id,
                               "INSERT INTO {tasks_roles} (role_id, task_id)
                                VALUES (1, ".$task_id."); "
                             );
    }

    // reassign all permissions for LoginUser role
    $all_login_user_tasks = array(10, 12, 13, 14, 15, 16, 20, 22, 24, 29, 31);
    foreach($all_login_user_tasks as $task_id) {
      $this->qup_all_networks("2008-11-05, by: Zoran Hron; re-assign all LoginUser permissions, permID: - " . $task_id,
                               "INSERT INTO {tasks_roles} (role_id, task_id)
                                VALUES (2, ".$task_id."); "
                             );
    }

    // reassign all permissions for Anonymous role
    $all_anonym_user_tasks = array(22, 24);
    foreach($all_anonym_user_tasks as $task_id) {
      $this->qup_all_networks("2008-11-05, by: Zoran Hron; re-assign all Anonymous permissions, permID: - " . $task_id,
                               "INSERT INTO {tasks_roles} (role_id, task_id)
                                VALUES (3, ".$task_id."); "
                             );
    }

    // reassign all permissions for GroupAdmin role
    $all_group_admin_tasks = array(6, 9, 10, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 24, 25, 26, 30, 31);
    foreach($all_group_admin_tasks as $task_id) {
      $this->qup_all_networks("2008-11-05, by: Zoran Hron; re-assign all GroupAdmin permissions, permID: - " . $task_id,
                               "INSERT INTO {tasks_roles} (role_id, task_id)
                                VALUES (4, ".$task_id."); "
                             );
    }

    // reassign all permissions for Group moderator role
    $all_group_moderator_tasks = array(6, 9, 10, 12, 13, 14, 15, 16, 18, 19, 21, 22, 23, 24, 26, 30, 31);
    foreach($all_group_moderator_tasks as $task_id) {
      $this->qup_all_networks("2008-11-05, by: Zoran Hron; re-assign all Group moderator permissions, permID: - " . $task_id,
                               "INSERT INTO {tasks_roles} (role_id, task_id)
                                VALUES (5, ".$task_id."); "
                             );
    }

    // reassign all permissions for Group member role
    $all_group_member_tasks = array(9, 10, 12, 13, 14, 22, 30, 31);
    foreach($all_group_member_tasks as $task_id) {
      $this->qup_all_networks("2008-11-05, by: Zoran Hron; re-assign all Group member permissions, permID: - " . $task_id,
                               "INSERT INTO {tasks_roles} (role_id, task_id)
                                VALUES (6, ".$task_id."); "
                             );
    }
// EOF ----------------- fix tasks_role DB table ---------------------------------------------------------------------------------------

   $this->qup_all_networks("add the new content type for tekvideo", "INSERT INTO {content_types} VALUES (12, 'TekVideo', 'TekVideo')");
   $this->qup_all_networks("adding table media_video for tekmedia video integration", "CREATE TABLE IF NOT EXISTS {media_videos} (id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,video_id VARCHAR( 50 ) NOT NULL , content_id INT( 11 ) NOT NULL , author_id INT( 11 ) NOT NULL , category_id INT( 11 ) NULL , external_thumbnail TEXT, internal_thumbnail TEXT, email_id VARCHAR( 11 ) NOT NULL , views INT( 11 ) NULL , embed_tag TEXT NULL , view_url TEXT NULL , duration INT( 11 ) NULL , status INT( 5 ) NULL, video_perm INT( 5 ) NULL)");

   $this->qup_all_networks("2008-11-20, by: Zoran Hron; adding new activity type, insert default data for activity_types_17",
                           "INSERT INTO {activity_types} (`id`, `title`, `type`, `description`, `points`) VALUES
                           (17, 'Content modified', 'content_modified', 'This activity should not be ranked. It is used for activities log only.', 0)");
   $this->qup_all_networks("2008-11-20, by: Zoran Hron; adding new activity type, insert default data for activity_types_18",
                           "INSERT INTO {activity_types} (`id`, `title`, `type`, `description`, `points`) VALUES
                           (18, 'Network settings updated', 'network_settings_updated', 'This activity should not be ranked. It is used for activities log only.', 0)");
   $this->qup_all_networks("2008-11-20, by: Zoran Hron; adding new activity type, insert default data for activity_types_19",
                           "INSERT INTO {activity_types} (`id`, `title`, `type`, `description`, `points`) VALUES
                           (19, 'Group settings updated', 'group_settings_updated', 'This activity should not be ranked. It is used for activities log only.', 0)");


// ----------------- adding user_contact DB table ---------------------------------------------------------------------------------------
   $this->qup_all_networks("2008-11-30, by: Zoran Hron; add the new DB table: user_contact ",
                           "CREATE TABLE IF NOT EXISTS {user_contact} (
                              `id` int(11) NOT NULL,
                              `user_id` int(11) NOT NULL,
                              `contact_name` varchar(128) NOT NULL,
                              `contact_email` varchar(128) NOT NULL,
                              `contact_extra` text NOT NULL,
                              `contact_type` varchar(64) NOT NULL,
                             PRIMARY KEY  (`id`),
                             KEY `user_id` (`user_id`) )"
                          );

   $this->qup_all_networks("2008-12-02, by: Zoran Hron; adding autoincrement attribute to user_contact.id field",
                           "ALTER TABLE {user_contact} CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT"
                          );

   $this->qup("2009-01-25, by: Zoran Hron - adding modified_by column to pa_forum_thread table",
              "ALTER TABLE {pa_forum_thread} ADD `modified_by` VARCHAR( 32 ) NULL;"
             );
   $this->qup("2009-01-25, by: Zoran Hron - adding modified_by column to pa_forum_post table",
              "ALTER TABLE {pa_forum_post} ADD `modified_by` VARCHAR( 32 ) NULL;"
             );


   $this->qup_all_networks("2009-01-28, by: Zoran Hron; adding more space for roles extra info",
              "ALTER TABLE `users_roles` CHANGE `extra` `extra` VARCHAR( 8048 ) NOT NULL DEFAULT 'a:3:{s:4:\"user\";b:0;s:7:\"network\";b:1;s:6:\"groups\";a:0:{}}';" );

    $this->qup_all_networks("2009-03-24, by: Martin - adding grou_id column to advertisements table",
              "ALTER TABLE {advertisements} ADD `group_id` INT( 11 ) NULL;"
             );


    $ts = time();
    $this->qup("2009-03-27, by: Martin - adding Ad Manager Roles",
              "INSERT INTO roles (id, name, description, created, changed, read_only, type)
              VALUES (7, 'Ad Manager', 'User can manage Network Ads and Group specific Ads', $ts, $ts, 1, 'network')"
             );

    $this->qup("2009-03-27, by: Martin - adding Group Ad Manager Roles",
              "INSERT INTO roles (id, name, description, created, changed, read_only, type)
              VALUES (8, 'Group Ad Manager', 'Group Member can manage Ads for this specific Group', $ts, $ts, 1, 'group')"
             );


    $this->qup_all_networks("2009-04-16, by: Martin - setting display_on correctly to NULL for old Media",
              "UPDATE {contents} SET display_on=NULL WHERE type IN (4,5,6,12);"
             );

    $this->qup("2009-04-17, by: Martin - adding upload images and videos to Group Members",
    array(
    "INSERT INTO `tasks_roles` (role_id, task_id) VALUES (6, 15)",
    "INSERT INTO `tasks_roles` (role_id, task_id) VALUES (6, 16)"
    ));

    $this->qup("2009-04-17, by: Martin - giving Ad Managers the right permissions",
    array(
    "INSERT INTO `tasks_roles` (role_id, task_id) VALUES (7, 3)",
    "INSERT INTO `tasks_roles` (role_id, task_id) VALUES (8, 3)"
    ));
    
    $this->qup_all_networks("2009-07-11, by: Martin - adding performance critical INDEX to groups_users",
              "ALTER TABLE {groups_users} ADD INDEX user_type_index (group_id, user_id, user_type)");

    $this->qup_all_networks("2009-06-25, by: Martin - altering groups table to make typedgroup group_type possible", "ALTER TABLE {groups} CHANGE group_type group_type VARCHAR( 64 ) NOT NULL DEFAULT 'regular'");

    $this->qup_all_networks("2009-06-26, by: Martin -setting all TypedGroups to the right group_type also", "UPDATE {groups} AS G LEFT JOIN {entities} AS E ON E.entity_id = G.group_id SET G.group_type='typedgroup' WHERE E.entity_service='typedgroup'");


    // finally, run the 'safe' updates in net_extra.php.
    run_net_extra();

  }//__endof__ do_updates

  function write($s, $newline=TRUE)
  {
    echo "$s" . ($newline ? "\n" : "");
    flush();
  }

  function note($msg)
  {
    $this->li("* $msg *");
  }

  function li($msg)
  {
    if (!$this->quiet) {
      if ($this->running_on_cli) {
  $this->write("* $msg");
      } else {
  $this->write("<li>$msg</li>");
      }
    }
  }

  function query($sql)
  {
    $this->li($sql);
    Dal::query($sql);
  }

  function is_applied($key, $network=NULL)
  {
    if (!$network) $network = '';
    $r = Dal::query_one("SELECT * FROM mc_db_status WHERE stmt_key=? AND network=?", Array($key, $network));
    return $r ? TRUE : FALSE;
  }

  // call qup for the main network plus all others
  function qup_all_networks($k, $sql_stmts) {
    if (!is_array($sql_stmts)) $sql_stmts = array($sql_stmts);
    if (!$this->quiet) { $this->note("Applying '$k' update to all networks ..."); }
    foreach ($sql_stmts as $sql) {
      if (!is_callable($sql) && (strpos($sql, "{") === FALSE)) {
        die("ERROR: SQL '$sql' is to be applied to all networks, but contains no {bracketed} table names!");
      }
    }
    //    $sth = Dal::query("SELECT address FROM networks WHERE is_active=1");
    $seen_default = FALSE;
    //    while (list($network_address) = Dal::row($sth)) {
    //    var_dump($this->networks);
    global $network_prefix;
    $prev_network_prefix = $network_prefix;
    $nets_done = $nets_updated = 0; $nets_total = count($this->networks);
    $last_prefix = ""; // to work out spacing
    foreach ($this->networks as $network_prefix) {
//      set_time_limit(30);
      ++ $nets_done;
      if ($network_prefix == 'default') {
  $network_prefix = ''; // default network has network=='' in mc_db_status table
  $seen_default = TRUE;
      }

      if ($this->is_applied($k, $network_prefix)) continue;
      if (!$this->quiet) {
  if ($this->running_on_cli) {
    echo "\r";
    $len_diff = strlen($last_prefix) - strlen($network_prefix);
    $spacing = ($len_diff > 0) ? str_repeat(" ", $len_diff) : "";
  } else {
    $spacing = "";
  }
  $this->write("* $nets_done/$nets_total [$network_prefix]$spacing", FALSE);
      }
      foreach ($sql_stmts as $sql) {
        if (is_callable($sql)) {
          $sql();
        } else {
          $new_sql = Dal::validate_sql($sql, $network_prefix);
          Dal::query($new_sql);
        }
      }
      Dal::query("INSERT INTO mc_db_status SET stmt_key=?, network=?", Array($k, $network_prefix));
      ++ $nets_updated;
      $last_prefix = $network_prefix;
    }
    $network_prefix = $prev_network_prefix;
    if (!$seen_default && $this->full_update) {
      $this->write("WARNING: applied change '$k' to all known networks, but the default network doesn't have an entry in the 'networks' table.  This means net_extra.php hasn't been run; something has gone wrong with the upgrade.");
    }
    if (!$this->quiet && $this->running_on_cli && $nets_updated) echo "\n";
  }

  function qup($k, $sql_or_func_array)
  {
//    set_time_limit(30);
    if (!$this->full_update) {
      // we're only updating a single network - so don't do global changes
      return;
    }

    if ($this->is_applied($k, NULL))
    {
      //      $this->note("$k already applied");
      return;
    }

    if (!is_array($sql_or_func_array)) $sql_or_func_array = array($sql_or_func_array);

    foreach ($sql_or_func_array as $sql_or_func) {
      if (is_callable($sql_or_func)) {
  $sql_or_func();
      }
      else {
  if (!$this->quiet) {
    $this->note("applying patch $k" . ($this->running_on_cli ? (" (<a href=\"db_update.php?override=".htmlspecialchars($k)."\">override</a>)") : ""));
  }
  $this->query($sql_or_func);
      }
    }

    Dal::query("INSERT INTO mc_db_status SET stmt_key=?", Array($k));
  }

  function dump_schema()
  {
    echo '<table border="1">';
    $th = Dal::query("SHOW TABLES");
    while ($tr = Dal::row($th))
    {
      list($tname) = $tr;
      echo "<tr><td><b>TABLE: $tname</b></td></tr>";
      $ch = Dal::query("DESCRIBE $tname");
      while ($cr = Dal::row_assoc($ch))
      {
        echo '<tr>';
        foreach ($cr as $k => $v)
          echo "<td>$v</td>";
        echo '</tr>';
      }
    }
    echo '</table>';
  }

  function main()
  {
    $this->db = Dal::get_connection();

    $this->note("Doing database update");

    // We use $this->db->getOne() below instead of Dal::query_one() as
    // the first time this script is run, the mc_db_status table will
    // not exist, which will fire an exception with Dal::query_one()
    // and break the installation.  Please don't change this to
    // Dal::query_one()!  -PP 2006-11-15
    $db_status = $this->db->getOne("SELECT * FROM mc_db_status LIMIT 1");

    if (!DB::isError($db_status))
    {
      $this->note("mc_db_status table in place");
    }
    else
    {
      $this->note("Creating mc_db_status table");
      $this->query("CREATE TABLE mc_db_status (stmt_key VARCHAR(255) NOT NULL, PRIMARY KEY(stmt_key))");
    }

    // add network column
    if (!$this->column_exists("mc_db_status", "network")) {
      $this->query("ALTER TABLE mc_db_status ADD COLUMN network VARCHAR(50) NOT NULL DEFAULT ''");
      $this->query("ALTER TABLE mc_db_status DROP PRIMARY KEY");
      $this->query("ALTER TABLE mc_db_status ADD PRIMARY KEY(stmt_key, network)");
    }

    /* 'broken' col disabled for now - use $this->broken_networks instead.
    // make sure the network table has the 'broken' column before we get started
        if (!$this->column_exists("networks", "broken")) {
      Dal::query("ALTER TABLE networks ADD COLUMN broken BOOLEAN DEFAULT '0'");
    }*/

    // find networks which have their tables (i.e. skip over broken networks)
    $this->networks = DbUpdate::get_valid_networks();

    $override = @$_GET['override'];
    if (!empty($override))
    {
      try {
  Dal::query("INSERT INTO mc_db_status SET stmt_key=?", Array($override));
      } catch (PAException $e) {
  echo "<p>exception trying to override: ".$e->getMessage()."</p>";
      }
    }

    $this->do_updates();

    if (!$this->quiet) {
      //        $this->dump_schema();
      $this->note("CORE db updates done.");
    }
  }

  /* Update a single network.  This is used during network creation,
   to ensure the network schema is up to date. */
  function update_single_network($network) {
    // make sure the network exists
    if (!DbUpdate::is_network_valid($network)) {
      throw new PAException(NETWORK_NOT_FOUND, "Cannot update $network as it does not exist in the database");
    }

    // we shouldn't need to care about this, but just in case, keep copies of updated vars
    $stored_full_update = $this->full_update;
    $this->full_update = FALSE;
    $stored_quiet = $this->quiet;
    $this->quiet = TRUE;

    // update just the specified network
    $this->networks = array($network);
    $this->do_updates();

    // pop old values
    $this->full_update = $stored_full_update;
    $this->quiet = $stored_quiet;
  }

  function table_exists($tablename){
    //$sql = "DESCRIBE $tablename";
    $sql = "SHOW TABLES LIKE '".Dal::quote($tablename)."'";
    $res = Dal::query($sql);
    while(list($tname) = Dal::row($res)) {
     if ($tname == $tablename) {
        return TRUE;
      }
    }
    return FALSE;

  }
  function column_exists($tablename,$column_name) {
/*
    $sql = "SHOW COLUMNS FROM $tablename LIKE '%$column_name%'";
*/
    $sql = "SHOW COLUMNS FROM $tablename WHERE Field='$column_name'";
    $res = Dal::query($sql);
    if ($res->numrows() > 0) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  function rebuild_role_tables() {
    $k = "2008-01-26 Zoran: Role Managment reorganization";
    if (!$this->quiet) {
      $this->note("applying patch $k" . ($this->running_on_cli ? (" (<a href=\"db_update.php?override=".htmlspecialchars($k)."\">override</a>)") : ""));
    }

    if ($this->is_applied($k, NULL))
    {
      //      $this->note("$k already applied");
      return;
    }

    $users = array();
    $sql = 'SELECT user_id FROM {users} WHERE 1 ';
    $res = Dal::query($sql);
    while ( $row = $res->fetchRow(DB_FETCHMODE_ASSOC) ) {
      $users[] = $row;
    }

    $admin_roles = array();
    if ($this->table_exists('admin_roles')) {
      $sql = 'SELECT * FROM {admin_roles} WHERE 1 ';
      if($res = Dal::query($sql)) {
        while ( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
          $admin_roles[] = $row;
        }
      }
    }

    $users_roles = array();
    $sql = 'SELECT * FROM {users_roles} WHERE 1 ';
    if($res = Dal::query($sql)) {
      while ( $row = $res->fetchRow(DB_FETCHMODE_ASSOC) ) {
        $users_roles[] = $row;
      }
    }

    if ($this->table_exists('users_adminroles')) {
      $users_adminroles = array();
      $sql = 'SELECT * FROM {users_adminroles} WHERE 1 ';
      if($res = Dal::query($sql)) {
        while ( $row = $res->fetchRow(DB_FETCHMODE_ASSOC) ) {
          $users_adminroles[] = $row;
        }
      }
    }

    $tasks_roles = array();
    $sql = 'SELECT * FROM {tasks_roles} WHERE 1 ';
    if($res = Dal::query($sql)) {
      while ( $row = $res->fetchRow(DB_FETCHMODE_ASSOC) ) {
        $tasks_roles[] = $row;
      }
    }

    Dal::query("DROP TABLE IF EXISTS `roles`");                   // roles was not used anyway !!!
    $sql = "CREATE TABLE `roles` (
                         `id` int(11) NOT NULL auto_increment,
                          `name` varchar(100) default NULL,
                          `description` tinytext,
                          `created` int(11) default NULL,
                          `changed` int(11) default NULL,
                          `read_only` tinyint(1) NOT NULL default '0',
                          PRIMARY KEY  (`id`)
                        )";
    Dal::query($sql);

    // insert SYSTEM roles

    $sql = "INSERT INTO {roles} (name, description, created, changed, read_only) VALUES (?, ?, ?, ?, ?)";
    Dal::query($sql, array('Administrator', 'Administrator permissions', time(), time(), 1));
    $admin_role_id = Dal::insert_id();

    $sql = "INSERT INTO {roles} (name, description, created, changed, read_only) VALUES (?, ?, ?, ?, ?)";
    Dal::query($sql, array('Login User', 'Ordinary logged in users permissions', time(), time(), 1));
    $loginUser_role_id = Dal::insert_id();

    $sql = "INSERT INTO {roles} (name, description, created, changed, read_only) VALUES (?, ?, ?, ?, ?)";
    Dal::query($sql, array('Anonymous', 'Anonymous users permissions', time(), time(), 1));
    $anonim_role_id = Dal::insert_id();


    $roles = array($admin_role_id     => 'Administrator',
                   $loginUser_role_id => 'Login User',
                   $anonim_role_id    => 'Anonymous'
             );

    $new_roles_map = $this->normalizeRoles($roles, $admin_roles);


    // create relation table `users_roles`
    Dal::query("DROP TABLE IF EXISTS `users_roles`");
    $sql = "CREATE TABLE `users_roles` (
                         `user_id` int(11) NOT NULL,
                         `role_id` int(2) NOT NULL default '-1',
                         KEY `user_id` (`user_id`)
                      )";
    Dal::query($sql);

    // assign default roles to users
    foreach($users as $user) {
       $sql = "INSERT INTO {users_roles} (user_id, role_id) VALUES (?, ?)";
       Dal::query($sql, array($user['user_id'], $loginUser_role_id));

       // if user_id == 1 user is owner: assign admin role
       if($user['user_id'] == 1) {
         $sql = "INSERT INTO {users_roles} (user_id, role_id) VALUES (?, ?)";
         Dal::query($sql, array($user['user_id'], $admin_role_id));
       }
    }

    if ($this->table_exists('users_adminroles')) {
      // restore roles from old "users_adminroles" data table
      foreach($users_adminroles as $a_role) {
          $new_role_id = $new_roles_map[$a_role['role_id']];
          $sql = "INSERT INTO {users_roles} (user_id, role_id) VALUES (?, ?)";
          Dal::query($sql, array($a_role['user_id'], $new_role_id));
      }
    }

    // restore roles from old "users_roles" data table
    foreach($users_roles as $a_role) {
        $new_role_id = $new_roles_map[$a_role['role_id']];
        $sql = "INSERT INTO {users_roles} (user_id, role_id) VALUES (?, ?)";
        Dal::query($sql, array($a_role['user_id'], $new_role_id));
    }


    // rebuild `tasks_roles` data table
    Dal::query("DROP TABLE IF EXISTS `tasks_roles`");
    $sql = "CREATE TABLE `tasks_roles` (
                         `task_id` int(11) NOT NULL,
                         `role_id` int(11) NOT NULL
                      )";
    Dal::query($sql);


    foreach($tasks_roles as $t_role) {
       $new_role_id = $new_roles_map[$t_role['role_id']];
       $sql = "INSERT INTO {tasks_roles} (task_id, role_id) VALUES (?, ?)";
       Dal::query($sql, array($t_role['task_id'], $new_role_id));
    }

    $new_tasks = array (
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (8, 1)",    // begin: Admin tasks
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (10, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (1, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (2, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (3, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (4, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (5, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (6, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (7, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (9, 1)",
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (11, 1)",   // end: Admin tasks
                         "INSERT INTO `tasks_roles` (`task_id`, `role_id`) VALUES (10, 2)"    // Login User tasks
                       );
    foreach($new_tasks as $sql) {
         Dal::query($sql);
    }

    // delete old table `admin_roles`
    Dal::query("DROP TABLE IF EXISTS `admin_roles`");

    // delete old table `users_adminroles`
    Dal::query("DROP TABLE IF EXISTS `users_adminroles`");

    Dal::query("INSERT INTO mc_db_status SET stmt_key=?", Array($k));

  }

  private function normalizeRoles($roles, $old_roles) {
    $old_roles_map = array();

    foreach($old_roles as $o_role) {                        // check is old role any of existing system roles
      if($new_id = array_search($o_role->name, $roles)) {
         $old_roles_map[$o_role->id] = $new_id;
      }
    }

    foreach($old_roles as $o_role) {
      $k = array_search($o_role->name, $roles);
      if( !$k ) {
        if($o_role->id != 0)  {  // skip old defined Login User Role
          $sql = "INSERT INTO {roles} (name, description, created, changed, read_only) VALUES (?, ?, ?, ?, ?)";
          $res = Dal::query($sql, array($o_role->name, $o_role->description, $o_role->created, $o_role->changed, 0));
          $old_roles_map[$o_role->id] = Dal::insert_id();  // new re-mapped role ID number
        }
      }
    }
    return $old_roles_map;
  }

}
?>
