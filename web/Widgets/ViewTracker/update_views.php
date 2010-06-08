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