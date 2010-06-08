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
  //anonymous user can not view this page;
  $login_required = FALSE;
  
  $use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.  
  
  //including necessary files
  include_once("web/includes/page.php");
  include_once "api/ModuleSetting/ModuleSetting.php";  
  include_once "ext/Group/Group.php";  
  
  global $login_uid;
  $msg = '';
  if (!empty($_GET['gid'])) {
    $gid = (int)$_GET['gid'];
    $group_data = ContentCollection::load_collection($gid, $login_uid);
    $is_member = Group::member_exists($gid, $login_uid);
    $is_admin = Group::is_admin($gid, $login_uid); 
    if ($group_data->reg_type == REG_INVITE && !$is_member && !$is_admin) {
      $msg = 9005;
    }
  }
  
  function setup_module($column, $module, $obj) {
    global $is_member, $is_admin, $group_data, $gid;
    
    $obj->gid = $gid;
    switch ($module) {
      case 'MembersFacewallModule':
        $group = new Group();
        $group->collection_id = $gid;
        $group->is_active = ACTIVE;
        $members = $group->get_members($cnt=FALSE, 5, 1, 'created', 'DESC',FALSE);
        if (is_array($members)) {
          $count = count($members);
          $group_members = members_to_array($members);
          $users_data = array();
          foreach ($members as $member) {
            $count_relations = Relation::get_relations($member['user_id'], APPROVED, PA::$network_info->network_id);
            $user = new User();
            $user->load((int)$member['user_id']);
            $login_name = $user->login_name;
            $user_picture = $user->picture;
            $users_data[] = array('user_id'=>$member['user_id'],'picture'=>$user_picture,'login_name'=>$login_name,'no_of_relations'=>count($count_relations));
          }
          $users = array('users_data'=>$users_data, 'total_users'=>$count);
        }
        $obj->links = $users;
        $obj->gid = $gid;
      break;
      case 'ImagesModule' :
        $obj->block_type = 'Gallery';            
        $obj->page = 'grouppage';
        $obj->title = 'Group Gallery';
        $obj->group_details['collection_id'] = $group_data->collection_id;
      break;
      case 'GroupForumModule':
        if ($group_data->reg_type == REG_INVITE ) {
          if (!$is_member && !$is_admin) {
            return "skip";
          }  
        }
        $obj->is_member = $is_member;
        $obj->is_admin = $is_admin;
        $obj->group_details = $group_data;
      break;
      case 'RecentPostModule':
        $obj->type = 'group';
        $obj->gid = $_REQUEST['gid'];
      break;
    }
  }

  function members_to_array($members) {
     $out = array();
     foreach($members as $member) {
       $out[] = $member['user_id'];
     }
     return $out;
  }
  
  function get_networks_users_id () {
    $users = array();
    $users_ids = array();
    $users = Network::get_members(array('network_id'=>PA::$network_info->network_id));
    if ( $users['total_users'] ) {
       for( $i = 0; $i < $users['total_users']; $i++) {
          $users_ids[] = $users['users_data'][$i]['user_id'];
       }
    }
    
    return $users_ids;
  }
  
  $page = new PageRenderer("setup_module", PAGE_FORUM_HOME, "Forum Home", 'container_three_column.tpl', 'header_group.tpl', PRI, HOMEPAGE, PA::$network_info);
  
  uihelper_error_msg($msg);
  uihelper_get_group_style($gid);
  echo $page->render();
?>