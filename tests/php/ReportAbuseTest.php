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
// Call ReportAbuseTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "ReportAbuseTest::main");
}

require_once dirname(__FILE__)."/lib/common.php";
require_once "ext/ReportAbuse/ReportAbuse.php";

class ReportAbuseTest extends PHPUnit_Framework_TestCase {
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("ReportAbuseTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
    }

    /**
     * @todo Implement testSave().
     */
    /*
    public function testSave() {
        $report_abuse_obj = new ReportAbuse();
        $report_abuse_obj->parent_type = TYPE_CONTENT;
        $report_abuse_obj->parent_id = 323;
        $report_abuse_obj->reporter_id = 1;
        $report_abuse_obj->body = 'This is a bad image i hope you will delete this content';
        $id = $report_abuse_obj->save();
        //this id should be equal to newly created id
        $res = Dal::query('SELECT max(report_id) as ID from {report_abuse}');
        $r = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $max_id = $r->ID;
        $this->assertEquals($id , $max_id);
    }
    */

    public function testSaveWithEmptyBody(){
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = TYPE_CONTENT;
      $report_abuse_obj->parent_id = 323;
      $report_abuse_obj->reporter_id = 1;
      $report_abuse_obj->body = '';
      try {
        $id = $report_abuse_obj->save();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING);
    }
    
    public function testSaveWithEmptyParentType(){
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = NULL;
      $report_abuse_obj->parent_id = 323;
      $report_abuse_obj->reporter_id = 1;
      $report_abuse_obj->body = 'This is a bad image i hope you will delete this content';
      try {
        $id = $report_abuse_obj->save();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING);
    }
    
    public function testSaveWithEmptyParentId(){
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = TYPE_CONTENT;
      $report_abuse_obj->parent_id = null;
      $report_abuse_obj->reporter_id = 1;
      $report_abuse_obj->body = 'This is a bad image i hope you will delete this content';
      try {
        $id = $report_abuse_obj->save();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING);
    }
    
    
    public function testSaveWithEmptyReporterId(){
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = TYPE_CONTENT;
      $report_abuse_obj->parent_id = 323;
      $report_abuse_obj->reporter_id = NULL;
      $report_abuse_obj->body = 'This is a bad image i hope you will delete this content';
      try {
        $id = $report_abuse_obj->save();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING);
    }
        
    public function testSaveWithInvalidReporterId(){
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = TYPE_CONTENT;
      $report_abuse_obj->parent_id = 323;
      $report_abuse_obj->reporter_id = 'abc';
      $report_abuse_obj->body = 'This is a bad image i hope you will delete this content';
      try {
        $id = $report_abuse_obj->save();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, USER_NOT_FOUND);
    }
    
    
    public function testSaveWithInvalidParentType(){
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = 'ssss';
      $report_abuse_obj->parent_id = 323;
      $report_abuse_obj->reporter_id = 1;
      $report_abuse_obj->body = 'This is a bad image i hope you will delete this content';
      try {
        $id = $report_abuse_obj->save();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, INVALID_ARGUMENTS);
    }
    
    /**
     * @todo Implement testGet().
     */
    public function testGet() {
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->report_id = 2;
      $result = $report_abuse_obj->get();
      
      $res = Dal::query('SELECT (body) from {report_abuse} where report_id = ?', array($report_abuse_obj->report_id));
      
      $r = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $status = $r->body;
      $this->assertEquals($result['body'] , $status);
    }

    public function testGet_WithEmptyId() {
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->report_id = NULL;
      try {
        $report_abuse_obj->get();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING); 
    }
    
    public function testGet_WithInvalidId() {
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->report_id = 'abc';
      try {
        $report_abuse_obj->get();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, INVALID_PARAMETER); 
    }
    
    
    /**
     * @todo Implement testGet_multiples().
     */
    
    public function testGet_multiples() {
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = TYPE_CONTENT;
      $report_abuse_obj->parent_id = 323;
      $result = $report_abuse_obj->get_multiples();
      
      $field = 'parent_type = ? AND parent_id = ?';
      $sql = "SELECT report_id, parent_type, parent_id, body, created, reporter_id
            FROM {report_abuse} 
            WHERE $field";
      
      $data = array($report_abuse_obj->parent_type, $report_abuse_obj->parent_id);
            
      $res = Dal::query($sql, $data);
      
      $test_result = array();
      if ($res->numRows()) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $test_result[] = $row;
        }
      }
     
      $this->assertEquals($result , $test_result); 
    }
    
    
    public function testGet_multiples_With_Invalid_type() {
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = 'abc';
      $report_abuse_obj->parent_id = 323;
      
      try{
        $result = $report_abuse_obj->get_multiples();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code , INVALID_ARGUMENTS); 
    }
    
    public function testGet_multiples_With_Empty_ParentId() {
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_type = 'abc';
      
      try{
        $result = $report_abuse_obj->get_multiples();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code , INVALID_ARGUMENTS); 
    }
    
    public function testGet_multiples_With_Empty_ParentType() {
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->parent_id = 323;
      
      try{
        $result = $report_abuse_obj->get_multiples();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code , INVALID_ARGUMENTS); 
    }
    
    
    public function testGet_multiples_With_Empty_All() {
      $report_abuse_obj = new ReportAbuse();
      
      try{
        $result = $report_abuse_obj->get_multiples();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code , INVALID_PARAMETER); 
    }
    
    public function testGet_multiples_with_ReportId() {
      $report_abuse_obj = new ReportAbuse();
      $report_abuse_obj->report_id = 2;
      $result = $report_abuse_obj->get_multiples();
      
      $field = 'report_id = ?';
      $sql = "SELECT report_id, parent_type, parent_id, body, created, reporter_id
            FROM {report_abuse} 
            WHERE $field";
      
      $data = array($report_abuse_obj->report_id);
            
      $res = Dal::query($sql, $data);
      
      $test_result = array();
      if ($res->numRows()) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $test_result[] = $row;
        }
      }
     
      $this->assertEquals($result , $test_result); 
    }

}

// Call ReportAbuseTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "ReportAbuseTest::main") {
    ReportAbuseTest::main();
}
?>
