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
include_once dirname(__FILE__)."/../../config.inc";
require_once "api/DB/Dal/Dal.php";
require_once "api/User/User.php";
require_once "api/PAException/PAException.php";
require_once "web/includes/classes/DynamicPage.class.php";
require_once "api/Cache/FileCache.php";
/**
* Class ModuleSetting represents the setting of the modules.
*
* @package Setting
* @author Tekriti Software
*/
class ModuleSetting {
  /**
  * The default constructor for setting class.
  */
  public function __construct() {
    return;
  }

  /**
  * Adds the given setting for the given user id.
  *
  * @param integer $assoc_id The id of the association entity for whom settings are to be added.
  * @param integer $array_of_data The array of settings data to be added like array([0]=>array([module_id]=>, [orientation]=>, [priority]=> ) [1]=>array([module_id]=>, [orientation]=>, [priority]=> ))
  */
  static function save_setting($assoc_id, $page_id, $array_of_data, $assoc_type = "user") {
    Logger::log("Enter: function ModuleSetting::save_setting");

    // get the prior settings
    // (passed settings might be only left or right or middle)
    $settings = ModuleSetting::load_setting($page_id, $assoc_id, $assoc_type);

    // now merge the two
    foreach ($array_of_data as $sec=>$mods) {
      $settings[$sec] = $mods;
    }

    $settings_data = serialize($settings);

    $sql = "SELECT * FROM {page_settings} WHERE assoc_id=? AND page_id=? AND assoc_type =?";
    $data = array($assoc_id, $page_id, $assoc_type);
    $res = Dal::query($sql, $data);

    if ($res->numRows() > 0) {
      $sql = "DELETE FROM {page_settings} WHERE assoc_id=? AND page_id=? AND assoc_type =?";
      $data = array($assoc_id, $page_id, $assoc_type);
      $res = Dal::query($sql, $data);

      $sql = "INSERT INTO {page_settings} (assoc_id, page_id, settings, assoc_type) VALUES (?, ?, ?, ?)";
      $data = array($assoc_id, $page_id, $settings_data, $assoc_type);
      Dal::query($sql, $data);
    }
    else {
      $sql = "INSERT INTO {page_settings} (assoc_id, page_id, settings, assoc_type) VALUES (?, ?, ?, ?)";
      $data = array($assoc_id, $page_id, $settings_data, $assoc_type);
      Dal::query($sql, $data);
    }

    Logger::log("Exit: function ModuleSetting::save_setting");
    return;
  }

  /**
  * Loads the given setting for the given user id.
  *
  * @param integer $type The type of the entity for whom settings are to be loaded.
  * @param integer $assoc_id The id of the association entity for whom settings are to be added.
  * @return $value string This string contains the object of given settings.
  */
  static function load_setting($page_id, $assoc_id, $assoc_type = "network", $child_type = null, $only_configurable = false ) {
    Logger::log("Enter: function ModuleSetting::load_setting");
    $settings = null;
    $sql = "SELECT page_id, settings FROM {page_settings} WHERE assoc_id=? AND page_id=? AND assoc_type =?";
    $data = array($assoc_id, $page_id, $assoc_type);
    $res = Dal::query($sql, $data);

    $dynamic_page = new DynamicPage($page_id);
    if(!is_object($dynamic_page) or !$dynamic_page->docLoaded) {
      throw new Exception("Page XML config file for page ID: $page_id - not found!");
    }
    $dynamic_page->initialize();
    $page_settings = $xml_settings = $dynamic_page->getPageSettings();

    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $settings = unserialize($row->settings);
      foreach($settings as $key => $value) {           // merge DB and XML settings
        $page_settings[$key] = $value;
      }
      if(!is_null($child_type)) { 
        if(false !== strpos($dynamic_page->page_type, $child_type)) {
          $settings = $page_settings;
        } else {
          $settings = null;
        }
      } else  {
        $settings = $page_settings;
      }
    } else if(($assoc_type == 'user') || ($assoc_type == 'group')) { // try to get default settings for current network
      $settings = self::load_setting($page_id, PA::$network_info->network_id, "network", $assoc_type, $only_configurable);
    } else {
      if($only_configurable == false) {
        $settings = $dynamic_page->getPageSettings();
      } else if(($only_configurable == true) && $dynamic_page->is_configurable) {
        if(!is_null($child_type)) {
          if(false !== strpos($dynamic_page->page_type, $child_type)) {
            $settings = $dynamic_page->getPageSettings();
          } else {
            $settings = null;
          }
        } else  {
          $settings = $dynamic_page->getPageSettings();
        }
      }
    }
    // Fix: always return navigation_code and boot_code from XML file
    $settings['navigation_code'] = $xml_settings['navigation_code'];
    $settings['boot_code'] = $xml_settings['boot_code'];

    Logger::log("Exit: function ModuleSetting::load_setting");
    return $settings;
  }
  /**
  * Loads the given setting for the given user id.
  *
  * @param integer $type The type of the user for whom settings are to be loaded.
  * @param integer $assoc_id The uid of the user for whom settings are to be loaded.
  * @return $value string This string contains the object of given settings.
  */
  static function load_default_setting($page_id) {
    Logger::log("Enter: function ModuleSetting::load_default_setting");
    $dynamic_page = new DynamicPage($page_id);
    if(!is_object($dynamic_page) or !$dynamic_page->docLoaded) {
      throw new Exception("Page XML config file for page ID: $page_id - not found!");
    }
    $dynamic_page->initialize();
    $settings = $dynamic_page->getAllModules();
    Logger::log("Exit: function ModuleSetting::load_default_setting");
    return $settings;
  }

   /**
  * Updates the given setting for the given page id.
  *
  * @param integer $page_id.
  * @param integer $array_of_data The array of settings data to be added like array([0]=>array([module_id]=>, [orientation]=>, [priority]=> ) [1]=>array([module_id]=>, [orientation]=>, [priority]=> ))
  * it is like array([0]=>array(page_id =>
                                page_name =>
                                data =>array(left=>array(
                                                          left_module1
                                                          left_module2
                                                         )
                                            middle=>array(
                                                          middle_module1
                                                          middle_module2
                                                         )
                                            right=>array(
                                                          right_module1
                                                          right_module2
                                                         )
                                            )
                                )
                     )
  */
  static function save_page_setting($page_id, $array_of_data) {

    Logger::log('Enter: function ModuleSetting::save_page_setting');

/*
    $dynamic_page = new DynamicPage($page_id);
    $dynamic_page->initialize();
    $page_settings = $dynamic_page->getPageSettings();
    if(!is_object($dynamic_page) or !$dynamic_page->docLoaded) {
      throw new Exception("Page XML config file for page ID: $key - not found!");
    }
*/
/*
    $sql = 'SELECT * FROM {page_default_settings} WHERE page_id = ?';
    $data = array($page_id);
    $res = Dal::query($sql, $data);
    if ($res->numRows() > 0) {
      $sql = 'UPDATE {page_default_settings} SET default_settings = ? WHERE page_id = ?';
      $data = array($array_of_data, $page_id);
      Dal::query($sql, $data);
    }
*/
    Logger::log('Exit: function ModuleSetting::save_page_setting');
    return;
  }

  /**
  * Generic Function to get record from page settings table
  * Function will take the field_name and value in the form of key => value pair
  */
  public static function get_pages_default_setting($page_type, $only_configurable = true, $from_XML = false) {
    global $app;

    Logger::log('Enter: function ModuleSetting::get_pages_default_setting');
    if(FileCache::is_cached($page_type .'_module_default_setting')) {
      $settings = FileCache::fetch($page_type .'_module_default_setting');
      return $settings;
    } else {
      $return = array();

      $results = $app->configObj->query("//*[@section='pages' and contains(@page_type, '$page_type')]");
//echo "Results:<pre>" . print_r($results, 1). "</pre>"; die();

//      $pages = array_flip(getConstantsByPrefix('PAGE_'));  // function defined in helper_functions.php
//      foreach($pages as $key => $const_name) {
      foreach($results as $const_name => $key) {
        if(is_numeric($key)) {
          $page_settings = self::load_setting($key, PA::$network_info->network_id, $page_type, null, $only_configurable);
          if($page_settings) {
            $cache_id = "dyn_page_$key";
            if(FileCache::is_cached($cache_id)) {
              $dynamic_page = FileCache::fetch($cache_id);
            } else {
              $dynamic_page = new DynamicPage($key);
              FileCache::store($cache_id, $dynamic_page, 1200);
            }
            if(!is_object($dynamic_page) or !$dynamic_page->docLoaded) {
              throw new Exception("Page XML config file for page ID: $page_id - not found!");
            }
            $dynamic_page->initialize();
            if(!$from_XML) {
              foreach($page_settings as $section => $value) {
                $dynamic_page->{$section} = $value;
              }
            }
            // also return the api_id
            $dynamic_page->api_id = strtolower(preg_replace("/^PAGE_/", '', $const_name));
            $return[] = $dynamic_page;
          }
        }
      }
      FileCache::store($page_type .'_module_default_setting', $return, 1200);
    }
    Logger::log('Exit: function ModuleSetting::get_pages_default_setting');
    return $return;
  }
}

?>