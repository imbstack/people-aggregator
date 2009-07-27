<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        MediaGalleryModule.php, BlockModule file to generate Image Gallery
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class MediaGalleryModule which generates all the Album list
 *               all friends list
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 *
 */

 /**
 * This class generates inner html of Image Audio Video Modules
 * set_vars variales set all the Value of Modules (Image,Audio,Video)
 * @package BlockModules
 * @subpackage MediaGalleryModule
 */
require_once "ext/TekVideo/TekVideo.php";
class MediaGalleryModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';

  /**
   * $default_album_id is used to hold the Default album id
   * @var interger
   */
  public $default_album_id;

  /**
   * $default_album_name is used to hold the Default album name
   * @var string
   */
  public $default_album_name;
  /**
   * $friend_list is used to hold the friend list of the User
   * @var array
   */
  public $friend_list;
  /**
   * $my_all_album is used to hold the array of my all albums
   * @var array
   */
  public $my_all_album;

  /**
   * $group_ids is used to hold the array of groups
   * @var array
   */
  public $group_ids;

  function __construct() {
    parent::__construct();
  }

  function handleRequest($request_method, $request_data) {

    if(!empty($request_data['action'])) {
      $action = $request_data['action'];
      $class_name = get_class($this);
      switch($request_method) {
        case 'POST':
          $method_name = 'handlePOST_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
          }
        break;
        case 'GET':
          $method_name = 'handleGET_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
          }
        break;
        case 'AJAX':
          $method_name = 'handleAJAX_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
          }
        break;
      }
    }
  }

  private function handlePOST_deleteMedia($request_data) {
    global $error, $error_msg;
//    echo "<pre>".print_r($request_data,1)."</pre>";
    // deleting media
    if(!empty($request_data['media_id'])) {
      $id = $request_data['media_id'];
      try {
        if($request_data['type'] == 'Images') {
          $new_image = new Image();
          $new_image->content_id = $id;
          $new_image->parent_collection_id = (!empty($request_data['gid'])) ? $request_data['gid'] : -1;
          $new_image->delete($id);
          $success_delete = TRUE;
          $error_msg = 2004;
        }

        if($request_data['type'] == 'Audios') {
          $new_image = new Audio();
          $new_image->content_id = $id;
          $new_image->delete($id);
          $success_delete = TRUE;
          $error_msg = 2005;
        }

        if($request_data['type'] == 'Videos') {
          $new_image = new TekVideo();
          $new_image->content_id = $id;
          $new_image->delete_video($id);
          $success_delete = TRUE;
          $error_msg = 2006;
        }
      } catch (PAException $e) {
        $error_msg = "$e->message";
        $error = TRUE;
      }
    }
  }

  function set_vars() {
    global $login_uid, $network_info;
    /* For handling the Album according to thier type */
    switch ($this->type) {
      case 'Images' :
        $all_albums = Album::load_all($this->uid, IMAGE_ALBUM);
      break;
      case 'Videos' :
        $all_albums = Album::load_all($this->uid, VIDEO_ALBUM);
      break;
      case 'Audios':
        $all_albums = Album::load_all($this->uid, AUDIO_ALBUM);
      break;
      default : // Treating Images are the default parameters
      break;
    }
    /* setting all the album for this page */
    /* Retrive the All album of user */
    $album = array();
    $j=0;
    if(!empty($all_albums)) {
      foreach ($all_albums as $alb) {
        $album[$j]['id'] = $alb['collection_id'];
        $album[$j]['name'] = $alb['title'];
        $j++;
      }
      $this->default_album_id = @$album[0]['id'];
      $this->default_album_name = @$album[0]['name'];
      $this->my_all_album = $album;
    }
    /* For handling Users Friend Album*/
    if (isset($login_uid)) {
      /* Here we calculate all the relation (Friend's) Ids
      TODO: add a check to load it only when a user wants to see his friend's gallery
      */
/*
      $relations_ids = Relation::get_all_relations((int)$login_uid);
      $users = array();
      $users_ids = array();
      $users = Network::get_members(array('network_id'=>$network_info->network_id));

      if ( $users['total_users'] ) {
        for( $i = 0; $i < $users['total_users']; $i++) {
            $users_ids[] = $users['users_data'][$i]['user_id'];
        }
      }

      if (!empty($relations_ids)) {
        $cnt = count($relations_ids);
        for ($i = 0; $i < $cnt; $i++) {
          if (!in_array($relations_ids[$i]['user_id'], $users_ids)) {
            unset($relations_ids[$i]);
          }
        }
      }
      // extracting some name who are not member of that network ;)
*/
      $relations_ids = Relation::get_all_relations((int)PA::$login_uid, 0, FALSE, 'ALL', 0, 'created', 'DESC', 'internal', APPROVED, PA::$network_info->network_id);

      /* Here varify that users has any relation or not ... as well as loads all album */
      if (!empty($relations_ids)) {
        /* Here we get all the frnds list of login User */
        $frnd_albums = array();
        $i = 0;
        sortByFunc($relations_ids, create_function('$relations_ids','return $relations_ids["login_name"];'));
        foreach ($relations_ids as $frnd_id) {
          $frnd_list[$i]['name'] = $frnd_id['login_name'];
          $frnd_list[$i]['id'] = $frnd_id['user_id'];
          $i++;
        }
      }
    }// End of Friend Album

    $this->friend_list = @$frnd_list; // can be empty
  }
   /* For handling the Groups media gallery */
   function set_group_media_gallery () {
     global $login_uid;
     /* Retriving all the Groups of the User */
     // Loading user's all groups (whom user is a member)
     $this->group_ids = Group::get_user_groups ($login_uid);

   }

}
?>