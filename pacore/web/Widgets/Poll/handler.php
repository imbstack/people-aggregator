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

include_once(dirname(__FILE__)."/../../../config.inc");
include_once("api/Poll/Poll.php");

//spliting the path_info to get the id and the channel id.
$param = preg_split("|/|", $path_info);
for($i = 2;$i<count($param);$i++) {
  list($k, $v) = explode('=', $param[$i]);
  $url_param[$k] = $v;
}
//loading poll based on the poll id.
$poll_data = array();
if (is_numeric($url_param['id'])) {
  $poll = new Poll();
  $poll_data = $poll->load_poll($url_param['id']);
}
if (!empty($poll_data)) {
  $flag=0;
  $user_info = $poll->load_vote($poll_data[0]->poll_id, PA::$login_uid);
  $total = count($user_info);
  $total_vote = $poll->load_vote($poll_data[0]->poll_id);
  $options = unserialize($poll_data[0]->options);
  $num_option = count($options);
  $cnt = count($total_vote);
  if ($cnt > 0) {
    for ($i=0;$i<$cnt;$i++) {
      if($total_vote[$i]->user_id == PA::$login_uid ) {
        $flag =1;
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
  //$total_vote = count($total_vote);
  $per_option = array();
  if (!empty($vote)) {
    for ($i=0;$i<count($vote);$i++){
      $per_option[] = round(($vote[$i][2]->counter/count($total_vote))*100, 1); 
    }
  }
  $template_file = 'web/Widgets/'.$widget_name.'/widget.tpl';
  $template = new Template($template_file);
  $template->set('url_param', $url_param);
  $template->set('login_uid', PA::$login_uid);
  $template->set('flag',$flag);
  $template->set('percentage',$per_option);
  $template->set('total_vote',$total_vote);
  $template->set('options',$options);
  $template->set('topic',$poll_data);
  $html .= $template->fetch();
} else {
  $html .= 'No such poll';
}
header("Content-Type: application/x-javascript");
echo "document.getElementById('pa_widget_poll').innerHTML = ".js_quote($html).";";

?>