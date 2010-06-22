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

require_once dirname(__FILE__)."/Dal.php";

/* DbUpdate class: useful functions for use while updating the database
 * Author: Phil
 */
class DbUpdate {

    /* static get_valid_networks()
     * Returns an array of network names which have been properly created.
     * (Use this to get a list of networks to update.  If you attempt to update
     * any networks in the 'networks' table with is_active=1, you will run into
     * database errors.)
     */
    public static function get_valid_networks() {
        $sth = Dal::query("SHOW TABLES");
        $tables = array();
        while($r = Dal::row($sth)) {
            $tables[$r[0]] = 1;
        }
        $sth = Dal::query("SELECT address FROM networks WHERE is_active=1");
        $networks = array();
        while($r = Dal::row($sth)) {
            $address = $r[0];
            if($address == 'default' || isset($tables[$address."_comments"])) {
                // comments table available - assume network has been initialised
                $networks[] = $address;
            }
        }
        // if we haven't run net_extra yet, the default network won't have an entry, so we add it in manually now.
        if(!in_array("default", $networks)) {
            $networks[] = "default";
        }
        return $networks;
    }
    // Check for the <net name>_comments table - to ensure that a network exists
    public static function is_network_valid($network_name) {
        return Dal::query_first("SHOW TABLES LIKE '".Dal::quote($network_name)."_comments'") ? TRUE : FALSE;
    }
}
?>