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
require_once "api/Logger/Logger.php";
require_once "web/includes/classes/XmlConfig.class.php";
require_once "web/includes/classes/PADefender.class.php";

/**
 * @class BootStrap
 *
 * The BootStrap class implements the basic methods for the PA
 * bootstrap and installation processes, including configuration
 * files loading, system environement data collecting  and
 * registering global variables.
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.3
 * update      Apr 2010, martin: 
 * corrected loading of AppConfig.yml file to load from project_dir first
 * all configuration should ONLY be on the project_dir, which in case of standalone Pa is IDENTICAL with core_dir
 *
 *
 */
class BootStrap {

 public  $install_dir;
 public  $document_root;
 public  $script_dir;
 public  $script_path;
 public  $request_method;
 public  $current_scheme;
 public  $server_name;
 public  $http_host;
 public  $domain_suffix;
 public  $domain_prefix;
 public  $remote_addr;
 public  $request_uri;
 public  $base_url;
 public  $user_agent;
 public  $current_route;
 public  $current_query;
 private $request_data;
 public  $installed_languages = array();
 public  $current_lang = null;
 private $pa_static_vars;
 public  $configObj;
 public  $configData;
 public  $upload_max_filesize;

 private $defend_rules;

   public function __construct($install_dir, $current_route, $route_query_str) {

    $this->current_route = $current_route;
    $this->current_query = $route_query_str;
    $this->debug = false;
    $this->install_dir = $install_dir;
    $this->killSlashes();
    $defendObj = new XmlConfig(getShadowedPath('config/defend_rules.xml'), 'rules');
    $this->defend_rules = $defendObj->asArray();
    $this->collectSystemData();
    if($this->debug) {
      echo "<pre>" . print_r($this,1) . "</pre>";
    }
   }

   private function collectSystemData() {

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
      if ((count($domain_parts)) > 2 && (!preg_match("|^\d+\.\d+\.\d+\.\d+|", PA_SERVER_NAME))) {
        $domain_match = array();
        if(preg_match("/([^\.\/]+).([^\.\/]+)$/", PA_SERVER_NAME, $domain_match)) {
          $domain_suffix = $domain_match[0];
          $domain_prefix = substr(PA_SERVER_NAME, 0, (strlen(PA_SERVER_NAME) - strlen($domain_suffix) -1));
        } else {
           throw new BootStrapException( "BootStrap::collectSystemData() - Unable to detect load domain suffix!", 1 );
        }
      } else {
        $domain_suffix = implode(".", $domain_parts);
        $domain_prefix = false;
      }
      define('PA_DOMAIN_SUFFIX', $domain_suffix);
      define('PA_DOMAIN_PREFIX', $domain_prefix);
      $this->domain_suffix = PA_DOMAIN_SUFFIX;
      $this->domain_prefix = PA_DOMAIN_PREFIX;

      $this->remote_addr = $this->getIP();

      $this->request_uri = $this->_normalize_URI($_SERVER['REQUEST_URI']);
      $_SERVER['REQUEST_URI'] = $this->request_uri;

      define('PA_BASE_URL', PA_CURRENT_SCHEME . '://' . PA_SERVER_NAME);
      $this->base_url = PA_BASE_URL;

      define('PA_INSTALL_DIR', $this->install_dir);

      define('PA_USER_AGENT', implode(' ', $this->getUserAgent($_SERVER['HTTP_USER_AGENT'])));
      $this->user_agent = PA_USER_AGENT;

      $this->request_data = $_REQUEST;

      $this->upload_max_filesize = parse_file_size_string(ini_get("upload_max_filesize"));


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
     $rfc_ip_private_list = array("10.0.0.0/8", "172.16.0.0/12", "192.168.0.0/16");
     $ip_array = $this->get_ip_array();

     foreach ( $ip_array as $ip_s ) {
       if(($ip_s != "") and (!$this->is_ip_innets($ip_s, $rfc_ip_private_list))) {
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
   * Load all configuration files in config scheme
   *
   *
   */
  public function autoLoadFiles($config_scheme) {
    ob_start();

      foreach($GLOBALS as $key => $val) {
        global $$key;
      }

      foreach($config_scheme as $file_info) {
        try {
           $before_eval_vars = get_defined_vars();
           $function_variable_names = array("function_variable_names" => 0, "before_eval_vars" => 0, "created" => 0);
           eval("?>".require_once($file_info['file']));
           $created = array_diff_key(get_defined_vars(), $GLOBALS, $function_variable_names, $before_eval_vars);
           foreach($created as $created_name => $created_value) {
              global $$created_name;
           }
           extract($created);
        } catch (Exception $e) {
           throw new BootStrapException($e->message ,1);
        }
      }
      return ob_get_clean();
  }

  public function loadConfigFile($fname) {
    $this->pa_static_vars = array_keys(get_class_vars('PA'));
    if (file_exists(PA::$project_dir . $fname)) { // check for config data in paproject path
      $proj_conf = new XmlConfig(PA::$project_dir . $fname, 'application');
      $proj_conf = $proj_conf->asArray();
    } 
    // or try to load default config file
    // this happens on install etc, so we search for it in the CORE dir
    else if (file_exists(PA::$core_dir . "{$fname}.distr")) { 
      $proj_conf = new XmlConfig(PA::$core_dir . "{$fname}.distr", 'application');
      $proj_conf = $proj_conf->asArray();
      // but we SAVE it to project_dir!!!
      $export_config = new XmlConfig(PA::$project_dir . $fname, 'application');  // and create AppConfig.xml
      $export_config->loadFromArray($proj_conf, $export_config->root_node);
      $export_config->saveToFile();
    } else {
        throw new BootStrapException( "BootStrap::loadConfigFile() - Unable to load \"$fname\"config. file!", 1 );
    }

    $this->configData = $proj_conf;
    $this->configObj  = new XmlConfig(null, 'application');
    $this->configObj->loadFromArray($this->configData, 
    	$this->configObj->root_node);
    $this->parseConfigFile('configuration', $proj_conf['configuration']);
    $this->afterParse();
  }

  /**
  *  BOOT main network, detect local networks
  *
  * detect network name and work out $base_url
  *
  **/
  public function detectNetwork() {
    global $network_prefix;
    $host = PA_SERVER_NAME;
    // URL to the root of the server.
    $base_url = "http://%network_name%.{$this->domain_suffix}";

    if( PA::$ssl_force_https_urls == true ) {
      $base_url = str_replace( 'http', 'https', $base_url );
    }

    if( preg_match( '/^\./', $this->domain_suffix ) ) {
      throw new BootStrapException( "Invalid domain sufix detected. Value: " . $this->domain_suffix, 1 );
    }
    PA::$domain_suffix = $this->domain_suffix;

    if( !PA::$config->enable_networks ) {              // spawning disabled
      $network_prefix = 'default';
      $network_url_prefix = PA::$config->domain_prefix;
      define( 'CURRENT_NETWORK_URL_PREFIX', PA::$config->domain_prefix );
      define( 'CURRENT_NETWORK_FSPATH', PA::$project_dir . '/networks/default' ); // turn off spawning, and guess domain suffix
      PA::$config->enable_network_spawning = FALSE;
    } else {
      // network operation is enabled - figure out which network we're on
      PA::$network_capable = TRUE;
      // Make sure $domain_suffix is formatted correctly
      if(!empty($this->domain_prefix) && $this->domain_prefix != PA::$config->domain_prefix) {
        $network_prefix = $this->domain_prefix;
        $network_url_prefix = $this->domain_prefix;
      } else { // domain prefix points to home network
        $network_prefix = 'default';
        $network_url_prefix = PA::$config->domain_prefix;

        define( 'CURRENT_NETWORK_URL_PREFIX', $network_url_prefix );
        define( 'CURRENT_NETWORK_FSPATH', PA::$project_dir . '/networks/default' );

      }
    }
    // Allow sessions to persist across entire domain
    ini_set( 'session.cookie_domain', $this->domain_suffix );
    $network_folder = getShadowedPath("networks/$network_prefix");
    if($network_folder)  { // network exists
      if(!defined('CURRENT_NETWORK_URL_PREFIX')) {
         define( 'CURRENT_NETWORK_URL_PREFIX', $network_url_prefix );
      }
      if(!defined('CURRENT_NETWORK_FSPATH')) {
         define( 'CURRENT_NETWORK_FSPATH', $network_folder );
      }
      if(file_exists(CURRENT_NETWORK_FSPATH . '/local_config.php')) {
        // and it has its own config file
        include( CURRENT_NETWORK_FSPATH . '/local_config.php' );
      }
    } elseif($this->domain_prefix != PA::$config->domain_prefix) {
        throw new BootStrapException( "Unable to locate network: " . htmlspecialchars($network_prefix) . "." . $this->domain_suffix, 1 );
    }

    // at this point, network is detected and we can start to work with the variables they define.
    // put network prefix in $base_url
    $base_url_pa = str_replace( '%network_name%', PA::$config->domain_prefix, $base_url );// LOCAL
    $base_url = PA::$url = str_replace( '%network_name%', CURRENT_NETWORK_URL_PREFIX, $base_url );

    // now we are done with $base_url - it gets define()d and we work out
    // the relative version (for ajax)
    define( 'BASE_URL_PA', $base_url_pa );
    define( 'BASE_URL', $base_url );

    $base_url_parts = parse_url( $base_url );
    PA::$local_url = preg_replace( '|/$|', '', @$base_url_parts[ 'path' ] ? $base_url_parts[ 'path' ] : '' );

    define( 'BASE_URL_REL', PA::$local_url );

    // work out theme path and check that it exists
    define( 'CURRENT_THEME_REL_URL', PA::$local_url . '/' . PA::$theme_rel );
    define( 'CURRENT_THEME_FSPATH', PA::$theme_path );
    define( 'CURRENT_THEME_FS_CACHE_PATH', PA::$project_dir . '/web/cache' );
    // Finally - Load network!
    PA::$network_info = get_network_info($network_prefix); // NOTE this should be retrieved from network XML config file
    PA::$extra = unserialize(PA::$network_info->extra);

  }

  public function detectDBSettings() {
    global $peepagg_dsn;
    // figure out CURRENT_DB.
    $peepagg_dsn = "mysql://". PA::$config->db_user .
                          ":". PA::$config->db_password .
                          "@". PA::$config->db_host .
                          "/". PA::$config->db_name;

    if(empty(PA::$config->db_user) || empty(PA::$config->db_password) || empty(PA::$config->db_host) || empty(PA::$config->db_name)) {
      throw new BootStrapException("Invalid DB connection string. Value: " . $peepagg_dsn, 1);
    }

    if(defined( 'CURRENT_DB' ))
    {
      throw new BootStrapException("CURRENT_DB already defined but CURRENT_DB should not be defined outside BootStrap class!", 1);
    }
    define('CURRENT_DB', PA::$config->db_name);
  }


  private function parseConfigFile($name, $values) {
    $type = null;
    $category = null;
    $section = null;
    foreach($values as $_name => $_value) {
      if($_name == '@attributes') {
        $category = (!empty($_value['category'])) ? $_value['category'] : null;
        $section = (!empty($_value['section'])) ? $_value['section'] : null;
        $type = (!empty($_value['type'])) ? $_value['type'] : null;
        continue;
      }
        if((($type <> 'array') && ($type <> 'multi_array')) && is_array($_value)) {
         $this->parseConfigFile($_name, $_value);
        } else {
          if($category == 'constant') {
            if($type == 'expression') {
              $exp = "\$var_value = $_value; return true;";
              if(!eval($exp)) {
                 throw new BootStrapException("Error - invalid config variable \"$name\", category: \"$category\", type: \"$type\"", BootStrapException::UNRECOVERABLE_BOOT_EXCEPTION);
              }
              define($name, $var_value);
            } else {
              define($name, $_value);
            }
          } else if($category == 'global') {
            if($type == 'expression') {
              $exp = "\$var_value = $_value; return true;";
              if(!eval($exp)) {
                 throw new BootStrapException("Error - invalid config variable \"$name\", category: \"$category\", type: \"$type\"", BootStrapException::UNRECOVERABLE_BOOT_EXCEPTION);
              }
              $GLOBALS[$name] = $var_value;
            } else {
              $GLOBALS[$name] = $_value;
            }
          } else if(($category == 'dynamic_var') || ($category == 'static_var') || ($category == 'item')) {
             $var_value = null;
             switch($type ) {
                case 'bool':
                case 'int':        $exp = "\$var_value = (int)$_value; return true;";  break;
                case 'array':      $exp = "\$var_value = \$_value; return true;";      break;
                case 'string':     $exp = "\$var_value = \"$_value\"; return true;";   break;
                case 'expression': $exp = "\$var_value = $_value; return true;";       break;
                case 'multi_array':
                        $exp =  "foreach(\$_value as \$m_key => &\$m_val) {
                                   if(is_array(\$m_val)) {
                                      \$m_val = \$this->parseConfigFile(\$m_key, \$m_val);
                                      \$var_value = \$_value;
                                    } else {
                                      \$var_value = \$_value;
                                    }
                                 }
                                 return true;";
                    break;
                default:
                    $exp = "\$var_value = $_value;";
             }
             if(!eval($exp)) {
               throw new BootStrapException("Error - invalid config variable \"$name\", category: \"$category\", type: \"$type\"", BootStrapException::UNRECOVERABLE_BOOT_EXCEPTION);
             }
             switch($category) {
                case 'dynamic_var' :
                  PA::$config->{$name} = $var_value;
                break;
                case 'static_var' :
                  if(in_array($name, $this->pa_static_vars)) {
                    PA::$$name = $var_value;
                  } else {
                    throw new BootStrapException("Error - Static variable \"$name\" must be defined within PA class as static property.", BootStrapException::UNRECOVERABLE_BOOT_EXCEPTION);
                  }
                break;
                case 'item' :
                    return $var_value;
                break;
                default:
                  throw new BootStrapException("Error - unknown config variable type! Var name: \"$name\", category: \"$category\", type: \"$type\"", BootStrapException::UNRECOVERABLE_BOOT_EXCEPTION);
             }
          }
        }
    }

  }

  /**
  *
  * NOTE: here should be placed code that should be executed after parsing the config data
  **/
  private function afterParse() {
    require_once "web/includes/functions/functions.php";
      if(PA::$profiler) PA::$profiler->startTimer('PADefender');
//
// QUESTION: Whether "post" and "get" data should be filtered here for all requests?
//
//      filter_all_post($_GET);
//      filter_all_post($_POST);
//      filter_all_post($_REQUEST);

      PADefender::testArrayRecursive($_GET, $this->defend_rules);
      PADefender::testArrayRecursive($_POST, $this->defend_rules);
      PADefender::testArrayRecursive($_REQUEST, $this->defend_rules);

      if(PA::$profiler) PA::$profiler->stopTimer('PADefender');

    // Path to performance log file for detailed performance logging - for spam debugging.
    if(isset(PA::$config->perf_log) && (!empty(PA::$config->perf_log))) {
       register_shutdown_function("pa_log_script_execution_time");
//       pa_log_script_execution_time(TRUE);
    }

    // populate GLOBAL var $file_type_info with max. upload file size value
    foreach ($GLOBALS['file_type_info'] as &$fti) {
      if($fti['max_file_size'] > $this->upload_max_filesize) {
        $fti['max_file_size'] = $this->upload_max_filesize;
      }
    }

    // enable new-style storage system
    if (PA::$config->use_storage) define("NEW_STORAGE", TRUE);

    // load profanity words list from file - merge with list from XML
    $profanity_file = getShadowedPath(PA::$config_path .'/profanity_words.txt');
    if($profanity_file) {
      $prof_arr = explode("\r\n", file_get_contents(PA::$project_dir . "/config/profanity_words.txt"));
      $prof_arr = array_merge((array)PA::$config->profanity, (array)$prof_arr);
      PA::$config->profanity = array_unique($prof_arr);
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
  public function loadLanguageFiles() {
    $culture_file = getShadowedPath(PA::$config_path .'/i18n.xml');
    $culture_data = new XmlConfig($culture_file);
    if($culture_data->docLoaded) {
      PA::$culture_data = $culture_data->asArray();
    } else {
      throw new BootStrapException("Error - Can't load \"$culture_file\" culture file.", BootStrapException::UNRECOVERABLE_BOOT_EXCEPTION);
    }
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
      if(PA::$config->pa_installed)  {
        $net_info = get_network_info();
        $net_settings = unserialize($net_info->extra);
        $this->current_lang = (isset($net_settings['default_language'])) ? $net_settings['default_language'] : 'english';
      }
    }
    session_commit();

    if($this->current_lang) {
      PA::$language = $this->current_lang;
    }

    ob_start();
    global $TRANSLATED_STRINGS;
      $strings_file = getShadowedPath("web/languages/".PA::$language."/strings.php");
      try {
            if(file_exists($strings_file)) {
               eval('?>' . require_once($strings_file));
            }
            $msg_handler = getShadowedPath("web/languages/".PA::$language."/MessagesHandler.php");
            if(file_exists($msg_handler)) {
               eval('?>' . require_once($msg_handler));
            } else {
               eval('?>' . require_once getShadowedPath("web/languages/english/MessagesHandler.php"));
            }
        } catch (Exception $e) {
           // Either an invalid language was selected, or one (e.g. English) without a strings.php file.
           $TRANSLATED_STRINGS = array();
           throw new BootStrapException($e->message ,1);
        }
      return ob_get_clean();
  }


  // figure out the remote IP address and check against the ban list
  public function check_ip_ban() {
    if (in_array(PA::$remote_ip, PA::$config->trusted_proxies))
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

/**
 * @class BootStrapException
 *
 * The BootStrapException class implements the basics methods for bootstrap and
 * installation exceptions.
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.2
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
          $message = $this->formatErrMessage($message);
          echo $message;
          exit;
        break;
        case self::UNRECOVERABLE_BOOT_EXCEPTION:
          $msg = "<div style=\"border: 1px solid red; padding: 24px\">
                    <h1 style=\"color: red\">BootStrapException</h1>\r\n
                    <p style=\"color: red\">$message</p> \r\n
                  </div>\r\n";
          echo $msg;
          exit;
        break;
        default:
          parent::__construct($message, $code);
      }
      Logger::log("BootStrapException: $message", LOGGER_ERROR, LOGGER_FILE);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    private function formatErrMessage($message) {
      $msg = "<div style=\"border: 1px solid red; padding: 24px\">
                <h1 style=\"color: red\">Configuration error</h1>\r\n
                <p style=\"color: red\">$message</p>\r\n
              </div>\r\n";
      return $msg;
    }
}

?>