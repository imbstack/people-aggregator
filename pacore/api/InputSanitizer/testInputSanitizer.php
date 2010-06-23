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
﻿<?php
require_once('./InputSanitizer.php');

/*
$input[] = '
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<p>TEST</p>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">';

$input[] = '
 ';

$input[] = '
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"
"http://www.w3.org/TR/REC-html40/loose.dtd">
<html><head><script>
var i=1;
var name;
while(i<11){
name = prompt("Tell me your Name Please");
alert("Hello"+" "+name+" from Manoj Gupta");
i=i+1;
}
</script></head></html>
';


$input[] =
  "<h1>japanese test</h1>

<p>これからは日本語です。</p>

<p>日本語はだいじょうぶ？</p>";

$input['second'] = 
  'Test <a href="#" onclick="alert(document.cookies)">show me</a><p>a paragraph';
// $input[] = implode('', file("./testHTML.html"));

$input[] = "Title with <b>bold long test that should be truncated. Is it? HELLO? How long does this need to be? HUH?</b>";

$input[] = "Long JP title: ぶいぶいぶいぶいぶいぶいぶいぶい ぶいいいいいいいいいいいいい　いいいいいいいいいいいいいぶいいいいいいいいいいい　ぼぼぼぼぼぼぼぼぼぼぼぼぼぼぼぼぼぼぼ　ととととととととととととっとととととととととととっととと";

*/
$input[] = 
  "<H1>Hallo <p>One test</p> This is <b>a test <img src=blah.gif name=test /><br>
<div>And another</div>
<a href='test.html'>A normal Link</a>
<a href='javascript:alert(date())'>An illegal Link</a>
";
$input[] = 
  "<b>ThisIsOneExtremlyLongStringThatNeedsToBeBrokenIntoSmallerChunks";


$sDom = new InputSanitizer(
  array('h1','p','b','a'),
  array('href')
);


print "\nStripping all HTML:\n";
run_tests();

print "\nPassing through HTML\n";
$sDom->htmlAllowedEverywhere = TRUE;
$sDom->wbr = 15;
run_tests();

function run_tests() {
  global $sDom, $input;

  $rea =
    $sDom->process($input);
  foreach($rea as $key=>$value) {
    print "[$key]: ".($value)."\n";
  }
  
  print "\nTruncating:\n";
  
  $rea =
    $sDom->process($input, 20);
  foreach($rea as $key=>$value) {
    print "[$key]: " . strlen($value) . "\n" . ($value)."\n";
  }
}



?>