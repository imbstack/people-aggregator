<?php

$login_required = FALSE;
require_once "web/includes/page.php";
require_once "Subversion/StandaloneClient.php";
require_once "Subversion/PAStateStore.php";
require_once "ext/JSON.php";
while (@ob_end_clean()); 

$state = new Subversion_PAStateStore(PA::$path);

?><h1>PeopleAggregator version information</h1>

<p><a href="system_update.php">system update</a> | version info</p>

<?php

include dirname(__FILE__)."/admin_login.php";

// --- past this point, we can assume the user is logged in as an admin ---

?>
<h2>Master version</h2>
<?php

echo "<p>This is PeopleAggregator v<b>".PA_VERSION."</b>.</p>";

?>
<h2>Detailed version information</h2>
<?php

if (file_exists("../../db/release_info.txt")) {
?>

<h3>Release info</h3>

<?php
    $text = file_get_contents("../../db/release_info.txt");
    if (preg_match("/\nBuild time: (.*?)\n/", $text, $m)) {
	echo "<p>Build time: <code><b>".htmlspecialchars($m[1])."</b></code></p>";
    }
    if (preg_match("/\nRepository: (.*?)\n/", $text, $m)) {
	echo "<p>Repository: <code><b>".htmlspecialchars($m[1])."</b></code></p>";
    }
    if (preg_match("/\nRevision: (.*?)\n/", $text, $m)) {
	echo "<p>Revision: <b>".htmlspecialchars($m[1])."</b></p>";
    }    
}

if (file_exists("../../db/source_svn_info.txt")) {
?>

<h3>Source Subversion information</h3>

<p>This is the development Subversion repository and revision used to build this release.</p>

<?php
    $text = file_get_contents("../../db/source_svn_info.txt");
    if (preg_match("/\nURL: (.*?)\n/", $text, $m)) {
	echo "<p>Repository: <code><b>".htmlspecialchars($m[1])."</b></code></p>";
    }
    if (preg_match("/\nRevision: (.*?)\n/", $text, $m)) {
	echo "<p>Revision: <b>".htmlspecialchars($m[1])."</b></p>";
    }    
}

?>
<h3>Built-in update system</h3>
<?php

if ($state->is_initialized()) {
?>
 <p>This is the version information used by the built-in update system. If you have <a href="http://wiki.peopleaggregator.org/Transitioning_to_Subversion_for_updates">transitioned to Subversion</a>, this information will be out of date and your version information will be down below under the "Subversion" heading.</p>
<?php
    echo "<p>Repository: <code><b>".htmlspecialchars($state->get_repository_root().$state->get_repository_path())."</b></code></p>";
    echo "<p>Revision: <b>".htmlspecialchars($state->get_revision())."</b></p>";
} else {
    echo "<p>The built-in update system has not been initialized.</p>";
}

?>
<h3>Subversion (external - .svn directory)</h3>
<?php

// check for .svn/entries
if (file_exists("../../.svn/entries")) {
    echo "<p><code>.svn/entries</code> file found</p>";
    $dom = new DOMDocument;
    $dom->load("../../.svn/entries");
    $xp = new DOMXPath($dom);
    $xp->registerNamespace("svn", "svn:");
    
    foreach ($xp->query("//svn:entry[@url]") as $node) {
?>
 <p>This is the version information used by Subversion. If you have <a href="http://wiki.peopleaggregator.org/Transitioning_to_the_auto-update_system_from_Subversion">transitioned from Subversion to the auto-update system</a>, this information will be out of date and your version information will be shown above.</p>
<?php
	$repo_url = $node->getAttribute("url");
	$repo_ver = $node->getAttribute("revision");
	echo "<p>Repository: <code><b>".htmlspecialchars($repo_url)."</b></code></p>";
	echo "<p>Revision: <b>".htmlspecialchars($repo_ver)."</b></p>";
	break;
    }
} else {
    echo "<p>No Subversion information found.</p>";
}

?>
<h3>Version at install time</h3>

<?php

if (file_exists("../../db/dist_files.txt")) {
?>
 <p>This is the repository and revision from which the tarball or zip file used to install this copy of PeopleAggregator was built.  If you have updated since installation, this will be different from the version(s) reported above.</p>
<?php
    $text = file_get_contents("../../db/dist_files.txt");
    if (preg_match("/\nURL: (.*?)\n/", $text, $m)) {
	echo "<p>Repository: <code><b>".htmlspecialchars($m[1])."</b></code></p>";
    }
    if (preg_match("/\nRevision: (.*?)\n/", $text, $m)) {
	echo "<p>Revision: <b>".htmlspecialchars($m[1])."</b></p>";
    }    
} else {
    echo "<p>No distribution metadata found; this copy was not installed from a distribution tarball or zip file.</p>";
}
?>

<h2>Historical information</h2>

<ul>

<li>Version 0.01 / Release 2 was the first public PeopleAggregator release, given out to attendees at Gnomedex 2006 on branded memory sticks.</li>

<li>Version 0.01 / Release 4 was the first update, made during Gnomedex.</li>

<li>Version 0.01 / Release 7 was the second update, made on 25 July 2006.  <a href="http://www.myelin.co.nz/post/2006/7/25/#200607251">Release notes</a>.</li>

<li>Version 0.01 / Release 9 was the third update, made on 1 August 2006.  <a href="http://www.myelin.co.nz/post/2006/8/1/#200608011">Release notes</a>.</li>

<li>Version 0.02 / Release 13 was the fourth update, made on 8 August 2006.  <a href="http://www.myelin.co.nz/post/2006/8/8/#200608081">Release notes</a>.</li>

<li>Version 0.03 / Release 15 was the fifth update, made on 15 August 2006.  <a href="http://www.myelin.co.nz/post/2006/8/15/#200608151">Release notes</a>.</li>

<li>Version 1.0 / Release 16 is identical to v0.03/r15.</li>

<li>Version 1.1 / Release 22 was the sixth update, made on 25 September 2006.  <a href="http://www.myelin.co.nz/post/2006/9/25/#200609251">Release notes</a>.</li>

<li>Version 1.2pre1, made on 19 February 2007.</li>

<li>Version 1.2pre2, made on 6 March 2007.</li>

<li>Version 1.2pre3, made on 28 March 2007, during BBM offsite in Walnut Creek.  <a href="http://www.myelin.co.nz/post/2007/3/29/#200703291">Release notes</a>.</li>

<li>Version 1.2pre7, made on 21 February 2008 at the Walnut Creek/Concord Campus.  <a href="http://www.peopleaggregator.net/content.php?cid=29192">Release notes</a>.</li>

<li>Version 1.2pre7final, made on 29 February 2008 at Green Valley Campus.  <a href="http://www.peopleaggregator.net/content.php?cid=29192">Release notes</a>.</li>

<li>Version 1.9, made on 28 March 2009 at Green Valley Campus.  <a href="http://www.peepagg.net/content/permalink/cid=67">Release notes</a>.</li>

<li>Version 2.0 Beta, made on 4 April 2009 at Green Valley Campus.  <a href="http://www.peepagg.net/content/permalink/cid=67">Release notes</a>.</li>

<li>Version 1.96, made on 13 April 2009 at Green Valley Campus.  <a href="http://www.peepagg.net/content/permalink/cid=67">Release notes</a>.</li>

<li>Version 2.0.0, made on 24 May 2009 at Green Valley Campus.  <a href="http://www.peepagg.net/content/permalink/cid=67">Release notes</a>.</li>
</ul>
