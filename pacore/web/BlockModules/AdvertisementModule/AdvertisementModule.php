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

  require_once "api/Advertisement/Advertisement.php";

class AdvertisementModule extends Module {

    public $module_type = 'user|group|network';

    public $module_placement = 'left|right|middle';

    public $outer_template = 'outer_public_center_module.tpl';

    function __construct() {
        parent::__construct();
    }

    function render() {
        $this->inner_HTML = $this->generate_inner_html();
        $ads = parent::render();
        return $ads;
    }

    function generate_inner_html() {
        if(empty($this->links)) {
            return "";
        }
        $links = $this->links;

        /*
        if ($links->type == 'textpad') {
          $this->title = $links->title;
        }
        */
        $pos = explode(',', $links->orientation);
        $x_loc = $pos[0];
        if($x_loc == 2) {
            $width = AD_WIDTH_MIDDLE;
            //$height = AD_HEIGHT_MIDDLE;
        }
        else {
            $width = AD_WIDTH_LR;
            //$height = AD_HEIGHT_LR;
            $this->outer_template = 'outer_public_side_module.tpl';
        }
        // we never want to reduce the height of an ad, so we set $height very high
        $height         = 1000;
        $tmp_file       = PA::$blockmodule_path.'/'.get_class($this).'/side_inner_html.tpl';
        $inner_html_gen = &new Template($tmp_file);
        $inner_html_gen->set_object('links', $links);
        $inner_html_gen->set('width', $width);
        $inner_html_gen->set('height', $height);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>