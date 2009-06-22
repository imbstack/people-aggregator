<?php
require_once "web/api/lib/ixr_xmlrpc.php";
/***************************************************************************\
      PHP BLOGGER API IMPLEMENTATION
      ==============================
 The following functions implement the methods available via the Blogger
 XML-RPC API. They are intended to provide a back-end to web-based systems
 using PHP as the preferred language, and should give you full control over
 blogs and the Blogger API.

 They are based on the PHP XML-RPC library which availabled from
 http://xmlrpc.usefulinc.com/ and which is included in this distribution.

 The author of this library is Beau Lebens, the Primary Consultant of
 DentedReality, which you can get more info on at www.dentedreality.com.au.

 Please email Beau at beau@dentedreality.com.au with comments/feedback/bugs
 with this library.

 Resources/Links;
 ----------------
 Blogger: http://www.blogger.com/
 Blogger API: http://plant.blogger/api/
 Get a Blogger AppID: http://plant.blogger.com/api/register.html
 DentedReality: http://www.dentedreality.com.au/
 PHP XML-RPC: http://xmlrpc.usefulinc.com/
 FireStarter Technologies: http://www.firestarter.com.au/
\***************************************************************************/



/***************************************************************************\
        BLOGGER VARIABLES
 Unless Evan changes the server details, the only variable here that you
 should need to change is $BLOGGER_APPID, which you should change to your
 own custom APPID, which can be obtained from the Blogger site at;
 http://plant.blogger.com/api/register.html
 Alternatively, the appid below is the one I registered for the development
 of this library, so I guess you could just use that ;)
\***************************************************************************/

global $BLOGGER_APPID;
global $BLOGGER_SERVER;
global $BLOGGER_PATH;

$BLOGGER_APPID  = "YOU MUST SET THIS TO YOUR BLOGGER API KEY";
$BLOGGER_SERVER = "plant.blogger.com";
$BLOGGER_PATH   = "/api/RPC2";


/***************************************************************************\
       BLOGGER API METHOD EMULATION FUNCTIONS
 Here is the main code, each of the functions is named after its Blogger API
 method, with the "." replaced with a "_". Simply call the function named
 after the API method you want, and it will operate. They are slightly
 simplified because there are a couple global variables, so check which vars
 are required by these functions before using them first.
\***************************************************************************/
// Return an array of arrays containing information about the blogs
// to which the specified user/pass combo has access.
// $blogs[] = $blog;
// $blog["url"] = url to blog;
// $blog["blogName"] = the name of the blog;
// $blog["blogid"] = the blog's id;
function blogger_getUsersBlogs($username, $password) {
  global $BLOGGER_APPID;

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create variables to send in the message
  $XMLappID = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");

  // Construct query for the server
  $getBlogsRequest = new xmlrpcmsg("blogger.getUsersBlogs", array($XMLappID, $XMLusername, $XMLpassword));

  // Send the query
  $result_struct = $blogClient->send($getBlogsRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result_array = xmlrpc_decode($values);

    // Check the result for error strings.
    $valid = blogger_checkFaultString($result_array);

    // Return something based on the check
    if ($valid === true) {
      return $result_array;
    }
    else {
      return $valid;
    }
  }
  else {
     return $result_struct->faultString();
  }

}

/************************************************************************************/
// This grabs information about a user.
// You have to know their username and password....
function blogger_getUserInfo($username, $password) {
  global $BLOGGER_APPID;

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create the variables that form the request
  $XMLappid = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");

  // Construct the actual request message
  $userInfoRequest = new xmlrpcmsg("blogger.getUserInfo", array($XMLappid, $XMLusername, $XMLpassword));

  // Now send the request
  $result_struct = $blogClient->send($userInfoRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result_array = xmlrpc_decode($values);

    // Check the result for error strings.
    $valid = blogger_checkFaultString($result_array);

    // Return something based on the check
    if ($valid === true) {
      return $result_array;
    }
    else {
      return $valid;
    }
  }
  else {
     return $result_struct->faultString();
  }
}

/************************************************************************************/
// Retrieves details of the last "x" posts on this blog
function blogger_getRecentPosts($blogid, $username, $password, $numPosts) {
  global $BLOGGER_APPID;

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create the variables that form the request
  $XMLappid = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLblogid  = new xmlrpcval($blogid, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");
  $XMLnumPosts  = new xmlrpcval($numPosts, "int");

  // Construct the actual request message
  $recentPostsRequest = new xmlrpcmsg("blogger.getRecentPosts", array($XMLappid, $XMLblogid, $XMLusername, $XMLpassword, $XMLnumPosts));

  // Now send the request
  $result_struct = $blogClient->send($recentPostsRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result_array = xmlrpc_decode($values);

    // Check the result for error strings.
    $valid = blogger_checkFaultString($result_array);

    // Return something based on the check
    if ($valid === true) {
      return $result_array;
    }
    else {
      return $valid;
    }
  }
  else {
     return $result_struct->faultString();
  }
}

/************************************************************************************/
// This returns the data for a particular post
function blogger_getPost($postid, $username, $password) {
  global $BLOGGER_APPID;

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create the variables that form the request
  $XMLappid = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLpostid  = new xmlrpcval($postid, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");

  // Construct the actual request message
  $getPostRequest = new xmlrpcmsg("blogger.getPost", array($XMLappid, $XMLpostid, $XMLusername, $XMLpassword));

  // Now send the request
  $result_struct = $blogClient->send($getPostRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result_array = xmlrpc_decode($values);

    // Check the result for error strings.
    $valid = blogger_checkFaultString($result_array);

    // Return something based on the check
    if ($valid === true) {
      return $result_array;
    }
    else {
      return $valid;
    }
  }
  else {
     return $result_struct->faultString();
  }
}

/************************************************************************************/
// This posts a new blog to the specified blog.
// If not specified as true, then the publish var defaults to false, and the
// blog is not published (updated) after this post
function blogger_newPost($blogid, $username, $password, $content, $publish=false) {
  global $BLOGGER_APPID;

  // Convert common synonyms for true so that we have a proper boolean
  if ($publish == "true" || $publish == "1" || $publish == "yes") {
    $publish = true;
  }

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create the variables that form the request
  $XMLappid = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLblogid  = new xmlrpcval($blogid, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");
  $XMLcontent = new xmlrpcval($content, "string");
  $XMLpublish = new xmlrpcval($publish, "boolean");

  // Construct the actual request message
  $newPostRequest = new xmlrpcmsg("blogger.newPost", array($XMLappid, $XMLblogid, $XMLusername, $XMLpassword, $XMLcontent, $XMLpublish));

  // Now send the request
  $result_struct = $blogClient->send($newPostRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result = xmlrpc_decode($values);

    // Return something based on the check
    if (is_array($result)) {
      return blogger_checkFaultString($result);
    }
    else {
      return $result;
    }
  }
  else {
     return $result_struct->faultString();
  }
}

/************************************************************************************/
// Delete a post - duh!
// returns 1 on success, 0 on failure
function blogger_deletePost($postid, $username, $password, $publish=false) {
  global $BLOGGER_APPID;

  // Convert common synonyms for true so that we have a proper boolean
  if ($publish == "true" || $publish == "1" || $publish == "yes") {
    $publish = true;
  }

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create the variables that form the request
  $XMLappid = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLpostid  = new xmlrpcval($postid, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");
  $XMLpublish = new xmlrpcval($publish, "boolean");

  // Construct the actual request message
  $deletePostRequest = new xmlrpcmsg("blogger.deletePost", array($XMLappid, $XMLpostid, $XMLusername, $XMLpassword, $XMLpublish));

  // Now send the request
  $result_struct = $blogClient->send($deletePostRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result = xmlrpc_decode($values);

    // Return something based on the check
    if (is_array($result)) {
      return blogger_checkFaultString($result);
    }
    else {
      return $result;
    }
  }
  else {
     return $result_struct->faultString();
  }
}

/************************************************************************************/
// Updates a post by storing a new string over the top of it
function blogger_editPost($postid, $username, $password, $publish=false, $string) {
  global $BLOGGER_APPID;

  // Convert common synonyms for true so that we have a proper boolean
  if ($publish == "true" || $publish == "1" || $publish == "yes") {
    $publish = true;
  }

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create the variables that form the request
  $XMLappid = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLpostid  = new xmlrpcval($postid, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");
  $XMLpublish = new xmlrpcval($publish, "boolean");
  $XMLstring  = new xmlrpcval($string, "boolean");

  // Construct the actual request message
  $editPostRequest = new xmlrpcmsg("blogger.editPost", array($XMLappid, $XMLpostid, $XMLusername, $XMLpassword, $XMLstring, $XMLpublish));

  // Now send the request
  $result_struct = $blogClient->send($editPostRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result = xmlrpc_decode($values);

    // Return something based on the check
    if (is_array($result)) {
      return blogger_checkFaultString($result);
    }
    else {
      return $result;
    }
  }
  else {
     return $result_struct->faultString();
  }
}

/************************************************************************************/
// Grabs the contents of either the "main" or "archiveIndex" template and return them
// in a string. By default the function returns all "<" converted to "&lt;" but I have
// reversed this, so the string that you get from this should be "as-is". If you are
// displaying in a textarea or something like that, then you should use the PHP func
// htmlspecialchars() to parse the result and make it HTML-Friendly :)
function blogger_getTemplate($blogid, $username, $password, $template="main") {
  global $BLOGGER_APPID;

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create the variables that form the request
  $XMLappid = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLblogid  = new xmlrpcval($blogid, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");
  $XMLtemplate  = new xmlrpcval($template, "string");

  // Construct the actual request message
  $getTemplateRequest = new xmlrpcmsg("blogger.getTemplate", array($XMLappid, $XMLblogid, $XMLusername, $XMLpassword, $XMLtemplate));

  // Now send the request
  $result_struct = $blogClient->send($getTemplateRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result_array = xmlrpc_decode($values);

    // Return something based on the check
    if (!is_array($result_array)) {
      $result_array = str_replace("&lt;", "<", $result_array);
      return $result_array;
    }
    else {
      return false;
    }
  }
  else {
     return $result_struct->faultString();
  }
}

/************************************************************************************/
// Sets the new contents of either the archiveIndex or main template to the string
// that you pass to it. Also checks to make sure that there are <Blogger> and
// </Blogger> tags in the string, otherwise returns an error.
function blogger_setTemplate($blogid, $username, $password, $template="main", $string) {
  global $BLOGGER_APPID;

  if (strpos($string, "<Blogger>") === false || strpos($string, "</Blogger>") === false) {
    return "Invalid template, must contain <Blogger> and </Blogger> tags.";
    exit;
  }

  // Connect to blogger server
  if (!($blogClient = blogger_connectToBlogger())) {
    return false;
    exit;
  }

  // Create the variables that form the request
  $XMLappid = new xmlrpcval($BLOGGER_APPID, "string");
  $XMLblogid  = new xmlrpcval($blogid, "string");
  $XMLusername  = new xmlrpcval($username, "string");
  $XMLpassword  = new xmlrpcval($password, "string");
  $XMLtemplate  = new xmlrpcval($template, "string");
  $XMLstring  = new xmlrpcval($string, "string");

  // Construct the actual request message
  $setTemplateRequest = new xmlrpcmsg("blogger.setTemplate", array($XMLappid, $XMLblogid, $XMLusername, $XMLpassword, $XMLstring, $XMLtemplate));

  // Now send the request
  $result_struct = $blogClient->send($setTemplateRequest);

  // Check the results for an error
  if (!$result_struct->faultCode()) {
    // Get the results in a value-array
    $values = $result_struct->value();

    // Compile results into PHP array
    $result_array = xmlrpc_decode($values);

    // Return something based on the check
    if (!is_array($result_array)) {
      $result_array = str_replace("&lt;", "<", $result_array);
      return $result_array;
    }
    else {
      return false;
    }
  }
  else {
     return $result_struct->faultString();
  }
}

/***************************************************************************\
        CUSTOM FUNCTIONS
 Here are a couple functions that I also added in here. They are not
 derivatives of the actual API, but I found them useful so I included them
 here anyway :)
\***************************************************************************/
// Return the HTML required to make a form select element which is made up in the form
// $select[$blogid] = $blogName;
// If the user only has one blog, then it return a string containing the name of the blog
// in plain text, with a hidden form input containing the blogid, using the same
// $name as it would have for the select
function blogger_getUsersBlogsSelect($getUsersBlogsArray, $name="blog", $selected="", $extra="") {
  foreach($getUsersBlogsArray as $blog) {
    if (is_string($blog)) {
      return false;
    }
    $blogs_select[$blog["blogid"]] = $result_array = str_replace("&lt;", "<", $blog["blogName"]);
  }
  if (sizeof($blogs_select) > 1) {
    return display_select($name, $blogs_select, $selected, $extra);
  }
  else {
    return $getUsersBlogsArray[0]["blogName"] . " <input type=\"hidden\" name=\"$name\" value=\"" . $getUsersBlogsArray[0]["blogid"] . "\">";
  }
}

/************************************************************************************/
// Gets an array of posts from the specified user in the last "x"
function blogger_getUserRecentPosts($blogid, $username, $password, $numUserPosts, $checkInPosts) {
  // Get all the posts from 0->$checkInPosts
  $posts = blogger_getRecentPosts($blogid, $username, $password, $checkInPosts);

  if (is_array($posts)) {
    // get info on the user so we know which ones to filter
    $user = blogger_getUserInfo($username, $password);
    $userid = $user["userid"];

    // Now pull out each post that belongs to this user, until $numPosts is reached
    $post_num = 0;
    $user_posts = array();
    foreach ($posts as $post) {
      if ($post["userid"] == $userid && $post_num < $numUserPosts) {
        $user_posts[] = $post;
      }
      $post_num++;
    }
    if (sizeof($user_posts) > 0) {
      return $user_posts;
    }
    else {
      return false;
    }
  }
  else {
    return $posts;
  }
}


/***************************************************************************\
        HELPER FUNCTIONS
 These functions are here as "helpers" to other functions, so you
 shouldn't need to call them directly during normal use of this library
\***************************************************************************/
// A generic debugging function, parses a string/int or array and displays contents
// in an easy-to-read format, good for checking values during a script's execution
function debug($value) {
  $counter = 0;
  echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"border: solid 1px #000000; background: #EEEEEE; width: 95%; margin: 20px;\" align=\"center\">\n";
  echo "<tr>\n<td colspan=\"3\" style=\"font-family: Arial; font-size: 13pt; font-weight: bold; text-align: center;\">Debugging Information</td>\n</tr>\n";
  if ( is_array($value) ) {
    echo "<tr>\n<td>&nbsp;</td>\n<td><b>Array Key</b></td>\n<td><b>Array Value</b></td>\n</tr>\n";
    foreach($value as $key=>$val) {
      if (is_array($val)) {
        debug($val);
      }
      else {
        echo "<tr>\n<td>$counter</td>\n<td>&nbsp;" . $key . "&nbsp;</td>\n<td>&nbsp;" . $val . "&nbsp;</td>\n</tr>\n";
      }
      $counter++;
    }
  }
  else {
    echo "<tr>\n<td colspan=\"3\">" . $value . "</td>\n</tr>\n";
  }
  echo "</table>\n";
}

/************************************************************************************/
// Returns a connection object to the blogger server
function blogger_connectToBlogger() {
  global $BLOGGER_APPID, $BLOGGER_SERVER, $BLOGGER_PATH;

  // Connect to blogger server
  if($blogClient = new xmlrpc_client($BLOGGER_PATH, $BLOGGER_SERVER)) {
    return $blogClient;
  }
  else {
    return false;
  }
}

/************************************************************************************/
// Checks a blogger result array for the existence of the "faultString" keyword
// and if it's in there, returns the string error, otherwise, returns true;
function blogger_checkFaultString($bloggerResult) {
  if ($bloggerResult["faultString"]) {
    return $bloggerResult["faultString"];
  }
  else if (strpos($bloggerResult, "java.lang.Exception") !== false) {
    return $bloggerResult;
  }
  else {
    return true;
  }
}

/************************************************************************************/
// This function was originally written by Troy Laurin of FireStarter Technologies
// www.firestarter.com.au
// Modified by Beau Lebens for this library.
function display_select($name, $options, $value = 0, $misc = "unset") {
  $select = "<select";
  if (strlen($name)) {
    $select .= " name=\"" . $name . "\"";
  }
  if (is_array($misc)) {
    while (list($id, $val) = each($misc)) {
      $select .= " " . $id . "=\"" . $val . "\"";
    }
  }
  $select .= ">";
  if (is_array($options)) {
    while (list($id, $val) = each($options)) {
      $select .= "\n<option";
      $select .= " value=\"" . $id . "\"";
      if (strcmp($id, $value))
        $select .= ">";
      else
        $select .= " selected>";
      $select .= htmlspecialchars($val) . "</option>";
    }
  }
  $select .= "\n</select>\n";
  return $select;
}
function usersubs_get_blog_id($data,$blogurl){
  $diff_blogs = array();
  foreach($data as $oneblog) {
    $diff_blogs[] = $oneblog["url"];
    if( $oneblog["url"] == $blogurl )
    return $oneblog["blogid"];
  }
  return 0;
}
/***************************************************************************\
        END OF LIBRARY
\***************************************************************************/




/***************************************************************************\
        TEST OPERATIONS
\***************************************************************************/
// Enter values for the variables below, then you can test this library by
// simly uncommenting any of the calls to the functions below. To see the
// result, uncomment the last line "debug($data);" and then you will see what
// the queries return.

/*
$TEST_USERNAME  = ""; // Plain-text username
$TEST_PASSWORD  = ""; // Plain-text password (don't freak out, just remove it befor going live!)
$TEST_BLOG  = ""; // A blog id to work with
$TEST_POST  = ""; // A post id, Warning: this gets deleted if you test blogger_deletePost();
$TEST_NUM = 15; // Change this to anything up to and including 20
$TEST_PUBLISH = false; // true or false (or 1 or 0)
*/

//$connection = blogger_connectToBlogger();
//$data = blogger_getUsersBlogs($TEST_USERNAME, $TEST_PASSWORD);
//$data = blogger_getUsersBlogsSelect($data); // Must uncomment line above as well.
//$data = blogger_getUserInfo($TEST_USERNAME, $TEST_PASSWORD);
//$data = blogger_getRecentPosts($TEST_BLOG, $TEST_USERNAME, $TEST_PASSWORD, $TEST_NUM);
//$data = blogger_getUserRecentPosts($TEST_BLOG, $TEST_USERNAME, $TEST_PASSWORD, 3, $TEST_NUM);
//$data = blogger_getPost($TEST_POST, $TEST_USERNAME, $TEST_PASSWORD);
//$data = blogger_newPost($TEST_BLOG, $TEST_USERNAME, $TEST_PASSWORD, "Test post using the PHP Blogger API Implementation by Beau Lebens!", $TEST_PUBLISH);
//$data = blogger_deletePost($TEST_POST, $TEST_USERNAME, $TEST_PASSWORD, $TEST_PUBLISH);
//$data = blogger_getTemplate($TEST_BLOG, $TEST_USERNAME, $TEST_PASSWORD, "main");
//$data = blogger_getTemplate($TEST_BLOG, $TEST_USERNAME, $TEST_PASSWORD, "archiveIndex");

// blogger_setTemplate() is not tested here because it causes big problems if you do it by mistake
// just trust me - it works.;)

// Output some detail on what we got!
//debug($data);
?>