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


require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Content/Content.php";
require_once "api/User/User.php";
require_once "api/Logger/Logger.php";
require_once "api/PAException/PAException.php";
require_once "db/Dal/Dal.php";
require_once "api/Category/Category.php";
require_once "api/Invitation/Invitation.php";
require_once "api/MessageBoard/MessageBoard.php";
require_once "ext/Access/Access.php";
require_once "api/Tag/Tag.php";
require_once "api/api_constants.php";
require_once "api/Permissions/PermissionsHandler.class.php";

  /**
   * constants
   */



   // define member type of group



   // define group properties

   define('REG_OPEN',0);
   define('REG_MODERATED',1);
   define('REG_INVITE',2);

   define('ACCESS_PUBLIC',0);
   define('ACCESS_PRIVATE',1);

   define('NOT_MODERATED',0);
   define('IS_MODERATED',1);



class Group extends ContentCollection {
  /**
   * @var enum access type of group Public/Private
   */
  public $access_type;

  /**
   * @var enum user registration type moderated/inviteonly/open
   */
  public $reg_type;

  /**
   * @var array group moderators
   */
  public $moderators = array();

  /**
   * @var int whether content is moderated or not.
   */
  public $is_moderated;
    /**
   * @var object
   */

  public $acl_object;

  public $header_image;

  public $header_image_action ;

  public $display_header_image;

  public $group_extra;

  public $created;

  /**
  * Default value for the collection type which is group type collection here.
  */
  public $type = GROUP_COLLECTION_TYPE;

  /**
  * Default value for the status of group collection.
  */
  public $is_active = ACTIVE;

  /**
  * To support the super groups, group type attribute is added to the groups.
  * By default all the groups will be of the regular type.
  * Group type is a enum type attribute.
  */
  public $group_type = 'regular';

  public $owner_id;

  /**
   * Constant var definitions
   */
  public $REG_OPEN = 0;
  public $REG_MODERATED = 1;
  public $REG_INVITE = 2;
  public $ACCESS_PUBLIC = 0;
  public $ACCESS_PRIVATE = 1;
  public $NOT_MODERATED = 0;
  public $IS_MODERATED = 1;

  /**
   * contructor, creates database handle
   * @access public
   */
  public function __construct() {
    Logger::log("Enter: Group::__construct");
    parent::__construct();
    $this->acl_object = new Access();
    $this->type = GROUP_COLLECTION_TYPE;
    Logger::log("Exit: Group::__construct");
  }

  /**
   * Destroys a database connection instances upon deletion on object.
   * @access public
   */
  public function __destruct() {
    $this->is_active = ACTIVE;
    $this->type = GROUP_COLLECTION_TYPE;
    parent::__destruct();
  }

  /**
   * load collection_id
   * @access private
   * @param int content_id
   */
   public function load ($collection_id) {
     Logger::log("Enter: Group::load() | Args: \$collection_id = $collection_id");
     parent::load($collection_id);
     $res = Dal::query("SELECT * FROM {groups} WHERE group_id = ?", array($collection_id));

     if ($res->numRows()) {
       $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
       foreach ($row as $key=>$v) $this->{$key} = $v;
       $Category = new Category();
       $Category->category_id = $this->category_id;
       $Category->load($Category->category_id);
       $this->category_name = $Category->name;
     }
     else {
       Logger::log("Thowing Exception CONTENT_NOT_FOUND");
       throw new PAException(CONTENT_NOT_FOUND, "No group matched the provided ID");
     }

     if($oid = Group::get_owner_id((int)$collection_id)) {
       $this->owner_id = $oid;
     } else {
       $this->owner_id = $this->author_id;
     }

     $res = Dal::query("SELECT * FROM {groups_users} WHERE group_id = ? AND (user_type =? OR user_type = ? )", array($collection_id, MODERATOR, OWNER));

     while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
       // if users is a moderator or owner
       $this->moderators[] = $row['user_id'];
     }
     Logger::log("Exit: Group::load()");
   }

   /**
    * Saves Group data to database
    * @access public
    * @param int $user_id ID of the user trying to save
    */
  public function save ($user_id=NULL) {
     Logger::log('Enter: Group::save() | Args: \$user_id = '.$user_id);
     if (!empty($user_id)) {
       $this->author_id = $user_id;
     }

     if (empty($this->title)) {
      Logger::log('Exit: Group::save(). Title of the group is not specified.');
      throw new PAException(GROUP_NAME_NOT_EXIST, 'Title of the group is not specified');
     }
     if (!isset($this->access_type)) {
      Logger::log('Exit: Group::save(). Access type for the group is not specifed');
      throw new PAException(GROUP_ACCESS_TYPE_NOT_EXIST, 'Access type for the group is not specifed');
     }
     if (!isset($this->reg_type)) {
      Logger::log('Exit: Group::save(). User registration type is not specified for the group.');
      throw new PAException(GROUP_REGISTRATION_TYPE_NOT_EXIST, 'User registration type is not specified for the group.');
     }
     if (!isset($this->is_moderated)) {
      Logger::log('Exit: Group::save(). Moderation type is not specifed for the group.');
      throw new PAException(GROUP_IS_MODERATED_NOT_EXIST, 'Moderation type is not specifed for the group.');
     }

     if (!empty($this->extra)) {
     	$this->extra = serialize($this->extra);
     }

     //if collection_id exists the update else insert
     if ($this->collection_id) {

//       $user_type = Group::get_user_type ($this->author_id, $this->collection_id);
//       $access = $this->acl_object->acl_check( 'action', 'edit', 'users', $user_type, 'group', 'all' );
       $access = PermissionsHandler::can_group_user(Group::get_owner_id((int)$this->collection_id), $this->collection_id, array('permissions' => 'manage_groups'));

       if (!$access) {

         throw new PAException(OPERATION_NOT_PERMITTED, 'You are not authorised to edit this group.');
       }

       $sql = "UPDATE {groups} SET access_type = ?, reg_type = ?, is_moderated = ?, category_id = ? , header_image = ? , header_image_action = ?, display_header_image = ?, group_type =?, extra=? WHERE group_id = ?";
       try {
         $res = Dal::query($sql, array($this->access_type, $this->reg_type, $this->is_moderated, $this->category_id, $this->header_image, $this->header_image_action, $this->display_header_image, $this->group_type, @$this->extra, $this->collection_id));
         parent::save();
       } catch (Exception $e) {
         Dal::rollback();
         throw $e;
       }
     }
     else {
       //only registered user can create a group
       // This already has been taken care via session
       // we can add further modification if not use session user_id

       try {
         parent::save();
         $sql = "INSERT INTO {groups} (group_id, access_type, reg_type, is_moderated, category_id, header_image, header_image_action, display_header_image, group_type, extra) VALUES (?, ?, ?, ?, ?,?,?,?, ?, ?)";
         $data = array($this->collection_id, $this->access_type, $this->reg_type, $this->is_moderated,$this->category_id,$this->header_image,$this->header_image_action,$this->display_header_image, $this->group_type, @$this->extra);
         $res = Dal::query($sql, $data);

         $this->created = time();

         $sql = "INSERT INTO {groups_users} (group_id, user_id, user_type, created) VALUES (?, ?, ?, ?)";
         $res = Dal::query($sql, array($this->collection_id, $this->author_id, OWNER, $this->created));

         foreach ($this->moderators as $mod) {
           $sql = "INSERT INTO {groups_users} (group_id, user_id, user_type, created) VALUES (?, ?, ?, ?)";
           $res = Dal::query($sql, array($this->collection_id, $mod, MODERATOR, $this->created));
         }
         Dal::commit();
       } catch (Exception $e) {
         Dal::rollback();
         throw $e;
       }
     }

     Logger::log("Exit: Group::save()");
     return $this->collection_id;
   }

   /**
   * delete collection
   * @access private
   */
   public function delete() {
     Logger::log("Enter: Group::delete()");

     $res = Dal::query("DELETE FROM {groups_users} WHERE group_id = ?", array($this->collection_id));

     $res = Dal::query("DELETE FROM {moderation_queue} WHERE collection_id = ?", array($this->collection_id));

     $res = Dal::query("DELETE FROM {groups} WHERE group_id = ?", array($this->collection_id));

     // delete all forums in a group
     MessageBoard::delete_all_in_parent($this->collection_id,'collection');

     parent::delete();
     Logger::log("Exit: Group::delete()");
   }

   // Call this function to verify that the user ($user_id) has access
   // to post to this group.  If the user is not authorized, a
   // USER_ACCESS_DENIED exception is thrown, otherwise the access
   // type is returned.
   public function assert_user_access($user_id) {
     $user_type = Group::get_user_type ($user_id, $this->collection_id);

     $access = $this->acl_object->acl_check( 'action', 'add', 'users', $user_type, 'group', 'contents');

     if (!$access) {
       throw new PAException(USER_ACCESS_DENIED, "You are not authorized to access this group");
     }

     return $access;
   }

   /**
    * post content to groups
    * @access public
    * @param int content_id ID of a content to be saved.
    * @param int user_id ID of user trying to post to the group
    */
    public function post_content ($content_id, $user_id) {
     Logger::log("Enter: Group::save() | Args: \$content_id = $content_id, \$user_id = $user_id");
     if (!$this->is_active) {
       throw new PAException(OPERATION_NOT_PERMITTED, "Trying to post to a deleted group");
     }

     $this->assert_user_access($user_id);

     //Code to check the existing members before posting the content
     /*if (!Group::member_exists($content_id, $user_id)) {
       //throw new PAException(OPERATION_NOT_PERMITTED, "You are not authorized to post content for this group.");
       return;
     }*/
     $sql = "UPDATE {contents} SET collection_id = ? WHERE content_id = ?";
     $res = Dal::query($sql, array($this->collection_id, $content_id));
     Logger::log("Exit: Group::save()");
   }


   /**
    * remove content from group
    * @access public
    * @param int content_id ID of content to be removed
    * @param int  user_id ID of user trying to remove post
    */
  public function remove_post ($content_id, $user_id) {
     Logger::log("Enter: Group::save() | Args: \$content_id = $content_id, \$user_id = $user_id");
     if (!$this->is_active) {
       throw new PAException(OPERATION_NOT_PERMITTED, "Trying to remove a post from a deleted group");
     }

     $user_type = Group::get_user_type ($user_id, $this->collection_id);
     $access = $this->acl_object->acl_check( 'action', 'delete', 'users', $user_type, 'group', 'contents');

     if (!$access) {
       throw new PAException(USER_ACCESS_DENIED, "You are not authorized to access this page");
     }


      //TODO: Also delete from contentcollections_contents
     $content = Content::load_content($content_id, $user_id);
     $content->delete();
     Logger::log("Exit: Group::save()");
   }
   /**
    * access user permissions on a group
    * @access public
    * @param int user_id ID of user trying to perform some operation to group
    * @param constant $access access permission to be checked
    * @param int content id.
    */
   //TODO: take a proper look in to functionality
   public function check_access ($user_id, $access = USER_ACCESS_READ, $content_id = 0) {
     //TODO: disabling check access for now because we do not have a proper user permission system

     Logger::log("Enter: Group::check_access() | Args: \$user_id = $user_id");
     $res = Dal::query("SELECT G.access_type AS access_type FROM  {groups} AS G, {groups_users} AS GU WHERE GU.group_id = ? AND G.group_id = ?", array($this->collection_id, $this->collection_id));

     // TO DO: according to changes in admin role change user id check
     if (($content_id == 0) || ($user_id == 1)) {
       Logger::log("Exit: Group::check_access() | Return: TRUE");
       return TRUE;
     }
     if ($res->numRows()) {
       $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
       if ($row['access_type'] == 0) {
        Logger::log("Exit: Group::check_access() | Return: TRUE");
        return TRUE;
       }
       else {
         $res = Dal::query("SELECT * FROM  {groups_users} WHERE group_id = ? AND user_id = ?", array($this->collection_id, $user_id));

         if ($res->numRows()) {
           Logger::log("Exit: Group::check_access() | Return: TRUE");
           return TRUE;
         }
         else {
           Logger::log("Exit: Group::check_access() | Return: FALSE");
           return FALSE;
         }
       }
     }
     else {
        Logger::log("Exit: Group::check_access() | Return: FALSE");
        return FALSE;
     }
   }

   /**
    * flag a content to be moderated
    * @access public
    * @param int content_id ID of content to be moderated
    */
   public function moderate_content ($content_id) {
     Logger::log("Enter: Group::moderate_content() | Args: \$content_id = $content_id");
     $c = Content::load_content($content_id, $_SESSION['user']['id']);
     if(!Group::is_admin($this->collection_id, $c->author_id)) {
       $res = Dal::query("INSERT INTO {moderation_queue} (collection_id, item_id, type) VALUES (?, ?, ?)", array($this->collection_id, $content_id, "content"));
       Content::update_content_status($content_id, 2);
     }
     else {
        $this->approve($content_id, 'content');
     }

     Logger::log("Exit: Group::moderate_content()");
     return;
   }

   /**
    * flag a user to be moderated
    * @access public
    * @param int user_id ID of user to be moderated
    */
   private function moderate_user ($user_id) {
     Logger::log("Enter: Group::moderate_user() | Args: \$user_id = $user_id");
     if(!Group::item_exists_in_moderation($user_id, "user")) {
        $res = Dal::query("INSERT INTO {moderation_queue} (collection_id, item_id, type) VALUES (?, ?, ?)", array($this->collection_id, $user_id, "user"));
     } else {
        throw new PAException(OPERATION_NOT_PERMITTED, "Your request for joining the group has been sent already .");
     }
     Logger::log("Exit: Group::moderate_user()");
     return;
   }

   /**
    * get content/user in moderated queue
    * @access public
    * @param string type ie user/content
    */
   function get_moderation_queue ($type, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC') {
     Logger::log("Enter: Group::get_moderation_queue()");

     $order_by = $sort_by.' '.$direction;
     if ( $show == 'ALL' || $cnt == TRUE) {
       $limit = '';
     } else {
       $start = ($page -1)* $show;
       $limit = 'LIMIT '.$start.','.$show;
     }

     $res = Dal::query("SELECT * FROM {moderation_queue} WHERE collection_id = ? AND type = ? $limit", array($this->collection_id, $type));
     if ( $cnt ) {
        return $res->numRows();
     }
     $contents = array();
     while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
       $contents[] = $row->item_id;
     }
     Logger::log("Exit: Group::get_moderation_queue()");
     return $contents;
   }

   /**
    * approve content/user in moderated queue
    * @access public
    * @param int id of user/content
    * @param string type ie user/content
    */
   public function approve ($item_id, $type) {
     Logger::log("Enter : Group::approve() | Args: \$item_id = $item_id, \$type = $type");
     //TODO: call join() and post_content() here.
     $this->created = time();
     switch($type) {
       case 'user':
         if(!Group::member_exists($this->collection_id, $item_id)) {
          $res = Dal::query("INSERT INTO {groups_users} (group_id, user_id, user_type, created) VALUES (?, ?, ?, ?)", array($this->collection_id, $item_id, MEMBER, $this->created));
         }
         break;
       case 'content':
         $res = Dal::query("UPDATE {contents} SET collection_id = ? WHERE content_id = ?", array($this->collection_id, $item_id));
         break;
     }
     $res = Dal::query("DELETE FROM {moderation_queue} WHERE collection_id = ? AND item_id = ? and type= ?", array($this->collection_id, $item_id, $type));
     Logger::log("Exit : Group::approve()");
   }

   /**
    * disapprove content/user in moderated queue
    * @access public
    * @param int id of user/content
    * @param string type ie user/content
    */
   public function disapprove ($item_id, $type) {
     Logger::log("Enter : Group::disapprove() | Args: \$item_id = $item_id, \$type = $type");
     $res = Dal::query("DELETE FROM {moderation_queue} WHERE collection_id = ? AND item_id = ? and type= ?", array($this->collection_id, $item_id, $type));
     Content::delete_by_id($item_id);
     Logger::log("Exit : Group::disapprove()");
   }

   /**
    * Join a group
    * @access public
    * @param int $user_id
    */
   public function join ($user_id, $email = NULL,$GInvID = NULL) {
     Logger::log("Enter: Group::join() | Args: \$user_id = $user_id");
     $this->created = time();
     if (!$this->is_active) {
       throw new PAException(OPERATION_NOT_PERMITTED, "Trying to join a deleted group");
     }

     if (Group::member_exists($this->collection_id, $user_id)) {
       throw new PAException(OPERATION_NOT_PERMITTED, "Already a member of this group.");
     }

     switch ($this->reg_type) {
     case $this->REG_MODERATED:
       // moderated group - send moderation request
       $this->moderate_user($user_id);
       break;

     case $this->REG_INVITE:
       // Check whether the user has the invitation to join the group or not.
       if(!Invitation::check_invitation($GInvID, $this->collection_id)) {
	 // uninvited!
	 throw new PAException(GROUP_NOT_INVITED, "You need to have invitation link to join this group.");
       }

       // fall through to default behaviour if we do have an invitation
     default:
       // either it's an open group, or an invite-only one and the
       // user has an invitation, so add the user to the group.
       $res = Dal::query("INSERT INTO {groups_users} (group_id, user_id, user_type, created) VALUES (?, ?, ?, ?)", array($this->collection_id, $user_id, MEMBER, $this->created));

       // assign Group Member Role to new member
       $user = new User();
       $user->load((int)$user_id);
       $role_extra = array('user' => false, 'network' => false, 'groups' => array($this->collection_id));
       $role = array('role_id' => GROUP_MEMBER_ROLE, 'extra' => serialize($role_extra));
       $user->set_user_role(array($role));

       break;
     }
     Logger::log("Exit: Group::join()");
     return TRUE;
   }

   /**
    * Leave a group
    * @access public
    * @param int $user_id
    */
   public function leave ($user_id) {
     Logger::log("Enter: Group::leave() | Args: \$user_id = $user_id");

     $res = Dal::query("DELETE FROM {groups_users} WHERE group_id = ? AND user_id = ? AND user_type = ?", array($this->collection_id, $user_id, MEMBER));
     $role_info = array(array('role_id' => null));        // this means - delete all roles for this group
     Roles::delete_user_roles($user_id, $role_info, $this->collection_id);
     Logger::log("Exit: Group::leave()");
     return TRUE;
   }

   /* Removes a user from ALL groups of which he/she is a member.
    * Used when deleting users.
    */
   public static function leave_all_groups($user_id) {
     Logger::log("Enter: Group::leave_all_groups() | Args: \$user_id = $user_id");

     $res = Dal::query("DELETE FROM {groups_users} WHERE user_id = ?", array($user_id));

     $role_info = array(array('role_id' => null));        // this means - delete all roles for this group
     $user_groups = Group::get_user_groups( $user_id );
     foreach($user_groups as $grp) {
       Roles::delete_user_roles($user_id, $role_info, $grp['gid']);
     }
     Logger::log("Exit: Group::leave_all_groups()");
     return TRUE;
   }

   /**
    * check for existence of a member
    * @access public
    * @param int group id.
    * @param int $user_id
    */
   public static function member_exists ($group_id, $user_id) {
     Logger::log("Enter: Group::member_exists() | Args: \$gid = $group_id, \$user_id = $user_id");
     $res = Dal::query("SELECT * FROM {groups_users} WHERE user_id = ? AND group_id = ?", array($user_id, $group_id));
     $ret = ($res->numRows() ? TRUE : FALSE);
     Logger::log("Exit: Group::member_exists() | Return: ".var_export($ret, TRUE));
     return $ret;
   }

   /**
    * get member list
    * @access public
    */

   public function get_members($cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC',$members_only=FALSE) {
     Logger::log("Exit: Group::get_members()");
     if (!$this->is_active) {
       throw new PAException(OPERATION_NOT_PERMITTED, "Trying to access deleted group");
     }
      $sort_by = trim($sort_by);
      $direction = trim($direction);
      $sort_by = ( $sort_by ) ? $sort_by : 'created';
      $direction = ( $direction ) ? $direction : 'DESC';
      $order_by = 'ORDER BY U.'.$sort_by.' '.$direction;
     if ( $show == 'ALL' || $cnt == TRUE) {
       $limit = '';
     } else {
       $start = ($page -1)* $show;
       $limit = 'LIMIT '.$start.','.$show;
     }
     if ($members_only) {


      $res = Dal::query("SELECT GU.user_id,GU.group_id,GU.user_type, GU.created FROM {groups_users} AS GU,{users} AS U WHERE GU.group_id = ? AND (GU.user_type=?) AND U.user_id = GU.user_id AND U.is_active = 1 $order_by $limit ", array($this->collection_id,MEMBER));
     } else {
       $sql =  "SELECT GU.user_id,GU.group_id,GU.user_type,GU.created FROM {groups_users} AS GU,{users} AS U WHERE GU.group_id = ? and (GU.user_type=? OR GU.user_type=? OR GU.user_type=?) AND U.user_id = GU.user_id AND U.is_active = 1 $order_by $limit ";
       $data_array = array($this->collection_id,MEMBER,OWNER,MODERATOR);
       $res = Dal::query($sql, $data_array);
     }
     if ( $cnt ) {
        return $res->numRows();
     }
     if ($res->numRows()) {
       $members = array();
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $members[] = array('user_id' => $row->user_id, 'user_type' => $row->user_type, 'join_date' => $row->created);
       }
       Logger::log("Exit: Group::get_members()");
       return $members;
     }
     else {
       Logger::log("Exit: Group::get_members()");
       return array();
     }
   }

   /**
    * get total members of the group
    * @access public
    */
   public static function get_member_count($group_id) {
     Logger::log("Exit: Group::get_member_count()");
     $sql = "SELECT count(*) AS cnt  from {groups_users} AS GU INNER JOIN {users} AS U on GU.user_id = U.user_id AND U.is_active = 1 WHERE GU.group_id = ? ";
     $data = array($group_id);
     $res = Dal::query($sql,$data);
     $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
     Logger::log("Exit: Group::get_member_count()");
     return $row->cnt;
   }

   public static function get_members_with_roles($group_id) {
     $sql = "SELECT * FROM {groups_users} AS GU INNER JOIN {users} AS U on GU.user_id = U.user_id AND U.is_active = 1 WHERE GU.group_id = ? ";
     $data = array($group_id);
     $res = Dal::query($sql,$data);
     $users = array();
     while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
     	$u = new User();
     	$u->load((int)$row->user_id);
     	$users[$row->user_id] = $row;
     	$users[$row->user_id]->display_name = $u->display_name;
     	// filter Roles for THIS group
     	foreach ($u->role as $i=>$role) {
     		$extra = unserialize($role->extra);
     		if (is_array($extra['groups'])) { // we are only looking for group relevant roles
     			foreach ($extra['groups'] as $gid) {
     				if ($group_id == $gid) { // applies to THIS group
     					$users[$row->user_id]->roles[] = $role->name;
     					$users[$row->user_id]->role_ids[] = $role->role_id;
     				}
     			}
     		}
     	}
     	// $users[$row->user_id]->user = $u;
     }
     return $users;
   }

  public static function get_moderators($group_id) {
  	$members = Group::get_members_with_roles($group_id);
   	$moderators = array();
   	foreach ($members as $i=>$member) {
   		if (!empty($member->role_ids)) {
   			if (in_array(GROUP_MODERATOR_ROLE, $member->role_ids)) $moderators[] = $member;
   		}
   	}
   	return $moderators;
  }

   /**
    * get all group administrators
    * @access public
    * @param int $group_id
    *
    */
  public static function get_admins($group_id) {
    $members = Group::get_members_with_roles($group_id);
    $admins = array();
    foreach ($members as $i=>$member) {
        if (!empty($member->role_ids)) {
            if (in_array(GROUP_ADMIN_ROLE, $member->role_ids)) $admins[] = $member;
        }
    }
    return $admins;
  }


   /**
    * get all groups
    * @access public
    * @param string search string
    * to do add pagination
    */
   public static function get_all ($search_string = '', $number = 'all', $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC', $type='regular') {
     Logger::log("Enter: Group::get_all() ");

     if ($number == 'all') {
       $limit = '';
     }
     else {
       $limit = 'LIMIT '.$number;
     }

     $order_by = $sort_by.' '.$direction;
     if ( $show == 'ALL' || $cnt == TRUE) {
       $limit = '';
     } else {
       $start = ($page -1)* $show;
       $limit = 'LIMIT '.$start.','.$show;
     }


     if ($search_string == '') {
       $res = Dal::query("SELECT G.*,CC.*,C.name FROM {contentcollections} AS CC, {groups} AS G LEFT JOIN {categories} AS C ON G.category_id = C.category_id 
       WHERE CC.collection_id = G.group_id 
       AND CC.is_active = 1 
       AND G.reg_type <> ? 
       AND group_type = '$type'
       ORDER BY $order_by $limit", array(REG_INVITE));
     }
     else {
       $res = Dal::query("SELECT G.*,CC.*,C.name FROM {contentcollections} AS CC, {groups} AS G LEFT JOIN {categories} AS C ON G.category_id = C.category_id 
       WHERE CC.collection_id = G.group_id 
       AND CC.is_active = 1 
       AND G.reg_type <> ? 
       AND group_type = '$type'
       AND (CC.title like ? OR CC.description like ?) 
       ORDER BY $order_by $limit", array(REG_INVITE, "%".addslashes($search_string)."%", "%".addslashes($search_string)."%"));
     }
     if ( $cnt ) {
        return $res->numRows();
      }

     $groups = array();
     if ($res->numRows()) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $group = array();
         $cnt = Group::get_member_count($row->group_id);
         foreach ($row as $k=>$v) $group[$k] = $v;
         $group['members'] = $cnt;
         $groups[] = $group;
       }
     }
     Logger::log("Exit: Group::get_all() ");
     return $groups;
   }

   /**
    * get largest groups
    * @access public
    * @param string search string
    */
   public static function get_largest_groups ($number = 'all', $type="regular") {
     Logger::log("Enter: Group::get_largest_groups() ");
     if ( $number == 'all' ) {
      $limit = '';
     } else {
      $limit = ' LIMIT '.$number;
     }
     $sql = "SELECT DISTINCT(GU.group_id), COUNT(GU.user_id) as cnt
              FROM {groups_users} as GU LEFT JOIN {groups} as G on GU.group_id = G.group_id 
              WHERE G.reg_type <> ? 
              AND group_type = '$type'
              GROUP BY GU.group_id ORDER BY cnt DESC $limit";
     $data = array(0=>REG_INVITE);// Reg_Type 2 is for Invite Only Groups.
     $res = Dal::query($sql, $data);
     $groups = array();
     if ($res->numRows()) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $group_by_id = Group::load_group_by_id($row->group_id);
         $groups[] = array('group_id' => $row->group_id, 'title' => $group_by_id->title, 'author_id' => $group_by_id->author_id, 'created' => $group_by_id->created, 'members' =>$row->cnt, 'picture'=>$group_by_id->picture);
       }
     }

     Logger::log("Exit: Group::get_largest_groups() ");
     return $groups;
   }
   /**
    * get the information of a single group by id
    * @access public
    * @param string search string
    * to do add pagination
    */
   public static function load_group_by_id($group_id) {
    Logger::log("Enter: Group::load_group_by_id() ");
    $row = null;
    $res = Dal::query("SELECT CC.title,CC.author_id,CC.description,CC.created,CC.picture, G.access_type, G.reg_type, G.header_image,G.header_image_action,G.display_header_image  FROM {groups} AS G, {contentcollections} AS CC WHERE G.group_id=? AND CC.collection_id = G.group_id AND CC.is_active = 1 ",array($group_id));
    if ($res->numRows()) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    }
    Logger::log("Exit: Group::load_group_by_id() ");
    return $row;
   }

   /**
    * check if user is admin of the group
    * @access public
    * @param int $group_id
    * @param int $user_id
    *
    */
   public static function is_admin($group_id, $user_id) {
     Logger::log("Enter: Group::is_admin() | Args: \$group_id = $group_id, \$user_id = $user_id");
     
     if (!$user_id) return false; // how can an annon user be admin ^^

     $is_super_user = false;
     if (defined('SUPER_USER_ID')) {                        // check if super user exists else raise exception
       if (SUPER_USER_ID == $user_id) {
         $is_super_user = true;
       }
     } else {
       throw new PAException(GROUP_PARAMETER_ERROR, "Configuration error: Super user is not defined for mother network.");
     }

     $res = Dal::query("SELECT * FROM {groups_users} WHERE group_id = ? AND user_id = ? AND user_type = ?", array($group_id, $user_id, OWNER));

     if($res->numRows() || $is_super_user) {                           // SUPER USER or GROUP OWNER!
       Logger::log("Exit: Group::is_admin() | Return: TRUE");
       return TRUE;
     }
     else {
        $user = new User();
        $user->load($user_id);
        foreach($user->role as $role) {
          if($role->role_id == GROUP_ADMIN_ROLE) {
            $role_extra = unserialize($role->extra);
            if(!empty($role_extra['groups']) && in_array($group_id, $role_extra['groups'])) {
            Logger::log("Exit: static function Group::is_admin | Return: TRUE");
            return true;
            }
          }
        }
       Logger::log("Exit: Group::is_admin() | Return: FALSE");
       return FALSE;
     }
   }

   /**
    * check if user is moderator of the group
    * @access public
    * @param int $group_id
    * @param int $user_id
    *
    */
   public static function is_moderator($group_id, $user_id) {
     Logger::log("Enter: Group::is_moderator() | Args: \$group_id = $group_id, \$user_id = $user_id");

     $res = Dal::query("SELECT * FROM {groups_users} WHERE group_id = ? AND user_id = ? AND user_type = ?", array($group_id, $user_id, MODERATOR));

     if($res->numRows()) {                           // GROUP MODERATOR
       Logger::log("Exit: Group::is_moderator() | Return: TRUE");
       return TRUE;
     }
     else {
        $user = new User();
        $user->load($user_id);
        foreach($user->role as $role) {
          if($role->role_id == GROUP_MODERATOR_ROLE) {
            $role_extra = unserialize($role->extra);
            if(!empty($role_extra['groups']) && in_array($group_id, $role_extra['groups'])) {
            Logger::log("Exit: static function Group::is_moderator | Return: TRUE");
            return true;
            }
          }
        }
        Logger::log("Exit: Group::is_moderator() | Return: FALSE");
        return FALSE;
     }
   }

  /**
   * get groups of a user
   * @access public
   * @param int $user_id
   */
  public static function get_user_groups ($user_id, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC', $type='private', $group_type=NULL) {
    Logger::log("Enter: Group::get_user_groups() | Args:  \$user_id = $user_id");

    $order_by = $sort_by.' '.$direction;
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }
    
    $type_select = "";
    if ($group_type) {
    	$type_select = "AND G.group_type='$group_type'";
    }

    if ( $type=='public' ) {
      $sql = "SELECT GU.group_id, GU.user_id, GU.user_type, CC.title
              FROM {groups_users} AS GU,
              {contentcollections} AS CC,
              {groups} AS G
              WHERE GU.user_id = ?
                AND  GU.group_id=CC.collection_id
                AND CC.is_active=?
                AND G.group_id = GU.group_id
                AND G.reg_type<> ?
                $type_select
              $limit";

      $data_array = array($user_id,1,REG_INVITE);
      $res = Dal::query($sql,$data_array);

    } else {
      $res = Dal::query("SELECT GU.*, CC.title 
      	FROM {groups_users} AS GU 
      	LEFT JOIN {contentcollections} AS CC 
      		ON GU.group_id=CC.collection_id 
      	LEFT JOIN {groups} AS G 
      		ON G.group_id=CC.collection_id 
      	WHERE user_id = ? 
      	$type_select
      	$limit", array($user_id));
    }
    if ( $cnt ) {
      return $res->numRows();
    }

    $groups = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $groups[] = array('gid' => $row->group_id, 'access' => $row->user_type, 'name' => $row->title);
      }
    }
    Logger::log("Exit: Group::get_user_groups() | Return: ".count($groups)." groups");
    return $groups;
  }

  /**
   * get number of groups in a category
   * @access public
   * @param int $category_id
   */
  public static function get_threads_count_of_category( $category_id ) {
    Logger::log("Enter: Group::get_threads_count_of_category() | Args:  \$category_id = $category_id");
    //$sql = "SELECT count(*) AS cnt FROM {groups} WHERE category_id = ? AND reg_type <> ?";
    $sql = "SELECT count(*) AS cnt FROM {groups} AS G,{contentcollections} AS CC WHERE category_id = ? AND reg_type <> ? AND G.group_id=CC.collection_id AND CC.is_active=?";
    $data = array($category_id, REG_INVITE, 1);
    $res = Dal::query($sql, $data);
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    Logger::log("Exit: Group::get_threads_count_of_category() | Return: ");
    return $row->cnt;
  }

  /**
   * get groups in a given category
   * @access public
   * @param int $category_id
   */
  static function load_groups_for_category($category_id, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC') {
    Logger::log("Enter: function Group::load_groups_for_category");

    $order_by = $sort_by.' '.$direction;
     if ( $show == 'ALL' || $cnt == TRUE) {
       $limit = '';
    } else {
       $start = ($page -1)* $show;
       $limit = 'LIMIT '.$start.','.$show;
    }

    $sql = "SELECT group_id FROM {groups}  AS G,{contentcollections} AS CC WHERE category_id = ? AND reg_type <> ? AND G.group_id=CC.collection_id AND CC.is_active=?";

    $sql .= " $limit";

    $data = array($category_id, REG_INVITE, 1);
    $res = Dal::query($sql, $data);

    if ( $cnt ) {
        return $res->numRows();
    }

    $groups = array();
    if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $groups[] = array('group_id' => $row->group_id);
      }
    }
    Logger::log("Exit: function Group::load_groups_for_category");
    return $groups;
  }

  /**
  * check for item in moderation queue.
  * @access private
  * @param int $item_id
  */

  function item_exists_in_moderation($item_id, $type='user') {
     Logger::log("Enter: Group::item_exists_in_moderation() | Args: \$item_id = $item_id, \$type = $type");
     $res = Dal::query("SELECT * FROM {moderation_queue} WHERE collection_id = ? AND item_id = ? AND type = ?", array($this->collection_id, $item_id, $type));
     if ($res->numRows()) {
       Logger::log("Exit: Group::item_exists_in_moderation() | Return: TRUE");
       return TRUE;
     }
     else {
       Logger::log("Exit: Group::item_exists_in_moderation() | Return: FALSE");
       return FALSE;
     }
  }

  /**
    * access user permissions on a group
    * @access public
    * @param int user_id ID of user trying to perform some operation to group
    * @param constant $access access permission to be checked
    */

  public static function get_user_type ($user_id, $gid) {
     Logger::log("Enter: Group::get_user_type() | Args: \$user_id = $user_id");
     $sql = "SELECT user_type FROM {groups_users} WHERE group_id = ? and user_id = ?";
     $data = array($gid, $user_id);
     $res = Dal::query($sql,$data);
     if ($res->numRows()>0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $user_type = $row->user_type;
     } else {
      $user_type = NOT_A_MEMBER;
     }
    return $user_type;
    Logger::log("Exit: Group::get_user_type() | Args: \$user_id = $user_id");
  }

  public static function set_user_type($user_id, $gid, $type) {
    $sql = "UPDATE {groups_users} SET user_type = ? WHERE group_id = ? and user_id = ?";
    try {
      $res = Dal::query($sql, array($type, $gid, $user_id));
    } catch (Exception $e) {
      echo $e->getMessage();
      Dal::rollback();
      throw $e;
    }
    return true;
  }

  public static function set_group_owner($new_owner_id, $gid) {       // insert new group owner
    $sql = "INSERT INTO {groups_users} (group_id, user_id, user_type) VALUES (?, ?, ?)";
    try {
      $res = Dal::query($sql, array($gid, $new_owner_id, OWNER));
    } catch (Exception $e) {
      echo $e->getMessage();
      Dal::rollback();
      throw $e;
    }
    return true;
  }

  /* create a new group (called from addgroup.php and api_impl.php) */
  static function save_new_group ($ccid, // content collection id
                                  $uid, // user who created the group
                                  $title, // group name
                                  $body, // group description
                                  $picture, // filename / url of a picture
                                  $group_tags, // comma-separated tag list
                                  $group_category, // category id
                                  $access = 0, $reg = 0, $is_mod = 0,
				  $header_image = "", $header_image_action = NULL, $display_header_image = NULL, $extra = NULL) {
    if (empty($title)) {
      throw new PAException(GROUP_PARAMETER_ERROR, "Please fill in the name of the group");
    }

    if ($group_category==0) {
      throw new PAException(GROUP_PARAMETER_ERROR, "Please select the category");
    }

    $tags = Tag::split_tags($group_tags);

    $new = new Group();
    if ($ccid) {
      $new->collection_id = $ccid;
    }
    $new->title = $title;
    $new->description = $body;
    $new->author_id = $uid;
    $new->allow_comments = 1;
    $new->access_type = $access;
    $new->reg_type = $reg;
    $new->is_moderated = $is_mod;
    $new->category_id = $group_category;
    $new->header_image_action=$header_image_action;
    $new->display_header_image = $display_header_image;
    $new->picture = $picture;

    $new->header_image=$header_image;
    $new->extra = $extra;

    //TODO; hard coded integers!
    $new->type = 1;
    $group_id = $new->save($uid);
    Tag::add_tags_to_content_collection($new->collection_id, $tags);

    return $group_id;
  }
     /**
   * get number of groups in the system
   * @access public
   */
  public static function get_total_groups() {
    Logger::log("Enter: Group::get_total_groups() | ");
    $res = Dal::query("SELECT count(*) AS cnt FROM {groups} AS G,{contentcollections} AS CC WHERE   CC. collection_id = G.group_id AND CC.is_active=? AND G.reg_type <> ? ", array(1,REG_INVITE));
    $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    Logger::log("Exit: Group::get_total_groups() | Return: $row->cnt");
    return $row->cnt;
  }
  		/**
     * Return all the categories and the Groups
     * @access Public
    */

    public static function category_group_listing() {
    Logger::log("[Enter: static function Group::category_group_listing]");

    $sql = "SELECT C.name AS category_name, C.category_id, CC.title AS group_name, G.group_id FROM {categories} AS C LEFT JOIN {groups} AS G ON C.category_id = G.category_id AND G.reg_type <> ? LEFT JOIN {contentcollections} AS CC ON G.group_id = CC.collection_id AND CC.is_active = 1 AND CC.type = 1 WHERE C.position RLIKE '^[0-9]+>$' AND C.is_active = 1";

    $res = Dal::query($sql,array(REG_INVITE));

    if( $res->numRows() ) {
      while( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) ) {
        $result[] = $row;
      }
    }
    Logger::log("[Exit: static function Group::category_group_listing] ");
    return $result;
  }

    public static function get_groups_by_user($uid=FALSE, $cnt=FALSE, $show='ALL', $page=1, $sort_by='created', $direction='DESC', $group_type='regular') {
    Logger::log("Enter: Group::get_groups_by_user() ");
     if ( $sort_by =='members') {
         $order_by = 'members'.' '.$direction;
     } elseif (preg_match('/^G\./', $sort_by)) {
       $order_by = ' '.$sort_by.' '.$direction;
     } else {
       $order_by = ' CC.'.$sort_by.' '.$direction;
    }
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    }
    else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }

     if ($uid) {
        $sql = "SELECT count(GU.user_id) AS members,
        CC.collection_id AS group_id,
        CC.title AS group_name, CC.*,
        U.first_name AS owner_first_name,
        U.last_name AS owner_last_name,
        U.login_name AS owner_login_name,
        U.user_id  AS owner_id,
        G.*
        FROM {contentcollections} AS CC
        INNER JOIN {groups_users} AS GU
        ON GU.group_id = CC.collection_id AND CC.is_active =1
        LEFT JOIN {users} AS U
        ON U.user_id = (SELECT user_id FROM {groups_users} WHERE group_id = CC.collection_id AND user_type = 'owner')
        LEFT JOIN {groups} AS G on CC.collection_id = G.group_id
        WHERE GU.user_id = ? 
        AND G.group_type='$group_type'
        GROUP BY CC.collection_id ORDER BY $order_by $limit";
        $res = Dal::query($sql, $uid);

     }

     else {
          $sql = "SELECT count(GU.user_id) AS members,
          CC.collection_id AS group_id, CC.title AS group_name, CC.*,
          U.first_name AS owner_first_name,
          U.last_name AS owner_last_name,
          U.login_name AS owner_login_name,
          U.user_id AS owner_id,
          G.*
          FROM {contentcollections} AS CC
          INNER JOIN {groups_users} AS GU
          ON GU.group_id = CC.collection_id
          AND CC.is_active = 1
          LEFT JOIN {users} AS U
          ON U.user_id = (SELECT user_id FROM {groups_users} WHERE group_id = CC.collection_id AND user_type = 'owner')
          LEFT JOIN {groups} AS G
          ON CC.collection_id = G.group_id
          WHERE G.reg_type <> ? 
          AND G.group_type = '$group_type'
          GROUP BY CC.collection_id ORDER BY $order_by $limit";
          $res = Dal::query($sql, REG_INVITE);
     }

     if( $cnt ) return $res->numRows();
     $group_description = array();
     if ( $res->numRows() ) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $group_description[] = $row;
       }
    }

   Logger::log("Exit: Group::get_groups_by_user() ");
   return $group_description;
   }

  /**
    * use for searching Groups
    * @access public
    */
  public static function get_groups_info_by_search($condition, $cnt=FALSE, $show='ALL', $page=1, $sort_by='created', $direction='DESC', $user_id=NULL, $group_type='regular') {
    Logger::log("Enter: Group::get_groups_info_by_search()");
    $data = array();
    $user_text = '';
    
    $data[] = '%'.$condition['keyword'].'%';
    $name = $condition['name_string'];
    if ($sort_by == 'members' ) {
        $order_by = $sort_by.' '.$direction;
    } elseif (preg_match('/^G\./', $sort_by)) {
       $order_by = ' '.$sort_by.' '.$direction;
		} else {
       $order_by = ' CC.'.$sort_by.' '.$direction;
    }
    if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    }
    else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }
    if($user_id) {
      $user_text = 'AND GU.user_id = ?';
    }
    
    $type_select = "AND G.group_type=$group_type";
    
    $sql= "SELECT count(GU.user_id) AS members,
           CC.collection_id AS group_id,
           CC.title AS group_name,
           CC.*,
           U.first_name AS owner_first_name,
           U.last_name AS owner_last_name,
           U.login_name AS owner_login_name,
           G.*,
           U.user_id AS owner_id 
           FROM {contentcollections} AS CC
           INNER JOIN {groups_users} AS GU
           	ON GU.group_id = CC.collection_id
           	AND CC.is_active =1 $user_text
           LEFT JOIN {users} AS U
           	ON U.user_id = (SELECT user_id FROM {groups_users} WHERE group_id = CC.collection_id AND user_type = 'owner')
           LEFT JOIN {groups} AS G
           	ON CC.collection_id = G.group_id
           WHERE CC.$name LIKE ?
           AND G.reg_type <> ?
           $type_select
           GROUP BY CC.collection_id
           ORDER BY $order_by $limit";
    array_push($data, REG_INVITE);

    if($user_id) {
      array_unshift($data, $user_id);
    }
    $res = Dal::query($sql, $data);
    if ( $cnt ) {
      return $res->numRows();
    }

    $search_result = array();
    if($res->numRows()) {
      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $search_result[] = $row;
      }
    }
    Logger::log("Exit: Group::get_groups_info_by_search()");
    return $search_result;
  }

   /**
    * return user_id of admin of the group
    * @access public
    * @param int $group_id
    *
    *
    * NOTE: This function should be changed to return users with GROUP_ADMIN_ROLE
    */
//
//  NOTE: not used anymore - use Group::get_admins($group_id)
//
//
/*
   public static function get_admin_id($group_id) {
     Logger::log("Enter: Group::get_admin_id() | Args: \$group_id = $group_id");

     $res = Dal::query("SELECT * FROM {groups_users} WHERE group_id = ? AND user_type = ?", array($group_id, OWNER));

     if ($res->numRows()) {
      while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        $groups_admin_id[] = array('user_id' => $row->user_id);
      }
     return $groups_admin_id[0];
    }
    return NULL;
  }
*/
  public static function get_owner_id($group_id) {
     $group_owner_id = null;
     Logger::log("Enter: Group::get_owner_id() | Args: \$group_id = $group_id");
     $res = Dal::query("SELECT * FROM {groups_users} WHERE group_id = ? AND user_type = ?", array($group_id, OWNER));

     if($res->numRows()) {
       $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
       $group_owner_id = $row->user_id;
     }
     Logger::log("Exit: Group::get_owner_id() | Args: \$group_id = $group_id");
     return $group_owner_id;
  }

  /**
  * function used to delete a group or groups.
  * @param $user_id and $group_id
  */

  public function delete_user_groups($user_id, $group_id = NULL) {
    $network_owner_id = (int)PA::$network_info->owner_id;

    //getting user groups

    $all_user_groups = Group::get_user_groups( $user_id );

    if(count( $all_user_groups ) > 0) {

      foreach( $all_user_groups as $group ) {
        $this->collection_id = $group['gid'];
        if( $group['access'] == OWNER ) {                                      // assign new Battalion/UGCGroup owner
          Group::set_user_type($user_id, (int)$group['gid'], MEMBER);          // only MEMBER can leave a group!
          $this->leave($user_id);
          if(Group::member_exists((int)$group['gid'], $network_owner_id)) {      // network owner is already member of group
            Group::set_user_type($network_owner_id, (int)$group['gid'], OWNER);  // transfer ownership to network owner
          } else {
            Group::set_group_owner($network_owner_id, (int)$group['gid']);       // insert new group owner
          }
          $role_id  = GROUP_ADMIN_ROLE;
          $role_extra = array('user' => false, 'network' => false, 'groups' => array((int)$group['gid']));
          $role = array('role_id' => $role_id, 'extra' => serialize($role_extra));
          $net_owner = new User();
          $net_owner->load($network_owner_id);
          $net_owner->set_user_role(array($role));
        }
        else if(($group['access'] == MEMBER) || ($group['access'] == MODERATOR)) {
          if($group['access'] == MODERATOR) {
            Group::set_user_type($user_id, (int)$group['gid'], MEMBER);        // only MEMBER can leave a group!
          }

          //voiding user membership
          $this->leave( $user_id );
        }

      }
    }
  }

  /**
    * get all groups
    * @access public
    * @param string search string
    */
   public static function get_all_groups_for_admin ($cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC') {
     Logger::log("Enter: Group::get_all_groups_for_admin() ");

     $order_by = $sort_by.' '.$direction;
     if ( $show == 'ALL' || $cnt == TRUE) {
       $limit = '';
     } else {
       $start = ($page -1)* $show;
       $limit = 'LIMIT '.$start.','.$show;
     }
     $res = Dal::query("SELECT * FROM {groups} AS G, {contentcollections} AS CC WHERE CC.collection_id = G.group_id AND CC.is_active = 1 ORDER BY created DESC $limit");

     if ( $cnt ) {
        return $res->numRows();
      }

     $groups = array();
     if ($res->numRows()) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $cnt = Group::get_member_count($row->group_id);
         $groups[] = array('group_id' => $row->group_id, 'title' => $row->title, 'author_id' => $row->author_id, 'category_id' => $row->category_id,'created' => $row->created, 'members' => $cnt,'picture'=>$row->picture);
       }
     }
     Logger::log("Exit: Group::get_all_groups_for_admin() ");
     return $groups;
   }

  /**
     function for geting all the content of a Group
  */
  public static function get_all_content_for_collection ($collection_id, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC') {
    Logger::log("Enter: Group::get_all_content_for_collection() ");

     $order_by = $sort_by.' '.$direction;
     if ( $show == 'ALL' || $cnt == TRUE) {
       $limit = '';
     } else {
       $start = ($page -1)* $show;
       $limit = 'LIMIT '.$start.','.$show;
     }

     $order_by =  ' ORDER BY C.'.$sort_by.' '.$direction;

     $sql = 'SELECT C.*,
            CT.name AS content_type,
            U.login_name AS author_name,
            U.picture AS author_picture
            FROM {contents} AS C
            INNER JOIN {content_types} AS CT
            ON CT.type_id = C.type
            INNER JOIN {users} AS U
            ON C.author_id = U.user_id
            WHERE C.collection_id = ?
            AND C.is_active = ? '.$order_by.' '.$limit;
     $data = array($collection_id,ACTIVE);

     $res = Dal::query( $sql, $data );

     if ( $cnt ) {
       return $res->numRows();
     }

     $groups_contents = array();
     if ($res->numRows()) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $groups_contents[] = $row;
       }
     }
    Logger::log("Exit: Group::get_all_content_for_collection() ");
    return $groups_contents;
  }

  /**
    * Purpose : this function saves the group theme and other settings
    * @access public
    * return : this function just saves it into db
  */
   public function save_group_theme() {
     Logger::log("Enter: Group::save_group_theme() ");
     $numargs = func_num_args();
     if (empty($numargs)) {
       return ;
     }
     $arg_list = func_get_args();
     $update_field = $arg_list[0];
     $cnt = count($update_field);
     $i=0;
     $data = array();

     $sql = 'UPDATE {groups} SET ';
     foreach ($update_field as $k=>$v) {
       if($cnt-1 == $i) {
         $sql .= $k.' = ? ';
       }
       else {
         $sql .= $k.' = ? ,';
       }
       $data[$i++] = $v;
     }
     $sql .= 'WHERE group_id = ?';
     $data[$i] = $this->collection_id;

     try {
       $res = Dal::query($sql, $data);
     }
     catch (Exception $e) {
       throw new PAException(REQUIRED_PARAMETERS_MISSING, 'invalid argument for updating group');
     }

     Logger::log("Exit: Group::save_group_theme() ");
   }


   public static function updateGroupExtra($gid, $extra) {
     $sql = 'UPDATE {groups} SET extra = ? WHERE group_id = ?';
     $data = array($extra, $gid);
     Dal::query($sql, $data);
   }

  /**
    * Purpose : this function returns detail of the theme
    * @access public
    * return : group's theme data
  */

   public function get_group_theme_detail() {
     Logger::log("Enter: Group::get_group_theme_detail() ");

     $sql = "SELECT header_image, header_image_action, display_header_image, extra FROM {groups} WHERE group_id = ?";
     $res = Dal::query($sql, array($this->collection_id));

     if ($res->numRows()) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {

         $groups_info = array('group_id' => $this->collection_id, 'header_image' => $row->header_image, 'header_image_action' => $row->header_image_action, 'extra' => $row->extra);
       }
     }

     Logger::log("Exit: Group::get_group_theme_detail() ");
     return $groups_info;
   }

  /**
    For loading group information in the basis of group_id
    Requirement :- take a group id
    Return :- all the information of group as well as group_owner name, ID and number of members in the group

   */

   public static function load_group($group_id = FALSE, $cnt = FALSE, $show = 'ALL', $page = 1, $sort_by = 'created', $direction = 'DESC', $speacial_condition = FALSE) {
    Logger::log("Enter: Group::load_group() ");

     if ($sort_by == 'members') {
       $order_by = 'members'.' '.$direction;
     } elseif (preg_match('/^G\./', $sort_by)) {
       $order_by = ' '.$sort_by.' '.$direction;
     } else {
       $order_by = ' CC.'.$sort_by.' '.$direction;
     }

    if ($show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    }
    else {
      $start = ($page -1)* $show;
      $limit = 'LIMIT '.$start.','.$show;
    }

    if ($group_id) {
        $sql = "SELECT count(GU.user_id) AS members,
        CC.collection_id AS group_id,
        CC.title AS group_name,
        CC.*,
        U.first_name AS owner_first_name,
        U.last_name AS owner_last_name,
        U.login_name AS owner_login_name,
        U.user_id  AS owner_id,
        G.*
        FROM {contentcollections} AS CC
        INNER JOIN {groups} AS G
        ON G.group_id = CC.collection_id
        INNER JOIN {groups_users} AS GU
        ON GU.group_id = CC.collection_id
        AND CC.is_active =1
        LEFT JOIN {users} AS U
        ON U.user_id = (SELECT user_id FROM {groups_users} WHERE group_id = CC.collection_id AND user_type = 'owner')
        WHERE CC.collection_id = ? $speacial_condition
        GROUP BY CC.collection_id ORDER BY $order_by $limit";
        $res = Dal::query($sql, $group_id);

     if($cnt) return $res->numRows();

     $group_description = NULL;
     if ( $res->numRows() ) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $group_description = $row;
       }
     }

     Logger::log("Exit: Group::load_group() ");
     return $group_description;
     }
   }

   /**
   * Method to get the valid enum values for the given field name
   */
   public static function get_allowed_values($field) {
     Logger::log("Enter: Group::get_allowed_values()");
     Logger::log("Exit: Group::get_allowed_values() ");
     return Dal::get_enum_values('groups', $field);
   }


}
?>
