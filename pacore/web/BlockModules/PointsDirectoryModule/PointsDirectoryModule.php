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
error_reporting(E_ALL);
// require_once "api/...";
// require_once "web/includes/classes/...";
require_once 'api/Entity/PointsEntity.php';
require_once 'api/Entity/FamilyTypedGroupEntity.php';
require_once 'api/Entity/TypedGroupEntityRelation.php';

class PointsDirectoryModule extends Module {

    public $module_type = 'user';

    public $module_placement = 'middle';

    function __construct() {
        parent::__construct();
        $this->title = null;
        $this->user_id = null;
        $this->class_name = get_class($this);
        $this->sub_title = sprintf(__('%s Points Directory'), PA::$network_info->name);
        $this->edit_perm = false;
        $this->limit = 5;
        $this->outer_template = "outer_public_center_module.tpl";
    }

    private function check_edit_perm() {
        return((($this->relType == 'parent') || ($this->relType == 'grand parent')) ? true : false);
        // for now: simple edit perm
    }

    private function get_familly_members($familly) {
        $fam_members = $familly->get_members(false, 'ALL', 0, 'created', 'DESC', true);
        foreach($fam_members as &$member) {
            list($relType, $relLabel) = TypedGroupEntityRelation::get_relation_to_group($member['user_id'], $familly->collection_id);
            if(empty($relType)) {
                $relType = 'member';
            }
            $member['member_type'] = $relType;
            $muser = new User();
            $muser->load((int) $member['user_id']);
            $member['user'] = $muser;
        }
        //      echo "<pre>" . print_r($fam_members,1) . "</pre>";
        return $fam_members;
    }

    function initializeModule($request_method, $request_data) {
        $this->action = (!empty($request_data['faction'])) ? $request_data['faction'] : null;
        $this->module = (!empty($request_data['module'])) ? $request_data['module'] : null;
        $this->page = (empty($request_data['page'])) ? 1 : (int) $request_data['page'];
        $this->category = (empty($request_data['category'])) ? null : $request_data['category'];
        $this->criteria = array(
            'network_id' => PA::$network_info->network_id,
        );
        $this->categories = PointsEntity::get_cetegories();
        $this->renderer->add_header_css('/'.PA::$theme_rel.'/points_directory.css');
        $this->relType = 'guest';
        if((!empty($request_data['fid'])) || (!empty($request_data['gid']))) {
            if(!empty($request_data['fid'])) {
                $this->criteria['family_id'] = $request_data['fid'];
            }
            elseif(!empty($request_data['gid'])) {
                $this->criteria['family_id'] = $request_data['gid'];
            }
            $this->fid = (!empty($this->criteria['family_id'])) ? $this->criteria['family_id'] : null;
            list($relType, $relLabel) = TypedGroupEntityRelation::get_relation_to_group(PA::$login_uid, (int) $this->fid);
            if(empty($relType)) {
                $relType = 'member';
            }
            $this->relType = $relType;
            $this->familly = ContentCollection::load_collection((int) $this->fid, PA::$login_uid);
            $this->fam_members = $this->get_familly_members($this->familly);
            $this->edit_perm = $this->check_edit_perm();
            $this->sub_title = sprintf(__('%s Points'), $this->familly->title.' Family');
            // this string should be replaced with $family->name
        }
        if(!empty($request_data['uid'])) {
            $this->user_id = $request_data['uid'];
            $this->sub_title = sprintf(__('Points for %s'), PA::$user->display_name);
            $this->criteria['user_id'] = $request_data['uid'];
        }
        if(!empty($this->category)) {
            $this->sub_title = sprintf(__('Search results for \'%s\' Category'), $this->category);
            $this->criteria['category'] = $this->category;
        }
        switch($this->page_id) {
            case PAGE_FAMILY:
                $this->url_base = PA::$url.PA_ROUTE_FAMILY."?gid=$this->fid";
                break;
            case PAGE_FAMILY_DIRECTORY:
                $this->url_base = PA::$url.PA_ROUTE_FAMILY_DIRECTORY;
                break;
            case PAGE_POINTS_DIRECTORY:
                if(isset($this->fid)) {
                    $this->url_base = PA::$url.PA_ROUTE_FAMILY."?gid=$this->fid";
                }
                else {
                    $this->url_base = PA::$url.PA_ROUTE_POINTS_DIRECTORY."?uid=$this->user_id";
                }
                break;
        }
        if($request_method == 'GET') {
            //echo "Action: " . $this->action . "Module: " . $this->module . "clname: " . $this->class_name; die();
            if(!empty($this->action) && ($this->module == $this->class_name)) {
                switch($this->action) {
                    case 'newPoints':
                        $this->view_mode = 'new';
                        $this->sub_title = __('Add Points');
                        $this->set_inner_template('edit_points_form.tpl.php');
                        $this->renderer->add_header_css('/'.PA::$theme_rel.'/modal.css');
                        $this->renderer->add_page_js('/'.PA::$theme_rel.'/javascript/forms.js');
                        $this->renderer->add_page_js('/'.PA::$theme_rel.'/javascript/jquery.validate.js');
                        $this->renderer->add_page_js('/'.PA::$theme_rel.'/javascript/attach_media_modal.js');
                        $this->renderer->add_page_js('/'.PA::$theme_rel.'/javascript/points_directory.js');
                        break;
                    case 'editPoints':
                        $this->view_mode = 'edit';
                        $this->sub_title = __('Edit Points');
                        $this->set_inner_template('edit_points_form.tpl.php');
                        $this->renderer->add_header_css('/'.PA::$theme_rel.'/modal.css');
                        $this->renderer->add_page_js('/'.PA::$theme_rel.'/javascript/forms.js');
                        $this->renderer->add_page_js('/'.PA::$theme_rel.'/javascript/jquery.validate.js');
                        $this->renderer->add_page_js('/'.PA::$theme_rel.'/javascript/attach_media_modal.js');
                        $this->renderer->add_page_js('/'.PA::$theme_rel.'/javascript/points_directory.js');
                        break;
                    case 'deletePoints':
                        $this->view_mode = 'list';
                        break;
                    default:
                }
            }
            else {
                $this->view_mode = 'list';
                $this->set_inner_template('center_inner_public.tpl.php');
                $this->showPointsDirectory($request_data);
            }
        }
    }

    private function showPointsDirectory($request_data) {
        if(!empty($this->fid)) {
            $this->Paging["querystring"] = "fid=$this->fid&gid=$this->fid";
        }
        elseif(!empty($this->user_id)) {
            $this->Paging["querystring"] = "uid=$this->user_id";
        }
        $this->Paging['page'] = $this->page;
        $this->Paging['show'] = $this->limit;
        $this->Paging['count'] = PointsEntity::search($this->criteria, true);
        $pagination = new Pagination();
        $pagination->setPaging($this->Paging);
        $this->page_first = $pagination->getFirstPage();
        $this->page_last = $pagination->getLastPage();
        $this->page_links = $pagination->getPageLinks();
        $items = PointsEntity::search($this->criteria, false, "(SELECT attribute_value FROM entityattributes where attribute_name = 'created' AND id = EA.id) DESC", $this->page, $this->limit);
        $this->items = $this->build_list_of_items($items);
        $this->inner_HTML = $this->generate_inner_html(array('sub_title' => $this->sub_title, 'category' => $this->category, 'categories' => $this->categories, 'items' => $this->items, 'edit_perm' => $this->edit_perm, 'user_id' => $this->user_id, 'page_first' => $this->page_first, 'page_last' => $this->page_last, 'page_links' => $this->page_links, 'url_base' => $this->url_base, 'message' => (isset($request_data['message']) ? $request_data['message'] : null), 'fid' => (isset($this->fid) ? $this->fid : null), 'fam_members' => (isset($this->fam_members) ? $this->fam_members : null)));
    }

    private function build_list_of_items($items) {
        $entity_attrs = array(
            'user_id',
            'giveuser_id',
            'family_id',
            'network_id',
            'entity_id',
            'category',
            'description',
            'media_cid',
            'media_type',
            'media_file',
            'rating',
            'place',
            'updated',
            'created',
        );
        $results = array();
        $cnt = 0;
        foreach($items as $item) {
            $results[$cnt]['entity_id'] = $item['entity_id'];
            $results[$cnt]['entity_name'] = $item['entity_name'];
            foreach($entity_attrs as $attr) {
                $results[$cnt][$attr] = (!empty($item['attributes'][$attr]['value'])) ? $item['attributes'][$attr]['value'] : null;
            }
            $user = new User();
            $user->load((int) $results[$cnt]['user_id']);
            $results[$cnt]['user'] = $user;
            if(!isset($results[$cnt]['giveuser_id']) || empty($results[$cnt]['giveuser_id'])) {
                $results[$cnt]['giveuser_id'] = $this->familly->owner_id;
            }
            $giveuser = new User();
            $giveuser->load((int) $results[$cnt]['giveuser_id']);
            $results[$cnt]['giveuser'] = $giveuser;
            switch($results[$cnt]['media_type']) {
                case 'image':
                    $default_pic = uihelper_resize_mk_img($results[$cnt]['media_file'], 86, 92, 'images/default_image.png', "", RESIZE_CROP);
                    break;
                case 'audio':
                    $default_pic = uihelper_resize_mk_img(null, 86, 92, 'images/default_audio.png', "", RESIZE_CROP);
                    break;
                case 'video':
                    $default_pic = uihelper_resize_mk_img(null, 86, 92, 'images/default_video.png', "", RESIZE_CROP);
                    break;
                default:
                    $default_pic = $default_pic = uihelper_resize_mk_img(null, 86, 92, 'images/default_image.png', "", RESIZE_CROP);
            }
            if(!empty($results[$cnt]['media_cid'])) {
                $content_url = PA::$url."/".FILE_MEDIA_FULL_VIEW."?cid={$results[$cnt]['media_cid']}";
                $default_pic = "<a href=\"$content_url\" alt=\"{$results[$cnt]['media_file']}\">$default_pic</a>";
            }
            $results[$cnt]['media_icon'] = $default_pic;
            $cnt++;
        }
        return $results;
    }
    //   $img_resized = uihelper_resize_mk_img($image_file, 200, 200, NULL, 'style="margin-left: 10px"');
    function handleRequest($request_method, $request_data) {
        if(!empty($this->action) && ($this->module == $this->class_name)) {
            // only if action and target module defined
            switch($request_method) {
                case 'POST':
                    $method_name = 'handlePOST_'.$this->action;
                    if(method_exists($this, $method_name)) {
                        $this-> {
                            $method_name
                        }($request_data);
                    }
                    else {
                        throw new Exception("$class_name error: Unhandled POST action - \"$this->action\" in request.");
                    }
                    break;
                case 'GET':
                    $method_name = 'handleGET_'.$this->action;
                    if(method_exists($this, $method_name)) {
                        $this-> {
                            $method_name
                        }($request_data);
                    }
                    else {
                        throw new Exception("$class_name error: Unhandled GET action - \"$this->action\" in request.");
                    }
                    break;
                case 'AJAX':
                    $method_name = 'handleAJAX_'.$this->action;
                    if(method_exists($this, $method_name)) {
                        $this-> {
                            $method_name
                        }($request_data);
                    }
                    else {
                        throw new Exception("$class_name error: Unhandled AJAX action - \"$this->action\" in request.");
                    }
                    break;
            }
        }
    }

    public function handleGET_newPoints($request_data) {
        global $error_msg;
        if(!$this->edit_perm) {
            $error_msg = __("You do not have permission for this action!");
            return;
        }
        $item = array();
        $item['entity_id'] = PointsEntity::get_next_id_for_user($this->user_id);
        $item['giveuser_id'] = PA::$login_uid;
        //echo "$this->user_id <pre>" . print_r( $item, 1) . "</pre>";
        try {
            $this->inner_HTML = $this->generate_inner_html(array('sub_title' => $this->sub_title, 'category' => $this->category, 'categories' => $this->categories, 'user_id' => $this->user_id, 'url_base' => $this->url_base, 'item' => $item, 'fid' => $this->fid));
        }
        catch(PAException$e) {
            $error_msg = $e->message;
        }
    }

    public function handleGET_editPoints($request_data) {
        global $error_msg;
        if(!$this->edit_perm) {
            $error_msg = __("You do not have permission for this action!");
            return;
        }
        try {
            $entity_id = $request_data['eid'];
            $ent = (array) PointsEntity::load($entity_id);
            $items = $this->build_list_of_items(array($ent));
            if(empty($items[0])) {
                throw Exception(__("Points Entity not found!"));
            }
            $this->inner_HTML = $this->generate_inner_html(array('sub_title' => $this->sub_title, 'category' => $this->category, 'categories' => $this->categories, 'user_id' => $this->user_id, 'url_base' => $this->url_base, 'item' => $items[0], 'fid' => $this->fid));
        }
        catch(PAException$e) {
            $error_msg = $e->message;
        }
    }

    public function handleGET_deletePoints($request_data) {
        global $app;
        $back_url = PA::$url.PA_ROUTE_POINTS_DIRECTORY."?uid=".PA::$login_uid;
        try {
            $entity_id = $request_data['eid'];
            $ent = PointsEntity::load($entity_id);
            if($ent->attributes['user_id']['value'] <> PA::$login_uid) {
                $msg = __("You do not have permission for this action!");
                return;
            }
            PointsEntity::delete('internal', 'points', $entity_id);
            $msg = __("Points sucessfully deleted.");
        }
        catch(PAException$e) {
            $msg = $e->message;
        }
        $app->redirect($back_url."&msg=".urlencode($msg));
    }

    public function handlePOST_savePoints($request_data) {
        global $app;
        $msg = null;
        $error = false;
        $form_data = $request_data['form_data'];
        $form_data['created'] = strtotime($form_data['created']);
        $form_data['updated'] = strtotime($form_data['updated']);
        $ent_name = array_shift($form_data);
        $media_data = $request_data['media'];
        foreach($media_data as $key => $value) {
            $form_data["media_$key"] = $value;
        }
        try {
            $entity = new PointsEntity();
            $entity->entity_service = 'internal';
            $entity->entity_type = 'points';
            $entity->entity_id = $form_data['entity_id'];
            $entity->entity_name = $ent_name;
            $entity->attributes = $form_data;
            PointsEntity::sync($entity);
            $msg = __("Points data sucessfully stored");
        }
        catch(PAException$e) {
            $error = true;
            $msg = $e->message;
        }
        $ent = (array) PointsEntity::load($form_data['entity_id']);
        $items = $this->build_list_of_items(array($ent));
        unset($request_data['form_data']);
        unset($request_data['media']);
        unset($request_data['action']);
        unset($request_data['module']);

        /*
             $this->view_mode = 'edit';
             $this->sub_title = __('Edit Points');
             $this->set_inner_template('edit_points_form.tpl.php');
             $this->inner_HTML = $this->generate_inner_html(array('sub_title' => $this->sub_title,
                                                                  'category' => $this->category,
                                                                  'categories' => $this->categories,
                                                                  'user_id' => $this->user_id,
                                                                  'url_base'   => $this->url_base, 
                                                                  'item' => $items[0],
                                                                  'error' => $error,
                                                                  'message' => $msg,
                                                                  'fid'     => $this->fid 
                                                                 )
                                                           );
        */
        /*
             $request_data['message'] = $msg;
             $this->view_mode = 'list';
             $this->set_inner_template('center_inner_public.tpl.php');
             $this->showPointsDirectory($request_data);
        */
        $app->redirect($this->url_base."&msg=".urlencode($msg));
    }

    function set_inner_template($template_fname) {
        $this->inner_template = PA::$blockmodule_path.'/'.get_class($this)."/$template_fname";
    }

    function render() {
        $content = parent::render();
        return $content;
    }

    function generate_inner_html($template_vars = array()) {
        $inner_html_gen = &new Template($this->inner_template);
        foreach($template_vars as $name => $value) {
            if(is_object($value)) {
                $inner_html_gen->set_object($name, $value);
            }
            else {
                $inner_html_gen->set($name, $value);
            }
        }
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
