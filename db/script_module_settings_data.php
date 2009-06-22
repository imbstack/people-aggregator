<?php
require_once dirname(__FILE__).'/../config.inc';
require_once "db/Dal/Dal.php";
require_once "db/Dal/DbUpdate.php";
require_once "api/Network/Network.php";

// Re-include constants.php to make sure we have the most up to date
// constants.  If we are in the middle of an update and this script is
// being included by web/update/run_scripts.php, we might not have all
// the constants.
include "web/includes/constants.php";

// NOTE: this script is obsolete now and should be removed!!!
//
// $settings_new contains the mapping of page names to modules they contain.
/*
global $settings_new;

$db = Dal::get_connection();

foreach (DbUpdate::get_valid_networks() as $net_address) {
    set_time_limit(30);
    $net = Network::get_network_by_address($net_address);
    $table_name = 'page_default_settings';
    if ($net->type <> MOTHER_NETWORK_TYPE) { // 1 for home network
	$table_name = $net->address.'_'.$table_name;
    }
    $sql = ' TRUNCATE TABLE '. $table_name;
    $res = Dal::query($sql);
    foreach ($settings_new as $page_id => $v1) {
        $page_name = $v1['page_name'];
        $data = $v1['data'];
        $settings_data = serialize($data);
        $is_configurable = (isset($v1['is_configurable'])) ? $v1['is_configurable'] : FALSE;//default value will be false is not specified

        $sql = "INSERT INTO $table_name (page_id, page_name, default_settings, is_configurable) VALUES (?, ?, ?, ?)";
        $data = array($page_id, $page_name, $settings_data, $is_configurable);

        $res = Dal::query($sql, $data);
    }
}

echo "page_default_settings table created and populated.\n";
*/
?>