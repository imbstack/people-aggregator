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

require_once "api/DB/Dal/Dal.php";
require_once "api/User/User.php";
require_once "api/Forum/PaForum.class.php";

class PaForumThread {
    const _public  = 1;
    const _private = 2;
    const _hidden  = 4;
    const _locked  = 8;
    const _sticky  = 16;

    /**
     * Name: id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $id;

    /**
     * Name: related_content_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $related_content_id;

    /**
     * Name: title
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $title;

    /**
     * Name: content
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $content;

    /**
     * Name: status
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $status;

    /**
     * Name: forum_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $forum_id;

    /**
     * Name: user_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $user_id;

    /**
     * Name: viewed
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $viewed;

    /**
     * Name: is_active
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $is_active;

    /**
     * Name: created_at
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $created_at;

    /**
     * Name: updated_at
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $updated_at;

    /**
     * Name: modified_by
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $modified_by = null;

    /**
     * Name: page_size
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $page_size = 20;

    /**
     * Name: current_page
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $current_page;

    /**
     * Name: conditional_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $conditional_steatment;

    /**
     * Name: order_by_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $order_by_steatment;

    /**
     * Name: sort_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $sort_steatment;

    /**
     * Name: fetch_mode
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     **/
    protected $fetch_mode = DB_FETCHMODE_OBJECT;

    /**
     * parent forum object
     **/
    public $forum;

    /**
     * parent board object
     **/
    public $board;
    //--------------- GET METHODS ----------------------------- //
    /**
     * Get value for field: id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result id
     **/
    public function get_id() {
        // returns the value of id
        return $this->id;
    }

    /**
     * Get value for field: related_content_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result related_content_id
     **/
    public function get_related_content_id() {
        // returns the value of related_content_id
        return $this->related_content_id;
    }

    /**
     * Get value for field: title
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result title
     **/
    public function get_title($limit = null) {
        // returns the value of title
        if(!$limit) {
            return $this->title;
        }
        else {
            $title = (strlen($this->title) <= $limit) ? $this->title : substr($this->title, 0, $limit-3).'...';
            return $title;
        }
    }

    /**
     * Get value for field: content
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result content
     **/
    public function get_content() {
        // returns the value of content
        return $this->content;
    }

    /**
     * Get value for field: status
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result status
     **/
    public function get_status() {
        // returns the value of status
        $this->status = $this->getThreadStatus();
        return $this->status;
    }

    /**
     * Get value for field: forum_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result forum_id
     **/
    public function get_forum_id() {
        // returns the value of forum_id
        return $this->forum_id;
    }

    /**
     * Get value for field: user_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result user_id
     **/
    public function get_user_id() {
        // returns the value of user_id
        return $this->user_id;
    }

    /**
     * Get value for field: viewed
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result viewed
     **/
    public function get_viewed() {
        // returns the value of viewed
        return $this->viewed;
    }

    /**
     * Get value for field: is_active
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result is_active
     **/
    public function get_is_active() {
        // returns the value of is_active
        return $this->is_active;
    }

    /**
     * Get value for field: created_at
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result created_at
     **/
    public function get_created_at() {
        // returns the value of created_at
        return $this->created_at;
    }

    /**
     * Get value for field: updated_at
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result updated_at
     **/
    public function get_updated_at() {
        // returns the value of updated_at
        return $this->updated_at;
    }

    /**
     * Get value for field: modified_by
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result modified_by
     **/
    public function get_modified_by() {
        // returns the value of modified_by
        return $this->modified_by;
    }

    /**
     * Get value for field: page_size
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result page_size
     **/
    public function get_page_size() {
        // returns the value of page_size
        return $this->page_size;
    }

    /**
     * Get value for field: current_page
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result current_page
     **/
    public function get_current_page() {
        // returns the value of current_page
        return $this->current_page;
    }

    /**
     * Get value for field: conditional_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result conditional_steatment
     **/
    public function get_conditional_steatment() {
        // returns the value of conditional_steatment
        return $this->conditional_steatment;
    }

    /**
     * Get value for field: order_by_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result order_by_steatment
     **/
    public function get_order_by_steatment() {
        // returns the value of order_by_steatment
        return $this->order_by_steatment;
    }

    /**
     * Get value for field: sort_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result sort_steatment
     **/
    public function get_sort_steatment() {
        // returns the value of sort_steatment
        return $this->sort_steatment;
    }

    /**
     * Get value for field: fetch_mode
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result fetch_mode
     **/
    public function get_fetch_mode() {
        // returns the value of fetch_mode
        return $this->fetch_mode;
    }
    //--------------- SET METHODS ----------------------------- //
    /**
     * Set value for field: id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public function set_id($id) {
        // sets the value of id
        $this->id = $id;
    }

    /**
     * Set value for field: related_content_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param related_content_id
     * @result void
     **/
    public function set_related_content_id($related_content_id) {
        // sets the value of related_content_id
        $this->related_content_id = $related_content_id;
    }

    /**
     * Set value for field: title
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param title
     * @result void
     **/
    public function set_title($title) {
        // sets the value of title
        $this->title = $title;
    }

    /**
     * Set value for field: content
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param content
     * @result void
     **/
    public function set_content($content) {
        // sets the value of content
        $this->content = $content;
    }

    /**
     * Set value for field: status
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param status
     * @result void
     **/
    public function set_status($status) {
        // sets the value of status
        $this->status = $status;
    }

    /**
     * Set value for field: forum_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param forum_id
     * @result void
     **/
    public function set_forum_id($forum_id) {
        // sets the value of forum_id
        $this->forum_id = $forum_id;
    }

    /**
     * Set value for field: user_id
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param user_id
     * @result void
     **/
    public function set_user_id($user_id) {
        // sets the value of user_id
        $this->user_id = $user_id;
    }

    /**
     * Set value for field: viewed
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param viewed
     * @result void
     **/
    public function set_viewed($viewed) {
        // sets the value of viewed
        $this->viewed = $viewed;
    }

    /**
     * Set value for field: is_active
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param is_active
     * @result void
     **/
    public function set_is_active($is_active) {
        // sets the value of is_active
        $this->is_active = $is_active;
    }

    /**
     * Set value for field: created_at
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param created_at
     * @result void
     **/
    public function set_created_at($created_at) {
        // sets the value of created_at
        $this->created_at = $created_at;
    }

    /**
     * Set value for field: updated_at
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param updated_at
     * @result void
     **/
    public function set_updated_at($updated_at) {
        // sets the value of updated_at
        $this->updated_at = $updated_at;
    }

    /**
     * Set value for field: page_size
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param page_size
     * @result void
     **/
    public function set_page_size($page_size) {
        // sets the value of page_size
        $this->page_size = $page_size;
    }

    /**
     * Set value for field: current_page
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param current_page
     * @result void
     **/
    public function set_current_page($current_page) {
        // sets the value of current_page
        $this->current_page = $current_page;
    }

    /**
     * Set value for field: conditional_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditional_steatment
     * @result void
     **/
    public function set_conditional_steatment($conditional_steatment) {
        // sets the value of conditional_steatment
        $this->conditional_steatment = $conditional_steatment;
    }

    /**
     * Set value for field: order_by_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param order_by_steatment
     * @result void
     **/
    public function set_order_by_steatment($order_by_steatment) {
        // sets the value of order_by_steatment
        $this->order_by_steatment = $order_by_steatment;
    }

    /**
     * Set value for field: sort_steatment
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param sort_steatment
     * @result void
     **/
    public function set_sort_steatment($sort_steatment) {
        // sets the value of sort_steatment
        $this->sort_steatment = $sort_steatment;
    }

    /**
     * Set value for field: fetch_mode
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param fetch_mode
     * @result void
     **/
    public function set_fetch_mode($fetch_mode) {
        // sets the value of fetch_mode
        $this->fetch_mode = $fetch_mode;
    }
    //--------------- CRUD METHODS ----------------------------- //
    /**
     * Class Constructor for: PaForumThread
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param orderby = null
     * @param sort = null
     * @param pagesize = 0
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result void
     **/
    public function __construct($conditionalStatement = null, $orderby = null, $sort = null, $pagesize = 0, $fetchmode = DB_FETCHMODE_OBJECT) {
        // set defaults
        $this->initialize($conditionalStatement, $orderby, $sort, $pagesize, $fetchmode);
    }

    public function initialize($conditionalStatement = null, $orderby = null, $sort = null, $pagesize = 0, $fetchmode = DB_FETCHMODE_OBJECT) {
        // set defaults
        if($conditionalStatement) {
            $this->conditional_steatment = $conditionalStatement;
        }
        if($orderby) {
            $this->order_by_steatment = $orderby;
        }
        if($sort) {
            $this->sort_steatment = $sort;
        }
        if($pagesize) {
            $this->page_size = $pagesize;
        }
        if($fetchmode) {
            $this->fetch_mode = $fetchmode;
        }
    }

    /**
     * Load object from database - dynamic method: load_PaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public function load_PaForumThread($id) {
        // use get method to load object data
        $this->get_PaForumThread($id);
    }

    /**
     * Save object to the database - dynamic method: save_PaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @result void
     **/
    public function save_PaForumThread() {
        // determine is this a new object
        if(!empty($this->id)) {
            $itemsToUpdate = array(
                'related_content_id' => $this->related_content_id,
                'title'              => $this->title,
                'content'            => $this->content,
                'status'             => $this->status,
                'forum_id'           => $this->forum_id,
                'user_id'            => $this->user_id,
                'viewed'             => $this->viewed,
                'is_active'          => $this->is_active,
                'created_at'         => $this->created_at,
                'updated_at'         => $this->updated_at,
                'modified_by'        => $this->modified_by,
            );
            $this->update_PaForumThread($this->id, $itemsToUpdate);
        }
        else {
            $this->insert_PaForumThread($this->related_content_id, $this->title, $this->content, $this->status, $this->forum_id, $this->user_id, $this->viewed, $this->is_active, $this->created_at, $this->updated_at, $this->modified_by);
        }
    }

    /**
     * Delete an existing record - dynamic method: delete_PaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public function delete_PaForumThread($id) {
        $conditionalStatement = "thread_id = $id AND is_active = 1";
        $thread_posts = PaForumPost::listPaForumPost($conditionalStatement);
        foreach($thread_posts as $post) {
            PaForumPost::deletePaForumPost($post->get_id());
        }
        // sql query
        $sql = "UPDATE { pa_forum_thread } SET is_active = 0 WHERE id = ?;";
        $params = array(
            $id,
        );
        // performs deletion of data
        $res = Dal::query($sql, $params);
    }

    /**
     * Delete an existing record - static method: deletePaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public static function deletePaForumThread($id) {
        // object self instance
        $instance = new self();
        // call dynamic method
        $instance->delete_PaForumThread($id);
    }

    /**
     * Delete all user threads - static method: delete_ThreadsForUser()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @result void
     **/
    public static function delete_ThreadsForUser($user_id) {
        $threads = self::listPaForumThread("user_id = $user_id AND is_active = 1");
        foreach($threads as $thread) {
            // when deleting a thread - all thread posts should be deleted
            $thread->delete_PaForumThread($thread->get_id());
        }
    }

    /**
     * Insert a new Record - dynamic method: insert_PaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param related_content_id
     * @param title
     * @param content
     * @param status
     * @param forum_id
     * @param user_id
     * @param viewed
     * @param is_active
     * @param created_at
     * @param updated_at
     * @param modified_by
     * @result id
     **/
    public function insert_PaForumThread($related_content_id, $title, $content, $status, $forum_id, $user_id, $viewed, $is_active, $created_at, $updated_at, $modified_by) {
        // items to be inserted in the database
        $params = array(
            null,
            $related_content_id,
            $title,
            $content,
            $status,
            $forum_id,
            $user_id,
            $viewed,
            $is_active,
            $created_at,
            $updated_at,
            $modified_by,
        );
        $__id = null;
        // insert query
        $sql = "INSERT INTO { pa_forum_thread } ( id, related_content_id, title, content, status, forum_id, user_id, viewed, is_active, created_at, updated_at, modified_by ) VALUES ( ?,?,?,?,?,?,?,?,?,?,?,? );";
        // perform insert in the database
        $res = Dal::query($sql, $params);
        if($res) {
            $__id = Dal::insert_id();
        }
        return $__id;
    }

    /**
     * Insert a new Record - static method: insertPaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param params = array()
     * @result id
     **/
    public static function insertPaForumThread($params = array()) {
        // object self instance
        $instance = new self();
        // required fields names
        $db_fields = array(
            "related_content_id",
            "title",
            "content",
            "status",
            "forum_id",
            "user_id",
            "viewed",
            "is_active",
            "created_at",
            "updated_at",
            "modified_by",
        );
        // build argument list
        foreach($db_fields as $param_name) {
            if(!array_key_exists($param_name, $params)) {
                throw new Exception("PaForumThread::insertPaForumThread() - Missing parameter $param_name.");
            }
            $$param_name = $params[$param_name];
        }
        // call dynamic method
        return $instance->insert_PaForumThread($related_content_id, $title, $content, $status, $forum_id, $user_id, $viewed, $is_active, $created_at, $updated_at, $modified_by);
    }

    /**
     * Retrieve an existing record - dynamic method: get_PaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumThread
     **/
    public function get_PaForumThread($id, $fetchmode = DB_FETCHMODE_OBJECT) {
        // MySQL query
        $sql = "SELECT * FROM { pa_forum_thread } WHERE id = ?;";
        // record ID
        $params = array(
            $id,
        );
        // execute query
        $res = Dal::query($sql, $params);
        $row = array();
        // data found?
        if($res->numRows() > 0) {
            // retrieve data object
            $row = $res->fetchRow($fetchmode);
            // populate this object
            if($fetchmode == DB_FETCHMODE_OBJECT) {
                $this->populateFromObject($row);
                return $this;
            }
            else {
                $this->populateFromArray($row);
                return $row;
            }
        }
        return null;
    }

    /**
     * Retrieve an existing record - static method: getPaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result object: PaForumThread
     **/
    public static function getPaForumThread($id, $fetchmode = DB_FETCHMODE_OBJECT) {
        // object self instance
        $instance = new self();
        // call dynamic method
        return $instance->get_PaForumThread($id, $fetchmode);
    }

    /**
     * Update an existing record - dynamic method: update_PaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result bool
     **/
    public function update_PaForumThread($id, $itemsToBeUpdated = array()) {
        // sql query
        $sql = "UPDATE { pa_forum_thread } SET ";
        // where steatment
        $__where = " WHERE id = ?;";
        // array of values
        $params = array();
        // build update paremeters
        foreach($itemsToBeUpdated as $field_name => $field_value) {
            $sql .= "$field_name = ?, ";
            $params[] = $field_value;
        }
        $sql      = rtrim($sql, " ,");
        $sql     .= $__where;
        $params[] = $id;
        // perform update operation
        $res = Dal::query($sql, $params);
        if($res) {
            $this->populateFromArray($itemsToBeUpdated);
            return true;
        }
        return false;
    }

    /**
     * Update an existing record - static method: updatePaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param id
     * @param itemsToBeUpdated = array()
     * @result void
     **/
    public static function updatePaForumThread($id, $itemsToBeUpdated = array()) {
        // object self instance
        $instance = new self();
        // call dynamic method
        return $instance->update_PaForumThread($id, $itemsToBeUpdated);
    }

    /**
     * Retrieved list of objects base on a given parameters - dynamic method: list_PaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param orderby = null
     * @param sort = null
     * @param limit = 0
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result array of objects: PaForumThread
     **/
    public function list_PaForumThread($conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT) {
        $this->initialize($conditionalStatement, $orderby, $sort);
        // build MySQL query
        $sql = "SELECT * FROM { pa_forum_thread } ";
        if($conditionalStatement) {
            $sql .= "WHERE $conditionalStatement";
        }
        if($orderby) {
            $sql .= " ORDER BY $orderby";
        }
        if($sort) {
            $sql .= " $sort";
        }
        if($limit) {
            $sql .= " LIMIT $limit";
        }
        $sql .= ";";
        // execute query
        $res = Dal::query($sql);
        $objects = array();
        // data found?
        if($res->numRows() > 0) {
            // retrieve data objects
            while($row = $res->fetchRow($fetchmode)) {
                if($fetchmode == DB_FETCHMODE_OBJECT) {
                    $obj = new PaForumThread();
                    $obj->populateFromObject($row);
                    $objects[] = $obj;
                }
                else {
                    $objects[] = $row;
                }
            }
        }
        return $objects;
    }

    /**
     * Retrieved list of objects base on a given parameters - static method: listPaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param orderby = null
     * @param sort = null
     * @param limit = 0
     * @param fetchmode = DB_FETCHMODE_OBJECT
     * @result array of objects: PaForumThread
     **/
    public static function listPaForumThread($conditionalStatement = null, $orderby = null, $sort = null, $limit = 0, $fetchmode = DB_FETCHMODE_OBJECT) {
        // object self instance
        $instance = new self();
        // call dynamic method
        return $instance->list_PaForumThread($conditionalStatement, $orderby, $sort, $limit, $fetchmode);
    }

    /**
     * Count records based on a given params - dynamic method: count_PaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param selectFields = array()
     * @param groupByFields = array()
     * @result int or array of counted objects
     **/
    public function count_PaForumThread($conditionalStatement = null, $selectFields = array(), $groupByFields = array()) {
        // build MySQL query
        $sql = "SELECT ";
        if(count($selectFields) > 0) {
            $sql .= implode(", ", $selectFields).", COUNT(*) AS counter ";
        }
        else {
            $sql .= "COUNT(*) AS counter ";
        }
        $sql .= "FROM { pa_forum_thread } ";
        if($conditionalStatement) {
            $sql .= "WHERE $conditionalStatement ";
        }
        if(count($groupByFields) > 0) {
            $sql .= "GROUP BY ".implode(", ", $groupByFields);
        }
        $sql .= ";";
        // execute query
        $res = Dal::query($sql);
        $objects = array();
        // data found?
        if($res->numRows() > 1) {
            // retrieve data objects
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $objects[] = $row;
            }
            return $objects;
        }
        elseif($res->numRows() == 1) {
            $data = $res->fetchRow(DB_FETCHMODE_OBJECT);
            return $data->counter;
        }
        else {
            return 0;
        }
    }

    /**
     * Count records based on a given params - static method: countPaForumThread()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param conditionalStatement = null
     * @param selectFields = array()
     * @param groupByFields = array()
     * @result int or array of counted objects
     **/
    public static function countPaForumThread($conditionalStatement = null, $selectFields = array(), $groupByFields = array()) {
        // object self instance
        $instance = new self();
        // call dynamic method
        return $instance->count_PaForumThread($conditionalStatement, $selectFields, $groupByFields);
    }

    /**
     * Get a page - dynamic method: getPage()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param page = 0
     * @result array of objects
     **/
    public function getPage($page = 0) {
        // calculate limit expression
        $l_start   = $this->page_size*$page;
        $l_end     = $this->page_size;
        $limit_str = "$l_start,$l_end";
        // performs deletion of data
        return $this->list_PaForumThread($this->conditional_steatment, $this->order_by_steatment, $this->sort_steatment, $limit_str);
    }

    /**
     * Populate object from array - dynamic method: populateFromArray()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param params = array()
     * @result void
     **/
    public function populateFromArray($params = array()) {
        // required fields names
        $db_fields = array(
            "id",
            "related_content_id",
            "title",
            "content",
            "status",
            "forum_id",
            "user_id",
            "viewed",
            "is_active",
            "created_at",
            "updated_at",
            "modified_by",
        );
        // build argument list
        foreach($db_fields as $param_name) {
            if(array_key_exists($param_name, $params)) {
                $this-> {
                    $param_name
                } = $params[$param_name];
            }
        }
    }

    /**
     * Populate object from another object - dynamic method: populateFromObject()
     *
     *
     * Generated with the DalClassGenerator created by: 
     * Zoran Hron <zhron@broadbandmechanics.com> 
     *
     * @param source = null
     * @result void
     **/
    public function populateFromObject($source = null) {
        // required fields names
        $db_fields = array(
            "id",
            "related_content_id",
            "title",
            "content",
            "status",
            "forum_id",
            "user_id",
            "viewed",
            "is_active",
            "created_at",
            "updated_at",
            "modified_by",
        );
        // build argument list
        foreach($db_fields as $param_name) {
            if(isset($source->$param_name)) {
                $this-> {
                    $param_name
                } = $source-> {
                    $param_name
                };
            }
        }
    }

    public function getPosts($pagesize = null, $page = null, $current_post = null, $orderby = "created_at", $sort = "ASC") {
        $posts                = array();
        $conditionalStatement = "thread_id = $this->id AND is_active = 1";
        $posts_obj            = new PaForumPost($conditionalStatement, $orderby, $sort, $pagesize);
        $total_posts          = $posts_obj->count_PaForumPost($conditionalStatement);
        if(!is_null($page)) {
            if((string) $page == 'last') {
                $page             = 0;
                $this->pagination = new MemoryPagging($total_posts, $pagesize, $page);
                $pages_arr        = $this->pagination->getPaggingData();
                if(isset($pages_arr['last'])) {
                    $page = $pages_arr['last'];
                }
                elseif(isset($pages_arr['pages'])) {
                    $page = array_pop($pages_arr['pages']);
                }
            }
            elseif((string) $page == 'first') {
                $page = 0;
                $this->pagination = new MemoryPagging($total_posts, $pagesize, $page);
            }
            elseif(is_numeric($page)) {
                $this->pagination = new MemoryPagging($total_posts, $pagesize, $page);
            }
            $posts = $posts_obj->getPage($page);
        }
        elseif(!is_null($current_post)) {
            $posts            = $posts_obj->list_PaForumPost($conditionalStatement, $orderby, $sort);
            $this->pagination = new MemoryPagging($posts, $pagesize,-1, array('id', $current_post));
            $page             = $this->pagination->current_page;
            $posts            = $this->pagination->getPageItems();
        }
        $this->current_page = $page;
        $this->pagination->pagging['selected'] = $page;
        foreach($posts as $post) {
            $post->user = $post->getAuthor();
        }
        return $posts;
    }

    public function getThreadStatistics() {
        $statistic            = array();
        $posts                = array();
        $conditionalStatement = "thread_id = $this->id AND is_active = 1";
        $orderby              = "created_at";
        $sort                 = "DESC";
        $limit                = 1;
        $posts                = PaForumPost::listPaForumPost($conditionalStatement, $orderby, $sort, $limit);
        $nb_posts             = PaForumPost::countPaForumPost($conditionalStatement);
        $user                 = new User();
        $user->load((int) $this->get_user_id());
        $statistics['user']  = $user;
        $statistics['posts'] = $nb_posts;
        $last_post           = null;
        if((!empty($posts[0]))) {
            $last_post = $posts[0];
            $last_post->user = $last_post->getAuthor();
        }
        $statistics['last_post'] = $last_post;
        // (!empty($posts[0])) ? $posts[0] : null;
        return $statistics;
    }

    public function getBoard() {
        $this->forum = PaForum::getPaForum($this->forum_id);
        $this->board = $this->forum->getBoard();
        return $this->board;
    }

    private function getThreadStatus() {
        // MySQL query
        $sql = "SELECT status+0 AS status FROM { pa_forum_thread } WHERE id = ?;";
        $res = null;
        // record ID
        $params = array(
            $this->id,
        );
        // execute query
        $res = Dal::query($sql, $params);
        if($res->numRows() > 0) {
            $_objarr = $res->fetchRow(DB_FETCHMODE_OBJECT);
            $res = $_objarr->status;
        }
        return $res;
    }

    public function updateThreadStatus($new_status, $type = 'set') {
        $status = $this->get_status();
        switch($type) {
            case 'set':
                $status = $status| $new_status;
                break;
            case 'reset':
                $status = $status^ $new_status;
                break;
            break;
        }
        $this->set_status($status);
    }

    public function getThreadIcon($default_icon = null) {
        $status = (int) $this->getThreadStatus();
        $icon_name = array();
        if($status&self::_public) {
            $icon_name[] = "public";
        }
        if($status&self::_private) {
            $icon_name[] = "private";
        }
        if($status&self::_hidden) {
            $icon_name[] = "hidden";
        }
        if($status&self::_sticky) {
            $icon_name[] = "sticky";
        }
        if($status&self::_locked) {
            $icon_name[] = "locked";
        }
        if(count($icon_name) == 0) {
            return $default_icon;
        }
        else {
            return implode('_', $icon_name).".gif";
        }
    }

    public function getNavigation($url, $css_class, $limit = 24, $separator = ' » ') {
        $navigation   = array();
        $navigation[] = $this->forum->getNavigation($url, $css_class, $limit, $separator);
        $this_link    = add_querystring_var($url, "thread_id", $this->id);
        $text         = $this->title;
        if(strlen($text) > $limit) {
            $text = substr($text, 0, $limit-3)."...";
        }
        $navigation[] = $this->get_a_tag($this_link, $css_class, $text);
        return implode($separator, $navigation);
    }

    private function get_a_tag($url, $class, $text) {
        return "<a href=\"$url\" class=\"$class\">$text</a>";
    }

    public function getAuthor() {
        $user = new User();
        $user->load((int) $this->user_id);
        return $user;
    }
}
?>