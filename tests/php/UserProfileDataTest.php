<?php

// Test that we can read user profile data.

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";

class UserProfileDataTest extends PHPUnit_Framework_TestCase {

  function testProfileReadingFunctions() {
    // find a user with 'newcss' set
    list($uid, $css) = Dal::query_one("SELECT user_id, field_value FROM user_profile_data WHERE field_type='ui' AND field_name='newcss' ORDER BY user_id LIMIT 1");
    if (empty($uid)) {
      echo "Test not possible as nobody has the newcss field set.  Try again on a more populated database.\n";
      return;
    }

    // find another field, so we can test with more than one
    list($f2_name, $f2_value) = Dal::query_one("SELECT field_name, field_value FROM user_profile_data WHERE field_type='ui' AND user_id=? AND field_name <>'newcss' AND field_value IS NOT NULL LIMIT 1", $uid);
    echo "getting ui/newcss and $f2_name properties from user_profile_data for user_id $uid.\n";

    $user = new User();
    $user->load((int)$uid);

    // load just the newcss field
    echo "getting just the newcss property for user $uid\n";
    $css2 = $user->get_profile_field('ui', 'newcss');
    $this->assertEquals($css, $css2);

    // load just the second field
    echo "getting just the $f2_name property for user $uid\n";
    $v = $user->get_profile_field('ui', $f2_name);
    $this->assertEquals($v, $f2_value);

    // load newcss and the second field, with get_profile_fields()
    echo "getting the newcss and $f2_name properties, with get_profile_fields()\n";
    $data = $user->get_profile_fields('ui', array('newcss', 'graagh', $f2_name));
    $this->assertEquals($css, $data['newcss']);
    $this->assertEquals(NULL, $data['graagh']);
    $this->assertEquals($f2_value, $data[$f2_name]);

    // try again, flushing the cache first
    Cache::reset();
    echo "(without cache) getting the newcss and $f2_name properties, with get_profile_fields()\n";
    $data = $user->get_profile_fields('ui', array('newcss', 'graagh', $f2_name));
    $this->assertEquals($css, $data['newcss']);
    $this->assertEquals(NULL, $data['graagh']);
    $this->assertEquals($f2_value, $data[$f2_name]);

    // regression test (phil) 2007-04-01, for bug spotted by martin
    // 2007-03-23: make sure we don't crash if we request fields that
    // are all cached.
    echo "regression: make sure it doesn't crash if everything is in the cache\n";
    $data = $user->get_profile_fields('ui', array('newcss'));
    $this->assertEquals($css, $data['newcss']);

    // try by loading the entire 'ui' section
    echo "getting entire ui section for user $uid\n";
    $ui = User::load_profile_section($uid, "ui");
    $this->assertEquals($css, $ui['newcss']['value']);
    $this->assertEquals($f2_value, $ui[$f2_name]['value']);
  }

}

?>