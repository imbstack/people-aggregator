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

echo __("basecamp test disabled");
exit;

$login_required = FALSE;
include_once("web/includes/page.php");
require_once "ext/BaseCamp/BaseCampClient.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bc_url = @$_REQUEST['bc_url'];
    $bc_login = @$_REQUEST['bc_login'];
    $bc_password = @$_REQUEST['bc_password'];
}

if (!$bc_url) $bc_url = 'http://myelin.grouphub.com';

?><form method="POST">
<table>
<tr><td>basecamp url (include http://, exclude trailing slash)</td><td><input type="text" size="50" name="bc_url" value="<?=htmlspecialchars($bc_url)?>"></td></tr>
<tr><td>basecamp login</td><td><input type="text" name="bc_login" value="<?=htmlspecialchars($bc_login)?>"></td></tr>
<tr><td>basecamp password</td><td><input type="password" name="bc_password" value="<?=htmlspecialchars($bc_password)?>"></td></tr>
<tr><td></td><td><input type="submit" value="Get your basecamp info"></td></tr>
</table>
</form><?

if (!$bc_login || !$bc_password) {
    echo "<p>".__("please enter your basecamp login details above.")."</p>";
    exit;
}

$bc = new BaseCampClient($bc_url, $bc_login, $bc_password);

echo "<p>".__("scraping contacts page to get companies")."</p>"; flush();

$companies = $bc->companies();

foreach ($companies as $company) {
    
    echo "<h1>company: ".htmlspecialchars($company['name'])."</h1>";
    foreach ($company['people'] as $person) {
	echo '<li><a href="mailto:'.$person['email'].'">'.$person['name'].'</a></li>';
    }
}

echo "<p>getting project list</p>"; flush();

$projects = $bc->list_projects();

foreach ($projects as $project) {
    echo "<h1>project</h1>";
    foreach ($project as $k=>$v) {
        echo "<li>$k -> $v</li>";
    }

    // now get detail on the people from each company who are involved in this project
    foreach ($companies as $company) {
	echo "<p>".__("getting list of people from company ").$company['id'].__(" who are working on this project")."</p>";
	flush();
	$people = $bc->people($company['id'], $project->id);
	
	foreach ($people as $person) {
	    echo "<h1>person</h1>";
	    foreach ($person as $k=>$v) {
		echo "<li>$k -> $v</li>";
	    }
	}
    }
}
?>