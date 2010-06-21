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
 *  Function : get_url()
 *  Purpose  : get fancy url - currently it is working for user only
 *  @param    $type - string - it will take up the file name
 *  @param    $params - array - contains extra params - uid, cid, current_url
 *  @return   fancy url of the user
 *  Note      PA::$config->enable_fancy_url should be set to TRUE in local_config.php
 */
function get_url($type, $params) {
    if(empty(PA::$config->enable_fancy_url)) {
        return $params['current_url'];
    }
    //fancy url for user
    if($type == FILE_USER_BLOG) {
        $url = PA::$url.'/users/'.$params['login'].'/';
    }
    //append query string if any
    if(!empty($params['query_str'])) {
        $url .= '?'.$params['query_str'];
    }
    //here we can add logic for other url types
    return $url;
}
// Rails-style fancy URL generator.
// Examples:
// Generate a user URL: $url = url_for("user", array("login" => "marc"));
// With extra params: $url = url_for("user", array("login" => "marc", "post_type" => "blog"));
// Including everything from $_GET not explicitly overridden:
//  $url = url_for("user", array("login" => "marc", "post_type" => "blog"), $_GET);
function url_for($controller, $params, $current_params = NULL) {
    // Default to sending the request to the script $controller.php
    $prefix = $controller.'.php';
    if($controller == "pages_links") {
        $prefix = 'pages.php';
    }
    // Fill in default parameters, if present
    if(!empty($current_params)) {
        foreach($current_params as $k => $v) {
            if(!isset($params[$k])) {
                $params[$k] = $v;
            }
        }
    }
    // If fancy URLs are enabled: see if we can find any known patterns.
    if(PA::$config->enable_fancy_url) {
        switch($controller) {
            case 'user':
            case 'user_blog':
                if(!empty($params['login'])) {
                    $prefix = ltrim(PA_ROUTE_USER_PUBLIC, "/").'/'.$params['login'];
                    unset($params['login']);
                }
                break;
            case 'links':
                if(!empty($params['caption'])) {
                    $prefix = "links/".$params['caption'].'/';
                    unset($params['caption']);
                }
                break;
            case 'pages_links':
                $prefix = "links/".$params['caption'].'/';
                break;
        }
    }
    // Fill in the query string
    $encoded_params = array();
    foreach($params as $k => $v) {
        $encoded_params[] = $k.'='.urlencode($v);
    }
    // Build final URL
    $url = PA::$url.'/'.$prefix;
    if(!($controller == "pages_links" && PA::$config->enable_fancy_url)) {
        if(!empty($encoded_params)) {
            $url .= '?'.implode("&", $encoded_params);
        }
    }
    return $url;
}

function link_to($linktext, $controller, $params, $current_params = NULL) {
    $url = htmlspecialchars(url_for($controller, $params, $current_params));
    $anchor_tag = !empty($linktext) ? $linktext : $url;
    return '<a href="'.$url.'">'.$anchor_tag.'</a>';
}
?>