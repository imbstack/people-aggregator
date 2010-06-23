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

include dirname(__FILE__)."/../config.inc";
require_once "api/User/ShadowUser.php";
require_once "api/Theme/Template.php";
require_once "ext/JSON.php";
require_once "web/BetaBlockModules/Module/Module.php";
require_once "web/includes/functions/functions.php";
require_once "web/includes/uihelper.php";

class WidgetException extends Exception {
}

class WidgetServer {
    function handle_request() {
	$json = new Services_JSON();
	try {
	    global $HTTP_RAW_POST_DATA;
	    if (!@PA::$config->enable_widgetization_server)
		$this->fail("Widget server is not enabled; you must set \PA::$config->enable_widgetization_server = TRUE in local_config.php.");
	    if ($_SERVER['REQUEST_METHOD'] != 'POST')
		$this->fail("This URL handles POST requests only");
	    if ($_SERVER['CONTENT_TYPE'] != 'application/x-javascript')
		$this->fail("Content-Type of application/x-javascript required");

	    // Parse input
	    $request = $json->decode($HTTP_RAW_POST_DATA);
	    if ($request == NULL) $this->fail("Null request");

	    $this->global = $request->global;

	    // Set up globals - network, user etc
	    if (!empty($this->global->user)) {
		PA::$login_user = new ShadowUser($this->global->namespace);
		// see if we can load it already
		if (! PA::$login_user->load($this->global->user->user_id)) {
		  // wasn't here before, so we create a shadow account
		  PA::$login_user = ShadowUser::create($this->global->namespace, $this->global->user, PA::$network_info);
		}
		
		PA::$login_uid = PA::$login_user->user_id;
	    }

	    // This should probably be in config.inc.  For the moment
	    // we figure out the network based on the URL, as with the
	    // rest of the system.
	    PA::$network_info = get_network_info();

	    // Render modules
	    $modules = array();
	    foreach ($request->modules as $req_module) {
		$module = array();
		$module['id'] = $req_module->id;
		$module['name'] = $name = $req_module->name;

		$params = array();
		foreach ($req_module->params as $k => $v) {
		    $params[$k] = $v;
		}

		// dispatch module
		ob_start();
		$module['html'] = $this->render_module(
		    $req_module->method, // 'get' or 'post'
		    $req_module->name, // module name
		    $req_module->args, // config arguments
		    $params, // user-supplied parameters
		    $req_module->post_url, // url to POST to if you want to post back to yourself
		    $req_module->param_prefix); // prefix for input parameters and textareas
		$errors = ob_get_contents();
		ob_end_clean();
		if (!empty($errors)) $module['errors'] = $errors;

		$modules[] = $module;
	    }
	    $response = array(
		'modules' => $modules,
		);

	    header("Content-Type: application/x-javascript");
	    echo $json->encode($response);

	} catch (WidgetException $e) {
	    echo $json->encode(array("error" => $e->getMessage()));
	}
    }

    function render_module($method, $name, $args, $params, $post_url, $param_prefix) {
	// validate name
	if (!preg_match("/^[A-Za-z]+$/", $name))
	    $this->fail("Invalid module name: $name");

	// Note that we explicitly don't check the module against any
	// allow list -- the asumption is that any PA install running
	// as a widget server is secured behind a firewall of some
	// sort, and the client is trusted (this is required, anyway,
	// as the client has to be able to force the user ID).  At
	// some point we should add in an authentication token (simple
	// shared secret, in local_config.php or in the DB), to make
	// the firewall less necessary.

	$module_path = "web/BetaBlockModules/$name";
	$module_controller = "$module_path/$name.php";

	// setup module
	require_once $module_controller;
	$obj = new $name;
	$obj->uid = PA::$login_uid;

	// display settings
	if (preg_match("/^([a-zA-Z0-9\_]+)$/", $args->view, $m))
	    $obj->view = $m[1];

	// widgetization-specific settings
	$obj->widgetized = TRUE;
	$obj->post_url = $post_url;
	$obj->param_prefix = $param_prefix;
	$obj->params = $params;

	// handle a POST request
	if ($method == 'post') {
	    if (!method_exists($obj, "render_for_post"))
		$this->fail("Unable to handle POST request; $name has no render_for_post() method");
	    return $obj->render_for_post();
	}

	return $obj->render();
    }

    function fail($msg) {
	throw new WidgetException($msg);
    }
}

$s = new WidgetServer();
$s->handle_request();

?>