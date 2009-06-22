<?php

// Test profanity filter.

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";
require_once "api/Validation/ProfanityFilter.php";

class ProfanityFilterTest extends PHPUnit_Framework_TestCase {
    function testProfanityFilter() {
        /* these test will no longer work, as we are dealing with random replacements
        
        $this->assertEquals(ProfanityFilter::filterHTML("<div>what <a href='foobar'>the</a> fuck?</div>"), "<div>what <a href='foobar'>the</a> #&@!*?</div>");
        $this->assertEquals(ProfanityFilter::filterHTML("what the fuck?"), "what the #&@!*?");
        $this->assertEquals(ProfanityFilter::filterHTML("---cusstest1---"), "---cusstest1-filtered---");
        */
        global $_PA;
        
        $profaneHTML = "<div>what <a href='foobar'>the</a> fuck?</div> brainfuck should be safe... what about assingement? Will it be filtered as ass?
        Let's see about FUcK or Ass";
        $filtered = ProfanityFilter::filterHTML($profaneHTML);
        // count profanity in original and filtered
        $cnt_prof = 0;
        $cnt_filt = 0;
        foreach ($_PA->profanity as $i=>$w) {
          $regexp = "/\b" . $w . "\b/i";
          $cnt_prof += preg_match_all($regexp, $profaneHTML, $m);
          $cnt_filt += preg_match_all($regexp, $filtered, $m);
        }
        echo "$cnt_prof profane words in input\n$cnt_filt in filtered output\n";
        echo "$profaneHTML \n------\n$filtered\n";
        $this->assertEquals($cnt_filt, 0, "expected 0 profane words, got $cnt_filt\n");
        
        
    }
}

?>