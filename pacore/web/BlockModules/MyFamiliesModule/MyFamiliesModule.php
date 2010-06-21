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
require_once "api/Group/Group.php";
require_once "web/includes/classes/Pagination.php";
require_once "api/Entity/TypedGroupEntityRelation.php";

class MyFamiliesModule extends Module {

    public $module_type = 'user|group';

    public $module_placement = 'left|right';

    public $outer_template = 'outer_public_side_module.tpl';

    public function __construct() {
        parent::__construct();
    }
    //code for module initialization
    public function initializeModule($request_method, $request_data) {
        if(empty($this->page_id)) {
            return 'skip';
        }
        switch($this->page_id) {
            case PAGE_USER_PUBLIC:
                $this->uid       = PA::$page_uid;
                $this->title     = abbreviate_text((ucfirst(PA::$page_user->first_name).'\'s '), 18, 10);
                $this->title    .= __('Families');
                $this->user_name = PA::$page_user->login_name;
                break;
            case PAGE_USER_PRIVATE:
                $this->title = __('My Families');
                $this->uid = PA::$login_uid;
                break;
            case PAGE_FAMILY:
            case PAGE_FAMILY_DIRECTORY:
            default:
                if(empty(PA::$login_uid)) {
                    return 'skip';
                }
                $this->uid = PA::$login_uid;
                break;
        }
    }

    private function get_families() {
        $userfamilies = TypedGroupEntityRelation::get_relation_for_user($this->uid, 'family');
        $family_details = array();
        foreach($userfamilies as $i => $fam) {
            $group                         = ContentCollection::load_collection((int) $fam->object_id, PA::$login_uid);
            $member_exist                  = Group::member_exists((int) $fam->object_id, $this->uid);
            $picture                       = $group->picture;
            $cnt                           = Group::get_member_count($group->collection_id);
            $family_details[$i]['id']      = $group->collection_id;
            $family_details[$i]['title']   = stripslashes($group->title);
            $desc                          = stripslashes($group->description);
            $desc                          = substr($desc, 0, 100);
            $family_details[$i]['desc']    = $desc;
            $family_details[$i]['picture'] = $picture;
            $family_details[$i]['members'] = $cnt;
            $family_details[$i]['access']  = $group->access_type;
        }
        return $family_details;
    }

    function render() {($this->title) ? $this->title : $this->title = __('My Families');
        $this->families = $this->get_families();
        if(sizeof($this->families)) {
            // $this->view_all_url = PA::$url . PA_ROUTE_GROUPS.'/uid='.$this->uid;
        }
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    function generate_inner_html() {
        $this->outer_template = 'outer_private_side_module.tpl';
        $tmp_file             = PA::$blockmodule_path.'/'.get_class($this).'/my_families.tpl.php';
        $inner_html_gen       = &new Template($tmp_file, $this);
        $inner_html_gen->set('families', $this->families);
        $inner_html_gen->set('mode', $this->mode);
        $inner_html_gen->set('user_name', @$this->user_name);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>
