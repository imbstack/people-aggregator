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
  
  define('NONEMPTY', 1);
  define('EMPTY_NUMERIC', 2);
  define('NUMERIC', 3);
  define('EMPTY_FLOAT', 4);
  define('FLOAT', 5);
  define('EMPTY_STRING', 6);
  define('STRING', 7);
  define('URL', 8); 
  define('FILE_VALIDATION', 9);
  
  define('IMAGE_FILE', 'image');
  define('AUDIO_FILE', 'audio');
  define('VIDEO_FILE', 'video');
  
  // global var $path_prefix has been removed - please, use PA::$path static variable
  
  class SbHelper {
   
    public $error_array = array();
    public $sb_event_forms = array(0 => 'event/generic', 1 => 'event/concert', 2 => 'event/conference');
    
    function SbHelper() {
    
/*-----------------------------------------*/
    
        $field_list = array(
                        '0' => array('name' => '/event/name', 'type' => EMPTY_STRING, 'caption' => 'Event name'),
                        // Why FLOAT?  Entry Fee should be OK with strings like "$42.50" and "400 yen".
                        //'1' => array('name' => '/event/price', 'type' => FLOAT, 'caption' => 'Entry Fee'),
                        '2' => array('name' => '/event/location/@postcode', 'type' => NUMERIC, 'caption' => 'Postal code'),
                        '3' => array('name' => 'sb_action_upload_file_name/event/image', 'type' => FILE_VALIDATION, 'caption' => 'Image:'.IMAGE_FILE),
                        '4' => array('name' => '/event/location/@city', 'type'=> STRING, 'caption' => 'City'),
                        '5' => array('name' => '/event/location/@state', 'type'=> STRING, 'caption' => 'State'),
                        '6' => array('name' => '/event/location/@country', 'type'=> STRING, 'caption' => 'Country'),
                      );  
        $this->sb_form_info['event/generic'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/event/name', 'type' => EMPTY_STRING, 'caption' => 'Concert name'),
                        '1' => array('name' => 'sb_action_upload_file_name/event/image', 'type' => FILE_VALIDATION, 'caption' => 'Image:'.IMAGE_FILE)                         
                      );
        $this->sb_form_info['event/concert'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/event/name', 'type' => EMPTY_STRING, 'caption' => 'Conference name'),
                        '1' => array('name' => 'sb_action_upload_file_name/event/image', 'type' => FILE_VALIDATION, 'caption' => 'Image:'.IMAGE_FILE)                         
                      );
        $this->sb_form_info['event/conference'] = array('field_list' => $field_list);

/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/media/@title', 'type' => EMPTY_STRING, 'caption' => 'Audio title'),                         
                        '1' => array('name' => 'sb_action_upload_file_name/media/@mediaurl', 'type' => FILE_VALIDATION, 'caption' => 'Audio:'.AUDIO_FILE),
                        '2' => array('name' => 'sb_action_upload_file_name/media/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:'.IMAGE_FILE)
                      );
        $this->sb_form_info['media/audio'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/media/@title', 'type' => EMPTY_STRING, 'caption' => 'Image title'),
                        '1' => array('name' => 'sb_action_upload_file_name/media/image', 'type' => FILE_VALIDATION, 'caption' => 'Image:'.IMAGE_FILE)
                      );
        $this->sb_form_info['media/image'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/media/@title', 'type' => EMPTY_STRING, 'caption' => 'Video title'),                         
                        '1' => array('name' => 'sb_action_upload_file_name/media/@mediaurl', 'type' => FILE_VALIDATION, 'caption' => 'Video:'.VIDEO_FILE),
                        '2' => array('name' => 'sb_action_upload_file_name/media/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:'.IMAGE_FILE)                     
                      );
        $this->sb_form_info['media/video'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Album name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@coverart', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                          
                      );
        $this->sb_form_info['review/album'] = array('field_list' => $field_list);        

/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@title', 'type' => EMPTY_STRING, 'caption' => 'Title of article')                         
                      );
        $this->sb_form_info['review/article'] = array('field_list' => $field_list);             

/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Book name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/book'] = array('field_list' => $field_list);   
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/club'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/@name', 'type' => EMPTY_STRING, 'caption' => 'Event name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/event'] = array('field_list' => $field_list);        
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Hotel/Resort\'s name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/hotel'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Service name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')
                      );
        $this->sb_form_info['review/localservice'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Magazine name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/magazine'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Movie/TV show name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/movie'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Restaurant\'s name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/restaurant'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Software\'s name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/software'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Song\'s name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@coverart', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/song'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/review/subject/@name', 'type' => EMPTY_STRING, 'caption' => 'Site name'),
                        '1' => array('name' => 'sb_action_upload_file_name/review/subject/@image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['review/website'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/group/@name', 'type' => EMPTY_STRING, 'caption' => 'Group name'),
                        '1' => array('name' => 'sb_action_upload_file_name/group/image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['showcase/group'] = array('field_list' => $field_list);
        
/*-----------------------------------------*/
        
        $field_list = array(
                        '0' => array('name' => '/showcase/@firstname', 'type' => EMPTY_STRING, 'caption' => 'First name'),
                        '1' => array('name' => 'sb_action_upload_file_name/showcase/image', 'type' => FILE_VALIDATION, 'caption' => 'Image:image')                         
                      );
        $this->sb_form_info['showcase/person'] = array('field_list' => $field_list);                                     
    }
    
    function set_mc_type ($sb_mc_type) {
        $this->sb_mc_type = $sb_mc_type;
    }
    
    
    function processForm () {
        $form_fields_array = $this->sb_form_info[$this->sb_mc_type]['field_list'];
                
        foreach($form_fields_array as $fieldinfo) {
            $name = $fieldinfo['name'];
            $caption = $fieldinfo['caption'];
            switch($fieldinfo['type']) {
            case NONEMPTY:
                $this->check_empty();
                break;
                
            case EMPTY_NUMERIC:
                $this->check_numeric($name, $caption, TRUE);
                break;
                
            case NUMERIC:
                $this->check_numeric($name, $caption, FALSE);
                break;
                
            case EMPTY_FLOAT:                        
                $this->check_float($name, $caption, TRUE);
                break;
                
            case FLOAT:
                $this->check_float($name, $caption, FALSE);
                break;
                
            case EMPTY_STRING:
                $this->check_empty($name, $caption);
                break;
                
            case STRING:
                $this->check_string($name, $caption);
                break;
                
            case URL:
                break; 
                
            case FILE_VALIDATION:
                $this->validate_file($name, $caption);
                break;       
            }            
        }
        
        $this->correct_links();
        
        if(in_array($this->sb_mc_type, $this->sb_event_forms)) {
            $this->check_event_date();
        }
        
        return $this->error_array;
    }

    
    function check_string($field, $caption) {
        if(!empty($_POST[$field]) && (preg_match("/^[a-zA-Z]+$/", $_POST[$field]) == 0)) {
            $this->error_array[] = $caption.' should contain only the letters ';
        }
    }
    
    function check_empty ($field, $caption) {
        if(empty($_POST[$field]) || !trim($_POST[$field])) {
            $this->error_array[] = $caption.' cannot be left blank';
        }
    }
    
    function check_float ($field, $caption, $check_empty) {
        if($check_empty) {
            $this->check_empty($field, $caption);
        }
        if(!empty($_POST[$field]) && !is_numeric($_POST[$field])) {
            $this->error_array[] = $caption.' should have a numeric value';
        }
    }
    
    function check_numeric ($field, $caption, $check_empty) {
        if($check_empty) {
            $this->check_empty($field, $caption);
        }
        if(!empty($_POST[$field]) && !is_numeric($_POST[$field])) {
            $this->error_array[] = $caption.' should have a valid integral value';
        }
    }
    
    function check_event_date () {
        if($_POST["/event/begins#ampm"] == "PM" && $_POST["/event/begins#hours"] < 12) {
            $beginHours = $_POST["/event/begins#hours"] + 12;
        } else {
            $beginHours = $_POST["/event/begins#hours"];
        }
        
        if($_POST["/event/ends#ampm"] == "PM" && $_POST["/event/ends#hours"] < 12) {
            $endHours = $_POST["/event/ends#hours"] + 12;
        } else {
            $endHours = $_POST["/event/ends#hours"];
        }
        
        $beginTime = mktime($beginHours, $_POST["/event/begins#minutes"], 0, $_POST["/event/begins#month"], $_POST["/event/begins#day"], $_POST["/event/begins#year"]);
        
        $endTime = mktime($endHours, $_POST["/event/ends#minutes"], 0, $_POST["/event/ends#month"], $_POST["/event/ends#day"], $_POST["/event/ends#year"]);
        
        if(!checkdate($_POST["/event/begins#month"], $_POST["/event/begins#day"], $_POST["/event/begins#year"])) {
          $this->error_array[] = "Please enter a valid event start date";
        }
        else if (!checkdate($_POST["/event/ends#month"], $_POST["/event/ends#day"], $_POST["/event/ends#year"])) {
          $this->error_array[] = "Please enter a valid event end date";
        }
        if($endTime < $beginTime) {
          $this->error_array[] = "Event start time should be less than end time : right now it ends before it begins!";
        } 
        else if ($endTime == $beginTime) {
          $this->error_array[] = "Event starting and ending time cannot be the same!";
        }
    }
    
    function correct_links () {
        foreach($_POST as $key => $value) {
          if(substr_count($key, "@url") || substr_count($key, "@map")) {
            if(!substr_count($value,"http://") && !empty($value)) {
              $_POST[$key] = "http://".$value;
              $_REQUEST[$key] = $_POST[$key];
            }
          }
        }
    }
    
    static function get_album_type ($sb_mc_type) {
        switch($sb_mc_type) {
            case 'media/image';
                $album_type = IMAGE_ALBUM;
               break;
            case 'media/audio';
                $album_type = AUDIO_ALBUM;
               break;
            case 'media/video';
                $album_type = VIDEO_ALBUM;                
               break;
            default:
                $album_type = -1;
        }
        return $album_type;
    }
    
    function validate_file($field, $caption) {
        if($_FILES[$field]['error'] == 0) {
            $temp_array = explode(":", $caption);
            $caption = $temp_array[0];
            $file_type = $temp_array[1];        
            /*$mime_type = mime_content_type($_FILES[$field]['tmp_name']);
            if(!strstr($mime_type,$file_type)) {
                $this->error_array[] = "Please select a valid ".$caption." file";
            }*/            
            
            if ( !check_file_type( $_FILES[$field]['tmp_name'], $file_type ) ) {
                $this->error_array[] = "Please select a valid ".$caption." file";
                //unset so it doesnt get added to xml schema
                unset($_FILES[$field]);
            }
        }
    }
}
?>