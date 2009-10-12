<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 */

// require_once PA::$blockmodule_path."/FacewallModule/FacewallModule.php";


class FamilyFacewallModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'left|right';
  public $title;
  public $view_all_url;
  public $sort_by = FALSE;
  public $outer_template = 'outer_public_side_module.tpl';


  function __construct($sort_by = null, $gid = null) {
    parent::__construct();
    $this->sort_by = $sort_by;
    $this->title = __("Family Members");
    $this->html_block_id = "members";
 }

  public function initializeModule($request_method, $request_data)  {
    if(empty($this->shared_data['group_info'])) return "skip";
    
    $this->is_in_family = $this->may_see_details = false;
    
    $sort = 'created';
    $group = $this->shared_data['group_info'];
    $this->gid = $group->collection_id;
    $users = $group->get_members($cnt=FALSE, 5, 1, $sort, 'DESC',FALSE);
    $total_users = count($users);

    $users_data = array();
		foreach ($users as $user) {
				$group_member = new User();
				$group_member->load((int)$user['user_id']);
				// see if we have a special relation of this user to the group
        list($relType, $membertype) = TypedGroupEntityRelation::get_relation_to_group($user['user_id'], (int)$this->gid);
				$users_data[] = array(
					'user_id' => $user['user_id'],
				'picture' => $group_member->picture,
				'login_name' => $group_member->login_name,
				'display_name' => $group_member->display_name,
				'family_status' => $membertype
				);
				
				if (PA::$login_uid == $user['user_id']) $this->is_in_family = $this->may_see_details = true;


		}
		$users = array('users_data'=>$users_data, 'total_users'=>$total_users);
		$this->links = $users;
		$this->sort_by = false;
  }

  function render() {
    if ($this->may_see_details) $this->view_all_url = PA_ROUTE_FAMILY_MEMBERS."/?gid=".$this->gid;
    else $this->view_all_url = NULL;
    if (empty($this->links)) {
      $this->do_skip = TRUE;
      return;
    }
    $this->inner_HTML = $this->generate_inner_html ($this->links);
    $content = parent::render();
    return $content;
  }


  function generate_inner_html($links) {
    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner.tpl.php';
    $obj_inner_template = & new Template($inner_template, $this);
    $obj_inner_template->set('links', $links);
    $obj_inner_template->set('block_name', $this->html_block_id);
    $obj_inner_template->set('current_theme_path', PA::$theme_url);
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }
}
?>
