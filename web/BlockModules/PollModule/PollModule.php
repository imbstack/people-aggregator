<?php
require_once "api/Content/Content.php";
require_once "ext/Poll/Poll.php";
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
    $this->title = __('Poll');
  }
  function render() {
    $this->flag = 0 ;
    $obj = new Poll();
    $current = $obj->load_current();
    $prev_poll = $obj->load_prevous_polls();
    $this->cnt_prev = count($prev_poll);
    if ($current) {
      $user_info = $obj->load_vote($current[0]->poll_id, $this->login_uid);
      $total = count($user_info);
      $total_vote = $obj->load_vote($current[0]->poll_id);
      $this->topic = $obj->load_poll($current[0]->poll_id);
      $this->options = unserialize($this->topic[0]->options);
      $num_option = count($this->options);
      $cnt = count($total_vote);
      if ($cnt > 0) {
        for ($i=0;$i<$cnt;$i++) {
          if($total_vote[$i]->user_id == PA::$login_uid || @$_COOKIE['vote'] == $current[0]->poll_id) {
            $this->flag =1;
            for ($j=1; $j<=$num_option;$j++) {
              if ($this->options['option'.$j]!='') {
                $vote[] = $obj->load_vote_option                                                           ($current[0]->poll_id,$this->options['option'.$j]);
              }
            }
            break;
          } else { 
              $this->flag = 0;
            }
        }
      }
      $this->total_vote = count($total_vote);
      if (!empty($vote)) {
        for ($i=0;$i<count($vote);$i++){
          $this->per_option[] = round(($vote[$i][2]->counter/count($total_vote))*100, 1); 
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
    $inner_html_gen->set('flag',$this->flag);
    $inner_html_gen->set('percentage',$this->per_option);
    $inner_html_gen->set('topic',$this->topic);
    $inner_html_gen->set('total_vote',$this->total_vote);
    $inner_html_gen->set('options',$this->options);
    $inner_html_gen->set('cnt_prev',$this->cnt_prev);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>