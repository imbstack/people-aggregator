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
class ManageCategoryModule extends Module {

  
  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  public $category;
  public $edit_title;
  public $edit_desc;
  public $cat_id;
  public $parent_id;
  public $parent_name;

  function __construct() {
    parent::__construct();
    $this->title = __('Manage Category');
    $this->html_block_id = 'ManageCategoryModule';
    $this->main_block_id = NULL;
  }

  // This function renders ManageAdCenterModule
  function render() {
    switch (@$_GET["type"]) {
      case 'Content':
        $this->type = 'Content';
      break;
      case 'Default':
      default:
        $this->type = 'Default';
    }

    if (@$_GET['a'] == "edit" && !empty($_GET['cat_id'])) {
      $edit_cat = new Category();
      $edit_cat->category_id = $_GET['cat_id'];
      $edit_cat->load();
      $this->edit_title = $edit_cat->name;
      $this->edit_desc = $edit_cat->description;
      $this->cat_id = $edit_cat->category_id;
      $this->type = $edit_cat->type;
      $parent_id = $edit_cat->find_parent($edit_cat->category_id);
      $this->parent_id = ($parent_id)? $parent_id : 0;
      if ($this->parent_id) {
        $edit_cat->category_id = $this->parent_id;
        $edit_cat->load();
        $this->parent_name = $edit_cat->name;
      } else {
        $this->parent_name = '';
      }
    }
    
    $this->categories = Category::build_all_category_list('', '', '', $this->type);
    $category = Category::build_root_list(NULL,$this->type);
    if(is_array($category)) {
      foreach ($category as $catg) {
        $this->get_childs($catg);
      }
    }
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  } 

  function get_childs($catg,$parent=NULL) {
    if($parent) {
      $catg->parent_name = $parent->name;
    } else {
      $catg->parent_name = '';
    }
    $this->category[] = $catg;
    $childs = Category::build_children_list($catg->category_id, NULL,$this->type);
    if($childs) {
      foreach ($childs as $chd) {
        $this->get_childs($chd,$catg);
      }
    }
  }
  // This function generated inner html for the ManageAdCenterModule
  function generate_inner_html () {    
    
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    $inner_html_gen = & new Template($tmp_file);
    $inner_html_gen->set('category', $this->category);
    $inner_html_gen->set('categories', $this->categories);
    $inner_html_gen->set('edit_title', $this->edit_title);
    $inner_html_gen->set('edit_desc', $this->edit_desc);
    $inner_html_gen->set('cat_id', $this->cat_id);
    $inner_html_gen->set('type', $this->type);
    $inner_html_gen->set('parent_id', $this->parent_id);
    $inner_html_gen->set('parent_name', $this->parent_name);
    $inner_html_gen->set('config_navigation_url',
                       network_config_navigation( 'manage_category'));
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
}
?>