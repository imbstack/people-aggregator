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
/**
 * gives the date format for message body
 * @param int timestamp.
 */
function message_body_date($timestamp) {
    return date("j M Y  H:i:s", $timestamp);
}

/**
 * gives the date format for message folder
 * @param int timestamp.
 */
function message_folder_date($timestamp) {
    return date("D d/m", $timestamp);
}

/**
 * gives the date format for contents.
 * @param int timestamp.
 */
function content_date($timestamp) {
    //  return date("F d, Y h:i A", $timestamp);
    return PA::datetime($timestamp, 'long', 'short');
}

/**
 * gives the date format for invitations.
 * @param int timestamp.
 */
function invitation_date($timestamp) {
    //  return date("F d, Y H:i:s", $timestamp);
    return PA::datetime($timestamp, 'long', 'short');
}

/**
 * gives the date format for invitations.
 * @param int timestamp. ex: 1 jan
 */
function user_date($timestamp) {
    return date("j M", $timestamp);
}

/**
 * gives the date format for manage contents.
 * @param int timestamp.
 */
function manage_content_date($timestamp) {
    return date("M d, Y", $timestamp);
}

/**
 * gives the date format for last login.
 * @param int timestamp.
 */
function last_login_date($timestamp) {
    return date("M d, Y", $timestamp);
}

/*
* Function to form the Month Select Box
*/
function month_options($month_selected = NULL) {
    $monthString = "";
    if(empty($month_selected) && !is_int($month_selected)) {
        $month_selected = "";
    }
    for($month = 2; $month <= 13; $month++) {
        if(($month-1) == $month_selected) {
            $selected = 'selected="selected"';
        }
        else {
            $selected = "";
        }
        $monthString .= "<option value='".($month-1)."' $selected>".date("F", mktime(0, 0, 0, $month, 0, 0))."</option>";
    }
    return $monthString;
}

/*
* Function to form the Date Select Box
*/
function date_options($date_selected = NULL) {
    $dateString = "";
    if(empty($date_selected) && !is_int($date_selected)) {
        $date_selected = "";
    }
    for($day = 1; $day <= 31; $day++) {
        if($day == $date_selected) {
            $selected = 'selected="selected"';
        }
        else {
            $selected = "";
        }
        $dateString .= "<option value='".$day."' ".$selected.">".$day."</option>";
    }
    return $dateString;
}

/*
* Function to form the Year Select Box
*/
function year_options($year_selected = NULL) {
    $yearString = "";
    if(empty($year_selected) && !is_int($year_selected)) {
        $year_selected = "";
    }
    for($year = 2006; $year <= date("Y")+20; $year++) {
        if($year == $year_selected) {
            $selected = 'selected="selected"';
        }
        else {
            $selected = "";
        }
        $yearString .= "<option value='".$year."' ".$selected.">".$year."</option>";
    }
    return $yearString;
}
?>
