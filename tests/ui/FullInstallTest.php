<?php
require_once dirname(__FILE__)."/UiTest.php";

class FullInstallTest extends UiTest
{
  protected function setUp() {
	parent::setUp();
	
	$this->assertTrue(file_exists("local_config.php"));

	// now we've set up the 'internal' ui test, we move local_config.php out of the way and get ready to start
	$new_local_config_name = "local_config.php.".date("Ymd-His");
	if (!copy("local_config.php", "local_config.php.pre_test")) {
	    $this->markTestIncomplete("Couldn't copy local_config.php to local_config.php.pre_test");
	}
	if (!rename("local_config.php", "".$new_local_config_name)) {
	    $this->markTestIncomplete("Couldn't move existing local_config.php out of the way");
	}
	echo "Moved local_config.php to $new_local_config_name\n";

	// get rid of web/config/local_config.php if it exists
	if (file_exists("web/config/local_config.php")) {
	    if (!unlink("web/config/local_config.php")) {
		$this->markTestIncomplete("Couldn't delete web/config/local_config.php");
	    }
	}

	// get rid of the 'pa_test' database
	system("echo 'drop database pa_test' | mysql");
    }

    protected function tearDown() {
	if (rename("local_config.php.pre_test", "local_config.php")) {
	    echo "Moved old local_config.php back into place.\n";
	} else {
	    echo "Failed to move old local_config.php back into place - you'll have to do it manually.\n";
	}
    }

    public function testEverything() {
	$this->open(PA::$local_url . PA_ROUTE_HOME_PAGE);
	$this->click("link=Click here to set up PeopleAggregator");
	$this->waitForPageToLoad("30000");
	$this->type("admin_password", "testadminpassword");
	$this->type("admin_password2", "testadminpassword");
	$this->type("mysql_dbname", "pa_test");
	$this->type("mysql_username", "pa_test");
	$this->type("mysql_password", "yiusdr67823flnauercfa");
	$this->click("//input[@value='Set up PeopleAggregator']");
	$this->waitForPageToLoad("30000");
	
	// now move the local_config.php into the right place
	$this->assertFalse(!rename("web/config/local_config.php", "local_config.php"));

	// and see if we can get to the system
	parent::testEverything();
    }
}

?>