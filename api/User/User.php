<?php
require_once dirname(__FILE__).'/../../config.inc';
require_once "db/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Cache/Cache.php";
//require_once "api/Tag/Tag.php";
require_once "api/Relation/Relation.php";
require_once "api/Network/Network.php";
require_once "api/Logger/Logger.php";
require_once "ext/PingClient/PingClient.php";
require_once "web/includes/functions/mailing.php";
require_once "api/api_constants.php";
// require_once "ext/ConfigVariable/ConfigVariable.php";
require_once "ext/Album/Album.php";
require_once "web/includes/functions/functions.php";
require_once "api/Storage/Storage.php";
require_once "api/User/UserDisplayName.class.php";
require_once "api/Forum/PaForumsUsers.class.php";
require_once "api/Validation/Validation.php";
require_once "api/Messaging/MessageDispatcher.class.php";
require_once "api/User/UserPopularity.class.php";


/**
* Class User represents a user in the system.
*
* @package User
* @author Tekriti Software
*/
class User {
  /**
  * The uid associated with this user.
  *
  * When creating a new user, do not set $uid.
  * It will get set on calling save(),
  * by the database creating next id.
  *
  * @var integer
  */
  public $user_id;

  /**
  * The login name associated with this user.
  *
  * Login names are unique in the system and can
  * consist of alphanumeric characters and underscores.
  * Login names are between 3-20 characters long.
  *
  * @var string
  */
  public $login_name;

  /**
  * The md5 hashed password for this user.
  *
  * @var string
  */
  public $password;

  /**
  * First name of the user.
  *
  * @var string
  */
  public $first_name;

  /**
  * Last name of the user.
  *
  * @var string
  */
  public $last_name;

  /**
  * Email address.
  *
  * @var string
  */
  public $email;

  /**
  * Filename of the user uploaded photo.
  *
  * @var string
  */
  public $picture;

  /**
  * Is this user active?
  *
  * FALSE means that this user has been 'deleted' in the system.
  *
  * @var bool
  */
  public $is_active;

  /**
  * Tags associated with this user.
  *
  * An array of strings which repesent the tags attached to this user.
  *
  * @var array
  */
  public $tags;

  /**
  * Variable indicating this is a new user or an existing user
  *
  * @var bool
  */
  private $is_new;

  /**
   * User creation date/time.
   *
   * @var unix-timestamp
   * @access public
   */
  public $created;

  /**
   * User information modification date/time.
   *
   * @var unix-timestamp
   * @access public
   */
  public $changed;

  /**
   * User last login date/time.
   *
   * @var unix-timestamp
   * @access public
   */
  public $last_login;


  /**
  * User Role
  *
  * @var array of integers
  */
  public $role = array ();


  /**
  * Forgot password id.
  *
  * @var integer
  */
  public $forgot_password_id;

  /**
  * The default constructor for User class.
  */
  public function __construct() {
    $this->is_new = TRUE;
    $this->is_active = 1;
  }

  // Cache of user names used by User::url_from_id
  private $user_name_cache;

  // Find a user's url, given their login id.
  // FIXME: move this function out into uihelper - funcs in /api shouldn't assume they are running inside PA
  public function url_from_id($user_id) {
    // global var $_base_url has been removed - please, use PA::$url static variable

    $user_id = (int)$user_id;
    $login = @$this->user_name_cache[$user_id];
    if (!$login) {
        list($login) = Dal::query_one("SELECT login_name FROM {users} WHERE user_id=? AND is_active=1", Array($user_id));
        $user_name_cache[$user_id] = $login;
    }
    return PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id;

    //return PA::$url . "/user/$login/";
  }

  public function get_name() {
    return "$this->display_name";
  }

  /**
  * Load a user object with data for the given user_id
  *
  * @param string $user_id_or_login_name The login name/user_id of the user whose information needs to be loaded
  *
  * @param string $key_column "user_id" if $user_id_or_login_name is a user id, "login_name" if it's a login name, "email" if it's an e-mail address, or NULL to guess if it's a user id or login name.
  */
  public function load($user_id_or_login_name, $key_column=NULL, $check_deleted=FALSE) {
    Logger::log("Enter: function User::load");

    if (!$user_id_or_login_name) {
      throw new PAException(USER_INVALID_LOGIN_NAME, "Invalid user id/login name/email address '$user_id_or_login_name'");
    }

    if (!$key_column) {
      $key_column = is_int($user_id_or_login_name) ? "user_id" : "login_name";
    }

    if (!in_array($key_column, array("user_id", "login_name", "email"))) {
      throw new PAException(VALIDATION_INCORRECT_TYPE, "Invalid user id type '$key_column'; must be either 'user_id', 'login_name' or 'email'");
    }

    $cache_key = "user:$key_column:$user_id_or_login_name";
    $row = Cache::getValue($cache_key);
    if ($row === NULL) {
      $sql = "SELECT * FROM {users} WHERE $key_column = ? AND is_active <> ? LIMIT 1";
      $data = array($user_id_or_login_name, DELETED);
      $res = Dal::query($sql, $data);

      if (!$res->numRows()) {
        if ($check_deleted) {
          // check to see if the user has been deleted - for key=user_id
          if (Dal::query_first("SELECT COUNT(*) FROM users WHERE $key_column = ? AND is_active=0", array($user_id_or_login_name))) {
            throw new PAException(USER_ALREADY_DELETED, "User $user_id_or_login_name has been deleted");
          }
        }
        Logger::log(" Throwing exception USER_NOT_FOUND | Message: User does not exist.", LOGGER_ERROR);
        $print_str = ucfirst(str_replace('_', ' ', $key_column));
        throw new PAException(USER_NOT_FOUND, "$print_str \"$user_id_or_login_name\" does not exist");
      }

      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      if (!empty($row)) {
        Cache::setValue($cache_key, $row);
      }
    }

    if (!empty($row)) {
      $this->user_id = (int)$row->user_id;
      $this->login_name = $row->login_name;
      $this->password = $row->password;
      $this->first_name = $row->first_name;
      $this->last_name = $row->last_name;
      $this->email = $row->email;
      $this->is_active = $row->is_active;
      $this->is_new = FALSE;
      $this->created = $row->created;
      $this->changed = $row->changed;
      $this->last_login = $row->last_login;
      $this->picture = $row->picture;
      $this->role = $this->load_user_roles();
    }
    $dn = new UserDisplayName($this);
    $this->display_name = $dn->get();

    Logger::log("Exit: function User::load");
  }

  /**
  * Input : functions takes array as parameter $users_array,
  * containing user names, user ids or email addresses.
  * $key_column tells the field name of the table for select.
  * For searching for login_name, user_id or email.
  **/
  public function load_users($users_array, $key_column='user_id') {
    Logger::log("Enter: function User::load_users");

    $data = array();

    if (is_array($users_array) && ($count = count($users_array)) > 0) {
      $tmp_sql = "";
      for ($counter = 0; $counter < count($users_array); $counter++) {
          $tmp_sql .= '?, ';
          $data[] = trim($users_array[$counter]);
      }
      $tmp_sql = substr($tmp_sql, 0, strlen($tmp_sql) - 2);
    }

    if(isset($tmp_sql)) {
      $sql = "SELECT * FROM {users} WHERE $key_column IN ($tmp_sql)";
    } else {
      $sql = "SELECT * FROM {users} WHERE is_active = 1";
    }
    $res = Dal::query($sql, $data);

    if (!$res->numRows()) {
      Logger::log(" Throwing exception USER_NOT_FOUND | Message: User does not exist.", LOGGER_ERROR);
      throw new PAException(USER_NOT_FOUND, "User $user_ids_or_login_names does not exist");
    }

    $uid_array = array();
    $i = 0;
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $uid_array[$i]['user_id'] = $row->user_id;
      $uid_array[$i]['picture'] = $row->picture;
      $uid_array[$i]['login_name'] = $row->login_name;
      $uid_array[$i]['first_name'] = $row->first_name;
      $i++;
    }

    Logger::log("Exit: function User::load_users");
    return $uid_array;
  }

  /**
  * @param $params - array of extra params
  * alias of load_user_roles()
  * @return array of user $roles
  */
  public function get_user_roles($params = null, $fetch_mode = DB_FETCHMODE_OBJECT) {
     return $this->load_user_roles($params, $fetch_mode);
  }

   /**
  *
  * @return array of user $roles for addressed network
  */
  public function get_user_roles_by_network($fetch_mode = DB_FETCHMODE_OBJECT, $network_address) {
     return $this->load_user_roles(array('by_address' => $network_address), $fetch_mode);
  }

  /**
  * check
  * @return TRUE if user have role with appropriate name
  */
  public function has_role_name($role_name) {
    $result = false;

    $sql = 'SELECT * FROM {roles} WHERE name = ?';
    $data = array($role_name);
    $res = Dal::query($sql, $data);
    $r = $res->fetchRow(DB_FETCHMODE_OBJECT);

    foreach($this->role as $role_obj) {
      if($role_obj->role_id == $r->id) {
         $result = true;
         break;
      }
    }
    return $result;
  }

  /**
  * check
  * @return TRUE if user have role with appropriate role ID
  */
  public function has_role_id($role_id) {
    $result = false;

    foreach($this->role as $role_obj) {
      if($role_obj->role_id == $role_id) {
         $result = true;
         break;
      }
    }
    return $result;
  }


  /**
  *
  * @param: $params['by_address'] - all user roles for a Network by network address
  * @param: $params['type'] - all user roles by type: user, network or group roles
  *
  *
  *
  * Load the user roles for the given user_id
  * @return $roles array of user roles id's
  */
  private function load_user_roles($params = null, $fetch_mode = DB_FETCHMODE_OBJECT) {
    Logger::log("Enter: function User::load_user_roles");

    if (!$this->is_active) {
     throw new PAException(OPERATION_NOT_PERMITTED, "Trying to load deleted user");
    }

    if(!empty($params['by_address']) && ($params['by_address'] != 'default')) {
       $sql = 'SELECT * FROM '.$params['by_address'].'_users_roles WHERE user_id = ?';
       $data = array($this->user_id);
    } else if(!empty($params['type'])) {
       $sql = "SELECT UR.role_id, UR.user_id, R.name, R.description, R.read_only, R.type, UR.extra
               FROM {users_roles} AS UR
               INNER JOIN {roles} AS R ON UR.role_id = R.id
               WHERE UR.user_id = ?
               AND R.type = ?
               GROUP BY R.id
               ORDER BY UR.role_id ASC
               LIMIT 0 , 30";
       $data = array($this->user_id, $params['type']);
    } else {
       $sql = "SELECT UR.role_id, UR.user_id, R.name, R.description, R.read_only, R.type, UR.extra
               FROM {users_roles} AS UR
               INNER JOIN {roles} AS R ON UR.role_id = R.id
               WHERE UR.user_id = ?
               GROUP BY R.id
               ORDER BY UR.role_id ASC
               LIMIT 0 , 30";
       $data = array($this->user_id);
    }
//    echo "SQL = " . $sql ."<br />";
    $res = Dal::query($sql, $data);

    $row = array();
    while ($r = $res->fetchRow($fetch_mode)) {
      if((!empty($params['type'])) && ($params['type'] == 'group') && (!empty($params['gid']))) {
//      echo "<pre>".print_r($r, 1) . "</pre>";
        $role_extra = ($fetch_mode == DB_FETCHMODE_OBJECT) ? unserialize($r->extra) : unserialize($r['extra']);
        if(count($role_extra['groups']) > 0) {
          $apply_to_groups = $role_extra['groups'];
          if(in_array($params['gid'], $apply_to_groups)) {
            $row[] = $r;
          }
        }
      } else {
        $row[] = $r;
      }
    }

    Logger::log("Exit: function User::load_user_roles");
    return $row;
  }


  private static function build_auth_token($login, $password_md5, $expires) {
    $base_token = $login . ":$expires";
    $sig = sha1($base_token . ':' . $password_md5);
    return "$base_token:$sig";
  }

  /**
   * Generate an authentication token with the given lifetime (seconds).
   */
  public function get_auth_token($lifetime) {
    // FIXME: generate a more opaque token - this method probably isn't secure
    $expires = time() + $lifetime;
    return User::build_auth_token($this->login_name, $this->password, $expires);
  }

  public static function from_auth_token($token) {
    if (!preg_match("/^(.*?):(\d+):([0-9a-f]+)$/", $token, $m)) {
      throw new PAException(USER_TOKEN_INVALID, "This token is invalid - bad format");
    }
    list($_foo, $login, $expires, $sig) = $m;
    // check expiry
    $lifetime = $expires - time();
    if ($lifetime < 0) {
      throw new PAException(USER_TOKEN_EXPIRED, "The token has already expired ($lifetime seconds ago)");
    }
    // load user
    $user = new User();
    $user->load($login);
    // validate signature [password]
    $calc_token = User::build_auth_token($login, $user->password, $expires);
    if ($calc_token != $token) {
      throw new PAException(USER_TOKEN_INVALID, "The token is not valid; the signature is incorrect");
      // removed " (calculated $calc_token, received $token)"
      // from this error message, as it allows anyone to 'try' for a valid token
      // ad actualy BE SHOWN THE RIGHT THING
    }
    // stash the token expiry time in the user object
    $user->auth_token_expires = intval($expires);
    return $user;
  }

  /** fetch a bunch of user ids, given a list of names **/
  public static function map_logins_to_ids($login_names) {
    $ret = array();

    if (!is_array($login_names)) {
      // If it's a single user id.
      $login_names = array( $login_names );
    }
    foreach ($login_names as $login_name) {
    $r = Dal::query_one("SELECT user_id FROM {users} WHERE login_name = ?", array($login_name));
    if (!$r) {
      throw new PAException(USER_NOT_FOUND, "User with login name '$login_name' not found");
    }
    $ret[$login_name] = $r[0];
    }
    return $ret;
  }

  /** fetch a bunch of login names, given a list of ids **/
  public static function map_ids_to_logins($user_ids) {
    $ret = array();
    if( !is_array( $user_ids ) ) {
      //if its single user id
      $user_ids = array( $user_ids );
    }
    foreach ($user_ids as $user_id) {
      $r = Dal::query_one("SELECT login_name FROM {users} WHERE user_id = ?", array($user_id));
      if (!$r) {
    throw new PAException(USER_NOT_FOUND, "User with id '$user_id' not found");
      }
      $ret[$user_id] = $r[0];
    }
    return $ret;
  }

  /**
  * Save the user data to the database
  *
  * When creating a new user, set all the attributes for the user (except user_id) and call save. Save will
  * set the user_id for the user.
  *
  */
  public function save() {
    Logger::log("Enter: function User::save");
    // global var $_base_url has been removed - please, use PA::$url static variable

    $sql = '';

    try {

      if (!$this->login_name || !$this->password || !$this->first_name || !$this->email) {
        Logger::log("Throwing exception REQUIRED_PARAMETERS_MISSING | Message: Required parameters missing", LOGGER_ERROR);
        throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required parameters missing");
      }

      if (!$this->is_active) {
        Logger::log("Throwing exception SAVING_DELETED_USER | Message: Saving a deleted user is not allowed", LOGGER_ERROR);
        throw new PAException(SAVING_DELETED_USER,"Saving a deleted user is not allowed");
      }

      if(!is_valid_web_image_name($this->picture)) { // fix invalid image names
        $this->picture = '';
        Logger::log("Throwing exception INVALID_USER_IMAGE_FORMAT | Message: Invalid user image format", LOGGER_ERROR);
        throw new PAException(SAVING_DELETED_USER,"Invalid image format");
      }
      // added to remove unnecessary check whether the word begins or ends with a 'space' character
      $this->first_name = @trim($this->first_name);
      $this->last_name  = @trim($this->last_name);
      $this->login_name = @trim($this->login_name);
      $this->password   = @trim($this->password);
      $this->email      = @trim($this->email);
      // checking the user data When creating a new user or updating the existing user value

      $this->check_authenticated_user_data();

      if ($this->is_new) {
        // Make sure that the login name is unique.
        $sql = 'SELECT * FROM {users} WHERE login_name = ? AND is_active <> ? AND is_active <> ?';
        $data = array($this->login_name, DELETED, UNVERIFIED);
        $res = Dal::query($sql, $data);

        if ($res->numRows() > 0) {
          Logger::log(" Throwing exception USER_LOGINNAME_TAKEN | Message: This Login name has already been taken", LOGGER_ERROR);
          throw new PAException(USER_LOGINNAME_TAKEN,"This Login name has already been taken");
        }
        // make sure that the email address is unique
        $sql = 'SELECT * FROM {users} WHERE email = ? AND is_active <> ?';
        $data = array($this->email, DELETED);
        $res = Dal::query($sql, $data);

        if (($res->numRows() > 0)) {
          Logger::log(" Throwing exception USER_EMAIL_NOT_UNIQUE | Message: Email address must be unique", LOGGER_ERROR);
          throw new PAException(USER_EMAIL_NOT_UNIQUE, "Email address that you have given is already taken please give another email address");
        }


        $this->user_id = Dal::next_id("User");
        $this->password = md5($this->password);
        if(!isset($this->created)) {
          $this->created = time();
        }
        $this->changed = $this->created;
        $this->last_login = time();

        $sql = 'INSERT into {users} (user_id, login_name, password, first_name, last_name, email, is_active, created, changed, picture, last_login) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $data = array($this->user_id, $this->login_name, $this->password, $this->first_name, $this->last_name, $this->email, $this->is_active, $this->created, $this->changed, $this->picture, $this->last_login);
        Dal::query($sql, $data);

    // Code for sending the data to ping server: begin

        $PingClient = new PingClient();
        global $host; // defined in config.inc
        // global var $path_prefix has been removed - please, use PA::$path static variable
        $pa_url = $host;
        $pa_activity = PA_ACTIVITY_USER_ADDED;
        $pa_user_url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $this->user_id;
        $pa_user_name = $this->first_name.' '.$this->last_name;
        $param_array = array(
        'pa_url'=> $pa_url,
        'pa_activity'=> $pa_activity,
        'pa_user_url'=> $pa_user_url, 'pa_user_name'=>$pa_user_name
    );

        $PingClient->set_params($param_array);
        // @$PingClient->send_ping();

    // Code for sending the data to ping server: end

        // By default first user is being assigned as ADMIN (admin role id is 2).
        if ($this->user_id == SUPER_USER_ID) {
          $user_roles = array();
          $user_roles[0] = array('role_id' => ADMINISTRATOR_ROLE, 'extra' => serialize(array('user' => false, 'network' => true, 'groups' => array())));
          $this->set_user_role($user_roles);
        }
      } else {
        // make sure that the email address is unique
        $sql = 'SELECT * FROM {users} WHERE email = ?';
        $data = array($this->email);
        $res = Dal::query($sql, $data);

        if (($res->numRows() > 0)) {
          $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
          if ($row->user_id != $this->user_id) {
            Logger::log(" Throwing exception USER_EMAIL_NOT_UNIQUE | Message: Email address must be unique", LOGGER_ERROR);
            throw new PAException(USER_EMAIL_NOT_UNIQUE, "Email address that you have given is already taken please give another email address");
          }
        }

        $sql = 'UPDATE {users} SET login_name = ?, password = ?, first_name = ?, last_name = ?, email = ?, is_active = ?, changed = ?, picture = ? WHERE user_id = ?';
        $data = array($this->login_name, $this->password, $this->first_name, $this->last_name, $this->email, 1, time(), $this->picture, $this->user_id);
        Dal::query($sql, $data);
      }

      // all done - commit to database
      Dal::commit();
    } catch (PAException $e) {
      Dal::rollback();
      throw $e;
    }
        // save the core user data so that search can find it
        $data = array();
        $data['first_name'] = $this->first_name;
        $data['last_name'] = $this->last_name;
        $data['email'] = $this->email;
        $data['login_name'] = $this->login_name;

        $old_data = User::load_user_profile($this->user_id, $this->user_id, BASIC, null);
        // ensure we are NOT duplicating data here!!
    foreach ($old_data as $i=>$d) {
        $k = $d['name'];
        $v = $d['value'];
        if (empty($data[$k])) {
            // only ever preserve if we are NOT submiting this field
            $data[$k] = $v;
        }
    }
    // turn it all to a format that this function undersatbds
    $user_data = array();
    foreach ($data as $k=>$v) {
        $user_data[] = array(
            'name' => $k,
            'value' => $v,
            'uid' => $this->user_id,
            'perm' => 1,
            'type' => BASIC
        );
    }
        $this->save_user_profile($user_data, BASIC);

    $this->is_new = FALSE;

    if ($this->tags) {
      // Attach an array of string tags to the user
      //Tag::add_tags_to_user($this->user_id, $this->tags);
    }

    Logger::log("Exit: function User::save");
  }


  /**
  * authenticate the user data When creating a new user or updating existing user value
  *
  */
  private function check_authenticated_user_data () {

    // Checking Login name of the user in the system and can consist of alphanumeric characters and underscores.
    if(!Validation::validate_auth_id($this->login_name)) {
      Logger::log("Throwing exception USER_INVALID_LOGIN_NAME | Message: The login name is not a valid authentication ID. Name: " . $this->login_name, LOGGER_ERROR);
      throw new PAException(USER_INVALID_LOGIN_NAME,'The login name is not a valid authentication ID. Name: ' . $this->login_name);
    }

    if (strlen($this->login_name) > 50) {
      Logger::log("Throwing exception USER_INVALID_LOGIN_NAME | Message: The login name is too long: it must be less than 16 characters. Name: " . $this->login_name, LOGGER_ERROR);
      throw new PAException(USER_INVALID_LOGIN_NAME,'The login name is too long: it must be less than 16 characters. Name: ' . $this->login_name);
    }

    if (strlen($this->login_name) < 3) {
      Logger::log("Throwing exception USER_INVALID_LOGIN_NAME | Message: The login name is too short: it must be greater than 5 characters. Name: " . $this->login_name, LOGGER_ERROR);
      throw new PAException(USER_INVALID_LOGIN_NAME,'The login name is too short: it must be greater than 2 characters. Name: ' . $this->login_name);
    }

    // Checking First name of the user.
    if (!Validation::validate_name($this->first_name)) {
      Logger::log("Throwing exception USER_INVALID_NAME | Message: The first name is not a valid authentication ID. Name: " . $this->first_name, LOGGER_ERROR);
      throw new PAException(USER_INVALID_NAME,'The first name is not a valid authentication ID. Name: ' . $this->first_name);
    }

    if (strlen($this->first_name) > 45) {
      Logger::log("Throwing exception USER_INVALID_NAME | Message: The first name is too long: it must be less than 45 characters. Name: " . $this->first_name, LOGGER_ERROR);
      throw new PAException(USER_INVALID_NAME,'The first name is too long: it must be less than 45 characters. Name: ' . $this->first_name);
    }

    // Checking last name of the user.
    if (!Validation::validate_name($this->last_name)) {
      Logger::log("Throwing exception USER_INVALID_LOGIN_NAME | Message: The last name is not a valid authentication ID. Name: " . $this->last_name, LOGGER_ERROR);
      throw new PAException(USER_INVALID_NAME,'The last name is not a valid authentication ID. Name: ' . $this->last_name);
    }

    if (strlen($this->last_name) > 45) {
      Logger::log("Throwing exception USER_INVALID_NAME | Message: The last name is too long: it must be less than 45 characters. Name: " . $this->last_name, LOGGER_ERROR);
      throw new PAException(USER_INVALID_NAME,'The last name is too long: it must be less than 45 characters. Name: ' . $this->last_name);
    }

    // Check for email.
    if (!Validation::validate_email($this->email)) {
      Logger::log("Throwing exception USER_INVALID_EMAIL | Message: The email address is invalid. Email: " . $this->email, LOGGER_ERROR);
      throw new PAException(USER_INVALID_EMAIL,'The email address is invalid. Email: ' . $this->email);
    }

    return TRUE;
  }

  /**
  * set last login time
  */
  public function set_last_login () {
     Logger::log("Enter: function User::set_last_login");

     $sql = 'UPDATE {users} SET last_login = ? WHERE user_id = ?';
     $data = array(time(), $this->user_id);
     $res = Dal::query($sql, $data);

     Logger::log("Exit: function User::set_last_login");
   }


  /**
  * Delete the user from the system
  *
  * User data is never 'hard deleted' from the database. Instead the 'is_active' flag is set to 1
  *
  */
  public static function delete($user_id, $true_delete = FALSE) {
    Logger::log("Enter: function User::delete");

    // check whether user is already deleted
                if($true_delete) {
      $sql = 'SELECT * from {users} WHERE user_id=?';
      $data = array($user_id);
                } else {
      $sql = 'SELECT * from {users} WHERE user_id=? AND is_active = ?';
      $data = array($user_id, DELETED);
                }
    $res = Dal::query($sql, $data);
    if (($res->numRows() > 0) && !$true_delete) {
      Logger::log("Throwing exception USER_ALREADY_DELETED | message: Can not delete a deleted user");
      throw new PAException(USER_ALREADY_DELETED, "Can not delete a deleted user.");
    } else {
                if($true_delete) {
        $sql  = 'DELETE FROM {users} WHERE user_id = ?';
        $sql1 = 'DELETE FROM {user_profile_data} WHERE user_id = ?';
        $data = array($user_id);
        $res = Dal::query($sql, $data);
        $res = Dal::query($sql1, $data);
                        } else {
        $sql = 'UPDATE {users} SET login_name = CONCAT(?, login_name), email = CONCAT(?, email), is_active = ? WHERE user_id = ?';
        $data = array(MARK_DELETED_USER.$user_id.MARK_DELETED_USER, MARK_DELETED_USER.$user_id.MARK_DELETED_USER, DELETED, $user_id);
        //updated login_name will be of the kind #17#gurpreet.Here 17 is the user_id, gurpreet is login_name and
        # is serving as delimiter.
        $res = Dal::query($sql, $data);
                        }
    }

    // make sure there aren't any lingering entries in networks_users or groups_users
    Network::leave_all_networks($user_id);
    Group::leave_all_groups($user_id);

    Logger::log("Exit: function User::delete");
  }


  /**
  * This function checks for existence of the user whether user data exist in the database or not
  */
  public static function user_exist($user_id_or_login_name) {
    Logger::log("Enter: function User::user_exist");

    // check if $user_id is an integer or string
    if (is_int($user_id_or_login_name)) {
      $sql = 'SELECT login_name FROM {users} WHERE user_id = ? AND is_active <> ?';
    } else if (is_string($user_id_or_login_name)) {
      $sql = 'SELECT user_id FROM {users} WHERE login_name = ? AND is_active <> ?';
    } else {
      throw new PAException(USER_INVALID_LOGIN_NAME, "parameter to user_exist() must be a string or integer");
    }

    $data = array($user_id_or_login_name, DELETED);
    $res = Dal::query($sql, $data);

    if ($res->numRows() > 0) {
      $row = $res->fetchRow();
      $return = $row[0];
    } else {
      $return = false;
    }

    Logger::log("Exit: function User::user_exist");

    return $return;
  }

  // Returns TRUE if there exists a deleted user with the specified user id or login name.
  public static function user_existed($user_id_or_login_name) {
    $key = is_int($user_id_or_login_name) ? 'user_id' : 'login_name';
    return Dal::query_first("SELECT COUNT(*) FROM users WHERE $key=? AND is_active=?", array($user_id_or_login_name, DELETED)) ? TRUE : FALSE;
  }

  /**
  * Take the $login_name, $password as an input, Return user_id if
  * user is Authenticated. Return false is user *is not present in
  * database
  *
  * @param string $login_name The login name of the user whose information needs to be loaded
  * @param string $password The password of the user whose information needs to be loaded
  * @return integer $id_of_authenticated_user This is the id of the authenticated user.
  */
  static function authenticate_user($login_name, $password) {
    Logger::log("Enter: function User::authenticate_user");

    $id_of_authenticated_user = null;

    if(!$login_name) {
      Logger::log("Throwing exception");
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required variable not specified");
    }

    if(!$password) {
      Logger::log("Throwing exception");
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "required variable not specified");
    }

    // Checking password in the database for the corresponding
    // login_name whether it exist already or not and the password
    // belongs to that login_name or not, if it does not exist then it
    // throw an exception.
    $password = md5($password);

    $sql = 'SELECT user_id, is_active FROM {users} WHERE login_name = ? AND password = ?';
    $data = array($login_name,$password);
    $res = Dal::query($sql, $data);
    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ;
      switch ($row->is_active) {
      case DELETED:
    // User has been deleted
    throw new PAException(USER_ALREADY_DELETED, 'Your account has been deleted by the administrator.');
      case DISABLED:
    // If user is disabled by the network administrator.
        throw new PAException(USER_DISABLED, 'Your account has been disabled by the administrator.');
      case UNVERIFIED:
        throw new PAException(SAVING_UNVERIFIED_USER, 'Your account has not been verified, Check your mail to activate your account');
      default:
    // log in OK
    $id_of_authenticated_user = $row->user_id;
    break;
      }
    }

    Logger::log("Exit: function User::authenticate_user");

    // Now user is authenticated so return its user_id.
    return (int)$id_of_authenticated_user;
  }

  /**
  * Delete User role(s)
  * @param array of integers $role_id
  */
  public function delete_user_role($user_roles = null, $group_id = null) {
    Logger::log("Enter: function User::delete_user_role");
    if(empty($user_roles)) {  // if function called without argument - delete all user roles
      $sql = 'DELETE FROM {users_roles} WHERE user_id = ?';
      $data = array($this->user_id);
      Dal::query($sql, $data);
    } else {
        if($group_id) {

           for ($i = 0; $i < count($user_roles); $i++) {
             if(!is_null($user_roles[$i]['role_id'])) {  // remove Group role
               $sql = 'SELECT * FROM {users_roles} WHERE user_id = ? AND role_id = ?';
               $data = array($this->user_id, $user_roles[$i]['role_id']);
             }
             else {         // remove all roles for a Group
               $sql = 'SELECT * FROM {users_roles} WHERE user_id = ?';
               $data = array($this->user_id);
             }
             $res = Dal::query($sql, $data);
             while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
               if(is_object($row)) {
                 $role_info = Roles::getRoleInfoByID((int)$row->role_id);
                 $role_extra = (!empty($row->extra)) ? unserialize($row->extra) : array();
                 if($role_info['type'] == 'group') {
                   $new_groups = array();
                   for($cnt = 0; $cnt < count($role_extra['groups']); $cnt++) {
                     if((int)$role_extra['groups'][$cnt] != (int)$group_id) {
                       $new_groups[] = (int)$role_extra['groups'][$cnt];
                     } else {
                       // do nothing  - for now
                     }
                   }
                   $role_extra['groups'] = $new_groups;
                 }
                 if(count($role_extra['groups']) > 0) { // user has this role in some other Groups!
                   $sql = 'UPDATE {users_roles} SET extra = ? WHERE user_id = ? AND role_id = ?';
                   $data = array(serialize($role_extra), $this->user_id, $row->role_id);
                 } else { // no groups to apply this role - so, delete it
                   $sql = 'DELETE FROM {users_roles} WHERE user_id = ? AND role_id = ?';
                   $data = array($this->user_id, $row->role_id);
                 }
                 Dal::query($sql, $data);
               }
             }
           }
         }
         else {
           for ($i = 0; $i < count($user_roles); $i++) {
             $sql = 'DELETE FROM {users_roles} WHERE user_id = ? AND role_id = ?';
             $data = array($this->user_id, $user_roles[$i]['role_id']);
             Dal::query($sql, $data);
           }
         }
    }
    Logger::log("Exit: function User::delete_user_role");
  }

  /**
  * Setting User role into database for the corresponding user and then load user role whenever that user is loaded.
  * @param array of integers $user_role User is defining their access permission through this user role
  */
  public function set_user_role($user_roles) {
    Logger::log("Enter: function User::add_user_role");

    if (!$this->is_active) {
      throw new PAException(OPERATION_NOT_PERMITTED, "User is already been deleted");
    }

    if (empty($user_roles)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required variable not specified");
    }

    // Inserting user roles for corresponding user_id.
    for ($i = 0; $i < count($user_roles); $i++) {
      $role_info = Roles::getRoleInfoByID((int)$user_roles[$i]['role_id']);
      $sql = 'SELECT * FROM {users_roles} WHERE user_id = ? AND role_id = ?';
      $data = array($this->user_id, $user_roles[$i]['role_id']);
      $res = Dal::query($sql, $data);
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      if(!empty($row) && is_object($row)) {       // role exists - just update role extra info
        if(!empty($user_roles[$i]['extra'])) {
          if($role_info['type'] == 'group') {
            $existing_extra = (!empty($row->extra)) ? unserialize($row->extra) : array();
            $new_extra = unserialize($user_roles[$i]['extra']);
            foreach($new_extra['groups'] as $grp) {
              if(empty($existing_extra['groups'])) {
                $existing_extra['groups'] = array((int)$grp);
              }
              else {
                foreach($existing_extra['groups'] as $_i => $_v) {
                  $existing_extra['groups'][$_i] = (int)$_v;
                }
                if(!in_array((int)$grp, $existing_extra['groups'])) {
                  array_push($existing_extra['groups'], (int)$grp);
                }
              }
            }
            $user_roles[$i]['extra'] = serialize($existing_extra);
          }
          $sql = 'UPDATE {users_roles} SET extra = ? WHERE user_id = ? AND role_id = ?';
          $data = array($user_roles[$i]['extra'], $this->user_id, $user_roles[$i]['role_id']);
        }
      } else {                                    // role does not exists - assign new role
        if(empty($user_roles[$i]['extra'])) {      // if extra info is empty - set default extras for new user role
          $user_roles[$i]['extra'] = array('user' => true, 'network' => true, 'groups' => array());
        }
        $sql = 'INSERT into {users_roles} (user_id, role_id, extra) values (?, ?, ?)';
        $data = array($this->user_id, $user_roles[$i]['role_id'], $user_roles[$i]['extra']);
      }
      Dal::query($sql, $data);
    }
    Logger::log("Exit: function User::add_user_role");
  }

// replaced with new function ******* should be removed
  /**
  * Delete User role from database for the corresponding user.
  * @param array of $user_role IDs
  */
/*
  public function delete_user_role($user_role) {
    Logger::log("Enter: function User::delete_user_role");

    if (!$this->is_active) {
      throw new PAException(OPERATION_NOT_PERMITTED, "User is already been deleted");
    }

    if (empty($user_role)) {
      throw new PAException(REQUIRED_PARAMETERS_MISSING, "Required variable not specified");
    }

   if(is_array($user_role) and (@$user_role[0] == -1)) {    // -1 = delete all user roles
        $sql = 'DELETE FROM {users_roles} WHERE user_id = ?';
        $data = array($this->user_id);
        Dal::query($sql, $data);
   } else {
      // Delete user roles specified with $user_role array.
      for ($i=0; $i<count($user_role); $i++) {
        $sql = 'DELETE FROM {users_roles} WHERE user_id = ? AND role_id = ?';
        $data = array($this->user_id, $user_role[$i]);
        Dal::query($sql, $data);
      }
    }
    Logger::log("Exit: function User::delete_user_role");
  }
*/

  public static function has_network_permissions($uid, $task_values, $strict = true) {
    Logger::log("Enter: function User::has_network_permissions");
    $result = self::can_user($uid, $task_values, 'network', $strict);
    Logger::log("Exit: function User::has_network_permissions");
    return $result;
  }

  public static function has_group_permissions($uid, $gid, $task_values, $strict = true) {
    Logger::log("Enter: function User::has_group_permissions");
    $result = self::can_user($uid, $task_values, 'group', $strict, $gid);
    Logger::log("Exit: function User::has_group_permissions");
    return $result;
  }

  /**
   Purpose : this function check user task permissions for a Group or a Network
   @param : $uid, $task_values, $type, $target_id, $strict
   @return : bool
  **/
  public static function can_user($uid, $task_values, $type = 'network', $strict, $target_id = null) {
    Logger::log("Enter: function User::can_user");

    if (SUPER_USER_ID == $uid) { // SUPER USER has all permissions!
      return TRUE;
    }

    if(!is_array($task_values)) {
      $task_values = array($task_values);
    }

    $user = new self();
    $user->load($uid);
    $roles = $user->get_user_roles(DB_FETCHMODE_OBJECT);

    $result = false;
    $user_tasks = array();
    foreach($roles as $role) {             // merge all tasks/permissions for specific role type
//      $role_obj = new Roles();
//      $role_obj->load((int)$role->role_id);
      $condition = ($type == 'network') ? ($role->extra['network'] == true) // apply role to network
                                        : ((count($role->extra['groups']) > 0)
                                            && in_array($target_id, $role->extra['groups'])
                                          ); // apply role to a group
//      if(($role_obj->type == $type) && $condition) {
      if($condition) {
        $role_tasks = Roles::get_tasks_of_role($role->role_id);
        if($role_tasks) {
          foreach($role_tasks as $rt) {
            $user_tasks[] = $rt->task_value;
          }
        }
      }
    }

    $found = 0;
    $nb_tasks = count($task_values);
    foreach($task_values as $value) {
      if(!in_array($value, $user_tasks) && ($strict == true)) {
        $result = false;
        break;
      }
      if(in_array($value, $user_tasks) && ($strict == false)) {
        $result = true;
        break;
      }
      if(in_array($value, $user_tasks) && ($strict == true)) {
        $found++;
      }
    }

    if($strict == true) {
      $result = ($found == $nb_tasks) ? true : false;
    }

    Logger::log("Exit: function User::can_user");
    return $result;
  }


  /**
  * This method set the profile in to the database for a specific type.
  * @param $array_user_data An associative array of all profile data like array([0] => array('uid'=> , 'name'=>, 'value'=>, 'type'=>, 'perm'=>), [1] => array () )
  */
  public function save_user_profile($array_user_data, $information_type) {
    Logger::log("Enter: User::save_user_profile");

    try {
      // Delete all profile data for the user.
      $sql = 'DELETE FROM {user_profile_data} WHERE user_id = ? AND field_type = ?';
      $data = array($this->user_id, $information_type);
      Dal::query($sql, $data);

      // Inserting data of user profile
      if(count($array_user_data)) {
        foreach ($array_user_data as $user_data) {
          if (empty($user_data['perm'])) {
            // If permission is not set then by default it is taking NONE.
            $user_data['perm'] = 0;
          }
          $sql = 'INSERT into {user_profile_data}
            (user_id, field_name, field_value, field_type, field_perm, seq)
            values (?, ?, ?, ?, ?, ?)';
          $data = array($user_data['uid'], $user_data['name'],
            @$user_data['value'], $user_data['type'],
            $user_data['perm'], @$user_data['seq']);
          Dal::query($sql, $data);
        }
      }
      // Commit transaction.
      Dal::commit();
    } catch (PAException $e) {
      Dal::rollback();
      throw $e;
    }
    Logger::log("Exit: User::save_user_profile");
  }

  /**
  * Method to load information of selected profile.
  * order by can be applied like $order_by => 'seq' or 'seq DESC' or 'seq ASC'
  */
  public static function load_user_profile($user_id, $my_user_id, $information_type=null, $order_by=null) {
    Logger::log("Enter: User::load_user_profile");
    $relations = array();
    $user_profile_data = array();
    $i=0;

    if (is_null($information_type)) {
      $sql = 'SELECT * FROM {user_profile_data} WHERE user_id = ? ';
      $data = array($user_id);
    } else {
      $sql = 'SELECT * FROM {user_profile_data} WHERE user_id = ? AND field_type = ?';
      $data = array($user_id, $information_type);
    }

    //added for gurpreet
    if(!empty($order_by)) {
      $sql .= ' ORDER BY '.$order_by;
    }

    $res = Dal::query($sql, $data);

    if ($res->numRows() > 0) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $array_of_data[$i]['uid'] = $row->user_id;
        $array_of_data[$i]['name'] = $row->field_name;
        $array_of_data[$i]['value'] = $row->field_value;
        $array_of_data[$i]['type'] = $row->field_type;
        $array_of_data[$i]['perm'] = $row->field_perm;
        $array_of_data[$i]['seq'] = ($row->seq) ? (int)$row->seq : NULL;
        $i++;
      }
    }

    if (!empty($array_of_data)) {
       // Getting degree 1 friendlist
      $relations = Relation::get_relations($user_id, null, PA::$network_info->network_id);

      if ($user_id == $my_user_id) {
      //Logged in user is viewing its own blog.
        $user_profile_data = $array_of_data;

      } else if (in_array($my_user_id, $relations)) {
      //some from user's relations is viewing its blog

        //check whether relation is in user's family
        $in_family = Relation::is_relation_in_family($user_id, $my_user_id);
        foreach ($array_of_data as $user_data) {
          //user data viewable to family members.
          if ($user_data['perm'] == IN_FAMILY && $in_family) {
            $user_profile_data[] = $user_data;
          }

          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
            $user_profile_data[] = $user_data;
          }

        }//end for

      } else {
        //user data viewable to user which is not in relation wth given user.
        foreach ($array_of_data as $user_data) {
          if ($user_data['perm'] == ANYONE) {
            $user_profile_data[] = $user_data;
          }
        }
      }

    }//end outer if

    Logger::log("Exit: User::load_user_profile");
    return $user_profile_data;
  }

  // Fetch a single profile field for the current user
  public function get_profile_field($information_type, $field_name) {
    $cache_key = "profile:$this->user_id:$information_type:$field_name";
    $data = Cache::getValue($cache_key);
    if ($data === NULL) {
      $data = Dal::query_first("SELECT field_value FROM {user_profile_data} WHERE user_id=? AND field_type=? AND field_name=? AND seq IS NULL LIMIT 1",
    array($this->user_id, $information_type, $field_name));
      Cache::setValue($cache_key, $data);
    }
    return $data;
  }

  // Fetch several profile fields for the current user.
  //
  // If fields are not available, they will be returned as NULL.
  // e.g. $user->get_profile_fields(GENERAL, array("desktop_image_display", "sub_caption")) could return
  // array("desktop_image_display" => 1, "sub_caption" => "this is my caption")
  // or array("desktop_image_display" => NULL, "sub_caption" => "this is my caption")
  public function get_profile_fields($information_type, $field_names) {
    $data = array();

    // look for already-cached data, and build sql to fill out the
    // final part of the WHERE clause - e.g. ... "AND field_name IN
    // (?, ?, ?)"
    $args = array($this->user_id, $information_type);
    $field_names_sql = array();
    foreach ($field_names as $field_name) {
      $cache_key = "profile:$this->user_id:$information_type:$field_name";
      $field_value = $data[$field_name] = Cache::getValue($cache_key);
      if ($field_value == NULL) {
    $args[] = $field_name;
    $field_names_sql[] = "?";
      }
    }

    if (!empty($field_names_sql)) {
      // we don't have all the required data yet: SELECT out what is
      // left to get, put it into the data array ($data =
      // array("field1" => "field value 1", ...)), and cache it.
      $sth = Dal::query("SELECT field_name, field_value FROM {user_profile_data} WHERE user_id=? AND field_type=? AND field_name IN (".implode(", ", $field_names_sql).") AND seq IS NULL", $args);
      while ($r = Dal::row($sth)) {
    list($field_name, $field_value) = $r;
    $data[$field_name] = $field_value;
    $cache_key = "profile:$this->user_id:$information_type:$field_name";
    Cache::setValue($cache_key, $field_value);
      }
    }

    return $data;
  }

  // Set a single profile field for the current user
  public function set_profile_field($information_type, $field_name, $field_value, $field_perm=1) {
    $cache_key = "profile:$this->user_id:$information_type:$field_name";
    Dal::query("DELETE FROM {user_profile_data} WHERE user_id=? AND field_type=? AND field_name=?",
           array($this->user_id, $information_type, $field_name));
    Dal::query("INSERT INTO {user_profile_data} SET user_id=?,  field_type=?, field_name=?, field_value=?, field_perm=?",
           array($this->user_id, $information_type, $field_name, $field_value, $field_perm));
    Cache::removeValue($cache_key);
  }

  // DELETE a single profile field for the current user
  public function delete_profile_field($information_type, $field_name, $seq = NULL) {
    $cache_key = "profile:$this->user_id:$information_type:$field_name";
    Dal::query("DELETE FROM {user_profile_data} WHERE user_id=? AND field_type=? AND field_name=? AND seq=?",
           array($this->user_id, $information_type, $field_name, $seq));
    Cache::removeValue($cache_key);
  }

  // Load profile data in a more accessible way.
  public static function load_profile_section($uid, $slot, $viewer_uid=null) {
    if (!$viewer_uid) $viewer_uid = $uid;
    $user_profile = User::load_user_profile($uid, $viewer_uid, $slot, null);
    $profile_data = array();
    $c = count($user_profile);
    for ($i=0; $i < $c; $i++) {
      $k = $user_profile[$i]['name'];
      $v = $user_profile[$i];
      if ($v['seq']) {
        // we have several rows for this name
        // it belongs to a collection
        $profile_data[$k][$v['seq']] = $v;
      } else {
        // there is only one of this kind
        $profile_data[$k] = $v;
      }
    }
    return $profile_data;
  }



  // Save profile data in a nore accessible way
  public function save_profile_section($data, $type, $preserve=false) {
    $uid = $this->user_id;
    $array_user_data = array();
    if ($preserve) {
        $old_data
        = User::load_user_profile($this->user_id, $this->user_id, $type, null);
        // ensure we are NOT duplicating data here!!
        foreach ($old_data as $i=>$d) {
            $k = $d['name'];
            if (empty($data[$k])) {
                // only ever preserve if we are NOT submiting this field
                $array_user_data[] = $d;
            }
        }
    }
    foreach ($data as $k=>$v) {
      if (isset($v[1])) {
        // we have a collection here
        // make flat and pull out the sequence
        foreach ($v as $seq=>$d) {
          if (! is_int($seq)) {continue;} // small cleanup
          $field = $d; // Array with keys 'value' and 'perm'
          $field['uid'] = $uid;
          $field['name'] = $k;
          $field['type'] = $type;
          $field['seq'] = $seq; // we actually have a value here now
          $array_user_data[] = $field;
        }
      } else {
        // standard case, single row
        $field = $v; // Array with keys 'value' and 'perm'
        $field['uid'] = $uid;
        $field['name'] = $k;
        $field['type'] = $type;
        $field['seq'] = NULL;
        $array_user_data[] = $field;
      }
    }
    try {
      $this->save_user_profile($array_user_data, $type);
    } catch (PAException $e) {
      throw ( $e );
      return false;
    }
    return true;
  }


  // Look up user ID from login name in $login.
  // Returns the user ID on success, or if the user doesn't exist,
  // throws a PAException(USER_NOT_FOUND) if $not_found_exception==TRUE, otherwise returns 0.
  public static function get_user_id_from_login_name($login, $not_found_exception=TRUE) {
    $r = Dal::query_one("SELECT user_id FROM {users} WHERE login_name = ?", Array($login));
    if (!$r) {
      if ($not_found_exception) {
        throw new PAException(USER_NOT_FOUND, "User $login not found");
      } else {
        return 0;
      }
    }
    return $r[0];
  }

  // Check for email id exist or not
  public static function get_user_data($email) {
    Logger::log("Enter: function User::get_user_data");
    $sql = "SELECT first_name, last_name, login_name, password, user_id, picture FROM {users} WHERE email = ?";
    $data = array($email);
    $res = Dal::query($sql, $data);
    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $login_name = $row->login_name;
      $password = $row->password;
      $first_name = $row->first_name;
      $last_name = $row->last_name;
      $user_id = $row->user_id;
      $picture = $row->picture;
      $pass = TRUE;
    } else {
      $pass = FALSE;
    }
    $array_of_value = array("email_exist"=> "$pass", "login_name"=> "$login_name", "password"=> "$password", "first_name"=> "$first_name", "last_name"=> "$last_name", "user_id"=> "$user_id", "picture"=> "$picture");

    Logger::log("Exit: function User::get_user_data");

    return $array_of_value;
  }

  public static function send_email_to_change_password ($email) {
    Logger::log("Enter: function User::send_email");

    // global var $_base_url has been removed - please, use PA::$url static variable

    $email_exist = User::get_user_data($email);

    if ($email_exist['email_exist'] == TRUE) {
      $first_name = $email_exist['first_name'];
      $last_name = $email_exist['last_name'];
      $user_name = $email_exist['login_name'];
      $password = $email_exist['password'];
      $user_id = $email_exist['user_id'];
      $status = 0;
      $forgot_password_id = md5(uniqid(rand()));

      //print "FORGOT::".$forgot_password_id; exit;
      // insert data into the database
      $sql = 'INSERT into {forgot_password} (user_id, forgot_password_id, status) values (?, ?, ?)';
      $data = array($user_id, $forgot_password_id, $status);
      $res = Dal::query($sql, $data);
      //print "FORGOT after::".$forgot_password_id; print_r($sql); exit;
//      $change_password_url = PA::$url.'/'.FILE_CHANGE_PASSWORD.'?log_nam='.$user_name.'&amp;uid='.$user_id.'&amp;forgot_password_id='.$forgot_password_id;
      $chng_psw_url = PA::$url.'/'.FILE_CHANGE_PASSWORD.'?log_nam='.$user_name.'&amp;uid='.$user_id.'&amp;forgot_password_id='.$forgot_password_id;
      $change_password_url = "<a href=\"$chng_psw_url\">$chng_psw_url</a>";

/*
      $array_of_data = array('first_name'=> $first_name, 'last_name'=> $last_name, 'config_site_name'=> PA::$site_name, 'user_name'=> $user_name, 'user_id'=> $user_id, 'change_password_url'=>$change_password_url);

      // calling common mailing method using flag (type=forgot_password)
      $check = pa_mail($email, 'forgot_password', $array_of_data);
*/
      $forg_user = new User();
      $forg_user->load((int)$user_id);
      $check = PAMail::send('forgot_password', $forg_user, PA::$network_info, array('change_password_url'=>$change_password_url));

      if ($check == FALSE) {
        Logger::log("Throwing exception MAIL_FUNCTION_FAILED | Mail is not sent to friend ", LOGGER_ERROR);
        throw new PAException(MAIL_FUNCTION_FAILED, "Mail is not sent to friend");
      }
    }
    Logger::log("Exit: function User::send_email");
  }

  /**
  *  Method to change the password.
  */
  public static function change_password ($password, $forgot_password_id) {
    Logger::log("Enter: function User::change_password");

    // global var $_base_url has been removed - please, use PA::$url static variable


    // check for email status
    $sql = "SELECT * FROM {forgot_password} WHERE forgot_password_id = ?";
    $data = array(trim($forgot_password_id));
    $res = Dal::query($sql, $data);

    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      if ($row->status == 1) {
        Logger::log("Throwing exception INVALID_FORGOT_PASSWORD_ID ", LOGGER_ERROR);
        throw new PAException(INVALID_FORGOT_PASSWORD_ID, "You cannot reuse the forgot password link.");
      } else {
        $user_id = $row->user_id;

        // MD5 encrypted password
        $enc_password = md5($password);

        // change password
        $sql = 'UPDATE {users} SET password = ? WHERE user_id = ?';
        $data = array($enc_password, $user_id);
        Dal::query($sql, $data);

        // change mail status
        $sql = "UPDATE {forgot_password} SET status = ? WHERE forgot_password_id = ? AND user_id = ?";
        $data = array(1, $forgot_password_id, $user_id);
        Dal::query($sql, $data);
        Logger::log("Exit: function User::change_password");
        return true;
      }
    } else {
      Logger::log("Throwing exception INVALID_FORGOT_PASSWORD_ID ", LOGGER_ERROR);
      throw new PAException(INVALID_FORGOT_PASSWORD_ID, "You forgot password link seems to be invalid");
    }

  }


  /**
  * All users.
  * @param $day, for which day wants to show registered member of pabase
  * @return $final_array, like array('users_data'=>, 'total_users'=>);
  */
  public static function allUsers($day = 1, $sort_by = 'latest_registered', $show = 'all') {
    Logger::log("Enter: function User::allUsers");

    $i = 0;

    if ($show != 'all') {
     $limit = "LIMIT $show";
    }

    $time_of_created = time() - (24 * 60 * 60 * $day);
    if ($sort_by == 'latest_registered') {
      //$res = Dal::query("SELECT user_id, picture, login_name FROM users where is_active = '1' AND created > $time_of_created $limit");
      $res = Dal::query("SELECT user_id, picture, login_name FROM {users} where is_active = '1' ORDER BY created DESC $limit");
    } else if ($sort_by == 'last_login'){
      //$res = Dal::query("SELECT user_id, picture, login_name FROM users where is_active = '1' AND last_login > $time_of_created $limit");
      $res = Dal::query("SELECT user_id, picture, login_name FROM {users} where is_active = '1' ORDER BY last_login DESC $limit");
    } else {
      $order_by = 'created DESC';
      $res = Dal::query("SELECT user_id, picture, login_name FROM {users} where is_active = '1' ORDER BY $order_by $limit");
    }

    $users_data = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $users_data[$i]['user_id'] = $row->user_id;
        $users_data[$i]['picture'] = $row->picture;
        $users_data[$i]['login_name'] = $row->login_name;
        $i++;
      }
    }
    $final_array = array('users_data'=>$users_data, 'total_users'=>$i);
    Logger::log("Exit: function User::allUsers");
    return $final_array;
  }

  public static function count_users() {
    list($count) = Dal::query_one("SELECT COUNT(*) FROM {users} WHERE is_active = 1");
    return intval($count);
  }

  /**
  * All users with limit.
  */
  public static function allUsers_with_paging($cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC', $condition = '') {
    Logger::log("Enter: function User::allUsers_with_paging");

    $i = 0;

    $order_by = $sort_by.' '.$direction;

    if ($show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }

    if ($condition['keyword']) {
      $data ='%'.$condition['keyword'].'%';
      $sql  ="SELECT * FROM {users} WHERE login_name LIKE ? ORDER BY $order_by $limit ";
      $res = Dal::query($sql, $data);
    } else {
      $res = Dal::query("SELECT * FROM {users} where is_active = '1'  ORDER BY $order_by $limit");
    }

    if ($cnt) {
      return $res->numRows();
    }

    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $users_data[$i]['user_id'] = $row->user_id;
        $users_data[$i]['first_name'] = $row->first_name;
        $users_data[$i]['last_name'] = $row->last_name;
        $users_data[$i]['email'] = $row->email;
        $users_data[$i]['picture'] = $row->picture;
        $users_data[$i]['login_name'] = $row->login_name;
        $users_data[$i]['created'] = $row->created;
        $users_data[$i]['is_active'] = $row->is_active;
        $i++;
      }
    }
    $final_array = array('users_data'=>$users_data, 'total_users'=>$i);

    Logger::log("Exit: function User::allUsers_with_paging");
    return $final_array;
  }

  /**
  * Load search info by user name.
  * @param $search_item the member's info to be searched
  * @return $final_array an array having the uid and other informations of resulted members
  */
  public static function search_by_name($search_item, $cnt=FALSE, $show='ALL', $page=0, $network_id = NULL) {
    Logger::log("Enter: User::search_by_name");
    $data = array();

    if ($show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1) * $show;
      $limit = ' LIMIT ' . $start . ',' . $show;
    }

    // Making query string.

    $search_string = "SELECT distinct(user_id), picture, login_name, first_name FROM {users} WHERE";

    // If first_name value exists then in data array the first name value will be added.
    if ($search_item['first_name'] && !$search_item['last_name']) {
      $search_string .= " first_name like '" . $search_item['first_name'] . "%' AND";
    } elseif ($search_item['first_name']) {
      $term = "%";
      $search_string .= " first_name like ? AND";
      $data = array($search_item['first_name'].$term);
    }

    if ($search_item['last_name']) {
      $term = "%";
      $search_string .= " last_name like ? AND";
      // if both last name and first name exist then in data array 'last_name' and its vale will be added at the end of array
      if ($search_item['first_name']) {
        array_push($data, $search_item['last_name'] . $term);
      }
      else {
        $data = array($search_item['last_name'] . $term);
      }
    }

    $search_string .= " is_active = ?$limit";
    // the value of is_active must be 1 and it is added in the end of the array
    if ($search_item['first_name'] || $search_item['last_name']) {
      array_push($data, 1);
    } else {
      $data = array(1);
    }

    $res = Dal::query($search_string, $data);

    if ($res->numrows() > 0) {
      $i = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $uid_array[$i]['user_id'] = $row->user_id;
        $uid_array[$i]['picture'] = $row->picture;
        $uid_array[$i]['login_name'] = $row->login_name;
        $uid_array[$i]['first_name'] = $row->first_name;
        $i++;
      }
    }
    $final_array = array('users_data'=>$uid_array, 'total_users'=>$i);
    Logger::log("Exit: User::search_by_name");
    return $final_array;
  }

  /**
  * all users with limit for Administrator
  */
  public static function allUsers_with_paging_admin($page = 1, $pagesize = 20) {
    Logger::log("Enter: function User::allUsers_with_paging");
    $i=0;
    if ($page!=1) {
      $page = $page*$pagesize-$pagesize;
    } else {
      $page = $page-1;
    }
    $res = Dal::query("SELECT * FROM {users} LIMIT $page, $pagesize");
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $users_data[$i]['user_id'] = $row->user_id;
        $users_data[$i]['first_name'] = $row->first_name;
        $users_data[$i]['last_name'] = $row->last_name;
        $users_data[$i]['email'] = $row->email;
        $users_data[$i]['is_active'] = $row->is_active;
        $users_data[$i]['picture'] = $row->picture;
        $users_data[$i]['login_name'] = $row->login_name;
        $users_data[$i]['created'] = $row->created;
        $i++;
      }
    }
    $final_array = array('users_data'=>$users_data, 'total_users'=>$i);
    Logger::log("Exit: function User::allUsers_with_paging");
    return $final_array;
  }

  /**
  * updating the status of the user that is altering the is_active field
  */
  public function update_status ($user_id, $is_active = 0) {
    Logger::log("Enter: function User::update_status");

    $sql = 'UPDATE {users} SET is_active = ? WHERE user_id = ?';
    $data = array($is_active, $user_id);
    Dal::query($sql, $data);

    Logger::log("Exit: function User::update_status");
    return;
  }

  /**
  * load info by a search
  * @param $search_item the member's info to be searched like Array([first_name] => 'nibha',   [last_name] => 'sachan', [submit_search] => 'Search User')
  * @return $uids an array having the uid of resulted members
  */
  public static function load_info_by_search($search_item, $user_id, $network_id=NULL, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC') {
    Logger::log("Enter: User::load_info_by_search");
    $db = Dal::get_connection();
    $data = array();
    $uids = array();
    $i=0;
    $j=1;

    $order_by = $sort_by . ' ' . $direction;
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1) * $show;
      $limit = 'LIMIT ' . $start . ',' . $show;
    }

    // Assigning each field name and field value in an array named
    // data where this array is not storing age to age till value as
    // well as not filling submit and its value from the search item
    // eg. $data[0] = 'last_name', $data[1] = 'nibha'

    foreach ($search_item as $k => $v) {
      if ($v && ($k != 'page') && ($k != 'submit_search')) {
        $data[$i] = $k;
        $data[$j] = ($k == 'sex') ? $v : ('%' . $v . '%');  // Omitting gender from partial search.
        $data1[] = $k;
        $i = $i + 2;
        $j = $j + 2;
      }
    }

    // Add a value 1 (for is_active) at the end of the array.

    array_push($data, 1);

    if ($network_id) {
      $search_string = "SELECT
        (U.user_id) as uid, U.first_name as first_name, U.login_name as login_name, U.picture as picture, UP.field_perm as field_perm, count(U.user_id) as counts
    FROM users AS U
    LEFT OUTER JOIN user_profile_data AS UP ON UP.user_id = U.user_id
    LEFT OUTER JOIN networks_users AS NU ON U.user_id = NU.user_id
    WHERE ";
    } else {
      $search_string = "SELECT
        (U.user_id) as uid, U.first_name as first_name, U.login_name as login_name, U.picture as picture, UP.field_perm as field_perm, count(U.user_id) as counts
        FROM users AS U
        LEFT OUTER JOIN user_profile_data AS UP ON UP.user_id = U.user_id
        WHERE ";
    }

    // now checking whether the array has any valid search parameters
    if (count($data1) > 0) {
      // now making query string for each of the key/value that exists in data()
      for ($j = 0; $j < count($data1); $j++) {
        $k = $j;
        $search_string .= "(UP.field_name = ? AND UP.field_value LIKE ? AND UP.field_perm <> 0)";
        // Now if search_item having any age field or next more field
        // then use OR to make query like (UP.field_name = ? AND
        // UP.field_value = ?) OR (UP.field_name = ? AND
        // UP.field_value = ?)
        if ($data1[$k+1]) {
          $search_string .= " OR";
        } else {
          $search_string .= " AND";
        }
      }
    }
    $count_of_fields = count($data1);

    if ($network_id) {
      $search_string .= " NU.network_id = $network_id AND";
    }

    $search_string .= " U.is_active = ? GROUP BY uid HAVING counts = $count_of_fields ORDER BY $order_by $limit";

    $res = Dal::query($search_string, $data);

    if (PEAR::isError($res)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED | Message: $res->getMessage()", LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $res->getMessage());
    }

    if ($cnt) {
      return $res->numRows();
    }

    if ($res->numrows() > 0) {
      $i = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
    $uid_array[$i]['user_id'] = $row->uid;
    $uid_array[$i]['login_name'] = $row->login_name;
    $uid_array[$i]['picture'] = $row->picture;
    $uid_array[$i]['first_name'] = $row->first_name;
    $uid_array[$i]['field_perm'] = $row->field_perm;
    $i++;
      }
    }

    // search according to perm
    $sql = "Select user_id from {relations} where relation_id = $user_id";
    $res = Dal::query($sql);

    if (PEAR::isError($res)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED | Message: $res->getMessage()", LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $res->getMessage());
    }

    if ($res->numrows() > 0) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
    $user_is_friend[] = $row->user_id;
      }
    }

    $j = 0;

    for ($i=0; $i<count($uid_array); $i++) {
      if ($uid_array[$i]['field_perm'] == WITH_IN_DEGREE_1) {
    if (!empty($user_is_friend)) {
      if (in_array($uid_array[$i]['user_id'], $user_is_friend) || ($user_id == $uid_array[$i]['user_id'])) {
        $user_ids[$j]['user_id'] = $uid_array[$i]['user_id'];
        $user_ids[$j]['login_name'] = $uid_array[$i]['login_name'];
        $user_ids[$j]['first_name'] = $uid_array[$i]['first_name'];
        $user_ids[$j]['picture'] = $uid_array[$i]['picture'];
        $j++;
      }
    }
      } else if ($uid_array[$i]['field_perm'] == NONE) {
    // Do nothing.
      } else {
    $user_ids[$j]['user_id'] = $uid_array[$i]['user_id'];
    $user_ids[$j]['login_name'] = $uid_array[$i]['login_name'];
    $user_ids[$j]['first_name'] = $uid_array[$i]['first_name'];
    $user_ids[$j]['picture'] = $uid_array[$i]['picture'];
    $j++;
      }
    }

    $users_with_data = array('users_data'=>$user_ids, 'total_users'=>count($user_ids));
    Logger::log("Exit: User::load_info_by_search");
    return $users_with_data;
  }

  // Added by Martin.
  // This function is needed for the login of remote users
  // as these are identified primarily via their extra profile data
  // type in user_profile_data = 4.
  public function quick_search_extended_profile($fieldname, $fieldvalue=NULL, $fieldtype=NULL) {
    $sql = "SELECT (U.user_id) as uid,
      U.login_name as login_name
      FROM users
      AS U LEFT OUTER JOIN
      user_profile_data AS UP
      ON UP.user_id = U.user_id
      WHERE U. is_active=1
      AND UP.field_name='$fieldname'";

    if ($fieldvalue) {
      $sql .= "
    AND   UP.field_value='$fieldvalue'";
    }

    if ($fieldtype) {
      $sql .= "
    AND   UP.field_type='$fieldtype'";
    }

    $uids = NULL;

    $res = Dal::query($sql);

    if (PEAR::isError($res)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED | " .
                  "Message: $res->getMessage()", LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $res->getMessage());
    }

    if ($res->numrows() > 0) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $uids = $row;
      }
    }

    return $uids;
  }

  // Added by Martin.
  // Return a list of 'field_type's the user
  // has in her profile.
  public static function list_availeable_profile_sections($uid) {
    $sql = "SELECT DISTINCT (field_type)
      FROM user_profile_data
      WHERE user_id = $uid";
    $res = Dal::query($sql);
    if (PEAR::isError($res)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED | " .
                  "Message: $res->getMessage()", LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $res->getMessage());
    }
    if ($res->numrows() > 0) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $sections[] = $row->field_type;
      }
    }
    return $sections;
  }

/*
  // FIXME: replace calls to this with calls to uihelper_resize_mk_user_img()
  public function get_img($maxdim, $link=TRUE) {
    // global var $path_prefix has been removed - please, use PA::$path static variable

    $img = ImageResize::mk_img_alternate($this->picture, $maxdim, DEFAULT_USER_PHOTO);
    if ($link) {
      $img = '<a href="' . PA::$url . '/user.php?uid=' . $u->user_id . '">' . $img . '</a>';
    }
    return $img;
  }
*/

  // Gives total number of users in the pa database.
  public static function get_member_count() {
    $res = Dal::query("select count(*) as cnt from {users} where is_active=1");
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    return $row->cnt;
  }

  public function list_widgets() {
    $sth = Dal::query("SELECT badge_tag, title FROM {blog_badges} WHERE user_id=? AND is_active=1 ORDER BY badge_tag", array($this->user_id));
    return Dal::all($sth);
  }

  public function load_widget($badge_tag) {
    $widget = new Widget($this->user_id);
    $widget->load($badge_tag);
    return $widget;
  }

  public function save_widget_config($badge_tag, $config, $title=NULL) {
    if (Dal::query_one("SELECT badge_config FROM {blog_badges} WHERE user_id=? AND badge_tag=? AND is_active=1", array($this->user_id, $badge_tag))) {
      Dal::query("UPDATE {blog_badges} SET badge_config=? WHERE user_id=? AND badge_tag=? AND is_active=1", array(serialize($config), $this->user_id, $badge_tag));
    } else {
      if (!$title) $title = $badge_tag;
      Dal::query("INSERT INTO {blog_badges} SET user_id=?, badge_tag=?, title=?, badge_config=?", array($this->user_id, $badge_tag, $title, serialize($config)));
    }
    Logger::log("Saved blog badge $badge_tag for user $this->user_id");
  }

  /**
  * function sets the status of user to active, disabled or deleted
  * @param array of parameters containing user id array, network id and status
  */
  public static function update_user_status ($params) {
    Logger::log("Enter: function User::update_user_status");
    $sql_extra = '';
    $data = array( $params['status'], time());
    $user_id_array = $params['user_id_array'];
    if (is_array($user_id_array)) {
      foreach($user_id_array as $user_id) {
        $data[] = $user_id;
        $sql_extra .= '? ,';
      }
      $sql_extra = substr($sql_extra, 0, (strlen($sql_extra) - 2));
    } else {
      $user_ids = $user_id_array;
      $sql_extra = '?';
    }

    $sql = 'UPDATE {users} SET is_active = ?, changed = ? WHERE user_id IN ( ' . $sql_extra . ' )';

    if (!($row = Dal::query($sql, $data))) {
      throw new PAException( INVALID_ARGUMENTS, 'Data not appropriate for updating user status.');
    }

    Logger::log("Exit: function User::update_user_status");
    return true;
  }

  /**
  * Soft delete of user related data.
  * function to delete a user from the system.
  * this function uses the method of th respective api's to delete the user related data.
  */
  public static function delete_user($user_id) {
    Logger::log("Enter: function User::delete_user");
    // Delete user content on homepage: Call content delete.
    $uid = (int)$user_id;
    try {
      Content::delete_user_content($uid);

      // Deleting user groups.
      $Group = new Group();
      $Group->delete_user_groups($uid);

      // Deleting user albums.
      Album::delete_user_albums($uid);

      // Deleting user relations.
      Relation::delete_user_relations($uid);

      // Deleting user comments.
      Comment::delete_user_comments($uid);

      PaForumsUsers::delete_PaForumsUsers($uid);

      UserPopularity::deleteUserPopularity( $uid );

    } catch(PAException $e) {
      Logger::log('Exception occured while deleting user:' . $uid . ' : ' . $e->message);
      throw new PAException(INVALID_ARGUMENTS, "User deletion failed for user_id =  '$uid' due to '$e->message'");
    }

    Logger::log("Exit: function User::delete_user");
  }

  /** This function keeps track of online registered user
  * @param user_id of logged in user
  * make an entry in users_online table
  * @return true, if entry succeeded else return false
  */

  public static function track_status($user_id) {
    // TODO: we can fetch recent entries as well by specifying time stamp

    // NOTE:
    //   this line removed because ConfigVariable and config_variable DB table
    //   not used
    //
    // $timeout = ConfigVariable::get('session_timeout',1800);

    $timeout = (int)get_cfg_var("session.gc_maxlifetime");
    $time = strtotime('-'.$timeout.' seconds');
    $sql = "DELETE FROM {users_online} WHERE timestamp < ".$time;
    Dal::query($sql);
    $sql = 'SELECT * FROM {users_online} WHERE user_id = ?';
    $data = array($user_id);
    $res = Dal::query($sql, $data);
    //if session for user exists then update that entry
    if ($res->numRows()) {
      $sql = 'UPDATE {users_online} SET timestamp = ? WHERE user_id = ?';
      $data = array(time(), $user_id);
    } else {
      $sql = 'INSERT INTO {users_online}(timestamp, user_id) VALUES(?, ?)';
      $data = array(time(), $user_id);
    }

    $res = Dal::query($sql, $data);
  }

  /**
  * This function will return number of currently online user
  * @return count of online users
  */
  public static function count_online_users($timestamp) {
    // delete user's entry that are older than $timestamp
    $sql = 'DELETE FROM {users_online}  WHERE timestamp < ?';
    $data = array($timestamp);
    $res = Dal::query($sql, $data);
    // Now get count of online registered user in a network
    $sql = 'SELECT count(*) AS cnt FROM {users_online} AS UO JOIN {networks_users} AS NU ON UO.user_id = NU.user_id WHERE NU.network_id = ? AND UO.timestamp between ? AND ?';
    $data = array(PA::$network_info->network_id, $timestamp, time());
    $res = Dal::query($sql, $data);
    if ($res->numRows()) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      return $row->cnt;
    } else {
      return false;
    }
  }

  /**
  * This function delete an entry corresponding to user if user logs out from the site
  */
  public static function set_status_offline($user_id) {
    Logger::log("Enter: function User::set_status_offline");
    $sql = 'DELETE FROM {users_online} WHERE user_id = ?';
    $data = array($user_id);
    try {
      $res = Dal::query($sql, $data);
    } catch(PAException $e) {
      Logger::log("Exit: function User::set_status_offline");
      throw $e;
    }
  }

  /**
  * Generic method to get the data from the user_profile_data table
  * @param $param: array of parameters as key value pair eg. array('user_id'=>1, 'field_type'=>'blog_rss')
  * @return array of user_profile data
  */
  public static function get_profile_data($params) {
    Logger::log("Enter: function User::get_profile_data");

    $sql = 'SELECT * FROM {user_profile_data}';
    $data = array();
    if (count($params)) {
      $sql .= ' WHERE 1';
      foreach ($params as $key => $value) {
        $sql .= ' AND '.$key.' = ?';
        $data[] = $value;
      }
    }

    try {
      $res = Dal::query($sql, $data);
    } catch (PAException $e) {
      Logger::log("Exit: function User::get_profile_data. Invalid sql, associated sql = $sql");
      throw $e;
    }

    $profile_data = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $profile_data[] = $row;
      }
    }

    Logger::log("Exit: function User::get_profile_data");
    return $profile_data;
  }

  /**
  * Generic function to save an entry in user_profile_data table on
  * the basis of user_id, field_type and field name.
  * @param user_data_array
  * @param array('field_type', ''field_name)
  */
  public function save_user_profile_fields($user_profile_data, $field_type, $field_name, $params=null) {
    Logger::log("Exit: function User::save_user_profile_fields");

    $sql = 'DELETE FROM {user_profile_data} WHERE user_id = ? AND field_type = ? AND field_name = ?';
    $data = array($this->user_id, $field_type, $field_name);

    if (count($params)) {
      foreach ($params as $key => $value) {
        $sql .= ' AND '.$key.' = ?';
        $data[] = $value;
      }
    }

    try {
      //Deleting the existing data from user_profile
      Dal::query($sql, $data);

      //Inserting new data to the user_profile
      foreach ($user_profile_data as $data) {
        $sql = 'INSERT into {user_profile_data} (user_id, field_name, field_value, field_type, field_perm, seq)  values (?, ?, ?, ?, ?, ?)';
        Dal::query($sql, $data);
      }

      //Commiting the changes to the database
      Dal::commit();

    } catch (PAException $e) {
      Logger::log("User::save_user_profile_fields function failed. Associate sql = $sql");
      Dal::rollback();
      throw $e;
    }

    Logger::log("Exit: function User::save_user_profile_fields");
  }

  /**
  * Updated search method for users. Search type can also be specified with every field one is searching for
  * e.g $search_item['first_name'] = array('value'=> 'test', 'type'=> LIKE_SEARCH) here type defines the search type
  * in this case it is LIKE SEARCH (constant defined in api_constants.php) means value
  * will be searched field_value = '%test%'
  * For date of birth we can specify date range like $search_item['dob'] = array('value'=>
  * array('lower_limit'=>200400, 'upper_limit'=>300500), 'type'=> RANGE_SEARCH)
  * This method will help us to search for value given in range and will give more freedom than the
  * method load_info_by_search which uses only LIKE to search for values.
  */
  public static function user_search($search_item, $user_id, $network_id=NULL, $cnt=FALSE, $show='ALL', $page=0, $sort_by='U.created', $direction='DESC', $condition = NULL) {
    Logger::log("Enter: User::user_search");
    $db = Dal::get_connection();
    $data = array();
    $uids = array();
    $i=0;
    $j=1;

    $order_by = $sort_by . ' ' . $direction;

    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1) * $show;
      $limit = 'LIMIT ' . $start . ',' . $show;
    }

    // ---- fix by Z.Hron: We don't need to read all data to count rows! Use MySQL function COUNT() in future!

    $search_string = "SELECT (U.user_id) as uid, U.first_name as first_name, U.login_name as login_name, U.picture as picture, UP.field_perm as field_perm, count(U.user_id) as counts";
    if($cnt) {
      $search_string = "SELECT count(U.user_id) as counts, (U.user_id) as uid";
    }
    // ---- EOF

    if ($network_id) {
      $search_string .= " FROM users AS U LEFT OUTER JOIN user_profile_data AS UP ON UP.user_id = U.user_id INNER JOIN networks_users AS NU ON U.user_id = NU.user_id AND NU.network_id = ?  ";
      $data[] = $network_id;
    } else {
      $search_string .= " FROM users AS U LEFT OUTER JOIN user_profile_data AS UP ON UP.user_id = U.user_id  ";
    }

    if (!empty($search_item['group_id'])) {
        $group_id = $search_item['group_id']['value'];
        unset($search_item['group_id']);
        $search_string .= " INNER JOIN {groups_users} AS GU ON U.user_id = GU.user_id AND GU.group_id = ?  ";
      $data[] = $group_id;
    }

    if (!empty($search_item['in_relation'])) {
        $relation_id = $search_item['in_relation']['value'];
        $search_string .= " INNER JOIN {relations} AS RU
        ON U.user_id = RU.relation_id
        AND RU.user_id = ? ";
        $data[] = $relation_id;

        if (!empty($search_item['in_relation']['type'])) {
            $status = $search_item['in_relation']['type'];
            $search_string .= "  AND RU.status = ? ";
          $data[] = $status;
        }
        unset($search_item['in_relation']);
    }

    if(!empty($condition)) {
      $search_string .= " WHERE $condition AND ";
    } else {
      $search_string .= " WHERE 1 AND ";    // field_perm > 0 AND "; // removed - field_perm checked for each field in code bellow!
    }

    $search_items_count = count($search_item);
    if ($search_items_count > 0) {
      $counter = 0;
      foreach ($search_item as $field_name => $field_details) {
        $counter++;
        switch ($field_details['type']) {
          case AGE_SEARCH:
            //date of birth will be saved in the formay YYYY-MM-DD
            $search_string .= '( UP.field_name = ? AND UP.field_value BETWEEN DATE(DATE_ADD(NOW(), INTERVAL ? YEAR)) AND DATE(DATE_ADD(NOW(), INTERVAL ? YEAR)) AND UP.field_perm <> ?)';
            $data[] = $field_name;
            $data[] = $field_details['value']['upper_limit']*(-1);
            $data[] = $field_details['value']['lower_limit']*(-1);
            $data[] = NONE;
          break;

          case GREATER_THAN:
            $search_string .= '( UP.field_name = ? AND UP.field_value '.$field_details['type'].' ? AND UP.field_perm <> ? )';
            $data[] = $field_name;
            $data[] = $field_details['value'];
            $data[] = NONE;
          break;

          case RANGE_SEARCH:
            $search_string .= '( UP.field_name = ? AND UP.field_value '.$field_details['type'].'  ? AND ? AND UP.field_perm <> ? )';
            $data[] = $field_name;
            $data[] = $field_details['value']['lower_limit'];
            $data[] = $field_details['value']['upper_limit'];
            $data[] = NONE;
          break;

          case LIKE_SEARCH:
            if(!empty($field_details['ignore_perm']) && ($field_details['ignore_perm'] == true)) {
              $search_string .= '( UP.field_name = ? AND UP.field_value '.$field_details['type'].' ? )';
              $data[] = $field_name;
              $data[] = '%'.$field_details['value'].'%';
            } else {
              $search_string .= '( UP.field_name = ? AND UP.field_value '.$field_details['type'].' ? AND UP.field_perm <> ? )';
              $data[] = $field_name;
              $data[] = '%'.$field_details['value'].'%';
              $data[] = NONE;
            }
          break;

          case GLOBAL_SEARCH:
            if(!empty($field_details['ignore_perm']) && ($field_details['ignore_perm'] == true)) {
              $search_string .= '( UP.field_value LIKE ? )';
              $data[] = '%'.$field_details['value'].'%';
            } else {
              $search_string .= '( UP.field_value LIKE ? AND UP.field_perm <> ? )';
              $data[] = '%'.$field_details['value'].'%';
              $data[] = NONE;
            }
          break;
          case IN_SEARCH:
              $search_string .= '( UP.field_name = ? AND UP.field_value IN ( '.$field_details['value'].' ) AND UP.field_perm <> ? )';
              $data[] = $field_name;
              $data[] = NONE;
          break;
          default:
            $search_string .= '( UP.field_name = ? AND UP.field_value '.$field_details['type'].' ? AND UP.field_perm <> ? )';
            $data[] = $field_name;
            $data[] = $field_details['value'];
            $data[] = NONE;
        }
        $search_string .= ($search_items_count ==$counter) ? ' AND ': ' OR ';
      }
    }
    $search_string .= " U.is_active = ? GROUP BY uid HAVING counts >= ? ORDER BY $order_by $limit";
    $data[] = ACTIVE;
    $data[] = $search_items_count;
    $res = Dal::query($search_string, $data);

    if (PEAR::isError($res)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED | Message: $res->getMessage()", LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $res->getMessage());
    }

    if ($cnt) {
      // fix by Z.Hron: We don't need to read all data to count rows! Use MySQL function COUNT() in future!
      $u_data = $res->fetchRow(DB_FETCHMODE_OBJECT);
      return (!empty($u_data)) ? $u_data->counts : 0;
//      return $res->numRows();
    }

    $uid_array = array();
    if ($res->numrows() > 0) {
      $i = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $uid_array[$i]['user_id'] = $row->uid;
        $uid_array[$i]['login_name'] = $row->login_name;
        $uid_array[$i]['picture'] = $row->picture;
        $uid_array[$i]['first_name'] = $row->first_name;
        $uid_array[$i]['field_perm'] = $row->field_perm;
        $i++;
      }
    }

    // search according to perm
    $sql = "Select user_id from {relations} where relation_id = $user_id";
    $res = Dal::query($sql);

    if (PEAR::isError($res)) {
      Logger::log(" Throwing exception DB_QUERY_FAILED | Message: $res->getMessage()", LOGGER_ERROR);
      throw new PAException(DB_QUERY_FAILED, $res->getMessage());
    }

    if ($res->numrows() > 0) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $user_is_friend[] = $row->user_id;
      }
    }

    $j = 0;
    $user_ids = array();
    for ($i=0; $i<count($uid_array); $i++) {
      if ($uid_array[$i]['field_perm'] == WITH_IN_DEGREE_1) {
        if (!empty($user_is_friend)) {
          if (in_array($uid_array[$i]['user_id'], $user_is_friend) || ($user_id == $uid_array[$i]['user_id'])) {
            $user_ids[$j]['user_id'] = $uid_array[$i]['user_id'];
            $user_ids[$j]['login_name'] = $uid_array[$i]['login_name'];
            $user_ids[$j]['first_name'] = $uid_array[$i]['first_name'];
            $user_ids[$j]['picture'] = $uid_array[$i]['picture'];
            $j++;
          }
        }
      } else if ($uid_array[$i]['field_perm'] == NONE) {
        // used when field_perm attribute ignored
        $user_ids[$j]['user_id'] = $uid_array[$i]['user_id'];
        $user_ids[$j]['login_name'] = $uid_array[$i]['login_name'];
        $user_ids[$j]['first_name'] = $uid_array[$i]['first_name'];
        $user_ids[$j]['picture'] = $uid_array[$i]['picture'];
        $j++;

      } else {
        $user_ids[$j]['user_id'] = $uid_array[$i]['user_id'];
        $user_ids[$j]['login_name'] = $uid_array[$i]['login_name'];
        $user_ids[$j]['first_name'] = $uid_array[$i]['first_name'];
        $user_ids[$j]['picture'] = $uid_array[$i]['picture'];
        $j++;
      }
    }

    $users_with_data = array('users_data'=>$user_ids, 'total_users'=>count($user_ids));
    Logger::log("Exit: User::user_search");
    return $users_with_data;
  }

  // This function returns an array maximum, minimum and average view of user profile
  public static function get_profile_view_stats ($field_name) {
    // Making a log file entry for Enter
    Logger::log("Enter: function User::get_profile_view_stats");
    $sql = "SELECT max(CAST(field_value AS UNSIGNED)) As MaxProfileView, min(CAST(field_value AS UNSIGNED)) AS MinProfileView, avg(CAST(field_value AS UNSIGNED)) as AvgProfileView FROM {user_profile_data} where field_name = '$field_name'";
    $res = Dal::query($sql);
    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $profile_view = array('min'=>$row->MinProfileView,
                            'max'=>$row->MaxProfileView,
                            'avg'=>$row->AvgProfileView);
    }
    // Making a log file entry for Exit
    Logger::log("Exit: function User::get_profile_view_stats");
    return $profile_view;
  }
  /**
  * Update total time spent by user on site in user_profile_data table
  */
  public function update_user_time_spent() {
    $params = array();
    $user_id = $this->user_id;
    $params["user_id"] = $user_id;
    $params["field_name"] = $field_name = "time_spent";
    $params["field_type"] = $field_type = 5;// five for extra information
    $res = $this->get_profile_data($params);
    $total_time = count($res) ? $res[0]->field_value : 0;
    $sql = "SELECT * FROM {users_online} WHERE user_id = ? ";
    $res = Dal::query($sql, array($user_id));
    if ($res->numRows()) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $time = time() - $row->timestamp;
    }
    else {
      $time = 0;
    }
    $total_time = $total_time + $time;
    $user_profile_data[] = array($user_id, $field_name, $total_time, $field_type, 0, NULL);
    $this->save_user_profile_fields($user_profile_data, $field_type, $field_name);
  }
  /**
  * get the login name from the user id
  */
  public static function get_login_name_from_id($user_id) {
    Logger::log("Enter: function User::get_login_name_from_id with user_id=".$user_id);
    list($login) = Dal::query_one("SELECT login_name FROM {users} WHERE user_id=? AND is_active=1", Array($user_id));
    Logger::log("Exit: function User::get_login_name_from_id");
    return $login;
  }
}
?>
