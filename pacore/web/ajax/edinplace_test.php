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
require_once("JSON.php");

$str = html_entity_decode($_REQUEST['value'], ENT_QUOTES);
//echo json_encode(array("msg"=>"success","result"=>"Stored: $str")); 
echo json_encode(array("msg"=>"success","result"=>"$str")); 

?>