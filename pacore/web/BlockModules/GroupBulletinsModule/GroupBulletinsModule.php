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

class GroupBulletinsModule extends Module {
  
  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->title = __("Send Group Bulletins");
    $this->html_block_id = "GroupBulletinsModule";
  }
  
  function initializeModule($request_method, $request_data) {
    if (empty($this->shared_data['group_info'])) return 'skip';
    $this->group_details = $this->shared_data['group_info'];
		if (empty($request_data['gid'])) return 'skip'; // sanity check
		
		// we do this check only if the user is not already permitted to manage ads
		$gp_access = PermissionsHandler::can_group_user(PA::$login_uid, $request_data['gid'], array('permissions' => 'manage_groups'));
		if (! $gp_access) return 'skip'; // user shoudn't have gotten here in the first place, just don't show anything
		$error_msg = false;

		if ($request_method == 'POST') {
			$value_to_validate = array('title'=>'Title','bulletin_body'=>'Bulletin body');
			foreach ($value_to_validate as $key=>$value) {
				$request_data[$key] = trim($request_data[$key]);
				if (empty($request_data[$key])) {
				$error_msg .= $value.' can not be empty<br>';
				}
			}

			if (!$error_msg) { // if no errors yet
				$subject = $request_data['title'];
				$bull_message = $request_data['bulletin_body'];
				$group = new Group();
				$group->load($request_data['gid']);
				
				if (!empty($request_data['bulletins'])) { // send to all members
					$gms = $group->get_members();
					foreach ($gms as $i=>$m) {
						$u = new User();
						$u->load((int)$m['user_id']);
						$to_members[] = $u;
					} 
				} else if (!empty($request_data['send_to_me_only'])) { // test send to admin user
					$to_members = array(PA::$login_user);
				}
				$this->sent_to = array();
				// send it
				if (!empty($to_members)) {
					foreach($to_members as $recipient) {
						$this->sent_to[] = $recipient->display_name;
						PANotify::send("group_bulletin_sent", 
							$recipient, 
							$group, 
							array(
								'bulletin.message' => $bull_message,
								'bulletin.subject' => $subject
							));
					}
				}
				
				// wannt a preview with that?
				if (!empty($request_data['preview'])) { // if preview is selected.
					$container_html = 'default_email_container.tpl';
					$email_container = & new Template('config/email_containers/'.$container_html);
					$email_container->set('subject', $subject);
					$email_container->set('message', $bull_message);
					$this->preview_msg = $email_container->fetch();
				}
			}
		}
	}
  
   function render() {    
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html() {
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    $group_bulletin = & new Template($tmp_file, $this);
    $group_bulletin->set('preview_msg', @$this->preview_msg);
    $inner_html = $group_bulletin->fetch();
    return $inner_html;
  }
}
?>
