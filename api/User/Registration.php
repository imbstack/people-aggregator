<?php
require_once dirname(__FILE__).'/../../config.inc';
require_once "api/User/User.php";
require_once "api/Invitation/Invitation.php";
require_once "api/Relation/Relation.php";
require_once "api/Message/Message.php";
require_once "api/Validation/Validation.php";
//require_once "web/includes/functions/auto_email_notify.php";
require_once "web/includes/functions/functions.php";
require_once "api/Messaging/MessageDispatcher.class.php";

/*

This code has been moved from web/register.php.  As such, it still has
some web-isms, which should be removed.  -PP 2006-12-09

To do:
- file upload: this should be done by register.php and a path passed in to register()

*/

/**
 * Class User_Registration handles registration of a new user.
 *
 * @package User
 * @author Tekriti Software and BroadBand Mechanics
 */
class User_Registration {
  function __construct() {
    $this->errors = array();
    $this->states    = array_values(PA::getStatesList());
    $this->countries = array_values(PA::getCountryList());
  }

  /* $params = array of user registration parameters
  * $network_info = a network object, if the user is to be joined to a default network, or NULL.
  *
  * Returns TRUE if user was successfully registered, or FALSE on error.
  *
  * If registration was successful, the following member variables will be valid:
  *  $this->newuser = User object for the created user.
  *
  * If registration failed, the following member variables will be valid:
  *  $this->msg = An error message for display
  *  $this->array_of_errors = A bunch of flags to say which error occurred (used by web/register.php).
  */
  function register($params, $network_info=NULL) {
    global $_PA;

    $this->newuser = new User();
    // filter input parameters (this is the same as filter_all_post())
    $params = Validation::get_input_filter(FALSE)->process($params);

    $this->error = false;
    $mother_network_info = Network::get_mothership_info();
    $mother_extra = unserialize($mother_network_info->extra);
    if (@$mother_extra['captcha_required'] == NET_YES) { // added by Z.Hron - if captcha is required
      //Providing the capcha check
      if(md5(strtoupper($_POST['txtNumber'])) != $_SESSION['image_random_value']) {
        $_SESSION['image_is_logged_in'] = true;
        $_SESSION['image_random_value'] = '';
        $error_login = true;
        $this->error = true;
        $this->msg .= "\nPlease enter correct code";
      }
    }
    if (!$this->error) {
      $login_name = trim($params['login_name']);
      $first_name = trim($params['first_name']);
      $last_name = trim(@$params['last_name']); // not mandatory
      $email = trim($params['email']);
      $password = trim($params['password']);
      $confirm_password = trim($params['confirm_password']);
      $date_created = (!empty($params['date_created'])) ? $params['date_created'] : null;

      $dob_day   = (!empty($params['dob_day']))   ? trim($params['dob_day']) : null;                         // General data (why? should be personal)
      $dob_month = (!empty($params['dob_month'])) ? trim($params['dob_month']) : null;                       // General data (why? should be personal)
      $dob_year  = (!empty($params['dob_year']))  ? $_PA->years[((int)trim($params['dob_year']))] : null;    // General data (why? should be personal)

      $homeAddress1 = (!empty($params['homeAddress1']))  ? trim($params['homeAddress1']) : null;             // General data
      $homeAddress2 = (!empty($params['homeAddress2']))  ? trim($params['homeAddress2']) : null;             // General data

      $city    = (!empty($params['city']))     ? trim($params['city']) : null;                               // General data
      $state   = null;
      if($params['state'] == -1) {  // State/Province: Other selected
        $state  = (!empty($params['stateOther'])) ? trim($params['stateOther']) : null;                      // General data
      } else if ($params['state'] > 0) { // one of US States selected
        $state  = (!empty($params['state']))  ? $this->states[(int)$params['state']] : null;                // General data
      }
      $country = ($params['country'] > 0)  ? $this->countries[(int)$params['country']] : null;               // General data

      $postal_code = (!empty($params['postal_code']))  ? trim($params['postal_code']) : null;                // General data
      $phone = (!empty($params['phone']))  ? trim($params['phone']) : null;                                  // General data

      $validate_array = array('login_name'=>'Login name','first_name'=>'First name', 'password'=>'Password', 'confirm_password'=>'Confirm password','email'=>'Email');
      $this->msg = '';

      $this->error = FALSE;
      foreach ( $validate_array as $key => $value ) {
        if(empty($params[$key])) {
          $this->msg .= "\n".$value." is mandatory";
          $this->error = TRUE;
        }
      }

      if(strlen($this->msg) > 0 ) {
        $this->msg = "\n"."Fields marked with * must not be left empty".$this->msg;
      }
    }
    //$error_login = FALSE;
    if (!$this->error) {
      if (empty($login_name)) {
        $error_login = TRUE;
        $this->error = TRUE;
      }
      if (is_numeric($login_name)) {   // Here we check the login name  is numeric or not
        if(strlen($this->msg) > 0){
          $this->msg .= "\n";
        }
        $this->msg .= "Login name must not be numeric";
        $error_login = TRUE;
        $this->error = TRUE;
      }
      if (is_numeric($first_name)) { // Here we check the first  name  is numeric or not
        if(strlen($this->msg) > 0){
          $this->msg .= "\n";
        }
        $this->msg .="First name must not be numeric";
        $error_login = TRUE;
        $this->error = TRUE;
      }
      if (is_numeric($last_name)) {      // Here we check the last name  is numeric or not
        if(strlen($this->msg) > 0){
          $this->msg .= "\n";
        }
        $this->msg .= "Last name must not be numeric";
        $error_login = TRUE;
        $this->error = TRUE;
      }
    }

    // if error occur than no need to checks these errors
    if (!$this->error) {
      if (!Validation::validate_email($email)) {
        $email_invalid = TRUE;
        $this->array_of_errors['error_email'] = $email_invalid;
        $this->error = TRUE;
        $this->msg .= __(' E-mail address is not valid');
      }

      // Calculating Allowed Domains
      if (file_exists(PA::$project_dir . "/web/config/domain_names.txt")) {
        $domain_names_file = PA::$project_dir . "/web/config/domain_names.txt";
      } elseif (file_exists(PA::$core_dir . "/web/config/domain_names.txt")) {
        $domain_names_file = PA::$core_dir . "/web/config/domain_names.txt";
      } else {
         throw new Exception("Allowed Domains configuration file \"/web/config/domain_names.txt\" not found");
      }
      $allowed_domains = preg_split("/\s+/", file_get_contents($domain_names_file));

      // Calcutating user domain
      $user_email = explode('@', $email);
      $user_domain = strtolower($user_email[1]);
      $found = 0;
      foreach ($allowed_domains as $i=>$d) {
      	if (!preg_match('/\W/', $d)) {
      		continue;
      	}
      	// make proper regex
      	$rx = preg_replace('/\*/', '[^\.]*', $d);
      	if (preg_match("/$rx/", $user_domain)) {
      		$found++;
      	}
      }
      if (! $found) {
      	// show error
        $email_invalid = TRUE;
        $this->array_of_errors['error_email'] = $email_invalid;
        $this->error = TRUE;
        $this->msg .= __('The domain of your E-mail address is not in the list of  allowed domains');
      }


      if ($password != $confirm_password) {
        $this->msg .= "\nPassword and Confirm Password do not match.";
        $error_password_conf = TRUE;
        $this->error = TRUE;
      }

      if (strlen($password) > 15) {
        $this->msg .= "\nThe password must be less than 15 characters.";
        $error_password_l = TRUE;
        $this->error = TRUE;
      }

      if (strlen($password) <5) {
        $this->msg .= "\nThe password must be longer than 5 characters.";
        $error_password_g = TRUE;
        $this->error = TRUE;
      }
    }

    if (!$this->error) {
      if (User::user_exist($login_name)) {
        $this->msg = "Login name $login_name is already taken";
        $error_login = TRUE;
        $this->error = TRUE;
      } elseif (User::user_existed($login_name)) {
        $this->msg = "Login name $login_name has been used in the past; it belongs to a deleted user.";
        $error_login = $this->error = TRUE;
      }
      $this->array_of_errors = array("error_login"=>@$error_login, "error_first_name"=>@$error_first_name, "error_email"=>@$error_email, "error_password_conf"=>@$error_password_conf, "error_password_l"=>@$error_password_l, "error_password_g"=>@$error_password_g);
    }


    if ($this->error != TRUE) {
      $this->newuser->login_name = $login_name;
      //TODO: change to md5
      $this->newuser->password = $password;
      $this->newuser->first_name = $first_name;
      $this->newuser->last_name = $last_name;
      $this->newuser->email = $email;
      if($date_created) {  // for users inserted via import accounts script!
       $this->newuser->created = $date_created;
      }
      $this->newuser->picture = Storage::validateFileId(@$params['user_filename']);
    }

    if ($this->error != TRUE) {
      try {
        $save_error = FALSE;
        $extra = unserialize($network_info->extra);
        if ($mother_extra['email_validation'] == NET_NO) { // if email validation not required
          $this->newuser->is_active = ACTIVE;
        } else {
          $this->newuser->is_active = UNVERIFIED;
        }
        $this->newuser->save();
	if ($this->newuser->picture) Storage::link($this->newuser->picture, array("role" => "avatar", "user" => $this->newuser->user_id));

        /* The following code should now be obsolete as this is done in User->save() */
        // saving data in user profile data also -- for searching making more easier
        $data_array = array(
        0 => array('uid'=>$this->newuser->user_id, 'name'=>'first_name', 'value'=>$this->newuser->first_name, 'type'=>BASIC, 'perm'=>1),
        1 => array('uid'=>$this->newuser->user_id, 'name'=>'last_name', 'value'=>$this->newuser->last_name, 'type'=>BASIC, 'perm'=>1));

        $this->newuser->save_user_profile($data_array, BASIC);
        // saving default notification for user from network notification setting
        $user_notification = array();
        $profile = array();
        $user_notification = $extra['notify_members'];
        $user_notification['msg_waiting_blink'] = $extra['msg_waiting_blink'];
        $profile['settings']['name'] = 'settings';
        $profile['settings']['value'] = serialize($user_notification);
        $this->newuser->save_profile_section($profile, 'notifications');
        // default notification for user ends
        $desktop_images=User_Registration::get_default_desktopimage($this->newuser->user_id, $network_info);

        // code for adding default desktop image for user
        if ( $desktop_images == "" ){
          $desktop_images = array('bay.jpg','everglade.jpg','bay_boat.jpg','delhi.jpg');
          $rand_key = array_rand($desktop_images);
          $desk_img = $desktop_images[$rand_key];
        }
        else{
          $desk_img = $desktop_images;
        }
        $data_array = array(
          0 => array('uid'=>$this->newuser->user_id, 'name'=>'user_caption_image', 'value'=>$desk_img, 'type'=>GENERAL, 'perm'=>NONE),
          1 => array('uid'=>$this->newuser->user_id, 'name'=>'dob_day', 'value'=>$dob_day, 'type'=>GENERAL, 'perm'=>NONE),
          2 => array('uid'=>$this->newuser->user_id, 'name'=>'dob_month', 'value'=>$dob_month, 'type'=>GENERAL, 'perm'=>NONE),
          3 => array('uid'=>$this->newuser->user_id, 'name'=>'dob_year', 'value'=>$dob_year, 'type'=>GENERAL, 'perm'=>NONE),
          4 => array('uid'=>$this->newuser->user_id, 'name'=>'dob', 'value'=>$dob_year.'-'.$dob_month.'-'.$dob_day, 'type'=>GENERAL, 'perm'=>NONE),
          5 => array('uid'=>$this->newuser->user_id, 'name'=>'homeAddress1', 'value'=>$homeAddress1, 'type'=>GENERAL, 'perm'=>NONE),
          6 => array('uid'=>$this->newuser->user_id, 'name'=>'homeAddress2', 'value'=>$homeAddress2, 'type'=>GENERAL, 'perm'=>NONE),
          7 => array('uid'=>$this->newuser->user_id, 'name'=>'city', 'value'=>$city, 'type'=>GENERAL, 'perm'=>NONE),
          8 => array('uid'=>$this->newuser->user_id, 'name'=>'state', 'value'=>$state, 'type'=>GENERAL, 'perm'=>NONE),
          9 => array('uid'=>$this->newuser->user_id, 'name'=>'country', 'value'=>$country, 'type'=>GENERAL, 'perm'=>NONE),
          10 => array('uid'=>$this->newuser->user_id, 'name'=>'postal_code', 'value'=>$postal_code, 'type'=>GENERAL, 'perm'=>NONE),
          11 => array('uid'=>$this->newuser->user_id, 'name'=>'phone', 'value'=>$phone, 'type'=>GENERAL, 'perm'=>NONE)
        );
        //}
        $this->newuser->save_user_profile($data_array, GENERAL);
        if ($mother_extra['email_validation'] == NET_NO) { //if email validation is not required
          // creating message basic folders
          Message::create_basic_folders($this->newuser->user_id);

          // adding default relation
          if ( $this->newuser->user_id != SUPER_USER_ID ) {
            User_Registration::add_default_relation($this->newuser->user_id, $network_info);
          }
          // adding default media as well as album
          User_Registration::add_default_media($this->newuser->user_id, '', $network_info);
          User_Registration::add_default_media($this->newuser->user_id, '_audio', $network_info);
          User_Registration::add_default_media($this->newuser->user_id, '_video', $network_info);
          User_Registration::add_default_blog($this->newuser->user_id);
          //adding default link categories & links
          User_Registration::add_default_links ($this->newuser->user_id);
          // adding header image
          User_Registration::add_default_header ($this->newuser->user_id);
          // Making user member of a network if he is registering to PA from a network
          if(!empty($network_info) && ($network_info->type != PRIVATE_NETWORK_TYPE)) {
            Network::join($network_info->network_id, $this->newuser->user_id);
            PANotify::send("network_join", $network_info, $this->newuser, array());
          }

        }
      } catch (PAException $e) {
        $this->msg = $e->message;
        if ($e->code == USER_EMAIL_NOT_UNIQUE) {
          $this->msg = "Email Address has already been taken, please enter other email address.";
        }
        $save_error = TRUE;
        if ($e->message == "The email address is invalid.") {
          $email_invalid = TRUE;
          $this->array_of_errors['error_email'] = $email_invalid;
        }
      }
    }

    if ($this->error == TRUE || $save_error == TRUE) {
      $this->msg = "Sorry! your registration failed. ".$this->msg;
      return FALSE;
    }

    // success!

    // give Login User permissions to new user is moved to  Network::join() now!
/*
     $this->newuser->set_user_role(array(LOGINUSER_ROLE));
*/
    return TRUE;
  }

  public static function add_default_relation($user_id, $network_info) {
    $extra = unserialize($network_info->extra);
    $relations_name = $extra['user_defaults']['user_friends'];
    if( $relations_name != '') {
      $relations = explode(',',$relations_name);
      $relations_ids = User::map_logins_to_ids( $relations );
      $selected = DEFAULT_RELATIONSHIP_TYPE; //  2 for friends
      foreach ($relations_ids as $key => $value) {
        if( !(Relation::relation_exists( $user_id, (int)$value )))
        Relation::add_relation($user_id, (int)$value, $selected, PA::$network_info->address, PA::$network_info->network_id);
      }
    }
  }
  public static function get_default_desktopimage($user_id, $network_info) {
    $extra = unserialize($network_info->extra);
    $destopimage_name = $extra['user_defaults']['desktop_image']['name'];
    return $destopimage_name;
  }

  public static function add_default_media($user_id, $type='', $network_info=NULL) {
    // global var $path_prefix has been removed - please, use PA::$path static variable
    require_once "api/User/User.php";
    require_once "ext/Album/Album.php";
    require_once "ext/Image/Image.php";
    require_once "ext/Audio/Audio.php";
    require_once "ext/Video/Video.php";
    require_once "api/ContentCollection/ContentCollection.php";

    //$extra contains networks extra information
    $extra = unserialize( $network_info->extra);
    /** setting common variables according to media type */
    if ($type == '') {
      $net_extra_ccid_str = $extra['user_defaults']['default_image_gallery'];
      $alb_type = IMAGE_ALBUM;
      $new_img = new Image();
    }
    elseif ($type=='_audio') {
      $net_extra_ccid_str = $extra['user_defaults']['default_audio_gallery'];
      $alb_type = AUDIO_ALBUM;
      $new_img = new Audio();
    }
    elseif ($type=='_video') {
      $net_extra_ccid_str = $extra['user_defaults']['default_video_gallery'];
      $alb_type = VIDEO_ALBUM;
      $new_img = new Video();
    }
    /** getting array of content collection from comma separated string */
    if( !empty($net_extra_ccid_str) ) {
      $net_extra_ccid = explode( ',', $net_extra_ccid_str );

      /** setting all content collection variables */
      if( count( $net_extra_ccid ) >= 1 ) {
        for( $i = 0; $i < count( $net_extra_ccid ); $i++ ) {
          $new_im_al = new Album( $alb_type );
          $new_im_al_default = new Album( $alb_type );
          $new_im_al->load((int)$net_extra_ccid[$i]);
          $content_collection_obj = new ContentCollection();
          $content_collection_obj->collection_id = $new_im_al->collection_id;
          $contents = $content_collection_obj->get_contents_for_collection();
          $new_im_al_default->title = $new_im_al->title;
          $new_im_al_default->description = $new_im_al->description;
          $new_im_al_default->author_id = $user_id;
          $new_im_al_default->type = 2; // FOR ALBUM, type is 2
          $new_im_al_default->save();
          /** Setting content variable */
          for( $j = 0; $j < count( $contents ); $j++ ) {
            if ($contents[$j]['type'] != 7) { // If content is not a SB content
              if ($alb_type == IMAGE_ALBUM) {
                $new_img_default = new Image();
                $new_img_default->type = IMAGE;
              }

              elseif ($alb_type == AUDIO_ALBUM) {
                $new_img_default = new Audio();
                $new_img_default->type = AUDIO;
              }
              elseif($alb_type == VIDEO_ALBUM) {
                $new_img_default = new Video();
                $new_img_default->type = VIDEO;
              }
              $new_img->load((int)$contents[$j]['content_id']);
              $new_img_default->file_name = $new_img->file_name;
              $new_img_default->file_perm = $new_img->file_perm;
              $new_img_default->title = $contents[$j]['title'];
              $new_img_default->body = $contents[$j]['body'];
              $tags = Tag::load_tags_for_content($contents[$j]['content_id']);

              $new_img_default->allow_comments = 1;
              $new_img_default->author_id = $user_id;
              $new_img_default->parent_collection_id = $new_im_al_default->collection_id;
              $new_img_default->save();
              if(!empty($tags)) {
                $tag_array=array();
                if (is_array($tags)) {
                  for ($i = 0; $i < count($tags); $i++) {
                    $tag_array[] = $tags[$i]['name'];
                  }
                }
                Tag::add_tags_to_content($new_img_default->content_id, $tag_array);
              }
            } else {
              // If content is a SB content
              //TODO: handling of SB content if it is in media gallery.
            }
          }
        }
      }
    }
  }

  public static function add_default_links($user_id) {
    global  $network_info;
    require_once "ext/NetworkLinks/NetworkLinks.php";
    require_once "api/Links/Links.php";
    $network_links = new NetworkLinks();

    $network_owner_id = ($network_info->type == MOTHER_NETWORK_TYPE) ? SUPER_USER_ID : Network::get_network_owner($network_info->network_id);

    $condition = array('user_id'=> $network_owner_id, 'is_active'=> 1);
    $link_categories = $network_links->load_category($condition); // load category as set by network operator
    $Links = new Links ();
    $error_array = array();
    //providing default links to the user, as set by network operator
    for($counter = 0; $counter < count($link_categories); $counter++) {
      $param_array = array('category_name' => $link_categories[$counter]->category_name,
      'user_id' => $user_id,
      'created' => time(),
      'changed' => time(),
      'is_active' => ACTIVE
      );
      $Links->set_params ($param_array);
      $category_id = $Links->save_category (); // save network operator category as user's link category
      $network_lists = new NetworkLinks();
      $network_lists->user_id = $network_info->owner_id;
      $condition = array('category_id'=>$link_categories[$counter]->category_id, 'is_active'=> ACTIVE);
      $list_array = $network_lists->load_link($condition); // load list for network operator's category
      for($i = 0; $i < count($list_array); $i++) {
        $param_array = array('title' => $list_array[$i]->title,
        'url' => $list_array[$i]->url,
        'category_id' => $category_id,
        'created' => time(),
        'changed' => time(),
        'is_active' => ACTIVE
        );
        $Links->set_params ($param_array);
        $Links->save_link (); // save network operator list as user's list
      }
    }
  }

  // This function provides a default blog to user
  public static function add_default_blog($user_id) {
    global $network_info;
    require_once "ext/BlogPost/BlogPost.php";
    $extra = unserialize($network_info->extra);
    if ($extra['user_defaults']['default_blog'] != NET_NO) {
      // if network operator has set a default blog
      $net_extra_blog_id = (int)$extra['user_defaults']['default_blog'];
      $condition = 'content_id = '.$net_extra_blog_id;
      $admin_content = Content::get(NULL, $condition);
      $no_display_on_home_page = !DISPLAY_ON_HOMEPAGE;
      try {
        $post_saved = BlogPost::save_blogpost(0, $user_id, $admin_content[0]['title']
        , $admin_content[0]['body'], NULL, NULL, -1, ACTIVE,
        $no_display_on_home_page, TRUE);
      } catch (PAException $e) {
        throw $e;
      }
    } // end of if
  }
  public function add_default_header($user_id) {
    global $network_info;
    $extra = unserialize($network_info->extra);
    // check value's of network header, network option , network display for User
    // Now save all these value in network's User
    $header_image = $extra['user_defaults']['desktop_image']['name'];
    $option = $extra['user_defaults']['desktop_image']['option'];
    $header_image_display = @$extra['user_defaults']['desktop_image']['display'];
    $new_profile = array();

    if (!empty($header_image)) {
      $new_profile['user_caption_image'] = array("name" =>"user_caption_image","value" => $header_image);
    }
    if (!empty($header_image_display)) {
      $new_profile['desktop_image_display'] = array("name" => "desktop_image_display","value" => $header_image_display);
    }
    if (!empty($header_image_option)) {
      $new_profile['desktop_image_action'] = array("name" => "desktop_image_action","value" => $header_image_option);
    }
    try {
      $user = new User();
      $user->user_id = $user_id;
      $user->save_profile_section($new_profile, GENERAL, true);
    } catch (PAException $e) {
      throw $e;
    }
  }//end of function
  public function download($source_url, $file_target)
  {
    // Preparations
    $source_url = str_replace(' ', '%20', html_entity_decode($source_url)); // fix url format
    if (file_exists($file_target)) { chmod($file_target, 0777); } // add write permission

    // Begin transfer
    if (($rh = fopen($source_url, 'rb')) === FALSE) { return false; } // fopen() handles
    if (($wh = fopen($file_target, 'wb')) === FALSE) { return false; } // error messages.
    while (!feof($rh))
    {
      // unable to write to file, possibly because the harddrive has filled up
      if (fwrite($wh, fread($rh, 1024)) === FALSE) { fclose($rh); fclose($wh); return false; }
    }

    // Finished without errors
    fclose($rh);
    fclose($wh);
    return true;
  }
}


?>
