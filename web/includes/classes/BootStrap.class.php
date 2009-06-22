<?php

define("PA_LANGUAGES_DIR", "web/languages");

/**
 * @class BootStrapException
 *
 * The BootStrapException class implements the basics methods for bootstrap and
 * installation exceptions.
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.0.1
 *
 *
 */
class BootStrapException extends Exception
{
    const CONFIGURATION_EXCEPTION      = 1;
    const UNRECOVERABLE_BOOT_EXCEPTION = 2;

    public function __construct($message, $code = 0) {
      switch($code) {
        case self::CONFIGURATION_EXCEPTION:
          $message = $this->formatConfigMessage($message);
          echo $message;
          exit;
        break;
        case self::UNRECOVERABLE_BOOT_EXCEPTION:
          $msg = "<div style=\"border: 1px solid red; padding: 24px\">
          <h1 style=\"color: red\">BootStrapException</h1>\r\n
          <font style=\"color: red\">$message</font> \r\n
          </div>\r\n";
          echo $msg;
          exit;
        break;
        default:
          parent::__construct($message, $code);
      }
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    private function formatConfigMessage($message) {
      $msg = "<div style=\"border: 1px solid red; padding: 24px\">
      <h1 style=\"color: red\">Configuration error</h1>\r\n
      <font style=\"color: red\">$message</font> \r\n
      <p>Here is a sample <code>local_config.php</code> file:</p>\r\n" .
      $this->get_sample_local_config() . "</div>\r\n";
      return $msg;
    }

    private function get_sample_local_config() {

      $sample_base_url = "http://%network_name%." . PA_DOMAIN_SUFFIX . rtrim(PA_SCRIPT_PATH, "/");
      return "<pre>&lt;?php\r\r\n".
             "// database connection string\r\n".
             "\$peepagg_dsn = \"mysql://user:password@server/databasename\";\r\r\n".
             "// where to log messages and errors.  note that this file must be writeable by the web server.\r\n".
             "\$logger_logFile = PA::\$path . \"/log/pa.log\";\r\r\n".
             "// url of the \"web\" directory\r\n".
             "\$base_url = \"$sample_base_url\";\r\r\n".
             "// the constant part of the domain (such that if we chop this off the server name, we should get the network name).\r\n".
             "\$domain_suffix = \"".PA_DOMAIN_SUFFIX."\";\r\r\n".
             "?&gt;</pre>\r\n".
             "<p>(except with your own database details).</p>";
    }

}

/**
 * @class BootStrap
 *
 * The BootStrap class implements the basics methods for the PA
 * bootstrap and installation processes, including configuration
 * files loading, system environement data collecting  and
 * registering global variables.
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.0.1
 *
 *
 */
class BootStrap {

 public  $pa_installed = false;
 public  $install_dir;
 public  $document_root;
 public  $script_dir;
 public  $script_path;
 public  $request_method;
 public  $current_scheme;
 public  $server_name;
 public  $http_host;
 public  $domain_suffix;
 public  $remote_addr;
 public  $request_uri;
 public  $base_url;
 public  $user_agent;
 public  $current_route;
 public  $current_query;
 private $request_data;
 public  $installed_languages = array();
 public  $current_lang = null;
 private $rfc_ip_private_list = array(
                                       "10.0.0.0/8",
                                       "172.16.0.0/12",
                                       "192.168.0.0/16"
                                     );

   public function __construct($install_dir) {
    global $current_route, $route_query_str;

    $this->current_route = $current_route;
    $this->current_query = $route_query_str;
    $this->debug = false;
    $this->install_dir = $install_dir;
    $this->killSlashes();
    $this->collectSystemData();
    $this->pa_installed = $this->is_PA_installed();
    if($this->debug) {
      echo "DOCUMENT_ROOT - "  . $this->document_root  . "<br />";
      echo "SCRIPT_DIR - "     . $this->script_dir     . "<br />";
      echo "SCRIPT_PATH - "    . $this->script_path    . "<br />";
      echo "REQUEST_METHOD - " . $this->request_method . "<br />";
      echo "CURRENT_SCHEME - " . $this->current_scheme . "<br />";
      echo "HTTP_HOST - "      . $this->http_host      . "<br />";
      echo "SERVER_NAME - "    . $this->server_name    . "<br />";
      echo "DOMAIN_SUFFIX - "  . $this->domain_suffix  . "<br />";
      echo "REMOTE_ADDR - "    . $this->remote_addr    . "<br />";
      echo "REQUEST_URI - "    . $this->request_uri    . "<br />";
      echo "BASE_URL - "       . $this->base_url       . "<br />";
      echo "INSTALL_DIR - "    . $this->install_dir    . "<br />";
      echo "USER_AGENT - "     . $this->user_agent     . "<br />";
    }
   }

   public function collectSystemData() {
      //
      // collect OS depended data
      //
      $path_separator = ":";
      $dir_separator  = "/";
      if(substr(PHP_OS, 0, 3) == "WIN") {
         $path_separator = ";";
         $dir_separator  = "\\";
      }
      if(!defined('PATH_SEPARATOR') || !defined('DIRECTORY_SEPARATOR')) {
         define('PATH_SEPARATOR', $path_separator);
         define('DIRECTORY_SEPARATOR', $dir_separator);
      }

      //
      // collect server data
      //
      define('PA_DOCUMENT_ROOT', realpath($_SERVER['DOCUMENT_ROOT']));
      $this->document_root = PA_DOCUMENT_ROOT;


      $dir_info = pathinfo(realpath($_SERVER['SCRIPT_FILENAME']));
      $path_info = pathinfo($_SERVER['SCRIPT_NAME']);
      define('PA_SCRIPT_DIR', @$dir_info['dirname']);
      define('PA_SCRIPT_PATH', @$path_info['dirname']);
      $this->script_dir = PA_SCRIPT_DIR;
      $this->script_path = PA_SCRIPT_PATH;


      if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
          define('PA_REQUEST_METHOD', 'AJAX');
      } else {
          define('PA_REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
      }
      $this->request_method = PA_REQUEST_METHOD;


      $scheme = sprintf('http%s', (isset($_SERVER['HTTPS']) &&
                                         $_SERVER['HTTPS'] == TRUE ? 's': ''));
      define('PA_CURRENT_SCHEME', $scheme);
      $this->current_scheme = PA_CURRENT_SCHEME;


      (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
        ? define('PA_HTTP_HOST', $_SERVER['HTTP_X_FORWARDED_HOST'])
        : define('PA_HTTP_HOST', $_SERVER['HTTP_HOST']);
      $this->http_host = PA_HTTP_HOST;


      (isset($_SERVER['HTTP_X_FORWARDED_SERVER']))
        ? define('PA_SERVER_NAME', $_SERVER['HTTP_X_FORWARDED_SERVER'])
        : define('PA_SERVER_NAME', $_SERVER['SERVER_NAME']);
      $this->server_name = PA_SERVER_NAME;


      $domain_parts = explode(".", PA_SERVER_NAME);
      if (count($domain_parts) > 1) {
        array_shift($domain_parts);
      }
      if (!preg_match("|^\d+\.\d+\.\d+\.\d+|", PA_SERVER_NAME)) {
        $domain_suffix = implode(".", $domain_parts);
      } else {
        $domain_suffix = false;
      }
      define('PA_DOMAIN_SUFFIX', $domain_suffix);
      $this->domain_suffix = PA_DOMAIN_SUFFIX;

      $this->remote_addr = $this->getIP();

      $this->request_uri = $this->_normalize_URI($_SERVER['REQUEST_URI']);
      $_SERVER['REQUEST_URI'] = $this->request_uri;

      define('PA_BASE_URL', PA_CURRENT_SCHEME . '://' . PA_SERVER_NAME);
      $this->base_url = PA_BASE_URL;

      define('PA_INSTALL_DIR', $this->install_dir);

      define('PA_USER_AGENT', implode(' ', $this->getUserAgent($_SERVER['HTTP_USER_AGENT'])));
      $this->user_agent = PA_USER_AGENT;

      $this->request_data = $_REQUEST;
   }

   private function _normalize_URI($uri_str) {
     $ret_str = preg_replace('/(\?\&|\&\?|\?+|\&+)/i', '&', $uri_str);
     return preg_replace('/(\&)/', '?', $ret_str, 1);
   }

   public function getRequestParam($name)  {
      return (isset($this->request_data[$name])) ? $this->request_data[$name] : null;
   }

   public function setRequestParam($name, $value, $type = 'GET')  {
      switch($type) {
        case 'GET':
          $_GET[$name] = $value;
        break;
        case 'POST':
          $_POST[$name] = $value;
        break;
      }
      $_REQUEST[$name] = $value;
      $this->request_data[$name] = $value;
   }

   public function unsetRequestParam($name, $type = 'GET')  {
      switch($type) {
        case 'GET':
          if(isset($_GET[$name])) unset($_GET[$name]);
        break;
        case 'POST':
          if(isset($_POST[$name])) unset($_POST[$name]);
        break;
      }
      if(isset($_REQUEST[$name])) unset($_REQUEST[$name]);
      if(isset($this->request_data[$name])) unset($this->request_data[$name]);
   }


   public function getRequestData()  {
      return $this->request_data;
   }

   public function getIp()  {
     $ip = "unknown";
     $ip_array = $this->get_ip_array();

     foreach ( $ip_array as $ip_s ) {
       if(($ip_s != "") and (!$this->is_ip_innets($ip_s, $this->rfc_ip_private_list))) {
         $ip = $ip_s;
         break;
       }
     }
     return($ip);
   }


   // This function check if a ip is in array of
   // rfc1918 private nets (ip and mask)
   private function is_ip_innets($ip, $snets) {
     $result = false;
     foreach($snets as $subnet) {
       list($net, $mask) = split("/", $subnet);
       $long_net = ip2long($net);
       $long_ip  = ip2long($ip);
       $bin_net  = str_pad(decbin($long_net), 32, "0", STR_PAD_LEFT);
       $firstpart= substr($bin_net, 0, $mask);
       $bin_ip   = str_pad(decbin($long_ip), 32, "0", STR_PAD_LEFT);
       $firstip  = substr($bin_ip, 0, $mask);

       if(strcmp($firstpart, $firstip) == 0) {
         $result = true;
         break;
       }
     }
     return($result);
   }

   // Building the ip array with the HTTP_X_FORWARDED_FOR and
   // REMOTE_ADDR HTTP vars.
   private function get_ip_array() {
     $tmp = array();
     if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
        $tmp =  explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
     } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $tmp[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
     }
     $tmp[] = $_SERVER['REMOTE_ADDR'];
     return $tmp;
   }

  /**
   * Check is PA installed - look for "/web/config/install.log"
   *
   *
   */
  private function is_PA_installed() {
    return file_exists(PA::$project_dir . "/web/config/local_config.php");
  }


  /**
   * Load all configuration files in config scheme
   *
   *
   */
  public function loadConfiguration($config_scheme) {
    ob_start();
      global $_PA, $base_url, $login_uid, $logger_logFile, $uploaddir;
      global $peepagg_dsn, $domain_suffix, $query_count_on_page, $network_info, $network_prefix;
      global $default_simplePA_settings, $current_theme_path, $current_theme_rel_path, $current_blockmodule_path;
      global $global_form_data, $global_form_error, $error, $error_msg, $js_includes, $js_includes_dont_optimize;
      global $pa_page_render_start, $debug_show_svn_version, $TRANSLATED_STRINGS;
      global $flickr_api_key, $flickr_api_secret, $flickr_auth_type;
      global $facebook_api_key, $facebook_api_secret, $facebook_auth_type;
      global $aim_presence_key, $aim_api_key, $debug_annotate_templates, $network_controls, $invalid_network_address;
      global $fortythree_api_key, $_form, $comments_disabled, $tags_allowed_in_fields;
      global $default_sender, $default_links_array, $debug_for_user, $debug_disable_template_caching;
      global $default_image_file_name, $default_image_title, $default_image_album_name;
      global $default_audio_file_name, $default_audio_title, $default_audio_album_name;
      global $default_video_file_name, $default_video_title, $default_video_album_name;
      global $default_link_categories, $outputthis_error_message, $post_type_message;
      global $allow_html_in_fields, $ping_server, $sb_dir_name, $sb_upload_dir, $sb_upload_url, $sb_mc_location;
      global $optimizers_use_url_rewrite, $use_css_optimizer, $use_js_optimizer, $use_js_packer, $cssjs_tag;
      global $query_count_on_page, $query_count_array;

      $global_form_data = array();
      $global_form_error = array();

      foreach($config_scheme as $config_file) {
        try {
           include($config_file);
        } catch (Exception $e) {
           throw new BootStrapException($e->message ,1);
        }
      }
    return ob_get_clean();
  }


  public function checkConfigVars() {

    // Make sure that all the vars are set
    foreach (array('peepagg_dsn', 'logger_logFile', 'base_url', 'domain_suffix', 'current_theme_rel_path') as $varname)
    {
      if (!isset($GLOBALS[$varname]))
      {
        if (defined("PEEPAGG_CONFIGURATION")) throw new BootStrapException("Error - \$$varname should be defined by the config process", 1);
      }
    }

    // Check for deprecated features
    foreach (array('config_site_name' => 'PA::$site_name', 'peepagg_lang' => 'PA::$language', ) as $oldvar => $newvar) {
      if (isset($GLOBALS[$oldvar])) {
        echo "<p>Your local_config.php or project_config.php contains a definition for \$$oldvar (setting it to '".htmlspecialchars($$oldvar)."').  This is deprecated and should be replaced by $newvar.</p><p>e.g. <code>$newvar = '".htmlspecialchars(str_replace("'", "\'", $$oldvar))."';</p>";
        exit;
      }
    }
  }

  public function getLanguagesList() {

    $languages_list = array();
    $languages_list['english'] = 'english';
    $language_dirs  = array(PA::$core_dir . DIRECTORY_SEPARATOR . PA_LANGUAGES_DIR,
                            PA::$project_dir . DIRECTORY_SEPARATOR . PA_LANGUAGES_DIR );

    foreach($language_dirs as $lang_path) {
      foreach(new RecursiveDirectoryIterator($lang_path, RecursiveDirectoryIterator::KEY_AS_PATHNAME) as $_path => $info) {
        $_entry = $info->getFilename();
        if ($info->isDir() && $_entry != '.svn')
        {
          if(file_exists($_path . DIRECTORY_SEPARATOR . 'strings.php')) {             // NOTE: language strings from paproject
            $languages_list[$_entry] = $_path . DIRECTORY_SEPARATOR . 'strings.php';  // will override strings from the core
          }
        }
      }
    }
    return $languages_list;
  }

  /**
   * Load internationalization string files
   *
   *
   */
  public function loadInternationalization() {

    $this->installed_languages = $this->getLanguagesList();
    session_start();
    if(!empty($this->request_data['lang'])) {
      if(array_key_exists($this->request_data['lang'], $this->installed_languages)) {
        $this->current_lang = $this->request_data['lang'];
        $_SESSION['user_lang'] = $this->current_lang;
      }
    } else if(isset($_SESSION['user_lang'])) {
      $this->current_lang = $_SESSION['user_lang'];
    } else {
      $net_info = get_network_info();
      $net_settings = unserialize($net_info->extra);
      $this->current_lang = (isset($net_settings['default_language'])) ? $net_settings['default_language'] : 'english';
    }
    session_commit();

    if($this->current_lang) {
      PA::$language = $this->current_lang;
    }

    ob_start();
    global $TRANSLATED_STRINGS;
      $strings_file = "web/languages/".PA::$language."/strings.php";
      if(PA::$language != 'english') {
         try {
           require_once($strings_file);
        } catch (Exception $e) {
           // Either an invalid language was selected, or one (e.g. English) without a strings.php file.
           $TRANSLATED_STRINGS = array();
           throw new BootStrapException($e->message ,1);
        }
      }
      return ob_get_clean();
  }


  // figure out the remote IP address and check against the ban list
  public function check_ip_ban() {
    global $_PA;
    if (in_array(PA::$remote_ip, $_PA->trusted_proxies))
      return;
    $ban_path = "ban/".str_replace(".", "/", PA::$remote_ip);
    if (file_exists($ban_path)) {
      header("HTTP/1.0 500 Internal Server Error");
      echo "ip parse failed!"; // fake error message so if someone legitimate gets banned, we know what happened.
      exit;
    }
  }

  public function getCurrentUser() {
    global $page_uid, $page_user, $login_uid, $login_name, $login_user;
    require_once "api/User/User.php";

    session_start();
      PA::$login_uid = NULL;
      PA::$login_user =  NULL;
      $login_uid = NULL;
      $login_name = NULL;
      $login_user = NULL;
      $this->CurrUser = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;

      if($this->CurrUser) {
        try {
          $user = new User();
          $user->load((int)$this->CurrUser['id'], "user_id", TRUE);
        } catch (Exception $e) {
          if(!in_array($e->getCode(), array(USER_NOT_FOUND, USER_ALREADY_DELETED))) {
            throw $e;
          }
          // The currently logged-in user has been deleted; invalidate the session.
          session_destroy();
          session_start();
          $login_uid = PA::$login_uid = $login_name = $login_user = PA::$login_user = NULL;
        }

        if($user->user_id) {
          $login_name = $this->CurrUser['name'];
          PA::$login_user = $login_user = $user;
          PA::$login_uid  = $login_uid  = $user->user_id;
        }

        if(PA::$login_uid) {
          PA::$login_user->update_user_time_spent();
          User::track_status(PA::$login_uid);
        }
      }

      // If a user is specified on the query string as an ID (uid=123) or
      // login name (login=phil), validate the id/name and load the user
      // object.
      if(!empty($_GET['uid'])) {
        $page_uid = PA::$page_uid = (int)$_GET['uid'];
        $page_user = PA::$page_user = new User();
        PA::$page_user->load(PA::$page_uid);
      } else if(!empty($_GET['login'])) {
        $page_user = PA::$page_user = new User();
        if(is_numeric($_GET['login'])) {
          PA::$page_user->load((int)$_GET['login']);
        } else {
          PA::$page_user->load($_GET['login']);
        }
        $page_uid = PA::$page_uid = PA::$page_user->user_id;
      } else {
        $page_uid = PA::$page_uid = $page_user = PA::$page_user = NULL;
      }

      // Copy PA::$page_* into PA::$* if present, otherwise use PA::$login_*.
      if(PA::$page_uid) {
        $uid = PA::$uid = PA::$page_uid;
        $user = PA::$user = PA::$page_user;
      } else {
        $uid = PA::$uid = PA::$login_uid;
        $user = PA::$user = PA::$login_user;
      }
      session_commit();
  }

  private function getUserAgent($ua) {

    $userAgent = array();
    $agent = $ua;
    $products = array();

    $pattern  = "([^/[:space:]]*)" . "(/([^[:space:]]*))?"
              . "([[:space:]]*\[[a-zA-Z][a-zA-Z]\])?" . "[[:space:]]*"
              . "(\\((([^()]|(\\([^()]*\\)))*)\\))?" . "[[:space:]]*";

    while( strlen($agent) > 0 ) {
      if ($l = ereg($pattern, $agent, $a)) {
          array_push($products, array($a[1], $a[3], $a[6])); // product, version, comment
          $agent = substr($agent, $l);
      } else {
          $agent = "";
      }
    }

    foreach($products as $product) {
      switch($product[0]) {
        case 'Firefox':
        case 'Netscape':
        case 'Safari':
        case 'Camino':
        case 'Mosaic':
        case 'Galeon':
        case 'Opera':
          $userAgent[0] = $product[0];
          $userAgent[1] = $product[1];
        break;
      }
    }

    if(count($userAgent) == 0) {
      if($products[0][0] == 'Mozilla' && !strncmp($products[0][2], 'compatible;', 11)) {
        $userAgent = array();
        if($cl = ereg("compatible; ([^ ]*)[ /]([^;]*).*", $products[0][2], $ca)) {
           $userAgent[0] = $ca[1];
           $userAgent[1] = $ca[2];
         } else {
           $userAgent[0] = $products[0][0];
           $userAgent[1] = $products[0][1];
         }
      } else {
         $userAgent = array();
         $userAgent[0] = $products[0][0];
         $userAgent[1] = $products[0][1];
      }
    }
    return $userAgent;
  }

  public function redirect($url) {
    if (!headers_sent())
        header('Location: '.$url);
    else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
    }
  }

  /* turn off magic quotes as much as possible */
  private function killSlashes() {
    // no magic quotes, thanks!
    set_magic_quotes_runtime(0);
    if (get_magic_quotes_gpc()) {
      $_POST    = $this->stripslashes_deep($_POST);
      $_GET     = $this->stripslashes_deep($_GET);
      $_REQUEST = $this->stripslashes_deep($_REQUEST);
      $_COOKIE  = $this->stripslashes_deep($_COOKIE);
    }
    define("SLASHES_KILLED", true);
  }

  private function stripslashes_deep($value) {
    $value = is_array($value) ? array_map(array($this,'stripslashes_deep'), $value) : stripslashes($value);
    return $value;
  }

}
?>
