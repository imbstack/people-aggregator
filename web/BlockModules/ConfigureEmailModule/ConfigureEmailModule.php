<?php
/** !
* ConfigureEmailModule.php is a part of PeopleAggregator.
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @author Zhoron Hron, [Owen Bell: 2 June 2010]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* Creates emails sent by PA
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/

require_once "ext/EmailMessages/EmailMessages.php";
require_once "web/includes/classes/TinyMCE.class.php";

class ConfigureEmailModule extends Module {

    public $module_type = 'system|network';
    public $module_placement = 'middle';
    public $outer_template = 'outer_public_center_module.tpl';

    function __construct() {

        parent::__construct();
        $this->title = __("Configure Email");
        $this->html_block_id = 'ConfigureEmailModule';
    }

    /** !!
    * Calls {@link generate_inner_html() } to create the html for the email
    * @return string $content the html that will be emailed
    *		contains the message and all other information
    */
    function render() {

        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    /** !!
    * Create the html to be emailed.  Includes the subject, message, and all
    * of the other parts that the email contains. Calls {@link getEmailContainers() }
    * to get the proper formatting for the kind of email being sent.
    * @return html $inner_html html for to be displayed in the email
    */
    function generate_inner_html() {

        if(!empty($_GET['template'])) {
            $this->template = $_GET['template'];
        }
        $tiny = new TinyMCE('medium');
        $email_list = EmailMessages::get_email_list();
        $template_list = $this->getEmailContainers(PA::$config_path."/email_containers");
        $inner_template = NULL;
        $inner_template = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_html.tpl';
        $obj_inner_template = &new Template($inner_template);
        $obj_inner_template->set('email_list', $email_list);
        $obj_inner_template->set('template_list', $template_list);
        $obj_inner_template->set('subject', $this->subject);
        $obj_inner_template->set('message', $this->message);
        $obj_inner_template->set('category', $this->category);
        $obj_inner_template->set('template', $this->template);
        $obj_inner_template->set('description', $this->description);
        $obj_inner_template->set('configurable_variables', $this->configurable_variables);
        $obj_inner_template->set('preview', $this->preview_msg);
        $obj_inner_template->set_object('tiny_mce', $tiny);
        $inner_html = $obj_inner_template->fetch();
        return $inner_html;
    }

    /** !!
    * Get the container to store the data for the email
    * @param string $path path to the email container to use
    * @return array $email_containers containers for the email to use
    */
    private function getEmailContainers($path) {

        $paths = array(PA::$core_dir."/$path", PA::$project_dir."/$path");
        // core templates will be overwritten with project templates
        $email_containers = array('None (Plain Text)'=>'text_only');
        foreach($paths as $path) {
            foreach(new DirectoryIterator($path) as $fileInfo) {
                if($fileInfo->isFile()) {
                    $base_name = $fileInfo->getFilename();
                    $file_name = pathinfo($base_name, PATHINFO_FILENAME);
                    $email_containers[$file_name] = $base_name;
                }
            }
        }
        return $email_containers;
    }
}
?>
