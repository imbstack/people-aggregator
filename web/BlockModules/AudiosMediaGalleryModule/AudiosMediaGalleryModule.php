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
 * File:        AudiosMediaGalleryModule.php, BlockModule file to generate Audio Module
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class AudiosMediaGalleryModule which generates html of 
 *              Audio Gallery list - it is middle module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once PA::$blockmodule_path.'/MediaGalleryModule/MediaGalleryModule.php';
require_once 'ext/Audio/Audio.php';

class AudiosMediaGalleryModule extends MediaGalleryModule {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_media_gallery_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->html_block_id = 'AudiosMediaGalleryModule';
    
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
  
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    global $login_uid;
    if (!isset($_GET['gid']) || empty($_GET['gid'])) {
      parent::set_vars();
      $frnd_list = null;
      if ( !empty($_GET['view']) ) {
        $frnd_list=$this->friend_list;
      }    
      $sb_audios = array();
      $new_album = new Album(AUDIO_ALBUM);
      if ($this->album_id) {
        $new_album = new Album();
        $new_album->album_type = AUDIO_ALBUM;
        $new_album->load((int)$this->album_id);
        $audio_data['album_id'] = $new_album->collection_id;
        $audio_data['album_name'] = $new_album->title;
      } else {
        $new_album->collection_id = $this->default_album_id;
        $audio_data['album_id'] = $this->default_album_id;
        $audio_data['album_name'] = $this->default_album_name;
      }
      
      $audio_ids = $new_album->get_contents_for_collection();
            
      if (!empty($audio_ids)) {
          $k=0;
          $ids = array();
          for ($i=0; $i<count($audio_ids); $i++) {
            if($audio_ids[$i]['type'] != 7) { // Type 7 is for SB Content
                $ids[$i] = $audio_ids[$i]['content_id'];
            } else {
              $tags = Tag::load_tags_for_content($audio_ids[$i]['content_id']);
              $tags = show_tags($tags, null); //show_tags function is defined in uihelper.php
              $sb_audios[] = array('content_id' => $audio_ids[$i]['content_id'], 'title' => $audio_ids[$i]['title'], 'type' => $audio_ids[$i]['type'], 'created' => $audio_ids[$i]['created'], 'tags' => $tags);
            }
          }
          $new_audio = new Audio();
          
          $data = $new_audio->load_many($ids, $this->uid, $login_uid);
          if(count($data) > 0) {
            foreach ($data as $d) {
              $audio_data[$k]['content_id'] = $d['content_id'];
              $audio_data[$k]['audio_file'] = $d['audio_file'];
              $audio_data[$k]['audio_caption'] = $d['audio_caption'];
              $audio_data[$k]['title'] = $d['title'];
              $audio_data[$k]['body'] = $d['body'];
              $audio_data[$k]['created'] = $d['created'];
              $audio_data[$k]['type'] = "";
              $tags_array = Tag::load_tags_for_content ($d['content_id']);
              $tags_string= "";
              $tags_string = show_all_contents_for_tag($tags_array);
              $audio_data[$k]['tags'] = $tags_string;
              $k++;
            }
          }
          // Merging Media Gallery content and SB Content
          for($counter = 0; $counter < count($sb_audios); $counter++) {
              $audio_data[$k]['content_id'] = $sb_audios[$counter]['content_id'];
              $audio_data[$k]['title'] = $sb_audios[$counter]['title'];
              $audio_data[$k]['type'] = $sb_audios[$counter]['type'];
              $audio_data[$k]['image_caption'] = $sb_audios[$counter]['title'];
              $audio_data[$k]['created'] = $sb_audios[$counter]['created'];
              $audio_data[$k]['tags'] = $sb_audios[$counter]['tags'];
              $k++;
          }
  
        }
      if (!empty($_GET['view'])) {
        if (empty($frnd_list)) 
        $audio_data=NULL;
      }
      $inner_template = NULL;
      switch ( $this->mode ) {
        default:
          $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
      }
    
      $obj_inner_template = & new Template($inner_template);
      $obj_inner_template->set('links', $audio_data);    
      $obj_inner_template->set('uid', $this->uid);
      $obj_inner_template->set('frnd_list', $frnd_list); 
      $obj_inner_template->set('my_all_album', $this->my_all_album);
      $obj_inner_template->set('show_view', $this->show_view);
      $inner_html = $obj_inner_template->fetch();
      return $inner_html;
     } else {
        parent::set_group_media_gallery();
        //------------- Handling the Groups Media gallery -----------
        $group = ContentCollection::load_collection((int)$_GET['gid'], $_SESSION['user']['id']);
        
        $audio_data = Audio::load_audios_for_collection_id ($_GET['gid'], $limit = 0);
        $i = 0;
        if (!empty($audio_data)) {
          foreach( $audio_data as $data) {
            $tags_array = Tag::load_tags_for_content ($data['content_id']);
            $tags_string = "";
            $tags_string = show_all_contents_for_tag($tags_array); 
            $audio_data[$i]['tags'] = $tags_string;
            $i++;
          }
        }
        $audio_data['album_id'] = $group->collection_id;
        $audio_data['album_name'] = $group->title;
    
        $inner_template = NULL;
        switch ( $this->mode ) {
          default:
            $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public_groups.tpl';
        }
        $obj_inner_template = & new Template($inner_template);
        $obj_inner_template->set('links', $audio_data);
        $obj_inner_template->set('show_view', $this->show_view);
        $obj_inner_template->set('my_all_groups', $this->group_ids);
        $inner_html = $obj_inner_template->fetch();
        return $inner_html;
     
     } 
  }

}

?>