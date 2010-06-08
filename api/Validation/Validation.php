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
require_once "api/InputSanitizer/InputSanitizer.php";

/* Code to validate various things.
 *
 * Originally in web/includes/functions/validation.php, but moved here
 * so we can use it from other api functions.
 */

class Validation {

    /**
     * get rid of whitespace at the start and the end of a url, and add http:// to the start if not present.
     * @param string url given by user
     */
    public static function validate_url($url) {
        $url = trim($url);
        if (!$url) return NULL;
        if (!preg_match("/^https?\:\/\//i", $url)) {
            $url = "http://$url";
        }
        return $url;
    }

    public static function validate_alpha_numeric($alphanum, $allow_spaces = 0) {
        if($allow_spaces == 1) {
            $regex = '[^A-Za-z0-9][[:space:]]';
        } else {
            $regex = '[^A-Za-z0-9]';
        }
        if (ereg($regex, $alphanum)) {
            return false;
        }
        else {
          return true;
        }            
    } 
     
    public static function validate_name($name_str) {
      if(preg_match('/^[^\x00-\x1F]+$/', $name_str)) {
        return true;
      }
      return false;
    }
    
    public static function validate_auth_id($id_str) {
    	// the login nmae needs to consist of any run of NON WHITESPACE chars
    	if (preg_match("/^(\S)+$/", $id_str)) return true;
    	else return false;
    }

    public static function validate_email($email_address, $chk_dns = false) {
      $isValid = true;
      $atIndex = strrpos($email_address, "@");
      if(is_bool($atIndex) && !$atIndex) {
        $isValid = false;
      }
      else {
        $domain = substr($email_address, $atIndex +1);
        $local  = substr($email_address, 0, $atIndex);
        $localLen  = strlen($local);
        $domainLen = strlen($domain);
        
        if($localLen < 1 || $localLen > 64) {
          // local part length exceeded
          $isValid = false;
        }
        else if($domainLen < 1 || $domainLen > 255) {
          // domain part length exceeded
          $isValid = false;
        }
        else if($local[0] == '.' || $local[$localLen-1] == '.') {
          // local part starts or ends with '.'
          $isValid = false;
        }
        else if(preg_match('/\\.\\./', $local)) {
          // local part has two consecutive dots
          $isValid = false;
        }
        else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
          // character not valid in domain part
          $isValid = false;
        }
        else if(preg_match('/\\.\\./', $domain)) {
          // domain part has two consecutive dots
          $isValid = false;
        }
        else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
          // character not valid in local part unless 
          // local part is quoted
          if(!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
            $isValid = false;
          }
        }
        
        if($isValid && $chk_dns) {
          if(!(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
            // domain not found in DNS
            $isValid = false;
          }
        }  
      }
      return $isValid;
    }

    public static function is_ascii_printable($str) {
      if(preg_match("/^[\x20-\x7e]+$/", $str)) {
        return true;
      }
      return false;
    }
    
    /**
    * Function to check whether the given URL is valid or not
    * return TRUE if valid 
    */
    public static function isValidURL($url)
    {
      return preg_match('|^http(s)?://[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }


    /* used by get_input_filter() */
    private static function get_no_filter_arrays() {
	$tags = array('ul');
	$attr = array();
	$filter = array($tags, $attr);
	return $filter;
    }
    private static function get_valid_filter_arrays_for_custumize() {
	$tags = array('strong', 'em', 'u', 'b', 'i', 'strike', 'sub', 'sup',
		      'ul', 'li', 'ol',
		      'a', 'img',
		      'p', 'blockquote', 'span', 
		      'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
		      'br', 'hr',
		      // for embedding flash widgets:
		      "object", "param", "embed",
		      );
	$attr = array('href', 'src', 'width', 'height', 'alt', 'align',
		      // for embedding flash widgets:
		      "name", "value", "type", "wmode", "allowfullscreen", "allowscriptaccess",
		      // for tinyMCE Insert Link dialog
		      'target', 'title', 'style',
		      );
	$filter = array($tags, $attr);
	return $filter;
    }

    /* used by filter_all_post() in web/functions.php */
    public static function get_input_filter($strip_all_tags = FALSE) {
	//$filter = get_no_filter_arrays();
	if($strip_all_tags) {
	    $filter = Validation::get_no_filter_arrays();
	} else {
	    $filter = Validation::get_valid_filter_arrays_for_custumize();
	}
	return new InputSanitizer($filter[0], $filter[1]);
    }

}

?>