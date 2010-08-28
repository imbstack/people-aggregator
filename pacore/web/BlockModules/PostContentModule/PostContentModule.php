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

require_once "api/Category/Category.php";
require_once "api/ContentCollection/ContentCollection.php";
require_once "api/Group/Group.php";


class PostContentModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_single_module.tpl';
  
  public $uid;
  public $content_type;
  public $targets;
  public $tags = NULL;
  public $show_external_blogs, $outputthis_error_mesg;
  public $album_type = IMAGE_ALBUM;
  public $err_album_name_exist;

  function __construct() {
    parent::__construct();
    $this->html_block_id = 'PostContentModule';
    $this->id = 0;
  }
  
  function set_id($id) {
    $this->id = $id;
  }
  
  function set_sb_vars($sb_mc_type,$content_id, $ccid) {
    $this->sb_mc_type = $sb_mc_type;
    $this->content_id = $content_id;
    $this->ccid = $ccid;
  }
  
  function render() {
    
    $this->inner_HTML = $this->generate_inner_html(); // $this->links
    $content = parent::render();
    return $content;
  }

  function generate_inner_html() {
		if($this->id) {
			$this->load_data();
		}
		if ($this->ccid > 0) {
			// load that group so we can check it's access and reg type
			$this->group = Group::load_group_by_id((int)$this->ccid);

		}
		$inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_blog.tpl';
    
    $inner_html_blog = new Template($inner_template);
    $inner_html_blog->set_object('network_info', PA::$network_info);
    $inner_html_blog->set('current_theme_path', PA::$theme_url);
    // $inner_html_blog->set('links', $this->links);
    $inner_html_blog->set('cid', $this->id);
    $inner_html_blog->set('ccid', $this->ccid);
    $inner_html_blog->set('parent_collection_id', $this->parent_collection_id);

    // some or most of the following can be empty so we use the @
    $inner_html_blog->set('blog_title', str_replace('"','&quot;',@$this->blog_title));
    $inner_html_blog->set('body', @$this->body);
    $inner_html_blog->set('trackback', @$this->trackback);
    $inner_html_blog->set('tag_entry', @$this->tag_entry);
    $inner_html_blog->set('error_msg', @$this->error_msg);
    $center_content = $inner_html_blog->fetch();


	    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private_simple.tpl';

    $inner_html_gen = new Template($inner_template);
    $inner_html_gen->set_object('network_info', PA::$network_info);
    $inner_html_gen->set('current_theme_path', PA::$theme_url);
    /*$inner_html_gen->set('title', $this->title);*/
    // $inner_html_gen->set('links', $this->links);
    $inner_html_gen->set('sb_mc_type', @$this->sb_mc_type);
    $inner_html_gen->set('sb_types', @$this->sb_types);
    $inner_html_gen->set('center_content', $center_content);
    $inner_html_gen->set('display', $this->display);
    $inner_html_gen->set('targets', $this->targets);
    $inner_html_gen->set('show_external_blogs', $this->show_external_blogs);
    $inner_html_gen->set('user_groups', $this->get_user_groups());
    
    $inner_html_gen->set('group_access', @$this->group->access_type);
    $inner_html_gen->set('group_reg', @$this->group->rdg_type);
    
    $inner_html_gen->set('album_type', $this->album_type);
    $inner_html_gen->set('outputthis_error_mesg', @$this->outputthis_error_mesg);
    $inner_html_gen->set('error_msg', @$this->error_msg);
    if($this->album_type != -1) {
        $inner_html_gen->set('user_albums', $this->get_user_albums());
    }
    $inner_html_gen->set('permission_to_post', $this->permission_to_post);
    $inner_html_gen->set('is_edit', $this->is_edit);
    $inner_html_gen->set('ccid', $this->ccid);
    $inner_html = $inner_html_gen->fetch();
    
    return $inner_html;
  }
  
  function getContent()
  {
     

    $mc = new SBMicroContent();
    $mc->set_mc_type($this->sb_mc_type);
    $edit = $instance = null;

    // if we've clicked an 'edit' button, parse the existing
    // structured content and pre-fill the form.  note that we don't
    // do this on POST, as in that case all the data we want is in
    // $_POST.
    if ($this->content_id && $_SERVER['REQUEST_METHOD'] == "GET") {
        $mc->load($this->content_id);
        $body = $mc->body;
        $edit = "edit";
        $instance = $mc->parse_structured_content($body);
	if (!$instance) throw new PAException(GENERAL_SOME_ERROR, "Failed to parse structured blogging content in content id $this->content_id");
    }

    $editor = $mc->get_mc_editor($edit, $instance);
    return $editor;
  }
  
  function load_data($error_msg='') {
     
    $this->categories = Category::build_all_category_list();
    if (!empty($error_msg)) {
      $this->error_msg = $error_msg;
    }
    if ($this->id == 0) {
      $this->title = __('Add Blog Post');
      return;
    } else {
      $this->title = '';
      $content = Content::load_content((int)$this->id, $_SESSION['user']['id']);
      $content_tags = Tag::load_tags_for_content((int)$this->id);
      $this->blog_title = stripslashes($content->title);
      $this->body = stripslashes($content->body);
      $this->trackback = $content->trackbacks;
      $this->collection_id = @$content->collection_id;
      if (count($content_tags)) {
        foreach ($content_tags as $tag) {
          $out[] = $tag['name'];
        }
        $this->tag_entry = implode(', ',$out);
      }      
    } 
  }
  
  function set_error_msg($error, $data_array="") {
    $this->error_msg = (isset($error)) ? $error : '';
    $this->err_album_name_exist = (isset($err_album_name_exist)) ? $err_album_name_exist : '';
    $this->blog_title = (isset($data_array["blog_title"])) ? $data_array["blog_title"] : '';
    $this->body = (isset($data_array["description"])) ? $data_array["description"] : ''; 
    $this->trackback = (isset($data_array["trackback"])) ? $data_array["trackback"] : ''; 
    $this->tag_entry = (isset($data_array["tags"])) ? $data_array["tags"] : ''; 
    
  }
  
  function get_user_groups() {
    $groups = Group::get_user_groups($_SESSION['user']['id'], FALSE, 'ALL');
    for ( $i=0; $i<count($groups);$i++ ) {
      if ( $groups[$i]['access'] == 'owner' ) {
        $this->user_groups[] = array('gid'=>$groups[$i]['gid'],'name'=>stripslashes($groups[$i]['name']));
      }
    }
    return $groups;
  }
  
  function get_user_albums() {
    switch($this->album_type) {
        case IMAGE_ALBUM;
           $caption = __('Default Image Album');
          break; 
        case AUDIO_ALBUM;
           $caption = __('Default Audio Album');
          break;
        case VIDEO_ALBUM;
           $caption = __('Default Video Album');
          break;                    
    }
    $albums = Album::load_all($_SESSION['user']['id'], $this->album_type);
    if(count($albums) == 0) {
        $Album = new Album($this->album_type);
          $Album->author_id = $_SESSION['user']['id'];
          $Album->type = 2;
          $Album->title = $caption;
          $Album->name = $caption;
          $Album->description = $caption;
          $Album->save();
          $albums = Album::load_all($_SESSION['user']['id'], $this->album_type);
    }
    return $albums;
  }
  
  function get_tags_for_content() {
      $tags_array = Tag::load_tags_for_content($this->content_id);
      $tags_string= "";
      if(count($tags_array) > 0) {
          for($counter = 0; $counter < count($tags_array); $counter++) {
              $tags_string .= $tags_array[$counter]['name'].", ";
          }
          $tags_string = substr($tags_string, 0, strlen($tags_string) - 2);
      }
      return $tags_string;
  }
}
?>