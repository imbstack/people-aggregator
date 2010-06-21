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
error_reporting(E_ALL);
// require_once "api/...";
// require_once "web/includes/classes/...";
class ShowMessageModule extends Module {

    public $module_type = 'user|group|network';

    public $module_placement = 'middle';

    public $error_msg;

    public $type;

    public $redirect_url;

    public $redirect_delay;

    public $window_title;

    private $msg_types = array(
        'info',
        'error',
        'confirm',
        'warning',
    );

    function __construct() {
        parent::__construct();
        $this->title = __('An Error Has Occurred');
        $this->type = 'error';
        $this->window_title = __('Error details');
        $this->error_msg = null;
        $this->redirect_url = PA::$url.PA_ROUTE_HOME_PAGE;
        $this->redirect_delay = 0;
        $this->outer_template = "outer_public_center_single_wide_module.tpl";
    }

    function initializeModule($request_method, $request_data) {
        //  echo "<pre>" . print_r($request_data, 1) . "</pre>";
        if(!empty($request_data['show_msg'])) {
            $this->error_msg = $request_data['show_msg'];
        }
        //  echo "Message is: " . $this->error_msg . "<br />";
        if(empty($this->error_msg)) {
            return 'skip';
        }
        if(!empty($request_data['redirect_url'])) {
            $this->redirect_url = urldecode($request_data['redirect_url']);
        }
        if(!empty($request_data['redirect_delay'])) {
            $this->redirect_delay = $request_data['redirect_delay'];
        }
        if(!empty($request_data['msg_type'])) {
            if(in_array($request_data['msg_type'], $this->msg_types)) {
                $this->type = $request_data['msg_type'];
            }
            else {
                $this->type = 'error';
            }
        }
        switch($this->type) {
            case 'info':
                $this->window_title = __('Information');
                $this->title = '';
                break;
            case 'error':
                $this->window_title = __('Error details');
                break;
            case 'confirm':
                $this->window_title = __('Please confirm action');
                $this->title = '';
                break;
            case 'warning':
                $this->window_title = __('Warning!');
                $this->title = '';
                break;
            default:
                if(!empty($request_data['window_title'])) {
                    $this->window_title = $request_data['window_title'];
                }
                break;
        }
        if(is_numeric($this->error_msg)) {
            $msg_obj = new MessagesHandler();
            $this->error_msg = $msg_obj->get_message($this->error_msg);
        }
        else {
            $this->error_msg = urldecode($this->error_msg);
        }
        if(!empty($this->redirect_url)) {
            $js = "<script type=\"text/javascript\">\n\r
                   var redirect_url='".$this->redirect_url."';\n\r";
            if($this->redirect_delay > 0) {
                $js .= "var redirect_delay='".$this->redirect_delay."';\n\r";
            }
            $js .= "</script>";
            $this->renderer->add_header_html($js);
        }
        $template_name = "show_".$this->type."_message.tpl";
        $this->set_inner_template($template_name);
        $this->inner_HTML = $this->generate_inner_html(array('error_msg' => $this->error_msg, 'type' => $this->type, 'window_title' => $this->window_title, 'redirect_url' => $this->redirect_url, 'redirect_delay' => $this->redirect_delay));
    }

    function set_inner_template($template_fname) {
        $this->inner_template = PA::$blockmodule_path.'/'.get_class($this)."/$template_fname";
    }

    /*
      function render() {
        return $this->inner_HTML;
      }
    */
    function render() {
        $content = parent::render();
        return $content;
    }

    function generate_inner_html($template_vars = array()) {
        $inner_html_gen = &new Template($this->inner_template);
        foreach($template_vars as $name => $value) {
            if(is_object($value)) {
                $inner_html_gen->set_object($name, $value);
            }
            else {
                $inner_html_gen->set($name, $value);
            }
        }
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }
}
