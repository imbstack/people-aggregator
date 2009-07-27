<?php 
 
// the containing page is expected to have set
// $may_edit, $assoc_id, $assoc_type, $assoc_title

require_once("api/Event/Event.php");
require_once("api/Event/EventAssociation.php");
require_once("api/Event/Calendar.php");
require_once("ext/Group/Group.php");
require_once("web/includes/classes/file_uploader.php");

require_once(dirname(__FILE__) . "/RenderCalendar.php");

$date_ts = time();

function zp($i) {
  return str_pad($i,2,'0',STR_PAD_LEFT);
}

global $msg;


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

$months = array();
for ($m=1;$m<=12;$m++) {
  $months[$m] = date("M", mktime(0, 0, 0, $m, 1));
}
$tday = date("d", $date_ts);
$tmonth = date("m", $date_ts);
$tyear = date("Y", $date_ts);
$thour = date("G", $date_ts);
$tmin = date("i", $date_ts);

// prefill the event data array
$virgin_event = $ed = array(
  'start_day' => $tday,
  'start_month' => $tmonth,
  'start_year' => $tyear,
  'start_hour' => $thour,
  'start_min' => $tmin,
  'end_day' => $tday,
  'end_month' => $tmonth,
  'end_year' => $tyear,
  'end_hour' => $thour,
  'end_min' => $tmin,
);

$is_edit = $is_display = false;
if (isset($_REQUEST['edit_event'])) {
  $is_edit = true;
  $eid = (int)$_REQUEST['edit_event'];
} else if (isset($_GET['display_event'])) {
  $is_display = true;
  $eid = (int)$_REQUEST['display_event'];
} else if (!empty($_GET['cid'])) {
  // handle edit links from permalink oages
  $eid = Event::get_eid_from_cid((int)$_GET['cid']);
} else {
  $eid = NULL;
}

$may_edit_calendar = $may_edit;
// are we displaying or editing an existing Event?
if (!empty($eid)) {
  // see if User may edit
  $euid = Event::owner($eid); // this also verifies if the event exists
  if ($euid) { // Event exists
    $ev = new Event();
    $ev->load_by_event_id($eid);
    $ed = put_event_data($ev);
    $date_ts = $ed['start_ts'];
  } else {
  	$eid = NULL;
  $is_display = false;  	
  }
  if ($euid == PA::$login_user->user_id) {
    $is_edit = true;
    $may_edit = true;
  } else {
    $may_edit = false;
    $is_edit = false;
  }
}

if (isset($_POST['create'])) {
  // first check if uer MAY create anything here
  $may_create = true;
  if ($assoc_type == 'group') {
    $is_member = Group::get_user_type((int)PA::$login_user->user_id, (int)$assoc_id);
    if ($is_member == NOT_A_MEMBER) {
      $msg = sprintf(__("You cannot add an Event to the calendar of group %s, as you are not a member."), $assoc_title);
      $may_create = false;

    }
  }
  
  if ($may_create) {
    $ed = get_event_data();
    // create the event
    $ev = new Event();
    $ev->user_id = PA::$login_user->user_id;
    $ev->event_data['description'] = $ed['event_description'];
    $ev->event_data['venue'] = $ed['event_venue'];
    foreach ($ed as $k=>$v) {
      if ($v) { $ev->{$k} = $v; }
    }
    
    // if this is for a group
    if ($assoc_type=='group') {
      // post the content to the group blog
      $ev->parent_collection_id = $assoc_id;
    }
  
    // handle uploaded inages
    try {
      if($img = image_uploaded()) $ev->event_data['banner'] = $img;
      $ev->save();
      $_POST = array();
      $ed = put_event_data($ev); // make sure the changes display
    } catch (PAException $err) {
      $msg .= __("Couldn't create event:")."<br />" . $err->getMessage();
    }
    if ($ev->event_id) {
      try {
        // also create EventAssociation to creating User
        $ea = new EventAssociation();
        $ea->event_id = $ev->event_id;
        $ea->user_id = PA::$login_user->user_id;
  
        $ea->assoc_target_type = $assoc_type;
        $ea->assoc_target_id = $assoc_id;
        $ea->assoc_target_name = $assoc_title;
  
        $ea->save();
      } catch (PAException $err) {
        $msg .= __("Couldn't create EventAssociation:")."<br />" . $err->getMessage();
      }
    }
    if ($ev->event_id) {
      $ed['event_id'] = $ev->event_id;
      $is_edit = false;
      $is_display = true;
    }
  }
}

if (isset($_POST['update'])) {
  $ed = get_event_data();
  $ev = new Event();
  $ev->load_by_event_id((int)$ed['event_id']);
  if (empty($ev->event_id)) {
    $msg = sprintf(__("There was a problem updating the event:")."<br />".__("Event %d doesn't exist"), (int)$ed['event_id']);
  } else {
    $ev->event_data['description'] = $ed['event_description'];
    $ev->event_data['venue'] = $ed['event_venue'];
    foreach ($ed as $k=>$v) {
      if ($v) { $ev->{$k} = $v; }
    }
    // handle uploaded inages
    try {
      if($img = image_uploaded()) $ev->event_data['banner'] = $img;
      $ev->save();
      $_POST = array();
      $ed = put_event_data($ev); // make sure the changes display
    } catch (PAException $err) {
      $msg .= __("Couldn't update the event:")."<br />" . $err->getMessage();
    }
    $is_edit = false;
    $is_display = true;
  }
}

if (isset($_REQUEST['delete'])) {
	if (empty($_POST['eventid'])) {
		// this came in via the permalink page where we only have cid and GET
		if (!empty($_GET['cid'])) {
			$_POST['event_id'] = Event::get_eid_from_cid((int)$_GET['cid']);
		}
	}
  $post_ed = get_event_data();
// echo "<pre>".print_r($post_ed,1)."</pre>";exit;
  if (! Event::exists($post_ed['event_id'])) {
    $msg = sprintf(__("There was a problem deleting the event:")."<br />".__("Event %d doesn't exist"), (int)$post_ed['event_id']);
  } else {
    try {
      Event::delete_by_id($post_ed['event_id']);
      $msg = __("The event has been deleted.");
      $is_edit = false;
      $is_display = false;
      $_POST = array();
      unset($_GET['display_event']);
      unset($_GET['delete']);
      $ed = $virgin_event;
      $eid = NULL;
      if (!empty($_GET['back_page'])) {
      	header("Location: ".$_GET['back_page']."?msg=$msg");
      }
    } catch (PAException $err) {
      $msg .= __("Couldn't delete the event:")."<br />" . $err->getMessage();
    }
  }  
}

// add_assoc_users
$add_assoc_user_errors = array();//declaring array so as to avoid notices for undefined variables
if (isset($_POST['add_assoc_users'])) {
  $ed = get_event_data();  
  if (empty($_POST['assoc_users'])) {
    $add_assoc_user_errors[] = __("No users given");
  } else {
    // split the passed value into login_names
    $login_names = preg_split("/\s*,\s*/", $_POST['assoc_users']);
    // see if each is a valid login_nmae and remember errors
    foreach ($login_names as $n=>$name) {
      $add_uid = (int)User::user_exist($name);
      if ($add_uid) {
        // try to add EventAssociation for this user_id
        try {
          $ea = new EventAssociation();
          $ea->event_id = $ed['event_id'];
          $ea->user_id = PA::$login_user->user_id;
          $ea->assoc_target_type = 'user';
          $ea->assoc_target_id = $add_uid;
          $ea->assoc_target_name = $name;
          $ea->save();
        } catch (PAException $err) {
          $add_assoc_user_errors[] = 
            __("There was a problem associating the event:")."<br />" . $err->getMessage();
        }
      } else {
        $add_assoc_user_errors[] = sprintf(__("%s is not a user"), $name);
      }
    }
  }
  if (count($add_assoc_user_errors)) {
    $msg .= __("Add people:");
    foreach ($add_assoc_user_errors as $n=>$err) {
      $msg .= "<br />$err";
    }
    $is_display = false;
    $is_edit = true;
  }
  // add eventAssociations for valid login_names
}

// add_assoc_group
$add_assoc_group_errors = array(); // declaring array so as to avoid notices for undefined variables
if (isset($_POST['add_assoc_groups'])) {
  $ed = get_event_data();
  $add_groups = $_POST['add_groups'];
  foreach ($add_groups as $i=>$add_gid) {
    $add_group = Group::load_group_by_id($add_gid);
    $add_gname = $add_group->title;
    // check if user MAY add
    $is_member = Group::get_user_type((int)PA::$login_user->user_id, (int)$add_gid);
    if ($is_member == NOT_A_MEMBER) {
      $add_assoc_group_errors[] = 
        sprintf(__("You cannot add an Event to the caledar of group %s, as you are not a member."), $add_gname);
    } else {
      // try to add EventAssociation for this group_id
      try {
        $ea = new EventAssociation();
        $ea->event_id = $ed['event_id'];
        $ea->user_id = PA::$login_user->user_id;
        $ea->assoc_target_type = 'group';
        $ea->assoc_target_id = $add_gid;
        $ea->assoc_target_name = $add_gname;
        $ea->save();
      } catch (PAException $err) {
        $add_assoc_group_errors[] = 
          __("There was a problem associating the event:")."<br />" . $err->getMessage();
      }
    }
  }
  if (count($add_assoc_group_errors)) {
    $msg .= __("Add group:");
    foreach ($add_assoc_group_errors as $n=>$err) {
      $msg .= "<br />$err";
    }
    $is_display = false;
    $is_edit = true;
  }
}

// see if we have a remove request for an association
foreach ($_POST as $k=>$v) {
  if (preg_match('/^remove_assoc_(\d+)/', $k, $m)) {
    $rem_assoc = (int)$m[1];
    $assoc = new EventAssociation();
    try {
      $assoc->load($rem_assoc);
    } catch (PAException $e) {
      $msg = __("You may not remove the association to this event.")."<br/>" . $e->getMessage(); 
    }
    if ($assoc->user_id) {
      // is this user OWNER and TARGET?      
      if (
        ($assoc->user_id == PA::$login_user->user_id) 
        // user is OWNER of this assoc
        &&
        ($assoc->assoc_target_type == 'user'
        && $assoc->assoc_target_id == PA::$login_user->user_id)
        // user is TARGET of this assoc
        ) {
          $msg = __("You cannot remove yourself from this event, as you created it.  Please use 'Delete Event' instead.");
        }
      // see if current user may modify/delete this one
      else if (
        ($assoc->user_id == PA::$login_user->user_id) 
        // user is OWNER of this assoc
        ||
        ($assoc->assoc_target_type == 'user'
        && $assoc->assoc_target_id == PA::$login_user->user_id)
        // user is TARGET of this assoc
        ) {
        EventAssociation::delete($rem_assoc);
      } else {
        $msg = __("You may not remove the association to this event.");
      }
    }
  }
}

function get_event_data() {
  // get and prepare Event data from POST
  $ed = array();
  $ed['event_id'] = @$_POST['event_id']; 
  $ed['event_title'] = @$_POST['event_title']; 
  $ed['event_venue'] = @$_POST['event_venue']; 
  $ed['event_description'] = @$_POST['event_description']; 

  $ed['start_hour'] = @$_POST['start_hour'];
  $ed['start_min'] = @$_POST['start_min'];
  $ed['start_month'] = @$_POST['start_month'];
  $ed['start_day'] = @$_POST['start_day'];
  $ed['start_year'] = @$_POST['start_year'];

  $ed['end_hour'] = @$_POST['end_hour'];
  $ed['end_min'] = @$_POST['end_min'];
  $ed['end_month'] = @$_POST['end_month'];
  $ed['end_day'] = @$_POST['end_day'];
  $ed['end_year'] = @$_POST['end_year'];

  $ed['start_time'] =
    date(DATE_ATOM,
    mktime(
      @$_POST['start_hour'],
      @$_POST['start_min'],
      0,
      @$_POST['start_month'],
      @$_POST['start_day'],
      @$_POST['start_year']
    ));
  $ed['end_time'] =
    date(DATE_ATOM,
    mktime(
      @$_POST['end_hour'],
      @$_POST['end_min'],
      0,
      @$_POST['end_month'],
      @$_POST['end_day'],
      @$_POST['end_year']
    ));
  return $ed;
}

function put_event_data($ev) {
  // get Event data from Event Object
  $ed = array();
  $ed['event_id'] = $ev->event_id; 
  $ed['event_title'] = $ev->event_title; 
  $ed['event_description'] = $ev->event_data['description']; 
  $ed['event_venue'] = $ev->event_data['venue']; 
  $ed['banner'] = @$ev->event_data['banner']; 

  $ed['start_time'] = $ev->start_time;
  $ed['end_time'] = $ev->end_time;

  $ed['start_ts'] = $start_ts = strtotime($ev->start_time);
  $ed['end_ts'] = $end_ts = strtotime($ev->end_time);
  

  $ed['start_hour'] = date("G", $start_ts);
  $ed['start_min'] = date("i", $start_ts);
  $ed['start_month'] = date("m", $start_ts);
  $ed['start_day'] = date("d", $start_ts);
  $ed['start_year'] = date("Y", $start_ts);

  $ed['end_hour'] = date("G", $end_ts);
  $ed['end_min'] = date("i", $end_ts);
  $ed['end_month'] = date("m", $end_ts);
  $ed['end_day'] = date("d", $end_ts);
  $ed['end_year'] = date("Y", $end_ts);

  return $ed;
}

function image_uploaded() {
  if (empty($_FILES['userfile']['name'])) {
    return false;
  } else {
    $uploadfile = PA::$upload_path.basename($_FILES['userfile']['name']);
    $myUploadobj = new FileUploader; // creating instance of file.
    $image_type = 'image';
    $file = $myUploadobj->upload_file(PA::$upload_path, 'userfile', true, true, $image_type);
    if( $file == false) {
      throw new PAException(INVALID_ID, "Error uploading image " 
      . $myUploadobj->error);
    } else {
      Storage::link($file, 
        array(
          "role" => "event_banner", 
          "user" => PA::$login_user->user_id,
        ));
      return $file;
    }
  }
}

?>
<div class="editmainpage">
<h1><? echo $title; ?> for <? echo $assoc_title; ?></h1>
  <form enctype="multipart/form-data" action="" method="post">
    <fieldset style="margin: 5px 30px 20px 30px; width: auto;">
    <div class="month-calendar">
    <?php 
      // find all EventAssociations for this assoc_id/type
      $range = Calendar::range($date_ts, 'month');

      $assoc_ids = 
        EventAssociation::find_for_target_and_delta($assoc_type, $assoc_id,
        $range['start'], $range['end']
        );
      $assocs = EventAssociation::load_in_list($assoc_ids);
      
      $cal = new RenderCalendar();
      
      // clean up the _GET a little
      unset($_GET['date']);
      unset($_GET['display_event']);
      unset($_GET['cid']);
      switch (@$_GET['dmode']) {
        case 'calendar':
        case 'list':
          $cal->display_mode = $_GET['dmode']; 
        break;
        default:
          $cal->display_mode = 'calendar';
      }
      $cal->calendar_url = PA::$url . "/$assoc_type" . "_calendar.php";
      $cal->calendar_url .= "?" . http_build_query($_GET);
      $cal->self_url = $cal->calendar_url; // identical in this case

      echo $cal->month(
        $range['start_ts'],
        $assocs,
        $may_edit_calendar
        ); 
    ?>
    </div>
    <div id="eventui">
    <? if (@$ed['event_id']) { ?>
    <input type="hidden" name="event_id" value="<?echo $ed['event_id']; ?>" />
    <? } ?>
    <?php 
    if ($is_display) {
      include(dirname(__FILE__) . "/display_event.php");
    } else {
        if ($assoc_type != 'group') {
          include(dirname(__FILE__) . "/edit_event.php");
        } else {
          $is_member = Group::get_user_type((int)PA::$login_user->user_id, (int)$assoc_id);
          if ($is_member != NOT_A_MEMBER) include(dirname(__FILE__) . "/edit_event.php");
      }
    }
    ?>
     <div class="editassocs">
    <?
    include(dirname(__FILE__) . "/edit_eventassocs.php"); 
    ?>
     </div>
    <?
  ?>
  <!--
    <h2>List of all Events for <?=$assoc_title?></h2>
    <ul class="event-associations">
    <?php
    // find all EventAssociations for this user
    $assoc_ids = EventAssociation::find_for_target_and_delta($assoc_type, $assoc_id);
    $assocs = EventAssociation::load_in_list($assoc_ids);
    // print_r($assocs);
    foreach ($assocs as $n=>$assoc) {
      // echo "<li>" . print_r($assoc, true) . "</li>\n";
      echo "<li><b>";
      echo "<a href=\"?display_event=" . $assoc->event_id . "\">";
      echo $assoc->event_title;
      echo "</a></b> on ";
      echo $assoc->start_time;
      echo "</li>\n";
    }
    ?>
    </ul>
    -->

    </div>
    
    <div style="width:auto; clear:both;"></div>
    </fieldset>
  </form>
</div>
