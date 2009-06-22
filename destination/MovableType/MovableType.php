<?php
/**
 * Class for routing content.
 *
 * @package BlogSpot
 * @author Tekriti Software (http://www.tekritisoftware.com)
 */

require_once "web/api/lib/ixr_xmlrpc.php";
require_once "web/includes/blogger.php";

class MovableType {

  /**
  * The default constructor for message class.
  */
  public function __construct() {
    return;
  }
  
  public function send($post, $blogurl, $username, $password) {
    if ($post && $blogurl && $username && $password) {
      $title = $post['title'];
      $description = $post['body'];
      $selected_blog = $blogurl."/mt-xmlrpc.cgi";
      $client = new IXR_Client("$selected_blog");
      $content['title'] = $title; 
      $content['description'] = $description;     
      
      if (!$client->query('metaWeblog.newPost', 1, $username, $password, $content, 1)) {
        die('Something went wrong - '. $client->getErrorCode() . ' : '. $client->getErrorMessage());
      }
    }
  }
}
?>