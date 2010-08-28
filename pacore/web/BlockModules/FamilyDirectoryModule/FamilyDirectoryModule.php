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
require_once 'api/Entity/FamilyTypedGroupEntity.php';

class FamilyDirectoryModule extends Module {
  public $module_type = 'group';
  public $module_placement = 'middle';


  function __construct() { 
    parent::__construct();
    $this->outer_template = 'outer_public_center_module.tpl';
		$this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/family_directory.tpl.php";
	  $this->title = __('Family Directory');
    $this->html_block_id = get_class($this);
    $this->page = 1;
    $this->show = 20;
		$this->directoryType = 'family';
	}

  function handleRequest($request_method, $request_data) {
  }

  function initializeModule($request_method, $request_data) {
  	 
    $this->groupCount = array();
		$classname = ucfirst($this->directoryType)."TypedGroupEntity";
		$instance = new $classname();
		$this->profilefields = $instance->get_profile_fields();
		if (!empty($_REQUEST['sort_by'])) {
			$this->sort_by = $_REQUEST['sort_by'];
		} else {
			$this->sort_by = 'name';
		}
		
		$this->sortFields = array(
			array('name' => 'name', 'label'  => __('Name'))
		);
		foreach ($this->profilefields as $i=>$field) {
			if (!empty($field['sort'])) {
		  	$this->sortFields[] = array(
		  			'name'=>$field['name'],
		  			'label' => $field['label']
		  	);
		  }
		}
		// get TypedGroupEntities for this type and build paging etc
		if (!empty($request_data['page'])) {
			$this->page = (int)$request_data['page'];
		}
		$this->Paging['page'] = $this->page;
		$this->Paging['show'] = $this->show;
		$this->groupCount[$this->directoryType] = 	
			TypedGroupentity::get_count($this->directoryType);

		// load list of entities
  	$this->typedGroupEntities =
  		TypedGroupentity::get_entities($this->directoryType, $this->sort_by, $this->page, $this->show);

		$this->Paging['count'] = $this->groupCount[$this->directoryType];
		$Pagination = new Pagination();
		$Pagination->setPaging($this->Paging);
		
		$this->page_first = $Pagination->getFirstPage();
		$this->page_last = $Pagination->getLastPage();
		$this->page_links = $Pagination->getPageLinks();
  }
  
  function render() {
    $this->inner_HTML = $this->generate_inner_html();

    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html() {
    $inner_html_gen = new Template($this->inner_template, $this);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }
  
}
?>