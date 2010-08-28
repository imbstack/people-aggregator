<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* Context sensitive links to all of the possible things that a user could do.
*
* On their private and public pages they can:
* view their page
* add widgets
* view messages
* view gallery
* view and edit their calendar
* view their friends
* go to their personal forum
* *families* not yet implemented as of 2 June 2010
* Edit their account
* change up their themes
*
* On their friends pages they can:
* send a message to the friend
* chang the relationship level
* end their friendship
*
* On a group page:
* go to the group home
* go to the group forum
* view group members
* view group gallery
* view group events
* join/leave the group
* if they own it they can:
*   delete the group
*   change the group theme
*
* On the people page:
* find people
* view only their friends
* view their friends galleries
*
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/

require_once "api/Network/Network.php";

class ActionsModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'left|right';
  public  $uid;
  public $outer_template = 'outer_public_side_module.tpl';
  function __construct() {

  parent::__construct();
    parent::__construct();
    $this->title = __('Actions');
    $this->html_block_id = 'ActionsModule';

  }

    /** !!
     * This handles the situation that there has been no inner_HTML generated
     * and then calls {@link Module::render() }.
     *
     * @return string $content  The html code specific to this module, and its outer html
    */
 function render() {
    $this->inner_HTML = $this->generate_inner_html();
    if (! $this->inner_HTML) {
      return "";
    }
    $content = parent::render();
    return $content;
  }

  /** !!
     * This figures out what page the module is being used on and generates 
     * the specific links that are needed for this page.  It implies that
     * this is to be used as a "tier 3" navigation bar.
     *
     * @todo do something about unused parameters
     */
  public function initializeModule($request_method, $request_data) {

     
    $this->navigation_links =
    	$this->renderer->top_navigation_bar->vars['navigation_links'];

    $ac =
	  	(array)@$this->navigation_links['level_3']
	  	+ (array)@$this->navigation_links['left_user_public_links']
		  ;
		  $actions = array();
		  foreach($ac as $k=>$action) {
		  	if ($k=='highlight') continue;
		  	$actions[$k] = $action;
		  }


    if (empty($actions)) {
      return 'skip';
    }
    $this->actions = $actions;
  }

    /** !!
     * This generates the page specific html to be passed on to the render function.
     * It uses the standard templates to achieve this.
     *
     * @return string $inner_html  The aforementioned page specific html
     */
  function generate_inner_html() {

    // $this->title .= "$page_name";
    $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/side_inner_public.tpl';
    $inner_html_gen = new Template($inner_template, $this);
    $inner_html_gen->set('actions', $this->actions);
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>
