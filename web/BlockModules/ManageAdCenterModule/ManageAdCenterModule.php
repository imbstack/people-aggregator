<?php
require_once "api/Validation/Validation.php";
require_once "web/includes/classes/Pagination.php";
require_once "web/includes/classes/file_uploader.php";
require_once "api/Permissions/PermissionsHandler.class.php";

class ManageAdCenterModule extends Module {

  public $module_type = 'system';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

  function __construct() {
    $this->title = __("Manage Ad Center");
    $this->html_block_id = 'ManageAdCenterModule';
    $this->main_block_id = NULL;
    $this->Paging["show"] = 5;
    $this->Paging["page"] = 1;
    $this->edit = false;
    $this->form_data = NULL;
  }

	function initializeModule($request_method, $request_data) {
		global $error_msg;
		$error = false;
		$msg = array();
		$form_data = NULL;
		$edit = false;
		$message = NULL;
		
		// check oermissions!
		$user_may = false;
		$user_may = PermissionsHandler::can_user(PA::$login_uid, array('permissions' => 'manage_ads'));
		
		// check for manageads of group permissions
		if (!empty($_REQUEST['gid']) && !$user_may) {
			// we do this checl only if the user is not already permitted to manage ads
			$gp_access = PermissionsHandler::can_group_user(PA::$login_uid, $_REQUEST['gid'], array('permissions' => 'manage_ads'));
			$user_may = $gp_access;
		}
		
		if (!$user_may) {
			$error_msg = __("You do not have permission to manage Ads.");
			return "skip";
		}
		
		// aging
		if (!empty($request_data['page'])) {
			$this->Paging["page"] = (int)$request_data['page'];
		}

		if ((!empty($request_data['action'])) && $request_data['action'] == 'edit' && !empty($request_data['ad_id'])) {
			$edit = TRUE;
			$res = Advertisement::get($params = NULL, $condition = array('ad_id' => ((int)$request_data['ad_id'])));
			if (!empty($res)) {
				$form_data['ad_id'] = $res[0]->ad_id;
				$form_data['ad_image'] = $res[0]->ad_image;
				$form_data['ad_script'] = $res[0]->ad_script;
				$form_data['ad_url'] = $res[0]->url;
				$form_data['ad_title'] = $res[0]->title;
				$form_data['ad_description'] = $res[0]->description;
				$form_data['ad_page_id'] = $res[0]->page_id;
				$form_data['orientation'] = $res[0]->orientation;
				$form_data['created'] = $res[0]->created;
			}
		} else if ((!empty($request_data['action'])) && $request_data['action'] == 'delete' && !empty($request_data['ad_id'])) {
				if (!empty($request_data['ad_id'])) {
					try {
						Advertisement::delete((int)$request_data['ad_id']);
						$error_msg = 19013;
					} catch (PAException $e) {
						$msg[] = $e->message;
					}
				}
		} else if (!empty($request_data['action']) && !empty($request_data['ad_id'])) {
			$update = false;
			switch ($request_data['action']) {
				case 'disable':
					$field_value = DELETED;
					$msg_id = 19010;
					$update = true;
				break;
				case 'enable':
					$field_value = ACTIVE;
					 $msg_id = 19011;
					$update = true;
				break;
			}
			if ($update) {
				$update_fields = array('is_active' => $field_value);
				$condition = array('ad_id' => $request_data['ad_id']);
				try {
					Advertisement::update($update_fields, $condition);
					$error_msg = $msg_id;
				} catch (PAException $e) {
					$msg[] = $e->message;
				}
			}
		}
		
		$advertisement = new Advertisement();
		
		if (!$error && ($request_method=='POST') && $request_data['btn_apply_name']) { // if page is submitted
			if (!empty($request_data['ad_id'])) {
				$advertisement->ad_id = $request_data['ad_id'];
				$advertisement->created = $request_data['created'];
				$msg_id = 19007;
			} else {
				$msg_id = 19008;
				$advertisement->created = time();
			}
			if (!empty($_FILES['ad_image']['name'])) {
				global $uploaddir;
				$filename = $_FILES['ad_image']['name'];
				$uploadfile = $uploaddir.basename($filename);
				$myUploadobj = new FileUploader;
				$file = $myUploadobj->upload_file($uploaddir, 'ad_image', TRUE, TRUE, 'image');
				$advertisement->ad_image = $form_data['ad_image'] = $file;
				if ($file == FALSE) {
					$error = TRUE;
					$msg[] = $myUploadobj->error;
				}
			} else {
				if (!empty($request_data['ad_id'])) {
					$advertisement->ad_image = $request_data['edit_image'];
				}
			}
			if (empty($request_data['ad_url']) && empty($request_data['ad_script'])) {
				$error = TRUE;
				$msg[] = MessagesHandler::get_message(19012);
			}
			if (!empty($request_data['ad_url'])) { // if url is given then validate
				$request_data['ad_url'] = validate_url($request_data['ad_url']);
				if(!Validation::isValidURL($request_data['ad_url']) ) {
					$error = TRUE;
					$msg[] = MessagesHandler::get_message(19009);
				}
			}
			$advertisement->user_id = PA::$login_uid;
			$advertisement->url = $form_data['ad_url'] = $request_data['ad_url'];
			$advertisement->ad_script = $form_data['ad_script'] = $request_data['ad_script'];
			$advertisement->title = $form_data['ad_title'] = $request_data['ad_title'];
			$advertisement->description = $form_data['ad_description'] = $request_data['ad_description'];
			$advertisement->page_id = $form_data['ad_page_id'] = $request_data['ad_page_id'];
			$advertisement->orientation = $form_data['orientation'] = $request_data['x_loc'].','.$request_data['y_loc'];
			$advertisement->changed = time();
			$advertisement->is_active = ACTIVE;
			if (!empty($_REQUEST['gid'])) {
				$advertisement->group_id = (int)$_REQUEST['gid'];
			}
			
			if (!$error) {
				try {
					$ad_id = $advertisement->save();
					if (!empty($file)) {
						Storage::link($file, array("role" => "ad", "ad" => $ad_id));
					}
					$error_msg = $msg_id;
				} catch (PAException $e) {
					$error_msg = $e->message;
				}
			} else {
				$error_msg = implode("<br/>", $msg);
			}
		}
		$this->form_data = $form_data;
		$this->edit = $edit;
		$this->message = $message;
	}
	
	
  // This function renders ManageAdCenterModule
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  // This function returns all the ads within the network
  function get_links() {
    $condition = array();
    if ($this->mode == 'textpad') {
      $condition['type'] = 'textpad';
    } else {
      $condition['type'] = 'ad';
      if (!empty($_REQUEST['gid'])) {
      	$condition['group_id'] = (int)$_REQUEST['gid'];
      } else {
      	$condition['group_id'] = NULL;
      }
    }
    $this->Paging["count"] = Advertisement::get(array('cnt' => TRUE), $condition);
    $params = array('cnt' => FALSE,
                    'show' => $this->Paging["show"],
                    'page' => $this->Paging["page"],
                    'sort_by' => 'changed',
                    'direction' => 'DESC');
    $ads_data = Advertisement::get($params, $condition);
    return $ads_data;
  }

  function generate_inner_html() {
    $links = $this->get_links();
    // set links for pagination
    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);
    $this->page_first = $Pagination->getFirstPage();
    $this->page_last = $Pagination->getLastPage();
    $this->page_links = $Pagination->getPageLinks();
    $tmp_file = NULL;
    $config_link_page = NULL;
    switch($this->mode) {
      case 'textpad':
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/textpad.tpl';
        $config_link_page = 'manage_textpads';
      break;
      default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
        $config_link_page = 'manage_ad_center';
    }
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('links', $links);
    $inner_html_gen->set('edit', $this->edit);
    $inner_html_gen->set('form_data', $this->form_data);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html_gen->set('config_navigation_url',
                       network_config_navigation($config_link_page));
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>