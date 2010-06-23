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
  * This file has all the common code to peoples.php and buddylist.php
  */
  
  $viewer_uid = 0;
  if (!empty($login_uid)) {
    $viewer_uid = $login_uid;
  }
  
  $search_vars = array('first_name', 'last_name');
  $advance_search_options = array('sex', 'city', 'state', 'company', 'user_tags', 'industry', 'age');
  $show_advance_search_options = false;//flag will be set when used has serached for any of $advance_search_options
  $search_vars = array_merge($search_vars, $advance_search_options);
  $search_data_array = array();
  
  if (isset($_GET['submit_search'])) {
    $total_search_vars = count($search_vars);
    for ($counter = 0; $counter < $total_search_vars; $counter++) {
      $var = $search_vars[$counter];      
      if (!empty($_GET[$var])) {
        if(in_array($var, $advance_search_options)) {
          $show_advance_search_options = true;
        }
        
        if ($var == 'age') {
          //check for valid date age range
          $age_range = array();
          $age_range = explode("-", $_GET[$var]);
          $age_range_count = count($age_range);
          switch($age_range_count) {
            case 1:
              //for more than 50 years
              $search_data_array['dob']['value'] = array('lower_limit'=>$age_range[0], 'upper_limit'=>150);
              //giving upper limit as 150 years
              $search_data_array['dob']['type'] = AGE_SEARCH;
            break;
            case 2:              
              $search_data_array['dob']['value'] = array('lower_limit'=>$age_range[0], 'upper_limit'=>$age_range[1]);
              $search_data_array['dob']['type'] = AGE_SEARCH;
            break;
            default:
          }          
        } else {
          $search_data_array[$var]['value'] = $_GET[$var];
          $search_data_array[$var]['type'] = LIKE_SEARCH;
        }
      }
    }
  }
  
  if (!empty($_GET['rows']) && is_numeric($_GET['rows']) && $_GET['rows'] > 0) {
    $rows = $_GET['rows'];
  } else {
    $rows = FACEWALL_ROW_COUNT;
  }

?>