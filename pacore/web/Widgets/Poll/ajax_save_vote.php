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
$login_required = FALSE;
include_once "../../includes/page.php";
require_once "api/Poll/Poll.php";

if (!empty($_POST['vote'])) {
  $vote = $_POST['vote'];
  $vote = html_entity_decode(stripslashes($vote));
  $poll_id = $_POST['poll_id'];
  $obj = new Poll();
  $obj->poll_id = $poll_id;
  $obj->vote = $vote;
  $obj->user_id = $_POST['uid'];
  $obj->is_active = 1;
  $obj->save_vote();
  $uid = $_POST['uid'];
  $poll_data = array();
  $poll = new Poll();
  $poll_data = $poll->load_poll($poll_id['id']);
 if (!empty($poll_data)) {
  $flag=0;
  $user_info = $poll->load_vote($poll_data[0]->poll_id, $uid);
  $total = count($user_info);
  $total_vote = $poll->load_vote($poll_data[0]->poll_id);
  $options = unserialize($poll_data[0]->options);
  $num_option = count($options);
  $cnt = count($total_vote);
  if ($cnt > 0) {
    for ($i=0;$i<$cnt;$i++) {
      if($total_vote[$i]->user_id == $uid) {
        $flag =1;
        $vote = array();
        for ($j=1; $j<=$num_option;$j++) {
          if ($options['option'.$j]!='') {
            $vote[] = $poll->load_vote_option                                                           ($poll_data[0]->poll_id,$options['option'.$j]);
          }
        }
        break;
      } else { 
        $flag = 0;
      }
    }
  }
  $per_option = array();
  if (!empty($vote)) {
    for ($i=0;$i<count($vote);$i++){
      $per_option[] = round(($vote[$i][2]->counter/count($total_vote))*100, 1); 
    }
  }
  }
  $cnt = count($options);
   echo '<h5>'.$poll_data[0]->title.'</h5>';
  for ($i=1;$i<=$cnt;$i++){
    echo '<div>';
    echo '<span>';
    echo stripslashes($options['option'.$i]);
    echo '</span>';
    $j = $i-1;
    echo "<span class='poll_bar'>" .'<img src="'.PA::$url.'/makebar.php?rating='.$per_option[$j].'&amp;width=95&amp;height=10" border="0" />'."</span>";
    echo "<span class='percent'>". $per_option[$j].'%'."</span>";
    echo "<br/>";
    echo '</div>';  
  }
} else {
 print (__('Error processing request'));
}
 
?>