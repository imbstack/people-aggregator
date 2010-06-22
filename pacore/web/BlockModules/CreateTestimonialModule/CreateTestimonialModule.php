<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
* CreateTestimonialModule.php is a part of PeopleAggregator.
* This is designed to simply to spit out a little form for the user to enter a 
* testimonial for another user into.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
* @author Tekriti Software
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
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

    /** !!
    * Create everything to be displayed.  The html is created by calling
    * { @link generate_inner_htnl() }.
    */
    function render() {
        $r                = get_page_user();
        $this->title     .= ucfirst($r->login_name);
        $this->inner_HTML = $this->generate_inner_html();
        $content          = parent::render();
        return $content;
    }

    /** !!
    * Set where the html should be rendered and what template it should user
    * @return string $inner_html all the html for the module
    */
    function generate_inner_html() {
        $tmp_file    = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_public.tpl';
        $net_details = &new Template($tmp_file);
        $inner_html  = $net_details->fetch();
        return $inner_html;
    }
}
?>
