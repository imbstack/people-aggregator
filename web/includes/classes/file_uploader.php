<?php
/**
* Class FileUploader uploads the file from web forms
* @package file_uploader
* @author Tekriti Software
*/
// include this file to check mime_type of the file that is to be uploaded

if (defined("NEW_STORAGE")) require_once "api/Storage/Storage.php";

class FileUploader {
  var $directory_name;
  var $max_filesize;
  var $error;
  var $raw_file_name;
  var $file_name;
  var $full_name;
  var $file_size;
  var $file_type;
  var $check_file_type;
  var $tmp_name;
  var $file_type_info = array();

  public function __construct() {
    $this->file_type_info = $GLOBALS['file_type_info'];
  }

  /**
  * sets directory_name
  * @access private
  */
  private function set_directory($dir_name = ".") {
    $this->directory_name = $dir_name;
  }

  /**
  * sets max_filesize
  * @access private
  */
  private function set_max_size($max_file = 10000000) {
    $this->max_filesize = $max_file;
  }

  /**
  * checks for directory to upload the file into
  * if directory is not present it creates the directory
  * @access private
  */
  private function check_for_directory() {
    if ( !file_exists($this->directory_name) ) {
      mkdir($this->directory_name,0777);
    }
    @chmod($this->directory_name,0777);
  }

  /**
  * returns error
  * @access private
  */
  private function error() {
    return $this->error;
  }

  /**
  * sets file_size
  * @access private
  */
  private function set_file_size($file_size) {
    $this->file_size = $file_size;
  }

  /**
  * sets tmp_name folder name
  * @access private
  */
  private function set_temp_name($temp_name) {
    $this->tmp_name = $temp_name;
  }

  /**
  * sets file_name,full_name
  * @access private
  */
  private function set_file_name($file) {
    $this->raw_file_name = $file; // 'insecure' filename, to pass to Storage
    $this->file_name = preg_replace('/[^\w\d\.\-]/', '_', $file);
    $this->full_name = $this->directory_name.$this->file_name;
  }

  /**
   * Uploads the file
   * @access public
   * @params
   * $uploaddir : Directory Name in which uploaded file is placed
   * $name : file input type field name
   * $rename : you may pass string or boolean
      true : rename the file if it already exists and returns the renamed file name.
   *  String : rename the file to given string.
   *  $replace =true : replace the file if it is already existing
   *  $file_max_size : file size in bytes. 0 for default
   *  $check_type : checks file type exp ."(jpg|gif|jpeg)"
   *  Example upload_file("temp","file",true,true,0,"jpg|jpeg|bmp|gif")
   * return : On success it will return file name else return (boolean)false
  */
  public function upload_file($uploaddir,$name,$rename=null,$replace=false,$check_type="") {
    $this->set_file_size($_FILES[$name]['size']);
    $this->error=$_FILES[$name]['error'];
    $this->set_temp_name($_FILES[$name]['tmp_name']);
    $this->set_directory($uploaddir);
    $this->check_for_directory();
    $this->set_file_name($_FILES[$name]['name']);
    $file_size = @$this->file_type_info[$check_type]['max_file_size']/1000000;
    if ($this->error == 1) {
      $this->error = sprintf(
          __("Your file is too large for the web server.  The largest file you can upload here is %.1fM.  If this is too small, please ask the administrator to increase the <code>upload_max_filesize</code> directive in <code>php.ini</code>."),
          floatval(parse_file_size_string(ini_get("upload_max_filesize")))/1048576.0);
    }
    elseif ($this->error == 3) {
      $this->error = __('The uploaded file was only partially uploaded.');
    }
    elseif ($this->error == 4) {
      $this->error = __('No file was uploaded');
    }
    elseif( !is_uploaded_file($this->tmp_name) ) {
      $this->error = sprintf(__("File %s not uploaded correctly."), $this->tmp_name);
    }

    if(empty($this->file_name)) {
      $this->error = __("File is not uploaded correctly.");
    }
    if( $this->error!="" ) {
      return false;
    }

    //check here for valid file
    if (!empty($check_type)) {
        // set max upload size
        if ( array_key_exists($check_type, $this->file_type_info) ) {
          $this->set_max_size($this->file_type_info[$check_type]['max_file_size']);
        }

        // check file size against maximum
        if ($this->file_size > $this->max_filesize) {
          $this->error = sprintf(
            __("File too large; %s file uploads are limited to %s.  If this is too small, please ask the administrator to increase the limit."),
            $check_type, format_file_size($this->max_filesize)
);
          return false;
        }
        //if $check_type is just image then we can check it via getImagesize() function if the file is valid or not
        // does this check always for image

        if ( $check_type == 'image' ) {
          $sizeCheck = @getImagesize($this->tmp_name);
          if ( !$sizeCheck ) {
            $this->error = __("Invalid image file.");
            return false;
          }
          //additional check to see if the image has any extension
          $ext = explode('.',$this->file_name);
          $ext = strtolower(end($ext));
          $img_mime = explode('/',$sizeCheck['mime']);
          $img_mime = strtolower(end($img_mime));
          //jpeg and jpg may have different extension and mime so handled specially
          if ($ext=='jpg' || $ext == 'jpeg' ) {
          } else if ($ext!=$img_mime) {
            //means there is no image extension so lets add it
            $this->file_name .= '.'.$img_mime;
          }

        }

        //check for other media types
        // can be turned of from config.inc define('CHECK_MIME_TYPE',0);
        if ( $check_type == 'audio' || $check_type == 'video') {
          if( CHECK_MIME_TYPE == 1 ) {
            $mime_type = exec('file -bi '.$this->tmp_name);

            // TO DO:: enalbe for audio/video //application/octet-stream
            if(empty($mime_type)) {
              $this->error = __("Invalid media file.");
              return false;
            }
            if (strstr($mime_type, $check_type)) {
            }
            else if (strstr($mime_type, 'media')) {
            }
            else if (strstr($mime_type, 'octet-stream')) { // Temporarily added for .wav file -- need to do something else
            }
            else {
              $this->error = __("Invalid media file.");
              return false;
            }
          }
        }
        //special treatment for doc types
        if ( $check_type == 'doc' ) {
          if( CHECK_MIME_TYPE == 1 ) {
            $mime_type = exec('file -bi '.$this->tmp_name);
            if (strstr($mime_type, 'msword') || strstr($mime_type, 'pdf') || strstr($mime_type, 'text/plain')) {//ToDo: Temporary fix. Need to correct it

            } else {
                $this->error = sprintf(
                    __("Invalid document type - supported formats are: %s"),
                    ".doc, .pdf");
              return false;
            }
          }
        }


    }//check_type

    if (!defined("NEW_STORAGE")) {
      // --- obsoleted by new Storage system ---
      if( !is_bool($rename) && !empty($rename) ) {
	if( preg_match("/\..*+$/",$this->file_name,$matches) ) {
	  $this->set_file_name($rename.$matches[0]);
	}
      }
      elseif( $rename && file_exists($this->full_name) ) {
	if( preg_match("/\..*+$/",$this->file_name,$matches) ) {
	  $this->set_file_name(substr_replace($this->file_name,"_".rand(0, rand(0,99)),-strlen($matches[0]),0));
	}
      }
      if(file_exists($this->full_name)) {
	if($replace) {
	  @unlink($this->full_name);
	} else {
	  $this->error = __("File error: File already exists");
	  return false;
	}
      }
    }

    $this->start_upload();
    if (!empty($this->error)) return false;
    if (!defined("NEW_STORAGE")) return $this->file_name;
    return $this->file_id;
  }
  /**
  * Name : start_upload()
  * @access private
  * Use : actually uploads the file
  */
  private function start_upload() {
    if(!isset($this->file_name)) {
      $this->error = "You must define filename!";
    }
    if ($this->file_size <= 0) {
      $this->error = "The file size is too small.";
    }
    if ($this->file_size > $this->max_filesize && $this->max_filesize!=0) {
      $this->error = "The file size is too large, upload a file having size less than ".($this->max_filesize/1000000)."M";
    }
    if ($this->error!="") return;

    $destination = $this->full_name;      
    if (!defined("NEW_STORAGE")) {
      // Old style - just throw it in web/files
      if (!@move_uploaded_file ($this->tmp_name,$destination)) {
	$this->error = "Unable to copy ".$this->file_name." to $destination directory.";
	return;
      }
    } else {
      if (!is_uploaded_file($this->tmp_name)) {
	$this->error = sprintf(__("Security error: File %s is not an uploaded file:"), $this->tmp_name);
	return;
      }
      
      // we now know that $this->tmp_name is a real file and has passed all the security checks, so throw it in storage...
      $this->file_id = Storage::save($this->tmp_name, $this->raw_file_name);
    }
  }
}
?>