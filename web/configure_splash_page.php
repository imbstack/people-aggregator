<?php
  /**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        configure_splash_page.php, web file to set the three showcase Networks on the Splash Page
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file is the first to be viewed by the new user. Some featured network will be
                displayed on this page. Super Admin or the moderator of the mothership
                ie admin of mother network can change the networks to be displayed on this page.
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 * This page us used for network settings
 * anonymous user can not view this page;
 * TODO: do_file_upload() function in network.inc.php should be made generic to upload the image file and then use it here.
 */
 
  $login_required = TRUE;
  //including necessary files
  $use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
  include_once("web/includes/page.php");
  require_once "web/includes/network.inc.php";
  require_once "web/includes/classes/file_uploader.php";
  require_once "api/Validation/Validation.php";
  require_once "api/ModuleData/ModuleData.php";
  
 
  global $network_info;
 
   
  $msg = null;
  $message = array();//array of messages and will be used to display messages.
  
  $showcased_networks = 3; //number of showcased networks.
  
  $configurable_sections = array('showcased_networks', 'network_of_moment', 'video_tours', 'register_today', 'configure','server_announcement');
  $section = 'showcased_networks';
  if (!empty($_GET['section'])) {
    if (!in_array($_GET['section'], $configurable_sections)) {
      $section = 'showcased_networks';
    } else {
      $section = $_GET['section'];
    }
  }
  $networks_data = array();  
  $networks_data = ModuleData::get($section);
  if (($networks_data)) {
    // data already exists for this module
    $networks_data = unserialize($networks_data);
    $action = 'update';
  } else {
    // network operator is saving the data for the first time.
    $action = 'save';
  }
  
  if (!empty($_POST)) {
    if ($section == 'showcased_networks') {
      for ($counter = 0; $counter < $showcased_networks; $counter++) {
        
        if (empty($_POST['network_url'][$counter])) {
          $message[] = __('Network URL cannot be left empty for showcased network ').($counter + 1);
          $networks_data[$counter]['network_url'] = null;
        } else {
          $networks_data[$counter]['network_url'] = Validation::validate_url($_POST['network_url'][$counter]);
        }
      
        if (!empty($_POST['caption'][$counter])) {
          $networks_data[$counter]['caption'] = $_POST['caption'][$counter];
        } else {
          $networks_data[$counter]['caption'] = null;
        }
        
        $image_file = 'network_image_'.$counter;
      
        if (!empty($_FILES[$image_file]['name'])) {
        //validating and then uploading the network image.
          $uploader = new FileUploader; //creating instance of file.      
          $file = $uploader->upload_file($uploaddir,$image_file,true,true,'image');
          if( $file == false) {
            $message[] = __(' For showcased network ').($counter + 1).', '.$uploader->error;
            $networks_data[$counter]['network_image'] = null;
          }
          else {
            $networks_data[$counter]['network_image'] = $file;        
	    Storage::link($file, array("role" => "showcased_net"));
          }
        } else if (!empty($_POST['current_network_image'][$counter])) {
        //getting the previously added image from the hidden form field.
          $networks_data[$counter]['network_image'] = $_POST['current_network_image'][$counter];
        } else {
        //setting the image to null.
          $networks_data[$counter]['network_image'] = null;
        }
        
      }//end for
    
    } else if ($section == 'network_of_moment') {
      if (empty($_POST['network_caption'])) {
        $message[] = __('No caption given to network of moment');
      } else {
        $networks_data['network_caption'] = $_POST['network_caption'];
      }
      
      if (empty($_POST['network_url'])) {
        $message[] = __('Please provide URL for network of moment');
      } else {
        $networks_data['network_url'] = Validation::validate_url($_POST['network_url']);
      }
    
    } else if ($section == 'video_tours') {
      
      if (empty($_POST['video_url'])) {
        $message[] = __('No video URL provided for video tours');
      } else {
        $networks_data['video_url'] = $_POST['video_url'];
      }
      
    } 

    


    

     else if ($section == 'server_announcement') {
           $networks_data['announcement_image_url'] = @$_POST['video_url'];
       }








      else if ($section == 'configure') {
      if (!empty($_POST['show_splash_page']) && $_POST['show_splash_page'] == ACTIVE) {
        $networks_data['show_splash_page'] = ACTIVE;
      } else {
        $networks_data['show_splash_page'] = INACTIVE;
      }
    }
    
    //code for fields which are common to some sections like description, image
    if ($section == 'video_tours' || $section == 'register_today' || $section == 'network_of_moment'|| $section == 'server_announcement') {
      
      if (empty($_POST['description'])) {
        $message[] = __('No description provided for network of moment');
      } else {
        $networks_data['description'] = $_POST['description'];
      }
      
      $image_file = 'network_image';
      if (!empty($_FILES[$image_file]['name'])) {
        //validating and then uploading the network image.
        $uploader = new FileUploader; //creating instance of file.      
        $file = $uploader->upload_file($uploaddir,$image_file,true,true,'image');
        if( $file == false) {
          $message[] = __(' For network of moment, ').$uploader->error;
          $networks_data['network_image'] = null;
        }
        else {
          $networks_data['network_image'] = $file;
        }
      } else if (!empty($_POST['current_network_image'])) {
      //getting the previously added image from the hidden form field.
        $networks_data['network_image'] = $_POST['current_network_image'];
      } else {
      //setting the image to null.
        $networks_data['network_image'] = null;
      }
    
    }
    
    $networks_data = serialize($networks_data);
    if ($action == 'save') {
      ModuleData::save($networks_data, $section);
    } else {
      ModuleData::update($networks_data, $section);
    }
    
    $networks_data = ModuleData::get($section);
    $networks_data = unserialize($networks_data);
    //TODO: display the error messages.
    
  }//end if
  
  function setup_module($column, $module, $obj) {
    global $featured_network, $showcased_networks, $networks_data, $section,$perm;
    $obj->perm = $perm;
    switch ($module) {
      default:
        $obj->mode = $section;
        $obj->showcased_networks = $showcased_networks;
        $obj->networks_data = $networks_data;
        
    }
  }
  
  $page = new PageRenderer("setup_module", PAGE_CONFIGURE_SPLASH_PAGE, __("Configure Splash Page"), 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, $network_info);
  
  $page->html_body_attributes ='class="no_second_tier network_config"';
  $css_array = get_network_css();
  if (is_array($css_array)) {
    foreach ($css_array as $key => $value) {
      $page->add_header_css($value);
    }
  }
  
  $css_data = inline_css_style();
  if (!empty($css_data['newcss']['value'])) {
    $css_data = '<style type="text/css">'.$css_data['newcss']['value'].'</style>';
    $page->add_header_html($css_data);
  }

  uihelper_error_msg($message);
  echo $page->render();
?>