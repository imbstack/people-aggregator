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
require_once "HTTP/Client.php";

class InstallTests
{
    private $writable_dirs = array('log', 'networks', 'config', 'web/files', 'web/cache');
    private $writable_files = array('web/install/PeepAgg.mysql');
    private $test_type;
    private $test_data;
    public $error;
    public $allow_spawning;
    public $peepagg_dsn;

    public function __construct($test_type, $test_data = null)
    {
//        $this->note("test<pre>" . print_r($test_data,1) . "</pre>");
        $this->allow_spawning = false;
        $this->test_type = $test_type;
        $this->test_data = $test_data;
        $this->error = 0;
    }

    public function run()
    {
        if(method_exists($this, $this->test_type)) {
           $this->{$this->test_type}();
        } else {
           throw new Exception("InstallTests error: Unknown test method - \"$this->test_type\" requested." );
           exit;
        }
    }

    private function baseTests()
    {
        // check for php5
        $php_ver = phpversion();
        define('PA_MIN_PHP_VERSION', '5.1.4');
        if (!version_compare($php_ver, PA_MIN_PHP_VERSION, '>='))
        {
            $msg = 'PeopleAggregator requires PHP'.PA_MIN_PHP_VERSION.'or later (you are currently using version'.$php_ver.').';
            if (!version_compare($php_ver, '5.0', '>='))
            {
                $msg .= '<a href="http://wiki.peopleaggregator.org/PeopleAggregator_requires_PHP5">Click here for some information on getting PHP5 up and running on your web server</a>.';
            }
            $this->note($msg, 'error');
        }
        else
        {
            $this->note('Running on PHP'.$php_ver, 'ok');
        }

        // now we can include stuff that requires php5.
        //include dirname(__FILE__).'/common.php';
        // short_open_tag check
        if (ini_get('short_open_tag'))
        {
            $this->note('Short PHP open tags are enabled', 'ok');
        }
        else
        {
            $this->note('Short PHP open tags are disabled; you must edit your <code>php.ini</code> file and change the line that says <code>short_open_tag = Off</code> to say <code>short_open_tag = On</code>', 'error');
        }

        // check for mysql
        if (function_exists('mysql_connect'))
        {
            $this->note('MySQL private functions are available', 'ok');
        }
        else
        {
            $this->note('MySQL private functions are not available - you may have to compile the MySQL client into your PHP installation, or install a PHP MySQL package (e.g. <code>php5-mysql</code> on many Linux distributions)', 'error');
        }

        // check for PEAR::DB
        if (require_once('DB.php'))
        {
            $this->note('PEAR::DB is available', 'ok');
        }
        else
        {
            $this->note('PEAR::DB (PHP DB interface) is not installed', 'error');
        }

        // check for gd
        $gd_installed = function_exists('imagecreatefromjpeg');
        if ($gd_installed)
        {
            $this->note('GD is installed.', 'ok');
        }

        // check for imagemagick
        $f = popen('which convert', 'r');
        $convert = trim(fgets($f));
        pclose($f);
        if ($convert)
        {
            $this->note('ImageMagick is installed.', 'ok');
        }
        // warn if neither is installed
        if (!$gd_installed && !$convert)
        {
            // Currently the ImageResize class dies if neither GD or IM is
            // installed.  If we fix this, change this to a nonfatal error.
            $this->note('Neither GD or ImageMagick is installed, so we can\'t create thumbnails.', 'warn');//    warn("Neither GD or ImageMagick is installed.  This means that media thumbnails will not work, which will seriously degrade the user interface.");
        }

        // check for curl private functions
        if (function_exists('curl_init'))
        {
            $this->note('cURL extension is installed', 'ok');
        }
        else
        {
            $this->note('cURL extension is not installed', 'error');
        }

        // check for xml private functions
        if (extension_loaded('xml'))
        {
            $this->note('XML extension is installed', 'ok');
        }
        else
        {
            $this->note('XML extension is not installed', 'error');
        }
        //FIXME warn("FIXME: We might need to check for libxml2.  Not sure.");

        // check for dom extension
        if (extension_loaded('dom'))
        {
            $this->note('DOM extension is installed', 'ok');
        }
        else
        {
            $this->note('DOM extension is not installed; your host probably needs to install a PHP XML package.', 'error');
        }

        // check for dom extension
        if (extension_loaded('xsl'))
        {
            $this->note('XSL extension is installed', 'ok');
        }
        else
        {
            $this->note('XSL extension is not installed; your host probably needs to install a PHP XML/XSL package.', 'error');
        }

        // check for zlib
        if (extension_loaded('zlib'))
        {
            $this->note('zlib is installed', 'ok');
        }
        else
        {
            $this->note('zlib is not installed', 'error');
        }

        // check register_globals
        if (ini_get('register_globals'))
        {
            $this->note('<code>register_globals</code> is turned on.  Please turn it off (in <code>php.ini</code> or by adding <code>php_flag register_globals Off</code> into the &lt;VirtualHost&gt; block in your Apache configuration file).', 'warn');
        }
        else
        {
            $this->note('<code>register_globals</code> is turned off', 'ok');
        }

        // check that various directories are writable
        foreach ($this->writable_dirs as $d)
        {
            $full_path = PA::$project_dir . "/$d";
            $test_fn = "$full_path/test_file.txt";
            $f = @fopen($test_fn, 'wt');
            if ($f)
            {
                $this->note("The <code>$d</code> directory is writable.", 'ok');
                fclose($f);
                unlink($test_fn);
            }
            else
            {
                $this->note("The <code>$d</code> directory does not appear to be writable.  If you are on Linux, you can fix this with: <br><code>chmod -R a+w $full_path</code>", 'error');
            }
        }

        // check that various files are writable
        foreach ($this->writable_files as $file)
        {
            $full_path = PA::$project_dir . "/$file";
            if (is_writable($full_path))
            {
                $this->note("The <code>$file</code> file is writable.", 'ok');
            }
            else
            {
                $this->note("The <code>$file</code> file does not appear to be writable.  If you are on Linux, you can fix this with: <br><code>chmod -R a+w $full_path</code>", 'error');
            }
        }

        // find base url (minus http:// suffix)
        $page_url_bare = PA_SERVER_NAME;// PA_BASE_URL;
        // make sure the base url is valid
        $this->can_get_peepagg_txt(PA_CURRENT_SCHEME . "://$page_url_bare/peopleaggregator.txt");
        // try stripping off the first url part (i.e. www.asdf -> asdf)
        $page_url_suffix = preg_replace('|^[^\.]+\.(.*)$|', '$1', $page_url_bare);//PA_DOMAIN_SUFFIX;
        $this->allow_spawning = FALSE;
        // check if it doesn't have any dots (e.g. http://colinux/web/config/)
        // - i.e. not suitable for sharing cookies over domains.
        if ($page_url_suffix == $page_url_bare)
        {
            $this->note('Apparently running on an internal web server - not possible to run multiple networks.', 'warn');
        }
        elseif (preg_match('|^\d+\.\d+\.\d+\.\d+|', $page_url_bare))
        {
            $this->note('Running with an IP address rather than a domain name - not possible to run multiple networks.', 'warn');
        }
        else
        {
            $this->can_get_peepagg_txt(PA_CURRENT_SCHEME . "://$page_url_suffix/peopleaggregator.txt");
            if ($this->can_get_peepagg_txt(PA_CURRENT_SCHEME . "://some-random-domain.$page_url_suffix/peopleaggregator.txt"))
            {
                $this->note("It looks like the server is set up to host <code>*.$page_url_suffix</code>, so network spawning is possible.", 'ok');
                $this->allow_spawning = TRUE;
            }
            else
            {
                $this->note('Wildcard domains do not appear to be enabled, so network spawning will be disabled.', 'warn');
            }
        }
    }

    private function dbTest() {
      global $peepagg_dsn;

      $params = $this->test_data;

      if(empty($params['db_user'])) {
        $this->note("You must supply a database user name", 'error');
        return false;
      }

      if(empty($params['db_name'])) {
        $this->note("You must supply a database name", 'error');
        return false;
      }

      if(empty($params['db_host'])) {
        $this->note("You must supply a host name", 'error');
        return false;
      }

      if(empty($params['db_password'])) {
        $this->note("You may not use a blank password for the MySQL connection", 'error');
        return false;
      }

      $user_link = @mysql_connect($params['db_host'], $params['db_user'], $params['db_password']);
      if($user_link) {
         $this->note("Able to connect to the MySQL server at <code>{$params['db_host']}</code> with supplied login details.", 'ok');

         // make sure the DB isn't already populated
         if(!mysql_select_db($params['db_name'], $user_link)) {
           mysql_close($user_link);
           $user_link = FALSE;
           if((!empty($params['mysql_root_username'])) && (!empty($params['mysql_root_password']))) {
              // if we have root credentials we will try to create database - so, no exit yet!
             $this->note("Database <code>{$params['db_name']}</code> does not exist or is inaccessible.", 'info');
           } else {
             $this->note("Database <code>{$params['db_name']}</code> does not exist or is inaccessible.", 'error');
             return false;
           }
         } else {
           $sth = $this->run_query("SHOW TABLES", $user_link);
           if(mysql_num_rows($sth)) {
             $this->note("The database <code>{$params['db_name']}</code> already contains data.  Please wipe it out or recreate the database before installing PeopleAggregator.", 'error');
             return false;
           }
         }
      } else {
         if((!empty($params['mysql_root_username'])) && (!empty($params['mysql_root_password']))) {
            // if we have root credentials we will try to create database - so, no exit yet!
           $this->note("Unable to connect to the MySQL server using the supplied login details", 'warn');
         } else {
           $this->note("Unable to connect to the MySQL server using the supplied login details", 'error');
           return false;
         }
      }

      if(!$user_link && (!empty($params['mysql_root_username'])) && (!empty($params['mysql_root_password']))) {
        $this->note("Trying administrator login...");
        $admin_link = @mysql_connect($params['db_host'], $params['mysql_root_username'], $params['mysql_root_password']);

        if(!$admin_link) {
           $this->note("Unable to connect to the MySQL server with the supplied login details or as an administrator", 'error');
           return false;
        } else {
           $this->note("Able to connect to the MySQL server with the supplied administrator login details - a new database will be created.", 'ok');
        }

        // make sure the db doesn't already exist
        if(mysql_select_db($params['db_name'], $admin_link)) {
           $this->note("The database <code>{$params['db_name']}</code> already exists. Please, choose a different name for a new database.", 'error');
           return false;
        }

        // create it
        $sql = "CREATE DATABASE ".$this->db_esc($params['db_name']);
//        $rollback_cmds[] = array("sql", "DROP DATABASE ".$this->db_esc($params['db_name']), $admin_link);
        $this->run_query($sql, $admin_link);

        // now grant permissions with successively looser hostnames until
        // we find one that lets the web server access the database.
        foreach (array("localhost",
                       "localhost.localdomain",
                       $_SERVER['SERVER_NAME'],
                       gethostbyname($_SERVER['SERVER_NAME']),
                       "%",
                      ) as $server_host) {
           $sql = "GRANT ALL ON ".$this->db_esc($params['db_name']).
                  ".* TO ".$this->db_esc($params['db_user'])."@".$this->db_esc($server_host).
                  " IDENTIFIED BY '".mysql_real_escape_string($params['db_password'])."'";
           $this->run_query($sql, $admin_link);
           if(($user_link = mysql_connect($params['db_host'], $params['db_user'], $params['db_password']))
           && mysql_select_db($params['db_name'], $user_link)) {
              $this->note("Successfully logged in to new database using credentials from host $server_host", 'ok');
              break;
           }
        }

        if(!$user_link) {
          $this->note("Failed to grant access credentials that would allow the web server to access the database.  Please try creating the database manually.", 'error');
          return false;
        }
      }

      if(!$user_link) {
        $this->note("Something went wrong - we should have successfully connected to the DB by now", 'error');
        return false;
      }
      $this->note("The database was successfully created.", 'ok');

      // now set up databases
      $this->note("Initializing database ... ", 'info');
      if($sql_file = getShadowedPath("web/install/PeepAgg.mysql")) {
        if($this->run_query_file($sql_file, $user_link)) {
           $this->note("The database was successfully populated.", 'ok');
           define("CURRENT_DB", $params['db_name']);
           $peepagg_dsn = "mysql://". $params['db_user'] .
                                 ":". $params['db_password'] .
                                 "@". $params['db_host'] .
                                 "/". $params['db_name'];
           $this->peepagg_dsn = $peepagg_dsn;
        } else {
          $this->note("The installer is unable to execute MySQL queries.", 'error');
          return false;
        }
      } else {
        $this->note("File <code>$fn</code> does not exists.", 'error');
        return false;
      }

     // now run upgrade scripts
      $this->note("Running database upgrade script.", 'info');
      try {
        $network_prefix = 'default';
        require_once "web/update/run_scripts.php";
        run_update_scripts(true);
        $this->note("The database was successfully upgraded.", 'ok');
      } catch (Exception $e) {
        $this->note("Error updating database: ".$e->getMessage(), 'error');
        return false;
      }

      return true;
    }

    private function run_query_file($fn, $user_link) {
      $sql = file_get_contents($fn);
      if(!$sql) {
        $this->note("Unable to read <code>$fn</code> file.", 'error');
        return false;
      }

      $queries = array();
      $query = "";
      foreach (preg_split("/\n/", $sql."\n") as $line) {
        // skip blank lines and comments
        if (!trim($line)) continue;
        if (preg_match("/^\s*\-\-/", $line)) continue;

        // collect query
        $query .= $line;
        if (preg_match("/;\s*$/", $line)) {
            $queries[] = $query;
            $query = "";
        }
      }

      // and run them!
      foreach ($queries as $query) {
        $this->run_query(trim($query), $user_link);
      }
      $this->note("Ran ".count($queries)." database queries from file <code>$fn</code>", 'info');
      return true;
    }


    private function note($msg, $type = '')
    {
        switch ($type)
        {
        case 'ok':
            $color = 'green';
            $status = 'OK';
            break;
        case 'info':
            $color = 'blue';
            $status = 'INFO';
            break;
        case 'warn':
            $color = 'orange';
            $this->error = 1;
            $status = 'WARNING';
            break;
        case 'error':
            $color = 'red';
            $this->error = 2;
            $status = 'ERROR';
            break;
            default: $color = '#000';
            $status = '...';
        }
        echo "<tr><td>$msg</td><td style='color:$color'>$status</td></tr>";
        flush();
    }

    private function can_download($url, &$res)
    {
        $client = new HTTP_Client;
        $e = $client->get($url);
        if (PEAR::isError($e))
        {
            $res = $e->getCode().'('.htmlspecialchars($e->getMessage()).')';
            return FALSE;
        }
        $resp = $client->currentResponse();
        $code = $resp['code'];
        if ($code !== 200)
        {
            $res = "bad status - $code";
            return FALSE;
        }
        else
        {
            $res = '200 OK';
        }
        $body = $resp['body'];
        return $body;
    }

    private function can_get_peepagg_txt($url)
    {
        $res = null;
        $body = $this->can_download($url, $res);
        if ((!$body) || (strpos($body, 'config/index.php') === FALSE))
        {
            $this->note("Trying to download <code>$url</code><br />Result: $res", 'warn');
            flush();
            return false;
        }
        else
        {
            $this->note("Trying to download <code>$url</code>", 'ok');
            flush();
            return true;
        }
    }

    private function run_query($sql, $link) {
      if (!($sth = mysql_query($sql, $link))) {
        throw new Exception(mysql_error());
        exit;
      }
      return $sth;
    }

    private function db_esc($s) {
      return "`".mysql_real_escape_string($s)."`";
    }

    public function showStatus(&$installer)
    {
        $err_clss = array('msg_ok', 'msg_warn', 'msg_err');
        $class = $err_clss[$this->error];
        $html = "<div id='inst_msg' class='$class'>";
        switch ($this->error)
        {
        case 0:
            $msg = 'This test passed completly. Please, press \'Next\' button.';
            $installer->error = false;
            break;
        case 1:
            $msg = 'This test passed but some features probably will not be available.';
            $installer->error = false;
            break;
        case 2:
            $msg = 'This test fail! Installer is unable to continue. Please, correct errors and then try again.';
            $installer->error = true;
            break;
        }
        $html .= "$msg</div>";
        return $html;
    }
}
?>
