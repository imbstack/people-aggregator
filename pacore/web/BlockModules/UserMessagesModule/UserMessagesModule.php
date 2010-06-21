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

require_once "api/Message/Message.php";

class UserMessagesModule extends Module {

    public $module_type = 'user';

    public $module_placement = 'left|right';

    public $outer_template = 'outer_public_side_module.tpl';

    function __construct() {
        parent::__construct();
        $this->title = __('Messages');
    }

    public function initializeModule($request_method, $request_data) {
        if(empty(PA::$login_uid)) {
            return 'skip';
        }
    }

    function render() {
        $total_msg = Message::get_new_msg_count(PA::$login_uid);
        if(($total_msg['unread_msg'] != 0) || ($total_msg['total']) != 0) {
            $this->view_all_url = PA::$url.PA_ROUTE_MYMESSAGE.'/folder_name=Inbox';
        }
        $this->links      = $total_msg;
        $this->inner_HTML = $this->generate_inner_html();
        $content          = parent::render();
        return $content;
    }

    function generate_inner_html() {
        switch($this->mode) {
            case PRI:
                $this->outer_template = 'outer_private_side_module.tpl';
                $inner_template = PA::$blockmodule_path.'/'.get_class($this).'/side_inner_private.tpl';
                break;
            default:
                $inner_template = PA::$blockmodule_path.'/'.get_class($this).'/side_inner_private.tpl';
                break;
        }
        $inner_html_gen = &new Template($inner_template);
        $inner_html_gen->set('links', $this->links);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
?>
