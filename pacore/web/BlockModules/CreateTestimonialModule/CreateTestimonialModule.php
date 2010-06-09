<?php
/** !
* CreateTestimonialModule.php is a part of PeopleAggregator.
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @author Tekriti Software, [Owen Bell: 2 June 2010]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
* This file contains a class CreateForumTopicModule  which 
* generates html of  of a form to create a forum topic in a group
* @example  [optional]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/
class CreateTestimonialModule extends Module {

    public $module_type = 'network';
    public $module_placement = 'middle';
    public $outer_template = 'outer_public_center_module.tpl';

    function __construct() {

        parent::__construct();
        $this->title = __("Write Testimonial for ");
        $this->html_block_id = 'CreateTestimonialModule';
    }

    function render() {

        $r = get_page_user();
        $this->title .= ucfirst($r->login_name);
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    function generate_inner_html() {
        $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_public.tpl';
        $net_details = &new Template($tmp_file);
        $inner_html = $net_details->fetch();
        return $inner_html;
    }
}
?>
