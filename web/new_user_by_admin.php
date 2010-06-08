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
  //anonymous user can not view this page;
  $login_required = TRUE;
  $use_theme = 'Beta';
  //including necessary files
  include_once("web/includes/page.php");
  require_once "api/Content/Content.php";
  require_once "api/Comment/Comment.php";
  require_once "api/User/User.php";
  require_once "api/Validation/Validation.php";
  require_once "api/Message/Message.php";
  require_once 'web/includes/classes/file_uploader.php';
//  require_once "web/includes/functions/mailing.php";
  require_once "web/includes/network.inc.php";
  require_once "api/Messaging/MessageDispatcher.class.php";
 
  $error = FALSE;
  $authorization_required = TRUE;

  $newuser = new User();
  //this function generate a random password of lenght 5-8 characters
  
  function generate_password() {
    $min=5;// minimum length of password
    $max=8;// maximum length of password
    $pwd="";   //to store generated password
    for($i=0;$i<rand($min,$max);$i++){
        $num=rand(48,122);
        if(($num > 97 && $num < 122)){
          $pwd.=chr($num);
        }else if(($num > 65 && $num < 90)){
                $pwd.=chr($num);
        }else if(($num >48 && $num < 57)){
                $pwd.=chr($num);
        }else if($num==95){
            $pwd.=chr($num);
        }else{
            $i--;
        }
    }
    return $pwd;  
  }
  if (isset($_POST['CreateUser']) && !$error ) {
    $error = FALSE;
    $login_name = trim( $_POST['login_name']  );
    $first_name = stripslashes( trim( $_POST['first_name'] )  );
    $last_name = stripslashes( trim( $_POST['last_name'] ) );
    $email = trim( $_POST['email'] );
    $password = "";
    if($_POST['radiobutton'] == "auto_pass") {
      $password = generate_password();
      $validate_array = array( 'login_name'=>'Login name','first_name'=>'First name','email'=>'Email' );
    } else {
      $password = trim( $_POST['password'] );
      $validate_array = array( 'login_name'=>'Login name','first_name'=>'First name','email'=>'Email', 'password'=>'Password' );
    }
    
    
    $msg = NULL;
    foreach ( $validate_array as $key => $value ) {
      if( empty( $_POST[$key] ) ) {
        $msg .= "<br> ".$value." is mandatory";
        $error = TRUE;
      }
    }
    if (!Validation::validate_email($email) && !empty($_POST['email'])) {
      $email_invalid = TRUE;
      $error = TRUE;
      $msg .= '<br> Email address is not valid';
    }
    if (strlen($login_name) <3 and !empty($login_name)) {
      $msg = "The username must be greater than 3 characters.";
      $error = TRUE;
    }
    if (strlen($password) > 15) {
      $msg = "The password must be less than 15 characters.";
      $error = TRUE;
    }
    if (strlen($password) <5) {
      $msg = "The password must be greater than 5 characters.";
      $error = TRUE;
    }
    if (User::user_exist($login_name)) {
        $msg = "Username $login_name is already taken";
        $error = TRUE;
    }
    
    // saving value if create user fails for any reason
    $vartoset = array('login_name','email','first_name','last_name', 'radiobutton', 'action');
    filter_all_post($_POST);//filters all data of html
    for ($i = 0; $i < count($vartoset); $i += 1) {
      $var = $vartoset[$i];
      if (!empty($_POST[$var])) {
        $form_data[$var] = $_POST[$var];
      }
    }  
 
    if ( $error == FALSE ) {
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
          $newuser->save();
          if (!empty($file)) {
					  Storage::link($file, array("role" => "avatar", "user" => $newuser->user_id));
          }
          // creating message basic folders
          Message::create_basic_folders($newuser->user_id);
          //token creation
          $expires = 3600*24*5;//5days
          $token = $newuser->get_auth_token($expires);

//        $user_url = PA::$url .'/mail_action.php?token='.$token.'&action=user';
//        $edit_url = PA::$url .'/mail_action.php?token='.$token.'&action=profile';
          $user_url = "<a href=\"". PA::$url . "/mail_action.php?token=$token&action=user\">". PA::$url ."/mail_action.php?token=$token&action=user</a>";
          $edit_url = "<a href=\"". PA::$url . "/mail_action.php?token=$token&action=profile\">". PA::$url ."/mail_action.php?token=$token&action=profile</a>";

          PAMail::send("create_new_user_by_admin", $newuser, PA::$network_info, array('greeting.message' => $_POST['greeting_msg'], 'user.password' => $password, 'user.link' => $user_url, 'edit.link' => $edit_url));

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
          
          
          $data_array = array(0 => array('uid'=>$newuser->user_id, 'name'=>'user_caption_image', 'value'=>$desk_img, 'type'=>GENERAL, 'perm'=>1));
         
          $newuser->save_user_profile($data_array, GENERAL);
          
          
          //sending mail to the newly created user
          $msg = "User has been Added successfully";
          //if new user is created in a network then he must set as a joined user
          if(!empty(PA::$network_info)) {
            $by_admin = true;
            Network::join(PA::$network_info->network_id, $newuser->user_id, NETWORK_MEMBER, $by_admin);
            // $by_admin = true overrides the 
            // user_waiting status if it would get set
            // this is an admin action, so we want it to happen in any case
          }
          $form_data = array();
        } catch( PAException $e )  {
          $msg = $e->message;
        }
        
      }// end if 
    } //end if
  }//main if
 
  function setup_module( $column, $module, $obj ) {
    global $paging,$form_data,$msg;
    switch( $module ){
      case 'NewUserByAdminModule':
      if($msg!='') {
        $obj->form_data = $form_data;
      }  
      break;
      }
    
  }
  $page = new PageRenderer( "setup_module", PAGE_NEW_USER_BY_ADMIN, "Create New User", 'container_two_column.tpl','header.tpl',PRI, HOMEPAGE,PA::$network_info );

  $page->html_body_attributes ='class="no_second_tier network_config"';

  uihelper_get_network_style();
  uihelper_error_msg($msg);

  echo $page->render();
?>
