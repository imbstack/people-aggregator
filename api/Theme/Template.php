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

// Template engine for PeopleAggregator.

// Derived from code in the public domain: http://www.massassi.com/php/articles/template_engines/

class Template {
    var $vars; /// Holds all the template variables

    /**
     * Constructor
     *
     * @param $file string the file name you want to load
     */
    function Template($file = null, $module = null) {
        $this->file = $file;
        $this->set_object("mod", $module);
    }

    /**
     * Set a template variable.
     */
    function set($name, $value) {
        $this->vars[$name] = is_object($value) ? $value->fetch() : $value;
    }

    /**
     * Set object.
     */
    function set_object($name, $value) {
        $this->vars[$name] = $value;
    }


    /**
     * Open, parse, and return the template file.
     *
     * @param $file string the template file name
     */
    function fetch($file = null) {
        if(!$file) $file = $this->file;
        @extract($this->vars);          // Extract the vars to local namespace
        ob_start();                    // Start output buffering
        include($file);                // Include the file
        $contents = ob_get_contents(); // Get the contents of the buffer
        ob_end_clean();                // End buffering and discard
        
        global $debug_annotate_templates;
        if ($debug_annotate_templates && isset($_GET['debug'])) {
            $contents .= '&larr; tpl=<abbr title="'.$file.'">'.basename($file).'</abbr>';
        }

        return $contents;              // Return the contents
    }
}

/**
* An extension to Template that provides automatic caching of
* template contents.
*/
class CachedTemplate extends Template {
    var $cache_id;
    var $expire;
    var $cached;

    /**
     * Constructor.
     *
     * @param $cache_id string unique cache identifier
     * @param $expire int number of seconds the cache will live
     */
    function CachedTemplate($cache_id = null, $expire = 60) {
    	// reduced default expires time frim 900 to 60
        $this->Template();
        $this->cache_id = $cache_id ? (PA::$project_dir."/web/cache/" . md5($cache_id)) : $cache_id;
        $this->expire   = $expire;
    }

    /**
     * Test to see whether the currently loaded cache_id has a valid
     * corrosponding cache file.
     */
    function is_cached() {
        global $debug_disable_template_caching;
        if ($debug_disable_template_caching) return false;

        if($this->cached) return true;

        // Passed a cache_id?
        if(!$this->cache_id) return false;

        // Cache file exists?
        if(!file_exists($this->cache_id)) return false;

        // Can get the time of the file?
        if(!($mtime = filemtime($this->cache_id))) return false;

        // Cache expired?
        if(($mtime + $this->expire) < time()) {
            @unlink($this->cache_id);
            return false;
        }
        else {
            /**
             * Cache the results of this is_cached() call.  Why?  So
             * we don't have to double the overhead for each template.
             * If we didn't cache, it would be hitting the file system
             * twice as much (file_exists() & filemtime() [twice each]).
             */
            $this->cached = true;
            return true;
        }
    }

    /**
     * This function returns a cached copy of a template (if it exists),
     * otherwise, it parses it as normal and caches the content.
     *
     * @param $file string the template file
     */
    function fetch_cache($file=NULL) {

        if($this->is_cached()) {
            $fp = @fopen($this->cache_id, 'r');
            $contents = fread($fp, filesize($this->cache_id));
            fclose($fp);
            global $debug_annotate_templates;
            if ($debug_annotate_templates && isset($_GET['debug'])) {
            	$contents .= "<br/>loaded from cache: ".$this->cache_id;
            }
            return $contents;
        }
        else {
            $contents = $this->fetch($file);

            global $debug_disable_template_caching;
            if (!$debug_disable_template_caching) {
                // Write the cache
                if($fp = fopen($this->cache_id, 'w')) {
                    fwrite($fp, $contents);
                    fclose($fp);
                }
                else {
                    die('Unable to write cache '.$this->cache_id);
                }
            }

            return $contents;
        }
    }

    public static function invalidate_cache($file) {
      $file_path = PA::$project_dir . "/web/cache/" . md5($file);
      if (file_exists($file_path)) {
        unlink($file_path);
      }
      
    }
}

?>