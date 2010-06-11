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
/**
 * Run all tests for PeopleAggregator
 * @author  Marek Kuziel <marek@kuziel.info>
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}
// {{{ Requires
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
// Require all tests (suites)
require_once 'Api/AllTests.php';
require_once 'ConfigVariableTest.php';
require_once 'ContentCommentsTest.php';
require_once 'EmailNotificationTest.php';
require_once 'EventBriteTest.php';
require_once 'GlobalFunctionsTest.php';
require_once 'NetworkCreationTest.php';
require_once 'NetworkDataTest.php';
require_once 'ProfanityFilterTest.php';
require_once 'ReadContentTest.php';
require_once 'StorageTest.php';
require_once 'StructuredBloggingParsingTest.php';
require_once 'UrlGenerationTest.php';
require_once 'UserProfileDataTest.php';
require_once 'UserRegistrationTest.php';
require_once 'TestimonialsTest.php';
require_once 'ReportAbuseTest.php';
require_once 'RolesTest.php';
require_once 'TasksTest.php';
// }}}
// {{{ AllTests
class AllTests
{
    // {{{ main()
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    // }}}
    // {{{ suite()
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('PeopleAggregator');
        $suite->addTest(Api_AllTests::suite());
	$suite->addTestSuite('ConfigVariableTest');
	$suite->addTestSuite('ContentCommentsTest');
	$suite->addTestSuite('EmailNotificationTest');
	$suite->addTestSuite('EventBriteTest');
	$suite->addTestSuite('GlobalFunctionsTest');
	$suite->addTestSuite('NetworkCreationTest');
	$suite->addTestSuite('NetworkDataTest');
	$suite->addTestSuite('ProfanityFilterTest');
	$suite->addTestSuite('ReadContentTest');
	$suite->addTestSuite('StorageTest');
	$suite->addTestSuite('StructuredBloggingParsingTest');
	$suite->addTestSuite('UrlGenerationTest');
	$suite->addTestSuite('UserProfileDataTest');
	$suite->addTestSuite('UserRegistrationTest');
	$suite->addTestSuite('TestimonialsTest');
	$suite->addTestSuite('ReportAbuseTest');
  $suite->addTestSuite('RolesTest');
  $suite->addTestSuite('TasksTest');
        return $suite;
    }
    // }}}
}
// }}}
if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
?>
