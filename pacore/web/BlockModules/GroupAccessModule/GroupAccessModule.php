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
class GroupAccessModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'left|right';
  
  public $group_details;
  public $outer_template = 'outer_public_side_module.tpl';
  public $join_this_group_string;
  public $is_member, $is_admin;

  function __construct() {
    parent::__construct();
    $this->is_member = $this->is_admin = FALSE;
  }
  
  function initializeModule($request_method, $request_data) {
    if (empty($this->shared_data['group_info'])) return 'skip';
    $this->group_details = $this->shared_data['group_info'];
  }

  function render() {
    $this->group_details = $this->shared_data['group_info']; 
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  public function generate_inner_html() {
    switch ($this->mode) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    $this->generate_group_links();
    $this->title = chop_string(stripslashes((!empty($this->group_details) ? $this->group_details->title : NULL)), GROUP_TITLE_LENGTH);
    
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set_object('group_details', $this->group_details);    
    $inner_html_gen->set('join_this_group_string', $this->join_this_group_string);
    $inner_html_gen->set('is_member', $this->is_member);
    $inner_html_gen->set('is_admin', $this->is_admin);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
  
  public function generate_group_links() {
    $user_type = FALSE;
    if(!empty(PA::$login_uid)) {
      $user_type = Group::get_user_type(PA::$login_uid, $this->group_details->collection_id);
      $this->is_member = ($user_type == MEMBER) ? TRUE : FALSE;
      if ($user_type == OWNER) {
        $this->is_member = TRUE;
        $this->is_admin = TRUE;
      }
    }
    
    if (!$this->is_member) {
      if((!empty($this->group_details) ? $this->group_details->reg_type : NULL) && $this->group_details->reg_type == REG_MODERATED) {
        $this->join_this_group_string = __('Request to join this group');
      } else {
        $this->join_this_group_string = __('Join This Group');
      }
    }
  }   
}
?>