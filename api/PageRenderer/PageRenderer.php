<?php

require_once "api/ModuleSetting/ModuleSetting.php";
require_once "api/Message/Message.php";
require_once "api/Advertisement/Advertisement.php";
require_once "api/Theme/Template.php";
require_once "web/BlockModules/Module/Module.php"; // for PRI, HOMEPAGE constants
require_once "web/includes/classes/DynamicPage.class.php";
require_once "api/Permissions/PermissionsHandler.class.php";
require_once "api/ModuleData/ModuleData.php";
require_once "web/includes/classes/Navigation.php";

// PageRenderer docs
/**
 *

  Use the PageRenderer class to load the left, middle and right
  modules from the database and do most of the work of rendering a
  page.

  Usage:

   // create renderer object and run all modules
   $page = new PageRenderer("setup_modules", PAGE_INVITATION, "Invite people", "media_gallery_pa.tpl");

   // if you want to add more modules, do it like this:
   $page->add_module("middle", "top", "<p>this html will be added to the top of the middle column</p>");

   // and display:
   echo $page->render();

*/

// PageRenderer class
class PageRenderer {
  // Properties
  // template used to render this page (e.g. "user_pa.tpl")
  public $page_template;

  // template used to render the header (e.g. "header.tpl")
  public $header_template;

  // setting data loaded with ModuleData::load_setting
  public $setting_data;

  // extra attributes to go in the html <body> tag
  public $html_body_attributes;

  // extra network_info to be added
  public $network_info;

  // javascript code to run on page load
  public $onload;

  // extra html to go in the <head> block, added with add_header_html()
  private $extra_head_html;

  // full-width content, to go below the header and above the modules
  private $fullwidth_content = "";

  // rendered modules
  private $module_arrays;

  // JS includes - array with all JS files that are included via add_header_js method
  private $js_includes = array();

  // JS includes - array with all JS files that are included via add_header_js method
  private $js_includes_dont_optimize = array();

  // CSS includes - array with all CSS files that are included via add_header_css method
  private $css_includes = array();

  // CSS includes - array with all CSS files that are included via add_header_css method but force
  // optimization to not work on them
  private $css_includes_dont_optimize = array();

  // __construct($cb, $page_type, $title, $page_template="homepage_pa.tpl", $header_template="header.tpl", $default_mode=PRI, $default_block_type=HOMEPAGE, PA::$network_info_ = NULL, $onload = NULL, $setting_data = NULL)
  function __construct($cb, $page_id, $title, $page_template="homepage_pa.tpl", $header_template="header.tpl", $default_mode=PRI, $default_block_type=HOMEPAGE, $network_info_ = NULL, $onload = NULL, $setting_data = NULL) {

    global $app, $page;

    if(PA::$profiler) PA::$profiler->startTimer('PageRenderer_init');

    // we may want to know the page_tpe elsewhere too
    PA::$config->page_type = $page_id;     // NOTE: PA::$config->page_type var = $page_id and should be removed!

    $this->page_id = $page_id;
    $this->debugging = isset($_GET['debug']);

    $this->page_template = $page_template;
    $this->top_navigation_template = 'top_navigation_bar.tpl'; //TO DO: Remove this hardcoded text afterwards
    $this->header_template = $header_template;
    //settings for current network
    $this->network_info = $network_info_ ? $network_info_ : PA::$network_info; //FIXME: does this have to be a parameter?  can't we just always use the global PA::$network_info?
    $this->module_arrays = array();

    // the function hide_message_window is added here
    // so whenever html page is loaded the message window's ok button gets focus
    // here if previouly some function is defined as
    // onload = "ajax_call_method(ajax_titles, $uid, ajax_urls);"
    // now it will look like
    // onload = "ajax_call_method(ajax_titles, $uid, ajax_urls); hide_message_window();"

    $this->onload = "$onload hide_message_window('confirm_btn');";
    $this->page_title = $title;
    $this->html_body_attributes = "";
    // default settings for the tiers
    $this->main_tier = @$_GET['tier_one'];
    $this->second_tier = @$_GET['tier_two'];
    $this->third_tier = @$_GET['tier_three'];

    $navigation = new Navigation;
    $this->navigation_links = $navigation->get_links();
    $this->message_count = null;

    if(!isset(PA::$login_uid)) {
      PA::$login_uid = @$_SESSION['user']['id'];
    }

    if (PA::$login_uid) {
      $this->message_count = Message::get_new_msg_count(PA::$login_uid);
    }

    if(!isset($dynamic_page)) {
      $dynamic_page = new DynamicPage($this->page_id);
      if(!is_object($dynamic_page) or !$dynamic_page->docLoaded) {
        throw new Exception("Page XML config file for page ID: $page_id - not found!");
      }
      $dynamic_page->initialize();
    }

    if( (false !== strpos($dynamic_page->page_type, 'group'))
          && ((!empty($_REQUEST['gid'])) || (!empty($_REQUEST['ccid']))) )  { // page is a group page - get group module settings
        $_gr_id = (!empty($_REQUEST['gid'])) ? $_REQUEST['gid'] : $_REQUEST['ccid'];
        $this->setting_data = ModuleSetting::load_setting($this->page_id, $_gr_id, 'group');
        $this->page_template = $this->setting_data['page_template'];
        if(empty($this->setting_data['access_permission'])) {  // no permissions required to access page
          $access_permission = true;
        } else {
          $access_permission = PermissionsHandler::can_group_user(PA::$login_uid, $_gr_id, array('permissions' => $this->setting_data['access_permission']));
        }
    } else
      if((false !== strpos($dynamic_page->page_type, 'user'))
          && (!empty(PA::$login_uid))) {   // page is an user page - get user module settings
//          echo "POSTING TO USER PAGE"; die();
        $this->setting_data = ModuleSetting::load_setting($this->page_id, PA::$login_uid, 'user');
        $this->page_template = $this->setting_data['page_template'];
        if(empty($this->setting_data['access_permission'])) {  // no permissions required to access page
          $access_permission = true;
        } else {
          $access_permission = PermissionsHandler::can_user(PA::$login_uid, array('permissions' => $this->setting_data['access_permission']));
        }
    } else {                             // page is a network page - get network module settings
        $this->setting_data = ModuleSetting::load_setting($this->page_id, PA::$network_info->network_id, 'network');
        $this->page_template = $this->setting_data['page_template'];
        if(empty($this->setting_data['access_permission'])) {  // no permissions required to access page
          $access_permission = true;
        } else {
          $access_permission = PermissionsHandler::can_network_user(PA::$login_uid, PA::$network_info->network_id, array('permissions' => $this->setting_data['access_permission']));
        }
    }

    $this->page = & new Template(CURRENT_THEME_FSPATH."/".$this->page_template);
    $this->page->set('current_theme_path', PA::$theme_url);
    $this->page->set('current_theme_rel_path', PA::$theme_rel);

    // Loading the templates variables for the Outer templates files
    $this->page->set('outer_class', get_class_name(PA::$config->page_type));
    $this->top_navigation_bar = & new Template(CURRENT_THEME_FSPATH."/".$this->top_navigation_template);
    $this->top_navigation_bar->set('current_theme_path', PA::$theme_url);
    $this->top_navigation_bar->set('current_theme_rel_path', PA::$theme_rel);
    $this->top_navigation_bar->set('navigation_links', $this->navigation_links);
    $this->setHeader($this->header_template);
    $this->footer = & new Template(CURRENT_THEME_FSPATH."/footer.tpl");
    $this->footer->set('current_theme_path', PA::$theme_url);
    $this->footer->set('page_name', $title);

    $page = $this;
    $this->preInitialize($this->setting_data);
    $this->initNew($cb, $default_mode, $default_block_type, $this->setting_data);

    if (!$access_permission) {
     $configure = unserialize(ModuleData::get('configure'));
     if(PA::logged_in()) {
       $redir_url = PA::$url . PA_ROUTE_USER_PRIVATE;
     } else if((!isset($configure['show_splash_page'])) || $configure['show_splash_page'] == INACTIVE) {
       $redir_url = PA::$url . '/' . FILE_LOGIN;
     } else {
       $redir_url = PA::$url;
     }

      $er_msg = urlencode("Sorry! you are not authorized to to access this page.");
      $this->showDialog($er_msg, $type = 'error', $redir_url, 10);
    }
    if(PA::$profiler) PA::$profiler->stopTimer('PageRenderer_init');
  }//end of constructor

  public function setHeader($header_tpl) {
    $this->header = & new Template(CURRENT_THEME_FSPATH."/".$header_tpl);
    $this->header->set('current_theme_path', PA::$theme_url);
    $this->header->set('current_theme_rel_path', PA::$theme_rel);
    $this->header->set_object('network_info', $this->network_info);
    $this->header->set('message_count', $this->message_count['unread_msg']);
    $this->header->set('navigation_links', $this->navigation_links);
  }


  private function preInitialize($setting_data) {
    global $app, $dynamic_page;
    global $page_uid, $uid;

    if (!empty(PA::$config->simple['use_actionsmodule'])) {
    	switch (PA::$config->actionsmodule_placement) {
    		case 'right':
                $side = PA::$config->actionsmodule_placement;
            break;
    		case 'left':
    			$side = PA::$config->actionsmodule_placement;
    		break;
    		default:
		    	$side = 'right';
	    	break;
    	}
			if (isset($this->setting_data[$side]) && is_array($this->setting_data[$side])) {
				// add in the ActionsModule to any page
				array_unshift($this->setting_data[$side], 'ActionsModule');
			}
    }

    $module_shared_data = array();
    if(!empty($setting_data['boot_code'])) {
       if(false == eval($setting_data['boot_code'] ."return true;")) {
         echo "<b>Evaluation of page boot code for page ID=".$setting_data['page_id']." failed".
              "Please check your  \"page_".$setting_data['page_id'].".xml\"  configuration file." ;
         }
    }

    $this->modules_array = array();
    foreach (array("toprow", "middle", "left", "right") as $module_column) {
      $column_modules = (!PA::$config->page_type) ? array() : @$this->setting_data[$module_column];
			if (count($column_modules) > 0) {
				foreach ($column_modules as $moduleName) {

					if (!$moduleName) continue;

					$file = PA::$blockmodule_path . "/$moduleName/$moduleName.php";

					// check if module file exists
					$module_exists = false;
					if (file_exists(PA::$project_dir."/$file")) {
						$module_exists = true;
					} elseif (file_exists(PA::$core_dir."/$file")) {
						$module_exists = true;
					}

					if (! $module_exists) {
						echo "<div class='module_error'>Module $moduleName does not exist.</div>";
						continue;
					}

					try {
							require_once($file);
					} catch (Exception $e) {
							echo "<p>Failed to require_once $file.</p>";
							continue;
							throw $e;
					}
					$obj = new $moduleName;
					$obj->login_uid = (int)PA::$login_uid; // uid of logged in user
					$obj->page_uid  = (int)$page_uid;      // uid specified in URL
					$obj->column    = $module_column;
					$obj->page_id   = (!empty($setting_data['page_id'])) ? $setting_data['page_id'] : PA::$config->page_type;
					$obj->renderer     = &$this;                     // dispatch page renderer object to all modules
					$obj->controller   = &$app;                      // dispatch front controller object to all modules
					$obj->dynamic_page = &$dynamic_page;             // dispatch DynamicPage object to all modules
					$obj->shared_data  = &$module_shared_data;
					$obj->module_name  = $moduleName;
					$this->modules_array[] = $obj;
				}
      }
    }

    foreach($this->modules_array as $module) {
      // check has a module the method that must be called
      // to initialize module data and settings
      $module->skipped = false;
      if(method_exists($module, 'initializeModule')) {
        // if method exists - execute it!
        $initResult = $module->initializeModule($app->request_method, $app->getRequestData());
        // module can send "skip" signal and it will be excluded
        // from rendering
        if($initResult == "skip") {
          $module->skipped = true;
        }
      }
    }
  }


  private function initNew($cb, $default_mode, $default_block_type, $setting_data) {
    global $page_uid, $uid;

    // render all modules
    foreach ($this->modules_array as $module) {
        // standard column-specific initialization
        switch ($module->column) {
        case 'left':
        case 'right':
          if ($default_mode) $module->mode = $default_mode;
          // some modules don't like to be set as PRI/HOMEPAGE
          switch ($module->module_name) {
          case 'LogoModule':
          case 'AdsByGoogleModule':
          case 'GroupAccessModule':
          case 'GroupStatsModule':
              break;
          default:
              if ($default_block_type) $module->block_type = $default_block_type;
          }
          break;
        case 'middle':
          break;
        }

        // now call the page callback and see if we need to skip
        // displaying this module
        $skipped = $module->skipped;
        if ($cb && !$skipped) {
            switch ($cb($module->column, $module->module_name, $module, $this)) {
            case 'skip':
                $skipped = TRUE;
                break;
             }
        }

        // now render for display
        $render_time = NULL;
        if (!$skipped) {
            $start_time = microtime(TRUE);
            $html = $module->render();
            if (!$module->do_skip) {//add the module to list if it is not being skipped from within the module.
              $render_time = microtime(TRUE) - $start_time;
              $this->module_arrays[$module->column][] = $html;
            }
        }

        if ($this->debugging) {
            $dhtml = "&larr; $module->module_name ($module->block_type; $module->mode; ".sprintf("%.3f s", $render_time).")";
            if ($skipped) $dhtml .= " SKIPPED";
            $dhtml .= "<br>";
            $this->module_arrays[$module->column][] = $dhtml;
        }
      }

/*
    $pages = Advertisement::get_pages(); // get pages where ads is to be displayed
    $display_ad = FALSE;
    foreach ($pages as $page) {
      if (PA::$config->page_type == $page['value']) {
        $display_ad = TRUE;
        break;
      }
    }
*/

    // the following code does exactly the same as
    // the above commented, only much more efficient
    // because it'snot loading ALL pages
    $display_ad = false;
    if (preg_match("/(network|group)/", $this->setting_data['page_type'])) {
    	$display_ad = true;
    }


    if ($display_ad) {
      // get all ads
      $netw_ads = array();
      try {
				$netw_ads = Advertisement::get(array(
					'direction'=>'ASC',
					'sort_by'=>'orientation'),
					array(
						'page_id' => PA::$config->page_type,
						'is_active' => ACTIVE,
						'group_id' => NULL));
      } catch (PAException $e) {
      	Logger::log(__FILE__." ".$e->getMessage(), 32);
      }

      $group_ads = array();
      if (!empty($_REQUEST['gid'])) {
				try {
					$group_ads = Advertisement::get(array(
					'direction'=>'ASC',
					'sort_by'=>'orientation'),
					array(
						'page_id' => PA::$config->page_type,
						'is_active' => ACTIVE,
						'group_id' => (int)$_REQUEST['gid']));
				} catch (PAException $e) {
					Logger::log(__FILE__." ".$e->getMessage(), 32);
				}
      }
      $all_ads = array();
      foreach($group_ads as $ad) $all_ads[] = $ad;
      foreach($netw_ads as $ad) $all_ads[] = $ad;

      if (!empty($all_ads)) {
        foreach($all_ads as $ad) {
          $pos = $ad->orientation;
          $pos = explode(',', $ad->orientation);
          $x_loc = $pos[0];
          //y_loc was not originally designed so for already created ads
          //FIX for already created ads
          if (array_key_exists(1, $pos)) {
            $y_loc = $pos[1];
          } else {
            $y_loc = 1;//Ads created before this logic implementation should come on top
          }

          //horizontal and vertical position should not be empty
          if (!empty($x_loc) && !empty($y_loc)) {
            $array_of_data = array('links' => $ad);
            $inner_html =  $this->add_block_module('AdvertisementModule', $array_of_data);

            $this->add_module_xy($x_loc, $y_loc, $inner_html);
          }
        }
      }//end of if all_ads
    }//end of display_ad

  }



  //
  // add_module($column, $vertical_position, $module)
  // Call this to add a module prior to display - e.g. user.php uses
  // this to add the UserCaptionModule and invitation.php uses it to
  // add status messages.
  //
  // e.g. $page->add_module("middle", "top", "<p>this is a test</p>");
  // will add 'this is a test' to the top of the middle module on your
  // page.
  function add_module($column, $vertical_position, $module) {
  	if (empty($this->module_arrays[$column]))
  		$this->module_arrays[$column] = array();
    switch ($vertical_position) {
    case 'top':
      // put the module on the start of the list
      array_unshift($this->module_arrays[$column], $module);
      break;

    case 'bottom':
      // put the module on the end of the list
      array_push($this->module_arrays[$column], $module);
      break;

    default:
      throw new PAException("", "Invalid vertical position $vertical_position");
    }
  }

  public function add_module_to_list($column, $vertical_position, $module_name) {
    switch ($vertical_position) {
    case 'top':
      // put the module on the start of the list
      array_unshift($this->setting_data[$column], $module_name);
      break;

    case 'bottom':
      // put the module on the end of the list
      array_push($this->setting_data[$column], $module_name);
      break;

    default:
      throw new PAException("", "Invalid vertical position $vertical_position");
    }
  }

  public function remove_module_from_list($column, $module_name, $all_modules = false) {
    if($all_modules) {
       foreach($this->setting_data[$column] as $key => $value) {
         unset($this->setting_data[$column][$key]);
       }
    } else {
       $key = array_search($module_name, $this->setting_data[$column]);
       if($key !== false) {
         unset($this->setting_data[$column][$key]);
       }
    }
  }


  //
  // add_module_xy($x, $y, $module)
  // Call this to add a module prior to display - at given co-ordinates
  // e.g. $page->add_module("1", "1", "<p>this is a test</p>");
  // will add 'this is a test' to the left top on your
  // page. Designed to meet the needs of advertisement module but can be extended
  function add_module_xy($x_loc, $y_loc, $module) {
    //identify the column of the modules
    switch ($x_loc) {
      case 1:
        $x = 'left';
        break;
      case 2:
        $x = 'middle';
        break;
      case 3:
        $x = 'right';
        break;
      default:
        throw new PAException("", "Invalid horizontal position $x_loc");
    }
    if(empty($this->module_arrays[$x])) $this->module_arrays[$x] = array();
    $max_y_loc = count($this->module_arrays[$x]) + 1;
    if ($y_loc > $max_y_loc) {
      //if y location is greater then max array index then add module to last
      $y_loc = $max_y_loc;
    }
    $left = array_slice ($this->module_arrays[$x], 0, $y_loc-1);
    $right = array_slice ($this->module_arrays[$x], $y_loc-1);
    $insert[0] = $module;
    $array = array_merge ($left, $insert, $right);
    $this->module_arrays[$x] = $array;


  }
  //
  // add_fullwidth_content($content)
  // Give this function some HTML if you want it to display at full
  // width, above the three module columns.  (see
  // comment_management.php for why this might be useful).
  function add_fullwidth_content($content) {
    $this->fullwidth_content .= $content;
  }
  //
  // rotate_modules($column, $repeat=1)
  // Call this function to move the top module in a column to the
  // bottom.  To move more than one module, pass in a value for
  // $repeat.
  public function rotate_modules($column, $repeat=1) {
      for ($i = 0; $i < $repeat; ++$i) {
	  array_push($this->module_arrays[$column], array_shift($this->module_arrays[$column]));
      }
  }
  //
  //  add_header_html($html)
  // add extra html to go in the header
  public function add_header_html($html) {
      if (preg_match("/^\s*$/", $html)) {
          return;
      }
      $this->extra_head_html .= $html."\n";
  }
  //
  //  add_header_css($file)
  public function add_header_css($file, $optimize = true) {
      if (preg_match("/^\s*$/", $file)) {
          return;
      }
      $path = '';
      $file = trim($file);
      $sanity_check = explode(DIRECTORY_SEPARATOR, $file);
      if (1 < count($sanity_check)) {
          $file = array_pop($sanity_check);
          $path .= implode(DIRECTORY_SEPARATOR, $sanity_check);
          $path .= DIRECTORY_SEPARATOR;
      }
      $this->css_includes[$path][$file] = $file;
      if ($optimize = false) {
          $this->css_includes_dont_optimize[$path][$file] = $file;
      }
  }
  //
  // add_header_js($file, $optimize = true)
  public function add_header_js($file, $optimize = true) {
    js_includes($file, $optimize);
  }

    /**
     * Add JavaScritp file with any path.
     *
     * Call with file name that contain relative path
     *
     *
     * Example: add_page_js('/')
     *
     * @param string $file JavaScript file
     */
  public function add_page_js($file) {
    global $js_includes;

    $path_info = pathinfo($file);
    if(!empty($path_info['dirname']) && !empty($path_info['basename'])) {
      $path = $path_info['dirname'];
      $file = $path_info['basename'];
      $js_includes[$path.DIRECTORY_SEPARATOR][$file] = $file;
    }
  }

    /**
     * Remove header JavaScritp file.
     *
     * Call with file name that you want to remove from global $js_includes array
     * and prvide also full URL path to the file.
     *
     * Example: remove_header_js(PA::$theme_url.'/javascript/', 'jquery.lite.js')
     *
     * @param string $path full URL path
     * @param string $file JavaScript file
     */
    public function remove_header_js($path, $file) {

        global $js_includes;

        if (isset($js_includes[PA::$theme_url.'/'])) {
            if (array_key_exists($file, $js_includes[PA::$theme_url.'/'])) {
                unset($js_includes[$js_includes[PA::$theme_url.'/'][$file]]);
            }
        }
    }

    /**
     * Get extra HEAD HTML code that was injected by PA code.
     *
     * This method generates JS and CSS includes that are added via add_header_js() and
     * add_header_css() methods.
     *
     * This method also generates optimization code. Have a look at default_config.php for the
     * configuration options.
     *
     * @return string $extra_head_html HTML snippet with relevant code
     */
    private function get_extra_head_html() {
        global $js_includes;
        global $js_includes_dont_optimize;
        // global var $_base_url has been removed - please, use PA::$url static variable

        global $use_js_optimizer;
        global $use_js_packer;
        global $use_css_optimizer;
        global $cssjs_tag;
        global $optimizers_use_url_rewrite;

        $extra_head_html = '';

        if ($this->debugging) {
            $cssjs_tag = rand(1000000, 9999999);
        }

        $combinator_url_js_prefix = '';
        $combinator_url_css_prefix = '';
        $cssjs_tag_prefix = '?';
        if (true === $use_js_optimizer && false === $optimizers_use_url_rewrite) {
            $combinator_url_js_prefix = '/combinator.php?t=javascript&f=';
            $combinator_url_css_prefix = '/combinator.php?t=css&f=';
            $cssjs_tag_prefix = '&cssjs_tag=';
        }

        $default_path_array = parse_url(PA::$theme_url);
        $default_path = $default_path_array['path'];
        // Set base_url
        $extra_head_html .= '<script type="text/javascript" language="javascript">';
        $extra_head_html .= 'var base_url = "'.addslashes(PA::$url).'";';
        $extra_head_html .= 'var CURRENT_THEME_PATH = "'.addslashes(PA::$theme_url).'";';
        $extra_head_html .= '</script>'."\n";
        // Load JQuery
        $extra_head_html .= '<script type="text/javascript" ';
        $extra_head_html .= 'language="javascript" src="'.$combinator_url_js_prefix.$default_path;
        $extra_head_html .= '/javascript/jquery.lite.js'.$cssjs_tag_prefix.$cssjs_tag.'"></script>'."\n";
        // Load base_javascript
        $extra_head_html .= '<script type="text/javascript" ';
        $extra_head_html .= 'language="javascript" src="'.$combinator_url_js_prefix.$default_path;
        $extra_head_html .= '/base_javascript.js'.$cssjs_tag_prefix.$cssjs_tag.'"></script>'."\n";

        if (!empty($js_includes)) {
            $this->remove_header_js(PA::$theme_url.'/javascript/', 'jquery.lite.js');
            $this->remove_header_js(PA::$theme_url.'/', 'base_javascript.js');
            foreach ($js_includes as $path => $files) {
                if (true === $use_js_optimizer && !isset($js_includes_dont_optimize[$path][$files])) {
                    $parsed_path = parse_url($path);
                    $files_string = implode(':', $files);
                    $extra_head_html .= '<script type="text/javascript" ';
                    $extra_head_html .= 'language="javascript" ';
                    $extra_head_html .= 'src="'.$combinator_url_js_prefix.$parsed_path['path'];
                    $extra_head_html .= $files_string;
                    $extra_head_html .= $cssjs_tag_prefix.$cssjs_tag.'"></script>'."\n";
                } else {
                    foreach ($files as $file) {
                        $extra_head_html .= '<script type="text/javascript" ';
                        $extra_head_html .= 'language="javascript" src="';
                        $extra_head_html .= $path.$file.$cssjs_tag_prefix.$cssjs_tag.'"></script>'."\n";
                    }
                }
            }
        }

        if (!empty($this->css_includes)) {
            foreach ($this->css_includes as $path => $files) {
                if (true === $use_css_optimizer && !isset($this->css_includes_dont_optimize[$path][$files])) {
                    $parsed_path = parse_url($path);
                    $files_string = implode(':', $files);
                    $extra_head_html .= '<link rel="stylesheet" type="text/css" ';
                    $extra_head_html .= 'href="'.$combinator_url_css_prefix.$parsed_path['path'];
                    $extra_head_html .= $files_string.$cssjs_tag_prefix.$cssjs_tag.'" />'."\n";
                } else {
                    foreach ($files as $file) {
                        $extra_head_html .= '<link rel="stylesheet" type="text/css" ';
                        $extra_head_html .= 'href="'.$path.$file.$cssjs_tag_prefix.$cssjs_tag.'" />'."\n";
                    }
                }
            }
        }

        // add specific body background-image style if so defined in the network info
        $extra = unserialize($this->network_info->extra);

        if (! empty($extra['basic']['background_image']['name'])) {
          $extra_head_html .= "<style>body {background-image:url("
            . PA::$url . "/files/" . trim($extra['basic']['background_image']['name'])
            . ") !important;"
            // . "	background-repeat: no-repeat;"
            . "}\n"

            // TODO: split the following CSS out into a css file
            // and @import it
            . "#footer {color: #aaaaaa;}\n"
            . "#container, #content, #col_a, #col_b, #col_c {
              background-image: url(none);
              background-color: transparent !important;
              }\n" // , #col_d
            . "#AdvertisementModule {
              border: none;
              background-image: url(none);
              background-color: transparent !important;
              }\n"
            . "#col_b h1, #col_d h1 {color: #fff;}\n"
            . "#col_b .wide_content .date {color: #fff;}\n"
            . "#body_shadow {
            visibility: hidden;
            }\n"
            . ".wide_content form
            {background-color: #fff;}\n"
            . "#LoginModule H1, #GroupsDirectoryModule H1, #SearchContentModule H1, #PeopleModule H1, #CustomizeUIModule H1, #MediaFullViewModule H1,
            #col_d .total_content H1, #PermalinkModule H1, #RegisterModule H1
            {color: #09F;}\n"
            . "#LoginModule, #GroupsDirectoryModule, #SearchContentModule, #PeopleModule, #CustomizeUIModule, #PostContentModule, #MediaFullViewModule, .total_content,
            #class_description, #RegisterModule
            {background-color: #fff;}\n"
            . "</style>\n";
        }


        $extra_head_html .= $this->extra_head_html;

        return $extra_head_html;
    }

  // This function is used to render a block module
  // $mdoule name specifies the block module to be rendered
  // $array_of_data specifies the variables to be set for that block module

  public function add_block_module($modulename, $array_of_data) {
    if (!empty($modulename)) {
      $file = PA::$blockmodule_path . "/$modulename/$modulename.php";
      try {
        require_once $file;
      } catch (PAException $e) {
        echo "<p>Failed to require_once $file.</p>";
      }
      $module_obj = new $modulename;
      foreach ($array_of_data as $key => $value) {
        $module_obj->$key = $value;
      }
      $inner_html = $module_obj->render();
      if ($inner_html == 'skip') return false;
      return $inner_html;
    }
    return false;
  }

  public function showDialog($msg = null, $type = 'error', $redirect_url = null, $redirect_delay = 0) {
   global $app;
    require_once PA::$blockmodule_path . "/ShowMessageModule/ShowMessageModule.php";
    $obj = new ShowMessageModule();
    $obj->renderer = &$this;
    $this->add_header_html(js_includes('message_dialogs.js'));
    $use_theme = $this->setting_data['page_theme'];
    // temporary hack
    if ($use_theme == 'Beta') $use_theme == 'Default';
    $this->add_header_css("/Themes/$use_theme/message_dialogs.css");

    if($msg)            $obj->error_msg = $msg;
    if($type)           $obj->type = $type;
    if($redirect_url)   $obj->redirect_url = $redirect_url;
    if($redirect_delay) $obj->redirect_delay = $redirect_delay;

    $obj->initializeModule($app->request_method, $app->getRequestData());

    $html = $obj->render();
    $this->module_arrays['middle'] = null;
    $this->module_arrays['left'] = null;
    $this->module_arrays['right'] = null;
    $this->page_template = 'container_one_column_full_width.tpl';
    $this->page = & new Template(CURRENT_THEME_FSPATH."/".$this->page_template);
    $this->page->set('current_theme_path', PA::$theme_url);
    $this->page->set('current_theme_rel_path', PA::$theme_rel);
    $this->add_module("middle", "top", $html);
  }

  // call $page->render() to get the html to display.
  function render() {

    if(PA::$profiler) PA::$profiler->startTimer('PageRenderer_render');
    // Get HTML of HEADer section
    $extra_head_html = $this->get_extra_head_html();
    html_header($this->page_title, $extra_head_html);

    if ($this->onload) $this->html_body_attributes .= ' onload="'.$this->onload.'"';
    html_body($this->html_body_attributes);

    $this->header->set('onload', $this->onload);
    $this->header->set('error', @$_GET['error']);

    $this->header->tier_one_tab = $this->main_tier;
    $this->header->tier_two_tab = $this->second_tier;
    $this->header->tier_three_tab = $this->third_tier;

    $this->page->set("fullwidth_content", $this->fullwidth_content);

    foreach ($this->module_arrays as $module_column => $array_modules) {
        $this->page->set('array_'.$module_column.'_modules', $array_modules);
    }

    $this->page->set('top_navigation_bar', $this->top_navigation_bar);
    $this->page->set('header', $this->header);
    $this->page->set('footer', $this->footer);
    $this->page->set('current_theme_path', PA::$theme_url);

    $res = $this->page->fetch();
    if(PA::$profiler) PA::$profiler->stopTimer('PageRenderer_render');
    return $res;
  }

}

?>