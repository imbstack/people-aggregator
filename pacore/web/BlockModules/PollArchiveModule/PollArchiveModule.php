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
require_once "api/Content/Content.php";
require_once "api/Poll/Poll.php";

class PollArchiveModule extends Module {
 
  public $module_type = 'network'; //'user|group|network'; 
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_center_module.tpl';
  public $per_option;
  
  function __construct() {
    parent::__construct();
    $this->title = __('Survey Archive');
  }
  function render() {
    $pollObj = new Poll();
    // $current = $pollObj->load_current();
    $this->prev_polls = $pollObj->load_prev_polls();
    if ($this->prev_polls) {
    	foreach ($this->prev_polls as $poll) {
    		$votes = $pollObj->load_vote($poll->poll_id);
    		$poll->total_votes = count($votes);
    		$options = array();
    		foreach (unserialize($poll->options) as $option=>$s) {
    			$options[$option]['title'] = $s;
    			$option_votes = $pollObj->load_vote_option($poll->poll_id, $s);
// echo "<pre>".print_r($option_votes,1)."</pre>";
          $options[$option]['count'] = $option_votes[2]->counter; 
          $options[$option]['percent'] = round(($option_votes[2]->counter / $poll->total_votes) * 100, 1); 
    		}
    		$poll->options = $options;
    	}
      $this->inner_HTML = $this->generate_inner_html();
      $content = parent::render();
      return $content;
    } else {
      $this->do_skip = TRUE;
      return 'skip';
    }
  }
  
  function generate_inner_html () {
    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/poll_archive.tpl.php';
    $inner_html_gen= & new Template($inner_template);
    $inner_html_gen->set('polls', $this->prev_polls);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>