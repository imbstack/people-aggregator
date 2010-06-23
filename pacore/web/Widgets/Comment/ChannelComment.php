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
require_once dirname(__FILE__).'/../../../config.inc';
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/User/User.php";
/**
 Class for handling the comments submitted through the comment widget.
**/
class ChannelComment {
  
  /**
   * Comment id.
  **/
  public $comment_id;
  /**
  * Id of the channel.
  **/
  public $channel_id;
  
  /**
  * Comment text.  
  **/
  public $comment;
  /**
   * User id .
  **/
  public $user_id;
  
  public $is_active;
  /**
   * Slug is unique for a page. Will hold nonnumaric value
  **/
  public $slug;
  
  /**
   * Function to save the comments.
  **/
  public function save() {
     Logger::log("Enter: function WidgetComment::save");
     if (!empty($this->comment_id)) {//edit
       $sql = "UPDATE {channel_comment} SET comment = ? where comment_id = ? AND is_active = ?";
       $data = array($this->comment, $this->comment_id, ACTIVE);
       Dal::query($sql, $data);
     } else {//save
       $created = time();
       $sql = "INSERT INTO {channel_comment} (channel_id, comment, user_id, created, is_active, slug) VALUES (?, ?, ?, ?, ?, ?)";
       $data = array($this->channel_id, $this->comment, $this->user_id, $created, $this->is_active, $this->slug);
       Dal::query($sql, $data);
     }
     Logger::log("Exit: function WidgetComment::save");
     return;
  }
  /**
    Function to convert the slug to a unique channel_id.
  **/
  public static function convert_slug_to_channel_id($slug) {
    Logger::log("Enter: function ChannelComment::convert_slug_to_channel_id");
    $sql = "SELECT channel_id FROM {channel_comment} WHERE slug = ? AND is_active = ?";
    $data = array($slug, ACTIVE);
    $channel_id = Dal::query_first($sql ,$data);
    if (empty($channel_id)) {
      $sql = "SELECT max(channel_id) FROM {channel_comment} ";
      $res = Dal::query_first($sql);
      if (empty($res)) {
        $channel_id = 1;
      }else {
        $channel_id = $res+1;
      }
    }
    return $channel_id;
    Logger::log("Exit: function ChannelComment::convert_slug_to_channel_id");
  }
  /**
  *function to load a channel_comment based on the comment_id.
  **/
  public function load() {
    Logger::log("Enter: function ChannelComment::load");
    $sql = "SELECT * FROM {channel_comment} WHERE comment_id = ? AND is_active <> ?";
    $res = Dal::query($sql, array($this->comment_id, 0));
    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
    }
    Logger::log("Exit: function ChannelComment::load");
    return $row;
  }
  /**
   * Function to load channel_omments based on channel_id and id. 
  **/
  public static function get_channel_comments($channel_id , $user_id = NULL, $cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC') {
    Logger::log("Enter: function ChannelComment::get_channel_comments");
    $order_by = $sort_by.' '.$direction;
     if ( $show == 'ALL' || $cnt == TRUE) {
       $limit = '';
     } else {
       $start = ($page -1)* $show;
       $limit = 'LIMIT '.$start.','.$show;
     }
    
     if ($user_id) {
       $with_user_id = " AND user_id = $user_id "; 
     } else {
       $with_user_id = "";
     }
     $sql = "SELECT * FROM {channel_comment} WHERE channel_id = ? AND is_active <> ?  $with_user_id ORDER BY $order_by $limit ";
     $data = array($channel_id, 0);
     $res = Dal::query($sql, $data);
     if ($cnt) {
       Logger::log("Exit: function ChannelComment::get_channel_comments in case of count");
       return $res->numRows();
     }
     $result = array();
     if ($res->numRows()) {
       $i = 0;
       while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
         $result[$i] = $row;
         $author = new User();
         $author->load((int)$row['user_id']);
         $result[$i]['author_details'] = $author;
         $i++;
       }
     }
    Logger::log("Exit: function ChannelComment::get_channel_comments");
    return $result;
  }
}
?>