<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        AddUserComment.php, BlockModule file to
                generate AddUserComment
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class AddUserComment  which 
 * generates html of a form to create comment
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
 

class AddUserComment extends Module {
  
  public $module_type = 'user';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  
  function __construct() {
    $this->title = __("Write Comment for ");
    $this->html_block_id = 'AddUserComment';
  }

   function render() { 
    $r = get_page_user();
    $this->title .= ucfirst($r->login_name);
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  function generate_inner_html () {
    
    $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    $user_comment_form = & new Template($tmp_file);
    $inner_html = $user_comment_form->fetch();
    return $inner_html;
  }
}
?>
