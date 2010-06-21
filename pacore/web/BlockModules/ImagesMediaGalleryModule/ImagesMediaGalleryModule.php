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
 * File:        ImagesMediaGalleryModule.php, BlockModule file to generate Image Gallery
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class ImagesMediaGalleryModule which generates html of
 *              Images list for a Album- it is middle module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 *
 */
require_once PA::$blockmodule_path."/MediaGalleryModule/MediaGalleryModule.php";
require_once "api/Image/Image.php";

class ImagesMediaGalleryModule extends MediaGalleryModule {

    public $module_type = 'user|group|network';

    public $module_placement = 'middle';

    public $outer_template = 'outer_public_center_media_gallery_module.tpl';

    function __construct() {
        parent::__construct();
        $this->html_block_id = 'ImagesMediaGalleryModule';
    }

    public function initializeModule($request_method, $request_data) {
        $this->renderer->add_page_js(PA::$theme_url.'/javascript/jtip.js');
        $this->renderer->add_header_css(PA::$theme_url.'/jtip.css');
        $gallery_info = $this->shared_data['media_gallery'];
        $this->uid = PA::$login_uid;
        if(!empty($request_data['uid'])) {
            $this->uid = $request_data['uid'];
        }
        if(!empty(PA::$login_user)) {
            $this->title = User::get_login_name_from_id($this->uid)." - ";
        }
        else {
            $this->title = "";
        }
        $this->title    .= "Media Gallery - ".PA::$network_info->name;
        $this->show_view = $gallery_info['show_view'];
        $this->type      = $gallery_info['media_type'];
        $this->album_id  = $gallery_info['album_id'];
    }

    function render() {
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    function generate_inner_html() {
        global $login_uid;
        if(!isset($_GET['gid']) || empty($_GET['gid'])) {
            parent::set_vars();
            $frnd_list = null;
            if(!empty($_GET['view'])) {
                $frnd_list = $this->friend_list;
            }
            $sb_images = array();
            $new_album = new Album(IMAGE_ALBUM);
            if($this->album_id) {
                $new_album = new Album();
                $new_album->album_type = IMAGE_ALBUM;
                $new_album->load($this->album_id);
                $image_data['album_id'] = $new_album->collection_id;
                $image_data['album_name'] = $new_album->title;
            }
            else {
                $new_album->collection_id = $this->default_album_id;
                $image_data['album_id']   = $this->default_album_id;
                $image_data['album_name'] = $this->default_album_name;
            }
            $image_ids = $new_album->get_contents_for_collection();
            $k         = 0;
            $ids       = array();
            if(!empty($image_ids)) {
                for($i = 0; $i < count($image_ids); $i++) {
                    if($image_ids[$i]['type'] != 7) {
                        // Type 7 is for SB Content
                        $ids[$i] = $image_ids[$i]['content_id'];
                    }
                    else {
                        $var = $image_ids[$i]['body'];
                        $start = strpos($var, '<image>')+7;
                        if($start > 7) {
                            $end       = strpos($var, '</image>');
                            $image_src = substr($var, $start, $end-$start);
                            $tags      = Tag::load_tags_for_content($image_ids[$i]['content_id']);
                            $tags      = show_tags($tags, null);
                            //show_tags function is defined in uihelper.php
                            $sb_images[] = array(
                                'content_id' => $image_ids[$i]['content_id'],
                                'title'      => $image_ids[$i]['title'],
                                'type'       => $image_ids[$i]['type'],
                                'created'    => $image_ids[$i]['created'],
                                'tags'       => $tags,
                                'image_src'  => $image_src,
                            );
                        }
                    }
                }
                $new_image = new Image();
                $data = $new_image->load_many($ids, $this->uid, $login_uid);
                if(count($data) > 0) {
                    foreach($data as $d) {
                        $image_data[$k]['content_id']    = $d['content_id'];
                        $image_data[$k]['image_file']    = $d['image_file'];
                        $image_data[$k]['image_caption'] = $d['image_caption'];
                        $image_data[$k]['title']         = $d['title'];
                        $image_data[$k]['body']          = $d['body'];
                        $image_data[$k]['created']       = $d['created'];
                        $image_data[$k]['type']          = "";
                        $tags_array                      = Tag::load_tags_for_content($d['content_id']);
                        $tags_string                     = "";
                        $tags_string                     = show_all_contents_for_tag($tags_array);
                        $image_data[$k]['tags']          = $tags_string;
                        $k++;
                    }
                }
                // Merging Media Gallery content and SB Content
                for($counter = 0; $counter < count($sb_images); $counter++) {
                    $image_data[$k]['content_id']    = $sb_images[$counter]['content_id'];
                    $image_data[$k]['title']         = $sb_images[$counter]['title'];
                    $image_data[$k]['type']          = $sb_images[$counter]['type'];
                    $image_data[$k]['image_caption'] = $sb_images[$counter]['title'];
                    $image_data[$k]['created']       = $sb_images[$counter]['created'];
                    $image_data[$k]['tags']          = $sb_images[$counter]['tags'];
                    $image_data[$k]['image_src']     = $sb_images[$counter]['image_src'];
                    $k++;
                }
            }
            if(!empty($_GET['view'])) {
                if(empty($frnd_list)) {
                    $image_data = NULL;
                }
            }
            $inner_template = NULL;
            switch($this->mode) {
                default:
                    $inner_template = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_public.tpl';
            }
            $obj_inner_template = &new Template($inner_template);
            $obj_inner_template->set('links', $image_data);
            $obj_inner_template->set('frnd_list', $frnd_list);
            $obj_inner_template->set('uid', $this->uid);
            $obj_inner_template->set('my_all_album', $this->my_all_album);
            $obj_inner_template->set('show_view', $this->show_view);
            $inner_html = $obj_inner_template->fetch();
            return $inner_html;
        }
        // End of IF Condition
        else {
            parent::set_group_media_gallery();
            //------------- Handling the Groups Media gallery -----------
            $group      = ContentCollection::load_collection((int) $_GET['gid'], $_SESSION['user']['id']);
            $image_data = Image::load_images_for_collection_id($_GET['gid'], $limit = 0);
            $i          = 0;
            if(!empty($image_data)) {
                foreach($image_data as $data) {
                    $tags_array             = Tag::load_tags_for_content($data['content_id']);
                    $tags_string            = "";
                    $tags_string            = show_all_contents_for_tag($tags_array);
                    $image_data[$i]['tags'] = $tags_string;
                    $i++;
                }
            }
            $image_data['album_id']   = $group->collection_id;
            $image_data['album_name'] = $group->title;
            $inner_template           = NULL;
            switch($this->mode) {
                default:
                    $inner_template = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_public_groups.tpl';
            }
            $obj_inner_template = &new Template($inner_template);
            $obj_inner_template->set('links', $image_data);
            $obj_inner_template->set('my_all_groups', $this->group_ids);
            $obj_inner_template->set('show_view', $this->show_view);
            $inner_html = $obj_inner_template->fetch();
            return $inner_html;
        }
    }
}
?>
