<?php
require_once "api/Category/Category.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "ext/Group/Group.php";


class GroupsCategoryModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';
  
  public $sub_cid, $total_groups;

  function __construct() {
    parent::__construct();
  }
  
  public function initializeModule($request_method, $request_data) {
    $this->total_groups = Group::get_total_groups();
  }
  
  private function get_links() {
    $links = Group::category_group_listing();

    $newarray = array();
    for ( $i = 0, $j = 1, $m = 0; $i < count( $links ); $i++, $j++, $m++ ) {
    $groups_info = array();
      if ( @$links[$i]->category_id != @$links[$j]->category_id ) {
        $newarray[$m]['cat_id']=$links[$i]->category_id;
        $newarray[$m]['cat_name']=$links[$i]->category_name;
        if ( $links[$i]->group_name ) {
          $groups_info[0]['group_name']= $links[$i]->group_name;
          $groups_info[0]['group_id']= $links[$i]->group_id;
          $members = 1;
        }
        else {
          $groups_info[0]['group_name'] = '';
          $groups_info[0]['group_id'] = '';
          $members= 0;
        }
        $newarray[$m]['members']=$members;
        $newarray[$m]['group_info']=$groups_info;

      }
      else {
         $k = 0;
         if( $links[$i]->group_name ) {
            $groups_info[$k]['group_name'] = $links[$i]->group_name;
            $groups_info[$k]['group_id'] = $links[$i]->group_id;
            ++$k;
         }
         while ( $links[$i]->category_id == @$links[$j]->category_id)  {
         // FIXME: the above indices like to go out of range, what's up here?
            if ( $links[$j]->group_name ) {
               $groups_info[$k]['group_name'] = $links[$j]->group_name;
               $groups_info[$k]['group_id'] = $links[$j]->group_id;
               ++$k;
            }
            $i++;
            $j++;
         }
         $newarray[$m]['members'] = $k;
         $newarray[$m]['cat_id'] = $links[$i]->category_id;
         $newarray[$m]['cat_name'] = $links[$i]->category_name;
         $newarray[$m]['group_info'] = $groups_info;
        }
     }
    $links = $newarray;
    return $links;
  }
  
  function render() {
    $this->title = sprintf(__('Browse %d Groups'), $this->total_groups);
    $this->links = $this->get_links();
    $this->inner_HTML = $this->generate_inner_html ($this->links);
    $content = parent::render();
    return $content;
  }

  function generate_inner_html ($links) {
    switch ($this->mode) {
     default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';   
     break;
    }
    $inner_html_gen = & new Template($inner_template);
    $inner_html_gen->set('newarray', $links);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>