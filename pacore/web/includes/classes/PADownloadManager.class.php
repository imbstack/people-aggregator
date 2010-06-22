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
 * @Class PADownloadManager
 *
 * This class handle WEB requests for file downloads
 *
 *
 *
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 * @todo
 * TODO        add code for handling downloads security and statistics (downloads tracker)
 *
 */
require_once "web/includes/functions/helper_functions.php";

class PADownloadManager {

    public static $last_error = null;

    public static $total_files = 0;

    public static $total_kbyts = 0;

    public static $statistics = array();

    public $file_type;

    public $file_path;

    public $dir_name;

    private $exlude_sec_test = array(
        'htm',
        'html',
        'json',
        'xml',
        'xspf',
        'js',
        'css',
        'gif',
        'png',
        'jpg',
        'jpeg',
    );

    public function __construct($file_path) {
        $this->file_path = $file_path;
        $path_parts      = pathinfo($file_path);
        $this->dir_name  = strtolower($path_parts['dirname']);
        $this->file_name = strtolower($path_parts['basename']);
        $this->file_type = strtolower($path_parts['extension']);
    }

    public function __destruct() {
    }

    public function getFile() {
        if($result = $this->checkDownloadPermissions()) {
            if($result = $this->download()) {
                self::$last_error = null;
            }
        }
        return $result;
    }

    private function checkDownloadPermissions() {
        if(in_array($this->file_type, $this->exlude_sec_test)) {
            return true;
        }
        else {
            if(1) {
                // this is temporrary - some nice code for test download permission comming soon !
                return true;
            }
            else {
                self::$last_error = __(F_NO_PERMIS);
                return false;
            }
        }
    }

    private function doStatistics($content_length) {
        // this is temporary - some nice code for handling download statistic comming soon !
        // statistics data should be written to a DB table
        self::$total_files += 1;
        self::$total_kbyts += $content_length;
        if(isset(self::$statistics[$this->file_name])) {
            self::$statistics[$this->file_name]['counter'] += 1;
        }
        else {
            self::$statistics[$this->file_name]['counter'] = 1;
        }
    }

    public function lastError() {
        return self::$last_error;
    }

    private function download() {
        switch($this->file_type) {
            case 'json':
                if(!$json = getShadowedPath($this->file_path)) {
                    self::$last_error = __(F_NOT_FOUND);
                    return false;
                }
                $content_length = filesize($json);
                $this->doStatistics($content_length);
                header("Content-type: application/json; charset: UTF-8");
                header("Cache-Control: no-cache, no-store, max-age=0, must-revalidate");
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Pragma: no-cache");
                header("Content-Length: ".(int) $content_length);
                flush();
                readfile($json);
                break;
            case 'xml':
            case 'xspf':
                if(!$xml = getShadowedPath($this->file_path)) {
                    self::$last_error = __(F_NOT_FOUND);
                    return false;
                }
                $content_length = filesize($xml);
                $this->doStatistics($content_length);
                header("Content-Type: application/$this->file_type; charset: UTF-8");
                header("Cache-Control: no-cache, no-store, max-age=0, must-revalidate");
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Pragma: no-cache");
                header("Content-Length: ".(int) $content_length);
                flush();
                readfile($xml);
                break;
            case 'htm':
            case 'html':
                if(!$html = getShadowedPath($this->file_path)) {
                    self::$last_error = __(F_NOT_FOUND);
                    return false;
                }
                return $this->streamFiles($html, "text/html; charset: UTF-8");
                break;
            case 'js':
                if(!$script = getShadowedPath($this->file_path)) {
                    self::$last_error = __(F_NOT_FOUND);
                    return false;
                }
                return $this->streamFiles($script, "text/javascript; charset: UTF-8");
                break;
            case 'css':
                if(!$style = getShadowedPath($this->file_path)) {
                    self::$last_error = __(F_NOT_FOUND);
                    return false;
                }
                return $this->streamFiles($style, "text/css; charset: UTF-8");
                break;
            case 'gif':
            case 'png':
            case 'jpg':
            case 'jpeg':
                //           if($this->file_type == 'jpg') $this->file_type = 'jpeg';
                if(!$image = getShadowedPath($this->file_path)) {
                    self::$last_error = __(F_NOT_FOUND);
                    return false;
                }
                return $this->streamFiles($image, "image/$this->file_type");
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
                if(!$file_download = getShadowedPath($this->file_path)) {
                    self::$last_error = __(F_NOT_FOUND);
                    return false;
                }
                return $this->streamDownloads($file_download, $this->file_type);
                break;
            default:
                self::$last_error = __(F_NOT_ALLOW);
                return false;
        }
        return true;
    }

    private function streamFiles($filename, $content_type) {
        $lastmod = filemtime($filename);
        // check if 'sending' is necessary
        $must_send = true;
        // get file content
        $content = file_get_contents($filename);
        $etag = '"'.md5($content).'"';
        // check 'If-Modified-Since' header
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && ((gmdate('D, d M Y H:i:s', $lastmod)." GMT") == trim($_SERVER['HTTP_IF_MODIFIED_SINCE']))) {
            header("HTTP/1.0 304 Not Modified");
            header("ETag: $etag");
            header("Content-Length: 0");
            $must_send = false;
        }
        // check 'If-None-Match' header (ETag)
        if($must_send && isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            $inm = explode(",", $_SERVER['HTTP_IF_NONE_MATCH']);
            foreach($inm as $i) {
                if(trim($i) != $etag) {
                    continue;
                }
                header("HTTP/1.0 304 Not Modified");
                header("ETag: $etag");
                header("Content-Length: 0");
                $must_send = false;
                break;
            }
        }
        // caching headers (enable cache for one day)
        $expires = 60*60*24;
        $exp_gmt = gmdate("D, d M Y H:i:s", time()+$expires)." GMT";
        $mod_gmt = gmdate("D, d M Y H:i:s", $lastmod)." GMT";
        header("Expires: $exp_gmt");
        header("Last-Modified: $mod_gmt");
        header("Cache-Control: public, max-age=$expires");
        header("Pragma: !invalid");
        // send image
        if($must_send) {
            $size = filesize($filename);
            $this->doStatistics($size);
            header("ETag: $etag");
            header("Content-Type: $content_type");
            header("Content-Length: $size");
            echo $content;
        }
        else {
            header("Content-Type: !invalid");
        }
        flush();
        return true;
    }

    private function streamDownloads($file_path, $ext) {
        $file = end(explode('/', $file_path));
        switch($ext) {
            case "asf":
                $type = "video/x-ms-asf";
                break;
            case "avi":
                $type = "video/x-msvideo";
                break;
            case "exe":
                $type = "application/octet-stream";
                break;
            case "cab":
                $type = "application/octet-stream";
                break;
            case "doc":
                $type = "application/msword";
                break;
            case "docx":
                $type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
                break;
            case "jar":
                $type = "application/octet-stream";
                break;
            case "mov":
                $type = "video/quicktime";
                break;
            case "mp3":
                $type = "audio/mpeg";
                break;
            case "mpg":
                $type = "video/mpeg";
                break;
            case "mpeg":
                $type = "video/mpeg";
                break;
            case "pdf":
                $type = "application/pdf";
                break;
            case "ppt":
                $type = "application/vnd.ms-powerpoint";
                break;
            case "pptx":
                $type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
                break;
            case "rar":
                $type = "encoding/x-compress";
                break;
            case "swf":
                $type = "application/x-shockwave-flash";
                break;
            case "txt":
                $type = "text/plain";
                break;
            case "wav":
                $type = "audio/wav";
                break;
            case "wma":
                $type = "audio/x-ms-wma";
                break;
            case "wmv":
                $type = "video/x-ms-wmv";
                break;
            case "zip":
                $type = "application/x-zip-compressed";
                break;
            default:
                $type = "application/force-download";
                break;
        }
        $header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $file, substr_count($file, '.')-1) : $file;
        if($stream = fopen($file_path, 'rb')) {
            $this->doStatistics(filesize($file_path));
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public", false);
            header("Content-Description: File Transfer");
            header("Content-Type: ".$type);
            header("Accept-Ranges: bytes");
            header("Content-Disposition: attachment; filename=\"".$header_file."\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($file_path));
            header('HTTP/1.1 200 OK');
            while(!feof($stream) && connection_status() == 0) {
                set_time_limit(0);
                print(fread($stream, 1024*8));
            }
            fclose($stream);
            flush();
            return true;
        }
        else {
            flush();
            self::$last_error = __(F_STR_ERROR);
            return false;
        }
    }
}
if(!function_exists('getShadowedPath')) {
    // global function
    function getShadowedPath($file_path) {
        if(file_exists(PA_PROJECT_PROJECT_DIR.DIRECTORY_SEPARATOR.$file_path)) {
            return(PA_PROJECT_PROJECT_DIR.DIRECTORY_SEPARATOR.$file_path);
        }
        elseif(file_exists(PA_PROJECT_CORE_DIR.DIRECTORY_SEPARATOR.$file_path)) {
            return(PA_PROJECT_CORE_DIR.DIRECTORY_SEPARATOR.$file_path);
        }
        return false;
    }
}
?>
