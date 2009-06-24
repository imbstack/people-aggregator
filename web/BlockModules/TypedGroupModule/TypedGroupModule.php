<?php
require_once 'web/includes/classes/DynamicFormFields.php';
require_once 'api/Entity/TypedGroupEntity.php';
require_once 'api/Entity/TypedGroupEntityRelation.php';
require_once 'web/includes/classes/file_uploader.php';

class TypedGroupModule extends Module {
  public $module_type = 'group';
  public $module_placement = 'middle';


  function __construct() { 
    $this->outer_template = 'outer_public_center_module.tpl';
	  $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/typedgroup.tpl.php";
    $this->title = __('Typed Group Settings');
    $this->html_block_id = 'TypedGroupModule';
  }

  function handleRequest($request_method, $request_data) {
		if ($request_method == "POST") {
			if (!$this->shared_data['moderation_permissions']) { 
				$this->err = __("Sorry, you can't perform this action.");
				return;
			}
			/* now handled in AddGroupModule
			switch ($request_data['op']) {
				case 'create_entity':
					$this->handleCreate($request_data);
				break;
				case 'edit_entity':
					$this->handleEdit($request_data);
				break;
			}
			// make sure we display the latest version
			if ($typedEntity = TypedGroupEntity::load_for_group($this->gid)) {
				$this->entity = $typedEntity;
			}
			*/
		}
  }

  function initializeModule($request_method, $request_data) {
  	global $_PA;
    if (empty($this->shared_data['group_info'])) {
    	return 'skip';
    }
  	$this->gid = $this->shared_data['group_info']->collection_id;
    if (empty($_PA->useTypedGroups)) {
  		return 'skip';
  	}
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
        $this->join_this_group_string = __('Request to join this group as');
      } else {
        $this->join_this_group_string = __('Join This Group as');
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


}
?>