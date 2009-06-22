<div class="month-calendar">

<?php
global  $current_theme_path;
// the containing page has passed
// $may_edit, $assoc_id and $assoc_type

require_once("api/Event/Event.php");
require_once("api/Event/EventAssociation.php");
require_once("api/Event/Calendar.php");
require_once("ext/Group/Group.php");
require_once(dirname(__FILE__) . "/../EventCalendarModule/RenderCalendar.php");

$date_ts = time();

// handle month navigation
if (!empty($_REQUEST['date'])) {
  // try convertin to a timestamp
  $ts = strtotime(trim($_REQUEST['date']));
  // check if this is sane date
  if (date(DATE_ATOM, $ts) != "1970-01-01T01:00:00+01:00") {
    // 1970-01-01T01:00:00+01:00 is what is returned if the $ts is a non-date
    // anything not convertable to a date is treated like 0
    $date_ts = $ts;
  }
} 

function zp($i) {
  return str_pad($i,2,'0',STR_PAD_LEFT);
}
$months = array();
for ($m=1;$m<=12;$m++) {
  $months[$m] = date("M", mktime(0, 0, 0, $m));
}
$tday = date("d", $date_ts);
$tmonth = date("m", $date_ts);
$tyear = date("Y", $date_ts);
$thour = date("G", $date_ts);
$tmin = date("i", $date_ts);


      // find all EventAssociations for this user
      $range = Calendar::range($date_ts, 'month');

      $assoc_ids = 
        EventAssociation::find_for_target_and_delta($assoc_type, $assoc_id,
        $range['start'], $range['end']
        );
      $assocs = EventAssociation::load_in_list($assoc_ids);
      $cal = new RenderCalendar();
      $cal->calendar_url = PA::$url . "/$assoc_type" . "_calendar.php";
      $cal->calendar_url .= "?" . http_build_query($_GET);
      $cal->self_url = $_SERVER['SCRIPT_NAME']; // PA::$url . "/$assoc_type" . ".php";
      $cal->self_url .= "?" . http_build_query($_GET);
      
      

      switch (@$_GET['dmode']) {
        case 'calendar':
        case 'list':
          $cal->display_mode = $_GET['dmode']; 
        break;
        default:
          $cal->display_mode = (!empty($mod->display_mode)) ? $mod->display_mode : 'calendar';
      }

      echo $cal->month(
        $range['start_ts'],
        $assocs,
        $may_edit
        ); 


?>
</div>