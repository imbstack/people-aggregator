<?php
//echo "fyi - NetworkDefaultControlModule/action.php not used yet"; exit;

require_once "web/includes/classes/file_uploader.php";
require_once "api/Roles/Roles.php";

if ($_form['operation'] == 'create_network') {
  $return_variable = create_new_network($_form);
  $failure_msg = $return_variable['msg']['failure_msg'];
  $succss_msg = $return_variable['msg']['success_msg'];
  $redirect_url = $return_variable['redirection_url'];
}

$msg_array = array();
$msg_array['failure_msg'] = $failure_msg;
$msg_array['success_msg'] = $succss_msg;
set_web_variables($msg_array, $redirect_url, $query_str);


// Here we made a function for creating the network  ;)

function create_new_network($_form) {

// function checks initial settings for network creation
$can_network_be_created = Network::can_network_be_created();

if ( $can_network_be_created['error'] == TRUE ) {
  $config_error = TRUE;
  $error = TRUE;
  $error_msg = $can_network_be_created['error_msg'];
} else if(!PA::$login_uid) {
  $config_error = TRUE;
}

//form_data is array used for form fields
// its initialized by $_form
$temp_data['action'] = 'add';
$vartoset = array('address','name','tagline','category','desc','header_image','header_image_option','action', 'type','network_group_title', 'network_content_moderation');
for ($i = 0; $i < count($vartoset); $i += 1) {
  $var = $vartoset[$i];
  if (!empty($_form[$var])) {
    $temp_data[$var] = trim($_form[$var]);
  }
  if ($var == 'type') {
    if (isset($_form[$var])) {
      $temp_data[$var] = $_form[$var];
    }
  }
}

  if (empty($config_error)) {
    filter_all_post($_form);//filters all data of html
    $error_post = check_error();//validation check

    if ($error_post['error']==TRUE) {
      $error = TRUE;
      $error_msg = $error_post['error_msg'];
    }
    if ( !$error_post ) {
      //upload file
      if (!empty($_FILES['network_image']['name'])) {
        $file_upload_result = do_file_upload();
        if ($file_upload_result['error']) {
          $error = TRUE;
          $error_msg = $file_upload_result['error_msg'];
        } else {
          $header_image = $network_image = $file_upload_result['file'];
        }
          } else {
            //image hidden
            $header_image = $network_image = trim($_form['header_image']);
          }
          //code to upload the icon image
          if (!empty($_FILES['inner_logo_image']['name'])) {
            $uploadfile = PA::$upload_path . basename($_FILES['inner_logo_image']['name']);
            $myUploadobj = new FileUploader; //creating instance of file.
            $image_type = 'image';
            $file = $myUploadobj->upload_file(PA::$upload_path, 'inner_logo_image', true, true, $image_type);
            if ($file == false) {
              $error = TRUE;
              $error_msg = $file_upload_result['error_msg'];
              unset($data_icon_image);
            } else {
              $data_icon_image = array('inner_logo_image' => $file);
            }

          } else {
            unset($data_icon_image);
          }
          //...code to upload the icon image
              $network_basic_controls = PA::$network_defaults;
              $network_basic_controls['basic']['header_image']['name'] = $header_image;
              $network_basic_controls['basic']['header_image']['option'] = ($_form['header_image_option'])?($_form['header_image_option']):DESKTOP_IMAGE_ACTION_STRETCH;
              // for title of network group
              $network_basic_controls['network_group_title'] = '';
              $network_basic_controls['network_group_title'] = $_form['network_group_title'];
              $network_basic_controls['network_content_moderation'] = $_form['network_content_moderation'];
              $temp_data['address'] = strtolower( $temp_data['address'] );
              $data = array(
                'user_id' => $_SESSION['user']['id'],
                'name' => strip_tags($temp_data['name']),
                'address' => $temp_data['address'],
                'tagline' => strip_tags($temp_data['tagline']),
                'category_id' => $temp_data['category'],
                'type' => $temp_data['type'],
                'description' => $temp_data['desc'],
                'extra'=>serialize($network_basic_controls),
                'created'=>time(),
                'changed'=>time(),

              );
        //add icon image
        if (is_array($data_icon_image) && !empty($data_icon_image['inner_logo_image'])) {
          $data = array_merge($data, $data_icon_image);
          $temp_data['inner_logo_image'] = $data_icon_image['inner_logo_image'];
        }

      $network = new Network;
      $network->set_params($data);
      try {
        $nid = $network->save();
        default_page_setting($network->address); // populate page_default setting
      }
      catch (PAException $e) {
        $error = TRUE;
        $error_msg = "$e->message";
      }

      if (!empty($nid)) {
        $_extra = serialize(array('user' => false, 'network' => true, 'groups' => array()));
        Roles::set_user_role_for_network($network->user_id, ADMINISTRATOR_ROLE, $network->address, $_extra);
        $location = "http://" . $temp_data['address'] . '.' . PA::$domain_suffix . BASE_URL_REL . PA_ROUTE_CONFIGURE_NETWORK;
      }
    }
    $msg_array = array();
    $msg_array['failure_msg'] = $error_msg;
    $msg_array['success_msg'] = 7006;
    $return_array = array('msg' => $msg_array, 'redirection_url' => $location, 'query_str' =>$query_str);

  }

  return $return_array;
}// End of function create new network


?>
