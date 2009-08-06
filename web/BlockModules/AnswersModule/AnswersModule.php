<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        ManageQuestionsModule.php, BlockModule file to generate ManageQuestionsModule
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class ManageQuestionsModule which generates html of 
 *              question, entry form - it is a center module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once "web/includes/classes/Pagination.php";
require_once "ext/Question/Question.php";

class AnswersModule extends Module {

  
  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  public $error;
  public $Paging, $question_id, $page_first, $page_last, $page_links;
 
  function __construct() {
    parent::__construct();
  }
  
  public function initializeModule($request_method, $request_data) {
    global $paging;
    $this->Paging = $paging;
  }

  public function handleSaveAnswer($request_method, $request_data) {
    $msg = NULL;
    $error = FALSE;
    switch ($request_method) {
      case 'POST':
          filter_all_post($request_data);
          $request_data['answer'] = trim($request_data['answer']);
          if(empty($request_data['answer'])) {
            $msg = __('Answer can not be left blank');
            $error = true;
          }
          else {
            $comment = new Comment();
            // setting some variables
            $usr = PA::$user;
            $comment->comment = $comment->subject = $request_data['answer'];
            $comment->parent_type = TYPE_ANSWER;
            $id = $comment->parent_id = $comment->content_id = $request_data['id'];
            $comment->user_id = $usr->user_id;
            $comment->name = $usr->login_name;
            $comment->email = $usr->email;    
              if($comment->spam_check()) {
                 $msg = __('Sorry, your Answer cannot be posted as it looks like spam. Try removing any links to possibly suspect sites, and re-submitting.');
                 $error = true;
                 Logger::log('Comment rejected by spam filter', LOGGER_ACTION);
              } else {
                $msg = __('Your Answer has been posted successfully');
                $comment->save_comment();
                if ($comment->spam_state != SPAM_STATE_OK) {
                  $msg = __('Sorry, your answer cannot be posted as it was classified as spam by Akismet, or contained links to blacklisted sites. Please check the links in your post, and that your name and e-mail address are correct.');
                  $error = true;
                } else {
                  unset($request_data);
                  //invalidate cache of content block as it is modified now
                  if(PA::$network_info) {
                     $nid = '_network_'.PA::$network_info->network_id;
                  } else {
                    $nid='';
                  }
                  //unique name
                  $cache_id = 'content_'.$id.$nid; 
                    CachedTemplate::invalidate_cache($cache_id);
                 }
               }
            }
          break;
        }
    $msg_array = array();
    $msg_array['failure_msg'] = $msg;
    $msg_array['success_msg'] = NULL;
    $redirect_url = NULL;
    $query_str = NULL;
    set_web_variables($msg_array, $redirect_url, $query_str);
  }
  
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  
  function get_links() {
    $question = new Question();
    $params = array('cnt' => FALSE,
                    'show' => 1,
                    'page' => 1,
                    'sort_by' => 'changed',
                    'direction' => 'DESC');
    $data = $question->load_many($params);
    $links = NULL;
    if (!empty($data)) {
      $links = objtoarray($data);
      $links = current($links);
      $this->title = $links['body'];
      $this->question_id = $links['content_id'];
      $comment = new Comment();
      $comment->parent_id = $this->question_id;
      $comment->parent_type = TYPE_ANSWER;
      $this->Paging['count'] = $comment->get_multiples_comment(TRUE);
      $links = $comment->get_multiples_comment(FALSE, $this->Paging['show'], $this->Paging['page']);
    }
    return $links; 
  }
  
  function generate_inner_html () {    
    $links = $this->get_links();
    // set links for pagination
    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    $inner_html_gen = & new Template($tmp_file);  
    $inner_html_gen->set('links', $links);
    $inner_html_gen->set('edit', @$this->edit);
    $inner_html_gen->set('question_id', $this->question_id);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>