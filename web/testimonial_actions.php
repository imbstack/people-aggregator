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

$login_required = TRUE;//for session protection pages 
include_once("web/includes/page.php");
require_once "api/Testimonials/Testimonials.php";

global  $login_uid;
// function for approve
function approved_testimonial($testimonial_id) {
  $testi = new Testimonials();
  $testi->testimonial_id = $testimonial_id;
  $testi->status = APPROVED;
  $testi->change_status();

}

// function for delete
function delete_testimonial($testimonial_id) {
  $testi = new Testimonials();
  $testi->testimonial_id = $testimonial_id;
  $testi->delete_testimonial();
}

// function for deny
function deny_testimonial($testimonial_id) {
  $testi = new Testimonials();
  $testi->testimonial_id = $testimonial_id;
  $testi->status = DENIED;
  $testi->change_status();

}

global $page_uid;
if($_GET['action']) {
  $testi = new Testimonials();
  $testi->testimonial_id = $_GET['id'];
  $result = $testi->get();
  
  // handle all the action here
  if($login_uid != $result['recipient_id']) {
    $location = PA::$url . PA_ROUTE_USER_PRIVATE . '/' . "msg_id=9010";
  }
  
  if($_GET['action'] == 'deny') {
    deny_testimonial($testi->testimonial_id);
    $location = PA::$url . PA_ROUTE_USER_PRIVATE . '/' . "msg_id=9012";
  }
  
  if($_GET['action'] == 'approve') {
    approved_testimonial($testi->testimonial_id);
    $location = PA::$url . PA_ROUTE_USER_PRIVATE . '/' . "msg_id=9011";
  }

  if($_GET['action'] == 'delete') {
    delete_testimonial($testi->testimonial_id);
    $login = User::get_login_name_from_id($page_uid);
    $current_url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login . '/msg_id=9014';
/*    
    $url_perms = array('current_url' => $current_url,
                              'login' => $login
                            );
    $url = get_url(FILE_USER_BLOG, $url_perms);
    $location = $url;
*/    
    $location = $current_url;
  }
  header("Location: $location");
  exit;
}

?>