<?php
/**
 * @script dipatcher.php
 *
 * First loaded script for any WEB request sent to the PA.
 * This script dispatch WEB requests for: WSAPI calls, file
 * requests including request for PHP scripts, JS, CSS and
 * other file types.
 * And finally this script implements shadowing model for
 * these file types and handle file downloads.
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.2.1
 *
 * @note       Do not forget that this script will be called
 *             before any other script for any WEB Request
 *             because rewritting rule in .htacces file forwarding
 *             all those requests here!
 **/
include dirname(__FILE__)."/../project_config.php";
include dirname(__FILE__)."/../autoload.inc.php";
require_once dirname(__FILE__)."/../pacore/web/includes/classes/PA.class.php";
require_once dirname(__FILE__)."/../pacore/web/includes/classes/BootStrap.class.php";
require_once dirname(__FILE__)."/../pacore/web/includes/classes/PADispatcher.class.php";
require_once dirname(__FILE__)."/../pacore/web/includes/classes/PADownloadManager.class.php";
require_once dirname(__FILE__)."/../pacore/api/Profiler/PAProfiler.class.php";
if(isset($_GET['profiler']) && $_GET['profiler'] == 1) {
    PA::$profiler = new PAProfiler();
    PA::$profiler->startTimer('PADispatcher');
}
$dispatcher = new PADispatcher($auto_load_list);
$script = $dispatcher->dispatch();
if(PA::$profiler) {
    PA::$profiler->stopTimer('PADispatcher');
}
if(PA::$profiler) {
    PA::$profiler->startTimer('Main Script', $script);
}
require_once($script);
if(PA::$profiler) {
    PA::$profiler->stopTimer('Main Script');
}
exit;
?>