<?php
require_once 'api/Entity/FamilyTypedGroupEntity.php';
require_once "api/Messaging/MessageDispatcher.class.php";
require_once 'web/includes/classes/DynamicFormFields.php';

class EditFamilyModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';

  function __construct() {

    $this->title = __('Create Family');
    $this->html_block_id = get_class($this);
    $this->id = 0;
  }

  function load_data($error_msg='', $request_data=NULL) {
    global $_PA, $global_form_data;
    $array_tmp = array();
    if ($this->id > 0) {
      $this->title = __('Change Family Settings');
      $group = ContentCollection::load_collection((int)$this->id, PA::$login_uid);
      $group_tags = Tag::load_tags_for_content_collection((int)$this->id);
      $this->collection_id = (int)$this->id;
      $this->groupname = stripslashes($group->title);
      $this->body = stripslashes($group->description);
      $this->access = $group->access_type;
      $this->reg_type = $group->reg_type;
      $this->is_moderated = $group->is_moderated;
      $this->group_category = $group->category_id;
      $this->display_header_image=$group->display_header_image;
      $this->group_type = $group->group_type;
      $this->tag_entry = NULL;
      if (count($group_tags)) {
        foreach ($group_tags as $tag) {
          $out[] = $tag['name'];
        }
        $this->tag_entry = implode(', ',$out);
      }
      if ($group->picture) {
        $this->group_photo = $group->picture;
        $this->upfile = $group->picture;
      }
       if ($group->header_image) {
        $this->header_image = $group->header_image;
        $this->upfile = $group->picture;
        $this->header_image_action=$group->header_image_action;
      }

    } // end (existing group)

    if ( !empty($global_form_data['addgroup']) ) {
      $this->collection_id = (int)$this->id;
      $this->groupname     =   $global_form_data['groupname'];
      $this->body          =   $global_form_data['groupdesc'];
      $this->access        =   @$global_form_data['groupaccess'];
      $this->reg_type      =   $global_form_data['reg_type'];
      $this->is_moderated  =   @$global_form_data['is_mod'];
      $this->group_category=   $global_form_data['group_category'];
      $this->tag_entry     =   $global_form_data['group_tags'];
      $this->display_header_image = @$global_form_data['display_header_image'];
      $this->group_type = @$global_form_data['group_type'];
    }
    $this->tag_entry = str_replace('"','&quot;',@$this->tag_entry);
		$this->gid = $this->id;
		$this->availTypes = TypedGroupEntity::get_avail_types();
		$this->selectTypes = array();
		foreach ($this->availTypes as $k=>$l) {
				$this->selectTypes[] = array(
						'label' => $l,
						'value' => $k
				);
		}
		$this->entity = NULL;
		$type = 'family';
		$params = array();
		if ($typedEntity = TypedGroupEntity::load_for_group($this->gid)) {
				$this->entity = $typedEntity;
				// get info about what profile fields this has
				$type = $this->entity->entity_type;
				$this->entity = $typedEntity;
				$params = $this->entity->attributes;
		} 
		$this->dynFields = new DynamicFormFields($params);
		$classname = ucfirst($type)."TypedGroupEntity";
		$instance = new $classname();
		$this->profilefields = $instance->get_profile_fields();

    return;
  }

  function initializeModule($request_method, $request_data) {
      global $_PA;
        if (!empty($request_data['gid'])) {
            $this->id = $request_data['gid'];
        }


      $this->load_data(@$error_msg, $request_data);

        if ($request_method == "POST") {
            // standard Group info handling
            $this->handlePOST($request_data);
        }
    }

  function render() {
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/edit_family.php';
    $inner_html_gen = & new Template($tmp_file, $this);

    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

    public function handlePOST($request_data) {
        global  $_PA, $uploaddir, $network_info;
        require_once "web/includes/classes/file_uploader.php";
        require_once "api/Activities/Activities.php";
        require_once "api/api_constants.php";

        if ($request_data['addgroup']) {

            filter_all_post($request_data);

            $groupname = trim($request_data['groupname']);
            $body = trim($request_data['groupdesc']);
            $tag_entry = trim($request_data['group_tags']);
            $group_category = 1; // diesn't really matter as Familes don't have categories

            $header_image = NULL;
            $header_image_action = @$request_data['header_image_action'];
            $display_header_image = @$request_data['display_header_image'];
            $collection_id = NULL;
            $this->extra = NULL;
// echo "<pre>".print_r($request_data,1)."</pre>";exit;
            if (!empty($request_data['ccid'])) {
                $collection_id = (int)$request_data['ccid'];
                $group = new Group();
                $group->load($collection_id);
                // preserve group info we are not editing in this module
                // load group extra
                $extra = $group->extra;
                if (!empty($extra)) {
                    $this->extra = unserialize($extra);
                }
                $header_image = $group->header_image;
                $header_image_action = $group->header_image_action;
                $display_header_image = $group->display_header_image;
            }

            $access = 0; // default access is 0 means public
            $reg_type = $request_data['reg_type'];
            if ($reg_type == REG_INVITE) {  
            	// if reg. type = "Invite" access is PRIVATE
              $access = ACCESS_PRIVATE;
            }
            $is_moderated = 0; 
            // is moderated is 0 means contents appear immediately
            $group_tags = $request_data['group_tags'];

            if (empty($request_data['groupname'])) {
                $error_msg = __("Please supply a name for the Family.");
            } else if (empty($error_msg)) {
            	try {
                if (empty($_FILES['groupphoto']['name'])) {
                    $upfile = $request_data['file'];
                } else {
                    $myUploadobj = new FileUploader; //creating instance of file.
                    $image_type = 'image';
                    $file = $myUploadobj->upload_file($uploaddir, 'groupphoto', true, true, $image_type);
                    if ($file == false) {
                        throw new PAException(GROUP_PARAMETER_ERROR, __("File upload error: ").$myUploadobj->error);
                    }
                    $upfile = $file; $avatar_uploaded = TRUE;
                }
                $result = 
                	Group::save_new_group($collection_id, PA::$login_uid, $groupname, $body, $upfile, $group_tags, $group_category, $access, $reg_type, $is_moderated, $header_image, $header_image_action, $display_header_image, $this->extra);
               $ccid = $result;
               if (!is_numeric($result)) {
               	throw new PAException(GROUP_CREATION_FAILED, 'Group creation failed: '.$result);
              } else {
              	if (@$avatar_uploaded) Storage::link($upfile, array("role" => "avatar", "group" => (int)$result));
                if (@$header_uploaded) Storage::link($header_image, array("role" => "header", "group" => (int)$result));
                $this->gid = $this->id = $result;
                
                // notification if this was creating a new Family
                if (empty($request_data['gid'])) {
                	$mail_type = $activity = 'group_created';
                  $act_text = ' created a new group';
                } else {
                	$mail_type = $activity = 'group_settings_updated';
                  $act_text = ' changed group settings ';
                }
                $group = new Group();
                $group->load((int)$this->gid);
                PANotify::send($mail_type, PA::$network_info, PA::$login_user, $group); // notify network onwer
                $_group_url = PA::$url . PA_ROUTE_FAMILY . '/gid='.$result;
                $group_owner = PA::$login_user;;
                $activity_extra['info'] = ($group_owner->first_name . $act_text);
                $activity_extra['group_name'] = $groupname;
                $activity_extra['group_id'] = $result;
                $activity_extra['group_url'] = $_group_url;
                $extra = serialize($activity_extra);
                $object = $result;
                if ($reg_type != REG_INVITE) {
                	Activities::save($group_owner->user_id, $activity, $object, $extra);
                }
                // if we reached here than the group is created
                if (empty($request_data['gid'])) {  
                	// when a new group is created
                  // we need to assign group admin role to group owner now:
                  $role_extra = array( 'user' => false, 'network' => false, 'groups' => array($this->gid) );
                        $user_roles[] = array('role_id' => GROUP_ADMIN_ROLE, 'extra' => serialize($role_extra));
                        $group_owner->set_user_role($user_roles);
                }
                
                $this->gid = $this->id;
                $this->handleEntity($request_data);
              }
            } catch (PAException $e) {
                if ($e->code == GROUP_PARAMETER_ERROR) {
                    $error_msg = $e->message;
                    if (empty($groupname)) {
                        $error_msg = __("Please supply a name for the Family.");
                    }
                } else {
                    $error_msg = $e->message;
                }
            }
         }
        }//if form is posted


        $msg_array = array();
        $msg_array['failure_msg'] = @$error_msg;
        $msg_array['success_msg'] = (!empty($this->id)) ? 90231:90221;
        $redirect_url = PA::$url . PA_ROUTE_FAMILY;
        $query_str = "?gid=".@$result;
        set_web_variables($msg_array, $redirect_url, $query_str);

    }

  private function handleEntity($request_data) {
      $this->err = '';
      // $data = $this->filter($request_data);
      $data = array();
      // use the profile_fields object for some processing
      foreach ($this->profilefields as $i=>$d) {
      	$k = $d['name'];
				switch ($d['type']) {
					case 'dateselect':
						$day = @$request_data[$k.'_day'];
						$month = @$request_data[$k.'_month'];
						$year = @$request_data[$k.'_year'];
						if ($day && $month && $year) {
							$data[$k.'_day'] = $day;
							$data[$k.'_month'] = $month;
							$data[$k.'_year'] = $year;
							$data[$k] = sprintf("%04d-%02d-%02d", $year, $month, $day);
					}
					break;
					default:
						if (!empty($request_data[$k])) $data[$k] = $request_data[$k];
					break;
				}
      }
      $data['type'] = 'family';
      $data['name'] = $request_data['groupname'];
      $data['group_id'] = $this->gid;
      if (empty($data['name'])) {
          $this->err .= __("Please supply a name.")."<br/>";
      }
      if (empty($this->err)) {
          // sync it
          FamilyTypedGroupEntity::sync($data);
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
