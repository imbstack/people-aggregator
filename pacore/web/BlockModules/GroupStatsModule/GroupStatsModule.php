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

class GroupStatsModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  public $group_details;

  public function __construct() {
    parent::__construct();
    $this->title = __('Group Statistics');
  }

  public function initializeModule($request_method, $request_data) {
    if (empty($request_data['gid'])) return 'skip';
    $this->gid = $request_data['gid'];
    // NOTE:
    // The code bellow is temporrary solution and should be removed
    // when refactoring of all other pages that using this module
    // will be done!
    // ----------------------------------------------------------------------
    if(empty($this->shared_data['group_info']) && !empty($request_data['gid'])) {
      $group = ContentCollection::load_collection((int)$this->gid);
      $member_type = Group::get_user_type(PA::$login_uid, (int)$this->gid);
      $this->shared_data['member_type'] = $member_type;
      $this->shared_data['group_info'] = $group;
      $this->shared_data['members'] = Group::get_member_count((int)$this->gid);
      $owner = new User();
      $owner->load((int)Group::get_owner_id((int)$this->gid));
      $this->shared_data['author_picture'] = $owner->picture;
      $this->shared_data['author_name'] = $owner->login_name;
      $this->shared_data['access_type'] = ($this->shared_data['group_info']->reg_type == $this->shared_data['group_info']->REG_OPEN) ? ' Open': ' Moderated';
    }
    // ----------------------------------------------------------------------
    switch ($this->page_id) {
      case PAGE_GROUP_THEME:
      case PAGE_GROUP_MODERATION:
      case PAGE_GROUP:
      case PAGE_SHOWCONTENT:
      case PAGE_PERMALINK:
        $this->group_details = $this->shared_data['group_info'];
        $this->group_details->author_picture = $this->shared_data['author_picture'];
        $this->group_details->author_name = $this->shared_data['author_name'];
        $this->group_details->members = $this->shared_data['members'];
        $this->access_type = $this->shared_data['access_type'];
      break;
      default:
        $this->group_details = $this->generate_group_links();
    }
  }

  public function render() {
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  public function generate_inner_html () {
    switch ($this->mode) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set_object('group_details', $this->group_details);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

  public function generate_group_links() {
    $group_info = NULL;
    // Retrive the group details
    $group_data = ContentCollection::load_collection($this->gid, NULL);
    $group_info['members'] = Group::get_member_count($this->gid);
    // Loading the Group owner
    $user = new User();
    $user->load((int)$group_data->author_id);
    $group_info->created = PA::datetime($group->created, 'long', 'short'); //date("F d, Y h:i A", $group_data->created);
    $group_info->author_picture = $user->picture;
    $group_info->author_name = $user->login_name;
    $group_info->author_id = $group_data->author_id;
    $group_info->category_name = $group_data->category_name;
    $group_info->access_type = ($group_data->reg_type == $group_data->REG_OPEN) ? ' Open': ' Moderated';
    unset($user);
    unset($group_data);
    return $group_info;
  }
}
?>
