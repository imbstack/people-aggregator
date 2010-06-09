<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* EditMediaModule.php is a part of PeopleAggregator.
* This module is displayed whenever media is edited on a user's, group's, or
*  network's page. For example, clicking the edit button next to the content
*  on the "Manage Group Contents" page leads to:
*
*    /edit_media.php?uid=[#]&cid=[#]&type=[type]
*
* Edit_Media.php is very similar to this object.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau?
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php

class EditMediaModule extends Module {
  
  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

  function __construct() {
    parent::__construct();
    $this->html_block_id = "EditMediaModule";
  }
    /** !!
    * Figures out what type of media we're looking at, then sets a couple
    * internal variables, and then dispaches the flow to either
    * {@see generate_inner_html()} and {@see generate_group_inner_html()}
    *
    * @return string Content to be displayed.
    */
  function render() {
    
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
        $this->image_path =  PA::$theme_url . "/images/audio_img.jpg";
        
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
    /** !!
    * Generates, using the template file in this directory, the edit page
    *
    * @return string The HTML for this module.
    */
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

    /** !!
    * Generates, again using the template file, the edit page, if the media meets
    * a certain condition.
    *
    * @return string The HTML for this module.
    */
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
