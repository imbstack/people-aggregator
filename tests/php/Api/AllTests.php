<?php
/**
 * Run all tests for PeopleAggregator's API
 * @author  Marek Kuziel <marek@kuziel.info>
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Api_AllTests::main');
}
// {{{ Requires
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
// Require all tests
require_once dirname(__FILE__).'/Category/CategoryTest.php';
// }}}
// {{{ Api_AllTests
class Api_AllTests
{
    // {{{ main()
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    // }}}
    // {{{ suite()
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('PeopleAggregator API');
	//        $suite->addTestSuite('Api_Category_CategoryTest');
        return $suite;
    }
    // }}}
}
// }}}
if (PHPUnit_MAIN_METHOD == 'Api_AllTests::main') {
    Api_AllTests::main();
}
?>
