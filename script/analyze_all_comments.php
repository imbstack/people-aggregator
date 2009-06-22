<?php

error_reporting(E_ALL);

include dirname(__FILE__)."/../config.inc";
require_once "api/Comment/Comment.php";
while (@ob_end_clean()); // clear output buffer

ini_set("memory_limit", 64*1048576);

$total = Comment::count_all_comments();
echo "Analyzing all comments ($total) \n\n";

$per_page = 1000;

$pages = ceil(floatval($total) / $per_page);
for ($page = 1; $page <= $pages; ++$page) {
  set_time_limit(60);
  $start_mem = memory_get_usage();
  echo "Page $page of $pages; current mem use $start_mem...";
  $cmt_rows = Comment::get_all_comments(0, $per_page, $page);
  echo " after comments loaded, mem usage is ".memory_get_usage()."\n";
  $del_ct = 0;
  foreach ($cmt_rows as $cmt_row) {
    $cmt = new Comment();
    $cmt->load_from_row($cmt_row);
    $del_ct += $cmt->index_spam_domains(TRUE);
    $cmt->index_words();
  }
  echo "$del_ct comments deleted due to blacklisting or excessive linking\n";
  unset($cmt_rows); unset($cmt_row);
  $end_mem = memory_get_usage();
  //  echo "end of page - mem used $end_mem (delta ".($end_mem - $start_mem).").\n";

  echo "Counting up totals\n";
  SpamDomain::recalculate_total_link_counts();
}

echo "Analyzed $total comments\n";

echo "Worst domains:\n";
$sth = Dal::query("SELECT id,domain,count,active_count FROM spam_domains ORDER BY count DESC LIMIT 25");
while ($r = Dal::row($sth)) {
  list($domain_id, $domain, $count, $active_count) = $r;
  echo "$count: $domain (id=$domain_id); $active_count not deleted\n";
}

echo "Worst domains with still-active comments:\n";
$sth = Dal::query("SELECT id,domain,count,active_count FROM spam_domains WHERE active_count <> 0 ORDER BY count DESC LIMIT 25");
while ($r = Dal::row($sth)) {
  list($domain_id, $domain, $count, $active_count) = $r;
  echo "$count: $domain (id=$domain_id); $active_count not deleted\n";
}

?>