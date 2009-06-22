<?php

error_reporting(E_ALL);
  if(($_SERVER['REQUEST_METHOD'] != 'POST')) { ?>
  
  <form name="install_form" id="install_form" method="POST" action="<?= "index.php" ?>#check">
  <div style="text-align: center; margin-top: 5em;">
     <h1><a href="#" onclick="javascript: document.forms['install_form'].submit();"><img border="0" src="images/palogo_black_bg.jpg" alt="PeopleAggregator"></a></h1>
     <p><a href="#" onclick="javascript: document.forms['install_form'].submit();">Click here to set up PeopleAggregator</a>.</p>
  </div>
  </form>
<?php
 exit;
}
 
// signal to config.inc not to start up output buffering
define("PA_DISABLE_BUFFERING", TRUE);

// flag to say we are still configuring
define("PEEPAGG_CONFIGURATION", 1);


// flag to say if we hit any fatal errors during checking
$is_fatal = FALSE;


function note($msg) {
    echo "<li>$msg</li>";
    flush();
}

function warn($msg) {
    echo "<li class='error'>WARNING: $msg</li>";
    flush();
}

function fatal($msg) {
    global $is_fatal;
    $is_fatal = TRUE;

    echo "<li class='error'>FATAL: $msg</li>";
    flush();
}

function dienow($msg) {
    fatal($msg);
    exit;
}

?><html>
<head>
<title>PeopleAggregator setup</title>
<style type="text/css"><!--

.error, .good {
    font-weight: bold;
}

.error {
   color: red;
}

.good {
    color: green;
}

.config p {
    margin-left: 205px;
}

.config_item {
    clear: left;
}

.config_item label {
    float: left;
    width: 200px;
    margin-right: 5px;
    text-align: right;
}

// --></style>
</head>
<body>

<h1><?= __('PeopleAggregator setup') ?></h1>

<?php

$local_config_path = PA::$project_dir ."/web/config";
$config_fn = "$local_config_path/local_config.php";
$final_config_fn = $config_fn;


// already configured?
if (file_exists($final_config_fn)) {
    ?>

    <h2><?= __('Already configured') ?></h2>

    <p><?= __('It looks like this PeopleAggregator is already set up.') ?> <a href="../"><?= __('Click here to log in') ?></a>!</p>

    <p><?= __('If something went wrong and you want to reconfigure PeopleAggregator, delete the') ?> <code><?php echo $final_config_fn ?></code> <?= __('file and refresh this page') ?>.</p>

    <?php
    exit;
}

// partly configured?
if (file_exists($config_fn)) {
    ?>

    <h2><?= __('Almost done!') ?></h2>

    <p><?= __('Congratulations - you have nearly finished installing PeopleAggregator!') ?></p>

    <p><?= __('The final step is to move the') ?> <code>local_config.php</code> <?= __('file from') ?> <code><?php echo realpath($config_fn) ?></code> <?= __('up into the') ?> <code><?php echo PA::$path ?>/</code> <?= __('directory') ?>.</p>

    <p><?= __('On Linux, the following command will do so') ?>:</p>

    <p style="margin-left: 5em"><code>mv <?php echo $config_fn ?> <?php echo PA::$path ?>/</code></p>

    <p><?= __('Once that is done, please refresh this page') ?>.</p>

    <p><?= __('Alternatively, if something went wrong and you want to reconfigure PeopleAggregator, delete') ?> <?= __('the') ?> <code><?php echo $config_fn ?></code> <?= __('file and refresh this page') ?>.</p>

    <?php
    exit;
}

// Check file permissions

$f = @fopen($config_fn, "wt");
if ($f) {
    fclose($f);
    unlink($config_fn);
} else {
    ?>

    <h2>Please make the config directory writable</h2>
      
    <p>To configure PeopleAggregator, the web server needs to be able to write to the <b>config</b> subdirectory (<?php echo $local_config_path ?>).</p>
      
    <p>On Linux/Unix, the following command will sort things out:</p>

    <p style="margin-left: 5em"><code>chmod -R a+w <?php echo $local_config_path ?></code></p>

    <p>After you have made the directory writable, hit REFRESH in your browser to continue the installation.</p>

    <?php 
    exit;
}

?>

<h2>Prerequisites</h2>

<ul>
<?php

if (file_exists(PA::$project_dir . "/db/dist_files.txt")) {
    $do_auto_update = TRUE;
} else {
    $do_auto_update = FALSE;
    warn("<code>db/dist_files.txt</code> not found! (<a href='http://wiki.peopleaggregator.org/Error:_db/dist_files.txt_not_found' target='_blank'>help!</a>)<br>  This means that this is NOT an official installation package and auto-update WILL NOT WORK.<br>  If you are installing from Subversion rather than using an official download, ignore this warning.");
}

note("Install root (path prefix): " . PA::$path);

// check for php5
$php_ver = phpversion();
note("Running on PHP ".$php_ver);
define("PA_MIN_PHP_VERSION", "5.1.4");
if (!version_compare($php_ver, PA_MIN_PHP_VERSION, ">=")) {
    $msg = 'PeopleAggregator requires PHP '.PA_MIN_PHP_VERSION.' or later (you are currently using version '.$php_ver.').';
    if (!version_compare($php_ver, "5.0", ">=")) {
	$msg .= '  <a href="http://wiki.peopleaggregator.org/PeopleAggregator_requires_PHP5">Click here for some information on getting PHP5 up and running on your web server</a>.';
    }
    dienow($msg);
}

// now we can include stuff that requires php5.
include dirname(__FILE__).'/common.php';

// short_open_tag check
if (ini_get("short_open_tag")) {
    note("Short PHP open tags are enabled");
} else {
    fatal("Short PHP open tags are disabled; you must edit your <code>php.ini</code> file and change the line that says <code>short_open_tag = Off</code> to say <code>short_open_tag = On</code>");
}

// check for mysql
if (function_exists("mysql_connect")) {
    note("MySQL functions are available");
} else {
    fatal("MySQL functions are not available - you may have to compile the MySQL client into your PHP installation, or install a PHP MySQL package (e.g. <code>php5-mysql</code> on many Linux distributions)");
}

// check for PEAR::DB
if (@include("DB.php")) {
    note("PEAR::DB is available");
} else {
    fatal("PEAR::DB (PHP DB interface) is not installed");
}

// check for gd
$gd_installed = function_exists("imagecreatefromjpeg");
if ($gd_installed) note("GD is installed.");

// check for imagemagick
$f = popen("which convert", "r");
$convert = trim(fgets($f));
pclose($f);
if ($convert) note("ImageMagick is installed.");

// warn if neither is installed
if (!$gd_installed && !$convert) {
    // Currently the ImageResize class dies if neither GD or IM is
    // installed.  If we fix this, change this to a nonfatal error.
    fatal("Neither GD or ImageMagick is installed, so we can't create thumbnails.");
//    warn("Neither GD or ImageMagick is installed.  This means that media thumbnails will not work, which will seriously degrade the user interface.");
}

// check for curl functions
if (function_exists("curl_init")) {
    note("cURL extension is installed");
} else {
    fatal("cURL extension is not installed");
}

// check for xml functions
if (extension_loaded("xml")) {
    note("XML extension is installed");
} else {
    fatal("XML extension is not installed");
}
//FIXME warn("FIXME: We might need to check for libxml2.  Not sure.");

// check for dom extension
if (extension_loaded("dom")) {
    note("DOM extension is installed");
} else {
    fatal("DOM extension is not installed; your host probably needs to install a PHP XML package.");
}

// check for dom extension
if (extension_loaded("xsl")) {
    note("XSL extension is installed");
} else {
    fatal("XSL extension is not installed; your host probably needs to install a PHP XML/XSL package.");
}

// check for zlib
if (extension_loaded("zlib")) {
    note("zlib is installed");
} else {
    fatal("zlib is not installed");
}

// check register_globals
if (ini_get("register_globals")) {
    warn("<code>register_globals</code> is turned on.  Please turn it off (in <code>php.ini</code> or by adding <code>php_flag register_globals Off</code> into the &lt;VirtualHost&gt; block in your Apache configuration file).");
}

// check that various directories are writable
foreach (array("log", "networks", "web/files", "web/cache", "web/sb-files") as $d) {
    $full_path = PA::$project_dir . "/$d";
    $test_fn = "$full_path/test_file.txt";
    $f = @fopen($test_fn, "wt");
    if ($f) {
        note("The <code>$d</code> directory is writable.");
        fclose($f);
        unlink($test_fn);
    } else {
        fatal("The <code>$d</code> directory does not appear to be writable.  If you are on Linux, you can fix this with: <br><code>chmod -R a+w $full_path</code>");
    }
}

// install root

?>
</ul>
<?php

if ($is_fatal) {
    ?>
<h2>Unable to set up PeopleAggregator</h2>

<p class="error">Your server is not currently capable of running PeopleAggregator.  Please fix any errors marked 'FATAL' above and try again.</p>

    <?php
    exit;
}

?>

<p class="good">Congratulations, it looks like your server can run PeopleAggregator!</p>

<?php

install_peopleaggregator();

?>
</body>
</html>