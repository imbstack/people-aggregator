<?php
/**
 * @author Tekriti Software (www.TekritiSoftware.com)
* Assumption: type var for mother network =1 always and it will be only network
* which will have type =1 ( MOTHER_NETWORK_TYPE defined below)
 */
include_once dirname(__FILE__)."/../../config.inc";
require_once "api/api_constants.php";
require_once "db/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/Tag/Tag.php";
require_once "ext/Access/Access.php";
require_once "web/includes/functions/functions.php";
require_once "api/Storage/Storage.php";

/**
 * Class Network represents a Network item in the system.
 * Networks is container of all site content pages
 */




class Network {


  /**
   * constant IS_MODERATED
  */
  const MODERATED = 0;

  /**
   * constant REGISTERED_VISITOR
  */
  const REGISTERED_VISITOR = 'registered_visitor';

  /**
   * constant ANONYMOUS_VISITOR
  */
  const ANONYMOUS_VISITOR = 'anonymous_visitor';


  /**
   * network_id uniquely defines the network.
   * @access public
   * @var int
   */
  public $network_id;

    /**
   * category_id defines the category of network.
   * @access public
   * @var int
   */
  public $category_id;

  /**
   * user_id defines user which is manipulating network
   * @access public
   * @var int
   */
  public $user_id;

    /**
   * stop_after_limit stops new registeration in network after max size
   * @access public
   * @var string.
   */
  public $stop_after_limit = 0;

   /**
   * maximum_members maximum number of users that can join network
   * @access public
   * @var string.
   */
  public $maximum_members = NETWORK_MAXIMUM_MEMBERS;

  /**
   * name relates the name of the network
   * @access public
   * @var string.
   */
  public $name;

    /**
   * address url relates the name of the network e.g. http://'address'.peopleaggregator.com
   * @access public
   * @var string.
   */
  public $address;

  /**
   * tagline is heading of the network that will appear on header of network
   * @access public
   * @var string.
   */
  public $tagline;

  /**
   * description of network
   * @access public
   * @var string.
   */

  public $description;

  /**
   * name of the image that will appear on header of network
   * @access public
   * @var string.
   */
  public $header_image;

  /**
   * name of the image that will appear on inner header of network
   * @access public
   * @var string.
   */
  public $inner_logo_image;

   /**
   * alt of the image that will appear on inner/outer header of network
   * @access public
   * @var string.
   */
  public $network_alt_text;

  /**
   * flag to indicate wheather network is active or not
   * @access public
   * @var int
   */
  public $is_active;

  /**
   * time at which the network was created
   * @access public
   * @var date
   */
  public $created;

  /**
   * time at which the network was modified
   * @access public
   * @var date
   */
  public $changed;

  /**
   * type of network i.e. mother network, regular networks
   * 1= mothernetowrk and 0 for regular networks
   * There will be only one network of type=1 which is mother network
   * @access public
   * @var date
   */
  public $type;

  /**
   * extra defines any extra information associated with network
   * @access public
   * @var text
   */
  public $extra;

  /**
   * contructor
   * @access public
   */
  public function __construct() {
    Logger::log("Enter: Network::__construct");
    //this will be used later for checking access permissions
    $this->acl_object = new Access();
    Logger::log("Exit: Network::__construct");
  }

  /**
   * sets the network class variables of network object
   * @access public.
   * @param data array
   * keys of array are class variables
   * format of data array should be like this e.g.
    $data_array = array(
                    'category_id' => 1,
                    'user_id' => 1,
                    'stop_after_limit' => 0,
                    'name' => 'network for teknokrats',
                    'address' => 'beta',
                    'tagline' => 'This network is only for Geeks',
                    'description' => 'All the persons having flare for technology can come here',
                    'header_image' => 'header_image.gif',
                    'inner_logo_image'=>'inner_logo_image',
                    'network_alt_text'=>'network_alt_text'
                    'created' => time(),
                    'changed' => time(),
                    'type' => 'public',
                    'extra' => 'extra'
                  );
   */
  public function set_params($data) {
    Logger::log("[ Enter: function Network::set_params]\n");
      foreach ( $data as $key=>$value ) {
        $this->$key = $value;
      }
      Logger::log("[ Exit: function Network::set_params]\n");
  }

  public function is_private() {

    if (PA::$config->all_networks_are_private) return TRUE;
    if ($this->type == PRIVATE_NETWORK_TYPE) return TRUE;
    return FALSE;
  }

  /**
    * Saves network to databse.
    * used for creating and updating the network
    * @access public.
    * called after $this->set_params()
  */
  public function save() {
    global $error, $error_msg;

    $error = false;
    Logger::log("[ Enter: function Network::save]\n");
    //first check whether it is insert or update
    // global var $path_prefix has been removed - please, use PA::$path static variable
    if ( $this->network_id ) {
      $old_type = Network::find_network_type($this->network_id);
      //update
      $update_fields = array('name','tagline','category_id','description','extra','type','inner_logo_image');
      $sql = " UPDATE {networks} SET changed = ? ";
      $data_array = array(time());

      foreach ($update_fields as $field) {
        if ( isset($this->$field) ) {
        $sql .= " , $field = ? ";
        array_push($data_array, $this->$field);
        }
      }
      $sql .=" WHERE network_id = ? " ;
      array_push($data_array, $this->network_id);
      $res = Dal::query($sql,$data_array);
      //fix for changing a network from private to public
      //the waiting_members must be changed to members

      $new_type = $this->type;
      if ($old_type == PRIVATE_NETWORK_TYPE && $new_type == REGULAR_NETWORK_TYPE) {
        Network::approve_all($this->network_id);
      }

    } else {
      //insert
      // here we have to do a lot of steps
      //first check if network already exists with same address
        if (Network::check_already($this->address)) {
          Logger::log("Thowing Exception NETWORK_ALREADY_EXISTS");
          throw new PAException(NETWORK_ALREADY_EXISTS,"Network with same address already exists");
        }
        // checks the permissions of directory network
        $network_folder = PA::$project_dir."/networks";
        if (!is_writable($network_folder)) {
          Logger::log("Thowing Exception NETWORK_DIRECTORY_PERMISSION_ERROR");
          throw new PAException(NETWORK_DIRECTORY_PERMISSION_ERROR,"Network folder ($network_folder) is not writable. Please check the permissions");
        }
        //if we have come this far we can insert the network easily
        // TODO add acl permission check
        //insert into networks
        $this->created = time();
        $this->type = ( $this->type ) ? $this->type : REGULAR_NETWORK_TYPE;
        $res = Dal::query("INSERT INTO {networks} (name, address, tagline, type,category_id, description,is_active, created, changed, extra, member_count, owner_id, inner_logo_image) VALUES ( ?, ?, ?,?, ?, ?, ?, ?, ?, ?, 1, ?, ? )", array($this->name, $this->address, $this->tagline, $this->type, $this->category_id, $this->description, 1, $this->created, $this->changed, $this->extra, $this->user_id, $this->inner_logo_image));
        $this->network_id = Dal::insert_id();

        //insert into networks_users
	$user_created = Dal::query_first("SELECT created FROM users WHERE user_id=?", $this->user_id);
        $res = Dal::query("INSERT INTO {networks_users} (network_id, user_id, user_type, created) VALUES (?, ?, ?, ?)", array($this->network_id, $this->user_id, NETWORK_OWNER, $user_created));
        //Now we have inserted new network we need to create other directory and config files as well
        try{
          $this->do_network_setup();
        } catch (PAException $e) {
            $error = TRUE;
            $error_msg = "$e->message";
        }
        if ( $error ) {
          $this->do_rollback();
          throw new PAException(NETWORK_INTERNAL_ERROR, "Some internal error occured while setting up the network. Message: $error_msg");
        }
    } //.. insert
    Logger::log("[ Exit: function Network::save]\n");
    return $this->network_id;
  }

  /* Get info on a single network */
  public static function get_network_by_address($network_name) {
    if ($network_name == 'www' || empty($network_name)) return self::get_mothership_info();
    $sth = Dal::query("SELECT * FROM {networks} WHERE is_active=1 AND address=?", array($network_name));
    return Network::from_array(Dal::row_assoc($sth));
  }

  /**
  * get the details of network
  * @access public.
  * @param array of parameters
  * format of $params
  e.g.
  1. for getting count only
  $params = array('cnt'=>TRUE);
  2. for getting all networks just call $network->get();
  3. for further paging options and order by set as
  $params = array('sort_by'=>'created',
  'direction'=>'DESC' ,
  'page'=>2,//page number
  'show'=>3//how many records on the page
  ) ;

  * @return array of all networks
  * by default it will load all networks' details
  */
  public function get( $params = NULL, $conditions = NULL ) {
    Logger::log("[ Enter: function Network::get] \n");
    // if network_id is set already then get that n/w only
    $args = array();
    if ( $this->network_id ) {
      $sql = " SELECT * FROM {networks} WHERE network_id = ? AND is_active = 1 ";
      $args[] = $this->network_id;
    } else {
      // here we have to check for additional filters used for paging
      // changed to identify to get the networks only which are not mother networks
      $sql = " SELECT * FROM {networks} WHERE 1 AND is_active = 1 AND type = ".REGULAR_NETWORK_TYPE;
      if ( $conditions ) {
        $sql = $sql . ' AND ' .$conditions;
      }
      // paging variables if set
      $sort_by = ( @$params['sort_by'] ) ? $params['sort_by'] : 'created';
      $direction = ( @$params['direction'] ) ? $params['direction'] : 'DESC';
      $order_by = ' ORDER BY '.$sort_by.' '.$direction;
      if ( @$params['page'] && @$params['show'] && !@$params['cnt']) {
        $start = ($params['page'] -1) * $params['show'];
        $limit = ' LIMIT '.$start.','.$params['show'];
      } else {
        $limit = "";
      }
      $sql = $sql . $order_by . $limit;
    }
    $res = Dal::query($sql, $args);
    if ( $params['cnt']==TRUE ) {
      // here we just want to know total networks
      Logger::log("[ Exit: function Network::get and returning count] \n");
      return $res->numRows();
    }
    $network = array();
    while ( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
      $network[] = $row;
    }
    Logger::log("[ Exit: function Network::get] \n");
    return $network;
  }

  // populate new Network object from DB row $info
  private function from_array($info) {
echo "from_array <pre>" . print_r($info, 1) . "</pre>";
    $net = new Network();
    foreach ($info as $k => $v) {
      $net->$k = $v;
    }
    return $net;
  }

  /**
  * get the details of network
  * @access public.
  * @param array of parameters
  * format of $params
  e.g.
  1. for getting count only
  $params = array('cnt'=>TRUE);
  2. for getting all networks just call $network->get();
  3. for further paging options and order by set as
  $params = array('sort_by'=>'created',
  'direction'=>'DESC' ,
  'page'=>2,//page number
  'show'=>3//how many records on the page
  ) ;

  * @return array of all networks
  * by default it will load all networks' details
  */
  static function get_user_networks( $uid, $params = NULL) {
    Logger::log("[ Enter: function Network::get_user_networks] \n");

    $sql = " SELECT N.*,NU.user_type FROM {networks} AS N, {networks_users} AS NU WHERE NU.user_id = ? AND NU.network_id = N.network_id AND N.is_active = ? AND N.type = ? ";
    $data_array = array( $uid, 1, REGULAR_NETWORK_TYPE );
    //paging variables if set
    $sort_by = ( $params['sort_by'] ) ? $params['sort_by'] : 'created';
    $direction = ( $params['direction'] ) ? $params['direction'] : 'DESC';
    $order_by = ' ORDER BY '.$sort_by.' '.$direction;
    if ( $params['page'] && $params['show'] && !$params['cnt']) {
      $start = ($params['page'] -1) * $params['show'];
      $limit = ' LIMIT '.$start.','.$params['show'];
    } else {
      $limit = "";
    }
    $sql = $sql . $order_by . $limit;
    $res = Dal::query($sql,$data_array);
    if ( $params['cnt']==TRUE ) {
      // here we just want to know total networks
      return $res->numRows();
    }
    $network = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
      $row->members = $row->member_count; // alias for old code that expects 'members' rather than 'member_count'
      $network[] = $row;
    }
    Logger::log("[ Exit: function Network::get_user_networks] \n");
    return $network;
  }


  /**
  * get the members of network
  * @access public.
  * @param array of parameters
  * format of $params
  e.g.
  1. for getting count only
  $params = array('cnt'=>TRUE);
  2. for further paging options and order by set as
  $params = array('sort_by'=>'created',
  'direction'=>'DESC' ,
  'page'=>2,//page number
  'show'=>3//how many records on the page
  ) ;

  * @return array of all networks
  * by default it will load all networks' details
  */
  static function get_network_members( $nid, $params = NULL, $conditions = NULL) {
    Logger::log("[ Enter: function Network::get_user_networks] \n");

    // ---- fix by Z.Hron: We don't need to read all data to count rows! Use MySQL function COUNT() in future!

    $sql = "SELECT NU.user_id, U.picture, U.login_name, U.first_name, U.last_name, U.email, U.created";
    if ( @$params['cnt']==TRUE ) {
      $sql = "SELECT COUNT(U.login_name) AS rowcounter";
    }

    // ---- EOF

    $loginname = "%".$conditions['keyword']."%";
    if( $conditions['keyword'] ) {//if some keyword is specified
      //SLOW: potentially slow join for large network; LIKE query
      $sql .= " FROM {networks_users} AS NU,{users} AS U WHERE NU.user_id = U.user_id AND U.user_id <> ? AND U.login_name LIKE ? AND NU.network_id =? AND NU.user_type <> ? ";
      $data_array = array( $conditions['owner_id'], $loginname, $nid, NETWORK_WAITING_MEMBER);
    } else {//if no keyword is specified but owner is viewing network user
      if( !empty( $conditions['owner_id'] ) ) {
	//SLOW: potentially nasty join; "U.user_id <> ?" will only match one user so change to "HAVING U.user_id <> ?" to allow index use for ORDER BY?
        $sql .= " FROM {networks_users} AS NU,{users} AS U WHERE NU.user_id = U.user_id AND U.user_id <> ? AND NU.network_id =? AND U.is_active <> ? AND NU.user_type <> ? ";
        $data_array = array($conditions['owner_id'], $nid, DELETED, NETWORK_WAITING_MEMBER);
      } else {// all networks
	//OPT: U.is_active check not required due to join with NU; default case will use NU.recent_users key.
        $sql .= " FROM {networks_users} AS NU INNER JOIN {users} AS U ON NU.user_id = U.user_id AND U.is_active <> ? WHERE NU.network_id =? AND NU.user_type <> ? ";
        $data_array = array(DELETED, $nid, NETWORK_WAITING_MEMBER);
      }
   }

   if(!empty($params['extra_condition'])) {
      $e_cond = trim($params['extra_condition']);
      $sql .= "AND $e_cond ";
   }
    //paging variables if set
    $i=0;
    // Changed NU.created to U.created, as this function is
    // used by install at a time when NU.created may not yet exist
    // -Martin
    $sort_by = ( @$params['sort_by'] ) ? $params['sort_by'] : 'U.created';
    $direction = ( @$params['direction'] ) ? $params['direction'] : 'DESC';
    $order_by = ' ORDER BY '.$sort_by.' '.$direction;
    if ( @$params['page'] && @$params['show'] && !@$params['cnt']) {
      $start = ($params['page'] -1) * $params['show'];
      $limit = ' LIMIT '.$start.','.$params['show'];
    } else {
      $limit = "";
    }
    $sql = $sql . $order_by . $limit;
    $res = Dal::query($sql,$data_array);

    if ( @$params['cnt']==TRUE ) {
      // fix by Z.Hron: We don't need to read all data to count rows! Use MySQL function COUNT() in future!
      $u_data = $res->fetchRow(DB_FETCHMODE_OBJECT);
      return $u_data->rowcounter;
    }

    $users_data = array();
    if($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $users_data[$i]['user_id'] = $row->user_id;
        $users_data[$i]['picture'] = $row->picture;
        $users_data[$i]['login_name'] = $row->login_name;
        $users_data[$i]['first_name'] = $row->first_name;
        $users_data[$i]['last_name'] = $row->last_name;
        $users_data[$i]['email'] = $row->email;
        $users_data[$i]['created'] = $row->created;
        $i++;
      }
    }
    $final_array = array('users_data'=>$users_data, 'total_users'=>$i);
    Logger::log("[ Exit: function Network::get_user_networks] \n");
    return $final_array;
  }

  /**
  * deletes network
  * @access public static
  * @param id of the network
  * @return success or raise exception if user has not the permissions to delete the network
  */
  public static function delete($network_id,$uid=NULL) {//$uid NULL for the time being unless we add check permissions
    // global var $path_prefix has been removed - please, use PA::$path static variable
    Logger::log("[ Enter: function Network::delete | Args: \$comment_id = $comment_id ]\n");
    if (Network::is_mother_network($network_id)) {
      throw new PAException(OPERATION_NOT_PERMITTED, "You cant delete mother network.");
    }
    Network::delete_all($network_id);
    Logger::log("Exit: function Network::delete\n");
    return;
  }

  /**
  * let the user join network
  * @access public
  * @param id of the network,uid of user
  * @return flag for joining request moderated or success message
  */
  static function join($network_id, $uid, $user_type=null, $by_admin=false) {
    // function modified just to have crude functionality for time being
    // TODO : when some one joins network do something
    global $default_sender;
    Logger::log("Enter: static function Network::join");

    if (Network::member_exists($network_id, $uid)) {//then make an entry for the network to be joined and user_id
      throw new PAException(OPERATION_NOT_PERMITTED, "Already a member of this network.");
    }
    $owner_id = self::get_network_owner($network_id);
    $roles = array();
    $roles[0] = array('role_id' => LOGINUSER_ROLE, 'extra' => serialize(array('user' => true, 'network' => true, 'groups' => array())));       // assign LOGINUSER_ROLE to new member
    if((((int)$network_id == 1) && ((int)$uid == 1)) || ($uid == $owner_id)) {     // user is mother network owner !
      $user_type = NETWORK_OWNER;
      $roles[0] = array('role_id' => ADMINISTRATOR_ROLE, 'extra' => serialize(array('user' => false, 'network' => true, 'groups' => array()))); // ADMIN role to mother network owner !
    }
    $roles_obj = new Roles();
    $roles_obj->assign_role_to_user($roles, $uid);
    $user_created = Dal::query_first("SELECT created FROM users WHERE user_id=?", array($uid));

    // find type of the network
    // $type = Network::find_network_type($network_id);
    $res  = Dal::query('SELECT type, extra FROM {networks} WHERE  network_id = ? ', array($network_id));
    $row  = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $type = $row->type;
    $extra = unserialize($row->extra);

    $email_val = false;
    if (! $by_admin) {
			if(isset($extra['email_validation']) && ($extra['email_validation'] == 1)) {
				 $email_val = true;
			}
			if ($type == PRIVATE_NETWORK_TYPE ) {
				$user_type = ( empty($user_type) ) ? NETWORK_WAITING_MEMBER: $user_type;
				// see if user has already applied for the network
				$res = Dal::query("SELECT * FROM {networks_users} WHERE network_id = ? AND user_id = ? AND user_type = ? ", array($network_id, $uid, $user_type));
				if ($res->numRows()>0) {
					throw new PAException(OPERATION_NOT_PERMITTED, "You have already requested to join this network.");
				}
			} else {
				$user_type = ($email_val) ? NETWORK_WAITING_MEMBER: NETWORK_MEMBER;
			}
    } else {
    	// join the new user in all cases, this is an admin action!
			$user_type = NETWORK_MEMBER;
    }
    $res = Dal::query("INSERT INTO {networks_users} (network_id, user_id, user_type, created) VALUES (?, ?, ?, ?)", array($network_id, $uid, $user_type, $user_created));
    //getting Mother network informaton
//    $network_data =  Network::get_mothership_info();//get the network_info of mother network such as network_id
//    if (!Network::member_exists($network_data->network_id, $uid)) {//if not a member of mother network ie directly joining a network then make an entry for mother network_id and user_id
//      $res = Dal::query("INSERT INTO {networks_users} (network_id, user_id, user_type, created) VALUES (?, ?, ?, ?)", array($network_data->network_id, $uid, NETWORK_MEMBER, $user_created));
//    }
    // Update cached member count
    Network::update_network_member_count($network_id);
    return TRUE;
  }

  /**
  * let the user leave network
  * @access public
  * @param id of the network,uid of user
  * @return flag for joining request moderated or success message
  */
  static function leave($network_id, $uid) {
    Logger::log("Enter: static function Network leave");
//     if (Network::is_mother_network($network_id)) {
//       throw new PAException(OPERATION_NOT_PERMITTED, "You cant delete mother network.");
//     }
    if (!Network::member_exists($network_id, $uid)) {
      throw new PAException(OPERATION_NOT_PERMITTED, "You are not member of this network.");
    }
    $user_type = Network::get_user_type($network_id, $uid);
    if ($user_type==NETWORK_OWNER) {
      throw new PAException(OPERATION_NOT_PERMITTED, "You cant leave your own network!!.");
    }
    $res = Dal::query("DELETE FROM {networks_users} WHERE network_id = ? AND user_id = ? ", array($network_id, $uid));

    // delete all user roles for this network
    Roles::delete_user_roles($uid, -1);              // -1 means: all user roles will be deleted
    // Update cached member count
    Network::update_network_member_count($network_id);
    Logger::log("Exit: static function Network leave");
    return TRUE;
  }

  // Removes a user from all networks -- called by User::delete
  static function leave_all_networks($uid) {
    Logger::log("Network::leave_all_networks($uid)");
    // Decrement the member count for all networks the user is a member of
    Dal::query("UPDATE networks_users NU LEFT JOIN networks N ON NU.network_id=N.network_id SET N.member_count=N.member_count-1 WHERE NU.user_id=?", array($uid));
    // Now remove the user from all networks
    Dal::query("DELETE FROM {networks_users} WHERE user_id=?", array($uid));
    return TRUE;
  }

  // Update networks.member_count after adding/deleting a network member
  static function update_network_member_count($network_id) {
    Dal::query("UPDATE {networks} SET member_count=(SELECT COUNT(*) FROM {networks_users} WHERE network_id=? AND user_type<>?) WHERE network_id=?", array($network_id,NETWORK_WAITING_MEMBER, $network_id));
  }

  /**
  * let the user member_exists
  * @access public
  * @param id of the network,uid of user
  * @return flag for joining request moderated or success message
  */
  static function member_exists($network_id, $uid) {
    Logger::log("Enter: static function Network::member_exists");
//    $res = Dal::query("SELECT * FROM {networks_users} WHERE network_id = ? AND user_id=? AND user_type <> ? ",array($network_id, $uid, NETWORK_WAITING_MEMBER));
    $res = Dal::query("SELECT * FROM {networks_users} WHERE network_id = ? AND user_id=? ",array($network_id, $uid));
    $ret = ($res->numRows() ? TRUE : FALSE);
    Logger::log("Exit: static function Network::member_exists");
    return $ret;
  }

  /**
  * let the user member_exists
  * @access public
  * @param id of the network,uid of user
  * @return flag for joining request moderated or success message
  */
  static function is_admin($network_id, $uid) {
    // this function will be used for every action
    // need to be in role based access control
    // role needs to be figured out
    Logger::log("Enter: static function Network::is_admin");

    // TODO: uncomment code below when applying roles to spawned networks
    // $user = new User();
    // $user->load($uid);
    // $has_admin_role = $user->has_role_name('Administrator');

    $is_super_user = false;
    if (defined('SUPER_USER_ID')) {                        // check if super user exists else raise exception
      if (SUPER_USER_ID == $uid) {
        $is_super_user = true;
      }
    } else {
      throw new PAException(ROLE_ID_NOT_EXIST, "Configuration error: Super user is not defined for mother network.");
    }
/*
    if (Network::is_mother_network($network_id)) {           //check if we asking about mother network
       // TODO: uncomment and replace line below when applying roles to spawned networks
       // if($is_super_user || $has_admin_role) {
       if($is_super_user) {                                  // only SUPER USER for mother network
          Logger::log("Exit: static function Network::is_admin | Return: TRUE");
          return true;
       } else {
          Logger::log("Exit: static function Network::is_admin | Return: FALSE");
          return false;
       }
    } else {
*/
      if($is_super_user) {                                  // SUPER USER for spawned network
         Logger::log("Exit: static function Network::is_admin | Return: TRUE");
         return true;
      }
      $res = Dal::query("SELECT  user_type FROM {networks_users} WHERE network_id = ? AND user_id=?",array($network_id,$uid));
      $ret = $res->numRows();
      if ( !$ret ) {
        Logger::log("Exit: static function Network::is_admin | Return: FALSE");
        return false;
      }
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      // TODO: uncomment and replace line below when applying roles to spawned networks
      // if (($row->user_type == NETWORK_OWNER) || $has_admin_role) {   // NETWORK OWNER or ADMINISTRATOR for spawned network
      if ($row->user_type == NETWORK_OWNER) {                           // NETWORK OWNER for spawned network
        Logger::log("Exit: static function Network::is_admin | Return: TRUE");
        return true;
      } else {
        $user = new User();
        $user->load($uid);
        foreach($user->role as $role) {
          if($role->role_id == ADMINISTRATOR_ROLE) {
            Logger::log("Exit: static function Network::is_admin | Return: TRUE");
            return true;
          }
        }
        Logger::log("Exit: static function Network::is_admin | Return: FALSE");
        return false;
      }
/*
    }
*/
  }


  /**
  * Checks if networks already exists with same address
  * @access public static.
  * @param address of the network
  *
  */
  static function check_already($address) {
    Logger::log("Enter: static function Network::check_already address = $address");
    $sql = "SELECT network_id FROM {networks} WHERE is_active = ? AND address = ? ";
    $data = array(1, $address);
    $res = Dal::query($sql,$data);
    if ( $res->numRows() ) {
      $return  = TRUE;
    } else {
      $return  = FALSE;
    }
    Logger::log("Exit: static function Network::check_already address = $address");
    return $return;
  }

  /**
  * get the type of user for a given network
  * @access public static.
  * @param address of the network
  */
  static function get_user_type($network_id,$uid) {
    Logger::log("[Enter: static function Network::get_user_type]network_id = $network_id,uid=$uid");
    $row = Dal::query_one_object("SELECT user_type FROM {networks_users} WHERE network_id = ? AND user_id = ?", array($network_id,$uid));
    Logger::log("[Exit: static function Network::get_user_type] ");
    return $row ? $row->user_type : NULL;
  }

  /**
  * Do network setup
  * @access public
  * @param
  */
  public function do_network_setup() {
    // global var $path_prefix has been removed - please, use PA::$path static variable
    Logger::log("[Enter: function Network::do_network_setup]\n");
    //1. create folder with network address
    $network_dir = PA::$project_dir.'/networks/'.$this->address;
    $network_name = $this->address;
    $database_name = CURRENT_DB;//TODO find db name
    if(!mkdir($network_dir,0777)) {
      Logger::log("Thowing Exception NETWORK_MKDIR_FAILED");
      throw new PAException(NETWORK_MKDIR_FAILED,"Failed to create network directory ($network_dir)");
    }
    //2. (deleted)

    //3. make PeepAgg.mysql for network and copy it here
    if(file_exists(PA::$project_dir . '/db/PeepAgg_tmpl.mysql')) {
       $source = PA::$project_dir . '/db/PeepAgg_tmpl.mysql';
    } else if(file_exists(PA::$core_dir . '/db/PeepAgg_tmpl.mysql')) {
       $source = PA::$core_dir . '/db/PeepAgg_tmpl.mysql';
    }
    $destination = $network_dir.'/PeepAgg.mysql';
    if($handle = @fopen($source, "r")) {
      $content = fread($handle, filesize($source));
      $content = str_replace('/%network_name%/', $network_name, $content);
      if($handle_w = fopen($destination, "w")) {
        if(!fwrite($handle_w, $content)) {
          Logger::log("Thowing Exception NETWORK_FILE_WRITE");
          throw new PAException(NETWORK_FILE_WRITE,"Network file could not be written.");
        }
        fclose($handle_w);
      }
      fclose($handle);
    }
    //4. open PeepAgg.mysql and create tables for network
    # if we have come this far then all went well and we can create new tables

    $file_content = file(PA::$project_dir.'/networks/'.$this->address.'/PeepAgg.mysql');

    $query = "";
    if ( !$file_content ) {
      Logger::log("Thowing Exception NETWORK_MYSQL_FILE");
      throw new PAException(NETWORK_MYSQL_FILE,"Database cant be configured");
    }
    foreach($file_content as $sql_line) {
      $tsl = trim($sql_line);
      if (($sql_line != "") && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != "#")) {
        $query .= $sql_line;
        if(preg_match("/;\s*$/", $sql_line)) {
          $query = trim($query);
          $res = Dal::query($query);
          $query = "";
        }
      }
    }

    //5. Run database update script to bring everything up to date
    define("PEEPAGG_UPDATING", 1);
    require_once "web/extra/db_update.php";
    try {
      $upd = new db_update_page();
      $upd->update_single_network($this->address);
    } catch (Exception $e) {
      echo $e->getMessage();
      die();
    }
    Logger::log("[Exit: function Network::do_network_setup]\n");
    return;
  }
  /**
  * get number of networks in a category
  * @access public
  * @param int $category_id
  */
  public static function get_threads_count_of_category( $category_id ) {
    Logger::log("Enter: Network::get_threads_count_of_category() | Args:  \$category_id = $category_id");
    //$sql = "SELECT count(*) AS cnt FROM {groups} WHERE category_id = ? AND reg_type <> ?";
    $sql = "SELECT count(*) AS cnt FROM {networks} AS N WHERE category_id = ? AND is_active = ? AND type = ? ";
    $data = array($category_id, 1, REGULAR_NETWORK_TYPE);
    $res = Dal::query($sql, $data);
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    Logger::log("Exit: Network::get_threads_count_of_category() | Return: ");
    return $row->cnt;
  }
  /**
  * get number of networks in the system
  * @access public
  */
  public static function get_total_networks() {
    Logger::log("Enter: Network::get_total_networks() | ");
    $res = Dal::query("SELECT count(*) AS cnt FROM {networks}  WHERE is_active=?", array(1));
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    Logger::log("Exit: Network::get_total_networks() | Return: $row->cnt");
    return $row->cnt;
  }
  /**
  * get total members of the network
  * @access public
  */
  public static function get_member_count($network_id) {
    return Dal::query_first("SELECT member_count FROM {networks} WHERE network_id = ?", array($network_id));
  }
  /**
  * get all description of network by id
  * network info , members, owner id
  * @access public
  */
  public static function get_by_id($network_id) {
    Logger::log("Enter: Network::get_by_id($network_id)");
    //OPT: Uses primary key on networks, then network_user_type key on networks_users.  Equivalent to:
    // SELECT * FROM {networks} WHERE network_id=? AND type=? (LIMIT 1)
    // SELECT NU.user_id FROM networks_users WHERE network_id=(network_id from last query) AND user_type=? (LIMIT 1)

/*
    NOTE: sql changed - when you want to get network by ID, network type should not be one of sql criterias!!!

    $sql = "SELECT N.*, NU.user_id FROM {networks} AS N LEFT JOIN {networks_users} AS NU ON NU.network_id=N.network_id WHERE N.is_active = ? AND N.network_id = ? AND NU.user_type = ? AND N.type = ?";
    $res = Dal::query($sql,array(1, $network_id, NETWORK_OWNER, REGULAR_NETWORK_TYPE));
*/
    $sql = "SELECT N.*, NU.user_id FROM {networks} AS N LEFT JOIN {networks_users} AS NU ON NU.network_id=N.network_id WHERE N.is_active = ? AND N.network_id = ? AND NU.user_type = ?";
    $res = Dal::query($sql,array(1, $network_id, NETWORK_OWNER));
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $row->total_members = $row->member_count; // alias for old code that expects to see 'total_members' rather than 'member_count'
    Logger::log("Exit: Network::get_by_id()");
    return $row;
  }

  /**
  * get the user_id of the owner of the network
  * @access public static
  */
  public static function get_network_owner($network_id) {
    Logger::log("[Enter: static function Network::get_network_owner]network_id = $network_id");
    $sql = "SELECT owner_id FROM {networks} WHERE network_id = ?";
    $data = array($network_id);
    $res = Dal::query($sql,$data);
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);

    if (!$row) {
      Logger::log("[Exit: static function Network::get_netwok_owner], case: NO OWNER! ");
      return NULL;        // No owner - bad, but known to happen on early early code!
    }

    if ($row->owner_id == 0) {     // required for compability with earlier PA versions
       Logger::log("[Exit: static function Network::get_netwok_owner], case: OWNER_ID = 0 ");
       return SUPER_USER_ID;
    } else {
       Logger::log("[Exit: static function Network::get_netwok_owner], case: OWNER FOUND.");
       return $row->owner_id;
    }
  }

  /** get the list of the networks based on number of members in the network
  * @access public.
  */
  public function get_largest_networks ( $cnt=FALSE, $show='ALL', $page=1, $sort_by='created', $direction='DESC') {
    Logger::log("Enter: Network::get_largest_networks() ");

    if ($cnt) {
      $ct = Dal::query_first("SELECT COUNT(*) FROM networks WHERE is_active=1 AND type=".REGULAR_NETWORK_TYPE);
      Logger::log("Exit: Network::get_largest_networks() (counted: count=$ct)");
      return $ct;
    }

    $order_by = $sort_by.' '.$direction;
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }

    $sql = "SELECT N.member_count AS members, N.*, N.owner_id AS owner_id, N.name as network_name FROM  {networks} AS N WHERE N.type = ".REGULAR_NETWORK_TYPE." AND N.is_active = 1 ORDER BY $order_by  $limit";
    $res = Dal::query($sql);
    $network_description = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $network_description[] = $row;
      }
    }

    Logger::log("Exit: Network::get_largest_networks() ");
    return $network_description;
  }

  /**
  * use for searching Networks by the network_id , network_name or network_tag
  * @access public
  */
  public static function get_networks_info_by_search($condition,$cnt=FALSE,$show='ALL', $page=1, $sort_by='created', $direction='DESC') {
    Logger::log("Enter: Network::get_networks_info_by_search()");
    $data='%'.$condition['keyword'].'%';
    $name=$condition['name_string'];
    if ($sort_by == 'members' ) {
      $order_by = $sort_by.' '.$direction;
    }
    else {
      $order_by = 'N.'.$sort_by.' '.$direction;
    }
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    }
    else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }
    $sql=" SELECT *, COUNT(U.user_id) AS members,C.name as category_name,N.description as description,U.user_id as owner_id,N.name as network_name from { networks } as N left join { categories } AS C on N.category_id = C.category_id LEFT JOIN { networks_users } AS NU ON N.network_id = NU.network_id left join { users } AS U ON NU.user_id = U.user_id WHERE N.$name LIKE ? AND N.type = ".REGULAR_NETWORK_TYPE." AND N.is_active = 1 GROUP BY N.network_id ORDER BY $order_by $limit ";
    $res = Dal::query($sql,$data);

    if ($cnt == TRUE) {
      return $res->numRows();
    }
    $search_result = array();
    if($res->numRows()) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $search_result[] = $row;
      }
    }
    Logger::log("Exit: Network::get_networks_info_by_search()");
    return $search_result;
  }
  /**
  * gives the all network information accociated with categories.
  * @access public static
  */
  public static function category_network_listing() {
    Logger::log("[Enter: static function Network::category_network_listing]");
    $sql = " SELECT N.maximum_members,N.network_id, N.name as network_name, C.category_id, C.name as category_name,N.address from { networks } as N RIGHT JOIN { categories } as C on N.category_id = C.category_id  AND N.type = ".REGULAR_NETWORK_TYPE." WHERE C.position RLIKE '^[0-9]+>$'  ORDER BY C.category_id ";
    $res = Dal::query($sql);
    if( $res->numRows() ) {
      while( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
        $result[] = $row;
      }
    }
    Logger::log("[Exit: static function Network::category_network_listing] ");
    return $result;
  }
  /**
  * gives the all networks information of the user
  * @access public static
  */
  public static function get_networks_by_user ($uid, $cnt=FALSE, $show='ALL', $page=1, $sort_by='created', $direction='DESC', $network_type = REGULAR_NETWORK_TYPE) {
    Logger::log("Enter: Network::get_networks_by_user() ");
    $order_by = $sort_by.' '.$direction;
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    }
    else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }
    if ($network_type === ALL_NETWORKS) {
      $sql = "SELECT N.member_count AS members, N.*, N.owner_id AS owner_id,name as network_name FROM  { networks } AS N LEFT JOIN { networks_users } AS NU ON N.network_id = NU.network_id WHERE NU.user_id = ? AND N.type <> ".MOTHER_NETWORK_TYPE." AND N.is_active = 1 GROUP BY N.network_id ORDER BY $order_by  $limit";
    } else {
      $type = $network_type;
      $sql = "SELECT N.member_count AS members, N.*, N.owner_id AS owner_id,name as network_name FROM  { networks } AS N LEFT JOIN { networks_users } AS NU ON N.network_id = NU.network_id WHERE NU.user_id = ? AND N.type = ".$type." AND N.is_active = 1 GROUP BY N.network_id ORDER BY $order_by  $limit";
    }

    $res = Dal::query($sql, $uid);
    $network_description = array();
    if ( $res->numRows() ) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $network_description[] = $row;
      }
    }
    if ( $cnt ) {
      return $res->numRows();
    }
    Logger::log("Exit: Network::get_networks_by_user() ");
    return $network_description;
  }

  //Added for 1.2 release
  /**
  * get total groups of the network
  * @access public
  */
  public function get_group_count($network_id) {
    Logger::log("Enter: Network::get_group_count()");

    return ;
  }
  /**
  * get total contents of the network
  * @access public
  */
  public function get_content_count($network_id) {
    Logger::log("Enter: Network::get_content_count()");

    Logger::log("Exit: Network::get_content_count()");
    return;
  }
  /**
  * checks in advance if the network can be created in the system
  * It
  * @access public
  */
  public static function can_network_be_created() {
    //check to see if network folder exists and is_writable
    // right now it contains only one condition
    // further checks can be added here

    if (!PA::$network_capable) {
      return array('error' => TRUE, 'error_msg' => __("Cannot create networks: this installation is not configured for network operation."));
    }

    $network_folder = PA::$project_dir."/networks";
    if (!is_writable($network_folder)) {
      return array('error' => TRUE, 'error_msg' => sprintf(__('Cannot create networks: %s is not writable'), $network_folder));
    }

    if (!PA::$config->enable_network_spawning) {
      return array('error' => TRUE, 'error_msg' => __("Cannot create networks: Network spawning has been disabled on this installation."));
    }

    return array('error' => FALSE);
  }


  /**
  * internal function called when something goes wrong during network creation step
  * @access public
  *
  * */
  public function do_rollback() {
    // global var $path_prefix has been removed - please, use PA::$path static variable
    Network::delete_all($this->network_id);
  }

  /**
  * delete all network related directories and tables
  * @access  static
  * Input $network_id
  * */
  public static function delete_all($network_id) {
    // global var $path_prefix has been removed - please, use PA::$path static variable
    $sql = "SELECT address from {networks} where network_id=?";
    $res = Dal::query($sql,array($network_id));
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $address = trim($row->address);
    // deleting the network at first
    $res = Dal::query("DELETE FROM {networks} WHERE network_id = ?",array($network_id));
    // cleaning up after delete
      //delete the directory of network
    if( is_dir(PA::$project_dir.'/networks/'.$address) && trim($address!='')  ) {
      system('rm -rf '.PA::$project_dir.'/networks/'.$address);
    }
    // delete all related tables
    $res =Dal::query('SHOW TABLES');
    $db_name = 'Tables_in_'.CURRENT_DB;
    $tbl_prefix = $address.'_';
    while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
       $tables[] = trim($row->$db_name);
     }
     foreach ($tables as $table ) {
      if(ereg('^'.$tbl_prefix,$table)) {
        Dal::query('DROP TABLE IF EXISTS '.$table);
      }
    }
    $res = Dal::query("DELETE FROM {tags_networks} WHERE network_id = ?", array($network_id));
    $res = Dal::query("DELETE FROM {networks_users} WHERE network_id = ?",array($network_id));
    $res = Dal::query("DELETE FROM mc_db_status WHERE network = ?", array($address));
    return;
  }

  /**
  * returns the type of network
  * @access public static
  */
  public static function find_network_type( $nid ) {
     Logger::log("Enter: Network::find_network_type() with parameter nid=".$nid);
    $res =Dal::query('SELECT type FROM {networks} WHERE  network_id = ? ', array($nid));
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    Logger::log("Exit: Network::find_network_type() with result type=".$row->type);
    return $row->type;
  }
  /**
  * returns TRUE FALSE according to network's type
  * @access public static
  */
  public static function is_mother_network($nid) {
    Logger::log("Enter: Network::is_mother_network() with nid=".$nid);
    $type = Network::find_network_type($nid );
    if ( MOTHER_NETWORK_TYPE ==  $type ) {
      $r = TRUE;
    } else {
      $r = FALSE;
    }
    Logger::log("Exit: Network::is_mother_network() with result type=".$r);
    return $r;
  }


 /**
  *  Function : get_members()
  *  Purpose  : get members of the network based on the parameters passed to it
  *  @param    $params - array - the various elements of this array may be defined
  *            as follows
  *            $params['search_keyword']=>'blah'
  *            $params['neglect_owner']=>TRUE - if we want to exclude owner of n/w
  *            $params['cnt']=>TRUE - if we want to get count
  *            $params['network_id']=>2 - get members of network having id 2
  *            $params['sort_by']=> 'U.created' - column name
  *            $params['direction']=> DESC - order by clause
  *            $params['page']=> 2 - page number 2
  *            $params['show']=> 5 - show 5 records
  *  @return   type array
  *            returns array of members of n/w
  */
  public static function get_members($params) {
    Logger::log("[ Enter: function Network::get_members] \n");

    // fix Mother Network owner not set issue
    if($params['network_id'] == 1) {
      $owner_arr = Network::get_members_by_type(array('network_id' => 1, 'user_type' => NETWORK_OWNER));
      if($owner_arr['total_users'] == 0) {
        Network::update_membership_type(array('user_id_array' => array(1), 'user_type' => NETWORK_OWNER, 'network_id' => 1));
      }
    }

    $data = array();
    $notshow = UNVERIFIED;
    if(isset($params['show_waiting_users']) && ($params['show_waiting_users'] == true)){
       $sql = "SELECT NU.user_id, NU.network_id, NU.user_type, U.*
               FROM {networks_users} AS NU, {users} AS U
               WHERE NU.network_id = ?
               AND NU.user_id = U.user_id
               AND U.is_active <> ? ";
       //count query to find total members
       $sql_count = "SELECT count(*) AS CNT
               FROM {networks_users} AS NU, {users} AS U
               WHERE NU.network_id = ?
               AND NU.user_id = U.user_id
               AND U.is_active <> ?  ";
       array_push($data, $params['network_id'], DELETED);
    } else {
       $sql = "SELECT NU.user_id, NU.network_id, NU.user_type, U.*
               FROM {networks_users} AS NU, {users} AS U
               WHERE NU.network_id = ?
               AND NU.user_id = U.user_id
               AND U.is_active <> ? AND U.is_active <> ? AND NU.user_type <> ? ";
       //count query to find total members
       $sql_count = "SELECT count(*) AS CNT
               FROM {networks_users} AS NU, {users} AS U
               WHERE NU.network_id = ?
               AND NU.user_id = U.user_id
               AND U.is_active <> ? AND U.is_active <> ? AND NU. user_type <> ? ";
       array_push($data, $params['network_id'], DELETED, UNVERIFIED, NETWORK_WAITING_MEMBER); // get only active members
    }
    //we dont want the owner of the network to come in listing
    if (!empty($params['neglect_owner']) && ($params['neglect_owner'] == TRUE)) {
      $sql.=" AND NU.user_type <>  ? AND U.user_id <> ? ";
      $sql_count.=" AND NU.user_type <> ?  AND U.user_id <> ? ";
      array_push($data, NETWORK_OWNER, SUPER_USER_ID);
    }
    //we have search criteria
    if (!empty($params['search_keyword'])) {
      $sql.=" AND U.login_name LIKE '%". $params['search_keyword'] ."%' ";
      $sql_count.=" AND U.login_name LIKE '%". $params['search_keyword'] ."%' ";
    }
    // if we are intersted in getting total records only then return count
    if ((!empty($params['cnt'])) && ($params['cnt'] == TRUE)) {
      $cnt = Dal::query_one_assoc($sql_count,$data);
      Logger::log("[ Enter: function Network::get_members returning count] \n");
      return $cnt['CNT'];
    }
    // OK we want to find the details
    $sort_by = (!empty($params['sort_by'])) ? $params['sort_by'] : 'U.created';
    $direction = (!empty($params['direction'])) ? $params['direction'] : 'DESC';
    $order_by = ' ORDER BY '. $sort_by .' '. $direction;
    if (!empty($params['page']) && (!empty($params['show']))) {
      $start = ($params['page'] -1) * $params['show'];
      $limit = ' LIMIT '.$start.','.$params['show'];
    } else {
      $limit = "";
    }
    $sql = $sql . $order_by . $limit;
    $res = Dal::query($sql, $data);
    $users_data = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
    	// get current info for the user
    	$u = new User();
    	$u->load((int)$row['user_id']);
    	$row['display_name'] = $u->display_name;
        $row['user_obj'] = $u;     // add user object, when it is already loaded!
      $users_data[] = $row;
    }
    if (empty($users_data)) {
      return NULL;
    }
    $final_array = array('users_data' => $users_data, 'total_users' => count($users_data));
    Logger::log("[ Exit: function Network::get_members] \n");
    return $final_array;
  }

   /**
  * get the details of mother network
  * @access public.
  * @param array of parameters
  * format of $params
  * @return mother network details
  */
  public static function get_mothership_info() {
    Logger::log("[ Enter: function Network::get_mothership_info] \n");
    $sql = " SELECT * FROM {networks} WHERE is_active = 1 AND type = ".MOTHER_NETWORK_TYPE;
    $network = Dal::query_one_assoc($sql);
    if(empty($network)){
      throw new PAException(MOTHER_NETWORK_NOT_DEFINED, "Configuration error: Mother network not configured.");
    }
    // we ned to set the network owner proprly
    // as it is set to 0 by default, which is not actually true
    if($network['owner_id'] < 1) {
      // we use id = 1 for now as it seems there is no way to set it
      $network['owner_id'] = 1;
    }

    return Network::from_array($network);
    Logger::log("[ Exit: function Network::get_mothership_info] \n");
  }

  /** This function will return statistics of a network
  * @access public
  * @return count of registered members, count of groups, count of contents
  */
  public static function get_network_statistics($param) {
    // setting count variables to 0
    $groups_count = $contents_count = $registered_members_count = $online_members_count = 0;
    $param['cnt'] = TRUE;
    $param['neglect_owner'] = FALSE; //network owner is a member with type OWNER
    $registered_members_count = Network::get_members($param);
    $sql = ' SELECT count(*) AS cnt FROM {groups} AS G,{contentcollections} AS CC WHERE CC. collection_id = G.group_id AND CC.is_active = ? AND G.reg_type <> ? ';
    $data = array(ACTIVE, REG_INVITE);
    $res = Dal::query($sql, $data);
    if ( $res->numRows() ) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $groups_count = $row->cnt;
    }

    $sql = 'SELECT count(*) as cnt  FROM {contents} WHERE is_active = ?';
    $data = array(ACTIVE);
    $res = Dal::query($sql, $data);
    if ( $res->numRows() ) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $contents_count = $row->cnt;
    }
    //getting count of online registered users
    $timestamp = time() - MAX_TIME_ONLINE_USER;
  // MAX_TIME_ONLINE_USER = 1800 sec to get a realistic count of currently online users
    $online_members_count = User::count_online_users($timestamp);

    return array('registered_members_count'=>$registered_members_count,
                  'groups_count'=>$groups_count,
                  'contents_count'=>$contents_count,
                  'online_members_count'=>$online_members_count
                  );
  }

  /**
  * update the membership type of a user to member, moderator, disabled, owner
  * @access public
  * @param array of parameters that is network id, user id array and membership type
  */

  public static function update_membership_type ( $params ) {
    Logger::log("[ Enter: function Network::update_membership_type] \n");

    /* NOTE: the owner ID of a network is also cached in the network
       table (network.owner_id).  This function will not update
       network.owner_id, as I don't think you can change the network
       owner.  If we ever let people change network owner, please
       change this function to update network.owner_id! */

    $sql_extra = '';
    $data = array( $params['user_type'], $params['network_id'] );
    $user_id_array = $params['user_id_array'];
    if( is_array( $user_id_array ) ) {
      foreach( $user_id_array as $user_id ) {
        $data[] = $user_id;
        $sql_extra .= '? ,';
      }
      $sql_extra = substr( $sql_extra, 0, ( strlen( $sql_extra ) - 2) );
    }
    else {
      $user_ids = $user_id_array;
      $sql_extra = '?';
    }

    $sql = 'UPDATE {networks_users} SET user_type = ? WHERE network_id = ? AND user_id IN ( '.$sql_extra.' )';

    if( !$row = Dal::query($sql, $data) ) {
      throw new PAException( INVALID_ARGUMENTS, 'Data not appropriate for updating user type.');
    }

    Logger::log("[ Exit: function Network::update_membership_type] \n");
    return $row;
  }
  /**
  * approve user to be member of the network
  * @access public
  * @param array of parameters that is network id, user id array
  */

  public static function approve($network_id, $uid) {
    Logger::log("[ Enter: function Network::approve] \n");
    //check if the user is in waiting list
    $sql = ' SELECT * from {networks_users} WHERE network_id = ? AND user_id = ? AND user_type = ? ';
    $data = array($network_id, $uid, NETWORK_WAITING_MEMBER);
    $res = Dal::query($sql, $data);
    if (!$res->numRows()) {
      throw new PAException( OPERATION_NOT_PERMITTED, 'user is not waiting member of network.');
    }
    $sql = "UPDATE {networks_users} SET  user_type = ? WHERE network_id = ? AND user_type = ?  AND user_id = ? ";
    $data = array(NETWORK_MEMBER, $network_id, NETWORK_WAITING_MEMBER, $uid );
    $res = Dal::query($sql, $data);
    // Update cached member count
    Network::update_network_member_count($network_id);
    Logger::log("[ Exit: function Network::approve] \n");
    return;
  }
 /**
  * deny user to be member of the network
  * @access public
  * @param array of parameters that is network id, user id array
  */

  public static function deny($network_id, $uid) {
    Logger::log("[ Enter: function Network::deny] \n");
    //check if the user is in waiting list
    $sql = ' SELECT * from {networks_users} WHERE network_id = ? AND user_id = ? AND user_type = ? ';
    $data = array($network_id, $uid, NETWORK_WAITING_MEMBER);
    $res = Dal::query($sql, $data);
    if (!$res->numRows()) {
      throw new PAException( OPERATION_NOT_PERMITTED, 'user is not waiting member of network.');
    }
    $res = Dal::query("DELETE FROM {networks_users} WHERE network_id = ? AND user_id = ? ", array($network_id, $uid));
    Logger::log("[ Exit: function Network::deny] \n");
    return;
  }
  /**
  *  Function : get_members_by_type()
  *  Purpose  : get members of the network based on the parameters passed to it
  *  @param    $params - array - the various elements of this array may be defined
  *            as follows
  *            $params['cnt']=>TRUE - if we want to get count
  *            $params['network_id']=>2 - get members of network having id 2
  *            $params['sort_by']=> 'U.created' - column name
  *            $params['direction']=> DESC - order by clause
  *            $params['page']=> 2 - page number 2
  *            $params['show']=> 5 - show 5 records
  *  @return   type array
  *            returns array of members of n/w
  */
  public static function get_members_by_type($params) {
    Logger::log("[ Enter: function Network::get_members_by_type] \n");
    $data = array();
    $sql = "SELECT NU.user_id, NU.network_id, NU.user_type, U.*
            FROM {networks_users} AS NU, {users} AS U
            WHERE NU.network_id = ?
            AND NU.user_id = U.user_id
            AND U.is_active <> ? AND U.is_active <> ? AND NU.user_type = ? ";
    //count query to find total members
    $sql_count = "SELECT count(*) AS CNT
                  FROM {networks_users} AS NU, {users} AS U
                  WHERE NU.network_id = ?
                  AND NU.user_id = U.user_id
                  AND U.is_active <> ? AND U.is_active <> ? AND NU. user_type = ? ";
    array_push($data, $params['network_id'], DELETED, UNVERIFIED, $params['user_type']); // get only active members
    //we dont want the owner of the network to come in listing


    // if we are intersted in getting total records only then return count
    if ((!empty($params['cnt'])) && ($params['cnt'] == TRUE)) {
      $cnt = Dal::query_one_assoc($sql_count,$data);
      Logger::log("[ Enter: function Network::get_members_by_type returning count] \n");
      return $cnt['CNT'];
    }
    // OK we want to find the details
    $sort_by = (!empty($params['sort_by'])) ? $params['sort_by'] : 'U.created';
    $direction = (!empty($params['direction'])) ? $params['direction'] : 'DESC';
    $order_by = ' ORDER BY '. $sort_by .' '. $direction;
    if (!empty($params['page']) && (!empty($params['show']))) {
      $start = ($params['page'] -1) * $params['show'];
      $limit = ' LIMIT '.$start.','.$params['show'];
    } else {
      $limit = "";
    }
    $sql = $sql . $order_by . $limit;
    $res = Dal::query($sql, $data);
    $users_data = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      $users_data[] = $row;
    }
    if (empty($users_data)) {
      return NULL;
    }
    $final_array = array('users_data' => $users_data, 'total_users' => count($users_data));
    Logger::log("[ Exit: function Network::get_members_by_type] \n");
    return $final_array;
  }
   /**
  *  Function : approve_all()
  *  Purpose  : approve all the waiting members to members
  */
  public static function approve_all($network_id) {
    Logger::log("[ Enter: function Network::approve_all] \n");
    $sql = "UPDATE {networks_users} SET user_type = ? WHERE  network_id = ? AND user_type = ? ";
    $data = array(NETWORK_MEMBER, $network_id, NETWORK_WAITING_MEMBER);
    $res = Dal::query($sql, $data);
    Network::update_network_member_count($network_id);
    Logger::log("[ Exit: function Network::approve_all] \n");
    return;
  }

  /**
  *  Function : get_user_network_info()
  *  Purpose  : retriving all the networks either public or private
  */
  public function get_user_network_info( $sql_param=array() ,$params=NULL) {
    Logger::log("[ Enter: function Network::get_user_network_info] \n");
    $sql = "SELECT N.*,NU.user_type FROM {networks} AS N, {networks_users} AS NU WHERE NU.network_id = N.network_id ";
    if (count($sql_param)) {
      for ($i=0; $i<count($sql_param); $i++) {
        $parameter = $sql_param[$i];
        foreach ($parameter as $field => $value) {
          switch ($field) {
            case 'key':
              $sql .= ' AND '.$value.' ';
            break;
            case 'operator':
              $sql .= ' '.$value. ' ? ';
            break;
            case 'value':
              $data[] = $value;
            break;
          }
        }
      }
    }
    //paging variables if set
    $sort_by = (!empty($params['sort_by'])) ? $params['sort_by'] : 'created';
    $direction = (!empty($params['direction'])) ? $params['direction'] : 'DESC';
    $order_by = ' ORDER BY '.$sort_by.' '.$direction;
    if ( $params['page'] && $params['show'] && !isset($params['cnt'])) {
      $start = ($params['page'] -1) * $params['show'];
      $limit = ' LIMIT '.$start.','.$params['show'];
    } else {
      $limit = "";
    }
    $sql = $sql . $order_by . $limit;
    $res = Dal::query($sql,$data);
    $result = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
       $result[] = $row;
    }
    Logger::log("[ Exit: function Network::get_user_network_info] \n");
    return $result;
  }
  /** make an entry in moderation queue if network content moderation is set
   * @access public
   * @param int content_id ID of content to be moderated
   * collection_id = -1 as default for independent content
   */
   public static function moderate_network_content ($collection_id = -1, $content_id) {

     Logger::log("Enter: Network::moderate_network_content() | Args: \$content_id = $content_id");
     $res = Dal::query("INSERT INTO {moderation_queue} (collection_id, item_id, type) VALUES (?, ?, ?)", array($collection_id, $content_id, "content"));
     Content::update_content_status($content_id, MODERATION_WAITING);
     Logger::log("Exit: Network::moderate_network_content()");
     return;
   }
   /**
    * approve contents if network content moderation is set
    * @access public
    * @param int id of content
    * @param string type ie user/content
    */
   public static function approve_content ($content_id, $type = 'content') {
     Logger::log("Enter : Network::approve_content() | Args: \$item_id = $content_id, \$type = $type");
     Content::update_content_status($content_id, ACTIVE);
     $res = Dal::query("DELETE FROM {moderation_queue} WHERE item_id = ? and type= ?", array($content_id, $type));
     Logger::log("Exit : Network::approve_content()");
     return;
   }
   /**
    * disapprove content/user in moderated queue
    * @access public
    * @param int id of user/content
    * @param string type ie user/content
    */
   public function disapprove_content ($content_id, $type = 'content') {
     Logger::log("Enter : Network::disapprove_content() | Args: \$item_id = $content_id, \$type = $type");
     $res = Dal::query("DELETE FROM {moderation_queue} WHERE item_id = ? and type= ?", array($content_id, $type));
     Content::delete_by_id($content_id);
     Logger::log("Exit : Network::disapprove_content()");
     return;
   }
    /**
  * check for content in moderation queue.
  * @access private
  * @param int $content_id, $collection_id
  * if collection_id is not set then it is -1
  * which indicates that content is not part of any collection
  * @param string type ie user/content
  */

  public static function item_exists_in_moderation($content_id, $collection_id, $type = 'content') {
     Logger::log("Enter: Network::item_exists_in_moderation() | Args: \$item_id = $content_id, \$type = $type");
     $res = Dal::query("SELECT * FROM {moderation_queue} WHERE collection_id = ? AND item_id = ? AND type = ?", array($collection_id, $content_id, $type));
     if ($res->numRows()) {
       Logger::log("Exit: Network::item_exists_in_moderation() | Return: TRUE");
       return TRUE;
     }
     else {
       Logger::log("Exit: Network::item_exists_in_moderation() | Return: FALSE");
       return FALSE;
     }
  }

}
?>
