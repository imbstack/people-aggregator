<?php

/**
* @short Class UrlHelper - generate internal URLs and Links
*
* @author Zoran Hron, March 2009.
*/
class UrlHelper {

  public static function url_for($route, $query_vars = array()) {
   global $app;
    if($route == 'current_page') {
      $url = PA::$url . $app->current_route; 
    } else if(preg_match("#http[s]?://[\w\.]+/[\w]+.php#i", $route)) {
      $url = $route;
    } else {
      $url = PA::$url . $route;
    }  
    return self::add_query_vars($url, $query_vars);
  }

  public static function link_to($route, $content = null, $title = null, $query_vars = array()) {
    $href = self::url_for($route, $query_vars);
    $link_content = (!$content) ? $href : $content;
    $link_title   = (!$title)   ? $content : $title;
    return "<a href=\"$href\" title=\"$title\" alt=\"$title\">$content</a>";
  }

  public static function normalize_url($url_str) {
    $ret_str = preg_replace('/(\?\&|\&\?|\?+|\&+)/i', '&', $url_str);
    return preg_replace('/(\&)/', '?', $ret_str, 1);
  }

  public static function add_query_vars($url, $query_vars) {
    $ret_url = $url;
    foreach($query_vars as $name => $value) {
      $ret_url .= (is_numeric($name)) ? "/$value" : "&$name=$value";
    }
    return self::normalize_url($ret_url);
  } 

}

?>