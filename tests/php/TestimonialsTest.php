<?php
// Call TestimonialsTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "TestimonialsTest::main");
}

require_once dirname(__FILE__)."/lib/common.php";
require_once "api/Testimonials/Testimonials.php";

class TestimonialsTest extends PHPUnit_Framework_TestCase {
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("TestimonialsTest");
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
   /* public function testSave() {
        $testi = new Testimonials();
        $testi->sender_id = 3;
        $testi->recipient_id = 7;
        $testi->body = 'Admin can do most of the things in the Admin capablities';
        $id = $testi->save();
        //this id should be equal to newly created id
        $res = Dal::query('SELECT max(testimonial_id) as ID from {testimonials}');
        $r = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $max_id = $r->ID;
        $this->assertEquals($id , $max_id);
    }*/
    public function testSaveWithEmptyBody(){
        $testi = new Testimonials();
        $testi->sender_id = 3;
        $testi->recipient_id = 7;
        $testi->body = '';
        try {
          $id = $testi->save();
        }
        catch (PAException $e) {
          $error = $e->message;
          $code = $e->code;
        }
        $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING);
    }
    
    public function testSaveWithEmptySenderId(){
        $testi = new Testimonials();
        $testi->sender_id = NULL;
        $testi->recipient_id = 7;
        $testi->body = 'testimonial with empty Sender id';
        try {
          $id = $testi->save();
        }
        catch (PAException $e) {
          $error = $e->message;
          $code = $e->code;
        }
        $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING);
    }
    
    public function testSaveWithEmptyRecipientId(){
        $testi = new Testimonials();
        $testi->sender_id = 3;
        $testi->recipient_id = NULL;
        $testi->body = 'testimonial with empty Sender id';
        try {
          $id = $testi->save();
        }
        catch (PAException $e) {
          $error = $e->message;
          $code = $e->code;
        }
        $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING);    
    }
    
    public function testSaveWithSenderIdSameWithRecipientId(){
        $testi = new Testimonials();
        $testi->sender_id = 3;
        $testi->recipient_id = 3;
        $testi->body = 'testimonial with empty Sender id';
        try {
          $id = $testi->save();
        }
        catch (PAException $e) {
          $error = $e->message;
          $code = $e->code;
        }
        $this->assertEquals($code, INVALID_ARGUMENTS);
    }
    
    public function testSaveWithInvalidRecipientId(){
        $testi = new Testimonials();
        $testi->sender_id = 3;
        $testi->recipient_id = 'xyz';
        $testi->body = 'testimonial with empty Sender id';
        try {
          $id = $testi->save();
        }
        catch (PAException $e) {
          $error = $e->message;
          $code = $e->code;
        }
        $this->assertEquals($code, USER_NOT_FOUND);     
    }
    
    /**
     * @todo Implement testChange_status().
     */
    public function testChange_status() {
	$this->markTestIncomplete("testChange_status not executed as it is dangerous -- it should find two users, create a testimonial between then, then change the status of THAT testimonial.  As it is, it will approve a testimonial that might be part of a real site.");
	
        $testi = new Testimonials();
        $testi->status = APPROVED;
        $testi->testimonial_id = 1;
        $testi->change_status();
        //this id should be equal to newly created id
        $res = Dal::query('SELECT (status) from {testimonials} where testimonial_id = ?', array(1));
        $r = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $status = $r->status;
        $this->assertEquals($testi->status , $status); 
    }
    
    public function testChange_statusWithInvalidTestimonialId() {
        $testi = new Testimonials();
        $testi->status = APPROVED;
        $testi->testimonial_id = 'abc';
        try {
          $testi->change_status();
        }
        catch (PAException $e) {
          $error = $e->message;
          $code = $e->code;
        }
        $this->assertEquals($code, INVALID_PARAMETER); 
    }

    public function testChange_statusWithEmptyTestimonialId() {
        $testi = new Testimonials();
        $testi->status = APPROVED;
        $testi->testimonial_id = 0;
        try {
          $testi->change_status();
        }
        catch (PAException $e) {
          $error = $e->message;
          $code = $e->code;
        }
        $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING); 
    }

    public function testChange_statusWithInvalidStatus() {
        $testi = new Testimonials();
        $testi->status = 'delete';
        $testi->testimonial_id = 1;
        try {
          $testi->change_status();
        }
        catch (PAException $e) {
          $error = $e->message;
          $code = $e->code;
        }
        $this->assertEquals($code, INVALID_PARAMETER); 
    }
                    
    /**
     * @todo Implement testGet().
     */
    public function testGet() {
        $testi = new Testimonials();
        $testi->testimonial_id = 1;
        $result = $testi->get();
        $res = Dal::query('SELECT (body) from {testimonials} where testimonial_id = ? AND is_active = ?', array($testi->testimonial_id, 1));
        $r = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $status = $r->body;
        $this->assertEquals($result['body'] , $status); 
    }

    public function testGet_WithInvalidTestimonialId() {
      $testi = new Testimonials();
      $testi->testimonial_id = 'abc';
      try {
        $testi->get();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, INVALID_PARAMETER); 
    
    }
    
    public function testGet_WithEmptyTestimonialId() {
      $testi = new Testimonials();
      $testi->testimonial_id = NULL;
      try {
        $testi->get();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING); 
    }
    
    /**
     * @todo Implement testDelete_testimonial().
     */
    public function testDelete_testimonial() {
      $this->markTestIncomplete("testChange_status not executed as it is dangerous -- it should find two users, create a testimonial between then, then change the status of THAT testimonial.  As it is, it will approve a testimonial that might be part of a real site.");

      $testi = new Testimonials();
      $testi->testimonial_id = 1;
      $testi->delete_testimonial();
      $res = Dal::query('SELECT (is_active) from {testimonials} where testimonial_id = ?', array(1));
      $r = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $status = $r->is_active;
      $this->assertEquals(DELETED , $status); 
    }
    
    public function testDelete_testimonial_WithInvalidId() {
      $testi = new Testimonials();
      $testi->testimonial_id = 'abc';
      try {
        $testi->delete_testimonial();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, INVALID_PARAMETER);
    }
    
    public function testDelete_testimonial_WithEmptyId() {
      $testi = new Testimonials();
      $testi->testimonial_id = NULL;
      try {
        $testi->delete_testimonial();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code, REQUIRED_PARAMETERS_MISSING);
    }
    
   /**
     * @todo Implement testGet_multiple_testimonials().
     */
  public function testGet_multiple_testimonials_WithTestimonial_Id() {
        $testi = new Testimonials();
        $testi->testimonial_id = 2;
        
        $result = $testi->get_multiple_testimonials();
        
        $res = Dal::query('SELECT T.testimonial_id, T.sender_id, T.recipient_id, T.body, T.created, T.changed, U.login_name as username, U.picture as user_pic, U.first_name as user_fname, U.last_name as user_lname, U.email as user_email 
            FROM testimonials as T
            INNER JOIN users as U
            ON T.sender_id = U.user_id
            WHERE T.testimonial_id = ? AND T.is_active = 1 AND status = "pending" AND U.is_active = 1  ORDER BY T.created DESC ', array($testi->testimonial_id));
        
        $test_result = array();
        if ($res->numRows()) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $test_result[] = $row;
          }
        }
        $this->assertEquals($result , $test_result); 
        
    }
    
    public function testGet_multiple_testimonials_WithSender_Id() {
        $testi = new Testimonials();
        $testi->sender_id = 3;
        
        $result = $testi->get_multiple_testimonials();
        
        $res = Dal::query('SELECT T.testimonial_id, T.sender_id, T.recipient_id, T.body, T.created, T.changed, U.login_name as username, U.picture as user_pic, U.first_name as user_fname, U.last_name as user_lname, U.email as user_email 
            FROM testimonials as T
            INNER JOIN users as U
            ON T.sender_id = U.user_id
            WHERE T.sender_id = ? AND T.is_active = 1 AND status = "pending" AND U.is_active = 1  ORDER BY T.created DESC', array($testi->sender_id));
        
        $test_result = array();
        if ($res->numRows()) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $test_result[] = $row;
          }
        }
        
        $this->assertEquals($result , $test_result); 
        
    }
    public function testGet_multiple_testimonials_WithRecipient_Id() {
        $testi = new Testimonials();
        $testi->recipient_id = 2;
        
        $result = $testi->get_multiple_testimonials();
        
        $res = Dal::query(' SELECT T.testimonial_id, T.sender_id, T.recipient_id, T.body, T.created, T.changed, U.login_name as username, U.picture as user_pic, U.first_name as user_fname, U.last_name as user_lname, U.email as user_email
            FROM testimonials as T
            INNER JOIN users as U
            ON T.sender_id = U.user_id
            WHERE T.recipient_id = ? AND T.is_active = 1 AND U.is_active = 1  AND status = "pending" ORDER BY T.created DESC ', array(2));
        
        $test_result = array();
        if ($res->numRows()) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $test_result[] = $row;
          }
        }
        
        $this->assertEquals($result , $test_result); 
    }
    
    public function testGet_multiple_testimonials_WithEmpty_Id() {
      $testi = new Testimonials();
      try {
        $testi->get_multiple_testimonials();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code , INVALID_PARAMETER); 
    }
        
    public function testGet_multiple_testimonials_WithMultiple_Id() {
      $testi = new Testimonials();
      try {
        $testi->sender_id = 3;
        $testi->recipient_id = 7; 
        $testi->get_multiple_testimonials();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code , INVALID_PARAMETER); 
    }
    
    public function testGet_multiple_testimonials_WithInvalid_Id() {
      $testi = new Testimonials();
      try {
        $testi->sender_id = 'abc';
        $testi->get_multiple_testimonials();
      }
      catch (PAException $e) {
        $error = $e->message;
        $code = $e->code;
      }
      $this->assertEquals($code , INVALID_PARAMETER); 
    }
}

// Call TestimonialsTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "TestimonialsTest::main") {
    TestimonialsTest::main();
}
?>
