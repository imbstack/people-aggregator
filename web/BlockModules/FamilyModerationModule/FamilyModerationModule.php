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

require_once "api/Category/Category.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "ext/Group/Group.php";
require_once "web/includes/classes/Pagination.php";
include "web/includes/classes/RegistrationPage.php";

class FamilyModerationModule extends Module {

  public $module_type = 'group';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  public $max_height,$view;
  public $uid, $members_data, $Paging;
  public $page_links, $page_prev, $page_next, $page_count;
  public $set_id;

  public function __construct() {
    parent::__construct();
  }

  public function initializeModule($request_method, $request_data) {
    global $paging;

    $this->Paging = $paging;
    if(!empty($this->shared_data['group_info'])) {
      $this->family = $this->shared_data['group_info'];
      $this->set_id = $this->family->collection_id;
    } else if(!empty($request_data['gid'])) {
      $this->family = ContentCollection::load_collection((int)$request_data['gid']);
      $this->set_id = $request_data['gid'];
    } else {
      return 'skip';
    }
    if (!empty($request_data['action']) && ($request_data['action'] == 'addChild')) 
    {
        $this->set_inner_template('add_child.tpl');
    }
    else if (!empty($request_data['view'])) 
    {
      $this->view = $request_data['view'];
      if($this->view == 'members') {
      
        $Pagination = new Pagination;
        $Pagination->setPaging($this->Paging);

        $this->page_prev = $Pagination->getPreviousPage();
        $this->page_next = $Pagination->getNextPage();
        $this->page_links = $Pagination->getPageLinks();

        $this->get_links();
        $this->set_inner_template('center_inner_public.tpl');
        $templ_vars = array( 'links' => $this->members_data,
                             'page_prev' => $this->page_prev,
                             'page_next' => $this->page_next,
                             'page_links' => $this->page_links,
                             'page_first' => $this->page_first,
                             'page_last' => $this->page_last,
                             'family_id' => $this->set_id,
                             'div_visible_for_moderation' => $this->view,
                            ); 
        $this->inner_HTML = $this->generate_inner_html($templ_vars);
      }
    }
  }

  function handleRequest($request_method, $request_data) {
    if(!empty($request_data['action'])) {
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


  public function handlePOST_deleteFamilyMembers($request_data) {
   global $app;
    if (!empty($request_data["members"]) && !empty($request_data["family_id"])) {
      $membersArr = array();
      $membersArr = $request_data["members"];
      $family = new Group();
      $family->collection_id = $request_data["family_id"];
      $membersArr_count = count($membersArr);
      for($counter = 0; $counter < $membersArr_count; $counter++) {
        list($res, $msg) = $this->leaveFamily($family, (int)$membersArr[$counter]);
        if(!$res) {
          return;
        }
      }
      $msg = __("Member(s) Deleted");
    } else {
      $msg = __('Please select a member');
    }
    $url = UrlHelper::url_for(PA_ROUTE_FAMILY_MODERATION, array('gid'    => $request_data["family_id"],
                                                                'msg'    => urlencode($msg)));
    $app->redirect($url);                                                                
  }

  private function leaveFamily($family, $uid) {
      $msg = null;
      $res = false;
      if (Group::member_exists((int)$family->collection_id, (int)$uid)) {
        try {
          $x = $family->leave((int)$uid);
          $res = true;
        } catch (PAException $e) {
          $msg = "Operation failed (".$e->message."). Please try again";
          $res = false;
        }
      }
      if (@$x) {
        $msg = sprintf(__("You have left \"%s\" successfully."), stripslashes($family->title));
        // also delete Family relation
        require_once 'api/Entity/TypedGroupEntityRelation.php';
        TypedGroupEntityRelation::delete_relation((int)$uid, (int)$family->collection_id, PA::$network_info->network_id);
      }
      return array($res, $msg);
  }

  private function handleGET_addChild($request_data) {
    $this->renderer->add_page_js(PA::$theme_url . '/javascript/jquery.validate.js');
    $this->renderer->add_page_js(PA::$theme_url . '/javascript/jquery.metadata.js');
    $this->renderer->add_page_js(PA::$theme_url . '/javascript/ajaxfileupload.js');
    $this->renderer->add_page_js(PA::$theme_url . '/javascript/child_registration.js');
    $title = __("Add a Child");

    $years = array_flip(PA::getYearsList());
    $years[' '] = '';
    $months = array_flip(PA::getMonthsList());
    $months[' '] = '';    
    $states    = array("-2" => '-Select-', "-1" => 'Other');
    $states    = $states + array_values(PA::getStatesList());
    
    $countries    = array("-1" => '-Select-');
    $countries    = $countries + array_values(PA::getCountryList());
    $this->inner_HTML = $this->generate_inner_html(array('title' => $title, 'states' => $states, 
                                                         'countries' => $countries, 'months' => $months,
                                                         'years' => $years, 'parent_uid' => PA::$login_uid ));
  }
  
  private function handlePOST_addChild($request_data) {
   global $error_msg;
  
    $error = FALSE;
    $login_name = trim( $_POST['login_name']  );
    $first_name = stripslashes( trim( $_POST['first_name'] )  );
    $last_name = stripslashes( trim( $_POST['last_name'] ) );
    $email = trim( $_POST['email'] );
    $password = trim( $_POST['password'] );
    $use_parent_email = $_POST['use_parent_email'];
//echo "<pre>".print_r($_POST, 1)."</pre>"; die();
    if(!isset($_POST['state'])) {
       if(isset($_POST['stateOther'])){
         $_POST['state'] = $_POST['stateOther'];
       } 
    }
    if(isset($_POST['stateOther'])) 
      unset($_POST['stateOther']);
    
    $msg = NULL;
   
    if (!Validation::validate_email($email) && !empty($_POST['email'])) {
      $email_invalid = TRUE;
      $error = TRUE;
      $msg .= '<br> Email address is not valid';
    }
    if (User::user_exist($login_name)) {
        $msg = "Username $login_name is already taken";
        $error = TRUE;
    }
    
 
    if ( $error == FALSE ) {
        $newuser = new User();
        $newuser->login_name = $login_name;
        $newuser->password = $password;
        $newuser->first_name = $first_name;
        $newuser->last_name = $last_name;
        $newuser->email = $email;
        $newuser->is_active = ACTIVE;
        if (!empty($_FILES['userfile']['name'])) {
          $myUploadobj = new FileUploader; //creating instance of file.
          $image_type = 'image';
          $file = $myUploadobj->upload_file(PA::$upload_path,'userfile',true,true,$image_type);
          if( $file == false) {
            $msg = $myUploadobj->error;
            $error = TRUE;
          }
          else {
            $newuser->picture = $file;
          }
        }
        if( $error == FALSE ) {
          try {
              if($use_parent_email) {
                $newuser->save($check_unique_email = false);
              } else {
                $newuser->save($check_unique_email = true);
              }  
              if (!empty($file)) {
                Storage::link($file, array("role" => "avatar", "user" => $newuser->user_id));
              }
              // creating message basic folders
              Message::create_basic_folders($newuser->user_id);

              // adding default relation
              if ( $newuser->user_id != SUPER_USER_ID ) {
                User_Registration::add_default_relation($newuser->user_id, PA::$network_info);
              }
              // adding default media as well as album
              User_Registration::add_default_media($newuser->user_id, '', PA::$network_info);
              User_Registration::add_default_media($newuser->user_id, '_audio', PA::$network_info);
              User_Registration::add_default_media($newuser->user_id, '_video', PA::$network_info);
              User_Registration::add_default_blog($newuser->user_id);
              //adding default link categories & links
              User_Registration::add_default_links ($newuser->user_id);
            
              // code for adding default desktop image for user
              $desk_img=uihelper_add_default_desktopimage($newuser->user_id);
       
              if(empty($desk_img)) {
                $desktop_images =array('bay.jpg','everglade.jpg','bay_boat.jpg','delhi.jpg');
                $rand_key = array_rand($desktop_images);
                $desk_img = $desktop_images[$rand_key];
              }
          
          
    $states       = array_values(PA::getStatesList());
    $countries    = array_values(PA::getCountryList());
    $profile_keys = array('dob_day', 'dob_month', 'dob_year', 'homeAddress1', 'homeAddress2',
                          'city', 'state', 'country', 'postal_code', 'phone', 'use_parent_email');
    $profile_data = array();                  
    filter_all_post($_POST);//filters all data of html
    foreach($profile_keys as $k => $pkey) { 
      if (!empty($_POST[$pkey])) { 
         if(($pkey == 'state') && ($_POST[$pkey]) >= 0) {
            $prof_rec = array( 'uid'  => $newuser->user_id,
                               'name' => $pkey,
                               'value'=> $states[$_POST[$pkey]],
                               'type' => GENERAL,
                               'perm' => 1);
         } 
         else if(($pkey == 'country') && ($_POST[$pkey]) >= 0) {
            $prof_rec = array( 'uid'  => $newuser->user_id,
                               'name' => $pkey,
                               'value'=> $countries[$_POST[$pkey]],
                               'type' => GENERAL,
                               'perm' => 1);
         } else { 
            $prof_rec = array( 'uid'  => $newuser->user_id,
                               'name' => $pkey,
                               'value'=> $_POST[$pkey],
                               'type' => GENERAL,
                               'perm' => 1);
         } 
         $profile_data[] = $prof_rec;
      }
    }  
              $profile_data[] = array('uid'=>$newuser->user_id, 'name'=>'user_caption_image', 'value'=>$desk_img, 'type'=>GENERAL, 'perm'=>1);
//     echo "<pre>".print_r($profile_data,1)."</pre>"; 
        
              $newuser->save_user_profile($profile_data, GENERAL);
          
              //if new user is created in a network then he must set as a joined user
              if(!empty(PA::$network_info)) {
                $by_admin = true;
                Network::join(PA::$network_info->network_id, $newuser->user_id, NETWORK_MEMBER, $by_admin);
                // $by_admin = true overrides the 
                // user_waiting status if it would get set
                // this is an admin action, so we want it to happen in any case
              }
              $user_joined = $this->family->join((int)$newuser->user_id, $newuser->email, null);
              if ($user_joined) {
                // deal with TypedGroup Relations
                require_once("api/Entity/TypedGroupEntityRelation.php");
                $type = 'child';
                TypedGroupEntityRelation::set_relation($newuser->user_id, $this->family->collection_id, $type);
       
                if($type == 'child') {              // if user type == child remove LoginUser and GroupMember roles
                  $newuser->delete_user_role();     // then assign 'Child' role only
                  $_extra = serialize(array( 'user' => false, 'network' => false, 'groups' => array($this->family->collection_id)));
                  $user_roles[] = array('role_id' => CHILD_MEMBER_ROLE, 'extra' => $_extra);
                  $newuser->set_user_role($user_roles);
                }
              }
              $msg = __("Child's account was successfully created");
          } catch( PAException $e )  {
              $msg = $e->message;
          }
        }// end if 
      } //end if
      $error_msg = $msg;
  }


  /**
    Get data for moderation option ie group moderation
   **/

  private function get_links() {
    $this->Paging["querystring"] = "view=members&gid=$this->set_id";
    $this->Paging["count"] = $this->family->get_members($cnt=TRUE, '', '', '', '',FALSE);
    $members = $this->family->get_members($cnt=FALSE, $this->Paging["show"], $this->Paging["page"], '', '',FALSE);
    $User = new User();
    foreach($members as $membersDetails) {
      if($membersDetails["user_type"] != 'owner') {
        $User->load((int)$membersDetails["user_id"]);
        // get the relationType for this user
        list($relType, $relLabel) = TypedGroupEntityRelation::get_relation_to_group((int)$membersDetails["user_id"], (int)$this->set_id);
        if (empty($relType)) {
            $relType = 'member';
            $relLabel = __('Member');
        }
        $this->members_data[] = array('user_id'=>$membersDetails["user_id"], 'first_name'=>$User->first_name, 'last_name'=>$User->last_name, 'email'=>$User->email, 'created'=>$membersDetails['join_date'], 'picture'=>$User->picture, 'user_type'=>$membersDetails["user_type"],'login_name'=>$User->login_name, 'relType' => $relType);
      }
    }
//    echo "<pre>".print_r($this->members_data,1)."</pre>";
    return;
  }


  function set_inner_template($template_fname) {
    $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/$template_fname";
  }

  function render() {
    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html($template_vars = array()) {
    
    $inner_html_gen = & new Template($this->inner_template);
    foreach($template_vars as $name => $value) {
      if(is_object($value)) {
        $inner_html_gen->set_object($name, $value);
      } else {
        $inner_html_gen->set($name, $value);
      }  
    }
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

/*
  public function render() {
    $this->get_links();
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  public function generate_inner_html () {
    switch ($this->mode) {
     default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
    $inner_html_gen = & new Template($tmp_file);

    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);

    $this->page_prev = $Pagination->getPreviousPage();
    $this->page_next = $Pagination->getNextPage();
    $this->page_links = $Pagination->getPageLinks();

    $inner_html_gen->set('links', $this->members_data);
    $inner_html_gen->set('page_prev', $this->page_prev);
    $inner_html_gen->set('page_next', $this->page_next);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('family_id', $this->set_id);
    $inner_html_gen->set('div_visible_for_moderation', $this->view);

    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
*/
}
?>