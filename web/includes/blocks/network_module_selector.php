<?php
if (!isset($login_required)) {
  $login_required = TRUE;
  $use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
  include_once("web/includes/page.php");
  $authorization_required = TRUE;
  

  $configure_permission = false;
  if(PA::$login_uid) {
    $configure_permission = User::has_network_permissions(PA::$login_uid, array('manage_themes'), true);
  }

  if (!$configure_permission) {
    //check applied to make sure that this file shouldn't be accesible directly through browser
    header('Location:'.PA::$url . PA_ROUTE_HOME_PAGE);
    exit;
  }
}

include_once "api/ModuleSetting/ModuleSetting.php";
 
 
 
 $page_id = PAGE_HOMEPAGE;
 if (!empty($_GET['page_id'])) {
   $page_details = ModuleSetting::get_pages_default_setting(array('page_id'=>$_GET['page_id']));
   if (!empty($page_details)) {
     //check for valid page_id. Otherwise homepage will be the default page.
     $page_id = $_GET['page_id'];
   }
 }
  
  $module_settings = ModuleSetting::load_setting($page_id, @$login_id);  
  if (!empty($_POST['save_mod_setting']) && $_GET['type'] == 'module' && !isset($_POST['default_setting']) ) {
    /* Now we can managing the links*/
    $data = array();
    $temp = array();
    $data = $_POST;
    // This function validate the data and gives result
    $error_msg = validate_module_setting_data($data);
    $error = (!empty($error_msg)) ? TRUE:FALSE;
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
  // Adding the Default option on the network module settings
  if (isset($_POST['default_setting']) && $_GET['type'] == 'module') {
     global $settings_new;
     $setting_default_data = $settings_new[$page_id]['data'];
     $module_array = serialize($setting_default_data);
     try { // Saving the data into database
        $success = ModuleSetting::save_page_setting($page_id, $module_array); 
        $module_settings = ModuleSetting::load_setting($page_id, NULL);
        $msg = 7010;
      } catch (PAException $e) { // handling exception while saving data 
        throw $e;
      }
  }
  if (!empty($error_msg)) {
    $msg = $error_msg;
  } 
  ?>