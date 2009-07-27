<?php
$login_required = FALSE;
//including necessary files
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require 'api/Rating/Rating.php';
 
$Rating = new Rating();
if (!empty($_POST)) { 
  $Rating->set_rating_type(@$_POST['rating_type']);
  $Rating->set_type_id(@(int)$_POST['type_id']);
  $Rating->set_rating(@(int)$_POST['rating']);
  $Rating->set_max_rating(@(int)$_POST['max_rating']);
  $Rating->set_user_id(@(int)$_POST['login_uid']);
  $Rating->rate();
  $rating = thumbs_rating(@$_POST['rating_type'], @(int)$_POST['type_id']);
  $html = NULL;
  $html .= '<div class="as_recommend"><span class="as_bold">'.$rating['overall'].'</span></div><br>';
  if($_POST['rating'] == 1 ) {
    $return['new'] = __('Your Recommendation: ').'<img src="'.PA::$theme_url . '/images/rec_yes1.png" alt="star" />';
   $html .= '<div class="as_recommendit"><div class="recit_t as_bold">'.$return['new'].'</div></div>';
  }
  else {
    $return['new'] = __('Your Recommendation: ').'<img src="'.PA::$theme_url . '/images/rec_no1.png" alt="star" />';
    $html .= '<div class="as_recommendit"><div class="recit_t as_bold">'.$return['new'].'</div></div>'; 
  }
}
print $html; 
  
?>