<?php
// this is a Module for DEBUGGING only
// add it to any Group page to see the list of members and their assgned Roles quickly
// NOTE: this Module cares not for permissions and provacy. It's for Debugging, ok?

class DebugGroupRolesModule extends Module {
  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

  function __construct() {
    parent::__construct();
  }

  function initializeModule($request_method, $request_data) {
    if (empty($this->shared_data['group_info'])) return 'skip';
    $this->group_details = $this->shared_data['group_info'];
  }

	public function render() {
		$this->title = "Debug Dump of Members and Roles assigned";
		$html = '';
		$battalion = $this->group_details;
		// see if target Battalion already has a Cadre
		$members = Group::get_members_with_roles($battalion->collection_id);
		foreach ($members as $i=>$member) {
			$html .= "<hr/><b>$member->login_name</b> ($member->user_id/$member->user_type)<br/>Assigned Roles:";
			if (empty($member->roles)) continue;
			$html .= "<ul>";
			foreach ($member->roles as $i=>$r) {
				$html .= "<li>$r</li>";
			}
			$html .= "</ul>\n";
		}
    $this->inner_HTML = $html;
    $content = parent::render();
    return $content;
	}
}
?>