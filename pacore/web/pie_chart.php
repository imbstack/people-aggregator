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
 * File:        pei_chart.php, web file to display pie chart.
 * Author:      tekritisoftware
 
 */
$login_required = FALSE;
$use_theme      = 'Beta';
$parameter      = '';
include_once("web/includes/page.php");
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/PieChart/PieChart.php";
require_once "api/Poll/Poll.php";
$poll_id      = $_REQUEST['id'];
$obj          = new Poll();
$data         = $obj->load_poll($poll_id);
$total_votes  = count($obj->load_vote($poll_id));
$prev_options = unserialize($data[0]->options);
$num_option   = count($prev_options);
$vote         = array();
$legends      = array();
for($j = 1; $j <= $num_option; $j++) {
    if($prev_options['option'.$j] != '') {
        $vote[] = $obj->load_vote_option($poll_id, $prev_options['option'.$j]);
        $legends[] = $prev_options['option'.$j];
    }
}
$cnt = count($vote);
for($i = 0; $i < $cnt; $i++) {
    if($total_votes != 0) {
        $percentage[] = round(($vote[$i][2]->counter/$total_votes)*100, 1);
    }
}
if(!empty($percentage)) {
    $pie = new PieChart(100, 50, $percentage);
    // colors for the data
    $pie->setColors(array("#e81e37", "#ff8800", "#0022ff", "#CC33FF", "#0000CC", '#FFFF33', '#FF0033', '#7FFF00'));
    // legends for the data
    $pie->setLegends($legends);

    /*
    // Display creation time of the graph
    $pie->DisplayCreationTime();*/
    // Height of the pie 3d effect
    $pie->set3dHeight(15);
    // Display the graph
    $pie->display();
}
?>