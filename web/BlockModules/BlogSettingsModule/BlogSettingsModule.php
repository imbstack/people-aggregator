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
// global var $path_prefix has been removed - please, use PA::$path static variable

class BlogSettingsModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'middle';
  public $feed_id;
  public $status;
  public $uid;
  public $user;
  public $outer_template = 'outer_public_group_center_module.tpl';

  function __construct() {
    parent::__construct();
    $this->title = null;
  }

  public function initializeModule($request_method, $request_data) {
  	 
    if (!(PA::$login_uid)) {
      header("Location: login.php?error=1");
      exit;
    }
    $this->uid = PA::$login_uid;
    $this->user = PA::$login_user;

		if (!empty(PA::$config->simple['use_simpleblog'])) {
      return 'skip';
    }
		
    $params_profile = array('field_name'=>'BlogSetting','user_id'=>$this->uid);
    $data_profile = $this->user->get_profile_data($params_profile);
    if(!empty($data_profile) && $data_profile[0]->field_value > BLOG_SETTING_STATUS_NODISPLAY) {
      return 'skip';
    }
  }
  
  public function handleBlogSetting($request_method, $request_data) {
    $msg = NULL;
    $error = FALSE;
    switch ($request_method) {
      case 'POST':
        $field_type = GENRAL;
        $field_name = 'BlogSetting';
        if ($request_data['personal_blog'] && $request_data['external_blog'] ) {
          $status = BLOG_SETTING_STATUS_ALLDISPLAY;
        } else if ($request_data['personal_blog'] && !$request_data['external_blog']) {
          $status = PERSONAL_BLOG_SETTING_STATUS ;
        } else if (!$request_data['personal_blog'] && $request_data['external_blog']) {
          $status = EXTERNAL_BLOG_SETTING_STATUS;
        } else  {
          $status = BLOG_SETTING_STATUS_NODISPLAY;
        }
        $params_profile = array(array($this->uid, $field_name, $status, $field_type, 1, null));
        $this->user->save_user_profile_fields($params_profile, $field_type, $field_name);
        if (!empty($request_data['mode']) && htmlspecialchars($request_data['mode']) == 'blog_rss') {
          $location = PA::$url . PA_ROUTE_EDIT_PROFILE . '?type=blogs_rss&msg_id=9025';
        } else {
          $location = PA::$url . PA_ROUTE_USER_PRIVATE . '/' . 'msg_id=9025';
        }
        header('Location:' . $location);
        exit;
        break;
      case 'GET':
        if (!empty($request_data['mode']) && htmlspecialchars($request_data['mode']) == 'blog_rss') {
          header('Location:'. PA::$url . PA_ROUTE_EDIT_PROFILE . '?type=blogs_rss&msg_id=9025'); 
          exit;
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
  
  function generate_inner_html () {    
    $inner_template = NULL;
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_html.tpl';
    }
    $inner_html_gen = & new Template($inner_template);    
    $inner_html_gen->set('status', $this->status);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>