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
$login_required = FALSE;
// global var $path_prefix has been removed - please, use PA::$path static variable
include_once("web/includes/page.php");
require_once "api/Advertisement/Advertisement.php";
if (!empty($_GET['ad_id'])) {
  $condition = array('ad_id' => $_GET['ad_id']);
  $res = Advertisement::get($params = NULL, $condition);
  $hit_count = $res[0]->hit_count;
  $hit_count++;
  $update_fields = array('hit_count' => $hit_count);
  Advertisement::update($update_fields, $condition);
}
?>