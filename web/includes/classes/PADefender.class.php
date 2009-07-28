<?php
/**
 * @Class PADefender
 *
 * This class provides basic functions for the detection of some of the
 * common web attacks such as MySQL injection, XSS, etc.
 *
 * based on PHPIDS attack detection rules
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 *
 */

 class PADefender {

   const suspended = 1;
   const permisive = 2;
   const restrictive = 3;

   public static $defend_mode; // = PADefender::restrictive;

   private $rules;
   public $is_attack;
   public $message;

   public function __construct($rules) {
     $this->rules = $rules;
     $this->is_attack = false;
     $this->message = null;
     if(!isset(self::$defend_mode)) {
       self::$defend_mode = PA::$config->pa_defender_mode;
     }
//echo "PADefender mode is: " . (int)self::$defend_mode . "<br />";die();
   }

   public function test($string) {
     $this->is_attack = false;
     $this->message = null;
//echo "PADefender mode is: " . (int)self::$defend_mode . "<br />";die();

     if(!is_string($string)) {
          throw new Exception(sprintf(__('%s function expects string as first argument.'), 'PADefender::test()'));
     }

     if(!preg_match('/[^\w\s\/@!?,\.]+|(?:\.\/)|(?:@@\w+)/', $string)) {
        return false;
     }
//echo "<pre>" . print_r($this->rules,1) . "</pre>";
     foreach($this->rules as $rule) {
       if($rule['active'] == 0) continue;
       if(preg_match("/{$rule['expression']}/ms", strtolower($string), $matches)) {
          $this->matches = $matches;
          $this->is_attack = true;
          $this->message = $rule['description'];
//echo "IsAtck: $this->is_attack , msg: " . $this->message . "<br />";
//     die();
          break;
       }
//echo "Testing: " . $rule['description'] . "rule active=" . (int)$rule['active'] . "<br />";
     }
     return $this->is_attack;
   }


   private function recursive_test($value, $key) {
//echo "DEF MODE: "   . PADefender::getMode() . "<br />";die();
     $res = false;
     if($this->test($value) && (PADefender::getMode() != PADefender::suspended)) {
       if((PADefender::getMode() == PADefender::permisive)) {
          $this->showMessage($this->message);
       } else {
         throw new PADefenderException($this->message, "<pre>" . print_r($this->matches,1) . "</pre>");
       }
     }
   }


   public static function testString($str, $rules) {
     $defender = new self($rules);
     if($defender->test($str) && (PADefender::getMode() != PADefender::suspended)) {
       if((PADefender::getMode() == PADefender::permisive)) {
          $this->showMessage($defender->message);
       } else {
          throw new PADefenderException($defender->message, "<pre>" . print_r($defender->matches,1) . "</pre>");
       }
     }
   }

   public static function testArrayRecursive($array, $rules) {
     $defender = new self($rules);
     return array_walk_recursive($array, array($defender, 'recursive_test'));
   }

   public static function getMode() {
      $session_started = (isset($_SESSION)) ? true : false;
      if(!$session_started) session_start();
      if(isset($_SESSION['defend_mode'])) {
        self::$defend_mode = $_SESSION['defend_mode'];
      }
      if(!$session_started) session_commit();
      return self::$defend_mode;
   }

   public static function setMode($mode) {
      $session_started = (isset($_SESSION)) ? true : false;
      if(!$session_started) session_start();
      self::$defend_mode = $_SESSION['defend_mode'] = $mode;
      if(!$session_started) session_commit();
  }

   private function showMessage($message) {
      $msg = "<div style=\"border: 1px solid red; padding: 24px\">
                <h1 style=\"color: red\">Warning - Potentially malicious or unauthorized activities detected</h1>\r\n
                <p style=\"color: red\">$message</p> \r\n
              </div>\r\n";
      echo $msg;
   }
}

 class PADefenderException extends Exception {

    public function __construct($msg, $details = null) {
       parent::__construct($msg);
       $this->handleException($msg, $details);
    }

    private function handleException($msg, $details) {
     global $app;
      require_once "api/Theme/Template.php";
      require_once "api/Logger/Logger.php";
      Logger::log("user_ip: [{$app->remote_addr}], rule: [$msg], \ndata matched: \n$details\n", LOGGER_ERROR, LOGGER_FILE, PA_PROJECT_PROJECT_DIR ."/log/defender.log");

      $message = __("Malicious or unauthorized activities detected. If you think this is an error, ".
                    "please notify the system administrator. Your data will be stored in our ".
                    "database for each case.");
      header("HTTP/1.0 406 Not Acceptable");
      $template_file = getShadowedPath('web/Themes/Default/defender.tpl');
      $template = & new Template($template_file);
      $template->set('message', $message);
      $template->set('details', $msg /* . "<pre>" . print_r($_POST,1) . "</pre>" */);
      echo $template->fetch();
      exit;
    }

 }
?>