<?php
require_once 'web/includes/classes/DynamicFormFields.php';
require_once 'api/Entity/FamilyTypedGroupEntity.php';
require_once 'api/Entity/TypedGroupEntityRelation.php';

class FamilyModule extends Module {
  public $module_type = 'group';
  public $module_placement = 'middle';


  function __construct() { 
    $this->outer_template = 'outer_public_center_module.tpl';
	  $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/typedgroup.tpl.php";
    $this->title = __('Family Profile');
    $this->html_block_id = get_class($this);
  }

  function handleRequest($request_method, $request_data) {
		if ($request_method == "POST") {
			if (!$this->shared_data['moderation_permissions']) { 
				$this->err = __("Sorry, you can't perform this action.");
				return;
			}
		}

    if(!empty($request_data['action']) && empty($request_data['module'])) { // when target module not defined!
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

  function initializeModule($request_method, $request_data) {
  	global $_PA;
    if (empty($this->shared_data['group_info'])) {
    	return 'skip';
    }
  	$this->gid = $this->shared_data['group_info']->collection_id;
  	$this->shared_data['member_type'] = $member_type = Group::get_user_type(PA::$login_uid, (int)$this->gid);
  	$this->is_member = ($member_type == MEMBER) ? TRUE : FALSE;
  	if ($member_type == OWNER) {
  		$this->is_member = TRUE;
      $this->is_admin = TRUE;
    }
    $acl = new Access();
    // check for moderation of group permissions
    $gp_access = $acl->acl_check( 'action', 'edit', 'users', $member_type, 'group', 'all' );
    if ((PA::$login_uid == SUPER_USER_ID) || ($member_type == 'moderator') ) {
    	$gp_access = 1;
		}
		$this->is_admin = $this->shared_data['moderation_permissions'] = $gp_access;
    $this->group_details = $this->shared_data['group_info'];

    if (!$this->is_member) {
      if((!empty($this->group_details) ? $this->group_details->reg_type : NULL) && $this->group_details->reg_type == REG_MODERATED) {
        $this->join_this_group_string = __('Request to join this family as');
      } else {
        $this->join_this_group_string = __('Join This family as');
      }
    } else {
    	// get the relationType for this user
    	list($relType, $relLabel) = TypedGroupEntityRelation::get_relation_to_group(PA::$login_uid, (int)$this->gid);
    	if (empty($relType)) {
    		$relType = 'member';
    		$relLabel = __('Member');
    	}
    	$this->relationType = $relType;
    	$this->relationTypeString = sprintf(__("You are a %s"), $relLabel);
    }

    $this->availTypes = TypedGroupEntity::get_avail_types();
    $this->selectTypes = array();
    foreach ($this->availTypes as $k=>$l) {
    	$this->selectTypes[] = array(
    		'label' => $l,
    		'value' => $k
    	);
    }

    // Do we already have a bound Entity?
    // we need to load the generic parent class, as we do noot yet know the type
    if ($typedEntity = TypedGroupEntity::load_for_group($this->gid)) {
		  $this->entity = $typedEntity;
		  // get info about what profile fields this has
		  $type = $this->entity->entity_type;
		  $classname = ucfirst($type)."TypedGroupEntity";
		  @include_once "api/Entity/$classname.php";
		  if (class_exists($classname)) {
		  	$instance = new $classname();
		  } else {
		  	// just get default
		  	$instance = new TypedGroupEntity();
		  }
		  $this->profilefields = $instance->get_profile_fields();
		  $this->availRelations = $instance->get_avail_relations();
		  
		  $this->selectRelations = array();
		  foreach ($this->availRelations as $k=>$l) {
		  	$this->selectRelations[] = array(
    			'label' => $l,
	    		'value' => $k
	    	);
  	  }

		  // does the admin want to edit it?
		  if(
		  	$this->shared_data['moderation_permissions']
		  	&&
		  	!empty($request_data['edit_enityprofile'])
		  ) {
			  $this->title = sprintf(__("Edit %s profile"), $typedEntity->attributes['name']['value']);
		  	$this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/edit_typedgroup_profile.tpl.php";
		  	// load into form
		  	$this->dynFields = new DynamicFormFields($this->entity->attributes);
			} else {
				// display only
				$this->title = sprintf(__("%s profile"), $typedEntity->attributes['name']['value']);
				$this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/typedgroup_profile.tpl.php";
			}
    } else {
    	return 'skip';
    	/*
			// moderators only
			if(!$this->shared_data['moderation_permissions']) {
				return 'skip';
			}
			*/
    }
  	
  	switch($this->column) {
    	case 'middle':
      break;
      case 'left':
      case 'right':
      default:
      break;
    }
  }
  
  function render() {
    $this->inner_HTML = $this->generate_inner_html();

    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html() {
    $inner_html_gen = & new Template($this->inner_template, $this);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
  
  private function handleCreate($request_data) {
  	$this->err = '';
  	$this->entity_type = @$request_data['type'];
  	$this->entity_name = @$request_data['name'];
  	if (empty($this->entity_type)) {
  		$this->err .= __("Please select a type.")."<br/>";
  	}
  	if (empty($this->entity_name)) {
  		$this->err .= __("Please supply a name.")."<br/>";
  	}
  	if (empty($this->err)) {
  		// create it
  		TypedGroupEntity::sync(
  			array(
  				'type' => $this->entity_type,
  				'name' => $this->entity_name,
  				'group_id' => $request_data['gid'],
  			)
  		);
  		// redirect
  		$url = PA::$url.PA_ROUTE_GROUP."?gid=".$this->gid."&msg=".urlencode(__("TypedEntity associated successfully."));
  		$this->controller->redirect($url);

  	}
  }
  
  private function handleEdit($request_data) {
  	$this->err = '';
  	$data = $this->filter($request_data);
		// handle photo upload
    if (!empty($_FILES)) {
      foreach ($_FILES as $field_name => $file_info) {
        if (!empty($file_info['name'])) {
          $uploadfile = PA::$upload_path.basename($_FILES[$field_name]['name']);
          $myUploadobj = new FileUploader; 
          $file = $myUploadobj->upload_file(PA::$upload_path, $field_name, true, true, 'image');
          if ($file == false) {
            $msg = $myUploadobj->error;
            $this->err .= sprintf(__('Please upload a valid Game Image in %s'), ucfirst($field_name))."<br/>";
            continue;
          } else {
            Storage::link($file, array("role" => "game_image", "user" => PA::$login_user->user_id));
            $data[$field_name] = $file;
          } 
        } else {
        	if (!empty($this->entity->attributes[$field_name])) {
        		$data[$field_name] = $this->entity->attributes[$field_name];
        	}
        } 
      }
    }
  	if (empty($data['name'])) {
  		$this->err .= __("Please supply a name.")."<br/>";
  	}
  	if (empty($this->err)) {
  		// sync it
  		TypedGroupEntity::sync($data);
  	}
	}

  private function filter($request_data) {
  	$filter = array('PHPSESSID', 'pa_login', 'uid', 'submit', 'action', 'page_id', 'op');
  	foreach($filter as $f) {
  		unset($request_data[$f]);
  	}
  	return $request_data;
  }

  private function handleGET_update($request_data) {
    global $_PA, $error_msg;
    if (PA::$login_uid && !empty($this->shared_data['group_info'])) {
      $group = $this->shared_data['group_info'];
      if (Group::member_exists((int)$request_data['gid'], (int)PA::$login_uid)) {
        // deal with TypedGroup Relations
        if (!empty($_PA->useTypedGroups)) {
            require_once("api/Entity/TypedGroupEntityRelation.php");
            $uid = PA::$login_uid;
            $gid = $group->collection_id;
            $type = @$request_data['relation'];
            try {
                TypedGroupEntityRelation::set_relation($uid, $gid, $type);
                $error_msg = sprintf(__("You have updated your relation to \"%s\" successfully."), stripslashes($group->title));
            } catch (PAException $e) {
                $error_msg = $e->getMessage();
            }
        }
      }
        }
    }
  private function handleGET_join($request_data) {
		require_once "api/Activities/Activities.php";
    global $_PA, $error_msg;
    if (PA::$login_uid && !empty($this->shared_data['group_info'])) {
      $group = $this->shared_data['group_info'];
      if (!Group::member_exists((int)$request_data['gid'], (int)PA::$login_uid)) {
        $user  = PA::$login_user;
        $login_name = $user->login_name;
        $group_invitation_id = (!empty($request_data['GInvID'])) ? $request_data['GInvID'] : null;
        try {
          $user_joined = $group->join((int)PA::$login_uid, $user->email, $group_invitation_id);
          // for rivers of people
          $activity = 'group_joined';//for rivers of people
          $activity_extra['info'] = ($login_name.' joined a new group');
          $activity_extra['group_name'] = $group->title;
          $activity_extra['group_id'] = $request_data['gid'];
          $extra = serialize($activity_extra);
          $object = $request_data['gid'];
          Activities::save(PA::$login_uid, $activity, $object, $extra);
          if (!empty($group_invitation_id)) { // if group is joined through group invitation
            $Ginv = Invitation::load($group_invitation_id);
            $gid = $Ginv->inv_collection_id;
            $user_obj = new User();
            $user_obj->load((int)$Ginv->user_id);
            $group = ContentCollection::load_collection((int)$gid, $Ginv->user_id);
            $user_type = Group::get_user_type($user_obj->user_id, $gid);
            if($group->reg_type == REG_MODERATED && $user_type == OWNER) {
              $group->collection_id = $gid;
              $group->approve(PA::$login_uid, 'user');
            }
            $user_accepting_ginv_obj = new User();
            $user_accepting_ginv_obj->load((int)PA::$login_uid);
            $Ginv->inv_user_id = PA::$login_uid;
            PANotify::send("invite_accept_group", $user_obj, $user_accepting_ginv_obj, $Ginv);
          }
        } catch (PAException $e) {
          if ($e->code == GROUP_NOT_INVITED) {
            $error_msg = $e->message;
//          header("Location: groups_home.php");
//          exit;
          }
          $error_msg = $e->message;
        }
      } else {
        $error_msg = sprintf(__("You are already a member of \"%s\""), stripslashes($group->title));
      }

      if (@$user_joined) {
        // deal with TypedGroup Relations
        if (!empty($_PA->useTypedGroups)) {
            require_once("api/Entity/TypedGroupEntityRelation.php");
            $uid = PA::$login_uid;
            $gid = $group->collection_id;
            $type = @$request_data['relation'];
            try {
                TypedGroupEntityRelation::set_relation($uid, $gid, $type);
            } catch (PAException $e) {
                $error_msg = $e->getMessage();
            }
        }

        $gid = (int)$request_data['gid'];
        if(!(Group::member_exists($gid, (int)PA::$login_uid)) && $group->reg_type == REG_MODERATED) { // if it is a manual join not an invited join
          $mail_type = 'group_join_request';
          $error_msg = sprintf(__("Your request to join \"%s\" has been submitted to the owner of the group."), stripslashes($group->title));
        } else {
          $mail_type = 'group_join';
          $error_msg = sprintf(__("You have joined \"%s\" successfully."), stripslashes($group->title));
        }
        PANotify::send($mail_type, $group, PA::$login_user, array());
      }
    } else {
        // redirect to login
        $msg = urlencode(__("You need to be logged in to join a group."));
        header("Location: ". PA::$url ."/login.php?".$msg."&return=".urlencode($_SERVER['REDIRECT_URL']
          .'?'. @$_SERVER['REDIRECT_QUERY_STRING']));

    }
  }

  private function handleGET_leave($request_data) {
    global $_PA, $error_msg;
    if(PA::$login_uid && !empty($this->shared_data['group_info']) && !empty($this->shared_data['login_user'])) {
      $group = $this->shared_data['group_info'];
      $user  = $this->shared_data['login_user'];
      $user_type = Group::get_user_type(PA::$login_uid, (int)$request_data['gid']);
      if((Group::is_admin((int)$request_data['gid'], (int)PA::$login_uid)) && ($user_type == OWNER)) { // admin can leave a group but owner can't
        $error_msg = __("You can't leave your own group.");
      } else if (Group::member_exists((int)$request_data['gid'], (int)PA::$login_uid)) {
        try {
          $x = $group->leave((int)PA::$login_uid);
        } catch (PAException $e) {
          $error_msg = "Operation failed (".$e->message."). Please try again";
        }
      } else {
        $error_msg = sprintf(__("You are not member of \"%s\"."), stripslashes($group->title));
      }
      if (!empty($_PA->useTypedGroups)) {
        require_once 'api/Entity/TypedGroupEntityRelation.php';
        TypedGroupEntityRelation::delete_relation(PA::$login_uid, $request_data['gid'], PA::$network_info->network_id);
      }
      if(@$x) {
        $error_msg = sprintf(__("You have left \"%s\" successfully."), stripslashes($group->title));
      }
    }
  }

  private function handleGET_delete($request_data) {
    global $_PA, $error_msg;
    if(PA::$login_uid && !empty($this->shared_data['group_info']) && !empty($this->shared_data['login_user']) && ((@$_POST['content_type'] != 'media'))) {
      $group = $this->shared_data['group_info'];
      $user  = $this->shared_data['login_user'];
      if(Group::is_admin((int)$request_data['gid'], (int)PA::$login_uid)) {
        $group->delete();
        // Deleting all the activities of this group from activities table for rivers of people module
        Activities::delete_for_group($request_data['gid']);

        if (!empty($_PA->useTypedGroups)) {
            require_once 'api/Entity/TypedGroupEntity.php';
            require_once("api/Entity/TypedGroupEntityRelation.php");
            TypedGroupEntityRelation::delete_all_relations($request_data['gid']);
            TypedGroupEntity::delete_for_group($request_data['gid']);
        }

        $this->controller->redirect(PA::$url . PA_ROUTE_GROUPS . "?error_msg=" . __("Group sucessfully deleted."));
      }
    }
  }
}
?>