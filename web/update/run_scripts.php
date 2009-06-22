<?php

/*

Call run_update_scripts() after doing an update or a fresh install.
It currently runs:

- database update (web/extra/db_update.php)

- default module settings (db/script_module_settings_data.php)

*/

error_reporting(E_ALL);

require_once "web/extra/db_update.php";
require_once "api/PAException/PAException.php";

function upd_write($s) {
    if (isset($_SERVER['REQUEST_METHOD'])) {
	echo "<h3>".htmlspecialchars($s)."</h3>\n";
    } else {
	echo "$s\n";
    }
    flush();
}

function run_update_scripts() {

    if (!defined("PEEPAGG_UPDATING")) define("PEEPAGG_UPDATING", 1);

    upd_write("Running database upgrade script ...");
    $upd = new db_update_page();
    $upd->main();

/* No longer used

    upd_write("Installing default module settings ...");

    if (!include("db/script_module_settings_data.php"))
        throw new PAException(GENERAL_SOME_ERROR, "Unable to install default module settings");
*/
}

if (realpath(@$_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) {
    upd_write("Running all scripts to update the system to the latest version.");

    try {
        run_update_scripts();
        upd_write("It looks like all the scripts ran correctly - your system is now up to date!");
	if (@$_SERVER['REQUEST_METHOD']) {
	    echo '
<p><b><a href="'.PA::$url . PA_ROUTE_HOME_PAGE .'">Click here to continue back to the (newly updated) homepage!</a></b></p>
';
	}
    } catch (Exception $e) {
        upd_write("AN ERROR OCCURRED: ".$e->getMessage());
    }
}

?>