<?php
error_reporting(E_ALL);
$login_required = FALSE;
include_once("web/includes/page.php");
include_once("web/Widgets/ViewTracker/ViewTracker.php");

$error = null;
if ($_POST) {
    $obj = new ViewTracker();
  try {
    $obj->set_type($_POST['type']);  
    $obj->title = $_POST['title'];
    $obj->url = $_POST['url'];
    $obj->time_stamp = time();
    $obj->save();
  } catch (PAException $e) {
    print ('Type does not exist');
  }  
}

?>