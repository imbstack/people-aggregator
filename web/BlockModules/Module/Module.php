<?php
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        Module.php, BlockModule to generate a block module
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class that is inherited by all other block
 *              modules. It contains the whole block module which typically
 *              consists of outer template and inner html
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 *
 */



// global var $path_prefix has been removed - please, use PA::$path static variable
// global var $_base_url has been removed - please, use PA::$url static variable


require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";



//possible orientations
define ('RIGHT', 0);
define ('LEFT', 1);
define ('CENTER_LEFT', 2);
define ('CENTER_RIGHT', 3);
define ('CENTER', 4);
define ('TAB', 5);
define ('MAX_SIDE_HEIGHT', 168);
define ('MAX_CENTER_HEIGHT', 300);

define ('PUB', 'public');
define ('PRI', 'private');
define ('HOMEPAGE', 'Homepage');
define ('HOMEPAGE_SORTBY', 'Homepage_sortby');

/**
 * This class generates full html module i.e. section of page
 * This is inherited by all block modules that produce inner html
 * @package BlockModules
 * @subpackage Module
 */

class Module {

  public $module_type = 'base_class';
  public $module_placement = '';

  /**
   * $is_private - flag to show that it is private or public view of the module
   * @var boolean
   */
  public $is_private;

  /**
   * $inner_HTML - inner html produced by the inherited module
   * @var string
   */
  public $inner_HTML;

  /**
   * $config_HTML - some modules have configure box with them
   * this var is used to hold that configure html
   * @var string
   */
  public $config_HTML;

  /**
   * $view_all_url - link to a page that contains full records
   * @var string
   */
  public $view_all_url;

  /**
   * $ajax_url - ajax page it is referring to internally
   * @var string
   */
  public $ajax_url;

  /**
   * $title - title of the header of module
   * @var string
   */
  public $title;

  /**
   * $height - height of module
   * @var int
   */
  public $height;

  /**
   * $mode - used in exceptional cases where we have to select the internal tpl files
   * @var string
   */
  public $mode;

  /**
   * $html_block_id - used to defile the ID property of html
   * this is used for various javascript and ajax applications
   * It is unique for every module on a single page
   * Generally it is same as the inherited class name
   * @var string
   */
  public $html_block_id;

  /**
   * $manage_links_url - a link to manage the module section (contents of module)
   * @var string
   */
  public $manage_links_url;

  /**
   * $edit_url - a link to edit the module (contents of the module)
   * @var string
   */
  public $edit_url;

  /**
   * $widgetized - TRUE if we are running inside the widget server.
   */
  public $widgetized = FALSE;

  /**
   * $post_url - URL to use as the action for POST-type forms.
   * Usually left blank, which means the module should figure out the
   * URL itself, but used when the module is being displayed through
   * the widgetization protocol (web/widget_dispatch.php).
   */
  public $post_url = NULL;

  /**
   * $param_prefix - prefix to add to HTML inputs.  Usually left
   * blank, but during widgetization this will be assigned by the
   * client.
   */
  public $param_prefix = NULL;

  /**
   * $post_params - array of parameters to use for POST request.
   * Equivalent to $_form in FormHandler/action.php.
   */
  public $post_params = NULL;

  /**
   * $rss_link - a link to show rss of the module
   * @var string
   */
  public $rss_link;

  /**
   * $orientation - show the location of module
   * used in earlier versions to show the left right orientation of module
   * @var string
   */
  public $orientation;

  /**
   * $caption_image - show image for caption in module if there is any
   * used in earlier versions to show desktop image for user
   * @var string
   */
  public $caption_image;

  /**
   * $show_filters - show the filter options in module
   * @var boolean
   */
  public $show_filters;

  /**
   * $block_heading - heading of center block - used for community block etc
   * @var string
   */
  public $block_heading;

  /**
   * $do_pagination - used for pagination
   * @var string
   */
  public $do_pagination;

  /**
   * $page_prev - links for previous page in pagination
   * @var string
   */
  public $page_prev;

  /**
   * $page_next - links for next page in pagination
   * @var string
   */
  public $page_next;
  /**
   * $page_first - links for first page in pagination
   * @var string
   */
  public $page_first;
    /**
   * $page_last - links for last page in pagination
   * @var string
   */
  public $page_last;
  /**
   * $page_links - all the page links like 1 | 2 | 3
   * @var string
   */
  public $page_links;
  /**
   * $max_height - maximum height of the module
   * @var int
   */
  public $max_height;
   /**
   * $block_type - type of the block module
   * @var int
   */
  public $block_type;

  /**
   * $outer class - for the outer class of the outer templetes , this variable is used to reduce the number of OUTER TEMPLETES FILES in Modules
   * @var string
   */
  public $outer_class_name;

  /**
  * $group_owner - It is set to true, if current user is group moderator
  */
  public $group_owner;
   /**
  * $group_member - It is set to true, if current user is group's member
  */
  public $group_member;

  /**
  * $do_skip: If this variable is set to true then the render function will return 'skip' to the PageRenderer
  */
  public $do_skip;

   /**
  * $page_id: Current Page ID where from module rendered; added by: Z.Hron
  */
  public $page_id;

   /**
  * $column: module layout info; added by: Z.Hron
  */
  public $column;

   /**
  * $shared_data: data shared between all modlules on a page; added by: Z.Hron
  */
  public $shared_data;

  /**
  * Will contain the message, either failure of success which are to be displayed on the web page
  */
  public $message = NULL;

  /**
  * This attribute will mark whether the message is a error message or success message.
  * If TRUE = error or failure message,
  * FALSE = success
  */
  public $isError = FALSE;

  /**
  * These two parameters are used to set the message from with in the module into web page file.
  * These variables are used by set_web_variables function defined in functions.php
  */
  public $redirect2 = NULL;
  public $queryString = NULL;

  /**
  * The default constructor for MembersFacewallModule class.
  * It initializes the default values of vars
  */
  function __construct() {
    $this->do_skip = FALSE;
    if(PA::$profiler) PA::$profiler->startTimer(get_class($this));

//    echo "MODULE CONSTRUCTOR CALLED by " . get_class($this) . "<br/>";
  }


  /**
  *  Function : render()
  *  Purpose  : produce html code of module. It generally uses two tpl files
  *             one for outer template and one for inner template
  *             inner template is produced by inherited module
  *  @return   type string
  *            returns rendered html code
  */
  function render() {

    if ($this->do_skip) return 'skip';//Module will be skipped if do_skip is true.
    $title = $this->title;
    $inner_HTML = $this->inner_HTML;
    $view_all_url = $this->view_all_url;
    $manage_links_url = $this->manage_links_url;
    $ajax_url = $this->ajax_url;
    $rss_link = $this->rss_link;
    $config_HTML = $this->config_HTML;
    $orientation = $this->orientation;
    $block_type = $this->block_type;
    $this->html_block_id = (empty($this->html_block_id)) ?get_class($this): $this->html_block_id;

    if (trim($this->config_HTML) == '') {
      $is_configurable = 0;
    }
    else {
      $is_configurable = 1;
    }


    $id_prefix = $this->html_block_id;

//     $main_block_id = 'mod_'.$this->html_block_id;
    $inner_block_id = $id_prefix.'_block_content';
    $inner_block_data_id = $id_prefix.'_block_data';
    $configure_block_id = $id_prefix.'_block_configure';
#This block of code is temporary and will be removed when outer_template is defined in all the block
#modules

    if (empty($this->outer_template)) throw new PAException(GENERAL_SOME_ERROR, 'Error in module; derived class must have $outer_template instance var');
    $template_file = CURRENT_THEME_FSPATH."/".$this->outer_template;

    //if ($this->title == 'Contents') { print "$template_file"; print "HHH::$this->height"." MMM::$this->max_height"; }

    $block = & new Template($template_file);
    $block->set('current_theme_path', PA::$theme_url);
    $block->set('title', $title);
    $block->set('is_configurable', $is_configurable);
    $block->set('inner_HTML', $inner_HTML);
    $block->set('ajax_url', $ajax_url);
    $block->set('view_all_url', $view_all_url);
    $block->set('manage_links_url', $manage_links_url);
    $block->set('config_HTML', $config_HTML);
    $block->set('mode', $this->mode);
    $block->set('block_type', $block_type);
    $block->set('id_prefix', $id_prefix);
    if ($this->caption_image) {
      $block->set('caption_image', $this->caption_image);
    }
    if ($this->show_filters) {
      $block->set('show_filters', $this->show_filters);
    }
    if ($this->block_heading) {
      $block->set('block_heading', $this->block_heading);
    }
    $block->set('outer_class_name', $this->outer_class_name);
    // $block->set('main_block_id', $this->main_block_id);
    $block->set('html_block_id', $this->html_block_id);
    $block->set('inner_block_id', $inner_block_id);
    $block->set('edit_url', $this->edit_url);
    $block->set('inner_block_data_id', $inner_block_data_id);
    $block->set('configure_block_id', $configure_block_id);
    $block->set('do_pagination', $this->do_pagination);
    $block->set('page_prev', $this->page_prev);
    $block->set('page_next', $this->page_next);
    $block->set('page_first', $this->page_first);
    $block->set('page_last', $this->page_last);
    $block->set('page_links', $this->page_links);
    $block->set('group_owner', $this->group_owner);
    $block->set('group_member', $this->group_member);
    $contents = $block->fetch();

    if(PA::$profiler) PA::$profiler->stopTimer(get_class($this));
    return $contents;              // Return the contents
  }

  function start_form($name_id, $method) {
    $ret = '<form method="'.$method.
      '" name="'.$name_id.
      '" id="'.$name_id.
      '" action="'.($this->post_url ? $this->post_url : "").
      '">';
    // when widgetized, the url specifies the module to post to.  if
    // not, we generate a hidden form_handler input.
    if (!$this->widgetized) {
      $ret .= '<input type="hidden" name="form_handler" value="'.get_class($this).'" />';
    }
    return $ret;
  }

  function input_tag($input_type, $name, $value) {
    return '<input type="'.$input_type.
      '" name="'.htmlspecialchars($this->param_prefix.$name).
      '" value="'.htmlspecialchars($value).
      '" />';
  }

  function textarea_tag($name, $value) {
    return '<textarea name="'.htmlspecialchars($this->param_prefix.$name).
      '">'.htmlspecialchars($value).'</textarea>';
  }

  function submit_tag($value) {
    return '<input type="submit" value="'.htmlspecialchars($value).'" />';
  }

  /**
  * Method will be used for setting the message in the web pages.
  */
  public function setWebPageMessage() {
    if (!empty($this->message)) {
      if (!$this->isError) {//Success
        $message = array('failure_msg'=>NULL, 'success_msg'=>$this->message);
      } else {//Message is a failure message
        $message = array('failure_msg'=>$this->message, 'success_msg'=>NULL);
      }
      @set_web_variables($message, $this->redirect2, $this->queryString);
    }
  }

}
?>
