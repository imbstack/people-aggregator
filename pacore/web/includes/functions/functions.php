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
define("CONTENT_CONTENT", 1);
include_once dirname(__FILE__).'/../../../api/DB/Dal/Dal.php';
//require_once "api/LoginCookie/LoginCookie.php";
require_once "api/InputSanitizer/InputSanitizer.php";
require_once "api/ReportAbuse/ReportAbuse.php";
require_once "web/includes/classes/FormHandler.php";
require_once "api/Login/PA_Login.class.php";
//return information about the current network
function get_network_info($network_name = null) {
    if($network_name) {
        return Network::get_network_by_address($network_name);
    }
    elseif(defined('CURRENT_NETWORK_URL_PREFIX')) {
        return Network::get_network_by_address(CURRENT_NETWORK_URL_PREFIX);
    }
    else {
        return Network::get_network_by_address('default');
        // if CURRENT_NETWORK_URL_PREFIX not defined
        //  return mother network info
    }
}

function get_path_to_root() {
    global $path_to_root;
    if(@$path_to_root) {
        return $path_to_root;
    }
    return '.';
}

/* Check for existing login session, redirecting to the login page if
* necessary.
*
* If $login_required and there isn't a session, check for an auth
* cookie and set up the session from there.
*
* If $login_required === "password" and there isn't a session, go
* straight to the login screen.  (This is for places like
* edit_profile.php, which shouldn't be accessible unless you have
* recently entered your password.)
*/
function check_session($login_required = TRUE, $redirect_function = NULL) {
    $msg = __("Sorry - you are not logged in or you have been logged out due to inactivity. Please, log in again.");
    session_start();
    // clear old cookies from earlier PA versions
    foreach(array("pa_username", "pa_password") as $name) {
        if(isset($_COOKIE[$name])) {
            setcookie($name, "", 0, "/");
        }
    }
    if(empty($_SESSION['user'])) {
        // no current session; see if we can auto-login from a cookie
        try {
            PA_Login::process_cookie();
        }
        catch(PAException$e) {
            // log, but otherwise silently drop it on the floow
            Logger::log("Exception occurred processing login cookie: ".$e->getTraceAsString());
        }
    }
    $not_logged_in = FALSE;
    if(empty($_SESSION['user'])) {
        $not_logged_in = TRUE;
        $msg = 'error=1';
    }
    elseif($login_required === "password" && $_SESSION['login_source'] != "password") {
        $not_logged_in = TRUE;
        $msg = 'msg='.urlencode("For your security, you must enter your password to access this page.");
    }
    if($not_logged_in) {
        // redirect to login page if login is required
        if($login_required) {
            if($redirect_function) {
                return $redirect_function();
            }
            header("Location: ".PA::$url."/login.php?".$msg."&return=".urlencode($_SERVER['REDIRECT_URL'].'?'.@$_SERVER['REDIRECT_QUERY_STRING']));
        }
        return 0;
    }
    else {
        ob_start();
        $time = gmdate('D, d M Y H:i:s').'GMT';
        header("Last-Modified: $time");
        header("Expires: $time");
        header("Pragma: no-cache");
        return 1;
    }
}

function register_session($login_name, $user_id, $role, $first_name, $last_name, $email, $picture = NULL) {
    @session_start();
    $_SESSION['user']['name']       = $login_name;
    $_SESSION['user']['id']         = $user_id;
    $_SESSION['user']['role']       = $role;
    $_SESSION['user']['first_name'] = $first_name;
    $_SESSION['user']['last_name']  = $last_name;
    $_SESSION['user']['email']      = $email;
    $_SESSION['user']['picture']    = $picture;
}

function has_html(&$s) {
    return(preg_match('/<[^>]+>/', $s)) ? true : false;
}

function chop_string($string, $length = 30, $link = "") {
    // global var $_base_url has been removed - please, use PA::$url static variable
    if(has_html($string)) {
        $san = new InputSanitizer();
        $san->passthrough = TRUE;
        // we want no HTML filtering here
        $return = $san->process($string, $length);
    }
    else {
        $return = substr($string, 0, $length);
        if(strlen($string) > $length) {
            $return .= "..";

            /*if($length >= DESCRIPTION_LENGTH && !empty($link)) {
            $return .= "<br><a href='".$link."' class='forums-module'>read more..</a>";
            }*/
        }
    }
    $return = nl2br($return);
    return $return;
}

function filter_all_post(&$post_array, $strip_all_tags = FALSE, $allow_tags_everywhere = FALSE) {
    $filt = Validation::get_input_filter($strip_all_tags);
    if($allow_tags_everywhere) {
        $filt->htmlAllowedEverywhere = TRUE;
    }
    $post_array = $filt->process($post_array);
}
//Function will take the comma separated tags as argument and return the array of these comma seprated tags
function tags_string_to_array($tagstring) {
    $tags = array();
    if(strlen($tagstring) > 0) {
        $tags_array = explode(",", $tagstring);
        foreach($tags_array as $value) {
            $tags[] = $value;
        }
    }
    return $tags;
}
// Function will take an array as argument and return the delimiter separated string
function tags_array_to_string($tagarray, $delimiter = ',') {
    $tagstring = "";
    if(count($tagarray) > 0) {
        for($counter = 0; $counter < count($tagarray); $counter++) {
            $tagstring .= $tagarray[$counter]['name'].$delimiter;
        }
        $tagstring = substr($tagstring, 0, strlen($tagstring)-1);
    }
    return $tagstring;
}

/*  This function is used to displat the formatted ouput.Following things will be handled by it
- Will split the String to chunks.
- Will Strip slashes
*/
function display_sanitized($body, $length = CHUNK_LENGTH) {
    $body = stripslashes($body);
    $body = chunk_split($body, $length);
    return $body;
}
//This function checks the mime type of file
//purpose e.g. if we change abc.pdf to abc.gif then it will cause GD crash
//So we can check it actually
if(!function_exists('mime_content_type')) {

    function mime_content_type($f) {
        //$output = system ( trim( 'file -bi ' . escapeshellarg ( $f ) ) ) ;
        $output = exec(trim('file -bi '.escapeshellarg($f)));
        return $output;
    }
}

/**
* function used to check permissions for user to do an activity
* @param $params is array of parameters like $params['action'], $param['uid']..
*/

/*
function user_can( $params ) {
  global $login_uid;
  $action = $params['action'];


  switch( $action ) {
    case 'edit_content':
    case 'delete_content':
      if( $params['uid'] && $params['cid'] ) {

        //super admin can edit/ delete any content
        if( $params['uid'] == SUPER_USER_ID ) {
          return true;
        }

        // network owner can edit / delete any content in a network
        if( Network::is_admin( PA::$network_info->network_id, $params['uid'] ) ) {
          return true;
        }

        //Loading content
        $content_obj = Content::load_content((int)$params['cid'], $params['uid'] );

        //author of the content can perform the action
        if( $content_obj->author_id == $params['uid'] ) {
          return true;
        }

        if( $content_obj->parent_collection_id != -1 ) { // content is a part of some collection
          // Loading collection
          $collection_obj = ContentCollection::load_collection((int)$content_obj->parent_collection_id, $params['uid'] );

          // owner of collection can also edit the content
          if ( $collection_obj->author_id == $params['uid'] ) {
            return true;
          }

        }

      }
      break;
    case 'delete_comment': // used in deletecomment.php
      //network owner can delete any comment
      $comment = $params['comment_info'];//array having the comment details

      if ($login_uid == SUPER_USER_ID) { //Super user can delete any comment
        return true;
      } else if (PA::$network_info->owner_id == $login_uid) {                              //Network owner can delete the comment
        return true;
      } else if (isset($comment['user_id']) and ($comment['user_id'] == $login_uid)) { //Author of comment can delete the comment
        return true;
      } else if (isset($comment['recipient_id']) and ($comment['recipient_id'] == $login_uid)) {
        return true;
      }

      $content = Content::load_content((int)$comment['content_id'], $login_uid);
      if ($content->author_id == $login_uid) { //Author of the content can delete the comment.
        return true;
      } else if ($content->parent_collection_id != -1) { // means content belongs to some collection

        $collection = ContentCollection::load_collection($content->parent_collection_id, $login_uid);
        if ($collection->author_id == $login_uid) {//If content on which comment has been posted belongs to some collection then author of that collection can delete the comment
          return true;
        }
      }
      return false;// return false in all the other cases
      break;
    case 'edit_forum': // Edit Forum - used in old Forums
      $perm_array = array(PA::$network_info->owner_id, SUPER_USER_ID, $params['group_owner'], $params['forum_owner']);
      return in_array($login_uid, $perm_array);
      break;

    case 'delete_rep': // Delete the Replies of forum - used in old Forums
    $perm_array = array(PA::$network_info->owner_id, SUPER_USER_ID, $params['group_owner'], $params['forum_owner'], $params['rep_owner']);
    return in_array($login_uid, $perm_array);
    break;

    case 'view_group_content': // not used!
      if ($params['allow_anonymous']) return true;
      $perm_array = array (PA::$network_info->owner_id, SUPER_USER_ID, $params['group_owner']);
      $member_type = array (MEMBER, MODERATOR, OWNER);
      if (in_array($login_uid, $perm_array) || in_array($params['member_type'], $member_type))
      return true;
      break;

    case 'view_abuse_report_form':
      if(empty($login_uid)) return false;
      $extra = unserialize(PA::$network_info->extra);
      $pram = $extra['notify_owner']['report_abuse_on_content']['value'];
      if (isset($pram) && ($pram > 0) ) return true;
      return false;
      break;

    case 'delete_comment_authorization':
      $perm_array = array(PA::$network_info->owner_id, SUPER_USER_ID,
      @$params['group_owner'], $params['content_owner'], $params['comment_owner']);
      return in_array($login_uid, $perm_array);
      break;
*/
/* celebrity feature was removed
case 'delete_item_message':
  if ($params['uid'] && $params['id']) {
    //super admin can edit/ delete any content
    if($params['uid'] == SUPER_USER_ID) {
      return true;
    }
    // network owner can edit / delete any content in a network
    if(Network::is_admin(PA::$network_info->network_id, $params['uid'])) {
      return true;
    }
    $celebrity         = new Celebrity();
    $celebrity->id     = $params['id'];
    $celebrity_message = $celebrity->load_message();
    if (!empty($celebrity_message)) {
      if ($celebrity_message['user_id'] == $params['uid']) {
        return true;
      }
    }
  }
  break;
 */
/*
  }

  return false;
}
*/
function group_user_authentication($group_id) {
    global $login_uid;
    $access_array = array();
    $access_array['style'] = "";
    if(!empty($login_uid)) {
        $user_type = Group::get_user_type($login_uid, $group_id);
        $group_var = new Group();
        $group_var->load($group_id);
        switch(trim($user_type)) {
            case NOT_A_MEMBER:
                if($group_var->reg_type == REG_MODERATED) {
                    $access_array['hyper_link'] = PA::$url.PA_ROUTE_GROUP."/action=join&amp;gid=$group_id";
                    $access_array['caption']    = 'Request to join';
                    $access_array['style']      = "style=\"width:160px;\"";
                }
                else {
                    $access_array['hyper_link'] = PA::$url.PA_ROUTE_GROUP."/action=join&amp;gid=$group_id";
                    $access_array['caption'] = 'Join';
                }
                break;
            case MEMBER:
                $access_array['hyper_link'] = PA::$url.PA_ROUTE_GROUP."/action=leave&amp;gid=$group_id";
                $access_array['caption'] = 'Unjoin';
                break;
            case OWNER:
                $access_array['hyper_link'] = PA::$url."/addgroup.php?gid=$group_id";
                $access_array['caption'] = 'Edit';
                break;
            case MODERATOR:
                $access_array['hyper_link'] = PA::$url.PA_ROUTE_GROUP_MODERATION."/view=content&gid=$group_id";
                $access_array['caption']    = 'Moderate';
                $access_array['style']      = "style=\"width:80px;\"";
                break;
        }
    }
    else {
        $access_array['hyper_link'] = PA::$url.PA_ROUTE_GROUP."/action=join&amp;gid=$group_id";
        $access_array['caption'] = 'Join';
    }
    return $access_array;
}

/**
    This function convert object into array
    some time we are using this type of variable
    '$links[$i]->login_name' convert these type of variable into array
  */
function objtoarray($data) {
    $cnt = count($data);
    if($cnt > 0) {
        $return_array = array();
        for($i = 0; $i < $cnt; $i++) {
            if(is_object($data[$i])) {
                foreach($data[$i] as $k => $v) {
                    $return_array[$i][$k] = $v;
                }
            }
            else {
                $return_array[$i] = $data[$i];
            }
        }
        return $return_array;
    }
    return $data;
}

/**
    This function is created for the sorting of array
    ie array( 0=> array( 'members'=>2, 'owner' => 'pa'), 1=> array('members'=>1, 'owner' => 'xyz'))
    Now we want to sort the array in the basis of owner , then we use this function
   */
function sortByFunc(&$arr, $func, $direc = 'asc', $change_the_key = NULL) {
    if(empty($arr)) {
        return;
    }
    $tmpArr = array();
    foreach($arr as $k => &$e) {
        $tmpArr[] = array(
            'f' => $func($e),
            'k' => $k,
            'e' => &$e,
        );
    }
    if($direc == 'desc') {
        arsort($tmpArr);
    }
    else {
        sort($tmpArr);
    }
    $arr = array();
    foreach($tmpArr as &$fke) {
        if($change_the_key) {
            $arr[] = &$fke['e'];
        }
        else {
            $arr[$fke['k']] = &$fke['e'];
        }
    }
}

/**
   * set the variables for the class
   * @param $modulename name of module, in which we create a form
   */

/* Function for handling the post data. Call this function on the top of set_up function of web page */
function handle_post($action_file = null) {
    global $global_form_data, $global_form_error;
    unset($global_form_data);
    unset($global_form_error);
    if(!empty($_POST['form_handler'])) {
        $msg_handler                    = new FormHandler();
        $msg_handler->block_module_name = $_POST['form_handler'];
        $msg_handler->action_file       = $action_file;
        $msg_handler->manage_post();
    }
    return;
}

/**
   * set the variables for the class
   * @param $msg_array takes a array of messages
   * @param $on_success set for redirection after success . if you want to change the page
   * @param $on_failure set for redirection after failure . if you want to change the page
   * @param $query_str while redirection if any Query string exits
  */

/* Call this function at the end of action.php file in the block module */
function set_web_variables($msg_array, $redirect_url = NULL, $query_str = NULL, $unset_array = NULL) {
    $msg_handler               = new FormHandler();
    $msg_handler->msg          = $msg_array;
    $msg_handler->redirect_url = $redirect_url;
    $msg_handler->query_str    = $query_str;
    $msg_handler->unset_array  = $unset_array;
    $msg_handler->handle_post_data();
}

function total_abuse($id, $type) {
    $report_abuse_obj              = new ReportAbuse();
    $report_abuse_obj->parent_type = $type;
    $report_abuse_obj->parent_id   = $id;
    $result                        = $report_abuse_obj->get_multiples();
    return count($result);
}

function is_task_available($user_id, $task, $mothership_only = TRUE) {
    $return = FALSE;
    if($mothership_only && PA::$network_info->type == MOTHER_NETWORK_TYPE) {
        $permission = Roles::check_permission($user_id, Tasks::get_id_from_task_value($task));
        if($permission == TRUE) {
            $return = TRUE;
        }
    }
    return $return;
}

/**
  * $file_path => physical path of the file eg. /usr/local/apache2/htdocs/paalpha/web/files
   * @return array of csv fields when TRUE false other wise
  */
function parse_csv_file($file_path, $get_first_row = FALSE) {
    //  $return = array('error'=>FALSE, 'message'=>NULL);
    if(file_exists(PA::$project_dir.DIRECTORY_SEPARATOR.$file_path)) {
        //specified file does not exists
        $file_path = PA::$project_dir.DIRECTORY_SEPARATOR.$file_path;
    }
    elseif(file_exists(PA::$core_dir.DIRECTORY_SEPARATOR.$file_path)) {
        $file_path = PA::$core_dir.DIRECTORY_SEPARATOR.$file_path;
    }
    else {
        return false;
    }
    if(!$handle = fopen($file_path, "r")) {
        //unable to open file
        return FALSE;
    }
    else {
        $data = array();
        $first_row = fgetcsv($handle, 1000, ",");
        if($get_first_row == TRUE) {
            return $first_row;
        }
        //array_push($data, $first_row);
        while(($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            array_push($data, $row);
        }
    }
    return $data;
}

/**
  * fnmatch() function used in in_wildCardArray() not available in Windows
  * so, we will emulate it in that case
  */
if(!function_exists('fnmatch')) {

    function fnmatch($pattern, $string) {
        return @preg_match('/^'.strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')).'$/i', $string);
    }
}

/**
  * @author   Z.Hron
  * @name     fnmatch_array
  * @brief    This function allows wildcards in the array of patterns to be searched
  * @return   bool
  *
  *
  * @example
  *
  *
  *  <code>
  *
  *   $patterns = array('*.hotmail.com');
  *   $domain    = 'anything.hotmail.com';
  *   if (fnmatch_array($domain, $patterns)) $banned = true;
  *
  *  </code>
  *
  */
function fnmatch_array($needle, $haystack) {
    foreach($haystack as $value) {
        if(fnmatch($value, $needle) === true) {
            return true;
        }
    }
    return false;
}

/**
  * @author   Z.Hron
  * @name     delete_users
  * @brief    This function delete array of users
  * @return   array of error messages
  *
  *
  */
function delete_users($params, $true_delete = FALSE) {
    $message_array = array();
    $user_id_array = $params['user_id_array'];
    foreach($user_id_array as $user_id) {
        if(PA::$network_info->type == MOTHER_NETWORK_TYPE) {
            if(!Network::member_exists(PA::$network_info->network_id, (int) $user_id)) {
                $message_array[] = "UserID $user_id does not exists.";
                continue;
            }
            //deleting user data from mothership
            try {
                User::delete_user($user_id);
                Activities::delete_for_user($user_id);
            }
            catch(PAException$e) {
                $message_array[] = $e->message;
            }
            $user_networks = Network::get_user_networks($user_id);
            if(count($user_networks)) {
                foreach($user_networks as $network) {
                    if($network->user_type != NETWORK_OWNER) {
                        $network_prefix = $network->address;
                        try {
                            User::delete_user($user_id);
                            Activities::delete_for_user($user_id);
                            Network::leave($network->network_id, $user_id);
                            //leave
                        }
                        catch(PAException$e) {
                            $message_array[] = $e->message;
                        }
                    }
                    else {
                        try {
                            Network::delete($network->network_id);
                        }
                        catch(PAException$e) {
                            $message_array[] = $e->message;
                        }
                    }
                }
            }
            //deleting user
            try {
                User::delete($user_id, $true_delete);
            }
            catch(PAException$e) {
                Logger::log('User has been already deleted');
            }
        }
        else {
            //user delete for network owner
            if(!Network::member_exists(PA::$network_info->network_id, (int) $user_id)) {
                $message_array[] = "UserID $user_id does not exists.";
                continue;
            }
            $network_prefix = PA::$network_info->address;
            try {
                User::delete_user($user_id);
                Activities::delete_for_user($user_id);
                Network::leave(PA::$network_info->network_id, $user_id);
                //network leave
            }
            catch(PAException$e) {
                $message_array[] = $e->message;
            }
        }
    }
    return $message_array;
}

/**
  * @author   Z.Hron
  * @name     is_valid_web_image_name
  * @brief    This function return true if a file name is valid WEB image name
  * @return   bool
  *
  *
  */
function is_valid_web_image_name($filename) {
    if(empty($filename)) {
        return true;
    }
    // no image name - that is allowed
    $filename = Storage::getFilename($filename);
    // map file id to filename if in storage
    $result = false;
    $valid_exts = array(
        'jpg',
        'jpeg',
        'gif',
        'png',
    );
    $fname = strtolower(htmlspecialchars($filename));
    $ext = end(explode('.', $fname));
    if(in_array($ext, $valid_exts)) {
        $result = true;
    }
    return $result;
}

/**
  * @author   Z.Hron
  * @name     add_querystring_var
  * @brief    This function append a key=>value at the end of query string
  * @return   url
  *
  *
  */
function add_querystring_var($url, $key, $value) {
    $url = preg_replace('/(.*)(\?|&)'.$key.'=[^&]+?(&)(.*)/i', '', $url.'&');
    $url = substr($url, 0,-1);
    if(strpos($url, '?') === false) {
        return($url.'?'.$key.'='.$value);
    }
    else {
        return($url.'&'.$key.'='.$value);
    }
}

/**
  * @author   Z.Hron
  * @name     get_url_for
  * @brief    This function build query string from array of key=>value params
  * @return   url
  *
  *
  */
function get_url_for($url, $params) {
    foreach($params as $key => $value) {
        $url = add_querystring_var($url, $key, $value);
    }
    return $url;
}

function get_tag_string($content_id) {
    $tags_array  = Tag::load_tags_for_content($content_id);
    $tags_string = "";
    $count       = count($tags_array);
    if($count > 0) {
        for($counter = 0; $counter < $count; $counter++) {
            $tags_string .= $tags_array[$counter]['name'].", ";
        }
        $tags_string = substr($tags_string, 0, strlen($tags_string)-2);
    }
    return $tags_string;
}
?>