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


  $msg = null;
  $message = array();//array of messages and will be used to display messages.

  $info_boxes = 9; //number of showcased networks.
  $announcements = 3;

  $configurable_sections = array('info_boxes','network_of_moment', 'video_tours', 'register_today', 'configure','server_announcement', 'showcase', 'survey');
  $section = 'info_boxes';
  if (!empty($_GET['section'])) {
    if (!in_array($_GET['section'], $configurable_sections)) {
      $section = 'info_boxes';
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
    if ($section == 'info_boxes') {
      for ($counter = 0; $counter < $info_boxes; $counter++) {

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
          $file = $uploader->upload_file(PA::$upload_path,$image_file,true,true,'image');
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

    } else if ($section == 'survey') {
      if (empty($_POST['question'])) {
$message[] = __('Please give a question to use.');
} else {
$networks_data['question'] = $_POST['question'];
}

if (empty($_POST['answer1'])) {
$message[] = __('Please provide at least 2 answers.');
} else {
$networks_data['answer1'] = ($_POST['answer1']);
}
if (empty($_POST['answer2'])) {
$message[] = __('Please provide at least 2 answers.');
} else {
$networks_data['answer2'] = ($_POST['answer2']);
}
if (empty($_POST['a1'])) {
//$message[] = __('Nothing Wrong');
} else {
$networks_data['a1'] = ($_POST['a1']);
}
if (empty($_POST['a2'])) {
//$message[] = __('Nothing wrong 2');
} else {
$networks_data['a2'] = ($_POST['a2']);
}
}else if ($section == 'showcase') {
if (empty($_POST['featured_user_name'])) {
$message[] = __('No Featured User!');
} else {
if (User::user_exist($_POST['featured_user_name'])) {
  $thisUser = new User();
  $thisUser->load($_POST['featured_user_name']);
  $networks_data['featured_user_name'] = $thisUser->login_name;
$networks_data['auto_user_id'] = $thisUser->user_id;
$networks_data['auto_user_picture_url'] = $thisUser->picture;
            } else {
                        $message[] = __("Featured User Not Found!");
            }
}

if (empty($_POST['featured_group_id'])) {
$message[] = __('No Featured Group!');
} else {
$networks_data['featured_group_id'] = $_POST['featured_group_id'];
}
  if (empty($_POST['featured_video_id'])) {
$message[] = __('No Featured Video!');
} else {
$networks_data['featured_video_id'] = $_POST['featured_video_id'];
}
  if (empty($_POST['featured_business_id'])) {
$message[] = __('No Featured Business!');
} else {
$networks_data['featured_business_id'] = $_POST['featured_business_id'];
}
  // So that this works in the common code section below
  $_POST['description'] = "Showcase Module Links";
} else if ($section == 'video_tours') {

if (empty($_POST['video_url'])) {
$message[] = __('No video URL provided for video tours');
} else {
$networks_data['video_url'] = $_POST['video_url'];
}
}
else if ($section == 'server_announcement') {
    /*
$networks_data['announcement_image_url'] = @$_POST['video_url'];
$networks_data['importance'] = $_POST['importance'];
$networks_data['description1'] = $_POST['description1'];
$networks_data['description2'] = $_POST['description2'];
$networks_data['description3'] = $_POST['description3'];
*/
for ($counter = 0; $counter < $announcements; $counter++) {

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
  $file = $uploader->upload_file(PA::$upload_path,$image_file,true,true,'image');
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


    }
    else if ($section == 'configure') {
      if (!empty($_POST['show_splash_page']) && $_POST['show_splash_page'] == ACTIVE) {
        $networks_data['show_splash_page'] = ACTIVE;
      } else {
        $networks_data['show_splash_page'] = INACTIVE;
      }
    }

    //code for fields which are common to some sections like description, image
    if ($section == 'video_tours' || $section == 'register_today' || $section == 'network_of_moment'|| $section == 'server_announcement'|| $section == 'showcase') {

      if (empty($_POST['description'])) {
        $message[] = __('No description provided for network of moment');
      } else {
        $networks_data['description'] = $_POST['description'];
      }

      $image_file = 'network_image';
      if (!empty($_FILES[$image_file]['name'])) {
        //validating and then uploading the network image.
        $uploader = new FileUploader; //creating instance of file.
        $file = $uploader->upload_file(PA::$upload_path,$image_file,true,true,'image');
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
    global $featured_network, $info_boxes, $networks_data, $section,$perm;
    $obj->perm = $perm;
    switch ($module) {
      default:
        $obj->mode = $section;
        $obj->info_boxes = $info_boxes;
        $obj->networks_data = $networks_data;

    }
  }

  $page = new PageRenderer("setup_module", PAGE_CONFIGURE_SPLASH_PAGE, __("Configure Splash Page"), 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);

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
