<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* As the name would imply, this module is used to create a group.  It also
* is used to modify a groups information, once it has been created.  It has a
* form with fields for name, category, tags, photo, description,
* and registration type.  This also handles the data that is submitted.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Tekriti Software
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/


require_once 'api/Category/Category.php';
require_once 'api/Entity/TypedGroupEntity.php';
require_once "api/Messaging/MessageDispatcher.class.php";

class AddGroupModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $uid;
  public $outer_template = 'outer_public_group_center_module.tpl';
  public $collection_id;
  public $groupname;
  public $body;
  public $access;
  public $reg_type;
  public $is_moderated;
  public $group_category;
  public $tag_entry;
  public $display_header_image;
  public $error_msg;
  public $group_photo;
  public $upfile;
  public $header_image;
  public $header_image_action;
  public $group_types;

  function __construct() {
    parent::__construct();
    $this->title = __('Create Group');
    $this->html_block_id = get_class($this);
    $this->id = 0;
  }

    /** !!
     * This is the function responsible for setting up the data to be displayed
     * in the form.  If this is a new group, it gives it a title and nothing
     * else.  If it is a group that is being modified, then it loads the old 
     * data and displays it.  When the form has been submitted, it fills in 
     * the form with the data from $global_form_data
     * @param string $error_message  The error from the submit.
     * @param array $request_data   Used to determine what is being created.
     */
  function load_data($error_msg='', $request_data=NULL) {
    global $global_form_data;
    $array_tmp = array();
    $this->categories = Category::build_root_list();
    if(is_array($this->categories)){
      foreach ($this->categories  as $category) {
        $array_tmp[] = array('category_id'=>$category->category_id,'name'=>$category->name);
      }
        $this->categories = $array_tmp;
    }
    if (!empty($error_msg)) {
      $this->error_msg = $error_msg;
    }
    if ($this->id == 0) {
      $this->title = __('Create Group');
    } else {
      $this->title = __('Change Group Settings');
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

    } // end else (existing group)

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

        if (!empty(PA::$config->useTypedGroups)) {
            require_once 'web/includes/classes/DynamicFormFields.php';
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
            $type = '';
            $params = array();
            if (!empty($request_data['entityType'])) $type = $request_data['entityType'];
            if (!empty($request_data['type'])) $type = $request_data['type'];
            if ($typedEntity = TypedGroupEntity::load_for_group($this->gid)) {
                $this->entity = $typedEntity;
                // get info about what profile fields this has
                $type = $this->entity->entity_type;
                $this->entity = $typedEntity;
                $params = $this->entity->attributes;
            } else if (!empty($type) && !empty($this->availTypes[$type])) {
							$params['type'] = $type;
            }
            $this->dynFields = new DynamicFormFields($params);
						$classname = ucfirst($type)."TypedGroupEntity";
						@include_once "api/Entity/$classname.php";
						if (class_exists($classname)) {
							$instance = new $classname();
						} else {
							// just get default
							$instance = new TypedGroupEntity();
						}
						$this->profilefields = $instance->get_profile_fields();
        }
    return;
  }

    /** !!
     * This sets the id and calls the {@link request_data() } method.
     * Then it responds to the post when the form is submitted with
     * {@link handlePOST() }.
     * @param string $request_method   Only POSTs are read at the moment.
     * @param array $request_data  Used to get the group id if possible.
     */
  function initializeModule($request_method, $request_data) {
       
        if (!empty($request_data['gid'])) {
            $this->id = $request_data['gid'];
        }


      $this->load_data(@$error_msg, $request_data);

        if ($request_method == "POST") {
            // standard Group info handling
            $this->handlePOST($request_data);
        }
    }
  
   /** !!
     * This is used to call the {@link generate_inner_html() } function,
     * and stitch its output with the outer html together using the 
     * {@link Module::render() } function.
     *
     * @return string $content  The full html to be displayed on the page
    */
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  
  /** !!
     * This calls in the template for the the form and sets the data to be displayed
     * from the data that was brought in by {@link load_data()}.
     *
     * @return string $inner_html  The module specific code to be shown on the page.
     */
  function generate_inner_html () {
    switch ( $this->mode ) {
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $inner_html_gen = & new Template($tmp_file, $this);

    $inner_html_gen->set('categories', $this->categories);
    $inner_html_gen->set('groupname', $this->groupname);
    $inner_html_gen->set('error_msg', $this->error_msg);
    $inner_html_gen->set('group_category', $this->group_category);
    $inner_html_gen->set('tag_entry', $this->tag_entry);
    $inner_html_gen->set('group_photo', $this->group_photo);
    $inner_html_gen->set('body', $this->body);
    $inner_html_gen->set('access', $this->access);
    $inner_html_gen->set('reg_type', $this->reg_type);
    $inner_html_gen->set('is_moderated', $this->is_moderated);
    $inner_html_gen->set('collection_id', $this->collection_id);
    $inner_html_gen->set('upfile', $this->upfile);
    $inner_html_gen->set('header_file', $this->header_image);
    $inner_html_gen->set('header_image_action', $this->header_image_action);
    $inner_html_gen->set('display_header_image', $this->display_header_image);
    $inner_html_gen->set('display_header_image', $this->display_header_image);
    $inner_html_gen->set('display_header_image', $this->display_header_image);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
  
  /** !!
     * This handles the data that is POSTed back to the page upon
     * submission of the form. There is a lot happening in here,
     * but it basically looks at the submitted data, figures out
     * what it is supposed to do with it (based on if the group is
     * being created or modified), then creates a new group or
     * updates the current data using the {@link handle_entity() } method.
     *
     * @param array $request_data  All of the data POSTed back to the form.
     */
    public function handlePOST($request_data) {
        require_once "web/includes/classes/file_uploader.php";
        require_once "api/Activities/Activities.php";
        require_once "api/api_constants.php";

        if ($request_data['addgroup']) {

            filter_all_post($request_data);

            $groupname = trim($request_data['groupname']);
            $body = trim($request_data['groupdesc']);
            $tag_entry = trim($request_data['group_tags']);
            $group_category = $request_data['group_category'];

            $header_image = NULL;
            $header_image_action = @$request_data['header_image_action'];
            $display_header_image = @$request_data['display_header_image'];
            $collection_id = NULL;
            $this->extra = NULL;

            if ($request_data['ccid']) {
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
            if($reg_type == REG_INVITE) {  // if reg. type = "Invite" access is PRIVATE
                $access = ACCESS_PRIVATE;
            }
            $is_moderated = 0; // is moderated is 0 means contents appear immediately
            $group_tags = $request_data['group_tags'];

            if(empty($request_data['groupname'])) {
                $error_msg = 90222;
            } else if (empty($group_category) && empty($error_msg)) {
                     $error_msg = 90224;
            } else if (empty($error_msg)) {

            try {
                if (empty($_FILES['groupphoto']['name'])) {
                    $upfile = $request_data['file'];
                } else {
                    $myUploadobj = new FileUploader; //creating instance of file.
                    $image_type = 'image';
                    $file = $myUploadobj->upload_file(PA::$upload_path, 'groupphoto', true, true, $image_type);
                    if ($file == false) {
                        throw new PAException(GROUP_PARAMETER_ERROR, __("File upload error: ").$myUploadobj->error);
                    }
                    $upfile = $file; $avatar_uploaded = TRUE;
                }
                $exception_message = NULL;
                    $result = Group::save_new_group($collection_id, $_SESSION['user']['id'], $groupname, $body, $upfile, $group_tags, $group_category, $access, $reg_type, $is_moderated, $header_image, $header_image_action, $display_header_image, $this->extra);
                    $ccid = $result;
                    $exception_message = 'Group creation failed: '.$result ;

                if (!is_numeric($result)) {
                    throw new PAException(GROUP_CREATION_FAILED, $exception_message);
                } else {
                    if (@$avatar_uploaded) Storage::link($upfile, array("role" => "avatar", "group" => (int)$result));
                    if (@$header_uploaded) Storage::link($header_image, array("role" => "header", "group" => (int)$result));
                        $this->gid = $this->id = $result;
                        if(empty($request_data['gid'])) {
                          $mail_type = $activity = 'group_created';
                          $act_text = ' created a new group';
                        } else {
                          $mail_type = $activity = 'group_settings_updated';
                          $act_text = ' changed group settings ';
                        }
                        $group = new Group();
                        $group->load((int)$this->gid);
                        PANotify::send($mail_type, PA::$network_info, PA::$login_user, $group); // notify network onwer

                        $_group_url = PA::$url . PA_ROUTE_GROUP . '/gid='.$result;
                        $group_owner = new User();
                        $group_owner->load((int)$_SESSION['user']['id']);
                        $activity_extra['info'] = ($group_owner->first_name . $act_text);
                        $activity_extra['group_name'] = $groupname;
                        $activity_extra['group_id'] = $result;
                        $activity_extra['group_url'] = $_group_url;
                        $extra = serialize($activity_extra);
                        $object = $result;
                        if($reg_type != REG_INVITE) {
                            Activities::save($group_owner->user_id, $activity, $object, $extra);
                        }
                        // if we reached here than the group is created

                     if (empty($request_data['gid'])) {  // when a new group is created
                        // so, we need to assign group admin role to group owner now:
                        $role_extra = array( 'user' => false, 'network' => false, 'groups' => array($this->gid) );
                        $user_roles[] = array('role_id' => GROUP_ADMIN_ROLE, 'extra' => serialize($role_extra));
                        $group_owner->set_user_role($user_roles);
                     }

                    if (!empty(PA::$config->useTypedGroups) && !empty($request_data['type'])) {
                        $this->gid = $this->id;
                        switch ($request_data['op']) {
                            case 'create_entity':
                            case 'edit_entity':
                                $this->handleEntity($request_data);
                            break;
                        }
                    }

                }
            } catch (PAException $e) {
                if ($e->code == GROUP_PARAMETER_ERROR) {
                    $error_msg = $e->message;
                    if (empty($groupname)) {
                        $error_msg = 90222;
                    } else if (empty($group_category)) {
                        $error_msg = 90224;
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
        $redirect_url = PA::$url . PA_ROUTE_GROUP;
        $query_str = "?gid=".@$result;
        set_web_variables($msg_array, $redirect_url, $query_str);

    }
  
  /** !!
     * This takes the data from the form and checks it
     * for the purposes of creating an entity, then 
     * syncs the data to the entity.
     * @param $request_data  The data to be added to the entity
     */
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
      $data['type'] = (!empty($this->entity_type)) ? $this->entity_type : $request_data['type'];
      $data['name'] = $request_data['groupname'];
      $data['group_id'] = $this->gid;
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
