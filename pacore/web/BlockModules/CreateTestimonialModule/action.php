<?php
/** !
* action.php is a part of PeopleAggregator.
* Action.php is part of CreateTestimonialModule. It is responsible for dealing
* with creating the Testimonials object.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Tekriti Software
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/
require_once "api/Testimonials/Testimonials.php";
global $page_uid, $login_uid;
if($_form) {
    $testi               = new Testimonials();
    $testi->sender_id    = $login_uid;
    $testi->recipient_id = $page_uid;
    $testi->body         = $_form['body'];
    try {
        $id = $testi->save();
    }
    catch(PAException$e) {
        $msg = $e->message;
        $code = $e->code;
    }
}
// Here we call the function
$msg_array                = array();
$msg_array['failure_msg'] = $msg;
$msg_array['success_msg'] = 9013;
$login                    = User::get_login_name_from_id($page_uid);
$current_url              = PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$login;

/*
$current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$page_uid;
$url_perms = array('current_url' => $current_url,
                    'login' => $login                  
                  );
$url = get_url(FILE_USER_BLOG, $url_perms);
$redirect_url = $url;
*/
$redirect_url = $current_url;
set_web_variables($msg_array, $redirect_url);
?>
