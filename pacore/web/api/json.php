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

include_once dirname(__FILE__) . "/../../config.inc";
require_once "web/api/lib/rest.php";
require_once "ext/JSON.php";

function json_encode_string($s) {
    $s = str_replace("\\", "\\\\", $s);
    $s = str_replace("\n", "\\n", $s);
    $s = str_replace('"', '\\"', $s);
    return $s;
}

function api_error($msg, $code='system_error')
{
    $json = new Services_JSON();
    echo $json->encode(array(
        'success' => FALSE,
        'code' => $code,
        'msg' => $msg,
        ));
    exit;
}

header("Content-Type: application/x-javascript; charset=UTF-8");

list($ret, $func_desc) = handle_rest_call();

$json = new Services_JSON();
echo $json->encode($ret);

?>