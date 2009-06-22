<?php

// This file is here to keep stuff out of web/config/index.php that
// will break under PHP4.  This lets index.php check for PHP5 and give
// a nice error rather than just failing with a parse error
// (unexpected T_NEW, etc).

class Installation_Failure extends Exception {
}

function run_query($sql, $link, $quiet=FALSE) {
    if (!$quiet) note($sql);
    if (!($sth = mysql_query($sql, $link))) {
        throw new Installation_Failure(mysql_error());
    }
    return $sth;
}

// just in case this will throw an error if you don't have PEAR,
// include it down here so the user doesn't get a blank page.
require_once "HTTP/Client.php";

function can_download($url) {
    echo "<li>Trying to download <code>$url</code> ... "; flush();
    $client = new HTTP_Client;
    $e = $client->get($url);
    if (PEAR::isError($e)) {
	echo "error ".$e->getCode()." (".htmlspecialchars($e->getMessage()).")<br>";
        return FALSE;
    }

    $resp = $client->currentResponse();
    $code = $resp['code'];
    if ($code !== 200) {
        echo "bad status: $code</li>"; flush();
        return FALSE;
    }

    $body = $resp['body'];
    echo strlen($body) . " bytes</li>"; flush();
    return $body;
}

function can_get_peepagg_txt($url) {
    $body = can_download($url);
    if (!$body) return FALSE;

    return strpos($body, "config/index.php") !== FALSE;
}

function get_default($key, $default) {
    $value = @$_REQUEST[$key];
    if ($value) return $value;
    return $default;
}

function focus_field($field) {
?>
<script type="text/javascript" language="javascript"><!--
    var n = document.getElementById("<?php echo $field ?>");
    n.focus();
// --></script>
<?php
}

function db_esc($s) {
    return "`".mysql_real_escape_string($s)."`";
}

function run_query_file($fn, $user_link) {
    $sql = file_get_contents($fn);
    if (!$sql) throw new Installation_Failure("Unable to read PeepAgg.mysql"); 
    
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
        run_query(trim($query), $user_link, TRUE);
    }

    note("Ran ".count($queries)." database queries from file <code>$fn</code>");
}

function install_peopleaggregator() {
    // global var $path_prefix has been removed - please, use PA::$path static variable

    ?>

<h2>Detecting URLs</h2>

<ul>
<?php
/*
// find base url (minus http:// suffix)
if (!preg_match("|^(.*?)/config/index.php$|", $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'], $m)) {
    dienow("Unable to find base URL");
}
*/
// find base url (minus http:// suffix)
$page_url_bare = PA_SERVER_NAME . rtrim(PA_SCRIPT_PATH, "/");

// make sure the base url is valid
if (!can_get_peepagg_txt("http://$page_url_bare/peopleaggregator.txt")) {
    dienow("Unable to guess base URL - I think it should be http://$page_url_bare/ but that URL does not seem to work.");
}
note("Good: we can access the system at http://$page_url_bare/");

// try stripping off the first url part (i.e. www.asdf -> asdf)
$page_url_suffix = preg_replace("|^[^\.]+\.(.*)$|", "$1", $page_url_bare);

$allow_spawning = FALSE;

// check if it doesn't have any dots (e.g. http://colinux/web/config/)
// - i.e. not suitable for sharing cookies over domains.
if ($page_url_suffix == $page_url_bare) {
    note("Apparently running on an internal web server - not possible to run multiple networks.");
}
else if (preg_match("|^\d+\.\d+\.\d+\.\d+|", $page_url_bare)) {
    note("Running with an IP address rather than a domain name - not possible to run multiple networks.");
}
else {
    if (can_get_peepagg_txt("http://$page_url_suffix/peopleaggregator.txt")) {
	note("It looks like it is also accessible at <code>http://$page_url_suffix/</code>; trying <code>$page_url_suffix</code> as the root URL.");
    } else {
	note("It is not accessible at <code>http://$page_url_suffix/</code>; trying <code>$page_url_bare</code> as the root URL.");
	$page_url_suffix = $page_url_bare;
    }

    if (can_get_peepagg_txt("http://some-random-domain.$page_url_suffix/peopleaggregator.txt")) {
	note("It looks like the server is set up to host <code>*.$page_url_suffix</code>, so network spawning is possible.");
	$allow_spawning = TRUE;
    } else {
	warn("Wildcard domains do not appear to be enabled (cannot access the root of this install at http://some_random_domain.$page_url_suffix/) so network spawning will be disabled.");
    }
}

// global  $domain_suffix;

if ($allow_spawning) {
    $page_url = "http://%network_name%.$page_url_suffix";
} else {
    $page_url = "http://$page_url_bare";
}

if(PA_DOMAIN_SUFFIX != false) {
  $domain_suffix = PA_DOMAIN_SUFFIX; // preg_replace("|/.*$|", "", $page_url_suffix);
  $domain_suffix_string = "\"".PA_DOMAIN_SUFFIX."\"";
} else {
  $domain_suffix = false;
  $domain_suffix_string = "FALSE";
}

// stash $page_url away as config.inc will modify it
$page_url_config = $page_url;

note("Base URL: <code>$page_url</code>" . ($domain_suffix ? "; domain suffix: <code>$domain_suffix</code>" : ""));

?>
</ul>

<h2>Configuration</h2>

<?php

$admin_password = get_default("admin_password", "");
$admin_password2 = get_default("admin_password2", "");

$mysql_server = get_default("mysql_server", "localhost");
$mysql_dbname = get_default("mysql_dbname", "peopleaggregator");
$mysql_username = get_default("mysql_username", "peopleaggregator");
$mysql_password = get_default("mysql_password", "");

$mysql_root_username = get_default("mysql_root_username", "root");
$mysql_root_password = get_default("mysql_root_password", "");

$home_network_config = str_replace("%network_name%", "www", $page_url) . "/config/";

?>

<form method="POST" action="<?= "index.php" ?>#check">
<div class="config">

<p>Some operations (upgrading, and content administration) require an administrator password for access.  Please enter an administrator password here.</p>

<div class="config_item"><label for="admin_password">Admin password</label>
 <input type="password" id="admin_password" name="admin_password" value="<?php echo $admin_password ?>"><?php if (!$admin_password) echo " &larr; must not be blank!"; ?></div>
<div class="config_item"><label for="admin_password">Repeat admin password</label>
 <input type="password" id="admin_password2" name="admin_password2" value="<?php echo $admin_password2 ?>"><?php if (!$admin_password2) echo " &larr; must not be blank!"; else if ($admin_password != $admin_password2) echo " &larr; must be the same as above!"; ?></div>

<p>Enter your database details below.</p>

<div class="config_item"><label for="mysql_server">MySQL server</label>
 <input type="text" name="mysql_server" value="<?php echo $mysql_server ?>"></div>
<div class="config_item"><label for="mysql_dbname">MySQL database name</label>
 <input type="text" name="mysql_dbname" value="<?php echo $mysql_dbname ?>"></div>
<div class="config_item"><label for="mysql_username">MySQL username</label>
 <input type="text" name="mysql_username" value="<?php echo $mysql_username ?>"></div>
<div class="config_item"><label for="mysql_password">MySQL password</label>
 <input type="password" name="mysql_password" value="<?php echo $mysql_password ?>"><?php if (!$mysql_password) echo " &larr; must not be blank!"; ?></div>

<p>If the database has not been created yet, you can enter your database administrator ("root") login details here to have it created automatically:</p>

<div class="config_item"><label for="mysql_root_username">Administrator username</label>
 <input type="text" name="mysql_root_username" value="<?php echo $mysql_root_username ?>"></div>
<div class="config_item"><label for="mysql_root_password">Administrator password</label>
 <input type="password" name="mysql_root_password" value="<?php echo $mysql_root_password ?>"></div>

<p><input type="submit" value="Set up PeopleAggregator"></p>

</div>
</form>
<?php

// only exec the rest after someone clicks 'setup'
if (!$_POST) exit;


// wrap install process in exception handler so we can roll back
$rollback_cmds = array();
try {

?>
<h2 id="check">Checking config details</h2>

<ul>
<?php

if (!$admin_password) {
    focus_field("admin_password");
    dienow("You must supply an admin password");
}

if ($admin_password != $admin_password2) {
    focus_field("admin_password");
    dienow("Both admin paswords must be the same");
}

if (!$mysql_password) {
    focus_field("mysql_password");
    dienow("You may not use a blank password for the MySQL connection");
}

$user_link = @mysql_connect($mysql_server, $mysql_username, $mysql_password);
if ($user_link) {
    note("Able to connect to the MySQL server at $mysql_server with supplied login details.");
    // make sure the DB isn't already populated

    if (!mysql_select_db($mysql_dbname, $user_link)) {
        note("Database does not exist or is inaccessible");
        mysql_close($user_link);
        $user_link = FALSE;
    } else {
        $sth = run_query("SHOW TABLES", $user_link);
        if (mysql_num_rows($sth)) throw new Installation_Failure("The database $mysql_dbname already contains data.  Please wipe it out or recreate the database before installing PeopleAggregator.  If PeopleAggregator is already installed here, you will have to create your local_config.php file manually.");
    }

} else {
    note("Unable to connect to the MySQL server using the supplied login details");
}

if (!$user_link) {
    note("Trying administrator login...");
    $admin_link = @mysql_connect($mysql_server, $mysql_root_username, $mysql_root_password);
    if (!$admin_link) dienow("Unable to connect to the MySQL server with the supplied login details or as an administrator");
    note("Able to connect to the MySQL server with the supplied administrator login details - a new database will be created.");

    // make sure the db doesn't already exist
    if (mysql_select_db($mysql_dbname, $admin_link)) {
        throw new Installation_Failure("Database $mysql_dbname already exists");
    }

    // create it
    $sql = "CREATE DATABASE ".db_esc($mysql_dbname);
    $rollback_cmds[] = array("sql", "DROP DATABASE ".db_esc($mysql_dbname), $admin_link);
    run_query($sql, $admin_link);

    // now grant permissions with successively looser hostnames until
    // we find one that lets the web server access the database.
    foreach (array("localhost",
                   "localhost.localdomain",
                   $_SERVER['SERVER_NAME'],
                   gethostbyname($_SERVER['SERVER_NAME']),
                   "%",
                   ) as $server_host) {
	$sql = "GRANT ALL ON ".db_esc($mysql_dbname).".* TO ".db_esc($mysql_username)."@".db_esc($server_host)." IDENTIFIED BY '".mysql_real_escape_string($mysql_password)."'";
        run_query($sql, $admin_link);

        if (($user_link = mysql_connect($mysql_server, $mysql_username, $mysql_password))
            && mysql_select_db($mysql_dbname, $user_link)) {
            note("Successfully logged in to new database using credentials from host $server_host");
            break;
        }
    }

    if (!$user_link) {
        throw new Installation_Failure("Failed to grant access credentials that would allow the web server to access the database.  Please try creating the database manually.");
    }
}

if (!$user_link) throw new Installation_Failure("Something went wrong - we should have successfully connected to the DB by now");

// set all local_config.php vars
global $peepagg_dsn;
$peepagg_dsn = "mysql://$mysql_username:$mysql_password@$mysql_server/$mysql_dbname";
$logger_logFile = "log/pa.log";
$default_relation_id = 1;

// now write out local_config.php
$local_config_text = "<"."?php

// local_config.php: This file contains server-specific settings like
// the database password, the base URL of this installation, and
// debugging flags.  Anything in default_config.php can be overridden
// here.

// If you want to change project-specific things like the site name,
// you can use project_config.php.

// Global defaults, which are shared by all other PeopleAggregator
// installations, are in default_config.php.

// Database details.
\$peepagg_dsn = \"$peepagg_dsn\";

// URL to the root of the server.
\$base_url = \"$page_url_config\";

// Parent domain part of the URL.
\$domain_suffix = $domain_suffix_string;
";

if ($allow_spawning) {
    $local_config_text .= "
// Network operation is enabled.  To disable, set \$_PA->enable_networks
// to FALSE.  To disable network spawning without deactivating existing
// networks, set \$_PA->enable_network_spawning to FALSE.
";
} else {
    $local_config_text .= "
// Network operation disabled as wildcard domains are not configured.
// Comment out the following line to enable network creation (after
// configuring wildcard DNS, etc).
\$_PA->enable_networks = FALSE;
";
}

$local_config_text .= "
// Path to log file (you may wish to change this to /var/log/somewhere/pa.log).
\$logger_logFile = PA::\$project_dir.\"/log/pa.log\";

// Administration password
\$admin_password = \"$admin_password\";

// When a new user registers on the site, they will automatically be marked as a friend of the user with this ID.
// (The default is 1, so everyone will be a friend of the first user.)
\$default_relation_id = $default_relation_id;

?".">
";

global $config_fn;
note("Writing local_config.php");
$rollback_cmds[] = array("delete", $config_fn);
if (!file_put_contents($config_fn, $local_config_text)) throw new Installation_Failure("Unable to write $config_fn");
// define LOCAL_CONFIG_OVERRIDE to tell config.inc to load our new
// temporary local_config.php rather than look for it in the global
// location
// define("LOCAL_CONFIG_LOCATION_OVERRIDE", $config_fn);
//

define("CURRENT_DB", $mysql_dbname);

// now set up databases
note("Initializing database ... ");
if(file_exists(PA::$project_dir . "/db/PeepAgg.mysql")) {
  run_query_file(PA::$project_dir . "/db/PeepAgg.mysql", $user_link);
} else {
  run_query_file(PA::$core_dir . "/db/PeepAgg.mysql", $user_link);
}

note("Running database upgrade script and installing default module settings ... ");
try {
    require_once "web/update/run_scripts.php";
    run_update_scripts();
} catch (Exception $e) {
    throw new Installation_Failure("Error updating database or installing default module settings: ".$e->getMessage());
}

/*
/* NOTE: AutoUpgrade no longer supported
/*       
/*

global $do_auto_update;
if (!$do_auto_update) {
    note("Skipping auto-upgrade preparation as it is disabled for this installation.");
} else {
    note("Preparing system for auto-upgradeability ... ");
    require_once "Subversion/PAStateStore.php";
    try {
        $store = new Subversion_PAStateStore(PA::$path);
        $store->initialize();
        note("Subversion update root: <code>".$store->get_repository_root()."</code>; path: <code>".$store->get_repository_path()."</code>; revision: ".$store->get_revision());
    } catch (Exception $e) {
        throw new Installation_Failure("Error preparing auto-upgrade system: ".$e->getMessage());
    }
}
*/

$str = "Congratulations! The People Aggregator successfully installed on your server. \n" .
       "If you want to re-install People Aggregator application, delete \"local_config.php\"\n" .
       "file and installation process will run again. \n";
       
$fp = fopen(PA::$project_dir . "/web/config/install.log", 'w+');
if($fp) {
  fwrite($fp, $str);
  fclose($fp);
}

?>
</ul>
<div style="width: 75%; padding: 24px;" >
<h2>All done!</h2>

<p class="good">Your database has been initialized and a <code>local_config.php</code> file has been written at <code><?php echo $config_fn ?></code>.
  To finish the installation, please <a href="../">click here</a>.
</p>
</div>
<?php exit; ?>

<?php

//throw new Installation_Failure("foo");

} catch (Installation_Failure $e) {
    warn("Installation failed (".$e->getMessage().") - undoing operations");
    foreach (array_reverse($rollback_cmds) as $cmd) {
        switch ($cmd[0]) {
        case 'sql':
            list(, $sql, $link) = $cmd;
            note("DB query: $sql");
            mysql_query($sql, $link);
            break;

        case 'delete':
            list(, $fn) = $cmd;
            note("Delete: $fn");
            unlink($fn);
            break;

        default:
            warn("Unknown rollback command type: ".$cmd[0]);
            break;
        }
    }
    die();
}

}

?>