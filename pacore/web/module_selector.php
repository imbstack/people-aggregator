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
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        module_selector.php, web file for module setting on different pages
 *              This is a page, visible to Network owner only
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This files is used to save page_default_setting for different pages in a particular network
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 * TODO : we have to remove external CSS file
 */
  //anonymous user can not view this page;
  $login_required = TRUE;
  $use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
  //including necessary files
  include_once("web/includes/page.php");
  include_once "api/ModuleSetting/ModuleSetting.php";
  require_once "web/includes/network.inc.php";
  
  
 // This array is used to redirect to home page default setting, if invalid page_id is set
  $page_array = array('home_page_id'=>PAGE_HOMEPAGE,
                      'user_default_page_id'=>PAGE_USER_PUBLIC, 
                      'group_directory_page_id'=>PAGE_GROUPS_HOME,
                      'network_directory_page_id'=>PAGE_NETWORKS_HOME
                      );
  $page = trim(@$_GET['page_id']);
  if (!empty($page)) {
    if (array_key_exists($page, $page_array)) {
      $page_id = (int)$page_array[$page];
    } 
  } else { // if no page_id is specified
    $page_id = PAGE_HOMEPAGE;
  }
  $module_settings = ModuleSetting::load_setting($page_id, PA::$login_uid);
 // p($module_settings);
  if (!empty($_POST['save_mod_setting'])) {
    /* Now we can managing the links*/
    $data = array();
    $temp = array();
    $data = $_POST;
    // This function validate the data and gives result
    $error_msg = validate_module_setting_data($data);
    // Checking the empty    
    if (!empty ($data['mod_left'] )) { // Handling Left Modules
      foreach ($data['mod_left'] as $left=>$key ) {  
        if ($data['left_module'][$left] == 'left') {
          $temp['left']['name'][$data['textfield_for_left'][$left]] = $key;
        }
        else {
          $temp['right']['name'][$data['textfield_for_left'][$left]] = $key;
        }             
      }
    }
    if (!empty ($data['mod_right'])) { // Handling Right Modules
      foreach ($data['mod_right'] as $left=>$key ) {  
        if ($data['right_module'][$left] == 'left') {
          $temp['left']['name'][$data['textfield_for_right'][$left]] = $key;
        }
        else {
          $temp['right']['name'][$data['textfield_for_right'][$left]] = $key;
        }             
      }
    }
    if (!empty ($temp['left']['name'])) {// Sort the left array According to their Stacking order
      ksort($temp['left']['name']);
      $set_module['left'] = $temp['left']['name'];
    } else {
      $set_module['left'] = '';
    }
    
    if (!empty ($temp['right']['name'])) {// Sort the right array According to their Stacking order
      ksort($temp['right']['name']);
      $set_module['right'] = $temp['right']['name'];
    } else {
       $set_module['right'] = '';
    }  
    
    if (!empty ($_POST['middle_column'])) { // middle Module remain same as before
       $foo = unserialize($_POST['middle_column']);
       foreach ($foo as $left=>$key ) {
         $set_module['middle'][] = $key;
      }
    }
    $module_array = serialize($set_module);
    try { // Saving the data into database
      $success = ModuleSetting::save_page_setting($page_id, $module_array); 
      $module_settings = ModuleSetting::load_setting($page_id, NULL);
      $msg = 7010;
    } catch (PAException $e) { // handling exception while saving data 
      throw $e;
    }

  }
  
  $page = new PageRenderer("setup_module", PAGE_MODULE_SELECTOR, "Module Selector",
                            'container_two_column.tpl','header.tpl', PRI, HOMEPAGE,
                            PA::$network_info);
   
   function setup_module($column, $module, $obj) {
     global $module_settings, $page_id;
     $obj->module_settings = $module_settings;
     $obj->page_id = $page_id;
  }
  if (!empty($msg) && is_int($msg)) {
    $msg_obj = new MessagesHandler();
    $msg = $msg_obj->get_message((int)$msg); 
    switch($page_id) {
      case PAGE_HOMEPAGE:
        $redirect_url = PA::$url . PA_ROUTE_HOME_PAGE;
      break;
      case PAGE_USER_PUBLIC:
        $redirect_url = PA::$url . PA_ROUTE_USER_PUBLIC . "/" . @$_REQUEST['uid'];
      break;
      case PAGE_GROUPS_HOME:
        $redirect_url = PA::$url . PA_ROUTE_GROUPS;
      break;
      case PAGE_NETWORKS_HOME:
        $redirect_url = PA::$url . "/networks_home.php";
      break;
    }
    $msg .= "<a href =$redirect_url>Click here</a> to view the result";
  }
  
  if (!empty($msg) || !empty($error_msg)) {
    $msg_alert = ($error_msg)? $error_msg: $msg;
    $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
    $msg_tpl->set('message', $msg_alert);
    $m = $msg_tpl->fetch();
    $page->add_module("middle", "top", $m);
  } 
  $page->html_body_attributes ='class="no_second_tier network_config"';
  $css_path = PA::$theme_url . '/layout.css';
  $page->add_header_css($css_path);
  $css_path = PA::$theme_url . '/network_skin.css';
  $page->add_header_css($css_path);
  $css_path = PA::$theme_url . '/admin2.css';
  $page->add_header_css($css_path);
  
  echo $page->render();
?>