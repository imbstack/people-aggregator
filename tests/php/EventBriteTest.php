<?php

// Test that we can fetch data from EventBrite

// Author: phil

require_once dirname(__FILE__)."/lib/common.php";
require_once "ext/EventBrite/EventBrite.php";

class EventBriteTest extends PHPUnit_Framework_TestCase {

  function testFetchAttendees() {

    // not really a test at the moment, just a script to use to get eventbrite data and make sure it's working

    global $eventbrite_login, $eventbrite_password, $eventbrite_event;
    if (empty($eventbrite_login) || empty($eventbrite_password) || empty($eventbrite_event)) {
      echo ($msg = "Eventbrite login and event details not available")."\n";
      $this->markTestSkipped($msg);
      return;
    }

    $eb = new EventBrite($eventbrite_login, $eventbrite_password);
    $attendees = $eb->get_attendees($eventbrite_event);

    echo count($attendees)." attendees\n";
    //echo "------------------- attendees ---------------\n";
    //var_dump($attendees);
    
  }

}

?>