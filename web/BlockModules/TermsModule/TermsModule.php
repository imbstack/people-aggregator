<?php

class TermsModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_single_module.tpl';

  function __construct() {
    parent::__construct();
  }

  function render() {
    $tpl =& new Template(PA::$blockmodule_path .'/'. get_class($this) . "/center_inner_public.tpl");
    $this->inner_HTML = $tpl->fetch();
    return parent::render();
  }

}

?>