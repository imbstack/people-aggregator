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
 *
 * @class FileCache
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
class FileCache {

    /**
     *
     * @param $cache_id string unique cache identifier
     * @param $content content of any PHP content type
     * @param $expire  cache lifetime
     */
    public static function store($cache_id, $content, $expire = 120) {
        self::fetch($cache_id, $content, $expire);
    }

    /**
     * Test to see whether the currently loaded cache_id has a valid
     * corrosponding cache file.
     */
    public static function is_cached($cache_id, $expire = 120) {
        $cache_file = PA::$project_dir . "/web/cache/" . md5($cache_id);
        // Cache file exists?
        if(!file_exists($cache_file)) return false;

        // Can get the time of the file?
        if(!($mtime = filemtime($cache_file))) return false;

        // Cache expired?
        if(($mtime + $expire) < time()) {
            @unlink($cache_file);
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * This function returns a cached copy of a content (if it exists),
     * otherwise, it parses it as normal and caches the content.
     *
     * @param $file string the template file
     */
    public static function fetch($cache_id, $content = null, $expire = 120) {

        $cache_file = PA::$project_dir . "/web/cache/" . md5($cache_id);
        if(self::is_cached($cache_id, $expire)) {
            $fp = @fopen($cache_file, 'r');
            $content = unserialize(fread($fp, filesize($cache_file)));
            fclose($fp);
            return $content;
        }
        else {
            // Write the cache
            if($fp = fopen($cache_file, 'w')) {
               fwrite($fp, serialize($content));
               fclose($fp);
            }
            else {
               die("Unable to write cache ".$cache_file);
            }
            return $content;
        }
    }

    public static function invalidate_cache($cache_id) {
      $cache_file = PA::$project_dir . "/web/cache/" . md5($cache_id);
      if (file_exists($cache_file)) {
        unlink($cache_file);
      }
      
    }
}

?>