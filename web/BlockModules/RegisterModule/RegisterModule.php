<?php

require_once "api/Theme/Template.php";
require_once "web/includes/image_resize.php";

class RegisterModule extends Module {
  
  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';

  function __construct() {
    $this->title = "Join ".PA::$site_name;
    $this->html_block_id = "RegisterModule";
    $this->states    = array("-2" => '-Select-', "-1" => 'Other');
    $this->states    = $this->states + array_values(PA::getStatesList());
    $this->countries    = array("-1" => '-Select-');
    $this->countries    = $this->countries + array_values(PA::getCountryList());
    /*    
    $this->states    = array_values(PA::getStatesList());
    $this->countries = array_values(PA::getCountryList());
    
    array_unshift($this->states, 'Other');
    array_unshift($this->states, '-Select-');
    array_unshift($this->countries, '-Select-');
*/    
  }

  function render() {
    if (!empty($this->shared_data['rp'])) {
    	$rp = $this->shared_data['rp'];
			if (isset($rp->reg_user)) {
				$this->array_of_errors = @$rp->reg_user->array_of_errors;
			}
    }

    $this->inner_HTML = $this->generate_inner_html ();    
    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html () {
    global $current_theme_path;
    
    $inner_template = NULL;
    switch ( $this->mode ) {
             
      default:
        $inner_template = PA::$blockmodule_path.'/RegisterModule/center_inner_public.tpl';      
    }
    
    $obj_inner_template = & new Template($inner_template, $this);
    $obj_inner_template->set('array_of_errors', @$this->array_of_errors);    
    $obj_inner_template->set('current_theme_path', $current_theme_path);
    $obj_inner_template->set('states', $this->states);
    $obj_inner_template->set('countries', $this->countries);
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }
}
?>