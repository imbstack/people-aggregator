<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* ExternalFeedModule.php is a part of PeopleAggregator.
* This module presumably shows the user any External Feeds they have imported
*  into PA.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau?
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/ExternalFeed/ExternalFeed.php";

class ExternalFeedModule extends Module {

    public $module_type = 'user';

    public $module_placement = 'left|right';

    public $outer_template = 'outer_public_side_module.tpl';

    function __construct() {
        parent::__construct();
        $this->html_block_id = "ExternalFeedModule";
        $this->title = __('External Feeds');
    }

    function render() {
        // global var $_base_url has been removed - please, use PA::$url static variable
        $ExternalFeed = new ExternalFeed();
        //$user = get_login_user();
        //$ExternalFeed->user_id = $user->user_id;
        //Temporarily displaying the feeds of SUPER USER for all users for demoing
        //To get the user feed data uncomment the above written two line and comment the line below written line
        $ExternalFeed->user_id = SUPER_USER_ID;
        $links = array();
        try {
            $links = $ExternalFeed->get_user_feed_data();
        }
        catch(PAException$e) {
            //TODO: pending error handling if function fails.
            //$error = $e->message;
        }
        $this->inner_HTML = $this->generate_inner_html($links);
        $content = parent::render();
        return $content;
    }

    function generate_inner_html($links) {
        switch($this->mode) {
            case PRI:
                $this->outer_template = 'outer_private_side_module.tpl';
                $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/side_inner_public.tpl';
                break;
            default:
                $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/side_inner_public.tpl';
                break;
        }
        $inner_html_gen = &new Template($tmp_file);
        $inner_html_gen->set('links', $links);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>
