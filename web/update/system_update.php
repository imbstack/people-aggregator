<?

$login_required = FALSE;
require_once "web/includes/page.php";
require_once "Subversion/StandaloneClient.php";
require_once "Subversion/PAStateStore.php";
require_once "ext/JSON.php";
while (@ob_end_clean()); 

error_reporting(E_ALL);

?>

<html>
<head>
<title>PeopleAggregator Update</title>
<style type="text/css"><!--
.error {
	border: solid black 1px;
	padding: 1em;
	background: red;
	font-weight: bold;
}
--></style>
</head>
<body>

<?

class js_display {

    function __construct() {
        $this->json = new Services_JSON();
    }

    function start() {
?>
<div id="updateable-status" style="border: solid 1px black; padding: 1em;">
     (status goes here)
</div>
<script language="javascript"><!--
     var n = document.getElementById("updateable-status");
// --></script>
<?
    }

    function display($txt) {
	set_time_limit(30);
?>
<script language="javascript"><!--
     n.innerHTML = <?=$this->json->encode($txt)?>;
// --></script>
<?
        flush();
    }

}

class html_display {
    function display($txt) {
	set_time_limit(30);
        echo "<li>$txt</li>\n";
	flush();
    }
}

class null_display {
    function display($txt) {
//        echo "<li>QUIET :-) -- ".htmlspecialchars($txt)."</li>\n";
    }
}

function show_op($key, $name, $disabled=FALSE) {

?>

<form method="POST">
<input type="hidden" name="op" value="<?=$key?>">
<input type="submit" value="<?=$name?>" <?= $disabled ? 'disabled="disabled"' : '' ?>>
</form>

<?

}

include dirname(__FILE__)."/admin_login.php";

// --- past this point, we can assume the user is logged in as an admin ---

function run_scripts() {
    // global var $_base_url has been removed - please, use PA::$url static variable

    ?>

<h1>Almost there!</h1>

<p><?= __('All the files have been updated. Now you need to') ?> <b><a href="run_scripts.php?return=<?= PA::$url.PA_ROUTE_HOME_PAGE?>"><?= __('click here to update the database') ?></a></b> <?= __('and the update will be complete') ?>.</p>

    <?php

    return;

    // no longer done - too many interactions with old/new Dal code, internationalization etc
    // global var $path_prefix has been removed - please, use PA::$path static variable
    try {
        include "web/update/run_scripts.php";
        run_update_scripts();
    } catch (Exception $e) {
        echo "<p>".__("Error occurred running database update:")." ".$e->getMessage()."</p>";
    }
}

function main() {
    // global var $path_prefix has been removed - please, use PA::$path static variable

    ?>

<h1><?= __('PeopleAggregator system update') ?></h1>

<p>system update | <a href="version.php">version info</a></p>

    <?
        
    $tmp_dir = PA::$project_dir . "/web/files";
    $tmp_fn = "$tmp_dir/update.treediff.xml";

    $state = new Subversion_PAStateStore(PA::$path);

    if (!$state->is_initialized()) $state->initialize();
    $client = new Subversion_StandaloneClient($state, $tmp_fn);
    
    $op = @$_POST['op'];
    switch ($op) {
    case 'login':
	break;
	
    case 'download':
	echo "<h2>downloading update</h2>";

        $root = $state->get_repository_root();

        if (preg_match("|^https://|", $root)) {
            echo "<p>ERROR: attempting to update from an HTTPS update server (<code>$root</code>), but we can't do HTTPS :-(</p>";
            break;
        }

        if (!preg_match("|^http://update.peopleaggregator.org/svn|", $root)) {
            echo '<p>WARNING: the update server is <code>'.$root.'</code>, which is not the official PeopleAggregator update server.</p>';
        }
        elseif ($root != 'http://update.peopleaggregator.org/svn/release') {
            echo '<p>WARNING: updating from '.$root.', which is on the official PeopleAggregator server, but may be a test release.</p>';
        }

	$rev = $state->get_revision();
	echo '<p>Requesting an update from revision r'.$rev.' from the server (<a href="'.$root.'">root</a>, <a href="'.$root.$state->get_repository_path().'">path</a>).</p>';
	
	$disp = new js_display();
	$disp->start();
	$client->displayer = $disp;
	$client->checkout();

        $err = NULL;
	$f = @fopen($tmp_fn, "rt");
	if ($f) {
            $xml = fread($f, 8192);
            fclose($f);
            if (!preg_match("|^<"."\?xml|", $xml)) {
                $err = "Invalid response received.";
            }
            else if (preg_match("|<m:human-readable.*?>(.*?)</m:human-readable>|s", $xml, $m)) {
                $err = $m[1];
            }
            else if (preg_match('|target-revision rev="(\d+)"|s', $xml, $m)) {
                $target_rev = (int)$m[1];
                if ($target_rev == $rev) {
                    echo "Your system is already up to date.";
                    unlink($tmp_fn);
                }
            }
	} else {
            $err = "Error reading temporary tree diff file $tmp_fn";
        }

        if ($err) {
            echo '<div class="error">An error occurred: <b>'.htmlspecialchars($err).'</b></div>';
            return;
        }

/*<?xml version="1.0" encoding="utf-8"?>
<D:error xmlns:D="DAV:" xmlns:m="http://apache.org/dav/xmlns" xmlns:C="svn:">
<C:error/>
<m:human-readable errcode="160005">
Cannot replace a directory from within
</m:human-readable>
</D:error>*/

	break;
	
    case 'test-apply':
        if (!file_exists($tmp_fn)) break;
    
	echo '<div id="progress-detail"><h2>Verifying that the update can be installed.</h2>'; flush();

	$msg_headers = array(
            "localmod" => "Your local modifications conflict with the update",
            "perms" => "The updater is unable to access some files or directories",
            );

	$client->displayer = new html_display();
	$msg_html = "";
	try {
	    $r = $client->apply_patch(FALSE);
	    $errors = $r['errors'];
            foreach ($errors as $level => $msgs) {
                $msg_list = "";
                foreach ($msgs as $msg) {
                    $msg_list .= "<li>".$msg."</li>";
                }
                $msg_html .= "<h3>".$msg_headers[$level]."</h3>
<ul>$msg_list</ul>";
            }
	} catch (Subversion_Failure $e) {
	    $msg_html = $e->getMessage();
	}

	echo <<<EOF
</div><!-- progress-detail -->
<script language="javascript" type="text/javascript"><!--
  // hide detail, so the user can see the error message
  document.getElementById("progress-detail").style.display = "none";
// --></script>
EOF;

	if ($msg_html) {
	    ?>

<p>The update (from r<?= $state->get_revision() ?> to r<?= $client->target_revision ?>) cannot be installed cleanly for the following reason(s):</p>

<div class="error"><?= $msg_html ?></div>

<p>You have several options at this point if you wish to install the update:</p>

<ol>

 <li>
  <p>Fix the issues listed above, then try installing the update again.</p>
  <? if (!@$errors['localmod']) { ?><p>HIGHLY RECOMMENDED - as the only issues above are to do with file access permissions.  Please fix the permissions, and try again.</p><? } ?>
  <? show_op("test-apply", "Click here to try installing the update again"); ?>
 </li>

 <? if (@$errors['localmod']) { ?>
 <li>
  <p>[NOT IMPLEMENTED YET] Migrate to using Subversion for updates.</p>
  <p>HIGHLY RECOMMENDED if you plan to maintain local changes.</p>
  <? show_op("svn-migrate", "Click here to create .svn folders in your PeopleAggregator install so you can use 'svn update'", TRUE); ?>
 </li>
 <? } ?>

 <li>
  <p>Overwrite your local changes.  This will probably result in a working system, but will destroy any changes you have made to your system, so make sure you know what you are doing!</p>
  <p>(Files with local modifications will be renamed, e.g. changedfile.php will become changedfile.php.local)</p>
  <? if (!@$errors['localmod']) { ?><p>NOT RECOMMENDED - as the only issues above are to do with file access permissions.  Instead, please fix the permissions, and try again.</p><? } ?>
  <? if (@$errors['perms']) { ?><p style="font-weight: bold">CURRENTLY NOT POSSIBLE due to the access permissions errors mentioned above.  Please fix them, then try again.</p><? } ?>
  <? show_op("force-apply", "Click here to install the update, overwriting your local changes.", @$errors['perms'] ? TRUE : FALSE); ?>
 </li>

 <li>
  <p>Install only the parts of the update that do not conflict with anything on your system.</p>
  <p>(Files with local modifications will not be touched, and new local files will block installation of new files or directories of the same name).</p>
  <p>This won't destroy anything you have changed, but could quite possibly result in a broken installation, so it is not recommended unless your changes are relatively minor.</p>
  <? if (!@$errors['localmod']) { ?><p>NOT RECOMMENDED - as the only issues above are to do with file access permissions.  Instead, please fix the permissions, and try again.</p><? } ?>
  <? show_op("soft-apply", "Click here to install the update without overwriting/deleting anything with local changes."); ?>
 </li>

</ol>

            <?
        } else {
	    show_op("apply", "Confirm - apply update to your installation");
	}
	return; // don't show 'an update has been downloaded' text

    case 'apply':
        if (!file_exists($tmp_fn)) break;
	echo "<h2>Applying update.</h2>";
	$client->displayer = new html_display();
	$client->apply_patch(TRUE);
        run_scripts();
	return;

    case 'soft-apply':
        if (!file_exists($tmp_fn)) break;
	echo "<h2>Applying update without overwriting local changes.</h2>";
	$client->displayer = new html_display();
	$client->apply_patch("soft");
        run_scripts();
	return;

    case 'force-apply':
        if (!file_exists($tmp_fn)) break;
	echo "<h2>Apply update, overwriting local changes.</h2>";
        echo "<p>Downloading extra files from the server ...</p><ul>";
        // find files to download
        $client->displayer = new null_display();
        $client->apply_patch();
	$client->displayer = new html_display();
        $client->download_incomplete_files($tmp_dir);
        echo "</ul>";

        // all done - now apply the patch
	$client->apply_patch("force");
        run_scripts();
	return;
	
    default:
	if ($op) throw new Subversion_Failure("Invalid operation: $op");
	break;
    }
    
    if (file_exists($tmp_fn)) {
	show_op("test-apply", "An update has been downloaded.  Click here to apply it to your system.");
    }
    else {
	show_op("download", "Check for updates");
    }
}

try {
    main();
} catch (Subversion_Failure $e) {
    echo "<p>ERROR: ".$e->getMessage()."</p>";
}

?>