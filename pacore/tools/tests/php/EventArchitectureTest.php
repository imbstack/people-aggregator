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

require_once dirname(__FILE__)."/lib/common.php";
require_once("api/Event/Event.php");
require_once("api/Event/EventAssociation.php");
require_once("api/Event/Calendar.php");
require_once("api/Network/Network.php");

class EventArchitectureTest extends PHPUnit_Framework_TestCase {

    public function testAddUpdateDeleteEvent() {
        //    Dal::register_query_callback("explain_query");
        echo "getting a user\n";
        $user = Test::get_test_user();
        $testusername = $user->first_name." ".$user->last_name;
        echo "test user = $testusername\n";

        /* setup some times and time strings */
        $today             = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $tomorrow          = mktime(0, 0, 0, date("m"), date("d")+1, date("Y"));
        $yesterday         = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
        $lastmonth         = mktime(0, 0, 0, date("m")-1, date("d"), date("Y"));
        $nextmonth         = mktime(0, 0, 0, date("m")+1, date("d"), date("Y"));
        $nextyear          = mktime(0, 0, 0, date("m"), date("d"), date("Y")+1);
        $oneday            = 60*60*24;
        $simple_dateformat = "Y-m-d";

        /* use the constants in the format parameter */
        // something like: Mon, 15 Aug 2005 15:12:46 UTC
        $now_rfc822 = date(DATE_RFC822);
        // something like: 2000-07-01T00:00:00+00:00
        // $now_atom = date(DATE_ATOM);
        // create an Event
        echo "create and save Event\n";
        $e = new Event();
        $e->content_id = "http://myevent.info/1";
        // anything basically
        $e->user_id     = $user->user_id;
        $e->event_title = "Test Event for $testusername";
        $now            = time();
        $nowplusoneday  = $now+$oneday;
        $e->start_time  = date(DATE_ATOM, $now);
        $e->end_time    = date(DATE_ATOM, $now+60*60);
        // duration 1h
        $e->event_data = array(
            'description' => "This Event takes place to test the class Event",
            'start'       => $now,
            'end'         => $now+60*60,
        );
        $e->save();
        // print_r($e);
        // see if we got it
        echo "Retrieving Event $e->event_id\n";
        $e2 = new Event();
        $e2->load($e->event_id);
        echo "Testing integrity of dates\n";
        // print_r($e2);
        // see if the stored timestamps match
        $this->assertEquals($now, $e2->event_data['start']);
        // see if our dates survived the DB conversion roundtrip
        $this->assertEquals($now, strtotime($e2->start_time));
        $this->assertEquals($now+60*60, strtotime($e2->end_time));
        // create two EventAssociations
        $ea1          = new EventAssociation();
        $ea2          = new EventAssociation();
        $ea1->user_id = $user->user_id;
        $ea2->user_id = $user->user_id;
        // user EventAssocoiation
        $ea1->assoc_target_type = 'user';
        $ea1->assoc_target_id = $user->user_id;
        // could very well be other user
        $ea1->assoc_target_name = $testusername;
        $ea1->event_id = $e->event_id;
        $ea1->save();
        // network EventAssocoiation
        // find a network the user is member of
        // $networks = Network::get_user_networks($user->user_id);
        $network = Network::get_mothership_info();
        // use the mothership
        // print_r($network);
        $ea2->assoc_target_type = 'network';
        $ea2->assoc_target_id = $network->network_id;
        // could very well be other user
        $ea2->assoc_target_name = $network->name;
        $ea2->event_id = $e->event_id;
        $ea2->save();
        echo "Testing EventAssociations for Event $e->event_id\n";
        $assoc_ids = EventAssociation::find_for_event($e->event_id);
        // print_r($assoc_ids);
        $a_cnt = count($assoc_ids);
        $this->assertEquals($a_cnt, 2, "expected 2 assocs, got $a_cnt\n");
        echo "Testing EventAssociations::find_for_target_and_delta for Network\n";
        $assoc_ids = EventAssociation::find_for_target_and_delta('network', $network->network_id);
        // find_for_target_and_delta($target_type, $target_id, $range_start = NULL, $range_end = NULL)
        // print_r($assoc_ids);
        $a_cnt = count($assoc_ids);
        // we expect at least one (or more, the user might have others too)
        $this->assertTrue(($a_cnt >= 1), "expected 1 or more assocs, got $a_cnt\n");
        echo "Testing EventAssociations::find_for_target_and_delta for Today\n";

        /*
        echo "yesterday = " . date(DATE_ATOM, $yesterday) . "\n";
        echo "today = " . date(DATE_ATOM, $today) . "\n";
        echo "event start_time = " . date(DATE_ATOM, strtotime($e2->start_time)) . "\n";
        echo "event end_time = " . date(DATE_ATOM, strtotime($e2->end_time)) . "\n";
        echo "tomorrow = " . date(DATE_ATOM, $tomorrow) . "\n";
        */
        $assoc_ids = EventAssociation::find_for_target_and_delta('network', $network->network_id, date(DATE_ATOM, $today), date(DATE_ATOM, $tomorrow));
        print_r($assoc_ids);

        /* 
        $assocs = EventAssociation::load_in_list($assoc_ids);
        print_r($assocs);
        */
        $a_cnt = count($assoc_ids);
        // we expect at least one (or more, the user might have others too)
        $this->assertTrue(($a_cnt >= 1), "expected 1 or more assocs, got $a_cnt\n");
        echo "Testing if the EventAssociations now show up in Tomorrow's Calendar\n";
        $assoc_ids = EventAssociation::find_for_target_and_delta('network', $network->network_id, date(DATE_ATOM, $tomorrow), date(DATE_ATOM, $tomorrow+$oneday));
        print_r($assoc_ids);
        $a_cnt2 = count($assoc_ids);
        // we expect one less than before
        $this->assertTrue(($a_cnt2 < $a_cnt), "expected ".$a_cnt-1." assocs, got $a_cnt2\n");
        echo "Modifying original Event\n";
        $e2->title = "changed title";
        $e2->end_time = date(DATE_ATOM, $nextmonth);
        $e2->save();
        // see if we got it
        $e3 = new Event();
        $e3->load($e->event_id);
        echo "Testing integrity of dates again in the Event\n";
        // see if our dates survived the DB conversion roundtrip
        $this->assertEquals($now, strtotime($e3->start_time));
        $this->assertEquals(date(DATE_ATOM, strtotime($e3->end_time)), date(DATE_ATOM, $nextmonth));
        echo "Testing if modified dates made it to the EventAssociations\n";
        $assoc_ids = EventAssociation::find_for_event($e3->event_id);
        $assocs = EventAssociation::load_in_list($assoc_ids);
        echo "e3 end_time: ".date(DATE_ATOM, strtotime($assocs[0]->end_time))."\nnextmonth: ".date(DATE_ATOM, $nextmonth)."\n";
        $this->assertEquals(date(DATE_ATOM, strtotime($assocs[0]->end_time)), date(DATE_ATOM, $nextmonth));
        echo "Testing if the EventAssociations now show up in Tomorrow's Calendar\n";
        echo "test range: ".date(DATE_ATOM, $tomorrow)." - ".date(DATE_ATOM, $tomorrow+$oneday)."\n";
        echo "event duration: ".date(DATE_ATOM, strtotime($e3->start_time))." - ".date(DATE_ATOM, strtotime($e3->end_time))."\n";
        // print_r($e3);
        $assoc_ids = EventAssociation::find_for_target_and_delta('network', $network->network_id, date(DATE_ATOM, $tomorrow), date(DATE_ATOM, $tomorrow+$oneday));
        print_r($assoc_ids);
        $a_cnt = count($assoc_ids);
        // we expect at least one (or more, the user might have others too)
        $this->assertTrue(($a_cnt >= 1), "expected 1 or more assocs, got $a_cnt\n");
        echo "Deleting Event $e->event_id\n";
        Event::delete($e->event_id);
        // try loading
        $this->assertNull($e3->load($e->event_id));
        echo "Testing if all EventAssociations have been removed\n";
        $assoc_ids = EventAssociation::find_for_event($e->event_id);
        $a_cnt = count($assoc_ids);
        $this->assertEquals($a_cnt, 0, "expected 0 assocs, got $a_cnt\n");
    }

    public function testStaticCalendarFunctions() {
        $today     = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $tomorrow  = mktime(0, 0, 0, date("m"), date("d")+1, date("Y"));
        $yesterday = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
        $lastmonth = mktime(0, 0, 0, date("m")-1, date("d"), date("Y"));
        $nextmonth = mktime(0, 0, 0, date("m")+1, date("d"), date("Y"));
        echo "Testing static Calendar Functions\n";
        echo "Calendar::range()\n";
        print_r(Calendar::range());
        echo "Calendar::range($today, 'month')\n";
        print_r(Calendar::range($today, 'month'));
        echo "Calendar::range(date(DATE_ATOM, \$today), 'month')\n";
        print_r(Calendar::range(date(DATE_ATOM, $today), 'month'));
        echo "Calendar::range($today, 'day')\n";
        print_r(Calendar::range($today, 'day'));
        echo "Calendar::range($today, 'year')\n";
        print_r(Calendar::range($today, 'year'));
    }
}
?>