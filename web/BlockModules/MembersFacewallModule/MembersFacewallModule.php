<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 */

require_once PA::$blockmodule_path."/FacewallModule/FacewallModule.php";


class MembersFacewallModule extends FacewallModule {

  public $module_type = 'group|network';
  public $module_placement = 'left|right';
  public $title;
  public $view_all_url;

  function __construct($sort_by = null, $gid = null) {
    parent::__construct();
    $this->sort_by = $sort_by;
    $this->title = sprintf(__("%s Members"), PA::$group_noun);
    $this->html_block_id = "members";
    $this->view_all_url = PA::$url . PA_ROUTE_PEOPLES_PAGE;
 }

  public function initializeModule($request_method, $request_data)  {
    if(!empty($this->shared_data['group_info'])) {
       $sort = ($this->sort_by == 'last_login') ? 'last_login' : 'created';
       $group = $this->shared_data['group_info'];
       $this->gid = $group->collection_id;
       $users = $group->get_members($cnt=FALSE, 5, 1, $sort, 'DESC',FALSE);
       $total_users = count($users);
    } else {
    	$this->title = __('Members');
       $net_params = array('page'=>1, 'show'=>5, 'network_id'=>PA::$network_info->network_id);
       if($this->sort_by == 'last_login') {
          $sort = array('sort_by'=>'U.last_login');
          $net_params = array_merge($net_params,$sort);
       }
       $users = Network::get_members($net_params);
       $total_users = count($users['users_data']);
    }
    $users_data = array();

    $status = null;
    if (!empty(PA::$extra['reciprocated_relationship']) && PA::$extra['reciprocated_relationship'] == NET_YES) {
        $status = APPROVED;
    }
    if (!empty($users)) {
      if(!empty($this->shared_data['group_info'])) {
        foreach ($users as $user) {
            $count_relations = Relation::get_relations($user['user_id'], $status, PA::$network_info->network_id);
            $group_member = new User();
            $group_member->load((int)$user['user_id']);
            $users_data[] = array(
            	'user_id' => $user['user_id'],
            'picture' => $group_member->picture,
            'login_name' => $group_member->login_name,
            'display_name' => $group_member->display_name,
            'no_of_relations' => count($count_relations)
            );

        }
        $users = array('users_data'=>$users_data, 'total_users'=>$total_users);

      } else {

        // counting no of relation of each user
        for ($i=0; $i<$total_users; $i++) {
          $count_relations = Relation::get_relations($users['users_data'][$i]['user_id'], $status, PA::$network_info->network_id);
          $curr_user_relations = $count_relations;
          $users['users_data'][$i]['no_of_relations'] = count($count_relations);
        }
      }
      $this->links = $users;
      $this->sort_by = TRUE;
    } else {
      $this->do_skip = TRUE;
    }
  }

  /**
  *  Function : render()
  *  Purpose  : produce html code from tpl file
  *  @return   type string
  *            returns rendered html code
  */
  function render() {

    if (!empty($this->gid)) {//if it is group's facewall then append gid in url
      $this->view_all_url = "/view_all_members.php?gid=".$this->gid;
    }
    $content = parent::render();
    return $content;
  }
}
?>
