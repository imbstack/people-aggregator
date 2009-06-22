<?php

// xml-rpc code
require_once "web/api/lib/ixr_xmlrpc.php";
// api stuff
require_once "web/api/lib/api_common.php";
// logging
require_once "api/Logger/Logger.php";

define("XMLRPC_AUTH_FAILURE", 4);

function fault($errno, $msg)
{
    $err = new IXR_Error(42, htmlspecialchars($msg));
    echo $err->getXml();
    exit;
}

function api_error($msg, $code='system_error')
{
    Logger::log("XML-RPC api_error occurred: code $code, message $msg");
    $r = new IXR_Value(
        array(
            'success' => FALSE,
            'msg' => $msg,
            'code' => $code,
            ));
    $xml = $r->getXml();

    echo <<<EOD
<methodResponse>
<!-- this is an xml-rpc endpoint; try accessing it with an xml-rpc client.  more info: http://www.xmlrpc.com/ -->
  <params>
    <param>
      <value>
        $xml
      </value>
    </param>
  </params>
</methodResponse>

EOD;
    exit;
}

function xmlrpc_error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{
    if ($errno & (E_NOTICE | E_STRICT)) return;
    fault(-99, "PHP error $errno: $errstr at line $errline in $errfile");
}

function xmlrpc_ob_end($content)
{
    $start = substr($content, 0, 5);
    if ($start != "<"."?xml" && $start != "<meth") {
	// may be an error - wrap it up
	$err = new IXR_Error(99, htmlspecialchars("System error: ".$content));
	return  $err->getXml();
    }
    return $content;
}

class PA_XMLRPC extends IXR_Server {
    function call($methodname, $args) {
        Logger::log("XML-RPC call: $methodname");

        list($methodname, $func_desc, $php_func) = api_get_function_descriptor($methodname);

	// make sure $args is a 1-elem array containing a hash
	if (gettype($args) != "array")
	    api_error("Parameters should be in an array", 'validation_request_wrapper');
        switch ($func_desc['argstyle']) {
        case 'positional':
            $arg = array();
            $argorder = $func_desc['argorder'];
            if (count($args) != count($argorder))
                api_error("Incorrect number of arguments; expected ".count($argorder), 'validation_incorrect_number_of_arguments');

            for ($i = 0; $i < count($args); ++$i) {
                $arg[$argorder[$i]] = $args[$i];
            }
            break;
        case 'named':
            if (sizeof($args) != 1)
                api_error("You should only send a single parameter in your XML-RPC request: a struct, containing all the required keys.", 'validation_request_wrapper');

            $arg = $args[0];
            if (gettype($arg) != "array")
                api_error("Expected a single parameter containing an XML-RPC struct, but got a value of type '".gettype($arg)."' instead.", 'validation_request_wrapper');
            break;
        default:
            api_error("Invalid argument style ".$func_desc['argstyle']);
        }

	//	var_dump($func_desc['args']);
	// validate the struct
	validate_content($arg,
	    array("type" => "hash",
		"content" => $func_desc['args']),
            "input to XML-RPC function",
            "auto");

        // call function, capturing any output - which might include errors
	ob_start("xmlrpc_ob_end");
	try {
	    $ret = $php_func($arg);
	    // check output
	    if ($ret['success']) validate_content($ret, $func_desc['return'], "XML-RPC response - not your fault!", "auto");
	} catch (PAException $e) {
	    $ret = api_err_from_exception($e);
            Logger::log("An exception occurred in an API call: code ".$e->getCode().", message ".$e->getMessage()."\n".$e->getTraceAsString(), LOGGER_ERROR);
	}
        
        return $ret;
    }
}

// Is this actually a REST call?
if ($_SERVER['REQUEST_METHOD'] == "GET" || preg_match("/urlencoded/", @$_SERVER['CONTENT_TYPE']))
{
    require_once "lib/rest.php";

    header("Content-Type: application/xml");

    list($ret, $func_desc) = handle_rest_call();
    
    $r = new IXR_Value($ret);
    $xml = $r->getXml();
    
    echo <<<EOD
<methodResponse>
  <params>
    <param>
      <value>
        $xml
      </value>
    </param>
  </params>
</methodResponse>

EOD;
}
else
{
    set_error_handler("xmlrpc_error_handler");
    new PA_XMLRPC();
}

?>