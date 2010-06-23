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

if (file_exists("official")) {
    fail("This script should not be run from inside a PeopleAggregator installation.  It should be copied onto a new web server to bootstrap the installation process.");
}

if (file_exists("_pa_priv") || file_exists("web/peopleaggregator.txt")) {
    fail("PeopleAggregator has already been downloaded.  If you want to reinstall, remove all files in this folder except <code>bootstrap_install.php</code> and refresh this page.");
}

function have_command($cmd) {
    $fn = trim(shell_exec("which $cmd"));
    if (file_exists($fn)) return TRUE;
    return FALSE;
}

function fail($msg) {
    echo "<b>$msg</b>";
    exit;
}

function download($url, $path) {
    if (!($f = fopen($path, "wb"))) {
	fail("Cannot write to file <code>$path</code>.  Do you need to set some directory permissions?");
    }

    if (!preg_match("|^http://([^/]*)(.*)$|", $url, $m)) {
	fail("Invalid URL: <code>$url</code>");
    }
    list(, $url_host, $url_path) = $m;

    echo "<p>Downloading $url "; flush();

    $s = fsockopen($url_host, 80, $errno, $errstr);
    if (!$s) {
	fail("Unable to open socket: error $errno / $errstr");
    }
    fputs($s, "GET $url_path HTTP/1.0\r\nHost: $url_host\r\nUser-Agent: PeopleAggregator bootstrap script\r\n\r\n");

    // read headers
    $content_length = -1;
    while (1) {
	$line = trim(fgets($s));
	if (!$line) break;
	
//	echo "header: $line<br>";
	if (preg_match("|^HTTP/[^ ]+ (\d+) (.*)$|", $line, $m)) {
	    list(, $code, $msg) = $m;
	    if ($code != 200) fail("received bad status from server: $code $msg");
	} elseif (preg_match("|^([A-Za-z\-]+)\:\s*(.*)$|", $line, $m)) {
	    list(, $k, $v) = $m;
	    if ($k == "Content-Length") $content_length = (int)$v;
	} else {
	    fail("invalid http header received: $line");
	}
    }
    if ($content_length < 0) fail("missing Content-Length header in response");
    echo "($content_length bytes):</p>";
    
    // read data
    $bytes_left = $content_length;
    $percent = 0;
    while ($bytes_left > 0) {
	set_time_limit(60);
	$get_len = 128*1024;
	if ($get_len > $bytes_left) $get_len = $bytes_left;
	$data = fread($s, $get_len + 1);
	if (!$data) fail("an error occurred reading data from the socket");
//	echo "read ".strlen($data)." bytes ($get_len?)... "; flush();
	$bytes_left -= strlen($data);
	$progress = 1.0 - ((float)$bytes_left / (float)$content_length);
	$new_percent =(int)($progress * 100.0);
	if ($new_percent != $percent) {
	    echo "$percent% ... "; flush();
	    $percent = $new_percent;
	}
	fwrite($f, $data);
    }
    echo "done!<br>";
    
    fclose($s);

    fclose($f);
}

$htaccess_written = FALSE;
function add_to_htaccess($text) {
    global $htaccess_written;
    if (!($f = @fopen(".htaccess", $htaccess_written ? "at" : "wt"))) {
	fail("Unable to create file <code>".getcwd()."/.htaccess</code>.  You probably need to change permissions on the current directory; please either make it world-writable or owned by the web server user.");
    }
    fwrite($f, $text);
    fclose($f);
    $htaccess_written = TRUE;
}

if (!preg_match("/^5\./", phpversion())) {
    echo "<p>Not running PHP5.  Your host might just not be configured to use it for .php files.  I'll try to write a .htaccess file to fix that.</p>";
    add_to_htaccess("AddHandler x-httpd-php5 .php
AddHandler x-httpd-php .php4
");
}

if ($htaccess_written) {
    fail("Now hit the REFRESH button in your browser.  If this message comes up again, please check with your host whether it is possible to fix the errors above.");
}

echo "<p>You are running PHP version ".phpversion().".</p>";

function run($cmd) {
    set_time_limit(60);
    echo "<p><b>".htmlspecialchars($cmd)."</b></p><pre>";
    system("$cmd 2>&1");
    echo "</pre>";
}

system("rm -rf pa_install_tmp");

if (!@mkdir("pa_install_tmp")) {
    fail("Unable to create directory <code>".getcwd()."/pa_install_tmp</code>.  You probably need to change permissions on the current directory; please either make it world-writable or owned by the web server user.");
}

download("http://update.peopleaggregator.org/dist/latest_releases.txt", "pa_install_tmp/latest_releases.txt");

$zip_fn = FALSE;
$files = file_get_contents("pa_install_tmp/latest_releases.txt");
$files = preg_split("/\n/", $files);
foreach ($files as $fn) {
    $fn = trim($fn);
    if (preg_match("/\.zip$/", $fn)) {
	$zip_fn = $fn;
    }
}
if (!$zip_fn) fail("error: can't find the filename of the latest zipped release on update.peopleaggregator.org");

preg_match("/^(.*?)\.zip$/", $zip_fn, $m);
list(, $zip_leaf) = $m;
$unzip_path = "pa_install_tmp/$zip_leaf";

//echo "<p>zip leaf $zip_leaf; after unzipping we need to mv $unzip_path/* .</p>";

download("http://update.peopleaggregator.org/dist/$zip_fn", "pa_install_tmp/latest_pa.zip");

run("cd pa_install_tmp && unzip -q latest_pa.zip");

run("mv $unzip_path _pa_priv");

run("rm -rf pa_install_tmp");

run("for f in _pa_priv/web/*; do ln -s \$f; done");

?>

<p>OK, PeopleAggregator has been downloaded and unzipped.  <a href=".">Click here to install</a>!</p>