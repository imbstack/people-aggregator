<?php
include_once dirname(__FILE__) . "/../config.inc";
require_once "web/includes/classes/DynamicPage.class.php";
require_once "web/includes/functions/html_generate.php";


/*

 The following data available for any script now:

 $app - Application BootStrap object created in boot.inc (available in global scope)

 $app->pa_installed
 $app->install_dir        or   PA_INSTALL_DIR
 $app->document_root      or   PA_DOCUMENT_ROOT
 $app->script_dir         or   PA_SCRIPT_DIR
 $app->script_path        or   PA_SCRIPT_PATH
 $app->request_method     or   PA_REQUEST_METHOD
 $app->current_scheme     or   PA_CURRENT_SCHEME
 $app->server_name        or   PA_SERVER_NAME
 $app->http_host          or   PA_HTTP_HOST
 $app->domain_suffix      or   PA_DOMAIN_SUFFIX
 $app->remote_addr        or   PA_REMOTE_ADDR
 $app->request_uri
 $app->base_url           or   PA_BASE_URL
 $app->user_agent         or   PA_USER_AGENT


 This function should be used for retrieving $_REQUEST data to eliminate
 many notice messages generated when you trying to get a $_REQUEST variable
 that does not exists

 $app->getRequestParam($name);



*/


// page ID must be given always!
if($req_page = $app->getRequestParam('page_id')) {

$request_data = $app->getRequestData();

$error     =  false;
$error_msg =  null;

  default_exception();
  if(!empty($request_data['error_msg'])) {
      $error = true;
      $error_msg = $request_data['error_msg'];
  } else if(!empty($request_data['msg'])) {
      $error_msg = $request_data['msg'];
  } else if(!empty($request_data['msg_id'])) {
      $error_msg = $request_data['msg_id'];
  }

  function setup_module($column, $module, $obj, $renderer) {
    global $page_settings, $module_shared_data, $app, $dynamic_page;


    // Dispatch shared data between modules. Those data should be initialized and/or calculated
    // in a block of the PHP code named "boot_code" which is stored in a page XML config file.
    // Note that each module could add new shared data! But usage of these data initialized in
    // a module depends of the modules loading order. So, to avoid this limitation, these new
    // data should be added inside initializeModule() function and should be used in the
    // appropriate action handler function.  Do not forget that. In any other case, you must
    // take care about module loading order. Inside a module, these data could be accessed
    // on this way: $this->shared_data['var_name'] = $value; or $value = $this->shared_data['var_name'];
    //


    // check has a module the method that could be called
    // in any request when 'action' parameter is not given
    if(method_exists($obj, 'handleRequest')) {
      // if method exists - execute it!
      $obj->handleRequest($app->request_method, $app->getRequestData());
    }

    // if 'action' parameter is given in a request, try to find
    // the appropriate module class method to handle it
    if($handler_name = $app->getRequestParam('action')) {
      // check has a module the appropriate method with this name
      // to handle action
      $handler_function = 'handle'. $handler_name;
      // for example: if form action = "/dynamic.php?page_id=7&action=AddGroupSubmit"
      //              this method name will be: handleAddGroupSubmit
      //
      if(method_exists($obj, $handler_function)) {
        // if method exists - execute it!
        $obj->{$handler_function}($app->request_method, $app->getRequestData());
      }
    }
  }


  try {
    $save_page = $app->getRequestParam('save');
    $new_page_settings = ($app->getRequestParam('page_settings'))
                       ? unserialize(urldecode($app->getRequestParam('page_settings')))
                       : null;

    $dynamic_page = new DynamicPage($req_page/*, $settings_new*/); // $settings_new = module settings from constants.php
                                                               // we will try to import page data, if page is defined
                                                               // in constants.php
    $dynamic_page->initialize();
    $dynamic_page->save_page = $save_page;
    $page_settings = $dynamic_page->buildPageSettings($new_page_settings);

    $login_required = ($page_settings['page_mode'] != 'public') ? true : false;
    $use_theme = $page_settings['page_theme'];
    include_once "web/includes/page.php";               // this should be removed when we will have all code moved
                                                        // into page_boot XML data and/or initializeModule() function

    $page = new PageRenderer('setup_module',
                              $page_settings['page_id'],
                              sprintf(__("%s - %s"), __($page_settings['page_name']), $network_info->name),
                              $page_settings['page_template'],
                              $page_settings['header_template'],
                              $page_settings['page_mode'],
                              $page_settings['block_type'],
                              $network_info,
                              NULL,
                              NULL /* $page_settings */);

     if(isset($page_settings['body_attributes'])) {
       $page->html_body_attributes = $page_settings['body_attributes'];
     }

     if(isset($page_settings['javascripts'])) {
       if(is_array($page_settings['javascripts'])) {
         foreach($page_settings['javascripts'] as $js_file) {
           $page->add_header_html(js_includes($js_file));
         }
       } else {
           $page->add_header_html(js_includes($page_settings['javascripts']));
       }
     }

     if(isset($page_settings['page_css'])) {
       if(is_array($page_settings['page_css'])) {
         foreach($page_settings['page_css'] as $css_file) {
           $page->add_header_css("/Themes/$use_theme/$css_file");
         }
       } else {
           $page->add_header_css("/Themes/$use_theme/" . $page_settings['page_css']);
       }
     }
     uihelper_error_msg($error_msg);
     echo $page->render();
  } catch (Exception $e) {
     throw($e);
     exit;
  }
}

  function set_error($er) {
    global $error, $error_msg;
    $error = TRUE;
    $error_msg = $er;
  }

/* example for adding error module to page top

  if ( $error_msg ) {
    $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
    $msg_tpl->set('message', $error_msg);
    $m = $msg_tpl->fetch();
    $page->add_module("middle", "top", $m);
  }
*/
?>
