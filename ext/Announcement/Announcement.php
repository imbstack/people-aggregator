<?php
  // Announcement class represents and manages the announcement of a network. It will extend content class.
  include_once dirname(__FILE__)."/../../config.inc";
  // global var $path_prefix has been removed - please, use PA::$path static variable
  require_once "api/Content/Content.php";
  require_once "api/Logger/Logger.php";
  
  /**
  * constant for content type
  */
  
  
  /**
  * constant for announcement position on page
  */
  define( "TOP", 1 );
  define( "MIDDLE", 2 );
  define( "BOTTOM", 3 );
  
  /**
 * Implements Announcement in a network.
 * @extends Content
 * @author Tekriti Software (www.TekritiSoftware.com)
 */
 
 class Announcement extends Content {
   public $announcement_time;
   public $position;
   public $status;
   
   /**
   * class Content::__construct
   */
   public function __construct(){
     parent::__construct();
     $this->type = ANNOUNCEMENT;
   }
   
    /**
   * load Announcement data in the object
   * @access public
   * @param content id of the Announcement.
   */
   
   public function load( $content_id) {
    Logger::log(" Enter: Announcement::load | Arg: \$content_id = $content_id" );
    Logger::log(" Calling: Content::load | Param: \$content_id = $content_id" );

    parent::load($content_id);
    $sql = "SELECT * FROM {announcements} WHERE content_id = $this->content_id";
    $res = Dal::query($sql);
    
    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
      $this->announcement_time = $row->announcement_time;
      $this->position = $row->position;
      $this->status = $row->status;
    }
    
    Logger::log("Exit: Announcement::load");
    return;
  }
  
    /**
   * Saves Announcement in database
   * @access public
   */
  public function save() {
    Logger::log("Enter: Announcement::save_announcement");
    Logger::log("Calling: Content::save");
    
    if ($this->content_id) {
       parent::save();
      //for child class
      $data = array();
      $update = '';
      $field_array = array('announcement_time','position','status');
      foreach ( $field_array as $key=>$value ) {
        if ( $this->$value ) {
          $update.='  '.$value.' = ?,';
          array_push($data, $this->$value);
        } 
      }
      if ( !empty($update) ) {
        $update = substr($update,0,-1);
        $sql = 'UPDATE {announcements} SET '.$update. ' WHERE content_id = ?' ;
        array_push($data, $this->content_id);
      }
       $res = Dal::query($sql, $data);
       
    }
    else {
      parent::save();
      $sql = "INSERT INTO {announcements} (content_id, announcement_time, position, status, is_active) VALUES (?, ?, ?, ?, ?)";
      $data = array($this->content_id, $this->announcement_time, $this->position, $this->status, $this->is_active);
      $res = Dal::query($sql, $data);
      return array('aid'=>$this->content_id);
    }

    Logger::log("Exit: Image::save_announcement");
    return $this->content_id;
  }
  
  
  /**
  * calls Content::delete() to soft delete a content
  * Soft delete is being performed here
  * set $this->content_id at the time of calling this method
  */
  public function delete () {
    Logger::log("Enter: Announcement::delete");
    Logger::log("Calling: Content::delete");
    $sql = 'UPDATE { announcements } SET is_active = ?, position = ?, status = ? WHERE content_id = ?';
    $data =array(0,0,0, $this->content_id);
    $res = Dal::query($sql, $data);
    parent::delete();
    Logger::log("Exit: Announcement::delete");
    return;
  }
  
   /**
   *  Load all announcement with its data for a single network. 
   * @param $condition, filter to be applied on database if search is needed
   * @param $count
   * @param $order_by
   * @return $announcement_data, an associative array, having content_id, title, body, announcement_time, position, *    status in it for each announcement.
   */
//    public static function load_announcements_array($cnt = FALSE, $show = 'ALL', $page = 0, $sort_by = 'created', $direction = 'DESC', $condition = NULL ) {
   public static function load_announcements_array($param = NULL, $condition = NULL) { 
    
     Logger::log("Enter: Announcement::load_announcements_array");
     
     $cid = NULL;
     $live = FALSE;
     if (!empty($param['show'])) {
       $show = $param['show'];
     } else {
       $show = 'ALL';
     }
     if (!empty($param['cnt'])) {
       $cnt = $param['cnt'];
     } else {
       $cnt = FALSE;
     }
     if (!empty($param['page'])) {
       $page = $param['page'];
     } else {
       $page = 0;
     }
     //check for condition
     if (!empty($condition['content_id'])) {
       $cid = $condition['content_id'];
     }
     if (!empty($condition['live'])) {
       $live = TRUE;
     }
      if ( $show == 'ALL' || $cnt == TRUE) {
      $limit = '';
    } else {
      $start = ($page -1) * $show;
      $limit = 'LIMIT '.$start.','.$show;
    }
     $i = 0;
     // load one record based upon cid
     if ($cid) {
       $sql = "SELECT *, C.title as title, C.body as body FROM {contents} AS C, {announcements} as A WHERE C.content_id = A.content_id AND C.content_id = ? AND C.is_active = ?  $limit";
       $data = array( $cid, 1 );
     } 
     else if($live) {//
      
       $sql = "SELECT *, C.title as title, C.body as body FROM {contents} AS C, {announcements} AS A WHERE A.status = ? AND A.is_active = ? AND C.content_id = A.content_id ORDER BY A.position";
       $data = array( $live, 1 );
     }
     else {  //
       $sql = "SELECT *, C.title as title, C.body as body FROM {contents} AS C, {announcements} as A WHERE C.content_id = A.content_id AND C.is_active = ?  $limit";
       $data = array( 1 );
     } 
    
     $res = Dal::query( $sql, $data );
     
     if($cnt) {
        return (int)$res->numRows();
     }
     $announcement_data = array();
     if ($res->numRows() > 0) {
       while ($row = $res->fetchRow( DB_FETCHMODE_OBJECT )) {
         if($row->status == 1) { //1 for live
           $status = 'Live';
         } else if($row->status == 2) {// 2 for archived
           $status = 'Archived';
         } 
         $announcement_data[$i]['content_id'] = $row->content_id;
         $announcement_data[$i]['title'] = $row->title;
         $announcement_data[$i]['body'] = $row->body;
         $announcement_data[$i]['announcement_time'] = $row->announcement_time;
         $announcement_data[$i]['position'] = $row->position;
         $announcement_data[$i]['status'] = $status;
         $i++;
       }
     }
     Logger::log("Exit: Announcement::load_announcements_array");
     return $announcement_data;
   }
   
/**
*checks where the announcement position demanded is available or not
* @param position position that is demanded
*/

  public static function is_position_available( $position, $condition = NULL) {
    Logger::log("Enter: Announcement::is_position_available | Arg: \$position = $position");
    if($condition['content_id']) {
      $sql = "SELECT * FROM {announcements}  WHERE position = ? and is_active = ? AND content_id <> ? and status = ?";
      $data = array($position,1,$condition['content_id'],1);
    } else {
      $sql = "SELECT * FROM {announcements}  WHERE position = ? AND is_active = ? AND status = ?";
      
      $data = array( $position, 1, 1);
     
    } 
    $res = Dal::query( $sql, $data );    
    if ($res->numRows() >= 1) {
      return false;
    } else {
      return true;
    } 
    Logger::log("Exit: Announcement::is_position_available");
  }
}


?>