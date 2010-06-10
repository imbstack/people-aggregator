<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* ConfigureEmailModule.php is a part of PeopleAggregator.
* Create the html for all emails that are sent by PA
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/

require_once "api/EmailMessages/EmailMessages.php";
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
  * Generate all content that will be displayed
  * @return string $content html to be displayed
  */
  function render() {
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }

  /** !!
  * Get the template that will be used for the email. Get list of containers
  * that will be used for the email by calling { @link getEmailContainers() }
  * and placing it in { @link $template_list }. Take all data about the email 
  * such as message and author as well as the template to be used from
  * { @link $template_list } and place it into ( @link $obj_inner_template }.
  * @return string $inner_html all the html to be displayed in the email,
  *		gathered by setting this equal to { @link $obj_inner_template }
  */
  function generate_inner_html () {
    if(!empty($_GET['template'])) {
      $this->template = $_GET['template'];
    }
    $tiny = new TinyMCE('medium');
    $email_list = EmailMessages::get_email_list();
    $template_list = $this->getEmailContainers(PA::$config_path . "/email_containers");
    $inner_template = NULL;
    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_html.tpl';
    $obj_inner_template = & new Template($inner_template);
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
  * Compile a list of all email containers in a specific location
  * dicated by { @link $path }
  * @param string $path the path to a collection of email containers
  * @return string $email_containers all containers in the location
  */
  private function getEmailContainers($path) {
    $paths = array(PA::$core_dir . "/$path", PA::$project_dir . "/$path"); // core templates will be overwritten with project templates
    $email_containers = array('None (Plain Text)' => 'text_only' );
    foreach($paths as $path) {
      foreach (new DirectoryIterator($path) as $fileInfo) {
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
