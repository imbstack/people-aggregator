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
require_once "api/Ranking/Ranking.php";

class RankingModule extends Module {

    public $module_type = 'system|network';

    public $module_placement = 'middle';

    public $outer_template = 'outer_public_center_module.tpl';

    public $error = "";

    function __construct() {
        parent::__construct();
        $this->title = __("Site Ranking");
    }

    function render() {
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    function generate_inner_html() {
        $inner_template = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_private.tpl';
        $inner_html_gen = &new Template($inner_template);
        $inner_html_gen->set('parameters', Ranking::get_parameters());
        $inner_html_gen->set('error', $this->error);
        $inner_html_gen->set('config_navigation_url', network_config_navigation('manage_ranking'));
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?> 
