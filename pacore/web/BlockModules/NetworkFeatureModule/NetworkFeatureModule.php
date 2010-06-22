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
Class NetworkFeatureModule extends Module {

    public $module_type = 'system|network';

    public $module_placement = 'middle';

    public $outer_template = 'outer_public_group_center_module.tpl';

    function __construct() {
        parent::__construct();
        $this->title         = __('Featured Network');
        $this->main_block_id = 'mod_network_feature';
        $this->html_block_id = "NetworkFeatureModule";
    }

    function render() {
        $network             = new Network();
        $this->network_links = $network->get();
        $this->inner_HTML    = $this->generate_inner_html();
        $links               = parent::render();
        return $links;
    }

    function generate_inner_html() {
        $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_public.tpl';
        $inner_html_gen = &new Template($tmp_file);
        $inner_html_gen->set('network_links', $this->network_links);
        $inner_html_gen->set('featured_network', $this->featured_network);
        $inner_html_gen->set('config_navigation_url', network_config_navigation('set_featured_network'));
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>