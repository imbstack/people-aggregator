<?php
require_once "api/Category/Category.php";
require_once "ext/Group/Group.php";
require_once "web/includes/classes/Pagination.php";

class MyGroupsModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public $uid, $Paging;
  public $page_links, $page_prev, $page_next, $page_count, $user_name;
  public $usergroups = array();

  public function __construct() {
  }
    
  //code for module initialization
  public function initializeModule($request_method, $request_data) {
    if (!PA::$login_uid) return 'skip';
    if (empty($this->page_id)) return 'skip';
      switch ($this->page_id) {
        case PAGE_GROUPS_HOME:
          if (PA::$page_uid && (PA::$page_uid != PA::$login_uid)) {
            $this->uid = PA::$page_uid;
            $page_user = get_user();
            $this->title = ucfirst($page_user->first_name).'\'s ';
            $this->title .= __('Groups');
            $this->user_name = $page_user->login_name;
          } else {
            $this->uid = PA::$login_uid;          
          }
          $this->usergroups = Group::get_user_groups((int)$this->uid, FALSE, 5, 1);
        break;
        case PAGE_USER_PUBLIC:
          $this->uid = PA::$page_uid;
          $this->title = abbreviate_text((ucfirst(PA::$page_user->first_name).'\'s '), 18, 10);
          $this->title .= __('Groups');
          $this->user_name = PA::$page_user->login_name;
          $this->usergroups = Group::get_user_groups((int)$this->uid, FALSE, 5, 1, 'created','DESC', 'public');
        break;
        case PAGE_USER_PRIVATE:
          $this->title = __('My Groups');
          $this->uid = PA::$login_uid;
          $this->usergroups = Group::get_user_groups((int)$this->uid, FALSE, 5, 1, 'created','DESC', 'public');
        break;
    }
  }
  
  public function get_links() {
    $this->Paging["count"] = Group::get_user_groups((int)$this->uid, TRUE);
    $ids = array();
    if ($this->usergroups) {
      foreach ($this->usergroups as $groups) {
        $ids[] = array('gid'=>$groups['gid'],'access'=>$groups['access']);
      }
    }
    $group_details = array();
    for ($gr = 0; $gr < count($ids); $gr++) {
      $group = ContentCollection::load_collection((int)$ids[$gr]['gid'],
      PA::$login_uid);
      $group_tags = Tag::load_tags_for_content_collection($ids[$gr]['gid']);
      $member_exist = Group::member_exists($ids[$gr]['gid'], PA::$login_uid);
      $picture =$group->picture;
      $cnt = Group::get_member_count($group->collection_id);
      $group_details[$gr]['id'] = $group->collection_id;
      $group_details[$gr]['title'] = stripslashes($group->title);
      $desc = stripslashes($group->description);
      $desc = substr($desc, 0, 100);
      $group_details[$gr]['desc'] = $desc;
      $group_details[$gr]['picture'] = $picture;
      $group_details[$gr]['members'] = $cnt;
      $group_details[$gr]['access'] = $ids[$gr]['access'];
    }
    return $group_details;   
  }

  function render() {
    ($this->title) ? $this->title : $this->title = __('My Groups') ;
		$this->links = $this->get_links();
		if (sizeof($this->links)) {
			$this->view_all_url = PA::$url . PA_ROUTE_GROUPS.'/uid='.$this->uid;
		}

    $this->inner_HTML = $this->generate_inner_html ();    
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    switch ($this->mode) {
      default:
        $this->outer_template = 'outer_private_side_module.tpl';
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;  
    }
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('mode', $this->mode);
    $inner_html_gen->set('user_name', $this->user_name);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>
