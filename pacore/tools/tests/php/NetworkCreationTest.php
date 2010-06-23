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

// Test that the network creation code is working properly

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";

class NetworkCreationTest extends PHPUnit_Framework_TestCase {
    
    function testNetworkCreation() {
	// check that we can create networks
	$can = Network::can_network_be_created();
	$this->assertFalse($can['error'], $can['error_msg']);

	// get network owner user and figure out name etc
	$user = Test::get_test_user();
	$name = "testnet".rand(10000, 99999);
	$network_basic_controls = array(); // with crossed fingers, we hope that it will succeed without any of the detail here!

	// make a new network
	$net = new Network();
	$net->set_params(array(
	    'user_id' => $user->user_id,
	    'name' => "auto-test network ($name)",
	    'address' => $name,
	    'tagline' => "not much of a tagline",
	    'category_id' => 8, // computers & internet
	    'type' => 0, // public=0, private=2
	    'description' => "This network has been created automatically by a PHPUnit test.  If the test succeeds, it will be deleted, too!",
	    'extra'=>serialize($network_basic_controls),
	    'created'=>time(),
	    'changed'=>time(),
	    )
	    );
	$net->save();
	//default_page_setting($net->address);

	// read it in again and see if it still works
	$net_read = Network::get_network_by_address($net->address);
	$this->assertEquals($net_read->network_id, $net->network_id);
	$this->assertEquals($net_read->type, 0);
	$this->assertEquals($net_read->member_count, 1);
	$this->assertEquals($net_read->owner_id, $user->user_id);

	// a user joins
	$user2 = Test::get_test_user(2);
	Network::join($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 2);

	// a user leaves
	Network::leave($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 1);

	// make it into a moderated network
	$net->type = 2;
	$net->save();

	// check that it really is moderated
	$net_read = Network::get_network_by_address($net->address);
	$this->assertEquals($net_read->network_id, $net->network_id);
	$this->assertEquals($net_read->type, 2);

	// a user requests
	Network::join($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 1);

	// request approved
	Network::approve($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 2);

	// user leaves
	Network::leave($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 1);

	// user requests
	Network::join($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 1);
       
	// all requests accepted (of course, there will only be the one)
	Network::approve_all($net->network_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 2);
	
	// user leaves
	Network::leave($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 1);

	// user requests
	Network::join($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 1);
       
	// request denied
	Network::deny($net->network_id, $user2->user_id);
	$this->assertEquals(Network::get_network_by_address($net->address)->member_count, 1);

	// delete network
	Network::delete($net->network_id);
    }
    
}

?>