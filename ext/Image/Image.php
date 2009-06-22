<?php
include_once dirname(__FILE__)."/../../config.inc";
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/Content/Content.php";
require_once "api/Logger/Logger.php";
require_once "api/Relation/Relation.php";


 


  

/**
 * Implements Image.
 * @extends Content
 * @author Tekriti Software (www.TekritiSoftware.com)
 */
class Image extends Content {

  public $file_name;
  public $file_perm;
  /**
   * class Content::__construct
   */
   public function __construct(){
     parent::__construct();
     //TODO: move to constants
     $this->type = IMAGE;
     $this->is_html = 0;
   }

    /**
   * load Image data in the object
   * @access public
   * @param content id of the Image.
   * @param $user_id user id of the person who is accessing data
   * @param $my_user_id user id of the person whose data is loading
   */
  public function load($content_id, $user_id=0, $my_user_id=0) {
    Logger::log("Enter: Image::load | Arg: \$content_id = $content_id");
    Logger::log("Calling: Content::load | Param: \$content_id = $content_id");

    parent::load($content_id);
    $sql = "SELECT * FROM {images} WHERE content_id = $this->content_id";
    $res = Dal::query($sql);
    if ($res->numRows() > 0) {
      $row = $res->fetchRow(DB_FETCHMODE_OBJECT);

      if ($my_user_id != 0) {
        // getting degree 1 friendlist
        $relations = Relation::get_relations($user_id, APPROVED, PA::$network_info->network_id);
        if ($user_id == $my_user_id) {
          $this->image_file = $row->image_file;
          $this->file_name = $row->image_file;
          $this->file_perm = $row->image_perm;
        }
        elseif (in_array($my_user_id, $relations)) {
          if (($row->image_perm == WITH_IN_DEGREE_1) || ($row->image_perm == ANYONE)) {
            $this->image_file = $row->image_file;
            $this->file_name = $row->image_file;
            $this->file_perm = $row->image_perm;
          }
        }
        elseif ($my_user_id == 0) {
          if (($row->image_perm == WITH_IN_DEGREE_1) || ($row->image_perm == ANYONE)) {
            $this->image_file = $row->image_file;
            $this->file_name = $row->image_file;
            $this->file_perm = $row->image_perm;
          }
        }
        else {
          if ($row->image_perm == ANYONE) {
            $this->image_file = $row->image_file;
            $this->file_name = $row->image_file;
            $this->file_perm = $row->image_perm;
          }
        }
      }
      else if ($user_id == $my_user_id) {
        $this->image_file = $row->image_file;
        $this->file_name = $row->image_file;
        $this->file_perm = $row->image_perm;
      }
      else {
        if ($row->image_perm == ANYONE) {
           $this->image_file = $row->image_file;
           $this->file_name = $row->image_file;
           $this->file_perm = $row->image_perm;
        }
      }

    }

    Logger::log("Exit: Image::load");
    return;
  }


  /**
   * Load Image data by taking an array of content ids
   * @access public
   * @param $array_cids an array of Image Ids.
   * @param $user_id user id of the person who is accessing data
   * @param $my_user_id user id of the person whose data is loading
   * @return $images, an associative array having aal data of images
   */

 // TO DO: Can be made static method
  public function load_many($array_cids, $user_id=0, $my_user_id=0) {
    Logger::log("Enter: Image::load_many | Arg: \$content_ids = ".implode(",", $array_cids));

    // TO Do: In the one query (wite query in the one string)
    $i = 0;
    $image = array();
    foreach ($array_cids as $cid) {
      $sql = "SELECT * FROM {contents} AS C, {images} as I WHERE C.content_id = I.content_id AND I.content_id = ? AND C.is_active = ?";
      $res = Dal::query($sql, array($cid, ACTIVE));      
            
      if ($res->numRows() > 0) {
        $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
        $image[$i]['content_id'] = $row->content_id;
        $image[$i]['image_file'] = $row->image_file;
        $image[$i]['image_caption'] = $row->title;
        $image[$i]['title'] = $row->title;
        $image[$i]['body'] = $row->body;
        $image[$i]['created'] = $row->created;
        $image[$i]['collcetion_id'] = $row->collection_id;
        $image[$i]['perm'] = $row->image_perm;
      }
      $i++;
      // loading tags for media

    }

    if ((count($image) > 0) && ($user_id != 0)) {
      // getting degree 1 friendlist
      $relations = Relation::get_relations($user_id, APPROVED, PA::$network_info->network_id);
      if ($user_id == $my_user_id) {
        $user_image_data = $image;
      }
      elseif (in_array($my_user_id, $relations)) {
        foreach ($image as $user_data) {
          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
            $user_image_data[] = $user_data;
          }
        }
      }
      elseif ($my_user_id == 0) {
        foreach ($image as $user_data) {
          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
            $user_image_data[] = $user_data;
          }
        }
      }
      else {
        foreach ($image as $user_data) {
          if ($user_data['perm'] == ANYONE) {
            $user_image_data[] = $user_data;
          }
        }
      }
    }
    else if ($user_id == $my_user_id) {
      $user_image_data = $image;
    }
    
    Logger::log("Exit: Image::load_many()");
    return $user_image_data;
  }



  /**
   * Saves Image in database
   * @access public
   */
  public function save() {
    Logger::log("Enter: Image::save_image");
    Logger::log("Calling: Content::save");

    //print '<pre>'; print_r($this); exit;
    if ($this->content_id) {
       parent::save();
       // if no permission is set then it is Nobody by default
       $this->file_perm = (empty($this->file_perm)) ? NONE : $this->file_perm;        
       $sql = "UPDATE {images} SET image_perm = ? WHERE content_id = ?";
       $data = array($this->file_perm, $this->content_id);
       $res = Dal::query($sql, $data);
       

       if ($this->file_name) {
         $sql = "UPDATE {images} SET image_file = ? WHERE content_id = ?";
         $data = array($this->file_name, $this->content_id);
         $res = Dal::query($sql, $data);
       }
    }
    else {
      $this->display_on = NULL; // carefull here this is NOT '0'
      parent::save();
      if (!$this->file_perm) {
        $this->file_perm = 0;
      }
      $sql = "INSERT INTO {images} (content_id, image_file, image_perm) VALUES (?, ?, ?)";
      $data = array($this->content_id, $this->file_name, $this->file_perm);
      $res = Dal::query($sql, $data);
    }
    //print '<pre>'; print_r($this); exit;

    Logger::log("Exit: Image::save_image");
    return $this->content_id;
  }



  /**
  * calls Content::delete() to soft delete a content
  * Soft delete is being performed here
  * set $this->content_id at the time of calling this method
  */
  public function delete () {
    Logger::log("Enter: Image::delete");
    Logger::log("Calling: Content::delete");
    parent::delete();
    Logger::log("Exit: Image::delete");
    return;
  }


   /**
   *  Load all Images with its data for a single user.
   * @param $user_id, the user id of the user whose images to be loaded.
   * @param $limit, limit how many images should be loaded. If no value is set then it will load all images
   * @param $user_id user id of the person who is accessing data
   * @param $my_user_id user id of the person whose data is loading
   * @return $image_data, an associative array, having content_id, image_file, title, body in it for each image.
   */
   public static function load_images($user_id=0, $limit = 0, $my_user_id=0) {
     Logger::log("Enter: Image::load_images | Arg: \$user_id = $user_id");
     Logger::log("Calling: Content::load | Param: \$limit = $limit");
     Logger::log("Calling: Content::load | Param: \$my_user_id = $my_user_id");

     $i = 0;
     if  ($user_id==0) {
       $sql = "SELECT * FROM {contents} AS C, {images} as I WHERE C.content_id = I.content_id AND I.image_perm = ? AND C.is_active = ? ORDER by C.created DESC ";
       if ($limit != 0) {
         $sql .= "LIMIT $limit";
       }
       $data = array(ANYONE, 1);
     }
     else {
      $sql = "SELECT * FROM {contents} AS C, {images} as I WHERE C.content_id = I.content_id AND C.author_id = ? AND C.is_active = ? ORDER by C.created DESC ";
      if ($limit != 0) {
        $sql .= "LIMIT $limit";
      }
      $data = array($user_id, 1);
     }
     $res = Dal::query($sql, $data);
     $image_data = array();
     if ($res->numRows() > 0) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $image_data[$i]['content_id'] = $row->content_id;
         $image_data[$i]['image_file'] = $row->image_file;
         $image_data[$i]['caption'] = $row->title;
         $image_data[$i]['title'] = $row->title;
         $image_data[$i]['body'] = $row->body;
         $image_data[$i]['created'] = $row->created;
         $image_data[$i]['collection_id'] = $row->collection_id;
         $image_data[$i]['perm'] = $row->image_perm;
         $image_data[$i]['author_id'] = $row->author_id;
         $i++;
       }
     }
     $user_image_data = array(); 
     if (!empty($image_data) && ($my_user_id != 0)) {
      // getting degree 1 friendlist
      $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
      if ($user_id == $my_user_id) {
        $user_image_data = $image_data;
      }
      elseif (in_array($my_user_id, $relations)) {
        foreach ($image_data as $user_data) {
          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
            $user_image_data[] = $user_data;
          }
        }
      }
      elseif ($my_user_id == 0) {
        foreach ($image_data as $user_data) {
          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
            $user_image_data[] = $user_data;
          }
        }
      }
      else {
        foreach ($image_data as $user_data) {
          if ($user_data['perm'] == ANYONE) {
            $user_image_data[] = $user_data;
          }
        }
      }
    }
    else if (($user_id == $my_user_id) && ($my_user_id != 0)) {
      $user_image_data = $image_data;
    }
    else if ($my_user_id == 0 && (!empty($image_data))) {
      foreach ($image_data as $user_data) {
        if ($user_data['perm'] == ANYONE) {
          $user_image_data[] = $user_data;
        }
      }
    }

     Logger::log("Exit: Image::load_images");
     return $user_image_data;
   }

   /**
   *  Load all Images with its data for a single user. with images album name
   * @param $user_id, the user id of the user whose images to be loaded.
   * @param $limit, limit how many images should be loaded. If no value is set then it will load all images
   * @return $image_data, an associative array, having content_id, image_file, title, body in it for each image.
   */
   public static function load_images_with_collection_name($user_id, $limit = 0) {
     Logger::log("Enter: Image::load_images | Arg: \$content_id = $content_id");
     Logger::log("Calling: Content::load | Param: \$content_id = $content_id");

     $i = 0;
     $sql = "SELECT *, C.title as title, C.body as body, CC.title as c_title FROM {contents} AS C, {images} as I, {contentcollections} as CC WHERE C.content_id = I.content_id AND C.author_id = ? AND CC.collection_id = C.collection_id AND C.is_active = ? ORDER by rand() ";
     if ($limit != 0) {
       $sql .= "LIMIT $limit";
     }
     $data = array($user_id, 1);
     $res = Dal::query($sql, $data);
     if ($res->numRows() > 0) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $image_data[$i]['content_id'] = $row->content_id;
         $image_data[$i]['image_file'] = $row->image_file;
         $image_data[$i]['caption'] = $row->title;
         $image_data[$i]['title'] = $row->title;
         $image_data[$i]['body'] = $row->body;
         $image_data[$i]['created'] = $row->created;
         $image_data[$i]['collection_id'] = $row->collection_id;
         $image_data[$i]['collection_title'] = $row->c_title;
         $i++;
       }
     }

     Logger::log("Exit: Image::load_images");
     return $image_data;
   }


   /**
   *  Load all Images with its data for a single $collection_id or group id.
   * @param $collection_id, the collection id whose images to be loaded.
   * @param $limit, limit how many images should be loaded. If no value is set then it will load all images
   * @return $image_data, an associative array, having content_id, image_file, title, body in it for each image.
   */
   public static function load_images_for_collection_id ($collection_id, $limit = 0, $order = "RAND()") {
     Logger::log("Enter: Image::load_images | Arg: \$collection_id = $collection_id");
     Logger::log("Calling: Content::load | Param: \$collection_id = $collection_id");

     $i = 0;
     $sql = "SELECT * FROM {contents} AS C, {images} as I WHERE C.content_id = I.content_id AND C.collection_id  = ? AND C.is_active = ? ORDER BY $order ";
     if ($limit != 0) {
       $sql .= "LIMIT $limit";
     }
     $data = array($collection_id, 1);
     $res = Dal::query($sql, $data);
     //p($res);
     $image_data = array();
     if ($res->numRows() > 0) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $image_data[$i]['content_id'] = $row->content_id;
         $image_data[$i]['image_file'] = $row->image_file;
         $image_data[$i]['image_caption'] = $row->title;
         $image_data[$i]['title'] = $row->title;
         $image_data[$i]['author_id'] = $row->author_id;
         $image_data[$i]['body'] = $row->body;
         $image_data[$i]['created'] = $row->created;
         $image_data[$i]['collection_id'] = $row->collection_id;
         $image_data[$i]['type'] = $row->type;
         $i++;
       }
     }

     Logger::log("Exit: Image::load_images");
     return $image_data;
   }
   
   
   /**
   * function to delete user images.
   */
   public static function delete_user_images ( $user_id, $collection_id = NULL) {
     Logger::log("Enter: Image::delete_user_images");
     
     $sql = 'DELETE FROM C, I, TC USING {contents} AS C, {images} AS I LEFT JOIN {tags_contents} AS TC ON C.content_id = TC.content_id WHERE C.content_id = I.content_id AND C.author_id = ?';
     $data = array( $user_id );
     
     if( $collection_id ) {
       $sql .= ' AND C.collection_id = ? ';
       $data[] = $collection_id;
     }
     
     if( !$res = Dal::query($sql, $data) ) {
       Logger::log("Image::delete_user_images function failed");
       throw new PAException(IMAGE_DELETE_FAILED, "User images delete failed");
     }
     
     Logger::log("Exit: Image::delete_user_images");
     return $res;
   }

  public static function load_recent_media_image($user_id=0, $my_user_id=0){
    Logger::log("Enter: Image::load_recent_media_image | Arg: \$user_id = $user_id");
    Logger::log("Calling: Content::load | Param: \$my_user_id = $my_user_id");

     $i = 0;
     if  ($user_id==0) {
       $sql = "SELECT * FROM {contents} AS C, {images} as I,{recent_media_track} as R WHERE C.content_id = I.content_id AND I.content_id=R.cid  AND R.type = ? AND I.image_perm = ? AND C.is_active = ? ORDER by C.created DESC ";
       
       $data = array(IMAGE,ANYONE, 1);
     }
     else {
      $sql = "SELECT * FROM {contents} AS C, {images} as I , {recent_media_track} as R WHERE C.content_id = I.content_id AND I.content_id=R.cid  AND R.type = ? AND C.author_id = ? AND C.is_active = ? ORDER by C.created DESC ";
      
      $data = array(IMAGE,$user_id, 1);
     }
     $res = Dal::query($sql, $data);
     if ($res->numRows() > 0) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $image_data[$i]['content_id'] = $row->content_id;
         $image_data[$i]['image_file'] = $row->image_file;
         $image_data[$i]['caption'] = $row->title;
         $image_data[$i]['title'] = $row->title;
         $image_data[$i]['body'] = $row->body;
         $image_data[$i]['created'] = $row->created;
         $image_data[$i]['collection_id'] = $row->collection_id;
         $image_data[$i]['perm'] = $row->image_perm;
         $image_data[$i]['author_id'] = $row->author_id;
         $i++;
       }
     }
     $user_image_data = array(); 
     if (!empty($image_data) && ($my_user_id != 0)) {
      // getting degree 1 friendlist
      $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
      if ($user_id == $my_user_id) {
        $user_image_data = $image_data;
      }
      elseif (in_array($my_user_id, $relations)) {
        foreach ($image_data as $user_data) {
          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
            $user_image_data[] = $user_data;
          }
        }
      }
      elseif ($my_user_id == 0) {
        foreach ($image_data as $user_data) {
          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] == ANYONE)) {
            $user_image_data[] = $user_data;
          }
        }
      }
      else {
        foreach ($image_data as $user_data) {
          if ($user_data['perm'] == ANYONE) {
            $user_image_data[] = $user_data;
          }
        }
      }
    }
    else if (($user_id == $my_user_id) && ($my_user_id != 0)) {
      $user_image_data = $image_data;
    }
    else if ($my_user_id == 0 && (!empty($image_data))) {
      foreach ($image_data as $user_data) {
        if ($user_data['perm'] == ANYONE) {
          $user_image_data[] = $user_data;
        }
      }
    }

     Logger::log("Exit: Image::load_recent_media_image");
     return $user_image_data;
   }

   /**
   *  Load all  Images of user gallery with its data for a single user.
   * @param $user_id, the user id of the user whose images to be loaded.
   * @param $limit, limit how many images should be loaded. If no value is set
     then it will load all images
   * @param $user_id user id of the person who is accessing data
   * @param $my_user_id user id of the person whose data is loading
   * @return $image_data, an associative array, having content_id, image_file,
     title, body in it for each image.
   */
   public static function load_user_gallery_images($user_id=0, $limit = 0,
   $my_user_id=0) {
     Logger::log("Enter: Image::load_user_gallery_images");

     $i = 0;
     if  ($user_id==0) {
       $sql = "SELECT * FROM {contents} AS C, {images} as I,
       {contentcollections} as CC WHERE C.content_id = I.content_id AND
       CC.collection_id = C.collection_id AND I.image_perm = ? AND C.is_active
       = ? AND CC.type = ? ORDER by C.created DESC ";
       if ($limit != 0) {
         $sql .= "LIMIT $limit";
       }
       $data = array(ANYONE, ACTIVE, ALBUM_COLLECTION_TYPE);
     } else {
      $sql = "SELECT * FROM {contents} AS C, {images} as I, {contentcollections}
      as CC WHERE C.content_id = I.content_id AND CC.collection_id =
      C.collection_id AND C.author_id = ? AND C.is_active = ? AND
      CC.type = ? ORDER by C.created DESC ";
      if ($limit != 0) {
        $sql .= "LIMIT $limit";
      }
      $data = array($user_id, ACTIVE, ALBUM_COLLECTION_TYPE);
     }
     $res = Dal::query($sql, $data);
     $image_data = array();
     if ($res->numRows() > 0) {
       while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
         $image_data[$i]['content_id'] = $row->content_id;
         $image_data[$i]['image_file'] = $row->image_file;
         $image_data[$i]['caption'] = $row->title;
         $image_data[$i]['title'] = $row->title;
         $image_data[$i]['body'] = $row->body;
         $image_data[$i]['created'] = $row->created;
         $image_data[$i]['collection_id'] = $row->collection_id;
         $image_data[$i]['perm'] = $row->image_perm;
         $image_data[$i]['author_id'] = $row->author_id;
         $i++;
       }
     }
     $user_image_data = array(); 
     if (!empty($image_data) && ($my_user_id != 0)) {
      // getting degree 1 friendlist
      $relations = Relation::get_relations($my_user_id, APPROVED, PA::$network_info->network_id);
      if ($user_id == $my_user_id) {
        $user_image_data = $image_data;
      } elseif (in_array($my_user_id, $relations)) {
        foreach ($image_data as $user_data) {
          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] ==
          ANYONE)) {
            $user_image_data[] = $user_data;
          }
        }
      } elseif ($my_user_id == 0) {
        foreach ($image_data as $user_data) {
          if (($user_data['perm'] == WITH_IN_DEGREE_1) || ($user_data['perm'] ==
          ANYONE)) {
            $user_image_data[] = $user_data;
          }
        }
      } else {
        foreach ($image_data as $user_data) {
          if ($user_data['perm'] == ANYONE) {
            $user_image_data[] = $user_data;
          }
        }
      }
    } else if (($user_id == $my_user_id) && ($my_user_id != 0)) {
      $user_image_data = $image_data;
    } else if ($my_user_id == 0 && (!empty($image_data))) {
      foreach ($image_data as $user_data) {
        if ($user_data['perm'] == ANYONE) {
          $user_image_data[] = $user_data;
        }
      }
    }

     Logger::log("Exit: Image::load_user_gallery_images");
     return $user_image_data;
   }   

}
?>
