<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* EditProfileModule.php is a part of PeopleAggregator.
* This file manages the various edit pages in a user's profile.
*  The different types of profile (basic, general, personal, etc) are all here,
*  and there are separate functions below to handle their data.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau?
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
require_once 'web/includes/classes/file_uploader.php';
require_once PA::$blockmodule_path.'/EditProfileModule/DynamicProfile.php';

class EditProfileModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_edit_profile_module.tpl';
  public $blogsetting_status;

  //All the valid profile types. If we have to add new profile type, then profile type should be entered here also.
  public $valid_profile_types = array('basic', 'general', 'personal', 'professional', 'notifications', 'delete_account');

  //Profile type currently under view
  public $profile_type;

  //User profile information for the particular section or type under view.
  public $section_info;

  //User id of the user who is editing the profile information.
  //By Default it will be login uid which will be set in constructor, but can be set from outside of this
  //class like for the cases where user having role of editing other's profile.
  public $uid;

  //User object having the user information
  public $user_info;

  function __construct() {
     
    parent::__construct();
    $this->main_block_id = "mod_edit_profile";
    $this->block_type = 'EditProfile';

    if (empty(PA::$config->simple['omit_advacedprofile'])) {
    //This is not simple PA. Add the advanced profile types to valid types.
      array_push($this->valid_profile_types, 'export');
    }
    //by default basic profile will be shown
    $this->profile_type = 'basic';
    $this->uid = PA::$login_uid;
    $this->user_info = PA::$login_user;
  }
    /** !!
    * Initalizes some things, shuffles data from $request_data to $this
    * @param array $request_method Provided method of data submission.
    * @param array $request_data POST/GET data. In here must be ['type'], which is like (basic, genera$
    */
  public function initializeModule($request_method, $request_data) {
    if (empty($this->uid)) return 'skip';

    if (!empty($request_data['type']) && in_array($request_data['type'], $this->valid_profile_types)) {
      $this->profile_type = $request_data['type'];
    }

    $this->section_info = $this->loadSection($this->profile_type, $this->uid);
    $this->section_info = $this->santitizeSectionInfo($this->section_info);
    $this->request_data = $request_data;
  }
    /** !!
    * Invokes {@see load_user_profile()} to load the current user profile.
    * @param string $profile_type Must be one of the profile types (Basic, general, etc.)
    * @param int $user_id The ID of the user of which to fetch the profile.
    * @return array User profile as loaded by {@see load_user_profile()} 
    */
  public function loadSection($profile_type, $user_id) {
    $section = NULL;
    switch ($profile_type) {
      case 'basic':
        $section = BASIC;
      break;
      case 'general':
        $section = GENERAL;
      break;
      case 'personal':
        $section = PERSONAL;
      break;
      case 'professional':
        $section = PROFESSIONAL;
      break;
    }
    return (!is_null($section)) ? User::load_user_profile($user_id, $user_id, $section) : FALSE;
  }
    /** !!
    * Returns a new section_info which has the old section_info
    * as well as permission data.
    * @param array $section_info Collection like [0]=>array(['name']=>'',['value']=>mixed,['perm']=>'')
    * @return array Collection like [0]=>array($name=>value,$name."_perm"=>$perm)
    */
  public function santitizeSectionInfo($section_info) {
    $sanitized_section_info = array();
    $count = count($section_info);
    for ($counter = 0; $counter < $count; $counter++) {
      $field_name = $section_info[$counter]['name'];
      $field_value = $section_info[$counter]['value'];
      $permission_name = $field_name."_perm";
      $permission_value = $section_info[$counter]['perm'];
      $sanitized_section_info[$field_name] = $field_value;
      $sanitized_section_info[$permission_name] = $permission_value;
    }
    return $sanitized_section_info;
  }
    /** !!
    * Upon post, this method calls a method defined later in this file,
    *  one with a name like {$section_name}ProfileSave, where $section_name
    *  is basic,general,professional,etc.
    * @param string $request_method Should be POST.
    * @param array $request_data Profile data to save. Will be passed to its respective method.
    */
  public function handleSaveProfile($request_method, $request_data) {
    global $error_msg;

    $error_msg = null;
    switch ($request_method) {
      case 'POST':
        filter_all_post(&$request_data);
        if (!empty($request_data['profile_type'])) {
          $saveHandler = $request_data['profile_type'].'ProfileSave';
          if (method_exists($this, $saveHandler)) {
            $this->$saveHandler($request_data);
          } else {
            $error_msg = __("EditProfileModule::handleSaveProfile() - Unknown save handler!");
          }
        }
      break;
    }
//    $this->setWebPageMessage();
  }
    /** !!
    ************************************************************************
    * The following methods take the request data, validate it, parse it,
    * and store it if there were no previous errors.
    ************************************************************************
    */
  public function basicProfileSave($request_data) {
    global $error_msg;
    $this->isError = TRUE;

    if (empty($request_data['first_name'])) {
      $this->message = __('Fields marked with * can not be empty, First name can not be empty.');
    } else if (empty($request_data['email_address'])) {
      $this->message = __('Fields marked with * can not be empty, Email field is mandatory.');
    } else if (!empty($request_data['pass']) || !empty($request_data['conpass'])) {
    	$set_new_password = true;
    	$new_password_ok = false;
      if ($request_data['pass'] != $request_data['conpass']) {
        $this->message = __('Password and confirm password should match.');
      } else if (strlen($request_data['pass']) < PA::$password_min_length) {
        $this->message = sprintf(__('Password should be of %s characters or more.'), PA::$password_min_length);
      } else if (strlen($request_data['pass']) > PA::$password_max_length) {
        $this->message = sprintf(__('Password should be less than %s charcaters.'), PA::$password_max_length);
      } else {
      	// all is good
      	$new_password_ok = true;
      }
    }
    if ($request_data['deletepicture'] == "true") {
	    $this->handleDeleteUserPic($request_data);
    }
    if (empty($this->message) && !empty($_FILES['userfile']['name'])) {
      $uploadfile = PA::$upload_path.basename($_FILES['userfile']['name']);
      $myUploadobj = new FileUploader;
      $file = $myUploadobj->upload_file(PA::$upload_path, 'userfile', true, true, 'image');
      if ($file == false) {
        $this->message = $myUploadobj->error;
        $error = TRUE;
      } else {
        $this->user_info->picture = $file;
        Storage::link($file, array("role" => "avatar", "user" => $user->user_id));
      }
    }

    if (empty($this->message)) {//If there is no error message then try saving the user information.
      $this->user_info->first_name = $request_data['first_name'];
      $this->user_info->last_name = $request_data['last_name'];
      $this->user_info->email = $request_data['email_address'];
      if (!empty($request_data['pass'])) $this->user_info->password = md5($request_data['pass']);
      try {
        $this->user_info->save();
        $dynProf = new DynamicProfile(PA::$login_user);
        $dynProf->processPOST('basic');
        $dynProf->save('basic');
        $this->message = __('Profile updated successfully.');
//        $this->redirect2 = PA_ROUTE_EDIT_PROFILE;
//        $this->queryString = '?type='.$this->profile_type;
        $this->isError = FALSE;
      } catch (PAException $e) {
        $this->message = $e->message;
      }
    }
    $error_msg = $this->message;
  }

  public function handleDeleteUserPic($request_data) {
	  $this->user_info->picture = NULL;
	  $this->user_info->save();
	  //        $this->message = 16019;
	  //        $this->redirect2 = PA_ROUTE_EDIT_PROFILE;
	  //        $this->queryString = '?type='.$this->profile_type;
	  $this->isError = FALSE;
	  //        $this->setWebPageMessage();
  }
    /** !!
    * Takes the HTML generated by {@see generate_inner_html()} and passes it for display.
    *
    * @return string HTML content to display.
    */
  function render() {
    //Added just keeping this module backward compatible. Can be removed when this will come via dynamic page generator.
    if (!empty($_GET['type']) && in_array($_GET['type'], $this->valid_profile_types)) {
      $this->profile_type = $_GET['type'];
    }
    $this->inner_HTML = $this->generate_inner_html();
    $content = parent::render();
    return $content;
  }
    /** !!
    * Parses the template to generate the HTML.
    *
    * @return string HTML.
    */
  function generate_inner_html () {
    switch ( $this->mode ) {
     default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';
    }

    $info = new Template($inner_template);

    // This lets us know what has just been POSTed, if anything.
    // e.g.: if $post_profile_type == 'basic', 'apply changes' has
    // just been clicked on the basic profile tab.
    $info->set('post_profile_type', (!isset($_POST['submit'])) ? NULL : $_POST['profile_type']);
    $info->set_object('uid', $this->uid);
    @$info->set('array_of_errors', $this->array_of_errors);
    @$info->set('user_data', $this->user_data);
    @$info->set('user_personal_data', $this->user_personal_data);
    @$info->set('user_professional_data', $this->user_professional_data);
    $info->set('blogsetting_status', $this->blogsetting_status);

    $info->set('type', $this->profile_type);
    $info->set('profile_type', $this->profile_type);
    $info->set('section_info', $this->section_info);
    $info->set_object('user_info', $this->user_info);
    $info->set('request_data', $this->request_data);
    $inner_html = $info->fetch();

    return $inner_html;
  }

}
?>
