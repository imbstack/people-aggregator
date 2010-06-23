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
require_once dirname(__FILE__)."/lib/common.php";
require_once "api/User/ShadowUser.php";

class ShadowUserTest extends PHPUnit_Framework_TestCase {

  public function testAddDeleteShadowUser() {
    //    Dal::register_query_callback("explain_query");
    global $network_info;
    
    echo "getting a user\n";
    $user = Test::get_test_user();
    
    $testusername = $user->first_name . " " . $user->last_name;
    echo "test user = $testusername\n";
    
		$namespace = 'php_unit';
    // testuser data
    $testdata = array(
		    'user_id' => "pa_" . $user->user_id,
		    'login_name' => 'testuser',
		    'email' => $namespace . $user->email,
		    'first_name' => $user->first_name,
		    'last_name' => $user->last_name,
		    );
    echo "TEST DATA:\n";
    print_r($testdata);
    
    $shadow_user = new ShadowUser($namespace);

    echo "Test load this shadow user, this should fail\n";
    $sh = $shadow_user->load($testdata['user_id']);
    $this->assertNull($sh);
    
    echo "Create a shadow user\n";
    $shadow_user = ShadowUser::create($namespace, $testdata, $network_info);
    echo "SHADOW USER DATA:\n";
    print_r($shadow_user);

    $this->assertNotNull($shadow_user);
    
    echo "Test updating the data\n";
    $testdata2 = $testdata;
    $testdata2['email'] = $namespace . "add" . $user->email;
    $testdata2['login_name'] = "newlogin";
    $testdata2['first_name'] = "newName";
    print_r($testdata2);
    $su2 = new ShadowUser($namespace);
    // load this with new data
    $su2->load($testdata2);
    unset($su2);
    
    Cache::reset();
    
    // now load it only via the original remote uid
    $su3 = new ShadowUser($namespace);
    $su3->load($testdata['user_id']);
    echo "UPDATED SHADOW USER DATA:\n";
    print_r($su3);
    
    echo "Delete it\n";
    ShadowUser::delete($shadow_user->user_id);
    
    // there should not be a shadow user of this id anymore
    $this->assertNull($shadow_user->load($testdata['user_id']));



  }
}

?>