<?php
require_once 'api/Cache/FileCache.php';
require_once 'web/includes/classes/DynamicPage.class.php';
require_once 'web/includes/functions/html_generate.php';

//
// Brief description:
//
// Load and render a dynamic page and dispatch shared data between modules.
// The shared data should be initialized in a block of the PHP code named
// "boot_code" which is stored in a page XML config file.
// Note that each module could add new shared data but usage of these data
// initialized in a module depends of the modules loading order.

// page ID must be given always!
if ($req_page = $app->getRequestParam('page_id'))
{
    $request_data = $app->getRequestData();
    $error = false;
    $error_msg = null;
    if (!empty($request_data['error_msg']))
    {
        $error = true;
        $error_msg = $request_data['error_msg'];
    }
    elseif (!empty($request_data['msg']))
    {
        $error_msg = $request_data['msg'];
    }
    elseif (!empty($request_data['msg_id']))
    {
        $error_msg = $request_data['msg_id'];
    }

    function setup_module($column, $module, $obj, $renderer)
    {
        global $page_settings, $module_shared_data, $app, $dynamic_page;

        // check has a module the method that could be called
        // in any request when 'action' parameter is not given
        if (method_exists($obj, 'handleRequest'))
        {
            $obj->handleRequest($app->request_method, $app->getRequestData());
        }
        // if 'action' parameter is given in a request, try to find
        // the appropriate module class method to handle it
        if ($handler_name = $app->getRequestParam('action'))
        {
            $handler_function = 'handle'. $handler_name;
            // check has a module the appropriate method with this name to handle action
            // for example: if form action = "/dynamic.php?page_id=7&action=AddGroupSubmit"
            //              name of this method will be: handleAddGroupSubmit
            if (method_exists($obj, $handler_function))
            {
                $obj->{$handler_function}($app->request_method, $app->getRequestData());
            }
        }
    }

    global $use_theme;  // Theme name defined in page XML config file
    try
    {
        $save_page = $app->getRequestParam('save');
        $new_page_settings = ($app->getRequestParam('page_settings')) ? unserialize(urldecode($app->getRequestParam('page_settings'))) : null;
        $cache_id = "dyn_page_$req_page";
        if (FileCache::is_cached($cache_id))
        {
            $dynamic_page = FileCache::fetch($cache_id);
            $page_settings = $dynamic_page->buildPageSettings($new_page_settings);
        }
        else
        {
            $dynamic_page = new DynamicPage($req_page);
            $dynamic_page->initialize();
            $dynamic_page->save_page = $save_page;
            $page_settings = $dynamic_page->buildPageSettings($new_page_settings);
            FileCache::store($cache_id, $dynamic_page, 1200);
        }
        // Force login if we're on a private network, unless we're on login.php, register.php or dologin.php.
        $login_required = ($page_settings['page_mode'] != 'public') ? true : false;
        if (!$login_required && PA::$network_info->is_private() && !@$login_never_required)
        {
            $login_required = TRUE;
        }
        // Check user session / login status, and redirect to login page (or
        // request page, for private networks) if required.
        if (!check_session($login_required, @$page_redirect_function))
        {
            if ($login_required)
            {
                exit;
            }
        }
        $use_theme = $page_settings['page_theme'];
        $page = new PageRenderer('setup_module', $page_settings['page_id'], sprintf(__('%s - %s'), __($page_settings['page_name']), PA::$network_info->name), $page_settings['page_template'], $page_settings['header_template'], $page_settings['page_mode'], $page_settings['block_type'], PA::$network_info, NULL, NULL/* $page_settings */
        );
        if (isset($page_settings['body_attributes']))
        {
            $page->html_body_attributes = $page_settings['body_attributes'];
        }
        if (isset($page_settings['javascripts']))
        {
            if (is_array($page_settings['javascripts']))
            {
                foreach ($page_settings['javascripts'] as $js_file)
                {
                    $page->add_header_html(js_includes($js_file));
                }
            }
            else
            {
                $page->add_header_html(js_includes($page_settings['javascripts']));
            }
        }
        if (isset($page_settings['page_css']))
        {
            if (is_array($page_settings['page_css']))
            {
                foreach ($page_settings['page_css'] as $css_file)
                {
                    $page->add_header_css("/Themes/$use_theme/$css_file");
                }
            }
            else
            {
                $page->add_header_css("/Themes/$use_theme/" . $page_settings['page_css']);
            }
        }
        uihelper_error_msg($error_msg);
        echo $page->render();
    }
    catch (Exception $e)
    {
        throw($e);
        exit;
    }
}
?>