<?php
require_once "api/Activities/Activities.php";
require_once "api/Messaging/MessageDispatcher.class.php";

class EditRelationModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  public $in_family = false;
  public $uid;

  /**
  * Class variable having relationship status of the user, if reciprocated relationship are enabled by the network admin
  */

  public $status;
  public $cache_id;
  public $delete_id;
  public $all_relations;
  public $relationship_level;
  public $user;
  public $relation_picture;
  public $login_name;
  public $rel_creater;
  public $relation;
  public $relation_uid;
  public $edit = FALSE;
  public $is_error = FALSE;

  function __construct() {
    parent::__construct();
  }

  public function initializeModule($request_method, $request_data) {
    if(empty(PA::$login_uid)) {
      header("Location: ". PA::$url .'/'.FILE_LOGIN.'?error=1&return='.urlencode($_SERVER['REQUEST_URI']));
    }
    $this->uid = PA::$login_uid;
    $this->relation_uid = $request_data['uid'];
    $this->user = PA::$user;
    $this->login_name = $this->user->login_name;
    $this->display_name = $this->user->display_name;
    $this->relation_picture = $this->user->picture;
    if ($this->uid == $request_data['uid']) {
      $message = __("You cannot make a relationship with yourself.");
      $this->is_error = TRUE;
      global $error_msg;
      $error_msg = $message;
    }
  }

  public function handleRequest($request_method, $request_data) {
    $msg = NULL;
    $action = (isset($request_data['do'])) ? $request_data['do'] : NULL;
    if($action == 'delete') {
      $this->delete_id = $this->relation_uid;
      Relation::delete_relation($this->uid, $this->delete_id, PA::$network_info->network_id);

      $this->cache_id = 'relation_private_'.$this->uid;
      CachedTemplate::invalidate_cache($this->cache_id);

      $this->cache_id = 'relation_public_'.$this->uid;
      CachedTemplate::invalidate_cache($this->cache_id);

      // invalidate cache of user who is being added in relation module
      $this->cache_id = 'in_relation_private_'.$this->delete_id;
      CachedTemplate::invalidate_cache($this->cache_id);

      $this->cache_id = 'in_relation_public_'.$this->delete_id;
      CachedTemplate::invalidate_cache($this->cache_id);
      header('Location:'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $this->delete_id.'&delete=1');
    }
    //getting relations of logged in user
    $this->all_relations = Relation::get_all_relations((int)$this->uid);
    $this->relationship_level = 2; //default relation level id is 2 for friend

    foreach($this->all_relations as $relation) {
      if($this->relation_uid ==  $relation['user_id']) {
        $this->relationship_level = $relation['relation_type_id'];
        $this->in_family = $relation['in_family'];
        $this->status = $relation['status'];
        if ($this->status == PENDING) {
          if (PA::$extra['reciprocated_relationship'] == NET_YES && ($action == 'add')) {
            $msg = sprintf(__('Your request for adding %s as a relation has already been sent'), $relation['display_name']);
          }
        }
      }
    }
    try {
      $this->user->load((int)$this->relation_uid);
      $this->title = __('Edit Relationship').' - '.$this->user->display_name; //title of the web page
      //picture and login relation
      $this->relation_picture = $this->user->picture;
      $this->login_name = $this->user->login_name;
      $this->display_name = $this->user->display_name;
    }
    catch (PAException $e) {
      $mesg = $e->message;
      $this->is_error = TRUE;
    }
    if(isset($request_data['submit'])) {
      $this->rel_creater = PA::$user;
      $this->relationship_level = $request_data['level'];
      if (PA::$extra['reciprocated_relationship'] == NET_YES) {
        if(Relation::getRelationData($this->relation_uid, $this->uid, PA::$network_info->network_id)) {
            Relation::update_relation_status($this->relation_uid, $this->uid, APPROVED, PA::$network_info->network_id);
            Relation::add_relation($this->uid, $this->relation_uid, $this->relationship_level, PA::$network_info->address, PA::$network_info->network_id, NULL, NULL, NULL, true, APPROVED);
            $relation_obj = Relation::getRelationData($this->relation_uid, $this->uid, PA::$network_info->network_id);
            PANotify::send("reciprocated_relation_estab", PA::$network_info, PA::$login_user, $relation_obj); // recipient is network owner

            $location = PA_ROUTE_USER_PRIVATE . '/msg=' . urlencode(__("The relationship request was approved."));
            header('Location:'.PA::$url.$location);
            exit;
        }
        $this->status = PENDING;
      } else {
        $this->status = APPROVED;
      }
      try {
        $this->relation = Relation::get_relation($this->rel_creater->user_id, $this->relation_uid, PA::$network_info->network_id);
        $this->edit = ($this->relation) ? TRUE : FALSE;
      }
      catch (PAException $e) {
        $this->edit = FALSE;
      }
      try {
        if (isset($request_data['in_family'])) {
          // If the user has checked the in_family checkbox.
          Relation::add_relation($this->uid, $this->relation_uid, $this->relationship_level, PA::$network_info->address, PA::$network_info->network_id, NULL, NULL, NULL, true, $this->status);

        } else {
          Relation::add_relation($this->uid, $this->relation_uid, $this->relationship_level, PA::$network_info->address, PA::$network_info->network_id, NULL, NULL, NULL, NULL, $this->status);
        }
        $this->user = PA::$user;     // relationship establisher image
        $relation_obj = Relation::getRelationData($this->uid, $this->relation_uid, PA::$network_info->network_id);
        if ($this->edit == FALSE) {
          if (PA::$extra['reciprocated_relationship'] == NET_YES) {
             PANotify::send("friend_request_sent", PA::$user, PA::$login_user, $relation_obj);
          } else {
              PANotify::send("relation_added", PA::$network_info, PA::$login_user, $relation_obj); // recipient is network owner
              PANotify::send("relationship_created_with_other_member", PA::$user, PA::$login_user, $relation_obj);
             //for rivers of people
              $activity = 'user_friend_added';//for rivers of people
              $activities_extra['info'] = ($this->display_name.' added new friend with id ='.$request_data['uid']);
              $extra = serialize($activities_extra);
              $object = $this->relation_uid;
              Activities::save(PA::$login_uid, $activity, $object, $extra);
          }
        }
        //invalidate cache of logged in user's relation module
        $this->cache_id = 'relation_private_'.$this->uid;
        CachedTemplate::invalidate_cache($this->cache_id);

        $this->cache_id = 'relation_public_'.$this->uid;
        CachedTemplate::invalidate_cache($this->cache_id);
        // invalidate cache of user who is being added in relation module
        $this->cache_id = 'in_relation_private_'.$this->relation_uid;
        CachedTemplate::invalidate_cache($this->cache_id);

        $this->cache_id = 'in_relation_public_'.$this->relation_uid;
        CachedTemplate::invalidate_cache($this->cache_id);
        if (PA::$extra['reciprocated_relationship'] == NET_NO) {
          if ($request_data['do']) {
            $location = PA_ROUTE_USER_PUBLIC . '/' . $this->relation_uid . "&msg=" . urlencode(__("Relationship estabilished."));
          }
        } else {
          $location = PA_ROUTE_USER_PRIVATE . '/msg_id='.urlencode(__("Your request has been sent for approval"));
        }
        header('Location:'.PA::$url.$location);
      }
      catch (PAException $e) {
        $message = $e->message;
      }
    }
    $msg_array = array();
    $msg_array['failure_msg'] = $msg;
    $msg_array['success_msg'] = NULL;
    $redirect_url = NULL;
    $query_str = NULL;
    set_web_variables($msg_array, $redirect_url, $query_str);
  }

  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html() {
    $template_file = NULL;
    switch($this->mode) {
      default:
        $template_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $template_obj = & new Template($template_file);
    $template_obj->set('uid', $this->uid);
    $template_obj->set('title', $this->title);
    $template_obj->set('login_name', $this->login_name);
    $template_obj->set('display_name', $this->display_name);
    $template_obj->set('relation_uid', $this->relation_uid);
    $template_obj->set('relationship_level',$this->relationship_level);
    $template_obj->set('relation_picture', $this->relation_picture);
    $template_obj->set('is_error', $this->is_error);
    $template_obj->set('in_family', $this->in_family);
    $template_obj->set('status', $this->status);
    $inner_html = $template_obj->fetch();
    return $inner_html;
  }
}
?>