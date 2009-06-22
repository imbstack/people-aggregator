<?php
/**
* This module will act as the Full view of feeds added by the user.
*/

// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/ExternalFeed/ExternalFeed.php";

class ViewFeedsModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';

  public $feed_id;

  function __construct() {
    $this->html_block_id = 'ViewFeedsModule';
    $this->title = null;
  }

  public function initializeModule($request_method, $request_data) {
     if(empty($this->shared_data['profile_feeds'])) return 'skip';
     $params_profile = array('field_name' => 'BlogSetting', 'user_id' => PA::$page_uid);
     $data_profile = User::get_profile_data($params_profile);
     $blogsetting = empty($data_profile) ? null : $data_profile[0]->field_value;
     if(($blogsetting == BLOG_SETTING_STATUS_ALLDISPLAY) || ($blogsetting == EXTERNAL_BLOG_SETTING_STATUS)) {
       $this->profile_feeds = $this->shared_data['profile_feeds'];
     } else {
       return 'skip';
     }
  }

  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {

    $inner_template = NULL;
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_html.tpl';
    }

    if (!empty($this->profile_feeds)) {
      $counter = 0;
      foreach ($this->profile_feeds as $data) {
        $params = array('feed_id'=>$data['feed_id']);
        $ExternalFeed = new ExternalFeed();

        try {
          //Getting the feed data corresponding to the feed id
          $this->profile_feeds[$counter]['links'] = $ExternalFeed->get_feed_data($params);
        } catch(PAException $e) {
          //TODO: pending error handling if function fails.
          //$error = $e->message;
        }
        $counter++;
      }
    }

    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('profile_feeds', @$this->profile_feeds);
    $inner_html_gen->set('feed_id', $this->feed_id);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>