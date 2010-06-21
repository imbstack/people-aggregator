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
/**
 * PeopleAggregator Comman widgets Handler
 *
**/
//error_reporting(E_ALL);
$login_required = FALSE;
$use_theme = 'Beta';
include_once("web/includes/page.php");

/*
 Function to validate whether the widget exists in the system and if so then the required arguments are passed or not.
*/
function widget_exist($name, $arguments) {

    /*
     * Steps to validate:-
     * 1. Check whether the directory Widgets exist or not.
     * 2. Check whether the widget with $name exist or not.
     * 3. IF the $name folder exist in the the Widgets directory then check for the required variables .
     * 4. If we have reached so far then the desired folder structure of widget is :-
          Widget
               |Comment(comment widget)
                       |widget.tpl(html of the widget)
                        handler.php(used to render and handle all request of that widget)
                        javascript.js(javascript used for the widget if any.)
                        images(this folder contains all images for the widget)
                              
    */
    $name = escapeshellcmd($name);
    if(is_dir('web/Widgets')) {
        if(is_dir('web/Widgets/'.$name)) {
            switch($name) {
                case 'Comment':
                    $required_parameters = array(
                        'slug',
                    );
                    if(count($required_parameters) > 0) {
                        foreach($required_parameters as $value) {
                            if(!preg_match("/$value/", $arguments)) {
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;
                    break;
                case 'Login':
                    return true;
                    break;
                case 'Poll':
                    $required_parameters = array(
                        'id',
                    );
                    if(count($required_parameters) > 0) {
                        foreach($required_parameters as $value) {
                            if(!preg_match("/$value/", $arguments)) {
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;
                    break;
                case 'ViewTracker':
                    $required_parameters = array(
                        'type',
                    );
                    if(count($required_parameters) > 0) {
                        foreach($required_parameters as $value) {
                            if(!preg_match("/$value/", $arguments)) {
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;
                    break;
                default:
                    return false;
            }
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

function js_quote($html) {
    return "'".str_replace("\n", "\\n", str_replace("'", "\\'", $html))."'";
}

/*
 url will be widgets.php/comment/uid=1234/id=56
 in which the widget name is comment i.e first argument and rest argument in the url are 
 the required argument for the widget.  
*/
$path_info = @$_SERVER['PATH_INFO'];
$url_var   = preg_split("|/|", $path_info);
$is_widget = false;
$html      = NULL;
if(!empty($url_var[1])) {
    $widget_name = $url_var[1];
    $is_widget = widget_exist($widget_name, $path_info);
}
else {
    print(__("Error parsing URL"));
}
//specified widget exists ..
if($is_widget) {
    // if widget exists then call its handler.
    include 'web/Widgets/'.$widget_name.'/handler.php';
}
else {
    print(__("Error parsing URL"));
}
?>