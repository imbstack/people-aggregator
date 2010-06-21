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

error_reporting(E_ALL);
$login_required = TRUE;
include_once("web/includes/page.php");
require_once "ext/JSON.php";
$t = new Testbench();
$t->main();
exit;

class Testbench {

    public static $valid_modules = array(
        // test
        'EchoTestModule' => '',
        'ReviewModule' => '',
        // homepage
        'MembersFacewallModule'      => '',
        'EventCalendarSidebarModule' => '',
        'ImagesModule'               => '',
        'NetworkDefaultLinksModule'  => '',
        'PopularTagsModule'          => '',
        'LogoModule'                 => '',
        'NewestGroupsModule'         => '',
        'RecentPostModule'           => '',
        'NetworkStatisticsModule'    => '',
        'TakerATourModule'           => '',
        'AdsByGoogleModule'          => '',
        // user page
        'UserPhotoModule'      => '',
        'AboutUserModule'      => '',
        'InRelationModule'     => '',
        'RecentCommentsModule' => '',
        'FlickrModule'         => 'needs javascript work',
        'LinkModule'           => 'needs javascript work',
        'RelationsModule'      => '',
        'MyGroupsModule'       => '',
        'MyLinksModule'        => '',
        'MyNetworksModule'     => '',
        'ProfileFeedModule'    => '',
        // messaging
        'MessageModule' => "needs javascript and form post work",
        'AddMessageModule' => "needs javascript and form post work",
        // events
        'EventCalendarModule' => 'needs javascript and form post work',
    );

    function main() {
        if(!@PA::$config->enable_widgetization_testbench) {
            ?>

<p>Widgetization testbench must be enabled specifically by setting <code>PA::$config->enable_widgetization_testbench = TRUE</code> in local_config.php.</p>

<?php
            exit;
        }
        $module_name = @$_GET['module'];
        if(empty($module_name)) {
            $this->show_modules();
            return;
        }
        $module_view = @$_GET['view'];
        if(empty($module_view)) {
            $module_view = "default";
        }
        $json = new Services_JSON();
        // prefix to mark parameters as being for this module
        $param_prefix = "w_".$module_name."_";
        // parameters to pass through to the backend
        $params = array();
        // GET or POST?
        $method = 'get';
        // If we got an HTTP POST, mark this as so
        if($_SERVER['REQUEST_METHOD'] == 'POST' && $_GET['wop'] == 'post') {
            $method = 'post';
        }
        // Collect parameters and pass them through
        foreach($_REQUEST as $k => &$v) {
            if(strpos($k, $param_prefix) !== 0) {
                continue;
            }
            $params[substr($k, strlen($param_prefix))] = &$v;
        }
        $request = array(
            'modules' => array(
                array(
                    'id'           => 1,
                    'name'         => $module_name,
                    'method'       => $method,
                    'post_url'     => PA::$url."/widget_testbench.php?module=$module_name&wop=post",
                    'param_prefix' => $param_prefix,
                    'args' => array(
                        'view' => $module_view,
                        'position' => 'center',
                    ),
                    'params' => $params,
                ),
            ),
            'global' => array(
                'namespace' => 'testbench',
                'user' => array(
                    'user_id'    => "pa_".PA::$login_user->user_id,
                    'email'      => "testbench+".PA::$login_user->email,
                    'first_name' => PA::$login_user->first_name,
                    'last_name'  => PA::$login_user->last_name,
                ),
            ),
        );
        $request_url = PA::$url."/widget_dispatch.php";
        if(!preg_match("|^http://(.*?)(/.*)$|", $request_url, $m)) {
            die("couldn't parse url");
        }
        list(, $request_host, $request_path) = $m;
        $request_json                        = $json->encode($request);
        $post                                = "POST $request_path HTTP/1.0
Host: $request_host
Connection: close
Content-Type: application/x-javascript
Content-Length: ".strlen($request_json)."

$request_json
";
        // actually perform POST
        $fs = fsockopen($request_host, 80, $errno, $errstr, 4);
        if(!$fs) {
            $response = array(
                "error" => "Failed to connect to widget server",
            );
        }
        else {
            fputs($fs, $post);
            $response_raw = "";
            while(!feof($fs)) {
                $resp = fread($fs, 8192);
                if($resp === FALSE) {
                    break;
                }
                $response_raw .= $resp;
            }
            fclose($fs);
            list($response_headers, $response_body) = explode("\r\n\r\n", $response_raw, 2);
            $response = $json->decode($response_body);
        }
        $tpl = new Template(PA::$theme_path.'/widget_testbench_widget.tpl');
        $tpl->set("module_name", $module_name);
        $tpl->set("post", $post);
        $tpl->set("response_raw", $response_raw);
        $tpl->set_object("response", $response);
        echo $tpl->fetch();
    }

    function show_modules() {
        $tpl = new Template(PA::$theme_path.'/widget_testbench_index.tpl');
        $tpl->set("modules", Testbench::$valid_modules);
        echo $tpl->fetch();
    }
}
?>