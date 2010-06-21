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

// Test that the user registration API code is working and includable.
// Author: phil
require_once dirname(__FILE__)."/lib/common.php";
require_once "api/User/Registration.php";

class UserRegistrationTest extends PHPUnit_Framework_TestCase {

    function testUserRegistration() {
        $login             = "testuser_".rand(10000, 99999);
        $firstName         = 'Test';
        $lastName          = 'User';
        $email             = "$login@myelin.co.nz";
        $password          = 'testuser';
        $home_network      = Network::get_mothership_info();
        $orig_member_count = $home_network->member_count;
        // register a new user
        $reg = new User_Registration();
        $this->assertTrue($reg->register(array('login_name' => $login, 'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'password' => $password, 'confirm_password' => $password,), $home_network));
        $this->assertEquals(Network::get_member_count($home_network->network_id), $orig_member_count+1);
        // test the user
        $new_user = $reg->newuser;
        $new_uid = (int) $new_user->user_id;
        $this->assertEquals($new_user->first_name, $firstName);
        $this->assertEquals($new_user->last_name, $lastName);
        $this->assertEquals($new_user->email, $email);
        // reload user and make sure it works
        $user = new User();
        $user->load($new_uid);
        $this->assertEquals($user->first_name, $firstName);
        $this->assertEquals($user->last_name, $lastName);
        $this->assertEquals($user->email, $email);
        // now delete the user
        User::delete($new_uid);
        // and try to load again
        $user_fail = new User();
        try {
            $user_fail->load($new_uid);
        }
        catch(PAException$e) {
            $this->assertEquals($e->getCode(), USER_NOT_FOUND);
        }
        // make sure member_count is correct
        $this->assertEquals(Network::get_member_count($home_network->network_id), $orig_member_count);
    }
}
?>