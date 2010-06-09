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

require_once 'JSON/JSON.php';

function json_encode($arg)
{
	global $services_json;
	if (!isset($services_json)) {
		$services_json = new Services_JSON();
	}
	return $services_json->encode($arg);
}

function json_decode($arg)
{
	global $services_json;
	if (!isset($services_json)) {
		$services_json = new Services_JSON();
	}
	return $services_json->decode($arg);
}

?>
