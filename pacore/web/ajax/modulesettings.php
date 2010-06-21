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
  require_once dirname(__FILE__).'/../../config.inc';
require_once "api/ModuleSetting/ModuleSetting.php";
require_once "api/Logger/Logger.php";
require_once "web/includes/constants.php";
require_once "web/includes/functions/user_page_functions.php";
require_once("JSON.php");
session_start();
$page_id = $_GET['page_id'];
Logger::log("ajax/modulesettings.php: page_id=$page_id");
if($_SESSION['user']['id']) {
    $uid = (int) $_SESSION['user']['id'];
}
else {
    // ah-ah no WAY :)
    Logger::log("ajax/modulesettings.php: No Session for this visit");
}
if(!$uid || !$page_id) {
    $msg = "ajax/modulesettings.php: No IDs for uid($uid) or page_id($page_id) passed";
    Logger::log($msg);
    print("ERROR: ".$msg);
    exit;
}
$json              = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
$data              = stripslashes($_GET['data']);
$new_settings_data = $json->decode($data);
// remove the possibly auto added ActionsModule
$new_settings_data['left'] = delete_module_from_array('ActionsModule', $new_settings_data['left']);
$new_settings_data['right'] = delete_module_from_array('ActionsModule', $new_settings_data['right']);
Logger::log("ajax/modulesettings.php: writing settings for uid($uid) or page_id($page_id) ");
Logger::log("settings DATA IS:: ".print_r($new_settings_data, true));
// Logger::log("RAW JSON DATA IS:: ".$data );
// save settings for THIS page
ModuleSetting::save_setting($uid, $page_id, $new_settings_data);

/*  
  if ($page_id == 1) {
    // if this is the USER page
    // we also want to save thiose settings
    // for her PUBLIC page
    //Code to remove modules from user public page
    
    $new_settings_data_public = $new_settings_data;
    
    $public_page_blacklist_modules = array('EnableModule', 'UserMessagesModule');     
    $new_settings_data_public['left'] = 
      delete_module_from_array(
        $public_page_blacklist_modules, $new_settings_data_public['left']
      );
  
    $new_settings_data_public['right'] = 
      delete_module_from_array(
        $public_page_blacklist_modules, $new_settings_data_public['right']
      );
    // save setting for the PUBLIC page also
  Logger::log("public settings DATA IS:: ".print_r($new_settings_data_public, true));
    ModuleSetting::save_setting($uid, PAGE_USER_PUBLIC, $new_settings_data_public);
  }
*/
// output something to the browser
print_r($new_settings_data);
exit;
?>