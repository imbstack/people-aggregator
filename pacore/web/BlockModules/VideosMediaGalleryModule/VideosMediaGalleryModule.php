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
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        VideosMediaGalleryModule.php, BlockModule file to generate Video Module
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class VideosMediaGalleryModule which generates html of 
 *              Video Gallery list - it is middle module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once PA::$blockmodule_path."/MediaGalleryModule/MediaGalleryModule.php";
require_once "api/TekVideo/TekVideo.php";

class VideosMediaGalleryModule extends MediaGalleryModule {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_media_gallery_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->html_block_id = 'VideosMediaGalleryModule';
  }
  
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();    
    $content = parent::render();
    return $content;
  }
  
  public function initializeModule($request_method, $request_data) {
    $gallery_info = $this->shared_data['media_gallery'];
    
    $this->uid = PA::$login_uid;
    if (!empty($request_data['uid'])) {
      $this->uid = $request_data['uid'];
    }
    if(!empty(PA::$login_user)) {
      $this->title = User::get_login_name_from_id($this->uid)." - ";
    } else {
      $this->title = "";
    }
    $this->title .= "Media Gallery - " . PA::$network_info->name;

    $this->show_view = $gallery_info['show_view'];
    $this->type = $gallery_info['media_type'];
    $this->album_id = $gallery_info['album_id'];
    
  }

  function generate_inner_html () {
    global $login_uid;
    $video_data = NULL;
    if (!isset($_GET['gid']) || empty($_GET['gid'])) {
      parent::set_vars();
      $frnd_list = null;
      if ( !empty($_GET['view']) ) {
        $frnd_list=$this->friend_list;
      }    
      $sb_videos = array();
      $new_album = new Album(VIDEO_ALBUM);
      if ($this->album_id) {
        $new_album = new Album();
        $new_album->album_type = VIDEO_ALBUM;
        $new_album->load($this->album_id);
        $album_data['album_id'] = $new_album->collection_id;
        $album_data['album_name'] = $new_album->title;
      } else {
        $new_album->collection_id = $this->default_album_id;
        $album_data['album_id'] = $this->default_album_id;
        $album_data['album_name'] = $this->default_album_name;
      }
      $params = $condition = array();
      if(!empty(PA::$page_uid) || PA::$login_uid != PA::$page_uid) {
        $condition['M.video_perm'] = (!empty($this->friend_list) && in_array(PA::$page_uid, $this->friend_list)) ? array(WITH_IN_DEGREE_1, ANYONE) : ANYONE;
      }
      $condition['C.collection_id'] = $album_data['album_id'];
      $video_info = TekVideo::get($params, $condition);
      $video_ids = objtoarray($video_info);
            
      if (!empty($video_ids)) {
          $k=0;
          $ids = array();
          for ($i=0; $i<count($video_ids); $i++) {
            if($video_ids[$i]['type'] != 7) { // Type 7 is for SB Content
                $ids[$i] = $video_ids[$i]['content_id'];
            } else {
              $tags = Tag::load_tags_for_content($video_ids[$i]['content_id']);
              $tags = show_tags($tags, null); //show_tags function is defined in uihelper.php
              $sb_videos[] = array('content_id' => $video_ids[$i]['content_id'], 'title' => $video_ids[$i]['title'], 'type' => $video_ids[$i]['type'], 'created'=>$video_ids[$i]['created'], 'tags' => $tags);
            }
          }
          if(count($video_ids) > 0) {
              $video_data = $video_ids;
          }
          // Merging Media Gallery content and SB Content
          for($counter = 0; $counter < count($sb_videos); $counter++) {
              $video_data[$k]['content_id'] = $sb_videos[$counter]['content_id'];
              $video_data[$k]['title'] = $sb_videos[$counter]['title'];
              $video_data[$k]['type'] = $sb_videos[$counter]['type'];
              $video_data[$k]['video_caption'] = $sb_videos[$counter]['title'];
              $video_data[$k]['created'] = $sb_videos[$counter]['created'];
              $video_data[$k]['tags'] = $sb_videos[$counter]['tags'];
              $k++;
          }
  
        }
      if (!empty($_GET['view'])) {
        if (empty($frnd_list)) 
        $video_data=NULL;
      }
      $inner_template = NULL;
      switch ( $this->mode ) {
        default:
          $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
      }
    
      $obj_inner_template = new Template($inner_template);
      $obj_inner_template->set('links', $video_data); 
      $obj_inner_template->set('album_data', $album_data);   
      $obj_inner_template->set('uid', $this->uid);
      $obj_inner_template->set('frnd_list', $frnd_list); 
      $obj_inner_template->set('my_all_album', $this->my_all_album);
      $obj_inner_template->set('show_view', $this->show_view);
      $inner_html = $obj_inner_template->fetch();
      return $inner_html;
    } else {
        // ----- Calling parents function which set all the Require variables
        parent::set_group_media_gallery();
        //------------- Handling the Groups Media gallery -----------
        $group = ContentCollection::load_collection((int)$_GET['gid'], $_SESSION['user']['id']);
        
        $params = $condition = array();
        $album_data['album_id'] = $group->collection_id;
        $album_data['album_name'] = $group->title;
        $condition['C.collection_id'] = $album_data['album_id'];
        $video_info = TekVideo::get($params, $condition);
        $video_data = objtoarray($video_info);
//         $video_data = Video::load_videos_for_collection_id ($_GET['gid'], $limit = 0);
        $i = 0;
        if (!empty($video_data)) {
          foreach( $video_data as $data) {
            $tags_array = Tag::load_tags_for_content ($data['content_id']);
            $tags_string = "";
            $tags_string = show_all_contents_for_tag($tags_array); 
            $video_data[$i]['tags'] = $tags_string;
            $i++;
          }
        }
    
        $inner_template = NULL;
        switch ( $this->mode ) {
          default:
            $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public_groups.tpl';
        }
        $obj_inner_template = new Template($inner_template);
        $obj_inner_template->set('links', $video_data);
        $obj_inner_template->set('album_data', $album_data);
        $obj_inner_template->set('show_view', $this->show_view);
        $obj_inner_template->set('my_all_groups', $this->group_ids);
        $inner_html = $obj_inner_template->fetch();
        return $inner_html;
    
    }  
  }

}
?>
