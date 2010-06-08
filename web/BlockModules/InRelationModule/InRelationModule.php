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

class InRelationModule extends Module {

  public $module_type = 'user|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  public $uid, $user_name;
  
  function __construct() {
    parent::__construct();
    $this->title = __("Added as a friend by");
    $this->html_block_id = "InRelationModule";    
  }
  
  public function initializeModule($request_method, $request_data) {
    if (!empty(PA::$page_uid)) {//Page uid will get preference over the login uid
      $this->uid = PA::$page_uid;
      $this->user_name = PA::$page_user->first_name.' '.PA::$page_user->last_name;
    } else if (!empty(PA::$login_uid)){
      $this->uid = PA::$login_uid;
      $this->user_name = PA::$login_user->first_name.' '.PA::$login_user->last_name;
    }
  }


  function render() {
    $extra = unserialize(PA::$network_info->extra);
    $status = null;
    if (@$extra['reciprocated_relationship'] == NET_YES) {
      $status = APPROVED;
    }
    
    //fix by Z.Hron: Show reciprocated relationship only for members of this network
    $added_by = Relation::get_all_user_ids((int)$this->uid,5, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC', $status, PA::$network_info->network_id);
    $this->links = $added_by;
    
    foreach($this->links as &$_link) {
       $count_relations = Relation::get_relations($_link['user_id'], $status, PA::$network_info->network_id);
       $_link['no_of_relations'] = count($count_relations);
    }
    $this->inner_HTML = $this->generate_inner_html ();
    if (count($this->links) > 0) {
      $this->view_all_url = PA::$url."/view_all_members.php?view_type=in_relations&amp;uid=$this->uid";
    }
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    switch ( $this->mode ) {
      case PRI:
        $this->outer_template = 'outer_private_side_module.tpl';
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
    }
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('mode', $this->mode);
    $inner_html_gen->set('uid', $this->uid);
    $inner_html_gen->set('user_name', $this->user_name);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>