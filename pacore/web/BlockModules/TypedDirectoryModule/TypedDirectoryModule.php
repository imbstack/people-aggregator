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
require_once 'api/Entity/TypedGroupEntity.php';

class TypedDirectoryModule extends Module {
  public $module_type = 'group';
  public $module_placement = 'middle';


  function __construct() { 
    parent::__construct();
    $this->outer_template = 'outer_public_center_module.tpl';
	  $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/master_directory.tpl.php";
    $this->title = __('Master Directory');
    $this->html_block_id = get_class($this);
    $this->page = 1;
    $this->show = 20;
  }

  function handleRequest($request_method, $request_data) {
  }

  function initializeModule($request_method, $request_data) {
  	 
    if (empty(PA::$config->useTypedGroups)) {
  		return 'skip';
  	}
  	
    
    $this->availTypes = TypedGroupentity::get_avail_types();
    $this->directoryType = NULL;
    $this->groupCount = array();
    
  	if (!empty($request_data['type'])) {
  		if (!empty($this->availTypes[$request_data['type']])) {
  			// we have been passed a valid TypedGroupEntity type
  			$this->directoryType = $request_data['type'];
  		} 
  	}
  	
  	if ($this->directoryType) {
		  $this->title = sprintf(__("%s directory"), 
		  	$this->availTypes[$this->directoryType]
		  );
		  $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/type_directory.tpl.php";

		  $classname = ucfirst($this->directoryType)."TypedGroupEntity";
		  @include_once "api/Entity/$classname.php";
		  if (class_exists($classname)) {
		  	$instance = new $classname();
		  } else {
		  	// just get default
		  	$instance = new TypedGroupEntity();
		  }
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
	
  		$this->groupCount[$this->directoryType] = TypedGroupentity::get_count($this->directoryType);
  		
  		// load list of entities
  		$this->typedGroupEntities =
  			TypedGroupentity::get_entities($this->directoryType, $this->sort_by, $this->page, $this->show);

			$this->Paging['count'] = $this->groupCount[$this->directoryType];
			$Pagination = new Pagination();
			$Pagination->setPaging($this->Paging);
	
			$this->page_first = $Pagination->getFirstPage();
			$this->page_last = $Pagination->getLastPage();
			$this->page_links = $Pagination->getPageLinks();
  	} else {
			foreach ($this->availTypes as $type=>$label) {
				$this->groupCount[$type] = TypedGroupentity::get_count($type);
			}
  	}
  	
  	switch($this->column) {
    	case 'middle':
      break;
      case 'left':
      case 'right':
      default:
      break;
    }
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