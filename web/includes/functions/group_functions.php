<?php  
  require_once "web/includes/classes/file_uploader.php";
  
  function header_image() {
  
  $header_image_action = $_POST['header_image_option'];
  $display_header_image = $_POST['desktop_image_display'];  
  
    if (!empty($_FILES['headerphoto']['name']) && empty($_POST['restore_default'])) {
      $uploadfile = PA::$upload_path . basename($_FILES['headerphoto']['name']);
      $myUploadobj = new FileUploader; //creating instance of file.
      $image_type = 'image';
      $file = $myUploadobj->upload_file(PA::$upload_path, 'headerphoto', true, true, $image_type);
        
      if ($file == false) {
        throw new PAException(GROUP_PARAMETER_ERROR, "File upload error: ".$myUploadobj->error);
      }
      $header_image = $file;
      
      $header_img = array('display_header_image'=>$display_header_image, 'header_image_action'=> $header_image_action, 'header_image'=>$header_image);
      return $header_img;
    }
    
    if (isset($_POST['restore_default'])) {
      $header_image = NULL;
      $header_img = array('display_header_image'=>$display_header_image, 'header_image_action'=> $header_image_action, 'header_image'=>$header_image);
      
      return $header_img;
    }
    
    $header_img = array('display_header_image'=>$display_header_image, 'header_image_action'=> $header_image_action);
    return $header_img;
 }
 
 function manage_module_setting() {
   if(isset($_POST['default_setting'])) {
     global $settings_new;
     $set_module = $settings_new[PAGE_GROUP]['data'];
     $return_array = array('module_data'=>$set_module, 'error'=>$error_msg);
     return $return_array;
   }
   if (!empty($_POST['save_mod_setting']) && $_GET['type'] == 'module' ) {
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
    $return_array = array('module_data'=>$set_module, 'error'=>$error_msg);
    return $return_array;
  }

 }
?>    