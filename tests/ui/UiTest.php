<?php
require_once 'lib/SeleniumTestCase.php';

require_once dirname(__FILE__).'/../../config.inc';

class UiTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected function setUp() {
	global $_PA;
	if (empty($_PA->selenium_host)) {
	    echo ($msg = "Please set \$_PA->selenium_host in your local_config.php file to use Selenium UI automation tests")."\n";
	    $this->markTestIncomplete($msg);
	}
	$this->setHost($_PA->selenium_host);
	$this->setBrowser('*firefox');
	$this->setBrowserUrl(PA::$url.'/');
	$this->verificationErrors = array();
	
    }
    
    function type_into_tinymce($content) {
	$this->waitForCondition('selenium.browserbot.getCurrentWindow().tinyMCE.getInstanceById("mce_editor_1"); selenium.browserbot.getCurrentWindow().tinyMCE.setContent("'.$content.'"); true', 10000);
    } 
    
    private function login() {
	$this->click("//a[1]/b");
	$this->waitForPageToLoad("30000");
	$this->type("username", $this->user_login);
	$this->type("password", "asdfasdf");
	$this->click("//input[@value='log in']");
	$this->waitForPageToLoad("30000");
    }
    
    public function testEverything() {
	$this->open(PA::$url.'/');
	/*	$title = $this->getTitle();
	$this->assertFalse(empty($title), "Something is failing majorly - either the site to test is dead or something is wrong with the Selenium / test configuration");
	$this->assertRegExp("/^Welcome/", $this->getTitle(), "Title of splash page incorrect");*/
	
	$this->user_key = rand() % 99999;

	// start
	$this->open(PA::$local_url . PA_ROUTE_HOME_PAGE);

	// people page (not logged in)
	$this->click("link=People");
	$this->waitForPageToLoad("30000");
	try {
	  $this->assertTrue($this->isTextPresent("This network has"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	  array_push($this->verificationErrors, $e->toString());
	}

	// groups page (not logged in)
	$this->click("link=Groups");
	$this->waitForPageToLoad("30000");
	try {
	  $this->assertTrue($this->isTextPresent("Search Groups"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	  array_push($this->verificationErrors, $e->toString());
	}

	// search (not logged in)
	$this->click("link=Search");
	$this->waitForPageToLoad("30000");
	try {
	  $this->assertTrue($this->isTextPresent("Search Content"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	  array_push($this->verificationErrors, $e->toString());
	}
	$this->type("allwords", "test search query izputyncwzxrlizsdf");
	$this->click("btn_searchContent");
	$this->waitForPageToLoad("30000");
	try {
	  $this->assertEquals("test search query izputyncwzxrlizsdf", $this->getValue("allwords"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	  array_push($this->verificationErrors, $e->toString());
	}
	try {
	  $this->assertTrue($this->isTextPresent("No content found"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	  array_push($this->verificationErrors, $e->toString());
	}

	// register a user
	$this->click("link=Login now");
	$this->waitForPageToLoad("30000");
	$this->user_login = "testuser".$this->user_key;
	$this->type("login_name", $this->user_login);
	$this->type("first_name", "testuser");
	$this->type("last_name", "user".$this->user_key);
	$this->type("document.formRegisterUser.password", "asdfasdf");
	$this->type("confirm_password", "asdfasdf");
	$this->type("email", "asdfasdf".$this->user_key."@myelin.co.nz");
	$this->click("joinbutton");
	$this->waitForPageToLoad("30000");

	// enable blog
	$this->click("personal_blog");
	$this->click("link=Save");
	$this->waitForPageToLoad("30000");
	$this->click("confirm_btn");

	// create a post
	$this->click("link=Create post");
	$this->waitForPageToLoad("30000");
	$this->type("title", "test post, posted from selenium");
	$this->type("tags", "tag1");
	$this->click("document.formCreateContent.publish_post");
	$this->waitForPageToLoad("30000");
	$this->assertTrue((bool)preg_match("/^Are you sure you want to post the content/",$this->getConfirmation()));
	$this->click("confirm_btn"); // as we didn't put in any content
	$this->type_into_tinymce("Content of the test post");
	$this->type("title", "test post, posted from selenium (to community blog)");
	$this->click("route_to_pa_home");
	$this->click("document.formCreateContent.publish_post");
	$this->waitForPageToLoad("30000");
	$this->assertTrue((bool)preg_match("/^Are you sure you want to post the content/",$this->getConfirmation()));
	$this->click("link=test post, posted from selenium (to community blog)");
	$this->waitForPageToLoad("30000");
	$this->click("link=Home");
	$this->waitForPageToLoad("30000");
	$this->click("link=comments (0)");
	$this->waitForPageToLoad("30000");
	$this->type("Content", "Here's a comment!");
	$this->click("addcomment");
	$this->assertTrue((bool)preg_match("/^Are you sure you want  to post the comment/",$this->getConfirmation()));
	//    $this->click("confirm_btn"); // this only happens if akismet rejects it.
	$this->click("link=Create a network");
	$this->waitForPageToLoad("30000");
	
	$this->net_key = rand() % 99999;
	$this->net_id = "test".$this->net_key;
	
	$this->type("address", $this->net_id);
	$this->click("link=Check Availability");
	try {
	    $this->assertTrue($this->isTextPresent("Network address testnet$this->net_key is available"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	    array_push($this->verificationErrors, $e->toString());
	}
	$this->type("name", "test network (selenium) $this->net_key");
	$this->type("tagline", "network subtitle $this->net_key");
	$this->select("category", "label=Family & Home");
	$this->type("textarea", "network automatically created with selenium");
	$this->type("network_group_title", "selenium community blog");
	$this->click("submit");
	try {
	    $this->waitForPageToLoad("30000");
	    $this->assertTrue(0, "We should have been redirected off-site as the new network is created.  It probably failed");
	} catch (Testing_Selenium_Exception $e) {
	    // good!
	}

	/*// this is what we could do if we could bypass the cross-domain security:
    try {
        $this->assertTrue($this->isTextPresent("Network created successfully"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("confirm_btn");
    $this->waitForPageToLoad("30000");*/
	
	// now restart with a new URL
	$this->stop();
	$new_url = str_replace("www", $this->net_id, PA::$url) . PA_ROUTE_HOME_PAGE;
	echo "new network: $new_url\n";
	$this->setBrowserUrl($new_url);
	$this->setupSelenium();
	$this->start();
	$this->open(PA::$local_url . PA_ROUTE_HOME_PAGE);
	$this->login();

	$this->click("link=Me");
	$this->waitForPageToLoad("30000");
	$this->click("link=Create post");
	$this->waitForPageToLoad("30000");
	$this->type("tags", "new network");
	$this->type("title", "test post, to new network (not to community blog) SHOULD NOT USE THIS TITLE");
	$this->type_into_tinymce("Content of the test post (new network)");
	$this->chooseCancelOnNextConfirmation();
	$this->click("document.formCreateContent.publish_post");
	$this->assertTrue((bool)preg_match("/^Are you sure you want to post the content/",$this->getConfirmation()));
	$this->type("title", "test post, to new network (not to community blog) (correct title)");
	$this->click("document.formCreateContent.publish_post");
	$this->waitForPageToLoad("30000");
	$this->assertTrue((bool)preg_match("/^Are you sure you want to post the content/",$this->getConfirmation()));
	try {
	    $this->assertTrue($this->isTextPresent("test post, to new network (not to community blog) (correct title)"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	    array_push($this->verificationErrors, $e->toString());
	}
	$this->click("link=delete");
	$this->waitForPageToLoad("30000");
	$this->assertTrue((bool)preg_match("/^Are you sure you want to delete this content/",$this->getConfirmation()));
	$this->click("link=Groups");
	$this->waitForPageToLoad("30000");
	$this->click("link=Create a Group");
	$this->waitForPageToLoad("30000");
	$this->type("group_name", "test group $this->net_key for new network");
	$this->click("addgroup");
	$this->waitForPageToLoad("30000");
	try {
	    $this->assertTrue($this->isTextPresent("Group has been created successfully"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	    array_push($this->verificationErrors, $e->toString());
	}
	$this->click("confirm_btn");
	$this->waitForPageToLoad("30000");
	$this->click("link=Create post");
	$this->waitForPageToLoad("30000");
	$this->type("title", "a post for the new group");
	$this->type_into_tinymce("Content of the test post (for new group)");
	$this->click("document.formCreateContent.publish_post");
	$this->waitForPageToLoad("30000");
	$this->assertTrue((bool)preg_match("/^Are you sure you want to post the content/",$this->getConfirmation()));
	$this->click("link=a post for the new group");
	$this->waitForPageToLoad("30000");
	$this->click("link=Delete");
	$this->waitForPageToLoad("30000");
	$this->assertTrue((bool)preg_match("/^Are you sure you want to delete this group/",$this->getConfirmation()));
	$this->click("link=Configure");
	$this->waitForPageToLoad("30000");
	$this->click("document.forms[0].type[1]");
	$this->click("submit");
	$this->waitForPageToLoad("30000");
	try {
	    $this->assertTrue($this->isTextPresent("Network Information Successfully Updated"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	    array_push($this->verificationErrors, $e->toString());
	}
	$this->click("confirm_btn");
	$this->click("link=Groups");
	$this->waitForPageToLoad("30000");
	$this->click("link=Create a Group");
	$this->waitForPageToLoad("30000");
	$this->type("group_name", "group for private network");
	$this->click("addgroup");
	$this->waitForPageToLoad("30000");
	try {
	    $this->assertTrue($this->isTextPresent("Group has been created successfully"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	    array_push($this->verificationErrors, $e->toString());
	}
	$this->click("confirm_btn");
	$this->waitForPageToLoad("30000");
	$this->click("link=Configure");
	$this->waitForPageToLoad("30000");
	$this->click("delete_network");
	$this->click("document.delete_form.submit");
	try {
	  $this->waitForPageToLoad("30000");
	    $this->assertTrue(0, "We should have been redirected back to the home site after deleting the new network.  It probably failed");
	} catch (Testing_Selenium_Exception $e) {
	    // good!
	}

	// the network will have been deleted so we should be back at the home network now
	$this->stop();
	$new_url = PA::$url . PA_ROUTE_HOME_PAGE;
	echo "back to home network: $new_url\n";
	$this->setBrowserUrl($new_url);
	$this->setupSelenium();
	$this->start();
	$this->open(PA::$local_url . PA_ROUTE_HOME_PAGE);
	$this->login();

	$this->click("link=Network directory");
	$this->waitForPageToLoad("30000");
	$this->type("keyword", "testnet$this->net_key");
	$this->click("//input[@type='image']");
	$this->waitForPageToLoad("30000");
	try {
	    $this->assertTrue($this->isTextPresent("No Network Found"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	    array_push($this->verificationErrors, $e->toString());
	}
	$this->type("keyword", "test network (selenium) $this->net_key");
	$this->click("//input[@type='image']");
	$this->waitForPageToLoad("30000");
	try {
	    $this->assertTrue($this->isTextPresent("No Network Found"));
	} catch (PHPUnit_Framework_AssertionFailedError $e) {
	    array_push($this->verificationErrors, $e->toString());
	}
	$this->click("link=Me");
	$this->waitForPageToLoad("30000");
	$this->click("link=Edit My Account");
	$this->waitForPageToLoad("30000");
	
    }
}

?>