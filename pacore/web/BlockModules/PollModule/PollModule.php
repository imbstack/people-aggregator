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
/**
 * This class generates inner html of poll
 * @package BlockModules
 * @subpackage 
 */ 

class PollModule extends Module {
 
  public $module_type = 'network'; //'user|group|network'; 
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  public $per_option;
  
  function __construct() {
    parent::__construct();
    $this->title = __('Survey');
  }
  function render() {
    $this->flag = 0 ;
    $obj = new Poll();
    $current = $obj->load_current();
    $prev_poll = $obj->load_prev_polls();
    $this->cnt_prev = count($prev_poll);
    if ($current) {
      $user_vate = $obj->load_vote($current[0]->poll_id, PA::$login_uid);
      $total_vote = $obj->load_vote($current[0]->poll_id);
      $this->total_vote_count = count($total_vote);

      $this->topic = $obj->load_poll($current[0]->poll_id);
      $this->options = unserialize($this->topic[0]->options);
      $num_option = count($this->options);
      $cnt = count($total_vote);
      if ($cnt > 0) {
        for ($i=0; $i<$cnt; $i++) {
          if ($total_vote[$i]->user_id == PA::$login_uid || @$_COOKIE['vote'] == $current[0]->poll_id) {
            $this->flag = 1;
            for ($j=1; $j<=$num_option; $j++) {
              if ($this->options['option'.$j] != '') {
                $vote[] = $obj->load_vote_option($current[0]->poll_id, $this->options['option'.$j]);
              }
            }
            break;
          } else { 
              $this->flag = 0;
            }
        }
      }
      if (!empty($vote)) {
        for ($i=0; $i<count($vote); $i++){
          $this->option_precent[] = round(($vote[$i][2]->counter / $this->total_vote_count) * 100, 1); 
          $this->option_vote_count[] = $vote[$i][2]->counter; 
        }
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
    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    $inner_html_gen= & new Template($inner_template);
    $inner_html_gen->set('flag', $this->flag);
    $inner_html_gen->set('percentage', @$this->option_precent);
    $inner_html_gen->set('vote_count', @$this->option_vote_count);
    $inner_html_gen->set('topic', $this->topic);
    $inner_html_gen->set('total_vote', $this->total_vote_count);
    $inner_html_gen->set('options', $this->options);
    $inner_html_gen->set('cnt_prev', $this->cnt_prev);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>