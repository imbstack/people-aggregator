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

/* ProfanityFilter - filter to get rid of 'cuss words' from publicly
   visible text.

   Author: martin (re written phil's prototype)
   Copyright (C) 2007 Broadband Mechanics, Inc.
   
   This class relies on the presence of PA::$config->profanity array
   which should be defined in one of the *_config.php files
   (default_config.php)

*/

class ProfanityFilter {
  
  public static function replaceCallback($text) {
    var_dump($text); echo "<hr>\n";
  }

  public static function replace($s) {
    // return strtoupper('*bleep*');
    $repl_chars = array('$'=>1,'%'=>1,'@'=>1,'#'=>1,'*'=>1);
    $chrcnt = strlen($s);
    $repl = '';
    for ($i=0;$i<$chrcnt;$i++) {
      $repl .= array_rand($repl_chars);
    }
    return $repl;
  }
  
  public static function filterHTML($html) {
     
    // a simple preg_replace will do for now to see it actually works
    // turn the PA::$config->profanity array into an array of regexp
    $profanity = array();
    foreach (PA::$config->profanity as $i=>$w) {
      $profanity[] = "!\b(" . $w . ")\b!ie"; 
      // the \b ensures that we are dealing with szand alone 
      // occurences of the word or phrase
      // /ie case insensitive and run replacement as code
    }
    $repl_code = "ProfanityFilter::replace('$1')";
    return (preg_replace($profanity, $repl_code, $html));
  }

}

?>