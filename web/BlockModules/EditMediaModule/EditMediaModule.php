<?php

class EditMediaModule extends Module {
  
  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  function __construct() {
    $this->html_block_id = "EditMediaModule";
  }

  function render() {
   global $current_theme_path;
   if ($this->contentcollection_type == '1') {  // if contentcolectin is group
      $data = $this->media_data;
    }
    else {
      $data = $this->media_data;
      if ( $data->type == IMAGE ) {
        $this->media_type = 'Image';
        $this->image_path = $data->image_file;
        
        if( $this->contentcollection_type == 2 ) {
          $image_albums = Album::load_all($this->uid, IMAGE_ALBUM);
          $this->links = $image_albums;
        }
      }
      else if ($data->type == AUDIO) {
        $this->media_type = 'Audio';
        $this->image_path =  "$current_theme_path/images/audio_img.jpg";
        
        if( $this->contentcollection_type == 2 ) {
          $audio_albums = Album::load_all($this->author_id, AUDIO_ALBUM);
          $this->links = $audio_albums;
        }        
      }
      else if ($data->type == TEK_VIDEO) {
        $this->media_type = 'Video';        
        $this->image_path = "files/".$data->internal_thumbnail;
        if( $this->contentcollection_type == 2 ) {
          $video_albums = Album::load_all($this->uid, VIDEO_ALBUM);
          $this->links = $video_albums;
        }
        $data->file_perm = $data->video_perm;
      }
    }
    if ($this->contentcollection_type == '1') {
      $this->inner_HTML = $this->generate_group_inner_html ();
    }
    else {
      $this->inner_HTML = $this->generate_inner_html ();
    }
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {

    switch( $this->mode ) {
      default:
        $template_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }
       
    $template_file_obj = & new Template($template_file);
    $template_file_obj->set_object('uid', $this->uid);
    $template_file_obj->set_object('links', $this->links);
    $template_file_obj->set_object('media_data', $this->media_data);
    $template_file_obj->set_object('media_type', $this->media_type);
    $template_file_obj->set_object('image_path', $this->image_path);    
    $template_file_obj->set_object('contentcollection_type', $this->contentcollection_type);
    $inner_html = $template_file_obj->fetch();
    return $inner_html;
  }
  function generate_group_inner_html () {
    switch( $this->mode ) {
      default:
        $template_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_group.tpl';
    }  
    $template_file_obj = & new Template($template_file);
    $template_file_obj->set_object('uid', $this->uid);
    $template_file_obj->set_object('media_data', $this->media_data);
    $template_file_obj->set_object('contentcollection_type', $this->contentcollection_type);
    $inner_html = $template_file_obj->fetch();
    return $inner_html;
  }
}
?>