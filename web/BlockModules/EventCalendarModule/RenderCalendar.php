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
require_once "api/Event/Event.php";
require_once "api/Event/EventAssociation.php";
require_once "api/Event/Calendar.php";

/*
* The RenderCalendar class
* this class is used to build HTML Calendars
* Author: Martin
*/

class RenderCalendar {
  public $first_day_of_week = 1; 
  // 0 = week starts on Sunday
  // 1 = week starts on Monday
  public $week_days;
  
  public $calendar_url;
  public $self_url;
  
  public $display_mode; // calendar|list
  
  public function __construct() {
    // populate $week_days
    $ts = time();
    for ($i=0;$i<7;$i++) {
      $wday = date("w", $ts);
      $wday_name = date("D", $ts);
      $this->week_days[$wday] = $wday_name;
      $ts += 60*60*24;
    }
  }

  // create HTML for a monthview
  // centered on the date given
  public function month($date, $event_assocs=NULL, $may_edit = false) {
    $calendar_url = $this->calendar_url;
    $self_url = $this->self_url;
    $range = Calendar::range($date, 'month');
    $days = Calendar::days($range, $event_assocs);

    $today_ts = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
    $thism_ts = $days[0]['date_ts'];
    // find on which day this month starts
    $first_week_day = date('w', $thism_ts);
    
    // header with Month and Year
    $html = "<ul class=\""
    	. $this->display_mode // calendar|list
    	. "\">\n"; 

    $html .= "<li class=\"calendarcell header\">\n";
    $prevm_ts = mktime(0, 0, 0, date("m", $thism_ts)-1, 1, date("Y", $thism_ts));
    $nextm_ts = mktime(0, 0, 0, date("m", $thism_ts)+1, 1, date("Y", $thism_ts));
    
    $html .= "<a class=\"month-prev\" href=\"$self_url&date="
      . date("Y-m-d", $prevm_ts) . "#calendar\" title=\"" 
      . date("F Y", $prevm_ts) . "\">&lt;</a>";
    $html .= "<a name=\"calendar\" class=\"month-next\" href=\"$self_url&date="
      . date("Y-m-d", $nextm_ts) . "#calendar\" title=\"" 
      . date("F Y", $nextm_ts) . "\">&gt;</a>";
    $html .= date("F Y", $thism_ts);
    $html .= "</li>\n";

    // render dayheaders
    $fd = $this->first_day_of_week;
    $ld = $fd+7;
    for ($i=$fd;$i<$ld;$i++) {
      if ($i > 6) { $d = $i - 7; } 
      else { $d = $i; }
      $html .= "<li class=\"calendarcell dayheader\">\n";
      $html .= $this->week_days[$d];
      $html .= "</li>\n";
    }
    
    $grid_count = 0;
    // render empty days at start of month if required
    if ($first_week_day < $fd) {
      $ed = 7 - $first_week_day;
    } else {
      $ed = $first_week_day;
    }
    for ($i=$fd;$i<$ed;$i++) {
      $html .= "<li class=\"calendarcell\">&nbsp;</li>\n";
      $grid_count++;
    }
    // render actual days
    $cnt = count($days);
    $last_week_day = 0;
    for ($i=0;$i<$cnt;$i++) {
      $events = $days[$i]['events'];
      $event_cnt = count($events);
      $html .= "<li class=\"calendarcell";
      // mark today as special
      if ($days[$i]['date_ts'] == $today_ts) {
        $html .= " today";
      }
      if ($event_cnt) {
        $html .= " hasevents";
      } else {
        $html .= " noevents";
      }
      $html .= "\">\n";
      $html .= "<span class=\"daynumber\">";
      $html .= date("d", $days[$i]['date_ts']);
      $html .= "</span>";
      
      $html .= "<div class=\"hovertip\">";
        // . "$event_cnt Events for "
        // . date("D jS", $days[$i]['date_ts'])

      if ($event_cnt) {
        $html .= "<ul class=\"eventlist\">\n";
        foreach ($events as $n=>$assoc) {
          $html .= "<li>";
          if ($may_edit) {
            $html .= "<a href=\"$calendar_url&display_event=" . $assoc->event_id . "\">";
          }
          $html .= "<b>" . _out($assoc->event_title);
          $html .= "</b><br /> on ";
          $html .= PA::date($assoc->start_time);
          if ($may_edit) {
            $html .= "</a>";
          }
          $html .= "</li>\n";
        }
        $html .= "</ul>\n";
      } 
      if ($may_edit) {
        // create link
        $html .= "<a class=\"create-event\" href=\"$calendar_url&date="
          . date("Y-m-d", $days[$i]['date_ts']) . "\">New</a>";
      }
      $html .= "</div>";
      $html .= "</li>\n";
      $grid_count++;
    }
    
    // render empty days at end of month if required
    for ($i=$grid_count;$i<42;$i++) {
      $html .= "<li class=\"calendarcell\">&nbsp;</li>\n";
    }
    // add just the events for the list view
		foreach ($event_assocs as $i=>$assoc) {
			$html .= '<li class="listview calendarcell">';
          $cid = Event::get_cid_from_eid((int)$assoc->event_id);
          $html .= "<a href=\"" . PA::$url.PA_ROUTE_PERMALINK 
          	. "&cid=" . $cid . "\">";
          $html .= "<b>" . _out($assoc->event_title);
          $html .= "</b></a><br /> starts ";
          $html .= PA::datetime($assoc->start_time);
          $html .= "<br /> ends ";
          $html .= PA::datetime($assoc->end_time);
          if ($may_edit) {
            $html .= " <a href=\"$calendar_url&display_event=" . $assoc->event_id . "\">";
            $html .= __('Edit');
            $html .= "</a>";
          }
			$html .= '</li>';

		}
    

    $html .= "<li class=\"calendarcell header\">";
    if ($may_edit) {
      // edit link
      $html .= "<b><a class=\"goto-edit\" href=\"$calendar_url\">" . __("Edit") . "</a></b>&nbsp;&nbsp;&nbsp;";
    }

    $html .= __("Display as") . ":\n";
    $html .= "<a href=# class=\"list-style\">".__("List")."</a>";
    $html .= "<a href=# class=\"calendar-style\">".__("Calendar")."</a>";
    $html .= "</li>\n";
        
    $html .= "</ul>\n";
    return $html;
  }
}

?>
