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
require_once "api/PieChart/PieChart.php";
require_once "api/Poll/Poll.php";
/**
 * This class generates inner html of poll
 * @package BlockModules
 * @subpackage 
 */ 
class ViewPollModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->title = "";
    $this->html_block_id = 'ViewPollModule';
  }
  
  function render() {
    global $login_uid;
   $obj = new Poll();
   $prev_poll = $obj->load_prevous_polls();
   $cnt = count($prev_poll);
   for ($i=0; $i<$cnt;$i++) {
     $total_votes[$prev_poll[$i]->poll_id] = count($obj->load_vote($prev_poll[$i]->poll_id));
     $prev_options[$i] = unserialize($prev_poll[$i]->options);
     $num_option = count($prev_options[$i]);
    for ($j=1; $j<=$num_option;$j++) {
      if ($prev_options[$i]['option'.$j]!='') {
        $vote[] = $obj->load_vote_option($prev_poll[$i]->poll_id , $prev_options[$i]['option'.$j]);
      }
    }
  }
  $percentage = array();
  for ($i=0;$i<count($vote);$i++){
    $j = $vote[$i][0];
    if ($total_votes[$j] != 0) {
      $percentage[$j][] = round(($vote[$i][2]->counter/$total_votes[$j])*100, 1);
      
    }
  }
    $this->current_poll = $obj->load_current();
    $this->per_prev_poll = $percentage;
    $this->prev_poll = $prev_poll;
    $this->prev_options = $prev_options;
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }
  
   function generate_inner_html () {
    
    $inner_template = NULL;
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $inner_html_gen= & new Template($inner_template);
    $inner_html_gen->set('prev_poll',$this->prev_poll);
    $inner_html_gen->set('per_prev_poll',$this->per_prev_poll);
    $inner_html_gen->set('prev_options', $this->prev_options);
    $inner_html_gen->set('current_poll', $this->current_poll);
    $inner_html_gen->set('my_name', 'saurabh');
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>