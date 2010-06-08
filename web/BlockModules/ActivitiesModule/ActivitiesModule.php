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
require_once "api/Activities/Activities.php";
require_once "api/Relation/Relation.php";

class ActivitiesModule extends Module {
  private $sel_options = array('All', 'My Groups', 'My Friends', 'My Activities');

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $outer_template = 'outer_public_side_module.tpl';

  /**
  * Subject => user_id of the user who is performing the action.
  */
  public $subject;

  /**
  * limit => number of entries to be shown in the activities module.
  */
  public $limit = 5; //5 will be shown by default.

  /**
  * Page type can have any string value which will define what kind of activity to be displayed.
  */
  public $page_type;

  function __construct() {
  global $app;
    parent::__construct();
    $this->html_block_id = 'ActivitiesModule';
    $this->title = __('Activity Feed');
    $this->selected = 0;
    $this->ajax_url = PA::$url . $app->current_route . "/module=ActivitiesModule&action=sort";
    $this->limit = 8;
  }

  function initializeModule($request_method, $request_data) {
    //Different activities will be displayed in the basis of page type or page_id
    $this->request_method = $request_method;
  if($request_method != 'AJAX') {
    if (empty($this->page_id)) return 'skip';
    switch ($this->page_id) {
      case PAGE_USER_PRIVATE:
        $this->page_type = 'user_private';
        $this->subject = PA::$login_uid;
        $this->title = __('My Activity Feeds');
      break;
      case PAGE_USER_PUBLIC:
        $this->page_type = 'user_public';
        $this->title = abbreviate_text((ucfirst(PA::$page_user->first_name).'\'s '), 14, 10);
        $this->title .= __('Activity Feeds');
        $this->subject = PA::$page_uid;
      break;
      case PAGE_GROUP:
      // case PAGE_GROUPS_HOME:
        $this->page_type = 'group';
        if(empty($request_data['gid'])) {
         if(PA::$login_uid) {
           $user_groups = Group::get_user_groups((int)PA::$login_uid, FALSE, 1, 1, 'created','DESC', 'public');
           if(count($user_groups) > 0) {
             $this->subject = $user_groups[0]['gid'];
           } else {
             return 'skip';
           }
         } else {
           return 'skip';
         }
        } else {
          $this->subject = $request_data['gid'];
        }
      break;
      default:
      //by default network activity will be shown on all the pages.
    }
   } else {  // NOTE: this is temporrary solution to keep module working on old pages
      $this->handleRequest($request_method, $request_data);
   }
  }

  function handleRequest($request_method, $request_data) {
    if(!empty($request_data['action']) && !empty($request_data['module']) && ($request_data['module'] == 'ActivitiesModule')) {
      $action = $request_data['action'];
      $class_name = get_class($this);
      switch($request_method) {
        case 'POST':
          $method_name = 'handlePOST_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
          }
        break;
        case 'GET':
          $method_name = 'handleGET_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
          }
        break;
        case 'AJAX':
          $method_name = 'handleAJAX_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
          }
        break;
      }
    }
  }

  private function handleAJAX_sort($request_data) {

    $types = array('network', 'group', 'user_friends', 'user_public');
    filter_all_post($request_data);
    if(isset($request_data['sort_by'])) {
       $this->selected = $request_data['sort_by'];
       $this->page_type = $types[$this->selected];
       switch ($this->page_type) {
         case 'user_public':
            $this->subject = PA::$login_uid;
         break;
         case 'group':
            if(empty($request_data['gid'])) {
              if(PA::$login_uid) {
                $user_groups = Group::get_user_groups((int)PA::$login_uid, FALSE, 1, 1, 'created','DESC', 'public');
                if(count($user_groups) > 0) {
//              echo "<pre>" . print_r($user_groups, 1) . "</pre>";
                  $this->subject = $user_groups[0]['gid'];
                } else {
                  print('<div style="margin:8px">No Feeds</div>');
                  exit;
                }
              } else {
                  print('<div style="margin:8px">No Feeds</div>');
                  exit;
              }
            } else {
               $this->subject = $request_data['gid'];
            }
         break;
         default:
          //by default network activity will be shown on all the pages.
       }

       $this->inner_HTML = $this->generate_inner_html ();
       print($this->inner_HTML);
    }
    exit;
  }

  function render() {
    $this->inner_HTML =$this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    $params = array('limit'=>$this->limit);
    $conditions = array();
    switch ($this->page_type) {
      case 'group':
        $params['activity_type'] = array('group_joined', 'group_image_upload', 'group_video_upload', 'group_audio_upload', 'group_post_a_blog', 'group_settings_updated');
        $conditions['object'] = $this->subject;
        $this->selected = 1;
      break;
      case 'user_public':
        $conditions['subject'] = $this->subject;
        $this->selected = 3;
      break;
      case 'user_private':
      case 'user_friends':
        $params['relation_ids'] = Relation::get_relations(PA::$login_uid, APPROVED, PA::$network_info->network_id);
        if(count($params['relation_ids']) == 0) {
          $this->do_skip = TRUE;
          return '<div style="margin:8px">No Feeds</div>';
        }
        $this->selected = 2;
      break;
    }
    $conditions['status'] = 'new';
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    $inner_html_gen = & new Template($tmp_file);

    $list = (Activities::get_activities($params, $conditions));

    if (empty($list)) {
      $this->do_skip = TRUE;
      return '<div style="margin:8px">No Feeds</div>';
    }
    $inner_html_gen->set('list', $list);
    $inner_html_gen->set('options', $this->sel_options);
    $inner_html_gen->set('selected_option', $this->selected);
    $inner_html_gen->set('ajax_url', $this->ajax_url);
    $inner_html_gen->set('block_name', $this->html_block_id);
    $inner_html_gen->set('request_method', $this->request_method);

    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>
