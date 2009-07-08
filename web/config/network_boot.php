<?php
/* BOOT main network, detect local networks

procedure:

//
//
//  any config file with correct defined parameters:
//  peepagg_dsn', 'logger_logFile', 'base_url', 'domain_suffix', 'current_theme_rel_path',
//  can (and must!) be loaded before this script called
//
//

1. detect network name and work out $base_url (which contains a
   template parameter in local_config.php).

2. check for a local_config.php (at
   PA::$path/networks/$network_prefix/local_config.php) for the
   current network, and load that if it exists.

 */


$host = PA_SERVER_NAME;

if(PA::$ssl_force_https_urls == true) {
  $base_url = str_replace("http", 'https', $base_url);
}

if (!$_PA->enable_networks || !$domain_suffix) {
	// spawning disabled
	define("CURRENT_NETWORK_URL_PREFIX", "www");
	define("CURRENT_NETWORK_FSPATH", PA::$project_dir . "/networks/default");
	// turn off spawning, and guess domain suffix
	$_PA->enable_network_spawning = FALSE;
  PA::$domain_suffix = $domain_suffix = PA_DOMAIN_SUFFIX;
} else {
	// network operation is enabled - figure out which network we're on
	PA::$network_capable = TRUE;
	PA::$domain_suffix = $domain_suffix;

	// Check that $base_url includes %network_name
	if (strpos($base_url, "%network_name%") === FALSE) {
		throw new BootStrapException("<p>Your <code>\$base_url</code> variable needs to include the text <code>%network_name%</code> or spawning will not work.</p>", 1);
	}

	// Make sure $domain_suffix is formatted correctly
	if (preg_match("/^\./", $domain_suffix)) {
		throw new BootStrapException("<p>Your <code>\$domain_suffix</code> variable must not start with a period.</p>", 1);
	}

	// Allow sessions to persist across entire domain
	ini_set('session.cookie_domain', $domain_suffix);

	// Now see if this request is for a sub-network, and load its settings if so
	if (strrpos($host, $domain_suffix) != strlen($host) - strlen($domain_suffix)) {
		// Something is wrong with $domain_suffix - it's not showing up at the end of $host.
		// (e.g. $host == "www.pa.example.com" and $domain_suffix == "pa.someotherexample.com").
		// Just assume the default network.
		define("CURRENT_NETWORK_URL_PREFIX", "www");
		$network_prefix = "default";                  // GLOBAL
	} else {
		while (1) {
			$network_prefix = substr($host, 0, strlen($host) - strlen($domain_suffix));
			$network_prefix = preg_replace("/\.*$/", "", $network_prefix);

			if (!$network_prefix || $network_prefix == "www") {
				// special case - default network
				define("CURRENT_NETWORK_URL_PREFIX", "www");
				$network_prefix = "default";
			}
      $network_folder = null;
			$core_network_folder = PA::$core_dir . "/networks/$network_prefix";       // network exists in CORE ?
      $proj_network_folder = PA::$project_dir . "/networks/$network_prefix";    // network exists in PROJECT ?
			if(is_dir($core_network_folder)) {
        $network_folder = $core_network_folder;
      } else if(is_dir($proj_network_folder)) {
        $network_folder = $proj_network_folder;
      }
      if($network_folder) {
				// network exists
				if (!defined("CURRENT_NETWORK_URL_PREFIX"))
           define("CURRENT_NETWORK_URL_PREFIX", $network_prefix);

        define("CURRENT_NETWORK_FSPATH", $network_folder);
				if (file_exists(CURRENT_NETWORK_FSPATH."/local_config.php")) {
					// and it has its own config file
					include(CURRENT_NETWORK_FSPATH."/local_config.php");
				}
				break;
			}

			header("HTTP/1.0 404 Not Found");
			echo "
	         <h1>Network not found</h1>\r\n
             <p>Unable to locate network <code><b>". htmlspecialchars($network_prefix) . "</b>. $domain_suffix </code>.</p>\r\n";
			exit;
		}
	}
}
unset($domain_suffix); // PA::$domain_suffix should be used from now on.



// at this point, all local_config.php files have been included, so we
// can start to work with the variables they define.

// put network prefix in $base_url
$base_url_pa = str_replace("%network_name%", 'www', $base_url); // LOCAL
$base_url = PA::$url = str_replace("%network_name%", CURRENT_NETWORK_URL_PREFIX, $base_url); // GLOBAL


// now we are done with $base_url - it gets define()d and we work out
// the relative version (for ajax)
define("BASE_URL_PA", $base_url_pa);
define("BASE_URL", $base_url);
$base_url_parts = parse_url($base_url);
PA::$local_url = preg_replace("|/$|", "", @$base_url_parts['path'] ? $base_url_parts['path'] : "");
define("BASE_URL_REL", PA::$local_url);


// figure out CURRENT_DB.
if (preg_match("/\/([^\/]*)$/", $peepagg_dsn, $m)) {  //GLOBAL
    if (defined("CURRENT_DB")) {
    	echo "
         <h1>CURRENT_DB should not be defined in local_config.php.</h1>
         <p>Please edit <code>local_config.php</code> and remove the line that looks like this:</p>
         <pre><code>define(\"CURRENT_DB\", \"". htmlspecialchars(CURRENT_DB)."\");</code></pre>";
        exit;
    }
    define("CURRENT_DB", $m[1]);
} else {
	echo "
      <h1>Can't parse $peepagg_dsn</h1>
      <p>I can't find a database name in here: <code>" . htmlspecialchars($peepagg_dsn) . "</code>.</p>";
    exit;
}


// work out theme path and check that it exists
$current_theme_path = PA::$theme_url = PA::$url . "/$current_theme_rel_path"; //GLOBAL
define("CURRENT_THEME_REL_URL", BASE_URL_REL."/$current_theme_rel_path");
PA::$theme_path = "web/$current_theme_rel_path";
define("CURRENT_THEME_FSPATH", PA::$theme_path);
define("CURRENT_THEME_FS_CACHE_PATH", PA::$project_dir . "/web/cache");


?>