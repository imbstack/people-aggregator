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
 * Run all tests for PeopleAggregator's API
 * @author  Marek Kuziel <marek@kuziel.info>
 */
// {{{ Requires
require_once dirname(__FILE__).'/../../lib/common.php';
require_once "api/Category/Category.php";
require_once dirname(__FILE__).'/../../SqlOptimizationTest.php';
// }}}
// {{{ Api_Category_CategoryTest
class Api_Category_CategoryTest extends PHPUnit_Framework_TestCase
{
    // {{{ testSpeedBuildAllCategoryListRunWithEmptyDataSet()
    public function testSpeedBuildAllCategoryListRunWithEmptyDataSet() {
        $desired_speed = 0.05;
        $t_start = microtime(true);
        // {{{ Run build all category list
        $output = Category::build_all_category_list();
        // }}}
        $t_end = microtime(true);
        $speed = $t_end - $t_start;
        $message = '';
        $message .= '---------------------------------'."\n";
        $message .= 'Actual speed: '.(float)$speed."\n";
        $message .= 'Desired speed: '.(float)$desired_speed."\n";
        $message .= '---------------------------------'."\n";
        $this->assertTrue((float)$desired_speed > (float)$speed, $message);
    }
    // }}}
    // {{{ testSqlBuildAllCategoryList()
    public function testSqlBuildAllCategoryList() {
        // Prepare SQL statement
        $position = '';
        $sql = "SELECT * FROM {categories} WHERE position RLIKE  '^".$position."[0-9]+>$'";
        // Prepare data in test format
        $queries = array();
        $queries[] = array('query' => $sql, 'parameters' => array());
        // Run SqlOptimizationTest
        $test = new SqlOptimizationTest($queries);
        print "\n";
        $result = $test->run();
        print "\n";
        if ($failures = $result->failures()) {
            print "\n".$failures[0]->thrownException()->toString()."\n";
        }
    }
    // }}}
}
// }}}
?>
