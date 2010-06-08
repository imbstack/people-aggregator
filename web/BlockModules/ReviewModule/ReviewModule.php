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

require_once "api/Review/Review.php";

class ReviewModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';

  public static $valid_params = array("view");
  
  function __construct() {
    $this->html_block_id = "ReviewModule";
    $this->main_block_id = "mod_review";
    $this->view = "default";
    parent::__construct();
  }

  function render() {
    if (empty(PA::$login_user)) return __("Login required");

    $this->title = 'Reviews';

    $this->subject_type = $this->params['subject_type'];
    $this->subject_id = $this->params['subject_id'];

    switch ($this->view) {
    case 'default':
      if (empty($this->subject_type) || empty($this->subject_id)) return "subject_type and subject_id are required";
      $this->reviews = Review::get_recent_by_subject($this->subject_type, $this->subject_id, 10);
      break;
    case 'recent_short':
      if (empty($this->subject_type)) return "subject_type is required";
      $this->reviews = Review::get_recent_by_subject_type($this->subject_type, 10);
      break;
    }

    // find unique author user_id values
    $user_ids = array();
    foreach ($this->reviews as $rev) $user_ids[$rev->author_id] = 1;
    if (!empty($user_ids)) {
      // load all users
      $u = new User();
      $users = $u->load_users(array_keys($user_ids));
      // map ids to user info
      $user_map = array();
      foreach ($users as $u) $user_map[$u['user_id']] = $u;
      // and finally put them all in the review objects
      foreach ($this->reviews as $rev) $rev->author = $user_map[$rev->author_id];
    }

    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function render_for_post() {
    if (empty(PA::$login_user)) return __("Login required");

    $rev = new Review;
    $rev->author_id = PA::$login_user->user_id;
    $rev->subject_type = $this->params["subject_type"];
    $rev->subject_id = $this->params["subject_id"];
    //TODO: validate subject_type and subject_id
    $rev->title = $this->params["title"];
    $rev->body = $this->params["body"];
    //TODO: ensure html is stripped properly
    $rev->save();
    
    return $this->render();
  }
  
  function generate_inner_html() {
    $tpl = & new Template(PA::$blockmodule_path .'/'. get_class($this) . "/center_inner_".$this->view.".tpl", $this);
    $inner_html = $tpl->fetch();
    return $inner_html;
  }

}
?>