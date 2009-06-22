<?php

// Test that we can read and count content items.

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";

class ReadContentTest extends PHPUnit_Framework_TestCase {

  function testLoadAllContentIDArray() {
    // test that $order_by is working properly - expect it's going to need C.$order_by
    // L941 Content.php
    // used by web/BetaBlockModules/NetworkResultContentModule/NetworkResultContentModule.php
    
    // count content
    $ct = Content::load_all_content_id_array(TRUE);
    echo "content count: $ct\n";

    // load first page
    $content = Content::load_all_content_id_array(FALSE, 10, 1);
    echo count($content)." content items\n";
    //var_dump($content);
  }

}

?>