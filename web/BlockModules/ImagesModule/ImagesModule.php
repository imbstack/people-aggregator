<?php

require_once "ext/Image/Image.php";
require_once "ext/Audio/Audio.php";
require_once "ext/TekVideo/TekVideo.php";


class ImagesModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public $sort_by = FALSE;
  public $outer_template = 'outer_public_side_module.tpl';
  public $uid;
  public $gid;
  public $page;
  public $group_details;
  

  function __construct() {
    parent::__construct();
    $this->title = __("Gallery");
    $this->block_type = 'Gallery';
    $this->html_block_id = "ImagesModule";
  }


  public function initializeModule($request_method, $request_data)  {
    global $error_msg;
    $this->uid = (empty($request_data['uid'])) ? PA::$login_uid : $request_data['uid'];
    switch ($this->page_id) {
      case PAGE_HOMEPAGE:
        $this->page = "homepage";
        $this->title = __("Recent Media");
        if (!empty(PA::$login_uid)) {
          $this->subject = PA::$login_uid;
        } else {
          $this->subject = 0;
        }
      break;
      case PAGE_GROUP:
        if (!empty($this->shared_data['group_info'])) {
          $this->group_details = $this->shared_data['group_info'];
          $this->gid = $this->group_details->collection_id;
          $this->page_type = 'group';
          $this->page = "grouppage";
          $this->title = __('Group Gallery');
          $this->subject = $this->gid;
          $this->view_all_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media&gid=$this->gid";
        } else {
          return "skip";
        }  
      break;
      case PAGE_PERMALINK:
        if (!empty($this->shared_data['group_info'])) {
          $this->group_details = $this->shared_data['group_info'];
          $this->gid = $this->group_details->collection_id;
          $this->page_type = 'group';
          $this->page = "grouppage";
          $this->title = __('Group Gallery');
          $this->subject = $this->gid;
          $this->view_all_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media&gid=$this->gid";
        } else {
          $this->page = "homepage";
          $this->title = __("Recent Media");
          if (!empty(PA::$login_uid)) {
            $this->subject = PA::$login_uid;
          } else {
            $this->subject = 0;
          }
        }  
      break;
      case PAGE_USER_PUBLIC:
          $this->page = "userpage";
          $this->title = ucfirst(PA::$page_user->first_name).'\'s ';
          $this->title = abbreviate_text($this->title, 18, 10);
          $this->title .= __('Gallery');
          $this->subject = $this->uid;
          if(PA::$login_uid) {
            $this->view_all_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=$this->uid";
          }  
      break;
      case PAGE_USER_PRIVATE:
          $this->mode = PRI;
          $this->page = "userpage";
          $this->title = __('My Gallery');
          $this->subject = $this->uid;
          $this->view_all_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=$this->uid";
      break;
      default:
    }
  }


  function render() {
    $pic = $aud = $vid = NULL;
    
    switch($this->page) {
      case 'homepage':
        $pic = Image::load_recent_media_image(0,$this->subject);
        $aud = Audio::load_recent_media_audio(0,$this->subject);
        $vid = TekVideo::get_media_recent_tekvideo(0,$this->subject);
      break;
      case 'grouppage':
        $pic = Image::load_images_for_collection_id((int)$this->gid);
        $aud = Audio::load_audios_for_collection_id((int)$this->gid);
        $tekvid = TekVideo::get(NULL, array('C.collection_id' => $this->group_details->collection_id));
        $vid = objtoarray($tekvid);
      break;
      case 'userpage':
        $pic = Image::load_user_gallery_images($this->uid, 10, PA::$login_uid);
        $aud = Audio::load_user_gallery_audio($this->uid, 10, PA::$login_uid);
        if(!empty(PA::$page_uid) || PA::$login_uid != PA::$page_uid) {
          $condition['M.video_perm'] = (!empty($this->friend_list) && in_array(PA::$page_uid, $this->friend_list)) ? array(WITH_IN_DEGREE_1, ANYONE) : ANYONE;
        }
        $tekvid = TekVideo::get(array('show' => 10, 'page' => 1), array('C.author_id' => $this->uid));
        $vid = objtoarray($tekvid);
        $this->view_all_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=$this->subject";
      break;
    }
    
    //even if we got more media, we can display only 6
    
    $pictures = array();
    $max = (count($pic) < 6) ? count($pic) : $max = 6;
    for ($i = 0; $i < $max; $i++) {
      $pictures[$i] = $pic[$i];
    }

    $audios = array();
    $max = (count($aud) < 6) ? count($aud) : $max = 6;
    for ($i = 0; $i < $max; $i++) {
      $audios[$i] = $aud[$i];
    }

    $videos = array();
    $max = (count($vid) < 6) ? count($vid) : $max = 6;
    for ($i = 0; $i < $max; $i++) {
      $videos[$i] = $vid[$i];
    }

    if (!empty($this->group_details) && (!empty($pictures) || !empty($audios) || !empty($videos))) {
      $this->view_all_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media&gid=".(int)$this->group_details->collection_id;
    }  
    
    $gallery = array('images'=>$pictures, 'audios'=>$audios, 'videos'=>$videos);
    $this->links = $gallery;
    $this->inner_HTML = $this->generate_inner_html( $this->links );

    if (empty($gallery['images']) && empty($gallery['audios']) && empty($gallery['videos'])) {
      $this->height = 70;
      $this->view_all_url = '';
    }
    else {
      $this->height = 260;
    }


    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html ($links) {
    
    $inner_template = NULL;
    switch ( $this->mode ) {
      case PRI:
        $this->outer_template = 'outer_private_side_module.tpl';
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
      break;
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    }
    
    $obj_inner_template = & new Template($inner_template);
    $obj_inner_template->set('gid', $this->gid);
    $obj_inner_template->set('links', $links);
    $obj_inner_template->set('current_theme_path', PA::$theme_url);
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }

}
?>
