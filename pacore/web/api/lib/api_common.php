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

// api descriptor, auto-generated with webapiwrappergen.py
require_once PA::$core_dir . "/web/api/lib/api_desc.php";
// actual implementations of api functions
require_once PA::$core_dir . "/web/api/lib/api_impl.php";

$type_map = array(
    'int' => 'integer',
    'hash' => 'array',
    'enum' => 'string',
    'binary' => 'string',
    'datetime' => 'object', // api methods should output IXR_Date objects for date and datetime
    'date' => 'object',
    );

// From http://www.phpwact.org/php/i18n/charsets - tests if a string
// is already encoded with UTF-8.
function utf8_compliant($str) {
    if ( strlen($str) == 0 ) {
        return TRUE;
    }
    // If even just the first character can be matched, when the /u
    // modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
    // invalid, nothing at all will match, even if the string contains
    // some valid sequences
    return (preg_match('/^.{1}/us',$str,$ar) == 1);
}

// Convert strings into utf-8.  If you have the mbstring extension
// installed, this will work fairly well to convert lots of charsets.
// if not, it will only do iso-8859-1.  RECOMMENDATION: Install
// mbstring if you expect to receive input in anything other than
// iso-8859-1 or utf-8.
function api_force_utf8($v, $src_encoding) {
    if (utf8_compliant($v)) {
        $ret = $v;
    }
    else {
        // assume iso-8859-1
        $ret = utf8_encode($v);
    }
    return $ret;
}

// set $convert_output to true to convert IXR_Date into '2006-05-22' etc
function validate_content(&$v, $desc, $context, $src_encoding, $convert_output=false, $path='')
{
    if (!$src_encoding) $src_encoding = "auto";

    global $type_map;

    $t = gettype($v);
    $expected_type = array_key_exists($desc['type'], $type_map) ? $type_map[$desc['type']] : $desc['type'];
    if ($t != $expected_type) {
        api_error("Validation error ($context): expected type $expected_type at position '$path', got $t", 'validation_incorrect_type');
    }

    switch ($desc['type'])
    {
    case 'hash':
        // check that all specified keys are correct
        $content = $desc['content'];
        foreach ($content as $k_name => $k_desc) {
            // if the key is missing:
            // - do nothing if it's marked as optional,
            // - replace with the default if one is given,
            // - otherwise fail with validation_missing_key
            if (!array_key_exists($k_name, $v)) {
                if (@$k_desc["optional"]) {
                    continue;
                }
                $dflt = @$k_desc['default'];
                if ($dflt === NULL) {
                    api_error("Validation error ($context, $path): key $k_name is required", 'validation_missing_key');
                }
                $v[$k_name] = $dflt;
            }
            validate_content($v[$k_name], $k_desc, $context, $src_encoding, $convert_output, "$path/$k_name");
        }
        if (!$desc['allow_extra_keys']) {
            // check for unknown (not allowed) keys
            foreach ($v as $k_name => $k_value) {
                if (!array_key_exists($k_name, $content)
                    && strpos($k_name, "__") !== 0) {
                    api_error("Validation error ($context, $path): key $k_name is not allowed", 'validation_extra_key');
                }
            }
        }
        break;
    case 'array':
        foreach ($v as &$i) {
            validate_content($i, $desc['item'], $context, $src_encoding, $convert_output, "$path/item");
        }
        break;
    case 'enum':
        if (!in_array($v, $desc['values']))
            api_error("Validation error ($context, $path): '$v' is not a valid enumeration value at position '$path'");
        break;
    case 'int':
        $mi = @$desc['min'];
        if ($mi !== NULL && $v < $mi)
            api_error("Validation error ($context, $path): value at $path ($v) must be >= $mi", 'validation_out_of_range');
        $mx = @$desc['max'];
        if ($mx !== NULL && $v > $mx)
            api_error("Validation error ($context, $path): value at $path ($v) must be <= $mx", 'validation_out_of_range');
        break;
    case 'string':
        // convert char encoding
        $v = api_force_utf8($v, $src_encoding);
        break;
    case 'date':
	if (!($v instanceof IXR_Date))
	    api_error("Validation error ($context, $path): '$v' must be an IXR_Date object");
	$v = $v->year.'-'.$v->month.'-'.$v->day;
	break;
    case 'datetime':
	if (!($v instanceof IXR_Date))
	    api_error("Validation error ($context, $path): '$v' must be an IXR_Date object");
	$v = $v->getIso();
	break;
    }
}

function api_get_function_descriptor($funcname)
{
    global $api_desc;

    // find the function descriptor
    $func_desc = @$api_desc['methods'][$funcname];
    if (!$func_desc) {
        api_error("Call to nonexistent function '$funcname'.");
    }

    // if it's an alias, get the new method name and descriptor
    if (@$func_desc['alias']) {
        $funcname = $func_desc['alias'];
        $func_desc = @$api_desc['methods'][$funcname];
    }

    // now figure out the function name in PHP
    $php_func = str_replace(".", "_", $funcname);
    if (!function_exists($php_func)) {
        api_error("System error: Function '$php_func' should exist, but has not been implemented.");
    }

    return array($funcname, $func_desc, $php_func);
}
?>