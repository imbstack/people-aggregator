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
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/Validation/Validation.php";

function validate_url($url) {
    return Validation::validate_url($url);
}

/**
 * validate the email addresses
 * @param string email_address this is the email address given by user
 */
function validate_email($email_address) {
    $return = 0;
    if(preg_match('/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i', $email_address, $domain)) {
        $domain = explode('.', $domain[0]);
        foreach($domain as $part) {
            // Iterate through the parts
            if(substr($part, 0, 1) == '_' || substr($part, strlen($part)-1, 1) == '_') {
                $return = 0;
            }
            else {
                $return = 1;
            }
        }
    }
    return $return;
}

function get_valid_filter_arrays() {
    $tags = array(
        'strong',
        'ol',
        'ul',
        'li',
        'b',
        'i',
        'em',
        'strike',
        'u',
        'p',
        'img',
    );
    $attr = array(
        'href',
        'src',
        'width',
        'height',
        'alt',
    );
    $filter = array(
        $tags,
        $attr,
    );
    return $filter;
}

/**
* function to check for valid image, video, audio, doc etc
* @param file_path=> physical path of the file, file_type => type of validaion eg. image
*/
function check_file_type($file_path, $file_type = 'image') {
    $return = '';
    if(!file_exists($file_path)) {
        // file path specified is not correct.
        $return = 'Please specify a valid file name';
        return $return;
    }
    $mime_type = @exec('file -bi '.$file_path);
    // checking the mime type of a file
    if($mime_type == 'application/octet-stream') {
        // do some more checking
        $f = @fopen($file_path, "rb");
        if($f) {
            $start = fread($f, 1024);
            fclose($f);
            // wmv
            if(strpos($start, "\x30\x26\xb2\x75\x8e\x66\xcf\x11\xa6\xd9\x00\xaa\x00\x62\xce\x6c") !== FALSE) {
                $mime_type = 'video/x-ms-wmv';
            }
            // add more file type checks here
        }
    }
    $valid_extensions = explode('|', $GLOBALS['file_type_info'][$file_type]['ext']);
    //getting the valid extensions from config.inc
    $is_valid_file = FALSE;
    $counter = 0;
    while(count($valid_extensions) && !$is_valid_file) {
        if(substr_count($mime_type, $valid_extensions[$counter])) {
            $is_valid_file = TRUE;
        }
        unset($valid_extensions[$counter]);
        $counter++;
    }
    return $is_valid_file;
}
?>