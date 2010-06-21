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

require_once "api/DB/Dal/Dal.php";

/* Local cache for DB rows, shared values, etc, and ext_cache to access the ext_cache DB table.
 * Author: Phil
 * Eventually this will also connect to memcached.
 */
class Cache {

    private static $store = array();

    public static function reset() {
        Cache::$store = array();
    }

    public static function setValue($k, $v) {
        Logger::log("Storing value $k in the cache", LOGGER_CACHE);
        Cache::$store[$k] = $v;
    }

    public static function setValues($values) {
        Logger::log("Storing multiple values in the cache", LOGGER_CACHE);
        foreach($values as $k => $v) {
            Cache::$store[$k] = $v;
        }
    }

    public static function getValue($k) {
        Logger::log("Retrieving value $k from the cache", LOGGER_CACHE);
        return array_key_exists($k, Cache::$store) ? Cache::$store[$k] : NULL;
    }

    public static function getValues($keys) {
        Logger::log("Retrieving multiple vales from the cache", LOGGER_CACHE);
        $ret = array();
        foreach($keys as $k) {
            $ret[$k] = array_key_exists($k, Cache::$store) ? Cache::$store[$k] : NULL;
        }
        return $ret;
    }

    public static function removeValue($k) {
        Logger::log("Removing value $k from the cache", LOGGER_CACHE);
        unset(Cache::$store[$k]);
    }

    public static function getExtCache($user_id, $key) {
        $row = Dal::query_one("SELECT data FROM ext_cache WHERE user_id=? AND cache_key=? AND expires < NOW()", array($user_id, $key));
        $result = NULL;
        if($row) {
            $result = @unserialize($row[0]);
        }
        return $result;
    }

    public static function setExtCache($user_id, $key, $data, $interval = "1 HOUR") {
        Dal::query("DELETE FROM ext_cache WHERE expires < NOW()");
        Dal::query("DELETE FROM ext_cache WHERE user_id=? AND cache_key=?", array($user_id, $key));
        if($data) {
            Dal::query("INSERT INTO ext_cache SET created=NOW(), expires=DATE_ADD(NOW(), INTERVAL $interval), user_id=?, cache_key=?, data=?", array($user_id, $key, serialize($data)));
        }
    }

    public static function flushExtCache($user_id, $key) {
        Dal::query("DELETE FROM ext_cache WHERE user_id=? AND cache_key=?", array($user_id, $key));
    }
}

/*
Cache::setValue("foo", "bar");
Cache::setValues(array("foo" => "bar",
		       "baz" => "boz",
		       ));
*/
?>