<?php
//anonymous user can not view this page;
$login_required = TRUE;

$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.  

//including necessary files
include_once("web/includes/page.php");
include_once "ext/Group/Group.php";  
require_once "api/MessageBoard/MessageBoard.php";

/*including Js files */
$parameter = js_includes('common.js');
// for query count

global $query_count_on_page, $login_uid;

  $query_count_on_page = 0;
  $mid = $_REQUEST['mid'];
  $request_info = load_info();
  $parent_id = $request_info['parent_id'];
  $parent_type = $request_info['parent_type'];

  $member_type = Group::get_user_type($login_uid, $parent_id);
  $gid = (int)$_GET['gid'];
  $group_data = ContentCollection::load_collection((int)$gid, $login_uid);
  $cat_obj = new MessageBoard();
  $edit_data = $cat_obj->get_by_id($mid);
 
 $params['action'] = 'edit_forum';
 $params['group_owner'] = $group_data->author_id;
 $params['forum_owner'] = $edit_data['user_id'];
 
 $msg = NULL;
 if ( user_can($params)) {
   $is_edit = TRUE;
 } else {
   $is_edit = FALSE;
   $msg = 'You are not authorized to edit forum';
 }
 
  function setup_module($column, $module, $obj) {
  global $group_data, $gid, $request_info, $is_edit, $edit_data, $member_type;
    $obj->gid = $gid;
    switch ($module) {
      case 'MembersFacewallModule':
        $group = new Group();
        $group->collection_id = $gid;
        $group->is_active = 1;
        $members = $group->get_members($cnt=FALSE, 5, 1, 'created', 'DESC',FALSE);
        if (is_array($members)) {
          $count = count($members);
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
      break;
      case 'CreateForumTopicModule':
        if (!$is_edit) return 'skip';
        $obj->edit_data = $edit_data; 
        $obj->is_edit = $is_edit;
      break;
    }
  }
  $page = new PageRenderer("setup_module", PAGE_CREATE_FORUM_TOPIC, "Edit forum", 'container_three_column.tpl','header_group.tpl',PRI,HOMEPAGE,$network_info);
  
  uihelper_error_msg($msg);
  uihelper_get_group_style($gid);
  
  echo $page->render();
?>