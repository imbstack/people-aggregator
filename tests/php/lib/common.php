<?php

if (@$_SERVER['REQUEST_METHOD']) {
  die("Test scripts are not accessible via a browser.");
}

require_once "PHPUnit/Framework.php";
require_once dirname(__FILE__)."/../../../config.inc";
require_once "api/User/User.php";
require_once "api/Content/Content.php";
require_once "api/Comment/Comment.php";
require_once "ext/BlogPost/BlogPost.php";
require_once "HTTP/Client.php";

class PA_TestCase extends PHPUnit_Framework_TestCase {

  public function get_and_parse($url, $c=NULL, $method="GET", $data=NULL) {
    if (empty($c)) $c = new HTTP_Client;
    $t_start = microtime(TRUE);
    switch ($method) {
    case "POST":
      //      $c->setDefaultHeader("Content-Type", "application/x-www-form-urlencoded");
      $code = $c->post($url, $data);
      break;
    default:
      $code = $c->get($url);
      break;
    }
    $t_retrieved = microtime(TRUE);
    $this->assertEquals(200, $code, "Error $code retrieving $url");
    $resp = $c->currentResponse();
    list($dom, $xp) = Test::parse_html($resp['body']);
    $t_finish = microtime(TRUE);
    echo sprintf("Downloaded %s in %.1f s\n", $url, $t_retrieved - $t_start);
    return array($dom, $xp, $t_retrieved - $t_start, $c);
  }
}

class Test {

  public static function get_test_user($uid=1) {
    // get the first user, for testing
    $user = new User();
    $user->load($uid);
    return $user;
  }

  public static function parse_html($html) {
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    return array($dom, new DOMXPath($dom));
  }

}

function start_timing_queries() {
  global $_timed_queries;
  $_timed_queries = array();
}

start_timing_queries();

function summarize_timed_queries() {
  global $_timed_queries;
  $display = array();
  foreach ($_timed_queries as $sql => $times) {
    $total = 0.0;
    foreach ($times as $tm) {
      $total += $tm;
    }
    $display[] = array($total, count($times), $sql);
  }
  sort($display);
  echo "================================================================================\n";
  echo "  Queries, with those taking the most aggregate time appearing first\n";
  foreach (array_reverse($display) as $q) {
    echo "--------------------------------------------------------------------------------\n";
    list($total_time, $n, $sql) = $q;
    echo sprintf("total %.04f s | count $n | average %.04f s | $sql\n", $total_time, $total_time / $n);
  }
}

function explain_query($sql, $args, $query_time) {
  if (!preg_match("/^\s*SELECT/i", $sql)) return;
  echo "================================================================================\n";
  echo sprintf("%.04f s | SQL: %s\n", $query_time, $sql);
  global $_timed_queries;
  $_timed_queries[$sql][] = $query_time;

  $explain = Dal::query("EXPLAIN $sql", $args);
  $tables = array();
  while ($r = Dal::row_assoc($explain)) {
    if (!empty($r['table'])) $tables[] = $r['table'];
    echo "\n";
    foreach ($r as $k => $v) {
      echo sprintf("%15s: %s\n", $k, $v);
    }
  }

  foreach ($tables as $table) {
    echo "--------------------------------------------------------------------------------\n";
    try {
      $create_table = Dal::query_one("SHOW CREATE TABLE $table");
    } catch (PAException $e) {
      if ($e->getCode() != DB_QUERY_FAILED) throw $e;
      $bits = preg_split("/(\s+|,)/", $sql);
      $pos = array_search($table, $bits);
      if ($pos === NULL) throw new PAException(GENERAL_SOME_ERROR, "Failed to find real name for table $table in query $sql");
      $table = (strtolower($bits[$pos-1]) == 'as') ? $bits[$pos-2] : $bits[$pos-1];
      $create_table = Dal::query_one("SHOW CREATE TABLE $table");
    }
    echo $create_table[1]."\n";
  }
  
}


?>