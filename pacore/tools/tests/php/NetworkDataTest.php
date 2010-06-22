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

// Test that the cached data in the networks table agrees with the
// authoritative data in networks_users.
// Author: phil
require_once dirname(__FILE__)."/lib/common.php";

class NetworkDataTest extends PHPUnit_Framework_TestCase {

    function testOwnerIdMemberCount() {
        $networks = array();
        $sth = Dal::query("SELECT network_id, address, member_count, owner_id FROM networks WHERE is_active=1");
        while(list($net_id, $address, $member_count, $owner_id) = Dal::row($sth)) {
            $networks[$net_id] = array(
                "address"      => $address,
                "member_count" => $member_count,
                "owner_id"     => $owner_id,
            );
        }
        // count all members for all networks
        $sth = Dal::query("SELECT network_id, COUNT(user_id) FROM networks_users GROUP BY network_id");
        while(list($net_id, $member_count) = Dal::row($sth)) {
            $networks[$net_id]['calc_member_count'] = $member_count;
        }
        // find all owners
        $sth = Dal::query("SELECT network_id, user_id FROM networks_users where user_type='owner'");
        while(list($net_id, $owner_id) = Dal::row($sth)) {
            $networks[$net_id]['calc_owner_id'] = $owner_id;
        }
        // verify them all
        $ok = TRUE;
        foreach($networks as $nid => $net) {
            $address = $net['address'];
            $mc      = $net['member_count'];
            $cmc     = $net['calc_member_count'];
            $oi      = $net['owner_id'];
            $coi     = (int) $net['calc_owner_id'];
            if($cmc && ($mc != $cmc || $oi != $coi)) {
                echo "NetworkDataTest ERROR: Network $nid [$address]: member_count $mc, calc $cmc | owner_id $oi, found $coi\n";
                $ok = FALSE;
            }
        }
        $this->assertTrue($ok);
    }
}
?>