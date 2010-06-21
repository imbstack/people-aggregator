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
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/User/User.php";
require_once "api/Relation/Relation.php";
require_once "api/Logger/Logger.php";
require_once "web/includes/functions/mailing.php";
require_once "api/Group/Group.php";
require_once "api/ContentCollection/ContentCollection.php";

/**
  * constant for invitation status
  */
define("INVITATION_DENIED", 0);
define("INVITATION_PENDING", 1);
define("INVITATION_ACCEPTED", 2);

/**
 * @package Invitation
 * Generic class to handle invitations for PA (for Groups as well as for relation invitation).
 * @author Tekriti Software (http://www.tekritisoftware.com   )
 */
class Invitation {

    /**
     * @var string invitation ID
     * @access public
     */
    public $inv_id;

    /**
     * @var integer PA id of invited user.
     * @access public
     */
    public $inv_user_id;

    /**
     * @var string PA Login name of invited user.
     * @access public
     */
    public $inv_username;

    /**
     * @var string First name of invited user.
     * @access public
     */
    public $inv_user_first_name;

    /**
     * @var string last name of invited user.
     * @access public
     */
    public $inv_user_last_name;

    /**
     * @var string PA Email of invited user.
     * @access public
     */
    public $inv_email;

    /**
     * @var string brief text for the subject line of email messsage.
     * @access public
     */
    public $inv_summary;

    /**
     * @var string message to be included in invitation email
     */
    public $inv_message;

    /**
     * @var integer user id of the user who sent this invitation.
     * @access public
     */
    public $user_id;

    /**
     * @var string PA login name the user who sent this invitation.
     * @access public
     */
    public $username;

    /**
     * @var integer type of invitation.
     * @access public
     */
    public $inv_collection_id;

    /**
     * @var integer status of an ID
     */
    public $inv_status = INVITATION_PENDING;

    /**
     * @var string link to register page.
     * @access public
     */
    public $register_url;

    /**
     * @var sting link to page that handles accepts.
     * @access public
     */
    public $accept_url;

    /**
     * @var sting link to page that handles denys.
     * @access public
     */
    public $deny_url;

    /**
     * @var array other specific data to be stored for invitations.
     * @access public
     */
    public $inv_data = array();

    /**
    * @var string the name of group for which invitation is send
    * @access public
    */
    public $inv_group_name;

    /**
     * constructor
     */
    public function __construct() {
        Logger::log("Enter: Invitation::__construct");
        $this->inv_id = md5(uniqid(rand()));
        Logger::log("Exit: Invitation::__construct");
    }

    /**
     * load an Invitation object
     * @access public
     * @param int $inv_id invit ID
     */
    public static function load($inv_id) {
        Logger::log("Enter: Invitation::load() | Args: $inv_id = $inv_id");
        $res = Dal::query("SELECT * FROM {invitations} WHERE inv_id = ?", array($inv_id));
        if($res->numRows() <= 0) {
            Logger::log(" Throwing exception INVITE_NOT_FOUND | Message: Invite not found.", LOGGER_ERROR);
            throw new PAException(INVITE_NOT_FOUND, "Invite not found");
        }
        else {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            $inv = new Invitation();
            $inv = unserialize($row->inv_data);
        }
        Logger::log("Exit: Invitation::load() ");
        return $inv;
    }

    /**
     * send invitaion through an email, If user send invitation to other user for being in his relation use $inv_collection_id = -1
     * @access public
     */
    public function send() {
        Logger::log("Enter: Invitaion::send()");
        $err = FALSE;
        // If user send invitation to other user for being in his relation use $inv_collection_id = -1
        if(!$this->inv_collection_id) {
            $this->inv_collection_id =-1;
        }
        // doing validation
        if(!($this->inv_username or $this->inv_email)) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, '$inv_username and $inv_email not set. One of these needs to be set.');
        }
        if($this->inv_username and $this->inv_email) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, '$inv_username and $inv_email both set. Only one of the two should be set.');
        }
        if(!$this->user_id) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, '$user_id not set');
        }
        if(!$this->inv_collection_id) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, '$inv_collection_id not set.');
        }
        if(!$this->register_url) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, '$register_url not set.');
        }
        if(!$this->accept_url) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, '$accept_url not set.');
        }
        $user = new User();
        $inv_user = new User();
        $user->load($this->user_id);
        if($this->inv_username) {
            $inv_user->load($this->inv_username);
        }
        else {
            unset($inv_user);
        }
        $serialized_data = serialize($this);
        $res = Dal::query("INSERT INTO {invitations} (inv_id, user_id, inv_user_id, inv_user_email, inv_collection_id, inv_status, inv_data) VALUES (?, ?, ?, ?, ?, ?, ?)", array((string) $this->inv_id, (int) $this->user_id, (int) $this->inv_user_id, (string) $this->inv_email, (int) $this->inv_collection_id, (int) $this->inv_status, (string) $serialized_data));
        Logger::log("Exit: Invitaion::send()");
    }

    /**
     * accept the invitation
     * @access public
     * @param int $inv_id invitation ID
     */
    public function accept() {
        Logger::log("Enter: Invitation::accept()");
        $is_valid = Invitation::validate_invitation_id($this->inv_id);
        if($is_valid == FALSE) {
            Logger::log("Throwing exception INVALID_ID ", LOGGER_ERROR);
            throw new PAException(INVALID_ID, "You cannot reuse the Invitation link.");
        }
        else {
            $res = Dal::query("UPDATE {invitations} SET inv_status = ?, inv_user_id = ? WHERE inv_id = ?", array(INVITATION_ACCEPTED, $this->inv_user_id, $this->inv_id));
            $res = Dal::query("SELECT user_id, inv_collection_id, inv_user_id FROM {invitations} WHERE inv_id=?", array($this->inv_id));
            if($res->numRows()) {
                $row           = $res->fetchRow(DB_FETCHMODE_OBJECT);
                $this->user_id = $row->user_id;
                $u             = new User();
                $u->load((int) $row->user_id);
                $user = new User();
                $user->load((int) $this->inv_user_id);
                if($row->inv_collection_id ==-1) {
                    // add as a friend in invitation sender user list
                    Relation::add_relation($u->user_id, $user->user_id, 2, PA::$network_info->address, PA::$network_info->network_id);
                    // add as a friend and send mail
                    Relation::add_relation($user->user_id, $u->user_id, 2, PA::$network_info->address, PA::$network_info->network_id);
                }
            }
        }
        Logger::log("Exit: Invitation::accept()");
    }

    /**
     * deny the invitation
     * @access public
     * @param int $inv_id invitation ID
     */
    public function deny() {
        Logger::log("Enter: Invitation::deny() | Args:  $inv_id = $inv_id");
        $res = Dal::query("SELECT user_id FROM {invitations} WHERE inv_id = ?", array($this->inv_id));
        if($res->numRows()) {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            $u = new User();
            $u->load((int) $row->user_id);
            $user = new User();
            $user->load((int) $this->inv_user_id);
            // data for passing in common mail method
            $array_of_data = array(
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'user_name'  => $user->login_name,
                'user_id'    => $user->user_id,
                'group_name' => $this->inv_group_name,
            );
        }
        $res = Dal::query("UPDATE {invitations} SET inv_status= ? WHERE inv_id = ?", array(INVITATION_DENIED, $this->inv_id));
        if($this->inv_collection_id ==-1) {
            $mail_type = "invite_deny_pa";
        }
        else {
            $mail_type = "invite_deny_group";
        }
        // calling common mailing method
        $check = pa_mail($u->email, $mail_type, $array_of_data);
        Logger::log("Exit: Invitation::deny()");
    }

    /**
     * get all invites for a collection/user (user if $inv_collection_id = -1 ).
     * @access public
     * @param int $collection_id
     */
    public static function get_all($user_id, $collection_id =-1) {
        Logger::log("Enter: Invitaion::get_all() | Args: $collection_id = $collection_id");
        if($collection_id ==-1) {
            $res = Dal::query("SELECT inv_id, inv_status FROM {invitations} WHERE user_id = ? AND inv_collection_id = ?", array($user_id,-1));
        }
        else {
            $res = Dal::query("SELECT inv_id, inv_status FROM {invitations} WHERE inv_collection_id = ?", array($collection_id));
        }
        $invites = array();
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $invites[] = array(
                'id' => $row->inv_id,
                'status' => $row->inv_status,
            );
        }
        Logger::log("Exit: Invitaion::get_all()");
        return $invites;
    }

    /**
     * get all pending invites for a user
     * @access public
     * @param int $user_id
     */
    public static function get_pending_invitations($user_id, $collection_id =-1, $collection_id_array = NULL) {
        Logger::log("Enter: Invitation::get_pending_invitations() | Args: $user_id = $user_id");
        if($collection_id_array && count($collection_id_array) > 0) {
            $res = Dal::query("SELECT count(*) as invitations, inv_id, inv_user_email FROM {invitations} WHERE  user_id = ? AND inv_status = ? AND  inv_collection_id IN (".implode($collection_id_array, ',').") GROUP BY inv_user_email", array($user_id, INVITATION_PENDING));
        }
        else {
            $res = Dal::query("SELECT count(*) as invitations, inv_id, inv_user_email FROM {invitations} WHERE inv_collection_id = ? AND user_id = ? AND inv_status = ? GROUP BY inv_user_email", array($collection_id, $user_id, INVITATION_PENDING));
        }
        $invites = array();
        $i = 0;
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $invites[$i]['inv_id']         = $row->inv_id;
            $invites[$i]['inv_user_email'] = $row->inv_user_email;
            $invites[$i]['invitations']    = $row->invitations;
            $i++;
        }
        Logger::log("Exit: Invitaion::get_pending_invitations()");
        return $invites;
    }

    /**
     * get all accepted invites for a user
     * @access public
     * @param int $user_id
     */
    public static function get_accepted_invitations($user_id, $collection_id =-1, $collection_id_array = NULL) {
        Logger::log("Enter: Invitation::get_accepted_invitations() | Args: $user_id = $user_id");
        if($collection_id_array && count($collection_id_array) > 0) {
            $res = Dal::query("SELECT I.inv_id, I.inv_user_email, I.inv_user_id FROM {invitations} AS I LEFT JOIN {users} AS U ON I.inv_user_id = U.user_id WHERE  I.user_id = ? AND I.inv_status = ? AND U.is_active = ? AND  I.inv_collection_id IN (".implode($collection_id_array, ',').") GROUP BY inv_user_email", array($user_id, INVITATION_ACCEPTED, ACTIVE));
        }
        else {
            $res = Dal::query("SELECT I.inv_id, I.inv_user_email, I.inv_user_id FROM {invitations} AS I LEFT JOIN {users} AS U ON 
        I.inv_user_id = U.user_id WHERE I.inv_collection_id = ? AND I.user_id = ? AND I.inv_status = ? AND U.is_active = ?", array($collection_id, $user_id, INVITATION_ACCEPTED, ACTIVE));
        }
        $invites = array();
        $i = 0;
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $invites[$i]['inv_id']         = $row->inv_id;
            $invites[$i]['inv_user_id']    = $row->inv_user_id;
            $invites[$i]['inv_user_email'] = $row->inv_user_email;
            $i++;
        }
        Logger::log("Exit: Invitaion::get_accepted_invitations()");
        return $invites;
    }

    /**
     * get pending invitations for specified email address + uid
     * @access public
     * @param varchar @email
     */
    public static function get_pending_invitations_for_user_by_email($email_id, $uid) {
        Logger::log("Enter: Invitation::get_pending_invitations_for_email_id() | Args: $email_id = $email_id");
        $res     = Dal::query("SELECT I.inv_id, I.inv_user_email, I.inv_collection_id, CC.title,U.user_id, U.first_name, U.last_name FROM {invitations} as I LEFT JOIN {contentcollections} as CC ON I.inv_collection_id = CC.collection_id INNER JOIN {users} as U on CC.author_id = U.user_id WHERE I.inv_collection_id <> -1 AND I.inv_user_email = ? AND I.inv_status = ?", array($email_id, INVITATION_PENDING));
        $invites = array();
        $i       = 0;
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            if(Group::member_exists((int) $row->inv_collection_id, (int) $uid)) {
                continue;
            }
            $invites[$i]['inv_id']            = $row->inv_id;
            $invites[$i]['inv_user_email']    = $row->inv_user_email;
            $invites[$i]['collection_id']     = $row->inv_collection_id;
            $invites[$i]['collection_name']   = $row->title;
            $invites[$i]['author_first_name'] = $row->first_name;
            $invites[$i]['author_last_name']  = $row->last_name;
            $i++;
        }
        Logger::log("Exit: Invitation::get_pending_invitations_for_email_id()");
        return $invites;
    }

    /**
     * get pending invitations for specified email address
     * @access public
     * @param varchar @email
     */
    public static function check_invitation($GInvID, $collection_id) {
        Logger::log("Enter: Invitation::check_invitation() | Args: $email_id = $email_id");
        $res = Dal::query("SELECT * FROM {invitations} WHERE inv_id = ? AND
inv_collection_id = ? ", array($GInvID, $collection_id));
        if($res->numRows() > 0) {
            return TRUE;
        }
        else {
            return FALSE;
        }
        Logger::log("Exit: Invitation::check_invitation()");
    }

    /**
    * Check whether invitation id is already used or not
    */
    public static function validate_invitation_id($invitation_id) {
        Logger::log("Enter: Invitation::validate_invitation_id() | Args: $invitation_id = $invitation_id");
        // checking status of invitation
        $sql = "SELECT * FROM {invitations} WHERE inv_id = ? AND inv_status = ?";
        $data = array(
            $invitation_id,
            INVITATION_PENDING,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            return TRUE;
        }
        else {
            return FALSE;
        }
        Logger::log("Exit: Invitation::validate_invitation_id() | Args: $invitation_id = $invitation_id");
    }

    public static function validate_group_invitation_id($invitation_id) {
        Logger::log("Enter: Invitation::validate_invitation_id() | Args: $invitation_id = $invitation_id");
        $valid = Invitation::validate_invitation_id($invitation_id);
        if(!$valid) {
            return false;
        }
        //get collection_id
        $Ginv = Invitation::load($invitation_id);
        //find if group is deleted or not
        $g = Group::load_group_by_id($Ginv->inv_collection_id);
        if(!$g) {
            return false;
        }
        //if we have come this far it means invitaion id is valid and  group is active
        Logger::log("Exit: Invitation::validate_invitation_id() | Args: $invitation_id = $invitation_id");
        return true;
    }

    /**
    * function to delete all the invitations for a collection.
    */
    public static function delete_invitations($collection_id) {
        Logger::log("Enter: Invitation::delete_invitations()");
        $sql = 'DELETE FROM {invitations} WHERE inv_collection_id = ?';
        $data[] = $collection_id;
        if(!$res = Dal::query($sql, $data)) {
            Logger::log("Throwing exception in function Group::delete_invitations while deleting invitations");
            throw new PAException(DELETION_FAILED, "Deletion failed while deleting invitations.");
        }
        Logger::log("Exit: Invitation::delete_invitations()");
        return;
    }
}
?>