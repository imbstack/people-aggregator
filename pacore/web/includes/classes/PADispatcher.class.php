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
/**
 * @Class PADispatcher
 *
 * This class dispatch WEB requests for: WSAPI calls, file
 * requests including request for PHP scripts, JS, CSS and
 * other file types.
 * And finally this class implements shadowing model for
 * these file types and handle file downloads.
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.2.1
 *
 * @note       Do not forget that this class will be created
 *             before any other class in the PA
 *
 */
define('F_NOT_FOUND'quote73"Requested file not found on this server.");
define('F_NO_PERMIS'quote73"You do not have permission to download this file.");
define('F_NOT_ALLOW'quote73"This file type is not allowed on this server");
define('F_STR_ERROR'quote73"PADownloadManager is unable to create file output stream.");
define('F_NO_ROUTE'quote73"PADispatcher is unable to resolve requested route.");

class PADispatcher {

    public $core_dir;

    public $project_dir;

    public $current_route;

    public $route_query_str;

    private $routes;

    private $dispatcher_scheme;

    private $routing_scheme;

    private $PCRE_MATCH_STRING;

    private $org_query_str;

    private $auto_load_list;

    public static $request_type;

    public function __construct($auto_load_list = array()) {
        $this->PCRE_MATCH_STRING = "!^([^?=]*)/(([^/?=]+)\.(asf|avi|css|csv|docx|doc|exe|cab|jar|gif|htc|html|htm|jpeg|jpg|json|js|mov|mp3|mpeg|mpg|pdf|php|png|pptx|ppt|rar|swf|txt|wav|wma|wmv|xml|xspf|zip))(.*)$!i";
        $this->core_dir          = PA_PROJECT_CORE_DIR;
        $this->project_dir       = PA_PROJECT_PROJECT_DIR;
        $this->routes            = array();
        $this->auto_load_list    = $auto_load_list;
        $this->current_route     = null;
        $this->route_query_str   = null;
        $this->storeServerData();
        $this->org_query_str     = $_SERVER['QUERY_STRING'];
        $this->dispatcher_scheme = sprintf('http%s', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == TRUE ? 's' : ''));
        $this->routing_scheme    = $this->dispatcher_scheme;
        $this->loadRedirectRules();
        self::$request_type = null;
    }

    public function dispatch() {
        $res_script = null;
        list($file_type, $file_path, $success) = $this->parseRequestURL();
        if($success) {
            switch($file_type) {
                case 'php':
                    self::$request_type = 'script';
                    if(!$script = getShadowedPath($file_path)) {
                        out_error404(F_NOT_FOUND, $file_path);
                    }
                    if(!$this->current_route) {
                        $this->current_route = $_SERVER['PHP_SELF'];
                        $this->route_query_str = $_SERVER['QUERY_STRING'];
                    }
                    else {
                        $this->route_query_str = $this->org_query_str;
                    }
                    $res_script = $this->bootApp($file_path);
                    break;
                default:
                    self::$request_type = 'file';
                    $download_manager = new PADownloadManager($file_path);
                    if(!$download_manager->getFile()) {
                        out_error404($download_manager->lastError(), $file_path);
                    }
                    exit;
            }
        }
        else {
            out_error404(F_NO_ROUTE);
        }
        return $res_script;
    }

    private function loadRedirectRules() {
        $prr_succes = @include_once($this->project_dir.DIRECTORY_SEPARATOR."redirect_rules.inc");
        $crr_succes = @include_once($this->core_dir.DIRECTORY_SEPARATOR."redirect_rules.inc");
        if($crr_succes) {
            $this->routes = array_merge($this->routes, $core_routes);
        }
        else {
            out_error_message('PADispatcher::loadRedirectRules() - Can\'t load Core rewriting rules!');
        }
        if($prr_succes) {
            if(isset($project_routes) && (count($project_routes) > 0)) {
                $rkeys = array_keys($this->routes);
                foreach($project_routes as $pr_key => $pr_val) {
                    $p_route = preg_split("/[\(]+|[\[]+/", $pr_key);
                    // split optional route params
                    foreach($rkeys as $key) {
                        // check is route overwritten by a project rewr. rule
                        if(false !== strpos($key, $p_route[0])) {
                            // yes - route is owerwritted
                            unset($this->routes[$key]);
                            // delete old route
                            break;
                        }
                    }
                    $this->routes[$pr_key] = $pr_val;
                    // add route rewritting rule
                }
            }
        }
    }

    private function bootApp($script) {
        global $app, $pa_page_render_start;
        $pa_page_render_start = microtime(TRUE);

        /* timing */
        if(!defined("PA_DISABLE_BUFFERING")) {
            ob_start("pa_end_of_page_ob_filter");
        }
        PA::$config      = new PA();
        PA::$project_dir = PA_PROJECT_PROJECT_DIR;
        PA::$core_dir    = PA_PROJECT_CORE_DIR;
        $app             = new BootStrap(PA_PROJECT_ROOT_DIR, $this->current_route, $this->route_query_str);
        $GLOBALS['app']  = $app;
        // make $app object available in global scope
        $app->loadConfigFile(APPLICATION_CONFIG_FILE);
        $app->autoLoadFiles($this->auto_load_list);
        $app->loadLanguageFiles();
        default_exception();
        // register default exception handler
        if(PA::$ssl_force_https_urls) {
            $this->routing_scheme = 'https';
        }
        if(PA::$ssl_security_on) {
            if($this->dispatcher_scheme != $this->routing_scheme) {
                $this->restoreServerData();
                header("Location: ".$this->routing_scheme."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
                // "$path_pref/$file_name" . $path_info . $guery_str);
                exit;
            }
        }
        if(PA::$profiler) {
            register_shutdown_function("show_profiler_statistic");
        }
        PA::$path      = PA_INSTALL_DIR;
        PA::$url       = PA_BASE_URL;
        PA::$remote_ip = $app->remote_addr;
        if(PA::$config->pa_installed) {
            $app->detectNetwork();
            $app->detectDBSettings();
            $app->getCurrentUser();
            if(($script != 'web/dologin.php') && (PA::$login_uid != SUPER_USER_ID) && (SITE_UNDER_MAINTAINENCE == 1)) {
                $script = "web/maintenance.php";
            }
        }
        else {
            $script = DEFAULT_INSTALL_SCRIPT;
        }
        ob_get_clean();
        return $script;
    }

    private function parseRequestURL() {
        $file_type = null;
        $file_path = null;
        $matches   = null;
        $match_url = $_SERVER['REQUEST_URI'];
        $this->applyRoutingRules($this->routes, $match_url, &$matches);
        if(empty($matches)) {
            $match_url = $_SERVER['REDIRECT_URL'];
            $this->applyRoutingRules($this->routes, $match_url, &$matches);
        }
        if(!empty($matches)) {
            $path_pref           = (isset($matches[1])) ? $matches[1] : null;
            $file_name           = $matches[2];
            $file_type           = strtolower($matches[4]);
            $url_info            = (isset($matches[5])) ? $matches[5] : null;
            $file_path           = "web".$path_pref.'/'.$file_name;
            $req_url_info        = @parse_url($url_info);
            $path_info           = (isset($req_url_info['path'])) ? $req_url_info['path'] : null;
            $guery_str           = null;
            $this->org_query_str = $_SERVER['QUERY_STRING'];
            if(isset($req_url_info['query'])) {
                $guery_str = $this->parseQueryString($req_url_info['query']);
            }
            elseif(isset($req_url_info['path'])) {
                $guery_str = $this->parseQueryString($req_url_info['path']);
            }
            elseif(isset($_SERVER['REDIRECT_QUERY_STRING'])) {
                $guery_str = $this->parseQueryString($_SERVER['REDIRECT_QUERY_STRING']);
            }
            if($file_type == 'php') {
                $_SERVER['SCRIPT_FILENAME'] = $file_path;
                $_SERVER['SCRIPT_NAME']     = "$path_pref/$file_name";
                $_SERVER['PHP_SELF']        = "$path_pref/$file_name".$path_info;
                $_SERVER['PATH_INFO']       = $path_info;
                $_SERVER['REQUEST_URI']     = "$path_pref/$file_name".$path_info.$guery_str;
                $_SERVER['QUERY_STRING']    = preg_replace("#[\?\&]*page_id\=[\d]+#"quote73"", $guery_str);
            }
        }
        return array($file_type, $file_path, ((count($matches) > 0) ? true : false));
    }

    private function storeServerData() {
        $this->org_serv_data['SCRIPT_FILENAME'] = $_SERVER['SCRIPT_FILENAME'];
        $this->org_serv_data['SCRIPT_NAME']     = $_SERVER['SCRIPT_NAME'];
        $this->org_serv_data['PHP_SELF']        = $_SERVER['PHP_SELF'];
        $this->org_serv_data['PATH_INFO']       = @$_SERVER['PATH_INFO'];
        $this->org_serv_data['REQUEST_URI']     = $_SERVER['REQUEST_URI'];
        $this->org_serv_data['QUERY_STRING']    = $_SERVER['QUERY_STRING'];
    }

    private function restoreServerData() {
        $_SERVER['SCRIPT_FILENAME'] = $this->org_serv_data['SCRIPT_FILENAME'];
        $_SERVER['SCRIPT_NAME']     = $this->org_serv_data['SCRIPT_NAME'];
        $_SERVER['PHP_SELF']        = $this->org_serv_data['PHP_SELF'];
        $_SERVER['PATH_INFO']       = @$this->org_serv_data['PATH_INFO'];
        $_SERVER['REQUEST_URI']     = $this->org_serv_data['REQUEST_URI'];
        $_SERVER['QUERY_STRING']    = $this->org_serv_data['QUERY_STRING'];
    }

    private function parseQueryString($query) {
        $query_string = "?".rtrim($query, "/?");
        $matches      = array();
        $arg_expr     = "#[\/\?\&]([^/&%]+\=[^\?&]+)#";
        if(preg_match_all($arg_expr, $query_string, $matches) && isset($matches[1])) {
            foreach($matches[1] as $param) {
                $param_info            = explode('=', $param);
                $param_name            = $param_info[0];
                $param_value           = $param_info[1];
                $_GET[$param_name]     = urldecode($param_value);
                $_REQUEST[$param_name] = urldecode($param_value);
            }
            $query_string = preg_replace('/(\?\&|\&\?|\?+|\&+)/i', '&', $query_string);
            return preg_replace('/(\&)/', '?', $query_string, 1);
        }
        return null;
    }

    private function applyRoutingRules($routes, $url, &$matches) {
        global $routing_scheme;
        if(false == preg_match($this->PCRE_MATCH_STRING, $url, $matches)) {
            foreach($routes as $expr_arr => $_route) {
                $expr_tmp = split(' ', $expr_arr);
                $_expr    = $expr_tmp[0];
                $_matches = array();
                if(true == preg_match('!'.$_expr.'!i', $url, $_matches)) {
                    if(!empty($expr_tmp[1])) {
                        $routing_scheme = strtolower($expr_tmp[1]);
                    }
                    $this->current_route = $this->getRouteForMask($url);
                    array_shift($_matches);
                    if(count($_matches) > 0) {
                        $arg_names = array();
                        $arg_expr = "#[\?\&]([^/&]+)\=\%[s]|\%[s]#";
                        if(preg_match_all($arg_expr, $_route, $_imatch) && (isset($_imatch[1]))) {
                            if(count($_imatch[1]) != count($_matches)) {
                                out_error_message("Redirect rule \"$_expr\" => \"$_route\" is invalid. <br />
                       Please check your redirect_rules.inc file.");
                            }
                            $arg_names = $_imatch[1];
                            $args = array();
                            for($cnt = 0; $cnt < count($_matches); $cnt++) {
                                if(!empty($arg_names[$cnt])) {
                                    $arg_name            = $arg_names[$cnt];
                                    $$arg_name           = $_matches[$cnt];
                                    $args[]              = "$$arg_name";
                                    $_GET[$arg_name]     = $$arg_name;
                                    $_REQUEST[$arg_name] = $$arg_name;
                                }
                                else {
                                    $args[] = "\"$_matches[$cnt]\"";
                                }
                            }
                        }
                        elseif((isset($_imatch[1])) && (count($_imatch[1]) != count($_matches))) {
                            $args = array();
                            foreach($_matches as &$_match) {
                                $_match = "\"$_match\"";
                            }
                            $args = array_merge($_matches, $args);
                        }
                        else {
                            out_error_message("Redirect rule \"$_expr\" => \"$_route\" is invalid. <br />
                     Please check your redirect_rules.inc file.");
                        }
                        $arguments = implode(', ', $args);
                        $res = eval("$new_url = sprintf($_route, $arguments);");
                        if(!empty($new_url)) {
                            $new_url = rtrim($new_url, " .&?=");
                        }
                    }
                    else {
                        $res = true;
                        $new_url = $_route;
                    }
                    if(($res !== false) && (isset($new_url))) {
                        $this->applyRoutingRules($routes, $new_url, &$matches);
                    }
                    break;
                }
            }
        }
    }

    private function getRouteForMask($mask) {
        $url = $mask;
        $urinfo = preg_split("#[\/\?]{1}#", $url,-1);
        for($cnt = 0; $cnt < count($urinfo); $cnt++) {
            if(preg_match("#[\=\?\&\+\,]+#", $urinfo[$cnt])) {
                unset($urinfo[$cnt]);
            }
        }
        return implode("/", $urinfo);
    }
}
if(!function_exists('getShadowedPath')) {
    // global function
    function getShadowedPath($file_path) {
        if(file_exists(PA_PROJECT_PROJECT_DIR.DIRECTORY_SEPARATOR.$file_path)) {
            return(PA_PROJECT_PROJECT_DIR.DIRECTORY_SEPARATOR.$file_path);
        }
        elseif(file_exists(PA_PROJECT_CORE_DIR.DIRECTORY_SEPARATOR.$file_path)) {
            return(PA_PROJECT_CORE_DIR.DIRECTORY_SEPARATOR.$file_path);
        }
        return false;
    }
}

/**
 * @class DispatcherException
 *
 * The DispatcherException class implements the basics methods for
 * WEB dispatcher exceptions
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.0.1
 *
 *
 **/
class DispatcherException extends Exception {

    public function __construct($message) {
        $msg = "<div style=\"border: 1px solid red; padding: 24px\">
                <h1 style=\"color: red\">DispatcherException</h1>\r\n
                <font style=\"color: red\">$message</font> \r\n
              </div>\r\n";
        echo $msg;
        exit;
    }
}
if(!function_exists('out_error404')) {
    // global function
    function out_error404($message, $file_path = null) {
        require_once "api/Theme/Template.php";
        header("HTTP/1.0 404 Not Found");
        $template_file = getShadowedPath('web/Themes/Default/error404.tpl');
        $template = &new Template($template_file);
        $template->set('message', $message);
        $template->set('file_name', $file_path);
        echo $template->fetch();
        exit;
    }
}
if(!function_exists('out_error_message')) {
    // global function
    function out_error_message($message) {
        header("Content-type: text/html");
        throw new DispatcherException($message);
    }
}
?>