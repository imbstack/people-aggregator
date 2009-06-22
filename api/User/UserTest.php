<?

require_once "User.php";
require_once 'PHPUnit.php';

class UserTestCase extends PHPUnit_TestCase {
  public function __construct($name) {
    parent::__construct($name);
  }

  public function testUserCRUD() {
    $user = new User();
    try {
      $user->load('gaurav');
    }
    catch (PAException $e) {
      if ($e->getMessage() == "No such user") {
        $user = new User();
        $user->first_name = 'Gaurav';
        $user->last_name = 'Bhatnagar';
        $user->homepage = 'http://www.newdelhitimes.org';
        $user->login_name = 'gaurav';
        $user->password = md5('password1');
        $user->email = 'gaurav@tekritisoftware.com';
        $user->save();
      }
      else {
        throw $e;
      }
    }

    $newuser = new User();
    $newuser->load('gaurav');
    $this->assertTrue($newuser->first_name == 'Gaurav');
    $newuser->delete();
    $this->assertTrue($newuser->is_active == FALSE);
  }
}

$suite = new PHPUnit_TestSuite();
$suite->addTest(new UserTestCase('testUserCRUD'));
$result = new PHPUnit_TestResult();
$suite->run($result);

print($result->toString());

?>
