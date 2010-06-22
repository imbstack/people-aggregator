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

$login_required = FALSE;
require_once "web/includes/page.php";
$path = $_SERVER['PATH_INFO'];

function notfound($why) {
    header("HTTP/1.0 404 not found");
    echo "FOAF file not found (".htmlspecialchars($why).")";
    exit;
}
if(@$_GET['uid']) {
    // foaf.php?uid=159
    $user = new User();
    $user->load((int) $_GET['uid']);
}
else {
    // foaf.php/myelin
    if(!preg_match("|^/([^/]+)(/)?$|", $path, $m)) {
        notfound("Unable to parse username out of URL");
    }
    $username = $m[1];
    // find user
    $user = new User;
    try {
        $user->load($username, "login_name");
    }
    catch(PAException$e) {
        notfound("User $username not found");
    }
}
try {
    $user_generaldata = User::load_user_profile($user->user_id, 0, GENERAL);
}
catch(PAException$e) {
    notfound("User $username exists, but unable to load profile data.");
}

function profile_block_filter($data) {
    $ret = array();
    foreach($data as $blk) {
        $ret[$blk['name']] = $blk;
    }
    return $ret;
}
$user_generaldata = profile_block_filter($user_generaldata);

function esc($s) {
    return htmlspecialchars($s);
}
header("Content-Type: application/rdf+xml");
echo "<";
?>?xml version="1.0"<?="?"?>>
<rdf:RDF
 xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
 xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
 xmlns:foaf="http://xmlns.com/foaf/0.1/"
 xmlns:admin="http://webns.net/mvcb/">
 <foaf:PersonalProfileDocument rdf:about="">
  <foaf:maker rdf:nodeID="me"/>
  <foaf:primaryTopic rdf:nodeID="me"/>
  <admin:generatorAgent rdf:resource="http://peopleaggregator.com/"/>
  <admin:errorReportsTo rdf:resource="http://www.myelin.co.nz/phil/email.php?subject=peopleaggregator+foaf+issue"/>
 </foaf:PersonalProfileDocument>
 <foaf:Person rdf:nodeID="me">
  <foaf:name><?=esc("$user->first_name $user->last_name")?></foaf:name>
  <foaf:givenname><?=esc($user->first_name)?></foaf:givenname>
  <foaf:family_name><?=esc($user->last_name)?></foaf:family_name>
  <foaf:nick><?=esc($user->login_name)?></foaf:nick>
  <foaf:mbox_sha1sum><?=sha1($user->email)?></foaf:mbox_sha1sum>
<? if(array_key_exists("blog_url", $user_generaldata)) {?>
  <foaf:weblog rdf:resource="<?=esc($user_generaldata['blog_url']['value'])?>"/>
<?
}?>
<? if($user->picture) {?>
  <foaf:depiction rdf:resource="<?=esc(Storage::getURL($user->picture))?>"/>
<?
}?>
 </foaf:Person>
</rdf:RDF>
