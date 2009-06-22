<?php
include_once dirname(__FILE__) . "/../../config.inc";
require_once "web/api/lib/rest.php";

define("API_XMLNS", "http://peopleaggregator.com/api/xmlns#");

function api_error($msg, $code='system_error')
{
    echo '<response xmlns="'.API_XMLNS.'"><success>false</success><code>'.$code.'</code><msg>'.htmlspecialchars($msg).'</msg></response>';
    exit;
}

// this assumes the data has been validated (see lib/rest.php) - take
// care!
function encode_xml($v, $desc, $path='')
{
    switch (gettype($v)) {
    case 'boolean':
	return $v ? 'true' : 'false';
    case 'int':
	return $v;
    case 'array':
	// test for numeric indices (based on json code)
	if (count($v) && (array_keys($v) !== range(0, sizeof($v) - 1))) {
	    // it's a hash
	    $keys = array();
	    $h_desc = $desc['content'];
	    foreach ($v as $k => $kv) {
		array_push($keys, "<$k>".encode_xml($kv, $h_desc[$k], "$path/$k")."</$k>");
	    }
	    return implode("", $keys);
	} else {
	    // it's an array
	    $items = array();
	    $a_desc = $desc['item'];
	    foreach ($v as $i) {
		array_push($items, "<item>".encode_xml($i, $a_desc, "$path/item")."</item>");
	    }
	    return implode("", $items);
	}
	break;
    default:
	return htmlspecialchars($v);
    }
}

header("Content-Type: application/xml");

echo '<'.'?xml version="1.0"?'.">\n";
list($ret, $func_desc) = handle_rest_call();
echo '<response xmlns="'.API_XMLNS.'">'.encode_xml($ret, $func_desc['return']).'</response>';
?>