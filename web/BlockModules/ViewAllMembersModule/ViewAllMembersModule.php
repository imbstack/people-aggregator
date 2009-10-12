<?php
require_once "web/includes/classes/Pagination.php";

class ViewAllMembersModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';

  public  $Paging;
  public $page_links, $page_prev, $page_next, $page_count;

  function __construct() {
    parent::__construct();
    $this->title = __("View all Members");
    $this->html_block_id = "ViewAllMembersModule";
  }

  function initializeModule($request_method, $request_data) {
    if (!empty($this->shared_data['group_info'])) {
	  	$this->gid = $this->shared_data['group_info']->collection_id;
	  	$this->view_type = "all";
	  	if ($this->shared_data['group_info']->group_type == "typedgroup") {
	  		PA::$config->useTypedGroups = true;
	  		require_once 'api/Entity/TypedGroupEntity.php';
	  		$this->entity = TypedGroupEntity::load_for_group((int)$this->gid);
	  		$this->entity_type = $this->entity->entity_type;
	  		PA::$group_noun = $this->entity->entity_type;
// echo "<pre>".print_r($this->entity, 1)."</pre>";exit;
	  	}
    }
    $this->network_info = PA::$network_info;
    global $paging;
    $this->Paging["page"] = $paging["page"];
    $this->Paging["show"] = $paging["show"];
    $this->page_user = NULL;
    if (PA::$page_uid && (PA::$page_uid != PA::$login_uid)) {
    	$user = new User(); ¤user-load(PA::$page_uid);
    	$this->page_user = $user->get_name();
    }
  }

   function render() {
    if ($this->view_type == 'all') {
      $this->Paging["count"] =  Network::get_members(array( 'network_id'=>$this->network_info->network_id, 'cnt'=>TRUE));
      $param = array('show'=>$this->Paging["show"],'page'=>$this->Paging["page"],'network_id'=>$this->network_info->network_id);
      $users_info = Network::get_members($param);
      $this->links = $users_info['users_data'];
    }
    else if (($this->view_type == 'relations') || ($this->view_type == 'in_relations')) {
      $extra = unserialize($this->network_info->extra);
      $this->reciprocated_relationship_set = FALSE;
      $this->relations = FALSE;
      if (@$extra['reciprocated_relationship'] == NET_YES) {
        $this->reciprocated_relationship_set = TRUE;
        $this->relations = TRUE;
      }
      $users_info = Relation::get_all_user_relations($this->uid, $no_of_relations = 0, FALSE, $this->Paging["show"], $this->Paging["page"], 'created', 'DESC', 'internal', null, PA::$network_info->network_id);
      $this->Paging["count"] = count($users_info);
      $this->title = __("View all ");
      if ($_SESSION['user']['id'] == $this->uid) {
        $this->title .= "my";
      }
      else {
        $this->title .= $this->page_user . ' \'s';
      }
      $this->title .= " friends";
      $this->sub_title = $this->title;
      $this->links = $users_info;
    }

    if ($this->gid) {
      $group = ContentCollection::load_collection((int)$this->gid, @PA::$login_uid);
      $this->Paging["count"] = $group->get_members(TRUE);
      $members = $group->get_members(FALSE, $this->Paging["show"], $this->Paging["page"]);
      $this->title = sprintf(__("View All %s Members"), __(PA::$group_noun));

      if (is_array($members)) {
        foreach ($members as $member) {
        	// load user info
          $user = new User();
          $user->load( (int) $member['user_id']);
          $u = array(
          	'user_id'=>$user->user_id,
          	'user_id'=>$user->user_id,
          	'first_name'=>$user->first_name,
          	'last_name'=>$user->last_name,
          	'display_name' => $user->display_name,
          	'email'=>$user->email,
          	'picture'=>$user->picture,
          	'login_name'=>$user->login_name,
          	'created'=>$user->created
          );
          if (!empty(PA::$config->useTypedGroups)) {
          	require_once 'api/Entity/TypedGroupEntityRelation.php';
          	// see if we have a special relation of this user to the group
          	list($relType,$u['membertype']) = TypedGroupEntityRelation::get_relation_to_group($member['user_id'], (int)$this->gid);
          }
          $tmp_arr[] = $u;
        }
      $this->links = $tmp_arr;
      }
    }

    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }



  function generate_inner_html () {
    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);
    $this->page_prev = $Pagination->getPreviousPage();
    $this->page_next = $Pagination->getNextPage();
    $this->page_links = $Pagination->getPageLinks();

    $inner_template = NULL;
    switch ( $this->mode ) {
      case 'relations':
      case 'in_relations':
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public_relation.tpl';
      break;
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public_relation.tpl';
    }

    $obj_inner_template = & new Template($inner_template, $this);
    $obj_inner_template->set_object('links', $this->links);
    $obj_inner_template->set_object('gid', @$this->gid);
    $obj_inner_template->set('sub_title', @$this->sub_title);
    $obj_inner_template->set('total', $this->Paging['count']);
    $obj_inner_template->set('reciprocated_relationship_set', @$this->reciprocated_relationship_set);
    $obj_inner_template->set('relations', @$this->relations);
    $obj_inner_template->set('in_relations', @$this->in_relations);
    $obj_inner_template->set('user_name', $this->page_user);
    $obj_inner_template->set('page_prev', $this->page_prev);
    $obj_inner_template->set('page_next', $this->page_next);
    $obj_inner_template->set('page_links', $this->page_links);
    $obj_inner_template->set('view_type', $this->view_type);

    $inner_html = $obj_inner_template->fetch();

    return $inner_html;
  }

  function get_networks_users_id() {
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

}
?>