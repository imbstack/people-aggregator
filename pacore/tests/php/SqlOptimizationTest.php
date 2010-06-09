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
 * SQL query optimization tests
 * @author  Marek Kuziel <marek@kuziel.info>
 */
// {{{ SqlOptimizationTest extends PHPUnit_Framework_Test
class SqlOptimizationTest implements PHPUnit_Framework_Test {
    private $queries;
    // {{{ __construct($queriesData)
    public function __construct($queriesData) {
        $this->queries = $queriesData;
    }
    // }}}
    // {{{ count()
    public function count() {
        return sizeof($this->queries);
    }
    // }}}
    // {{{ run(PHPUnit_Framework_TestResult $result = NULL)
    public function run(PHPUnit_Framework_TestResult $result = NULL) {
        if ($result === NULL) {
            $result = new PHPUnit_Framework_TestResult;
            $result->startTest($this);
	    $t_start = microtime(TRUE);
            $counter = 0;
            foreach ($this->queries as $query_data) {
                $query = 'EXPLAIN '.$query_data['query'];
                $parameters = $query_data['parameters'];
                $parameters_print = '';
                try {
                    if (!empty($parameters)) {
                        $res = Dal::query($query, $parameters);
                        $parameters_print = 'PARAMETERS:'."\n";
                        foreach ($parameters as $param) {
                            $parameters_print .= '- '.$param."\n";
                        }
                    } else {
                        $res = Dal::query($query);
                    }
                } catch (PAException $e) {
                    try {
                        PHPUnit_Framework_Assert::assertEquals($e->getCode(), DB_QUERY_FAILED);
                    }
                    catch (PHPUnit_Framework_AssertionFailedError $e) {
                        $result->addFailure($this, $e);
                    }
                    catch (Exception $e) {
                        $result->addError($this, $e);
                    }
                }
		        $tables = array();
		        print "{{{ ==================================================================\n";
		        $query_row = wordwrap($explain."QUERY: \"$query\"", 70);
		        print $query_row."\n";
		        if (!empty($parameters_print)) {
		            print "----------------------------------------------------------------------\n";
		            print $parameters_print;
		        }
		        while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                    print "----------------------------------------------------------------------\n";
                    print 'ID: '.$row->id."\n";
                    print 'SELECT TYPE: '.$row->select_type."\n";
                    print 'TABLE: '.$row->table."\n";
                    print 'TYPE: '.$row->type."\n";
                    print 'POSSIBLE KEYS: '.$row->possible_keys."\n";
                    print 'KEY: '.$row->key."\n";
                    print 'KEY LENGTH: '.$row->key_len."\n";
                    print 'REFERENCE: '.$row->ref."\n";
                    print 'ROWS: '.$row->rows."\n";
                    print 'EXTRA: '.$row->Extra."\n";
		            if (!empty($row->table)) {
                        $tables[] = $row->table;
                    }
                    $counter++;
                }
		        // Now show all the tables used in the query.
		        foreach ($tables as $table) {
		            print "----------------------------------------------------------------------\n";
		            try {
			            $create_table = Dal::query_one("SHOW CREATE TABLE $table");
		            } catch (PAException $e) {
			            if ($e->getCode() != DB_QUERY_FAILED) {
                            throw $e;
                        }
			            $bits = preg_split("/(\s+|,)/", $query);
			            $pos = array_search($table, $bits);
			            if ($pos === NULL) {
                            throw new PAException(GENERAL_SOME_ERROR, "Failed to find real name for table $table in query $sql");
                        }
			            $table = (strtolower($bits[$pos-1]) == 'as') ? $bits[$pos-2] : $bits[$pos-1];
			            $create_table = Dal::query_one("SHOW CREATE TABLE $table");
		            }
		            echo $create_table[1]."\n";
		        }
		        print "================================================================== }}}\n";
            }
            $result->endTest($this, microtime(TRUE) - $t_start);
            return $result;
        }
    }
    // }}}
}
// }}}
?>
