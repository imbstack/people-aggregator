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
require_once dirname(__FILE__).'/../../config.inc';
require_once dirname(__FILE__).'/User.php';
require_once "api/User/Registration.php";
require_once "api/Logger/Logger.php";

/*
* Class ShadowUser is intended to enable the creation and management of
* "Shadow User Accounts," which are a mapping of external User IDs to
* PA User accounts
* The idea is to create PA accounts "in the background"
*/
class ShadowUser extends User {
    // the namesoace for the remote site
    // e.g. 'videoplay'
    // or 'nsid' in case of flickr
    public $namespace;
    // this is the login_name that get's sync'ed to the
    // login_name on the ecternal side
    public $display_login_name;

    function __construct($namespace) {
        $this->namespace = $namespace;
        parent::__construct();
    }

    function load($remote_id_or_userinfo) {
        Logger::log("Enter: ShadowUser::load");
        $remote_id = NULL;
        $userinfo = NULL;
        if(is_array($remote_id_or_userinfo)) {
            $userinfo = $remote_id_or_userinfo;
            $remote_id = $remote_id_or_userinfo['user_id'];
        }
        else {
            $remote_id = $remote_id_or_userinfo;
        }
        $u = parent::quick_search_extended_profile('user_id', $remote_id, $this->namespace);
        try {
            parent::load($u->login_name);
            Logger::log("Exit: ShadowUser::load, success");
        }
        catch(PAException$e) {
            Logger::log("Exit: ShadowUser::load, fail");
            return NULL;
        }
        // if we have been passed userinfo
        // pass it on the check for needed sync
        if($userinfo) {
            $this->sync($userinfo);
        }
        // load th display_login_name
        $this->display_login_name = $this->get_profile_field($this->namespace, 'display_login_name');
        return $this->user_id;
    }
    // sync'ing the passed in userdata with stored data
    // this is called automatically if userdata is passed to the load()
    // this method will compare the store data to the passed
    // and only update the DB if the data has changed
    public function sync($userdata) {
        // make sure we have a loaded ser here
        if(empty($this->email)) {
            throw new PAException(USER_INVALID, "There was no User loaded yet");
        }
        $needupdate = FALSE;
        // check the core user profile fields
        $corefields = array(
            "first_name",
            "last_name",
            "email",
        );
        foreach($corefields as $i => $field) {
            if($this-> {
                $field
            } != $userdata[$field]) {
                $this-> {
                    $field
                } = $userdata[$field];
                $needupdate = TRUE;
            }
        }
        if($needupdate) {
            try {
                $this->save();
            }
            catch(PAException$e) {
                throw $e;
            }
        }
        // handle the dislay_login_name
        $userdata['display_login_name'] = $userdata['login_name'];
        // check the namespace profile fields
        $extendeddata = User::load_profile_section($this->user_id, $this->namespace);
        $needupdate = FALSE;
        foreach($extendeddata as $key => $data) {
            if(isset($userdata[$key])) {
                // only if it got passed
                if($userdata[$key] != $data['value']) {
                    $extendeddata[$key]['value'] = $userdata[$key];
                    $needupdate = TRUE;
                }
            }
        }
        if($needupdate) {
            $this->save_profile_section($extendeddata, $this->namespace);
        }
    }
    // call this method with appropriate user info
    // to create a shadow user account
    // returns the new ShadowUser
    static

    function create($namespace, $userinfo, $network_info) {
        // setup the needed info
        if(empty($userinfo['login_name'])) {
            $userinfo['display_login_name'] = $userinfo['first_name'].'.'.$userinfo['last_name'];
        }
        else {
            $userinfo['display_login_name'] = $userinfo['login_name'];
        }
        // this is the real internal PA login_name
        // which should NOT be displayed
        // instead use the display_login_name
        $userinfo['login_name']       = $namespace.".".$userinfo['user_id'];
        $userinfo['confirm_password'] = $userinfo['password'] = substr(md5($userinfo['email'].rand()), 0, 12);
        $reg_user                     = new User_Registration();
        if($reg_user->register($userinfo, $network_info)) {
            // Success!
            $reg_user->newuser->set_last_login();
            // also save the external user_id
            $reg_user->newuser->set_profile_field($namespace, 'user_id', $userinfo['user_id'], 0);
            $reg_user->newuser->set_profile_field($namespace, 'display_login_name', $userinfo['display_login_name'], 0);
            // load it as a shadow user
            Cache::reset();
            $su = new ShadowUser($namespace);
            $su->load($userinfo['user_id']);
            return $su;
        }
        else {
            throw new PAException(BAD_PARAMETER, $reg_user->msg);
            return NULL;
        }
    }
}
?>