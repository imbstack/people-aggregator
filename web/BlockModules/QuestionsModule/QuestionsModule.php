<?php 
require_once "ext/Question/Question.php";


class QuestionsModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
 
  function __construct() {
    parent::__construct();
    ////No need to specify the html_block_id if it is same as the name of the Module name.
    //$this->html_block_id = "QuestionsModule";    
  }
  
   function render() {
    $this->title = __("Question of the Day");
    $question = new Question();
    
//-- fix by Z.Hron - show Question module only if $questions_total > 0
    $chk_cnt_params = array('cnt' => TRUE, 'is_active' => 1, 'show' => 1);
    $questions_total = $question->load_many($chk_cnt_params);
    if($questions_total > 0)  {
//--    
      $params = array('cnt' => FALSE,
                      'show' => 1,
                      'page' => 1,
                      'is_active' => 1,
                      'sort_by' => 'changed',
                      'direction' => 'DESC');
      $data = $question->load_many($params);
      $this->links = objtoarray($data);
 
      $this->inner_HTML = $this->generate_inner_html ();
      $network_stats = parent::render();
      return $network_stats;
//--      
    } else {
      return null;   
    }
//--    
  }
  
  function generate_inner_html () {
    switch ( $this->mode ) {
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;  
    }
    $inner_html_gen = & new Template( $tmp_file );
    $inner_html_gen->set('links', $this->links);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>