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
// validation code
require_once "web/api/lib/api_common.php";

function rest_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    if($errno&(E_NOTICE| E_STRICT)) {
        return;
    }
    api_error("PHP error $errno: $errstr at line $errline in $errfile");
}

function handle_rest_call() {
    global $api_desc;
    set_error_handler("rest_error_handler");
    $magic = ini_get("magic_quotes_gpc");
    $path = explode('/', $_SERVER['PATH_INFO']);
    while(sizeof($path) && !$path[0]) {
        array_shift($path);
    }
    // get function name
    $funcname = implode(".", $path);
    // find function descriptor
    list($funcname, $func_desc, $php_func) = api_get_function_descriptor($funcname);
    // check request method
    if(strtolower($_SERVER['REQUEST_METHOD']) != $func_desc['type']) {
        api_error("Request method ".$_SERVER['REQUEST_METHOD']." is incorrect for this function; ".strtoupper($func_desc['type'])." is required.", 'invalid_request_method');
    }
    // check and parse input
    $args_desc = $func_desc['args'];
    $args = array();
    foreach($args_desc as $arg_name => $arg_desc) {
        // get value
        $v = @$_REQUEST[$arg_name];
        if($v === NULL) {
            continue;
        }
        // missing keys will be detected during validation
        // massage it into required type
        $arg_type = $arg_desc['type'];
        switch($arg_type) {
            case 'string':
            case 'enum':
                if($magic) {
                    $v = stripslashes($v);
                }
                break;
            case 'boolean':
                switch($v) {
                    case 'true':
                        $v = TRUE;
                        break;
                    case 'false':
                        $v = FALSE;
                        break;
                    default:
                        api_error("Invalid boolean value '$v' (must be 'true' or 'false') passed as argument $arg_name to function $funcname", "validation_invalid_value");
                }
            
            break;
            case 'int' : $v = intval($v);
            break;
        case 'float' : $v = floatval($v);
        break;
    default : api_error("Argument type '$arg_type' not supported in REST mode");
}
// and store
$args[$arg_name] = $v;
}
// validate input
validate_content($args, array("type" => "hash", "content" => $func_desc['args']), "REST input", "auto");
// call function
try {
    $ret = $php_func($args);
    // check output type
    if(gettype($ret) != 'array') {
        api_error("Returned data from $php_func should be an array, but received ".gettype($ret)." instead.");
    }
    $validate = TRUE;
    if($path[0] == 'peopleaggregator') {
        if(!array_key_exists("success", $ret)) {
            api_error("Missing 'success' field in returned data from $php_func.");
        }
        if(!$ret['success']) {
            $validate = FALSE;
        }
    }
    // check output
    if($validate) {
        validate_content($ret, $func_desc['return'], "REST response", "auto", true);
    }
}
catch(PAException$e) {
    $ret = api_err_from_exception($e);
    Logger::log("An exception occurred in an API call: code ".$e->getCode().", message ".$e->getMessage()."\n".$e->getTraceAsString(), LOGGER_ERROR);
}
// if we got this var, it validates
return array($ret, $func_desc);
}
?>