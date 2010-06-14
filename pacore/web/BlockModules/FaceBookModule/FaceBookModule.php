<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* FaceBookModule.php is a part of PeopleAggregator.
* [description including history]
* @deprecated
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/

require_once "web/includes/classes/Pagination.php";

class FaceBookModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';

  public  $Paging;
  public $page_links, $page_prev, $page_next, $page_count;

  function __construct() {
    parent::__construct();
    $this->title = __("FaceBookFeed");
    $this->html_block_id = "FaceBookModule";
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
	return '<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/en_US" type="text/javascript"></script><script type="text/javascript">FB.init("088d3c736e2fa1c0b57c9591660dbc88");</script><fb:live-stream event_app_id="428746685223" xid="YOUR_EVENT_XID" width="300" height="500"></fb:live-stream>';
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
