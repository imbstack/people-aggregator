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
require_once "web/includes/classes/Pagination.php";
require_once "api/User/User.php";
require_once "api/Network/Network.php";

class TaskRoleManageModule extends Module {

  public $module_type = 'system|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_group_center_module.tpl';
  
  function __construct() {
    parent::__construct();
    $this->main_block_id = "mod_network_user_result";
    $this->title = __('Assign Roles to Tasks');
  }

  
   //render the contents of the page   
   function render() {
     $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  //inner html of the module generation
  function generate_inner_html () {
    switch ( $this->mode ) {
      default:
        $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';   
    }
    $inner_html_gen = new Template($inner_template);
    $role = new Roles();
    $params = array('sort_by' => 'id', 'direction' => 'ASC', 'cnt' => false);
    $this->links = $role->get_multiple($params);
    $inner_html_gen->set('links', $this->links);
    $task =  Tasks::  get_instance() ;
    $tasklist = $task->get_tasks(); 
    $inner_html_gen->set('tasklist', $tasklist);
    $inner_html_gen->set('super_user_and_mothership', @$this->super_user_and_mothership);
    $inner_html_gen->set('config_navigation_url',
                      network_config_navigation('manage_tasks_relationship'));
    $inner_html = $inner_html_gen->fetch();
    
    return $inner_html;
  }
}
?>

