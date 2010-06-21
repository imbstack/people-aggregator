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
include_once dirname(__FILE__)."/../../config.inc";
require_once "api/DB/Dal/Dal.php";
require_once "api/User/User.php";
require_once "api/PAException/PAException.php";
require_once "api/api_constants.php";

/**
* Class MessageBoard deals with message board system
* @package MessageBoard
* @author Tekriti Software
*/
class MessageBoard {

    /**
      * user_id is author.
      * @access public
      * @var int
      */
    public $user_id = null;

    /**
     * parent_id defines parent of the message
     * @access public
     * @var int
     */
    public $parent_id = 0;

    /**
     * parent_type defines type of parent
     * @access public
     * @var string
     */
    public $parent_type;

    /**
     * boardmessage_id uniquely defines the message.
     * @access public
     * @var int
     */
    public $boardmessage_id;

    /**
     * title of message.
     * @access public
     * @var string
     */
    public $title;

    /**
     * body of the message.
     * @access public
     * @var string
     */
    public $body;

    /**
     * user_name is name of the author.
     * @access public
     * @var string
     */
    public $user_name;

    /**
     * email is email of the author.
     * @access public
     * @var string
     */
    public $email;

    /**
     * homepage is homepage of the author.
     * @access public
     * @var string
     */
    public $homepage;

    /**
    * allow_anonymous is .
    * @access public
    * @var boolean 0,1
    */
    public $allow_anonymous = FALSE;

    /**
    * The default constructor for message class.
    */
    public function __construct() {
        //Following attributes are required for making the anonymous user to post comments or replies to forum messages. In the system anonymous user can not post comments, hence initiliazing in the constructor to default values.
        $this->user_name       = NULL;
        $this->email           = NULL;
        $this->allow_anonymous = FALSE;
        return;
    }

    /**
    * Saves thread if category_id is set
    * Saves reply if parent_message_id is set
    * Makes association of category-thread / message-reply
    * @access public
    * @param set_category_id(category_id),set_parent_message_id(parent_message_id) 
    */
    function save($uid = NULL, $is_insert = 1) {
        Logger::log("Enter: function MessageBoard::save");
        // validate $this->user_id
        if($this->user_id < 1 && $this->user_id !== NULL) {
            throw new PAException(INVALID_ID, "MessageBoard::user_id variable must be either NULL or a valid user ID when posting.");
        }
        //exception if anything go wrong
        // anonymous user cant start a new thread
        if($is_insert == 1) {
            if($this->parent_type != PARENT_TYPE_MESSAGE) {
                if(!$this->user_id || $this->user_id == '-1') {
                    Logger::log(" Throwing exception USER_NOT_LOGGED_IN | Message: $res->getMessage()", LOGGER_ERROR);
                    throw new PAException(USER_NOT_LOGGED_IN, "Anonymous User cant start a thread");
                }
            }
            else {
                // anonymous user can reply only if allowed
                $check = MessageBoard::check_annonymous_allowed($this->parent_id);
                if(!$check && !$uid) {
                    Logger::log(" Throwing exception USER_NOT_LOGGED_IN | Message: $res->getMessage()", LOGGER_ERROR);
                    throw new PAException(USER_NOT_LOGGED_IN, "Anonymous user cant post a comment");
                }
            }
            //exception if anything go wrong EOF
            $this->created = time();
            $this->changed = $this->created;
            if($this->user_id == '') {
                $this->user_id = ANONYMOUS_USER_ID;
            }
            $sql = " INSERT into {boardmessages} ( title, body, created, changed, user_id, allow_anonymous, user_name, email, homepage, parent_id, parent_type) values ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
            $data = array(
                $this->title,
                $this->body,
                $this->created,
                $this->changed,
                $this->user_id,
                $this->allow_anonymous,
                $this->user_name,
                $this->email,
                $this->homepage,
                $this->parent_id,
                $this->parent_type,
            );
            Dal::query($sql, $data);
            $insert_id = Dal::insert_id();
        }
        else {
            $this->changed = time();
            $sql = " UPDATE {boardmessages} set title =? , body =? ,changed=? , allow_anonymous = ? WHERE  boardmessage_id=?";
            $data = array(
                $this->title,
                $this->body,
                $this->changed,
                $this->allow_anonymous,
                $this->boardmessage_id,
            );
            Dal::query($sql, $data);
            $insert_id = $this->boardmessage_id;
        }
        return $insert_id;
        Logger::log("Exit: function MessageBoard::save");
    }

    /**
    * checks whether a given message allows anonymous user's reply
    * @access private - called in save()
    * @param id of message
    * @return TRUE/FALSE 
    */
    public static function check_annonymous_allowed($id) {
        Logger::log("Enter: function MessageBoard::check_annonymous_allowed");
        $sql = "SELECT allow_anonymous FROM {boardmessages} WHERE boardmessage_id = ?";
        $data = array(
            $id,
        );
        $res             = Dal::query($sql, $data);
        $row             = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $allow_anonymous = $row->allow_anonymous;
        if($allow_anonymous == ALLOW_ANONYMOUS) {
            return TRUE;
        }
        else {
            return FALSE;
        }
        Logger::log("Exit: function MessageBoard::check_annonymous_allowed");
    }

    /**
    * delete a message and its replies
    * @access public
    * @param id of message
    */
    static

    function delete($id) {
        Logger::log("Enter: function MessageBoard::delete");
        //delete from boardmessages
        $sql = " DELETE FROM {boardmessages} where boardmessage_id = ? ";
        $data = array(
            $id,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: function MessageBoard::delete");
    }

    /**
    * deletes all forums topics and replies in a parent
    * @access public static
    * @param id of message
    */
    static

    function delete_all_in_parent($parent_id, $parent_type) {
        Logger::log("Enter: function MessageBoard::delete_all_in_parent");
        if($parent_type == PARENT_TYPE_MESSAGE) {
            $sql = " DELETE FROM {boardmessages} WHERE parent_id = ? AND parent_type=? ";
            $data = array(
                $parent_id,
                $parent_type,
            );
            Dal::query($sql, $data);
            MessageBoard::delete($parent_id);
            return;
        }
        else {
            $sql = "SELECT  boardmessage_id FROM {boardmessages} WHERE parent_id = ? AND parent_type=? ";
            $data = array(
                $parent_id,
                $parent_type,
            );
            $res = Dal::query($sql, $data);
            if($res->numRows() > 0) {
                while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                    $pid = $row->boardmessage_id;
                    MessageBoard::delete_replies($pid);
                    MessageBoard::delete($pid);
                }
            }
            return;
        }
        Logger::log("Exit: function MessageBoard::delete_all_in_parent");
    }

    /**
    * deletes all forums replies
    * @access public static
    * @param id of message
    */
    static

    function delete_replies($parent_id) {
        Logger::log("Enter: function MessageBoard::delete_replies");
        $sql = " DELETE FROM {boardmessages} WHERE parent_id = ? AND parent_type=? ";
        $data = array(
            $parent_id,
            PARENT_TYPE_MESSAGE,
        );
        Dal::query($sql, $data);
        Logger::log("Exit: function MessageBoard::delete_replies");
        return;
    }

    /**
  * gets all threads in category if category_id is set by method set_category_id()
  * gets all replies of message if parent_message_id is set by method set_parent_message_id()
  * @access public
  * @param set_category_id(category_id),set_parent_message_id(parent_message_id)
  */
    public function get($count = FALSE, $show = 'ALL', $page = 0, $sort_by = 'changed', $direction = 'DESC') {
        Logger::log("Enter: function MessageBoard::get_threads_of_category");
        //find count
        if($count) {
            $sql = "SELECT count(*) AS CNT FROM {boardmessages} WHERE parent_id = ? AND parent_type = ?";
            $data = array(
                $this->parent_id,
                $this->parent_type,
            );
            $res = Dal::query($sql, $data);
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            return $row->CNT;
        }
        //find count EOF
        $order_by = $sort_by.' '.$direction;
        if($show == 'ALL') {
            $limit = '';
        }
        elseif(!empty($show)) {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql = " SELECT * FROM {boardmessages} WHERE parent_id = ? AND parent_type = ? ORDER BY $order_by $limit ";
        $data = array(
            $this->parent_id,
            $this->parent_type,
        );
        $res = Dal::query($sql, $data);
        $arr_boardmessges = array();
        if($this->parent_type == PARENT_TYPE_MESSAGE) {
            $arr_boardmessges[0] = $this->get_by_id($this->parent_id);
        }
        if($res->numRows() > 0) {
            $i = 1;
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $user_value = User::user_exist((int) $row->user_id);
                if($user_value) {
                    $total_replies = $this->get_children_count($row->boardmessage_id, PARENT_TYPE_MESSAGE);
                    $arr_boardmessges[$i] = array(
                        'boardmessage_id' => $row->boardmessage_id,
                        'title'           => $row->title,
                        'body'            => $row->body,
                        'created'         => $row->created,
                        'total_replies'   => $total_replies,
                        'user_id'         => $row->user_id,
                        'user_name'       => $row->user_name,
                        'email'           => $row->email,
                        'allow_anonymous' => $row->allow_anonymous,
                        'homepage'        => $row->homepage,
                        'user_picture'    => '',
                    );
                    if($row->user_id !=-1 AND $row->user_id != '') {
                        $user = new User();
                        $user->load((int) $row->user_id);
                        $arr_boardmessges[$i]['user_name']    = $user->login_name;
                        $arr_boardmessges[$i]['email']        = $user->email;
                        $arr_boardmessges[$i]['user_picture'] = $user->picture;
                    }
                    $i++;
                }
            }
        }
        return $arr_boardmessges;
        Logger::log("Exit: function MessageBoard::get_threads_of_category");
    }

    public function get_by_id($id) {
        Logger::log("Enter: function MessageBoard::get_threads_of_category");
        $sql = " SELECT * FROM {boardmessages} WHERE  boardmessage_id = ?  ";
        $data = array(
            $id,
        );
        $res = Dal::query($sql, $data);
        $arr_boardmessges = array();
        if($res->numRows() > 0) {
            $i             = 1;
            $row           = $res->fetchRow(DB_FETCHMODE_OBJECT);
            $total_replies = $this->get_children_count($row->boardmessage_id, PARENT_TYPE_MESSAGE);
            $arr_boardmessges = array(
                'boardmessage_id' => $row->boardmessage_id,
                'title'           => $row->title,
                'body'            => $row->body,
                'created'         => $row->created,
                'total_replies'   => $total_replies,
                'user_id'         => $row->user_id,
                'user_name'       => $row->user_name,
                'email'           => $row->email,
                'allow_anonymous' => $row->allow_anonymous,
                'homepage'        => $row->homepage,
                'user_picture'    => '',
            );
            if($row->user_id !=-1 AND $row->user_id != '') {
                $user = new User();
                $user->load((int) $row->user_id);
                $arr_boardmessges['user_name']    = $user->login_name;
                $arr_boardmessges['email']        = $user->email;
                $arr_boardmessges['user_picture'] = $user->picture;
            }
        }
        return $arr_boardmessges;
        Logger::log("Exit: function MessageBoard::get_threads_of_category");
    }

    /**
    * sets parent information 
    * @access public
    * @param id of parent, type of parent
    */
    public function set_parent($id, $type) {
        $this->parent_id = $id;
        $this->parent_type = $type;
    }

    /**
     * gets number of replies to message id 
     * @access public
     * @param id of message
     * @return total number of replies
     */
    public static function get_children_count($id, $type) {
        Logger::log("Enter: function MessageBoard::get_reply_count");
        $sql = "SELECT count(*) AS cnt FROM {boardmessages} WHERE  parent_id = ? AND parent_type=?";
        $data = array(
            $id,
            $type,
        );
        $res = Dal::query($sql, $data);
        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $cnt = $row->cnt;
        Logger::log("Exit: function MessageBoard::get_reply_count");
        return $cnt;
    }

    /**
  * gets details of last post
  * @access public
  * @param id of message
  * @return array containing user name and date of last post
  */

    /*  public static function get_last_post($id) {
      Logger::log("Enter: function MessageBoard::get_last_post");
       
      $sql  = "SELECT user_id,created FROM {boardmessages} WHERE  parent_messageid = ? LIMIT 1";
      $data = array($id);
      $res  = Dal::query($sql, $data);

      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $last_post['last_post_created'] = $row->created;
      if ($row->user_id != -1 AND $row->user_id != '') {
        $user = new User();
        $user->load((int)$row->user_id);
        $last_post['last_post_user_name'] = $user->login_name;
        $last_post['last_post_user_id'] = $row->user_id;
      } else {
        $last_post['last_post_user_name'] = 'Anonymous';
      }    
      return $last_post;
      Logger::log("Exit: function MessageBoard::get_last_post");
    }*/
    /**
    * function to get the forums on different conditons like group_id, user_id
    */
    public static function get_forums($params) {
        Logger::log("Enter: function MessageBoard::get_forums");
        $sql = 'SELECT * FROM {boardmessages}';
        $data = array();
        if(count($params) > 0) {
            $where = ' WHERE 1';
            foreach($params as $field => $value) {
                $where .= ' AND '.$field.' = ?';
                $data[] = $value;
            }
            $sql .= $where;
        }
        if(!$res = Dal::query($sql, $data)) {
            Logger::log("Image::delete_user_audios function failed");
            throw new PAException(AUDIO_DELETE_FAILED, "User audios delete failed");
        }
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $forums[] = $row;
        }
        Logger::log("Exit: function MessageBoard::get_forums");
        return $forums;
    }

    /**
    * function used to delete all the forum and replies given by a user.
    * if group_id is given then the forums of that particular group will be deleted
    * otherwise all forums related to that in all the groups will be deleted
    */
    public function delete_user_forums() {
        Logger::log("Enter: function MessageBoard::delete_user_forums");
        //$forums = MessageBoard::get_forums( $params );
        // delete replies posted by user
        Dal::query("DELETE FROM {boardmessages} WHERE user_id = $this->user_id AND parent_type = '".PARENT_TYPE_MESSAGE."'");
        //find threads
        //now we have only threads left
        $sql = "SELECT  boardmessage_id FROM {boardmessages} WHERE user_id = $this->user_id ";
        $res = Dal::query($sql);
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $pid = $row->boardmessage_id;
                MessageBoard::delete_replies($pid);
                //delete replies
                MessageBoard::delete($pid);
                //delete the board message itself
            }
        }
        Logger::log("Exit: function MessageBoard::delete_user_forums");
    }

    public function get_all_network_forum($cnt = FALSE, $show = 'ALL', $page = 1, $sort_by = 'created', $direction = 'DESC') {
        Logger::log("Enter: function MessageBoard::get_all_network_forum()");
        if($sort_by) {
            $sort_by = 'B.'.$sort_by;
        }
        $order_by = $sort_by.' '.$direction;
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql = "SELECT  B.title AS forum_title,CC.title AS group_name,B.*,CC.*,B.user_name AS author_name FROM {boardmessages} AS B INNER JOIN {contentcollections} AS CC ON B.parent_id = CC.collection_id WHERE B.parent_type = 'collection' ORDER BY $order_by  $limit";
        $res = Dal::query($sql);
        if($cnt) {
            return $res->numRows();
        }
        $forum_details = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $forum_details[] = $row;
            }
        }
        Logger::log("Exit: function MessageBoard::get_all_network_forum()");
        return $forum_details;
    }
}
?>