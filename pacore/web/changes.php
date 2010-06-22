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
  require_once dirname(__FILE__)."/../config.inc";
require_once "api/DB/Dal/Dal.php";
require_once "api/PingServer/PingServer.php";
// TO DO: Change the File Path.
$filename = '/web/Ping/changes_user.xml';
if(file_exists(PA::$project_dir.$filename)) {
    $filename = PA::$project_dir.$filename;
}
elseif(file_exists(PA::$core_dir.$filename)) {
    $filename = PA::$core_dir.$filename;
}
else {
    $filename = null;
}
// make file if it does not exists or remake it after one hour
if(($filename == null) || (!(time()-filectime($filename)) < 3600)) {
    $PingServer = new PingServer();
    $PingServer->generate_xml();
}
$handle = @fopen($filename, 'r');
$content = @fread($handle, filesize($filename));
print $content;
?>