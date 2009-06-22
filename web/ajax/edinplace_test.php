<?php 
require_once("JSON.php");

$str = html_entity_decode($_REQUEST['value'], ENT_QUOTES);
//echo json_encode(array("msg"=>"success","result"=>"Stored: $str")); 
echo json_encode(array("msg"=>"success","result"=>"$str")); 

?>