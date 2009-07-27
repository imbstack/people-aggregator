<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        UploadMediaModule.php, BlockModule file to generate Uploading Media
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class UploadMediaModule which generates html for
 *              uploading Media- it is middle module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once PA::$blockmodule_path.'/MediaGalleryModule/MediaGalleryModule.php';
require_once "api/TekMedia/TekMedia.php";
class UploadMediaModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_media_gallery_module.tpl';

  function __construct() {
     parent::__construct();
     $this->html_block_id = 'UploadMediaModule';
  }

  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html() {
     
    $user_id = PA::$login_uid;
    if (!empty($this->mode)) {
      $this->type = $this->mode;
      $user_id = (!empty($this->user_id)) ? $this->user_id : PA::$login_uid;
    }
    switch ($this->type) {
      case 'Videos' :
        $gid = NULL;
          if(!empty($_GET['gid'])) {
            $gid = $_GET['gid'];
          }
          if (empty($this->mode)) {
            $TekMedia = new TekMedia();
            $form_key =  $TekMedia->generate_form($gid);
          }
	        $alb_type = VIDEO_ALBUM;
      break;
      case 'Audios':
	      $alb_type = AUDIO_ALBUM;
      break;
      default: // default to the image album if no type given
      case 'Images':
	      $alb_type = IMAGE_ALBUM;
      break;
    }
    $all_albums = Album::load_all($user_id, $alb_type);
    $default_name = PA::$config->default_album_titles[$alb_type];

    /* setting all the album for this page */
    /* Retrive the All album of user */
    $album = array();
    $j=0;
    if(!empty($all_albums)) {
      foreach ($all_albums as $alb) {
        $album[$j]['id'] = $alb['collection_id'];
        $album[$j]['name'] = $alb['description'];
        $j++;
      }
      $this->default_album_id = $album[0]['id'];
      $this->default_album_name = $album[0]['name'];
    }
    $this->my_all_album = $album;
    if ($this->mode == 'Videos') {
    	if (@$this->view == 'remote') {
      	$tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/ajax_form_video_upload.tpl';
    	} else {
      	$tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/form_video_upload.tpl';
    	}
      $this->outer_template = 'empty_outer.tpl';
    } else {
      $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }

    if (@$this->view == 'ajax') {
    	switch ($this->mode) {
    		case 'Videos':
    			$t = 'video';
    		break;
    		case 'Images':
    			$t = 'image';
    		break;
            case 'Audios':
                $t = 'audio';
            break;
    	}
    	if (!empty($t)) {
    		$tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/ajax_'.$t.'.tpl.php';
    	}
      $this->outer_template = 'empty_outer.tpl';
    }

    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('my_all_album', $this->my_all_album);
    $inner_html_gen->set('default_name', $default_name);
    $inner_html_gen->set('back', @$_SERVER['HTTP_REFERER']);
    $inner_html_gen->set('media_type', $this->type);
    if(!empty($form_key)) {
      $inner_html_gen->set('form_key', $form_key);
    }
    $inner_html = $inner_html_gen->fetch();

    return $inner_html;
  }
}
?>