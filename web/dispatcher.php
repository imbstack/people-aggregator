<?php

/**
 * @script dipatcher.php
 *
 * First loaded script for any WEB request sent to the PA.
 * This script dispatch WEB requests for: WSAPI calls, file
 * requests including request for PHP scripts, JS, CSS and
 * other file types.
 * And finally this script implements shadowing model for
 * these file types and handle file downloads.
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.8
 *
 * @note       Do not forget that this script will be called
 *             before any other script for any WEB Request
 *             because rewritting rule in .htacces file forwarding
 *             all those requests here!
 */

include dirname(__FILE__) . "/../../project_config.php";

$core_dir    = PA_PROJECT_CORE_DIR;
$project_dir = PA_PROJECT_PROJECT_DIR;

define( "REGEXP_MATCH_STRING",
        "^([^?=]*)/(([^/?=]+)\." .
        "(asf|avi|css|csv|doc|docx|exe|cab|jar|gif|htc|htm|html|jpg|jpeg|js|" .
        "json|mov|mp3|mpg|mpeg|pdf|php|png|ppt|pptx|rar|" .
        "swf|txt|wav|wma|wmv|xml|xspf|zip))" .
        "(.*)$"
      );

define( "PCRE_MATCH_STRING",
        "!^([^?=]*)/(([^/?=]+)\." .
        "(asf|avi|css|csv|docx|doc|exe|cab|jar|gif|htc|html|htm|jpeg|jpg|json|" .
        "js|mov|mp3|mpeg|mpg|pdf|php|png|pptx|ppt|rar|" .
        "swf|txt|wav|wma|wmv|xml|xspf|zip))" .
        "(.*)$!i"
      );

 global $current_route, $route_query_str;

 $routes = array();
 $current_route = null;

 $prr_succes = @include_once($project_dir . DIRECTORY_SEPARATOR . "redirect_rules.inc");
 $crr_succes = @include_once($core_dir . DIRECTORY_SEPARATOR . "redirect_rules.inc");
 if($crr_succes) $routes = array_merge($routes, $core_routes);
 if (!empty($project_routes)) {
    if($prr_succes) $routes = array_merge($routes, $project_routes);
 }

 $matches = null;
 $match_url = $_SERVER['REQUEST_URI'];
 checkRedirections($routes, $match_url, &$matches);

 if(empty($matches)) {
     $match_url = $_SERVER['REDIRECT_URL'];
     checkRedirections($routes, $match_url, &$matches);
 }

 if(!empty($matches)) {
    $path_pref = (isset($matches[1])) ? $matches[1] : null;
    $file_name = $matches[2];
    $file_type = strtolower($matches[4]);
    $url_info  = (isset($matches[5])) ? $matches[5] : null;
    $file_path = "web" . $path_pref . '/' . $file_name;
    $req_url_info =  @parse_url($url_info);
    $path_info = (isset($req_url_info['path'])) ? $req_url_info['path'] :null;

    $guery_str = null;
    $org_query_str = $_SERVER['QUERY_STRING'];
    if(isset($req_url_info['query'])) {
        $guery_str = process_query_string($req_url_info['query']);
    } else if(isset($req_url_info['path'])) {
        $guery_str = process_query_string($req_url_info['path']);
    } else if(isset($_SERVER['REDIRECT_QUERY_STRING'])) {
        $guery_str = process_query_string($_SERVER['REDIRECT_QUERY_STRING']);
    }
    switch($file_type) {
      case 'php':
        $_SERVER['SCRIPT_FILENAME'] = $file_path;
        $_SERVER['SCRIPT_NAME']     = "$path_pref/$file_name";
        $_SERVER['PHP_SELF']        = "$path_pref/$file_name" . $path_info;
        $_SERVER['PATH_INFO']       = $path_info;
        $_SERVER['REQUEST_URI']     = "$path_pref/$file_name" . $path_info . $guery_str;
        $_SERVER['QUERY_STRING']    = preg_replace("#[\?\&]*page_id\=[\d]+#", "", $guery_str);
        if(!$script = get_real_file_path($file_path)) {
           out_error404($file_path);
        }
        if(!$current_route) {
          $current_route   = $_SERVER['PHP_SELF'];
          $route_query_str = $_SERVER['QUERY_STRING'];
        } else {
          $route_query_str = $org_query_str;
        }
        require_once($file_path);
      break;
      case 'json':
          if(!$json = get_real_file_path($file_path)) {
             out_error404($file_path);
          }
          $content_length = filesize($json);
          header("Content-type: application/json; charset: UTF-8");
          header("Cache-Control: no-cache, no-store, max-age=0, must-revalidate");
          header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
          header("Pragma: no-cache");
          header("Content-Length: " . (int)$content_length);
          flush();
          readfile($json);
          exit;
      break;
      case 'xml':
      case 'xspf':
          if(!$xml = get_real_file_path($file_path)) {
             out_error404($file_path);
          }
          $content_length = filesize($xml);
          header("Content-Type: application/$file_type; charset: UTF-8");
          header("Cache-Control: no-cache, no-store, max-age=0, must-revalidate");
          header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
          header("Pragma: no-cache");
          header("Content-Length: " . (int)$content_length);
          flush();
          readfile($xml);
          exit;
      break;
      case 'js':
          if(!$script = get_real_file_path($file_path)) {
             out_error404($file_path);
          }
          $content_length = filesize($script);
          header("Content-type: text/javascript; charset: UTF-8");
          header("Cache-Control: must-revalidate");
          header("Expires: " . gmdate("D, d M Y H:i:s", time()+3600) . " GMT");
          header("Content-Length: " . (int)$content_length);
          flush();
          readfile($script);
          exit;
      break;
      case 'css':
          if(!$style = get_real_file_path($file_path)) {
             out_error404($file_path);
          }
          $content_length = filesize($style);
          header("Content-type: text/css; charset: UTF-8");
          header("Cache-Control: must-revalidate");
          header("Expires: " . gmdate("D, d M Y H:i:s", time()+3600) . " GMT");
          header("Content-Length: " . (int)$content_length);
          flush();
          readfile($style);
          exit;
      break;
      case 'gif':
      case 'png':
      case 'jpg':
      case 'jpeg':
          if($file_type == 'jpg') $file_type = 'jpeg';
          if(!$image = get_real_file_path($file_path)) {
             out_error404($file_path);
          }
          $img_size = filesize($image);
          $expires = 60 * 60 * 24 * 3;
          $exp_gmt = gmdate("D, d M Y H:i:s", time() + $expires )." GMT";
          $mod_gmt = gmdate("D, d M Y H:i:s", time() + (3600 * -5 * 24 * 365) )." GMT";
          header("Content-type: image/$file_type");
          header('Content-Length: ' . $img_size);
          flush();
          readfile($image);
          exit;
      break;
      case "asf":
      case "avi":
      case "csv":
      case "doc":
      case "docx":
      case "exe":
      case "cab":
      case "jar":
      case "mov":
      case "mp3":
      case "mpg":
      case "mpeg":
      case "pdf":
      case "ppt":
      case "pptx":
      case "rar":
      case "txt":
      case "htc":
      case "wav":
      case "wma":
      case "wmv":
      case "zip":
      case "swf":

          if(!$file_download = get_real_file_path($file_path)) {
             out_error404($file_path);
          }
          download($file_download, $file_type);
          exit;
      break;
      case 'htm':
      case 'html':
          if(!$html = get_real_file_path($file_path)) {
             out_error404($file_path);
          }
          $content_length = filesize($html);
          header("Content-type: text/html; charset: UTF-8");
          header("Cache-Control: must-revalidate");
          header("Expires: " . gmdate("D, d M Y H:i:s", time()+3600) . " GMT");
          header("Content-Length: " . (int)$content_length);
          flush();
          readfile($html);
          exit;
      default:
    }
 } else {
    out_error404($_SERVER['REQUEST_URI']);
 }

 function checkRedirections($routes, $url, &$matches) {
  global $current_route;
    if(false == preg_match(PCRE_MATCH_STRING, $url, $matches)) {
       foreach($routes as $_expr => $_route) {
         $_matches = array();
         if(true == preg_match('!'.$_expr.'!i', $url, $_matches)) {
           $current_route   = getRouteForMask($url);
           array_shift($_matches);
           if(count($_matches) > 0) {
             $arg_names = array();
             $arg_expr = "#[\?\&]([^/&]+)\=\%[s]|\%[s]#";
             if(preg_match_all($arg_expr, $_route, $_imatch) && (isset($_imatch[1])))
             {
                if(count($_imatch[1]) != count($_matches)) {
                  out_error_message(
                      "Redirect rule \"$_expr\" => \"$_route\" is invalid. <br />
                       Please check your redirect_rules.inc file."
                  );
                }
                $arg_names = $_imatch[1];
                $args = array();
                for($cnt = 0; $cnt < count($_matches); $cnt++) {
                  if(!empty($arg_names[$cnt])) {
                    $arg_name  = $arg_names[$cnt];
                    $$arg_name = $_matches[$cnt];
                    $args[] = "\$$arg_name";
                    $_GET[$arg_name] = $$arg_name;
                    $_REQUEST[$arg_name] = $$arg_name;
                  } else {
                    $args[] = "\"$_matches[$cnt]\"";
                  }
                }
             } else if((isset($_imatch[1])) && (count($_imatch[1]) != count($_matches))){
                $args = array();
                foreach($_matches as &$_match) {
                  $_match = "\"$_match\"";
                }
                $args = array_merge($_matches, $args);
             } else {
                out_error_message(
                    "Redirect rule \"$_expr\" => \"$_route\" is invalid. <br />
                     Please check your redirect_rules.inc file."
                );
             }
             $arguments = implode(', ', $args);
             $res = eval("\$new_url = sprintf(\$_route, $arguments);");
             if(!empty($new_url)) {
               $new_url = rtrim($new_url, " .&?=");
             }
           } else {
             $res = true;
             $new_url = $_route;
           }
           if(($res !== false) && (isset($new_url))) {
             checkRedirections($routes, $new_url, &$matches);
           }
           break;
         }
       }
    }
 }

  function download($file_path, $ext) {
      $file = end(explode('/', $file_path));
      switch($ext){
          case "asf":     $type = "video/x-ms-asf";                break;
          case "avi":     $type = "video/x-msvideo";               break;
          case "exe":     $type = "application/octet-stream";      break;
          case "cab":     $type = "application/octet-stream";      break;
          case "doc":     $type = "application/msword";            break;
          case "docx":    $type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"; break;
          case "jar":     $type = "application/octet-stream";      break;
          case "mov":     $type = "video/quicktime";               break;
          case "mp3":     $type = "audio/mpeg";                    break;
          case "mpg":     $type = "video/mpeg";                    break;
          case "mpeg":    $type = "video/mpeg";                    break;
          case "pdf":     $type = "application/pdf";               break;
          case "ppt":     $type = "application/vnd.ms-powerpoint"; break;
          case "pptx":    $type = "application/vnd.openxmlformats-officedocument.presentationml.presentation"; break;
          case "rar":     $type = "encoding/x-compress";           break;
          case "swf":     $type = "application/x-shockwave-flash"; break;
          case "txt":     $type = "text/plain";                    break;
          case "wav":     $type = "audio/wav";                     break;
          case "wma":     $type = "audio/x-ms-wma";                break;
          case "wmv":     $type = "video/x-ms-wmv";                break;
          case "zip":     $type = "application/x-zip-compressed";  break;
          default:        $type = "application/force-download";    break;
      }
      $header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
                   ? preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1)
                   : $file;
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: public", false);
      header("Content-Description: File Transfer");
      header("Content-Type: " . $type);
      header("Accept-Ranges: bytes");
      header("Content-Disposition: attachment; filename=\"" . $header_file . "\";");
      header("Content-Transfer-Encoding: binary");
      header("Content-Length: " . filesize($file_path));
      header('HTTP/1.1 200 OK');
      if ($stream = fopen($file_path, 'rb')){
         while(!feof($stream) && connection_status() == 0){
            set_time_limit(0);
            print(fread($stream,1024*8));
         }
         fclose($stream);
      }
      flush();
  }

/**
 * @class DispatcherException
 *
 * The DispatcherException class implements the basics methods for
 * WEB dispatcher exceptions
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.0.1
 *
 *
 */
class DispatcherException extends Exception {
    public function __construct($message) {
      $msg = "<div style=\"border: 1px solid red; padding: 24px\">
                <h1 style=\"color: red\">DispatcherException</h1>\r\n
                <font style=\"color: red\">$message</font> \r\n
              </div>\r\n";
      echo $msg;
      exit;
    }
}

 function out_error404($file_path) {
   header("HTTP/1.0 404 Not Found");
   echo "
         <h1>Requested URI not found!</h1>
         <p>We apologize, requested URL \"$file_path\" not found.</p>
        ";

   exit;
 }

 function out_error_message($message) {
    header ("Content-type: text/html");
    throw new DispatcherException($message);
 }

 function get_real_file_path($file_path) {
  global $core_dir;
  global $project_dir;
      if(file_exists($project_dir . DIRECTORY_SEPARATOR . $file_path)) {
         return ($project_dir . DIRECTORY_SEPARATOR . $file_path);
      } else if(file_exists($core_dir . DIRECTORY_SEPARATOR . $file_path)) {
         return ($core_dir . DIRECTORY_SEPARATOR . $file_path);
      }
      return false;
  }

  function process_query_string($query) {
    $query_string =  "?" . rtrim($query, "/?");
    $matches = array();
    $arg_expr = "#[\/\?\&]([^/&%]+\=[^\?&]+)#";
    if(preg_match_all($arg_expr, $query_string, $matches) && isset($matches[1])) {
      foreach($matches[1] as $param) {
        $param_info  = explode('=', $param);
        $param_name  = $param_info[0];
        $param_value = $param_info[1];
        $_GET[$param_name]     = urldecode($param_value);
        $_REQUEST[$param_name] = urldecode($param_value);
      }
     $query_string = preg_replace('/(\?\&|\&\?|\?+|\&+)/i', '&', $query_string);
     return preg_replace('/(\&)/', '?', $query_string, 1);
    }
    return null;
  }

  function getRouteForMask($mask) {
    $url = $mask;
    $urinfo = preg_split("#[\/\?]{1}#", $url, -1);
    for($cnt=0; $cnt < count($urinfo); $cnt++) {
      if(preg_match("#[\=\?\&\+\,]+#", $urinfo[$cnt])) {
        unset($urinfo[$cnt]);
      }
    }
    return implode("/", $urinfo);
  }
?>