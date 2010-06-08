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

// The PA class stores certain globals - critical things which affect every page.
// Less 'global' configuration items should be member variables of PA::$config (see below).
class PA {
  public static $config;
  public static $facebook_request_string = "Hi Facebook friend - come join this PeopleAggregator network!";
// Example: in ROTC default_config.php :
// PA::$facebook_request_string = __("Come check out ROTCLink - where cadets and alumni stay connected!");

  // TODO: add Currencies, number formats and other i18N data, Z.Hron 2008-12-01
  public static $culture_data = array();

  // Core root directory
  public static $core_dir;

  // Project root directory
  public static $project_dir;

  // relative path to the Config directory
  public static $config_path;

  // Site name
  public static $site_name;

  // Default email sender
  public static $default_sender;

  // UI language
  public static $language;

  // Server ID (for file replication)
  public static $server_id;

  // Naming convention:
  // - $something_{path|url}: absolute path (e.g. /var/www/...../homepage.php) or URL (e.g. http://example.com/foobar/web/homepage.php)
  // - $something_local_url: URL relative to web root (e.g. /foobar/web/homepage.php)
  // - $something_rel_url: URL relative to web folder (e.g. homepage.php)
  public static $path; // absolute path to the root of the site files (web/..); same as $path_prefix
  public static $url; // absolute url of the web subdirectory;
  public static $local_url; // relative url of the web subdirectory; same as BASE_URL_REL (relative to root of url)
  public static $upload_path; // absolute path to web/files directory; same as $uploaddir - DEPRECATED; use Storage functions instead!
  public static $theme_url; // absolute url to web/Themes/Default directory; same as $current_theme_path
  public static $theme_rel; // relative url to web/Themes/Default directory; same as $current_theme_rel_path (relative to web directory)
  public static $theme_path; // path to web/Themes/XYZ directory
  public static $blockmodule_path; // absolute path to web/BlockModules directory
  public static $domain_suffix = FALSE; // copy of $domain_suffix from local_config.php
  public static $network_capable = FALSE; // TRUE if we are capable of running multiple networks, i.e. wildcard DNS is configured

  // current network
  public static $network_info = NULL; // Network object for current network

  // logged-in user
  public static $login_uid = NULL; // uid of logged-in users
  public static $login_user = NULL; // User object for logged-in user
  // user specified on the url
  public static $page_uid = NULL; // uid specified in 'uid' (user_id) or 'user' (login_name) on the query string (if valid)
  public static $page_user = NULL; // User object corresponding to $page_uid
  // user specified on the url, or logged-in user if no user specified
  public static $uid = NULL;
  public static $user = NULL;

  public static $group_noun = 'Group';
  public static $group_noun_plural = 'Groups';
  public static $group_cc_type = 1; // GROUP_COLLECTION_TYPE

  public static $people_noun = 'People';
  public static $mypage_noun = 'Me';

  // user data settings
  // moving these from constants to PA:: vars
  public static $password_min_length = 5;
  public static $password_max_length = 15;

  // where to send user after login
  public static $after_login_page = '/';


  public static function logged_in() { return !empty(PA::$login_uid); }

  // the IP of the remote user, or the last proxy in the chain used to get to us
  public static $remote_ip = NULL;
  // all nonlocal IPs in the chain: array(PA::$remote_ip, next_nearest_proxy_ip, ..., final_possible_client).
  // (note that we can only trust the first one -- this is just for forensics)
  public static $remote_ip_with_proxies = NULL;

  //this static variable will have the unserialized value of network_info
  public static $extra;

  //tekmedia keys
  public static $video_accesskey;
  public static $video_secretkey;
  public static $tekmedia_server;
  public static $tekmedia_site_url;
  public static $tekmedia_iframe_form_path;

  // set true to allow UrlHelper to generate https URLs
  public static $ssl_security_on;

  // set true to force the https url scheme for whole PA site (all urls and links will be converted)
  public static $ssl_force_https_urls;

  // contains network default settings
  public static $network_defaults;

  // global PA profiler object
  public static $profiler = null;

  //Static method which will check whether the content moderation is on for the current network
  //TRUE -> If content moderation is on, FALSE otherwise
  public static function is_moderated_content() {
    return (!empty(PA::$extra['network_content_moderation'])) ? TRUE : FALSE;
  }

  //-- i18N helper functions -----------------------------------------------------------------------BOF
  public static function date($date, $format = 'long') {
   if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['date_time'])) {
      $date_format = self::$culture_data[self::$language]['date_time'][$format]['date'];
    } else {
      $date_format = self::$culture_data['default']['date_time'][$format]['date'];  // en_US is default culture
    }
    return date($date_format, (is_numeric($date)) ? $date : strtotime($date));
  }

  public static function time($time, $format = 'long') {
    if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['date_time'])) {
      $date_format = self::$culture_data[self::$language]['date_time'][$format]['time'];
    } else {
      $date_format = self::$culture_data['default']['date_time'][$format]['time'];  // en_US is default culture
    }
    return date($date_format, (is_numeric($time)) ? $time : strtotime($time));
  }

  public static function datetime($date, $date_format = 'long', $time_format = 'long') {
    if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['date_time'])) {
      $date_format = self::$culture_data[self::$language]['date_time'][$date_format]['date'];
      $date_format .= ' ' . self::$culture_data[self::$language]['date_time'][$time_format]['time'];
    } else {
      $date_format = self::$culture_data['default']['date_time'][$date_format]['date'];  // en_US is default culture
      $date_format .= ' ' . self::$culture_data['default']['date_time'][$time_format]['time'];
    }
    return date($date_format, (is_numeric($date)) ? $date : strtotime($date));
  }

  public static function getCountryList($language = null) {

    if(!empty($language) && !empty(self::$culture_data[$language]['countries'])) {
       $country_list = self::$culture_data[$language]['countries'];
    } else if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['countries'])) {
       $country_list = self::$culture_data[self::$language]['countries'];
    } else {
       $country_list = self::$culture_data['default']['countries'];  // en_US is default culture
    }
    return $country_list;
  }

  public static function getStatesList($language = null) {
    if(!empty($language) && !empty(self::$culture_data[$language]['states'])) {
       $states_list = self::$culture_data[$language]['states'];
    } else if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['states'])) {
       $states_list = self::$culture_data[self::$language]['states'];
    } else {
       $states_list = self::$culture_data['default']['states'];  // en_US is default culture
    }
    return $states_list;
  }

  public static function getMonthsList($language = null) {
    if(!empty($language) && !empty(self::$culture_data[$language]['months'])) {
       $months_list = self::$culture_data[$language]['months'];
    } else if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['months'])) {
       $months_list = self::$culture_data[self::$language]['months'];
    } else {
       $months_list = self::$culture_data['default']['months'];  // en_US is default culture
    }
    return $months_list;
  }

  public static function getDaysList($language = null) {
    if(!empty($language) && !empty(self::$culture_data[$language]['days'])) {
       $days_list = self::$culture_data[$language]['days'];
    } else if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['days'])) {
       $days_list = self::$culture_data[self::$language]['days'];
    } else {
       $days_list = self::$culture_data['default']['days'];  // en_US is default culture
    }
    return $days_list;
  }

  public static function getDaysInMonth($month, $year) {
    return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year %400 ? 28 : 29)))
                       : (($month - 1) % 7 % 2 ? 30 : 31);
  }

  public static function getYearsList() {
    $years = array();
    $lastyear = date("Y", time());
    $firstyear = $lastyear-76;
    for($i=1, $j=$firstyear; $i<=76 && $j<=$lastyear; $i++, $j++) {
      $years[$i] = $j;
    }
    return $years;
  }
  //-- i18N helper functions ------------------------------------------------------------------------EOF

  public static function resolveRelativePath($path) {
    if($path{0} == DIRECTORY_SEPARATOR) {
      $path = substr( $path, 1);                      // remove leading '/'
    }
    if(file_exists(self::$project_dir . DIRECTORY_SEPARATOR . $path)) {
      return (self::$project_dir . DIRECTORY_SEPARATOR . $path);
    } else if(file_exists(self::$core_dir . DIRECTORY_SEPARATOR . $path)) {
      return (self::$core_dir . DIRECTORY_SEPARATOR . $path);
    }
    return false;
  }


}
?>
