<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* DebugGroupRolesModule.php is a part of PeopleAggregator.
* According to its original author (Martin):
*   this is a Module for DEBUGGING only
*   add it to any Group page to see the list of members and their assigned Roles quickly
*   NOTE: this Module cares not for permissions and privacy. It's for debugging, ok?
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
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
  /** !!
  * Copies $shared_data['group_info'] to a local variable.
  *  skipping this module if that variable is not set.
  *
  * @todo These variables do nothing.
  */
  function initializeModule($request_method, $request_data) {
    if (empty($this->shared_data['group_info'])) return 'skip';
    $this->group_details = $this->shared_data['group_info'];
  }
  
  /** !!
  * Generates the HTML by iterating through a group's members with roles.
  * 
  * @return string HTML to display for the module.
  */
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
