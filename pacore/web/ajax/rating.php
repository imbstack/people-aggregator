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
  $login_required = TRUE;
//including necessary files
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require 'api/Rating/Rating.php';
$Rating = new Rating();
if(!empty($_POST)) {
    $Rating->set_rating_type(@$_POST['rating_type']);
    $Rating->set_type_id(@(int) $_POST['type_id']);
    $Rating->set_rating(@(int) $_POST['rating']);
    $Rating->set_max_rating(@(int) $_POST['max_rating']);
    $Rating->set_user_id(@(int) $login_uid);
    $Rating->rate();
    $rating = rating(@$_POST['rating_type'], @(int) $_POST['type_id']);
    print $rating['overall'];
}
?>