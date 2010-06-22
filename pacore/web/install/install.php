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
require_once "PAInstaller.class.php";
session_start();
global $installer;
if(!isset($_SESSION['installer'])) {
    $_SESSION['installer'] = serialize(new PAInstaller());
}
$installer = unserialize($_SESSION['installer']);
if(false !== strpos($_SERVER['SCRIPT_NAME'], 'basic_tests.php')) {
    include("basic_tests.php");
    $_SESSION['installer'] = serialize($installer);
    session_write_close();
    exit;
}
if(false !== strpos($_SERVER['SCRIPT_NAME'], 'db_tests.php')) {
    include("db_tests.php");
    $_SESSION['installer'] = serialize($installer);
    session_write_close();
    exit;
}
$installer->run();
$_SESSION['installer'] = serialize($installer);
session_write_close();
?>